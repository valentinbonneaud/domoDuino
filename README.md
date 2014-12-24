domoArduino
===========

Personal domotic &amp; weather station project with an Arduino UNO.

This is a fully functional home domotic project with 8 programables outputs (relays), controlable with an IR remote.

There is also a weather station functionality with temperature, humidity sensors and a Geiger counter.

The domotic part can be configured with a webpage (on the Arduino).

Domotic
---------

Domotic Functionalities :

- Visualize the state of all outputs,
- Enable or disable any output,
- Set a timer (fixed hour for on/off or count-down) to an output (in futures versions),
- Configure the actions of the IR remote.

All these actions can be done through the web page of the server and not directly to the Arduino because of the limited ressources of the Arduino. However, a small webserver is running on the Arduino, but only for synchronization purposes (synchronization between the server and the arduino of the output states, ...).

Weather Station
-------------

The weather station is contitued of 

- DS18S20 (1-Wire) temperature sensors,
- DHT11 humidity (and temperature) sensors,
- SBM-20 Geiger tube with detector board.

All the sensors' informations are sent to a webserver with PHP and MySQL for handling the data. This server is accessible via a web browser to diplay HTML5 graph of the measurements (over differents time axis).

Schema
-------------

Will be uploaded soon !

An RTC (DS1337) is connnected via I2C to the Arduino, like that even without Internet connection, the arduino can perform the time related actions (timer on outputs).


Structure of the repro
----------------------

The repro is splitted into two parts, the first one is all the code concerning the Arduino board, the second is for the remote server.

History
------------

Last version : v0.2 - Still under development

v0.2 :

- Server side : Website done except the sensors page (plots)
- Server side : Databases done

v0.1 :

- Support of the DS18S20 sensors
- Support of the RTC


License
-------------

All this project is distributed under the MIT License. Have fun with this project ! If you want to modify it, do it !

The MIT License (MIT)

Copyright (c) [2014] [Valentin BONNEAUD]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
