<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/DBConnect.php');

class DBUser{
	private $connect;

	function __construct(){
		$this->connect = new DBConnect();
		$this->connect = $this->connect->connect();
	}

	function __destruct(){
		$this->connect->close();
	}

	public function login($username, $password){
		if(is_null($username) || is_null($password)){
			displayJSON(FAIL);
			return false;
		}

		if($this->checkAccount($username, $password)){
			session_start();
			$_SESSION['username'] = $username;
			$_SESSION['ip'] = $this->getIP($username);
			$_SESSION['idUser'] = $this->getIDUser($username);
			displayJSON(SUCCESS);
			return true;
		} else {
			displayJSON(FAIL);
			return false;
		}
	}

	public function getID($apikey){
		if(is_null($apikey)){
			return false;
		}

		$query = "SELECT ".USERS_id." FROM ".USERS_table." WHERE ".USERS_apikey." = '".mysqli_real_escape_string($this->connect,$apikey)."'";
		$executeResults = mysqli_query($this->connect, $query);
		if(!$executeResults){
			die("Invalid query");
		} else {
			if($executeResults->num_rows == 1) {
				$row = mysqli_fetch_assoc($executeResults);
				return $row[USERS_id];
			} else {
				return false;
			}
		}
	}

	private function getIP($username)
	{
		$query = "SELECT ".USERS_ip." AS ip FROM ".USERS_table." WHERE ".USERS_username." = '".mysqli_real_escape_string($this->connect,$username)."'";
		$executeResults = mysqli_query($this -> connect, $query);
		if(!$executeResults){
			die("Invalid query");
		} else {
			$row = mysqli_fetch_assoc($executeResults);
			if(isset($row["ip"])){
				return $row["ip"];
			} else {
				return false;
			}
		}
	}

	private function getIDUser($username)
	{
		$query = "SELECT ".USERS_id." AS id FROM ".USERS_table." WHERE ".USERS_username." = '".mysqli_real_escape_string($this->connect,$username)."'";
		$executeResults = mysqli_query($this -> connect, $query);
		if(!$executeResults){
			die("Invalid query");
		} else {
			$row = mysqli_fetch_assoc($executeResults);
			if(isset($row["id"])){
				return $row["id"];
			} else {
				return false;
			}
		}
	}

	private function checkAccount($username, $password){
		$query = "SELECT COUNT(*) AS COUNT FROM ".USERS_table." WHERE ".USERS_username." = '".mysqli_real_escape_string($this->connect,$username)."' AND ".USERS_password." = SHA1('".mysqli_real_escape_string($this->connect,$password)."')";
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
		$query = "UPDATE ".USERS_table." SET ".USERS_password." = SHA1('".mysqli_real_escape_string($this->connect,$newPassword).
					"') WHERE ".USERS_username." = '".mysqli_real_escape_string($this->connect,$username)."' AND ".USERS_password." = SHA1('".mysqli_real_escape_string($this->connect,$oldPassword)."');";
		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}
}
?>
