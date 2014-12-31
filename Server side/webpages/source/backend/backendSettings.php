<?php

require_once(dirname(__FILE__).'/DBUser.php');
require_once(dirname(__FILE__).'/ArduinoConnect.php');
require_once(dirname(__FILE__).'/Sensors.php');

// We check if the user is logged, if yes, the variables $username, $ip, $idUser are created
include("checkLoginBackend.php");

if(isset($_POST['action']))
{
	$action = $_POST['action'];

	if($action == 'nameOutputChange') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$arduino = new ArduinoConnect();
			if($arduino->updateOutputName($data['id'], $data['text']))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changeAction') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$i = $data['i'];
			$arduino = new ArduinoConnect();

			$arr = $data['buttons'];

			if($arduino->sendUpdateRemote($i,$arr))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changePassword') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$oldPassword = $data['old'];
			$newPassword = $data['new'];
			$dbUser = new DBUser();
			if($dbUser->changePassword($username, $oldPassword, $newPassword))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changeSensorUnit') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$idSensor = $data['id'];
			$text = $data['text'];
			$sensors = new Sensors();
			if($sensors->updateSensorsUnit($idSensor, $text))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changeSensorName') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$idSensor = $data['id'];
			$text = $data['text'];
			$sensors = new Sensors();
			if($sensors->updateSensorsName($idSensor, $text))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'deleteSensor') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$id = $data['id'];
			$address = $data['address'];
			$sensors = new Sensors();
			if($sensors->deleteSensor($address))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changeIP') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$ip = $data['ip'];
			$arduino = new ArduinoConnect();
			// we check that the ip given by the user is correct and that an arduino is behind this ip
			$_POST['ping'] = $arduino->testArduino($ip);
			if($arduino->updateArduinoURL($ip))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	}
}

?>
