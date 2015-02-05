<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/ArduinoConnect.php');

// We check if the user is logged, if yes, the variables $username, $ip, $idUser are created
include("checkLoginBackend.php");

if(isset($_POST['dur']) && isset($_POST['time']) && isset($_POST['i']) && isset($_POST['inverse']) && isset($_POST['days'])){

	$arduino = new ArduinoConnect();

	if($arduino->sendUpdateAlarm($_POST['i'],$_POST['time']['hour'], $_POST['time']['min'], $_POST['dur']['hour'], $_POST['dur']['min'], $_POST['days'], $_POST['inverse']))
		displayJSON(SUCCESS, $_POST);
	else
		displayJSON(FAIL, $_POST);
}

?>
