 /*
 
 Interruption pins :
 
  Board	          int.0	  int.1	  int.2	  int.3	  int.4	  int.5
  Uno, Ethernet	  2	  3
  Mega2560	  2	  3	  21	  20	  19	  18
  Leonardo	  3	  2	  0	  1
  Due	          (any pin, more info http://arduino.cc/en/Reference/AttachInterrupt)
 
 I2C pins :
 
  Any Arduino pins labeled:  SDA  SCL
  Uno, Redboard, Pro:        A4   A5
  Mega2560, Due:             20   21
  Leonardo:                   2    3
 
 */
 
 // Address of the EEPROM 1010 A_2 A_1 A_0 R/W
 // in my case A_2=A_1=A_0=0 so the address is 0x50 (we keep only the first 7 bits)

/////////////////////////////////
/////////// Includes ////////////
/////////////////////////////////

// You need to install all the following arduino librairies : 
// - OneWire (DS18XX, temperature sensor)
// - DHT11 (DHT11, humidity sensor)
// - RTClib (RTC support)
// - SFE_BMP180 (BMP180, presure sensor)
// - PCD8544 (screen, nokia 5110)

#include <OneWire.h>
#include <SPI.h>
#include <Ethernet.h>
#include <IRremote.h>
#include <PCD8544.h>
#include <idDHT11.h>
#include <SFE_BMP180.h>
#include <Wire.h>
#include <RTClib.h>

//////////////////////////////////////
/////////// Configuration ////////////
//////////////////////////////////////

// Constants
// you can change this MAC address if there is a other device with the same address (very unlikely !)
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// Server to post the data
IPAddress serverIP(1,2,3,4);
String serverIPString = "1.2.3.4";
// put the location of the submission part on your server with the api key
String page = "/arduino/submit.php?apikey=xxxxxxxxxxxxxxxxxx";

// Micro sign for the LCD
static const byte micro[] = { B11111111, B00001000, B00001000, B00000100, B00001111 };
static const byte temp[] = { B00000000, B00000000, B00000110, B00000110, B00000000 };

// IP configuration
IPAddress ip(192,168,1,150);
IPAddress gateway(192,168,1,1);

// this array are the buttons code sent by the remote, you have to modify it with respect of you remote
// you can use the same program in the repro to find the codes of your remote
// denon rc-1175
unsigned long irButtons[] = {1373301766, 1758654652, 435002292, 1749611822, 1218427694, 3076422308, 1752769948, 1344315987, 3428191179, 2422781229};
// the value 21845 is doing nothing to all outputs, 65535 is setting all to off
// these values will be used only if the EEPROM is empty
unsigned long irActions[] = {21845, 21845, 21845, 21845, 21845, 21845, 21845, 21845, 21845, 65535};


#define DEBUG true // To activate the Serial.print calls
#define NB_SEC_POST 900 // Set the number of second between two updates to the server
#define PORT_SERVER 80 // Port number of the arduino webserver, usefull to configure the port forwarding
// on your NAT and also to configure the website

// Ports

// Ports 10,11,12,13 are reserved for the Ethernet Shield
// Ports 3,4,5,6,7 are reserved for the LCD screen

// Buttons
#define PIN_BUTTON_SCREEN 19
#define PIN_BUTTON_LCD_BACKLIGHT 18
#define PIN_BACKLIGHT 22
// Outputs
#define PIN_OUTPUT_RELAYS_START 23
#define NB_OUTPUTS 8
int PIN_OUTPUT_RELAYS_STOP = PIN_OUTPUT_RELAYS_START + NB_OUTPUTS - 1;
#define PIN_OUTPUT_RELAYS_CLOSE HIGH // close logical state (if you use active low relay, put HIGH)
#define PIN_OUTPUT_RELAYS_ACTIVE LOW // active state of the relays (contrary of PIN_OUTPUT_RELAYS_CLOSE)

#define PIN_ONE_WIRE 9
// IR remote
#define PIN_IR 8
#define NB_IR_BUTTONS 10
// Geiger
#define PIN_GEIGER 2
#define PIN_INTER_GEIGER 0 // interrupt number (must be the one that use the previus defined pin (see table above))
// DHT11
#define PIN_DHT11 18
#define PIN_INTER_DHT11 5 // interrupt number (must be the one that use the previus defined pin (see table above))


