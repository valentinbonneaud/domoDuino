#include <IRremote.h>

int PIN_IR = 8;
IRrecv irrecv(PIN_IR);
decode_results results;

void setup(void) {
  Serial.begin(9600);
  irrecv.enableIRIn();
}

// To read the code corresponding at the pressed button you just
// have to read the value in the serial monitor ! You can ignore
// the value 4294967295, it's a separation value.

void loop(void) {
  
  if(irrecv.decode(&results)) {
    Serial.println(results.value);
    irrecv.resume();
    delay(10);
  }
    
}
