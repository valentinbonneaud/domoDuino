#include <OneWire.h>

OneWire ds(10);  // on pin 10

void setup(void) {
  // initialize inputs/outputs
  // start serial port
  Serial.begin(9600);
}

unsigned int i;
boolean searchSensors = false; 
unsigned int loopNb=0;
byte data[12];
byte addr[8];
int TReading, Tc_10, whole, fract;

void loop(void) {
  
  // Time division :
  // 800 : reading the temperature sensors
  // 900 : submit the measures to the server
  
  if(loopNb == 800 || searchSensors)
  // We read the temperature sensors
  {
    // we set this variable to true, to be able to ask to multiple sensors
    // otherwise only one sensors will be activated.
    searchSensors = true;
    
    if (!ds.search(addr)) { // No more sensors connected, restart the search
        ds.reset_search();
        searchSensors = false;
        return;
    }
  
    if (OneWire::crc8(addr, 7) != addr[7]) {
        Serial.print("/!\\ Address CRC is not valid ! /!\\ \n");
        return;
    }
  
    if ( addr[0] != 0x10) {
        Serial.print("/!\\ Device is not a DS18S20 family device. /!\\ \n");
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
      Serial.print("/!\\ Data CRC is not valid ! /!\\ \n");
      return;
    }
    
    TReading = (data[1] << 8) + data[0];
    
    if (TReading & 0x8000) // if the result is negative, we take the 2's complement
      TReading = (TReading ^ 0xffff) + 1;
      
    Tc_10 = TReading*10/2; // we convert the received data in tenth of degree C
    whole = Tc_10 / 10;
    fract = Tc_10 % 10; // we extract fractional part
    
    // We print the results 
    
    for(i = 0; i < 8; i++) {
      Serial.print(addr[i], HEX);
      Serial.print(" ");
    }
  
    if (TReading & 0x8000)// if its negative
       Serial.print("-");
    
    Serial.print(whole);
    Serial.print(".");
    Serial.print(fract);
    Serial.println();
  }
  
  if(loopNb == 900) {
   // We submit the measures to the server
   
  }
  
  // We increase the number of loop
  loopNb++;
  if(loopNb == 1000) {
    Serial.println("----------------------------");
    loopNb=0;
  }
  delay(1);
}