#define ALTITUDE 550.0 // Altitude for computing the presure relative to the sea-level
#define MAX_CPM 2500 // Define the maximum size for the array storing all the bips
#define ADDRESS_EEPROM 0x50 // I2C address of the EEPROM (only the 7 first bits)

// LCD
#define NB_SEC_SCREEN 10 // Set the number of second between two updates of the screen data
#define LINE_TEMP1 0
#define addressSensorLine1 "xxxx" // Address of the sensor that we want to display on the first line of the screen
#define textLine1 "Ext. " // Short (no more than 7 characters) intro of the sensor to display on the screen
#define LINE_TEMP2 1
#define addressSensorLine2 "yyyy" // Address of the sensor that we want to display on the second line of the screen
#define textLine2 "Int. " // Short (no more than 7 characters) intro of the sensor to display on the screen
#define LINE_HUMIDITY 2
#define LINE_PRESURE 3
#define LINE_RADIATION 4

// RTC
// Date of reference
#define DAY_REFERENCE 16470
#define DAY_REFERENCE_NUMBER 2

//////////////////////////////////
/////////// Variables ////////////
//////////////////////////////////

//////// Ethernet ///////
EthernetClient client;
EthernetServer server(PORT_SERVER);
String GETData;
unsigned long arrayResults[3];
//////// IR Remote ///////
IRrecv irrecv(PIN_IR);
//////// DS18XX ///////
OneWire ds(PIN_ONE_WIRE);
decode_results results;
int T, whole, fract;
unsigned int nbSensors = 0;
byte data[12];
byte addr[8];
byte type_s;
boolean searchSensors = false; 
boolean OneWireDone = false;
boolean reading = false;
//////// RTC ///////
RTC_DS1307 rtc;
/////// DHT11 - Humidity ///////
void dht11_wrapper(); // must be declared before the lib initialization
idDHT11 DHT11(PIN_DHT11,PIN_INTER_DHT11,dht11_wrapper); // Lib instantiate
//////// Presure ///////
SFE_BMP180 pressure;
//////// LCD ///////
static PCD8544 lcd;
int screenToDisplay = 0; // Screen to display
boolean stateLCD_Retro = false; // Used to check if the backlight of the LCD is on or off
//////// Geiger counter ///////
unsigned int bips[MAX_CPM];
//////// Timers ///////
// The followings arrays need to contains NB_OUTPUTS elements
unsigned long timers[] = {530488319, 530488319, 530488319, 530488319, 530488319, 530488319, 530488319, 530488319};
DateTime timersStop[NB_OUTPUTS];
boolean timerActive[] = {false, false, false, false, false, false, false, false};
//////// Loop variables ///////
unsigned long loopNb=0;
unsigned int i; // used inside the for loops
long currentTimePost=0 , currentTimeScreen=0;


//////////////////////////////////
/////////// Functions ////////////
//////////////////////////////////

// Return the current number of seconds since the
// arduino is on (we take the module 65535, because
// we store it as an unsigned int).
unsigned int getTime() {
  return ((millis()/1000)%65535);
}

/////////// Geiger Counter ////////////

// Return the current number of count, all the old ones 
// are deleted.
int getCPM() {
  unsigned int current = getTime();
  int cpm=0;
  
  for(int i=0;i<MAX_CPM;i++) {
   if(abs(current - bips[i]) > 60)
     bips[i]=0;
   else if(bips[i] != 0)
     cpm++;
    
  }
  
  return cpm;
}

// Add a value of count inside the count array (and
// deleted the old ones).
void putCPM() { 
  
  getCPM(); // to remove the old count
  
  for(int i=0;i<MAX_CPM;i++) {
   if(bips[i] == 0) {
     bips[i]=getTime();
     break;
   }
  }
  
}

// Interrupt function (function that is executed
// when an interrpution is launch)
void tube_impulse(){
  //procedure for capturing events from Geiger Kit
  putCPM();
}

/////////// Humidity Sensor ////////////

// Needed function for the DHT11 sensor
void dht11_wrapper() {
  DHT11.isrCallback();
}

/////////// LCD ////////////

// Clear all the lines of the LCD
void clearLCD() {
 for(int i=0;i<=5;i++)
   clearLine(i);
}

// Clear the line i (0-5) of the LCD
void clearLine(int i) {
  lcd.setCursor(0, i);
  lcd.clearLine();
}

