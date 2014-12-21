#include <OneWire.h>
OneWire ds(10);  // on pin 10

void setup(void) {
  // initialize inputs/outputs
  // start serial port
  Serial.begin(9600);
}

void loop(void) {
  byte i;
  byte data[12];
  byte addr[8];

  ds.reset_search();
  if ( !ds.search(addr)) {
      Serial.print("No more addresses.\n");
      ds.reset_search();
      return;
  }

  Serial.print("New device : ");

  if ( addr[0] == 0x10)
      Serial.print("DS18S20");
  else if ( addr[0] == 0x28)
      Serial.print("DS18B20");
  else {
      Serial.print("not recognized: 0x");
      Serial.println(addr[0],HEX);
      Serial.print(" with the address : ");
      for( i = 0; i < 8; i++) {
        Serial.print(addr[i], HEX);
        Serial.print(" ");
      }
      return;
  }
  
  Serial.print(" with the address : ");
  for( i = 0; i < 8; i++) {
    Serial.print(addr[i], HEX);
    Serial.print(" ");
  }
  
  Serial.println();
  
  if (OneWire::crc8(addr, 7) != addr[7]) {
      Serial.print("/!\\ Address CRC is not valid ! /!\\ \n");
      return;
  }

  ds.reset();
  ds.select(addr);
  ds.write(0x44,1); // we start the conversion of the temperature

  delay(1000);
  ds.reset();
  ds.select(addr);    
  ds.write(0xBE); // we ask the temperature

  for ( i = 0; i < 9; i++) { // we read 9 bytes
    data[i] = ds.read();
  }
  
  // we don't compute the temperature, we just check that the CRC is ok
  // to check if the sensor is correctly working
  if ( OneWire::crc8(data, 8) != data[8]) {
      Serial.print("/!\\ Data CRC is not valid ! /!\\ \n");
      return;
  }

}
