<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/DBConnect.php');

class DBUser{
	private $connect;

	function __construct(){
		$this -> connect = new DBConnect();
		$this -> connect = $this -> connect -> connect();
	}

	function __destruct(){
		$this -> connect -> close();
	}

	public function login($username, $password){
		if(is_null($username) || is_null($password)){
			displayJSON(FAIL);
			return false;
		}

		if($this -> checkAccount($username, $password)){
			session_start();
			$_SESSION['username'] = $username;
			$_SESSION['ip'] = $this->getIP($username);
			displayJSON(SUCCESS);
			return true;
		} else {
			displayJSON(FAIL);
			return false;
		}
	}

	private function getIP($username)
	{
		$query = "SELECT ".USERS_ip." FROM ".USERS_table." WHERE ".USERS_username." = '".$username."'";
		$executeResults = mysqli_query($this -> connect, $query);
		if(!$executeResults){
			die("Invalid query");
		} else {
			$row = mysqli_fetch_assoc($executeResults);
			if(isset($row[USERS_ip])){
				return $row[USERS_ip];
			} else {
				return false;
			}
		}
	}

	private function checkAccount($username, $password){
		$query = "SELECT COUNT(*) AS COUNT FROM ".USERS_table." WHERE ".USERS_username." = '".$username."' AND ".USERS_password." = SHA1('".$password."')";
		$executeResults = mysqli_query($this -> connect, $query);
		if(!$executeResults){
			die("Invalid query");
		} else {
			$row = mysqli_fetch_assoc($executeResults);
			if($row["COUNT"] == 1){
				return true;
			} else {
				return false;
			}
		}
	}

	public function changePassword($username, $oldPassword, $newPassword){
		if(is_null($username) || is_null($oldPassword) || is_null($newPassword)){
			return false;
		}

		if($this->checkAccount($username, $oldPassword)){
			if( $this->replacePassword($username, $oldPassword, $newPassword))
				return true;
			else
				return false;
		} else {
			return false;
		}
	}

	private function replacePassword($username, $oldPassword, $newPassword){
		$query = "UPDATE ".USERS_table." SET ".USERS_password." = SHA1('".$newPassword.
					"') WHERE ".USERS_username." = '".$username."' AND ".USERS_password." = SHA1('".$oldPassword."');";
		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}
}
?>