/////////// Presure Sensor ////////////

// Return the presure measurement
double getPresure() {
  char status;
  double T,P,p0,a;
  
  // The module need the temperature to be able to compute the presure, so
  // we first compute the temperature
  
  status = pressure.startTemperature();
  if (status != 0)
  {
    // Wait for the measurement to complete:
    delay(status);

    status = pressure.getTemperature(T);
    if (status != 0)
    {     
      // Start a pressure measurement:
      // The parameter is the oversampling setting, from 0 to 3 (highest res, longest wait).
      // If request is successful, the number of ms to wait is returned.
      // If request is unsuccessful, 0 is returned.

      status = pressure.startPressure(3);
      if (status != 0)
      {
        // Wait for the measurement to complete:
        delay(status);

        status = pressure.getPressure(P,T);
        if (status != 0)
        {
          p0 = pressure.sealevel(P,ALTITUDE); // relative (sea-level) pressure
          return p0;
        }
      }
    }
  }
  
  // error
  return 0;
}

/////////// EEPROM ////////////

void i2c_eeprom_write_byte( int deviceaddress, unsigned int addressdata, byte data ) {
  int rdata = data;
  Wire.beginTransmission(deviceaddress);
  Wire.write((int)(addressdata >> 8)); // MSB
  Wire.write((int)(addressdata & 0xFF)); // LSB
  Wire.write(rdata);
  Wire.endTransmission();
}
  
byte i2c_eeprom_read_byte( int deviceaddress, unsigned int addressdata ) {
  byte rdata = 0xFF;
  Wire.beginTransmission(deviceaddress);
  Wire.write((int)(addressdata >> 8)); // MSB
  Wire.write((int)(addressdata & 0xFF)); // LSB
  Wire.endTransmission();
  Wire.requestFrom(deviceaddress,1);
  if (Wire.available()) rdata = Wire.read();
  return rdata;
}

// We save the IR actions into the EEPROM
void writeIRActions() {
  // We save it from 0x0 to 0x14 (2 words for each of the 10 actions)
  
  for(unsigned int i=0;i<NB_IR_BUTTONS;i++) {
    i2c_eeprom_write_byte(ADDRESS_EEPROM,2*i,(irActions[i]>>8) & 0xFF);
    delay(10); //add a small delay
    i2c_eeprom_write_byte(ADDRESS_EEPROM,2*i+1,irActions[i] & 0xFF); 
    delay(10); //add a small delay
  } 
}

// We load the actions from the EEPROM
void loadIRActions() {
  
  for(unsigned int i=0;i<NB_IR_BUTTONS;i++) {
    unsigned long x = i2c_eeprom_read_byte(ADDRESS_EEPROM,2*i) << 8;
    x = x + i2c_eeprom_read_byte(ADDRESS_EEPROM,2*i+1);
    irActions[i]=x;
  } 
  
}

// We save the timers into the EEPROM
void writeTimers() {
  // one timer value is on 32 bits, we have 8 outputs so we need 32*8 = 64 words
  
  int offset = 2*NB_IR_BUTTONS+1;
  
  for(unsigned int i=0;i<NB_OUTPUTS;i++) { // We load a timer = 32 bits = 4 words
    i2c_eeprom_write_byte(ADDRESS_EEPROM,offset+4*i,(timers[i]>>24) & 0xFF);
    delay(10); //add a small delay
    i2c_eeprom_write_byte(ADDRESS_EEPROM,offset+4*i+1,(timers[i]>>16) & 0xFF);
    delay(10); //add a small delay
    i2c_eeprom_write_byte(ADDRESS_EEPROM,offset+4*i+2,(timers[i]>>8) & 0xFF);
    delay(10); //add a small delay
    i2c_eeprom_write_byte(ADDRESS_EEPROM,offset+4*i+3,timers[i] & 0xFF); 
    delay(10); //add a small delay
  }
}

// We load the timers from the EEPROM
void loadTimers() {
  
  int offset = 2*NB_IR_BUTTONS+1;
  
  for(unsigned int i=0;i<NB_OUTPUTS;i++) {
    unsigned long x;
    unsigned long r = i2c_eeprom_read_byte(ADDRESS_EEPROM,offset+4*i);
    x = r << 24;
    r = i2c_eeprom_read_byte(ADDRESS_EEPROM,offset+4*i+1);
    x = x + (r << 16);
    r = i2c_eeprom_read_byte(ADDRESS_EEPROM,offset+4*i+2);
    x = x + (r << 8);
    r = i2c_eeprom_read_byte(ADDRESS_EEPROM,offset+4*i+3);
    x = x + r;
    timers[i]=x;
  } 
  
}

