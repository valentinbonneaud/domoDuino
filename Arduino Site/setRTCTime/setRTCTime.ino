// This program set the RTC with the given time and print the current time using the serial port

#include <Wire.h>
#include "RTClib.h"

// You need to modify the following lines :
// For example if you want to set the RTC to Febuary 5th, 2015 at 7:44pm
// you have to define :
#define YEAR 2015
#define MONTH 2
#define DAY 5
#define HOUR 19
#define MINUTE 44
#define SECOND 0

RTC_DS1307 rtc;

void setup () {
  Serial.begin(9600);
  Wire.begin();
  rtc.begin();
  rtc.adjust(DateTime(YEAR, MONTH, DAY, HOUR, MINUTE, SECOND));
}

void loop () {
    DateTime now = rtc.now();
    
    Serial.print(now.year(), DEC);
    Serial.print('/');
    Serial.print(now.month(), DEC);
    Serial.print('/');
    Serial.print(now.day(), DEC);
    Serial.print(' ');
    Serial.print(now.hour(), DEC);
    Serial.print(':');
    Serial.print(now.minute(), DEC);
    Serial.print(':');
    Serial.print(now.second(), DEC);
    Serial.println();
    delay(3000);
}
