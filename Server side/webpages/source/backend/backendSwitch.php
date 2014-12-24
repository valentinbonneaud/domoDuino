<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/ArduinoConnect.php');

session_start();
if(isset($_SESSION['pass'])){
    $pass = $_SESSION['pass'];
} else {
    $pass = null;
}


if(isset($_POST['type']) && isset($_POST['i'])){

	$type = htmlspecialchars($_POST['type']);
	$i = htmlspecialchars($_POST['i']);

	$arduino = new ArduinoConnect();

	$arr = array(1,1,1,1,1,1,1,1);

	if($type == "on") $arr[$i] = 2;
	else if ($type == "off") $arr[$i] = 3;

	if($arduino->sendUpdateOutput($arr))
		displayJSON(SUCCESS, $_POST);
	else
		displayJSON(FAIL, $_POST);
}

?>