/////////// Debug ////////////

// Function used to print on the serial port
void printlnDebug(String s) {
  if(DEBUG)
    Serial.println(s); 
}

// Function used to print on the serial port
void printDebug(String s) {
  if(DEBUG)
    Serial.print(s); 
}

/////////// Post data to the server ////////////

// Add a sensor value to the data
String addValuePost(String address, String data) {
  printlnDebug("We add : ("+address+","+data+") : " + "data["+String(nbSensors)+"][address]=" + address + "&data["+String(nbSensors)+"][value]="+data+"&");
  nbSensors++;
  // We need to put nbSensors-1 because we increment it juste before !
  return "data["+String(nbSensors-1)+"][address]=" + address + "&data["+String(nbSensors-1)+"][value]="+data+"&";
}

// Send the collected values with addValuePost to the server defined in the constants (serverIP and serverIPString)
void pushDataToServer() {
  
  // we reset the ethernet connection, just in case of a link failure during the loop
  Ethernet.begin(mac, ip, gateway);
  
  nbSensors=0;
  String oneWire = updateOneWire(false);
  String presure = updatePresure(false);
  String humidity = updateHumidity(false);
  String geiger = updateGeigerCounter(false);
  
  if (client.connect(serverIP, 80)) {
    
    printDebug("Data submitted to the webserver : "); 
    
    // Make a HTTP request to submit the sensor' data
    client.print("GET "+page+"&");
    printDebug("GET "+page+"&");
    // We update the sensors, take 1s per OneWire sensor, 1s per humidity sensor, the
    // others are negligible, we don't update the LCD
    client.print(oneWire);
    printDebug(oneWire);
    client.print(presure);
    printDebug(presure);
    client.print(humidity);
    printDebug(humidity);
    client.print(geiger);
    printlnDebug(geiger);
    client.println(" HTTP/1.1");
    client.println("Host: "+serverIPString);
    client.println("Connection: close");
    client.println();
    client.stop();
  } 
  else {
    // if you didn't get a connection to the server:
    printlnDebug("connection failed");
  }
  
}

/////////// IR Remote ////////////

// Test if there is a new action to do received by IR
void applyIRAction() {
  
  if(irrecv.decode(&results)) {
      
      for(i = 0; i< NB_IR_BUTTONS; i++) {
        if(irButtons[i] == results.value) {
          printlnDebug(i+"");
          
          unsigned long toSet = irActions[i];
          // output the value of each analog input pin
          for (int j = PIN_OUTPUT_RELAYS_START; j <= PIN_OUTPUT_RELAYS_STOP; j++) {
          // we extract the value corresponding to this output
            int val = (toSet >> 2*j)%4;
            if( val == 2) //on
              digitalWrite(j,PIN_OUTPUT_RELAYS_ACTIVE);
            else if( val == 3) //off
              digitalWrite(j,PIN_OUTPUT_RELAYS_CLOSE);
          }
          
        }
      }
      irrecv.resume();
      delay(10);
  }
}

/////////// Update sensors ////////////

String updatePresure(boolean updateLCD) {
  
  int presure = round(getPresure()); // we don't need the decimal part
  
  if(presure > 0) {
    
    // LCD
    if(updateLCD) {
      clearLine(LINE_PRESURE);
      lcd.print("Presure ");
      lcd.print(presure);
      lcd.print("mb");
    }
    
    return addValuePost("presure", String(presure));
    
  }
  
  return "";
}

