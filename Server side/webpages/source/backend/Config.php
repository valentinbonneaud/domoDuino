<?php

define("HOST_SERVER", "");
define("USERNAME", "");
define("PASSWORD", "");
define("DATABASE_NAME", "");

define("USERS_table","users");
define("USERS_id","id");
define("USERS_username","username");
define("USERS_password","password");
define("USERS_apikey","apikey");
define("USERS_ip","ipArduino");

define("OUTPUTS_table","outputs");
define("OUTPUTS_userID","userID");
define("OUTPUTS_nb","outputNb");
define("OUTPUTS_name","outputName");

define("SENSORS_table","sensors");
define("SENSORS_idUser","idUser");
define("SENSORS_idSensor","idSensor");
define("SENSORS_address","address");
define("SENSORS_name","name");
define("SENSORS_unit","unit");

define("MEASURES_table","measures");
define("MEASURES_id","id");
define("MEASURES_idUser","idUser");
define("MEASURES_address","address");
define("MEASURES_time","dateMeasure");
define("MEASURES_value","value");

define("NB_OUTPUT","8");
define("NB_BUTTONS_IR","10");

//Time settings
define("END_NIGHT_HOUR","7");
define("END_DAY_HOUR","19");

// time between two samples of data in seconds
define("FREQ_SENSOR", 10); 
// data to load at the initialization of the graph (in hours)
define("RETRIEVE_GRAPH", 2);  

?>
