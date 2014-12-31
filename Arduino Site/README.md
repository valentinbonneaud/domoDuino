domoDuino
===============

Arduino Side
-------------

In this part we will talk about the program running on the arduino and all the connections between the arduino, sensors and other devices.

Installation
--------------

To install this part, you need to open the main/mainArduino.ino file inside the arduido IDE, and modify the configuration part of the code (IP configuration, server settings, remote type and codes, ports used). There are more details inside the code.

General
--------------

Ethernet Shield
--------------

To submit the sensors measurements, obtain the actions to do from the website, etc, an ethernet shield is needed. It's a board to plug on top of the arduino, and this card add an ethernet port and a SD card reader (not used in this project) to the arduino. This shield is very easily controlled, we just have to initialize inside the setup function and here we go ! After the collection of all the sensor's data, the internet connection is used to send the measurements to the server through a webpage (using the api key as authentication). This is the client part which is used only after the sensor's data collection. 

There is also a server part which is used for the domotic part. Indeed, when the website on the server need to send or get data (on/off on a output, configuration data, ...) to the arduino, the server will send a GET request directly to the arduino. This imply to run constantly the server on the arduino which is impossible because we do not have a multi thread processor ! That's why I have used the time division. The server is not responding to requests only during sensors data collection, this is around 5-10s/cycle (it depend obviously on the number of sensors). Thus during this collection, the arduino is not answering to requests, the ethernet keep all the requests inside a stack and send it to the arduino after the collection, so to minimize the waiting time of the server (and thus the loading time of the user webpage), the server is also running between the OneWire and I2C collection.

Conventions
------------

I will define here some formats that are used in this project

First when we send the state of the relays or when we receive commands for the relays (or for the configuration of the IR actions), I used the following format :

Output nb : 7  6  5  4  3  2  1  0
            -- -- -- -- -- -- -- --
each -- is two bits and correspond to one output,
0b01 = do nothing
0b10 = on
0b11 = off

So for example, if the server when to know the state of the outputs, the arduino will send the number 0b1111 1011 1111 1111 = 64511 if all the outputs are off except the 5th. Or if the server want to power on the 4th output (and do nothing on the others), this number will be sent to the arduino : 0b0101 0110 0101 0101 = 22101. The two majors benefit from this pattern is that it's easy to encode and decode (shift and modulo) and the generated number is always constituted of 5 digits which simplify a lot the parsing in the arduino software.

Secondly, the format used at the arduino server is the following :

http://ipOfArduino:port/?t=typeNumber&a=data1&b=data2&e

where typeNumber is : 

- 0 : get the outputs values (data1 and data2 are ignored), the html page returned will contain a number (as computed previously),
- 1 : set an output value, data1 is the new output value computed as previously (data2 is ignored),
- 2 : get the action of a given button on the IR remote, data1 is used to send the button number (data2 is ignored),
- 3 : set the action of a given button on the IR remote, data1 is used to send the button number while data2 is used to send the new action,
- 4 : [in development] will be used for getting the timer data of an output,
- 5 : [in development] will be used for setting the timer data of an output,
- 6 : [not used]
- 7 : use ping the arduino (data1 and data2 are ignored), used in the user setting part of the website


Sensors
-------------

For the weather station part, I use several components :

- DS18S20 OneWire sensors, I bought the one inside a metal tube because they are waterproof and you have a good thermal inertia. It's usefull if you want to trigger things with the temperature measure (if you measure the temperature over a very short period of time you can have some jumps, for example if a raindrop hit the sensor), with this type of sensors you will obtain a lovely exponential curve which prevent rapid switching ! This kind of sensors are also very chip : less than one euro / piece on alibaba (more expensive on ebay ...),
- DHT11 humidity sensor, cheap but the communication is not standard (but there is an arduino library !),
- BMP085 I2C sensor, this sensor is used for measuring the barometric pressure. I choose this sensor because it's cheap (1-2 euros/piece), use I2C to communicate, have high precision (0.03hPa) and a super low consumption (3uA),
- BOI-33 Geiger tube with a detector board which send an interruption to the arduino at each detection

Thus there are one port used for OneWire (temperature sensors), one for the humidty sensor, two for the I2C bus (pressure sensor and RTC), one for the Geiger counter, so in total we use 5 ports for the sensors.

IR Remote
------------

I added a very cheap IR Remote (3 euro on ebay) to control the domotic part of the project easily. There is an array for the actions to do when a given button is pressed, the default values are set in the constants definitions but the user can set them through the website. The convention for the value is the same as previously, the default value is 21845 (do nothing to all outputs). To find the value sent by the remote when a key is pressed, you can use the arduino program findIRCode of the repository.

Note: you can use any remote as long as you can obtain the code for each button that you want to use. In my case I use a denon remote (because I don't use some buttons on it), it's much powerful (so the reception at the receiver is better) than the small remote provided with the receiver.

Real Time Clock
------------

[Still in development ...]

Outputs
----------

The outputs are controlled with a 8-relays boards (5-6 euros on ebay). This is absolutly neeeded to control 220V equipment. This board insures electrical isolation (with relays and opto-isolator) and facilitates the controls of outputs (we can drive the relays directly with the arduino, we don't need to add a transistor and a diode, everything is on the board).

ZigBee
---------

In the future I will probably add a ZigBee module too remote all the cables of the sensors, add sensors in others rooms, add some "remote" outputs to control other stuff ...

Schema
-----------

Will be uploaded soon !