String updateOneWire(boolean updateLCD) {
  ds.reset_search();
  
  String post = "";
  
  while(ds.search(addr)) { // While there are more sensors connected

    if (OneWire::crc8(addr, 7) != addr[7]) {
        printlnDebug("/!\\ Address CRC is not valid ! /!\\ \n");
        continue;
    }
  
    switch (addr[0]) {
      case 0x10: // DS18S20
        type_s = 1;
        break;
      case 0x28: // DS18B20
        type_s = 0;
        break;
      case 0x22: // DS1822
        type_s = 0;
        break;
      default:
        printlnDebug("Device is not a DS18x20 family device.");
        continue;
    } 
  
    ds.reset();
    ds.select(addr);
    ds.write(0x44,1); // we start the conversion of the temperature
  
    delay(1000);
    ds.reset();
    ds.select(addr);    
    ds.write(0xBE); // we ask the temperature
  
    for (i = 0; i < 9; i++) // we read 9 bytes for the temperature
      data[i] = ds.read();
      
    if (OneWire::crc8(data, 8) != data[8]) {
      printlnDebug("/!\\ Data CRC is not valid ! /!\\ \n");
      continue;
    }
       
    int16_t raw = (data[1] << 8) | data[0];
    if (type_s) {
      raw = raw << 3; // 9 bit resolution default
      if (data[7] == 0x10) {
        // "count remain" gives full 12 bit resolution
        raw = (raw & 0xFFF0) + 12 - data[6];
      }
    } else {
      byte cfg = (data[4] & 0x60);
      // at lower res, the low bits are undefined, so let's zero them
      if (cfg == 0x00) raw = raw & ~7;  // 9 bit resolution, 93.75 ms
      else if (cfg == 0x20) raw = raw & ~3; // 10 bit res, 187.5 ms
      else if (cfg == 0x40) raw = raw & ~1; // 11 bit res, 375 ms
      // default is 12 bit resolution, 750 ms conversion time
    }

    T = (float)raw/16.0*100; // we convert the received data in tenth of degree C
    whole = abs(T) / 100;
    fract = abs(T) % 100; // we extract fractional part
    
    // We construct the strings with the result
    String addressString = "", tempString = "";
    
    for(i = 0; i < 8; i++) {
      printDebug(String(addr[i],HEX));
      printDebug(" ");
      addressString = addressString + String(addr[i],HEX);
    }
      
    if(T < 0)
      tempString = "-"+String(whole)+"."+String(fract);
    else
      tempString = String(whole)+"."+String(fract);
      
   printlnDebug(tempString);
      
   if(updateLCD && addressString == addressSensorLine1) {
     clearLine(LINE_TEMP1);
     lcd.print(textLine1+tempString); lcd.write(1); lcd.print("C");
   } else if(updateLCD && addressString == addressSensorLine2) {
     clearLine(LINE_TEMP2);
     lcd.print(textLine2+tempString); lcd.write(1); lcd.print("C");
   }
    
    post = post + addValuePost(addressString, tempString);
  }
  
  return post;
}

String updateHumidity(boolean updateLCD) {
  
  int state = DHT11.acquireAndWait();
  
  if(state == IDDHTLIB_OK) {
    
    int humidity = int(DHT11.getHumidity());
    
    // LCD
    if(updateLCD) {
      clearLine(LINE_HUMIDITY);
      lcd.print("Humidity ");
      lcd.print(humidity);
      lcd.print("%");
    }
    
    return addValuePost("humidity", String(humidity));
    
  }
  
  return "";
}

String updateGeigerCounter(boolean updateLCD) {
  int Count = getCPM();
  
  // LCD
  if(updateLCD) {
    clearLine(LINE_RADIATION);
    lcd.print(Count/100.0, 3);
    lcd.print(" ");
    lcd.write(0);
    lcd.print("s/h");
  }
  
  if(Count != 0)
    return addValuePost("geiger", String(Count));
  else
    return "";
}

/////////// Timers ////////////

