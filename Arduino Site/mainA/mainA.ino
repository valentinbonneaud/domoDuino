#include <OneWire.h>
#include <SPI.h>
#include <Ethernet.h>
#include <IRremote.h>

//////////////////////////
//  Configuration Part  //
//////////////////////////

// Constants
// you can change this MAC address if there is a other device with the same address (very unlikely !)
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };

// Server to post the data
IPAddress serverIP(0,0,0,0);
String serverIPString = "0.0.0.0";
// put the location of the submission part on your server with the api key
String page = "/arduino/submit.php?apikey=API_KEY_HERE";

// IP configuration
IPAddress ip(192,168,1,177);
IPAddress gateway(192,168,1,1);
int PORT_SERVER = 80;

// IR remote
int NB_IR_BUTTONS = 10;
// this array are the buttons code sent by the remote, you have to modify it with respect of you remote
// you can use the same program in the repro to find the codes of your remote
// small ir remote
//long irButtons[] = {16738455, 16724175, 16718055, 16743045, 16716015, 16726215, 16734885, 16728765, 16730805, 16732845};
// denon rc-1175
unsigned long irButtons[] = {1373301766, 1758654652, 435002292, 1749611822, 1218427694, 3076422308, 1752769948, 1344315987, 3428191179, 2422781229};
// the value 21845 is doing nothing to all outputs, 65535 is setting all to off
long irActions[] = {21845, 21845, 21845, 21845, 21845, 21845, 21845, 21845, 21845, 65535};

// To activate the Serial.print calls
// /!\ WARNING : the Serial port use the output 0 and 1, so there states can be unperdictable
boolean DEBUG = false;

// Ports

// Ports 10,11,12,13 are reserved for the Ethernet Shield

int PIN_ONE_WIRE = A5;
int PIN_IR = 8;
int PIN_OUTPUT_RELAYS_START = 0;
int PIN_OUTPUT_RELAYS_STOP = 7;
int PIN_GEIGER = A0;
int PIN_OUTPUT_RELAYS_CLOSE = HIGH; // close logical state (if you use active low relay, put HIGH)
int PIN_OUTPUT_RELAYS_ACTIVE = LOW; // active state of the relays (contrary of PIN_OUTPUT_RELAYS_CLOSE)
int MAX_LOOP = 55900;

//////////////////////////
//     Program Part     //
//////////////////////////

// Variables

EthernetClient client;
EthernetServer server(PORT_SERVER);
String post = "";
OneWire ds(PIN_ONE_WIRE);
IRrecv irrecv(PIN_IR);
decode_results results;
int T, whole, fract;
unsigned int i;
unsigned int nbSensors = 0;
unsigned int loopNb=0;
byte data[12];
byte addr[8];
byte type_s;
boolean searchSensors = false; 
boolean OneWireDone = false;
boolean reading = false;
String GETData;
unsigned long arrayResults[3];


void setup(void) {

  if(DEBUG) // start serial port
    Serial.begin(9600);
  
  Ethernet.begin(mac, ip, gateway);
  irrecv.enableIRIn();
  
  // We activate the output relays, by default we deactivate all the relays
  for(int i = PIN_OUTPUT_RELAYS_START; i <= PIN_OUTPUT_RELAYS_STOP; i++) {
    pinMode(i,OUTPUT);
    digitalWrite(i,PIN_OUTPUT_RELAYS_CLOSE);
  }
}


