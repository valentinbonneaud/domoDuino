<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/DBConnect.php');
require_once(dirname(__FILE__).'/Config.php');

class Sensors{

	private $ip;
	private $username;
	private $connect;

	function __construct(){
		session_start();
		if(isset($_SESSION['ip'])){
			$this->ip = $_SESSION['ip'];
			$this->username = $_SESSION['username'];
		} else {
			$this->ip = null;
			$this->username = null;
		}
		$this -> connect = new DBConnect();
		$this -> connect = $this -> connect -> connect();
	}

	function __destruct(){
		$this->connect->close();
	}

	public function getSensors(){

		if(is_null($this->username)){
			return false;
		}

		$sensors = array();

		// We first check if there are new sensors
		$sql = "SELECT m.".MEASURES_address." AS address FROM ".MEASURES_table." m, ".USERS_table." u WHERE u.".USERS_username." = '".$this->username."' AND u.".USERS_id." = m.".MEASURES_idUser." AND m.".MEASURES_address." NOT IN (SELECT s.".SENSORS_address." FROM ".SENSORS_table." s, ".USERS_table." u WHERE u.".USERS_id." = s.".SENSORS_idUser." AND u.".USERS_username." = '".$this->username."' GROUP BY s.".SENSORS_address.") GROUP BY m.".MEASURES_address;

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				// We add the new sensors to the user database
				$sql = "INSERT INTO ".SENSORS_table." (".SENSORS_idUser.", ".SENSORS_idSensor.", ".SENSORS_address.") SELECT (SELECT ".USERS_id." FROM ".USERS_table." WHERE ".USERS_username." = '".$this->username."'), IF( MAX( s.".SENSORS_idSensor." ) IS NULL , 0, MAX( s.".SENSORS_idSensor." ) +1 ) AS id, '".$row['address']."' FROM ".SENSORS_table." s, ".USERS_table." u WHERE u.".USERS_username." = '".$this->username."' AND u.".USERS_id." = s.".SENSORS_idUser;

				$executeResults2 = mysqli_query($this->connect, $sql);

				if(!$executeResults2){
					// Invalid query
					return false;
				}
			}
		}

		// We retrieve the sensors
		$sql = "SELECT s.".SENSORS_address." AS address, s.".SENSORS_idSensor." AS idSensor, s.".SENSORS_unit." AS unit, s.".SENSORS_name." AS name FROM ".SENSORS_table." s, ".USERS_table." u WHERE u.".USERS_id." = s.".SENSORS_idUser." AND u.".USERS_username." = '".$this->username."' GROUP BY s.".SENSORS_address;
		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				$sensors[$row['idSensor']]['address'] = $row['address'];
				$sensors[$row['idSensor']]['unit'] = $row['unit'];
				$sensors[$row['idSensor']]['name'] = $row['name'];
			}
		}

		return $sensors;
	}

	public function updateSensorsName($idSensor, $name){

		$query = "UPDATE ".SENSORS_table." SET ".SENSORS_name." = '$name' WHERE ".SENSORS_idSensor." = ".$idSensor." AND ".SENSORS_idUser." IN (SELECT ".USERS_id." FROM ".USERS_table." WHERE ".USERS_username." = '".$this->username."');";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	public function updateSensorsUnit($idSensor, $unit){

		$query = "UPDATE ".SENSORS_table." SET ".SENSORS_unit." = '$unit' WHERE ".SENSORS_idSensor." = ".$idSensor." AND ".SENSORS_idUser." IN (SELECT ".USERS_id." FROM ".USERS_table." WHERE ".USERS_username." = '".$this->username."');";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

}
?>