// Check the timer of the ith output
void checkTimer(int i, DateTime now) {
  
  int dayNumber = 1<<getDay(now);

  // We decode the data of the timer
  int decoded[6];
  decoded[0] = timers[i]%2; // reverse
  unsigned long remainder = timers[i]/2;
  decoded[1] = remainder%128; // days to activate
  remainder = remainder/128;
  decoded[2] = (remainder%1440)%60; // duration minutes
  decoded[3] = (remainder%1440)/60; // duration hours
  remainder = remainder/1440;
  decoded[4] = (remainder%1440)%60; // alarm minutes
  decoded[5] = (remainder%1440)/60; // alarm hours
  
  // First if the output has been activated by a timer then timerActive[i] will be true
  // and timersStop[i] will contains the time at which we need to deactivate the output
  if(timerActive[i]) {
   // The output has been activated by an timer
   
   // We check if it's the time to deactivate the output
   if(  timersStop[i].day() == now.day() &&
        timersStop[i].hour() == now.hour() &&
        timersStop[i].minute() == now.minute()) {
     
     timerActive[i] = false;
     
     printlnDebug("Timer disactivated");
     
     if(decoded[0] == 0)
      digitalWrite(PIN_OUTPUT_RELAYS_STOP-i,PIN_OUTPUT_RELAYS_CLOSE);
     else
      digitalWrite(PIN_OUTPUT_RELAYS_STOP-i,PIN_OUTPUT_RELAYS_ACTIVE);
          
    }
   
   return; 
  }
  
  // if we are here, then the timer is not yet actived
  
  // the bit number i is 1 if the day is selected
  if((dayNumber & decoded[1]) == 0x00) // not the good day
    return;
    
  if(now.hour() != decoded[5]) // not the good hour
    return;
        
  if(now.minute() != decoded[4]) // not the good minute
    return;
    
  printlnDebug("Timer activated");
      
  // We are here when the hour is the good one !
  // First we set the hour at which we will to disable the output
  timersStop[i] = DateTime(now.unixtime() + decoded[3]*60*60 + decoded[2]*60);
  timerActive[i] = true;
    
  // We activate the output
  if(decoded[0] == 1)
    digitalWrite(PIN_OUTPUT_RELAYS_STOP-i,PIN_OUTPUT_RELAYS_CLOSE);
  else
    digitalWrite(PIN_OUTPUT_RELAYS_STOP-i,PIN_OUTPUT_RELAYS_ACTIVE);
}

// Check the timer of all the outputs
void checkTimerAll(DateTime now) {
  
  for(int i=0;i<NB_OUTPUTS;i++)
    checkTimer(i,now);
}


// Return the number of the day with the following convention
// Moday = 0, ... , Sunday = 6
int getDay(DateTime now) {
 
  unsigned long dayNow = now.unixtime() / 86400L; // we get the number of days since 1970
  dayNow = (dayNow - DAY_REFERENCE)%7; // We substract the reference day and we take the modulo 7 of
  // the result to remove the weeks between the two date
  dayNow = (dayNow + DAY_REFERENCE_NUMBER)%7; // we add the day number of the reference to remove the offset
  
  return dayNow;
}

/////////// Webserver ////////////

void webserver() {
  
  EthernetClient clientServer = server.available();
  
  if (clientServer) {
    printlnDebug("new client");
    // an http request ends with a blank line
    boolean currentLineIsBlank = true;
    GETData = "";
    
    while (clientServer.connected()) {
      if (clientServer.available()) {
        char c = clientServer.read();

        // if you've gotten to the end of the line (received a newline
        // character) and the line is blank, the http request has ended,
        // so you can send a reply
       
        if(reading && c == ' ') reading = false;
        if(c == '?') reading = true; //found the ?, begin reading the info

        if(reading) {
          
          if (c!='?')
            GETData += c;
        }
        
        
        if (c == '\n' && currentLineIsBlank) {
          // send a standard http response header
          clientServer.println("HTTP/1.1 200 OK");
          clientServer.println("Content-Type: text/html");
          clientServer.println("Connection: close");  // the connection will be closed after completion of the response
          clientServer.println();
          
          if(arrayResults[0] == 0) // getOutputs
          {
            unsigned long sensorReading = 0;         
            // output the value of each analog input pin
            for (i = PIN_OUTPUT_RELAYS_START; i <= PIN_OUTPUT_RELAYS_STOP; i++) { 
              if(digitalRead(i) == PIN_OUTPUT_RELAYS_ACTIVE) sensorReading = (sensorReading << 2) + 2;
              else sensorReading = (sensorReading << 2) + 3;
  
            }
            clientServer.print(sensorReading);
          } else if(arrayResults[0] == 1) // setOutputs
          {
            unsigned long toSet = arrayResults[1];
            // output the value of each analog input pin
            for (i = 0; i < NB_OUTPUTS; i++) {
              // we extract the value corresponding to this output
              int val = (toSet >> 2*i)%4;
              if( val == 2) //on
                digitalWrite(PIN_OUTPUT_RELAYS_START+i,PIN_OUTPUT_RELAYS_ACTIVE);
              else if( val == 3) //off
                digitalWrite(PIN_OUTPUT_RELAYS_START+i,PIN_OUTPUT_RELAYS_CLOSE);
            }
            
            // we send the answer to the server
            clientServer.print("1");

          } else if(arrayResults[0] == 2) // getRemote
          {
            // if we ask an existing button
            if(arrayResults[1] >= 0 && arrayResults[1] < NB_IR_BUTTONS)
              clientServer.print(irActions[arrayResults[1]]);
          } else if(arrayResults[0] == 3) // setRemote
          {
            // if we ask an existing button
            if(arrayResults[1] >= 0 && arrayResults[1] < NB_IR_BUTTONS)
              irActions[arrayResults[1]] = arrayResults[2];
              
            // We save in the EEPROM the data
            writeIRActions();
              
            clientServer.print("1");
          } else if(arrayResults[0] == 4) // get alarms
          {
            // we send the answer to the server
            clientServer.print(timers[arrayResults[1]]);
          } else if(arrayResults[0] == 5) // set alarms
          { 
            if(arrayResults[1] >= 0 && arrayResults[1] < NB_OUTPUTS)
              timers[arrayResults[1]] = arrayResults[2];
            
            // We save in the EEPROM the data
            writeTimers();
            // we send the answer to the server
            clientServer.print("1");
          } else if(arrayResults[0] == 7) // ping
          {
            // we send the answer to the server
            clientServer.print("1");
          }
          break;
        }
        if (c == '\n') {
          // you're starting a new line
          currentLineIsBlank = true;
          // the line is finished so we can parse the get data
          if(GETData != ""){
            printlnDebug(GETData);
            parseGET(GETData);
            GETData = "";

          }

        } 
        else if (c != '\r') {  
          // you've gotten a character on the current line
          currentLineIsBlank = false;
        }
      }
    }
  
  
  }
  // we give some time to the client before closing the connection
  delay(1);
  clientServer.stop();
}

