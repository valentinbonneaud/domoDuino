<?php
require_once(dirname(__FILE__).'/DBUser.php');

if(isset($_POST['data'])){ 
	$data = $_POST['data'];
	$username = $data['user'];
	$password = $data['pass'];
	
	$dbUser = new DBUser();
	$dbUser -> login($username, $password);
}
?>
