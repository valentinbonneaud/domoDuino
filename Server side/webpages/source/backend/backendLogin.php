<?php
require_once(dirname(__FILE__).'/DBUser.php');

if(isset($_POST['data'])){ 
	$data = $_POST['data'];
	$username = htmlspecialchars($data['user']);
	$password = htmlspecialchars($data['pass']);
	
	$dbUser = new DBUser();
	$dbUser -> login($username, $password);
}
?>
