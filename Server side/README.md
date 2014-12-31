Domoduino
===============

Server Side
---------------

This part is hosted on a remote server or inside the arduino subnetwork. This server will save all the measurements of the various sensors connected to the arduino. All the data is stored inside MySQL databases.

Note : If your server is not on your home subnet and you don't have an static external IP on the arduino, you have to use an DynDNS (such as no-ip, ...) on your ISP router to be able to reach your arduino from the server.

Installation
-------------

To install this part, you have to change the parameters (MySQL credentials, users, ip of the arduino) inside the /source/backend/Config.php file. You also have to create the database using the SQL file provided in the repository.

Generalities
------------

There is a login system on the website to protect the access of the platform. Like that all actions need an authentication or an api key.

Data Collection
-----------------

The data is sent from the arduino to the server through a get request on a specific url. The authentication is ensure by the api key. The data (user id + address of sensor + value of the sensor) is inserted directly into the "measures" database, the MySQL also engine add a timestamp.

Domotic
--------------

This is the main page of the website. Through this page, the user can manage all the outputs of the arduino. Firstly, the user see the current state of all outputs (the current state of the outputs are loaded from the arduino during the loading of the page). But the user can also activate or deactivate any output with a simple button and almost no latency (like the request is directly sent to the arduino and not the other way around). It's also possible to program an output to automatically switch on or off at a given time (with recurrence or not) [still in development] using the RTC of the circuit. You can read the README of the arduino part for further details.

Sensors page
------------

On the sensors section, it's possible to visualize plot of the sensors measurements and statistical data of the measurements such as min/max, average over the last hour, last 24 hours, last day or last night. The graph part is using the Javascript library Highcharts, and it's continually updating the data using Ajax (so the user don't need to refresh the page to see the new measures submitted by the arduino). The statistics part is also done using Ajax to provide a constinous updating of the displayed data.

User Settings
----------------

Through the user setting pages, the user can :

- Change its password,
- Change the Arduino URL with a verification of the reachability of the arduino (with a special request to the arduino) while the user is tipping the URL,
- Change the outputs name (names displayed on the graph section),
- Change the action of the IR remote (the current configuration is loaded from the arduino and the change are submitted, nothing is stored on the server),
- Management of the sensors (change the name, unit, and delete all the data related to this sensor). 
