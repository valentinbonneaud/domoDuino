<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/DBConnect.php');
require_once(dirname(__FILE__).'/Config.php');

class Sensors{

	private $ip;
	private $username;
	private $connect;
	private $idUser;

	function __construct(){
		session_start();
		
		$this->ip = $_SESSION['ip'];
		$this->username = $_SESSION['username'];
		$this->idUser = $_SESSION['idUser'];
		$this->connect = new DBConnect();
		$this->connect = $this->connect->connect();
	}

	function __destruct(){
		$this->connect->close();
	}

	public function getSensors(){

		$sensors = array();

		// We first check if there are new sensors
		$sql = "SELECT DISTINCT m.".MEASURES_address." AS address FROM ".MEASURES_table." m WHERE m.".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND m.".MEASURES_address." NOT IN (SELECT DISTINCT s.".SENSORS_address." FROM ".SENSORS_table." s WHERE s.".SENSORS_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' )";

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				// We add the new sensors to the user database
				$sql = "INSERT INTO ".SENSORS_table." (".SENSORS_idUser.", ".SENSORS_idSensor.", ".SENSORS_address.") SELECT '".mysqli_real_escape_string($this->connect,$this->idUser)."', IF( MAX( s.".SENSORS_idSensor." ) IS NULL , 0, MAX( s.".SENSORS_idSensor." ) +1 ), '".mysqli_real_escape_string($this->connect,$row['address'])."' FROM ".SENSORS_table." s WHERE s.".SENSORS_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."'";

				$executeResults2 = mysqli_query($this->connect, $sql);

				if(!$executeResults2){
					// Invalid query
					return false;
				}
			}
		}

		// We retrieve the sensors
		$sql = "SELECT DISTINCT s.".SENSORS_address." AS address, s.".SENSORS_idSensor." AS idSensor, s.".SENSORS_unit." AS unit, s.".SENSORS_name." AS name FROM ".SENSORS_table." s WHERE s.".SENSORS_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."'";
		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				$newData = array();
				$newData['id'] = $row['idSensor'];
				$newData['address'] = $row['address'];
				$newData['unit'] = $row['unit'];
				$newData['name'] = $row['name'];
				$sensors[] = $newData;
			}
		}

		return $sensors;
	}

	public function updateSensorsName($idSensor, $name){

		$query = "UPDATE ".SENSORS_table." SET ".SENSORS_name." = '".mysqli_real_escape_string($this->connect,$name)."' WHERE ".SENSORS_idSensor." = '".mysqli_real_escape_string($this->connect,$idSensor)."' AND ".SENSORS_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."';";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	public function updateSensorsUnit($idSensor, $unit){

		$query = "UPDATE ".SENSORS_table." SET ".SENSORS_unit." = '".mysqli_real_escape_string($this->connect,$unit)."' WHERE ".SENSORS_idSensor." = '".mysqli_real_escape_string($this->connect,$idSensor)."' AND ".SENSORS_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."';";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	public function sendNewMeasure($id, $address, $value){

		// We need to multiply the time by 1000 to match the format of JS time
		$query = "INSERT INTO ".MEASURES_table."(".MEASURES_time.",".MEASURES_idUser.",".MEASURES_address.",".MEASURES_value.") VALUES ('".(time()*1000)."', '".mysqli_real_escape_string($this->connect,$id)."','".mysqli_real_escape_string($this->connect,$address)."','".mysqli_real_escape_string($this->connect,$value)."');";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	public function deleteSensor($address){

		$query = "DELETE FROM ".MEASURES_table." WHERE ".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND ".MEASURES_address." = '".mysqli_real_escape_string($this->connect,$address)."'";

		$executeResults = mysqli_query($this -> connect, $query);
		if(!$executeResults){
			return false;
		}

		$query = "DELETE FROM ".SENSORS_table." WHERE ".SENSORS_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND ".SENSORS_address." = '".mysqli_real_escape_string($this->connect,$address)."'";

		$executeResults = mysqli_query($this -> connect, $query);
		if(!$executeResults){
			return false;
		}

		return true;

	}

	public function getMeasuresSensor($address, $limit){

		$sensors = array();

		// We first check if there are new sensors
		$sql = "SELECT m.".MEASURES_time." AS time, m.".MEASURES_value." AS value FROM ".MEASURES_table." m WHERE m.".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND m.".MEASURES_address." = '".mysqli_real_escape_string($this->connect,$address)."' ORDER BY ".MEASURES_time." DESC LIMIT 0,".$limit;

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				$sensors[] = array($row['time'], $row['value']);
			}
		}

		return $sensors;
	}

	public function getMeasuresSince($address, $time){

		$sensors = array();

		// We first check if there are new sensors
		$sql = "SELECT m.".MEASURES_time." AS time, m.".MEASURES_value." AS value FROM ".MEASURES_table." m WHERE m.".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND m.".MEASURES_address." = '".mysqli_real_escape_string($this->connect,$address)."' AND m.".MEASURES_time." > '".mysqli_real_escape_string($this->connect,$time)."' ORDER BY ".MEASURES_time." DESC";

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				$sensors[] = array($row['time'], $row['value']);
			}
		}

		return $sensors;
	}

	public function getStatisticsSince($address, $timestamp) {

		// We first check if there are new sensors
		$sql = "SELECT ROUND(AVG(m.".MEASURES_value."),2) AS average, MIN(m.".MEASURES_value.") AS min, MAX(m.".MEASURES_value.") AS max FROM ".MEASURES_table." m WHERE m.".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND m.".MEASURES_address." = '".mysqli_real_escape_string($this->connect,$address)."' AND m.".MEASURES_time." > '".mysqli_real_escape_string($this->connect,$timestamp)."'";

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			return mysqli_fetch_array($executeResults, MYSQL_ASSOC);
		}

	}

	public function getStatisticsBetween($address, $timestampStart, $timestampEnd) {

		// We first check if there are new sensors
		$sql = "SELECT ROUND(AVG(m.".MEASURES_value."),2) AS average, MIN(m.".MEASURES_value.") AS min, MAX(m.".MEASURES_value.") AS max FROM ".MEASURES_table." m WHERE m.".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND m.".MEASURES_address." = '".mysqli_real_escape_string($this->connect,$address)."' AND m.".MEASURES_time." > '".mysqli_real_escape_string($this->connect,$timestampStart)."' AND m.".MEASURES_time." < '".mysqli_real_escape_string($this->connect,$timestampEnd)."'";

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			return mysqli_fetch_array($executeResults, MYSQL_ASSOC);
		}

	}

	public function getClosestMeasure($address, $timestamp) {

		// We first check if there are new sensors
		$sql = "SELECT m.".MEASURES_time." AS time, m.".MEASURES_value." AS value FROM ".MEASURES_table." m WHERE m.".MEASURES_idUser." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND m.".MEASURES_address." = '".mysqli_real_escape_string($this->connect,$address)."' ORDER BY ABS(dateMeasure - ".mysqli_real_escape_string($this->connect,$timestamp).") ASC LIMIT 0,1";

		$executeResults = mysqli_query($this->connect, $sql);

		if(!$executeResults){
			// Invalid query
			return false;
		} else {
			$ret = mysqli_fetch_array($executeResults, MYSQL_ASSOC);
			if(abs($ret['time'] - $timestamp) < 2*60*60*1000) // if the measure is more than two hours away from the given timestamp, we discard
				return $ret;
			else
				return null;
		}

	}

}
?>