// Parse the GET request sent by the server and save
// the data parsed inside the arrayResult array.
void parseGET(String str) {
  // the request are always like the model :
  // /?t=typeNumber&a=data1&b=data2&e
  
  // we search the first number : the type of request
  int startIndex = str.indexOf("t");
  int endIndex = str.indexOf("a");
  String typeStr = str.substring(startIndex + 2, endIndex - 1);
  char typeChar[4];
  typeStr.toCharArray(typeChar, sizeof(typeChar));
  arrayResults[0] = atoi(typeChar);
  
  // getOutput or ping = don't need to parse the rest of the GET
  if(arrayResults[0] == 0 || arrayResults[0] == 7) {
    return;
  }
  
  
  startIndex = str.indexOf("a");
  endIndex = str.indexOf("b");
  String aStr = str.substring(startIndex + 2, endIndex -1);
  char tempA[7];
  aStr.toCharArray(tempA, sizeof(tempA));
  arrayResults[1] = atol(tempA);
  
  // setOutput or getRemote = don't need to parse the rest of the GET
  if(arrayResults[0] == 1 || arrayResults[0] == 2) {
    return;
  }  
  
  startIndex = str.indexOf("b");
  endIndex = str.indexOf("e");
  String bStr = str.substring(startIndex + 2, endIndex -1);
  char tempB[18];
  bStr.toCharArray(tempB, sizeof(tempB));
  arrayResults[2] = atol(tempB);
}

/////////////////////////////////////
/////////// Arduino Loop ////////////
/////////////////////////////////////


