Domoduino
===========

This is a personal domotic &amp; weather station project with an Arduino UNO.

The domotic part is constitued of 8 programables outputs (relays) controllable with an IR remote and through a webserver (on/off buttons and clock automaton).

There is also a weather station part which measure various informations such as temperature, humidity and radiation (beta and gamma). All the data is sent to a remote server for storage and visualization. There is also a small screen connected directly to the system, to display a short summary of the measurements.

Domotic
---------

Domotic Functionalities :

- Visualize the state of all outputs,
- Enable or disable any output,
- Set a timer (fixed hour for on/off or count-down) to an output (in futures versions),
- Configure the actions of the IR remote.

All these actions can be done through the web page of the server and not directly to the Arduino because of the limited ressources of the Arduino. However, a small webserver is running on the Arduino, but only for synchronization purposes (synchronization between the server and the arduino of the output states, IR remote actions, ...).

Weather Station
-------------

The weather station is contitued of 

- DS18X20 (1-Wire) temperature sensors,
- DHT11 humidity (and temperature) sensors,
- SBM-20/BOI-33 Geiger tube with detector board,
- BMP180 - Presure sensor.

All the sensors' informations are sent to a webserver with PHP/JS and MySQL for handling the data. This server is accessible via a web browser to diplay HTML5 graph of the measurements (over differents time axis) and some statistics. Some measurements are also directly visible on a screen on top of the arduino.

Schema
-------------

Will be uploaded soon !

Structure of the repro
----------------------

The repro is splitted into two parts, the first one is all the code concerning the Arduino board, the second is for the remote server.

There is a README in each part for more precise details on the implementation.

History
------------

Last version : v1.0 - First version with all the functionalities working !

v1.0 :

- Arduino side : Screen added, BMP180 (presure sensor) added, Geiger board added and RTC added. Modification of the structure of the program, now the submition interval is set in minutes and no longer in numbers of loops. All the program is clocked with the RTC.
- Server side : Timer on the outputs added.

v0.4 :

- Server side : Communication between the arduino and the server done, modification of the arduino ip added in user setting (with a verification of reachability), code cleaned and secured against various types of attacks, statistics added.
- Arduino side : Communication between the arduino and the server done, request parser added, IR remote added.

v0.3 :

- Server side : Plot done using highchart JS lib, manage sensors (rename, change unit, delete), user account setting added.
- Arduino side : Server/Client TCP added, code cleaned.

v0.2 :

- Server side : Website done except the sensors page (plots).
- Server side : Databases done.

v0.1 :

- Support of the DS18S20 sensors.
- Support of the RTC.


License
-------------

All this project is distributed under the MIT License. Have fun with this project ! If you want to modify it, do it (and send me an email through my website to see what awesone things you have done !)

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
