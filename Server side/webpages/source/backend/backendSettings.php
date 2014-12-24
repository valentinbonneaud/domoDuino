<?php

require_once(dirname(__FILE__).'/DBUser.php');
require_once(dirname(__FILE__).'/ArduinoConnect.php');
require_once(dirname(__FILE__).'/Sensors.php');

session_start();
if(isset($_SESSION['username'])){
    $username = htmlspecialchars($_SESSION['username']);
    $password = $_SESSION['pass'];
} else {
    $username = null;
}

if(isset($_POST['action']))
{
	$action = htmlspecialchars($_POST['action']);

	if($action == 'nameChange') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$arduino = new ArduinoConnect();
			if($arduino->updateOutputName(htmlspecialchars($data['id']), htmlspecialchars($data['text'])))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changeAction') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$type = $data['type'];
			$i = htmlspecialchars($data['i']);
			$j = $data['j'];
			$arduino = new ArduinoConnect();

			$arr = array(1,1,1,1,1,1,1,1);

			if($type == "on") $arr[$j] = 2;
			else if ($type == "off") $arr[$j] = 3;

			if($arduino->sendUpdateRemote($i,$arr))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changePassword') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$oldPassword = htmlspecialchars($data['old']);
			$newPassword = htmlspecialchars($data['new']);
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
			$id = htmlspecialchars($data['id']);
			$text = htmlspecialchars($data['text']);
			$sensors = new Sensors();
			if($sensors->updateSensorsUnit($id, $text))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	} else if($action == 'changeSensorName') {
		if(isset($_POST['data']))
		{
			$data = $_POST['data'];
			$id = htmlspecialchars($data['id']);
			$text = htmlspecialchars($data['text']);
			$sensors = new Sensors();
			if($sensors->updateSensorsName($id, $text))
				displayJSON(SUCCESS, $_POST);
			else
				displayJSON(FAIL, $_POST);
		}
	}
}

?>