void setup(void) {

  if(DEBUG) // start serial port
    Serial.begin(9600);
  
  // We init all the buses
  Ethernet.begin(mac, ip, gateway);
  irrecv.enableIRIn();
  Wire.begin();
  rtc.begin();
  pressure.begin();
  
  // We activate the output relays, by default we deactivate all the relays
  for(int i = PIN_OUTPUT_RELAYS_START; i <= PIN_OUTPUT_RELAYS_STOP; i++) {
    pinMode(i,OUTPUT);
    digitalWrite(i,PIN_OUTPUT_RELAYS_CLOSE);
  }
  
  // We init the array of geiger counter
  memset(bips,0,sizeof(bips));
  
  lcd.begin(84, 48);  
  // Add the perso characters to the beggining of the ASCII table
  lcd.createChar(0, micro);
  lcd.createChar(1, temp);
  
  // Init display
  lcd.setCursor(0, 1);
  lcd.print("domoDuino v1.0");
  lcd.setCursor(0,3);
  lcd.print("   Valentin  ");
  lcd.setCursor(0,4);
  lcd.print("   BONNEAUD  ");
  delay(2000);
  clearLCD();
  // End init display
  
  // We load the saved data from the EEPROM
  loadIRActions();
  loadTimers();
  
  // Geiger counter
  pinMode(PIN_GEIGER, INPUT); // set pin INT0 input for capturing GM Tube events
  digitalWrite(PIN_GEIGER, HIGH); // turn on internal pullup resistors, solder C-INT on the PCB
  attachInterrupt(PIN_INTER_GEIGER, tube_impulse, FALLING); //define external interrupts
  
  // Button to change the screen
  pinMode(PIN_BUTTON_SCREEN, INPUT); // set pin INT0 input for capturing GM Tube events
  digitalWrite(PIN_BUTTON_SCREEN, HIGH); // turn on internal pullup resistors, solder C-INT on the PCB
  
  // Button to activate the backlight of the screen
  pinMode(PIN_BUTTON_LCD_BACKLIGHT, INPUT); // set pin INT0 input for capturing GM Tube events
  digitalWrite(PIN_BUTTON_LCD_BACKLIGHT, HIGH); // turn on internal pullup resistors, solder C-INT on the PCB
  pinMode(PIN_BACKLIGHT, OUTPUT);

}

void loop(void) {
  
  // We check if the button is pressed
  if(digitalRead(PIN_BUTTON_SCREEN) == LOW) {
    currentTimeScreen=0; // we force the update of the screen
    screenToDisplay = (screenToDisplay+1)%3;
    printDebug("Screen : ");
    printlnDebug(String(screenToDisplay));
  }
  
  // We check if the button is pressed
  if(digitalRead(PIN_BUTTON_LCD_BACKLIGHT) == LOW) {
    
    if(!stateLCD_Retro) {
      // we activate the backlight
      printlnDebug("We activate the backlight");
      stateLCD_Retro = true;
      digitalWrite(PIN_BACKLIGHT, HIGH);
    } else {
      // We deactivate the backlight
      printlnDebug("We deactivate the backlight");
      stateLCD_Retro = false;
      digitalWrite(PIN_BACKLIGHT, LOW);
    }
    delay(500); // we dalay to avoid the rebound effect
  }
  
  // We get the current time for the RTC
  DateTime now = rtc.now();
  
  // Each NB_SEC_POST seconds we post the data to the server
  if(now.secondstime() - currentTimePost > NB_SEC_POST) {
    currentTimePost = now.secondstime(); // we saved the last time that we post the data
    
    // We send the data to the server
    pushDataToServer();

  }
  
  // Each NB_SEC_SCREEN seconds we refresh the screen
  if(now.secondstime() - currentTimeScreen > NB_SEC_SCREEN) {
    currentTimeScreen = now.secondstime();
    
    // Screen to display is the summary of the measurements
    if(screenToDisplay == 0) {
      
      clearLCD();
      // We update the sensors, take 1s per OneWire sensor, 1s per humidity sensor, the
      // others are negligible
      updateOneWire(true);
      updatePresure(true);
      updateHumidity(true);
      updateGeigerCounter(true);
    } else if (screenToDisplay == 1) { // the screen to display is the summary of the outputs
      
      clearLCD();
      
      for(int i=0;i<NB_OUTPUTS;i++) {
        if(i%2==0) clearLine(i/2);
        lcd.print("Out"); lcd.print(i); lcd.print(" "); 
        if(digitalRead(PIN_OUTPUT_RELAYS_STOP-i) == PIN_OUTPUT_RELAYS_CLOSE)
          lcd.print("C ");
        else
          lcd.print("O ");
      }
    } else if (screenToDisplay == 2) { // display the network info
      
      clearLCD();
      clearLine(0); 
      lcd.print("Network info");
      clearLine(2);
      lcd.print(Ethernet.localIP());
 
      if (client.connect(serverIP, 80)) {
        client.stop();
        clearLine(3);
        lcd.print("UP");
      } else {
        clearLine(3);
        lcd.print("DOWN");
      }
     
    }
  }
  
  // We check if there is a new incomming connection
  webserver();
  // We check if there is a new IR command
  applyIRAction();
  // We check if there is an active timer
  checkTimerAll(now);
  
  delay(1);
}