void loop(void) {
  
  // The arduino allocate the tasks using the time, in
  // the first 
  
  // 3 temperatures sensors + submit = 0-900 : 5s
  // Need one submit each 15min or 900s
  // At each iteration of the loop there is a delay of 1ms
  // So to wait 900-5 = 895s, we need 895000 loop cycles so
  // we need to reset the counter at 895900

  // Time division :
  // 0-100 : reading the OneWire sensors (1 iteration per sensor, 
  // so if you have more than 100 sensors, you need to increase this range).
  // 100-200 : IR Remote
  // 200-300 : Handling web page requests
  // 300-400 : Reading of the I2C sensors
  // 400-500 : Reading of the humidity sensor
  // 900 : submit the measures to the server (need only one iteration)
  //
  // After the reserved slots, the IR Remote, the web server are running constantly. You can see more details on the github readme
  
  loopNb++; // we have to increment this value otherwise, 
  // the loop function will be stuck here (return finish the loop function
  // without the final incrementation)
    
  if(loopNb == 1) { digitalWrite(4,LOW); }
  if(loopNb == 900) { digitalWrite(4,HIGH); }
  
  // OneWire Sensors
  if(loopNb >= 0 && loopNb < 100)
  {
    
    if (OneWireDone || !ds.search(addr)) { // No more sensors connected, we don't reset the 
    // search here otherwise we will read multiple times the temperature inside one
    // cycle, so we will reset the search at the end of the cycle when loopNb == MAX_LOOP
        OneWireDone = true;
        return;
    }
  
    if (OneWire::crc8(addr, 7) != addr[7]) {
        printlnDebug("/!\\ Address CRC is not valid ! /!\\ \n");
        return;
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
        return;
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
      return;
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
    
    // We print the results 
    
    post = post + "data["+nbSensors+"][address]=";
    
    for(i = 0; i < 8; i++) {
      printDebug(String(addr[i],HEX));
      printDebug(" ");
      post = post + String(addr[i],HEX);
    }
    
    printlnDebug("");
      
    if(T < 0)
      post = post + "&data["+nbSensors+"][value]=-"+whole+"."+fract+"&";
    else
      post = post + "&data["+nbSensors+"][value]="+whole+"."+fract+"&"; 
    
    nbSensors++;
  }
  
  // IR Remote
  if((loopNb >= 100 && loopNb < 200) || loopNb > 900) {
   
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
  
  // Web server
  if((loopNb >= 200 && loopNb < 300) || loopNb > 900) {
    
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
              for (i = PIN_OUTPUT_RELAYS_START; i <= PIN_OUTPUT_RELAYS_STOP; i++) {
                // we extract the value corresponding to this output
                int val = (toSet >> 2*i)%4;
                if( val == 2) //on
                  digitalWrite(i,PIN_OUTPUT_RELAYS_ACTIVE);
                else if( val == 3) //off
                  digitalWrite(i,PIN_OUTPUT_RELAYS_CLOSE);
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
  
  // I2C Sensors
  if(loopNb >= 300 && loopNb < 400)
  {
    // TODO when I will have the sensors !
  }
  
  
  // DHT11 sensor
  if(loopNb >= 400 && loopNb < 500)
  {
    // TODO when I will have the sensor !  
  }
  
  
  // Submission of the data to the server
  if(loopNb == 900) {
   if (client.connect(serverIP, 80)) {
      // Make a HTTP request to submit the sensor' data
      client.print("GET "+page+"&");
      client.print(post);
      client.println(" HTTP/1.1");
      client.println("Host: "+serverIPString);
      client.println("Connection: close");
      client.println();
      client.stop();
      printDebug("Data submitted to the webserver : "); 
      printlnDebug(post);
    } 
    else {
      // if you didn't get a connection to the server:
      printlnDebug("connection failed");
    }
    // we reset the data to post
    post = "";
  }
  
  
  // if we are at the end of the cycle
  if(loopNb == MAX_LOOP) {
    printlnDebug("----------------------------");
    loopNb=0;
    // we reset the OneWire search to read again the value of the sensors
    ds.reset_search();
    OneWireDone = false;
    nbSensors = 0;
  }
  
  delay(1);
}


void printlnDebug(String s) {
  if(DEBUG)
    Serial.println(s); 
}

void printDebug(String s) {
  if(DEBUG)
    Serial.print(s); 
}

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
  char tempB[7];
  bStr.toCharArray(tempB, sizeof(tempB));
  arrayResults[2] = atol(tempB);
}
