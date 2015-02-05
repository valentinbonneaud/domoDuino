<?php

require_once(dirname(__FILE__).'/DBDisplay.php');
require_once(dirname(__FILE__).'/DBConnect.php');
require_once(dirname(__FILE__).'/Config.php');

// To submit an output to the arduino we use 16 bits number :
// Output nb : 7  6  5  4  3  2  1  0
//             -- -- -- -- -- -- -- --
// each -- is two bits and correspond to one output,
// 0b01 = do nothing
// 0b10 = on
// 0b11 = off

class ArduinoConnect{

	private $ip;
	private $username;
	private $idUser;
	private $connect;

	function __construct(){
		session_start();
		if(isset($_SESSION['ip'])){
			$this->ip = $_SESSION['ip'];
			$this->username = $_SESSION['username'];
			$this->idUser = $_SESSION['idUser'];
		} else {
			$this->ip = null;
			$this->username = null;
			$this->idUser = null;
		}
		$this->connect = new DBConnect();
		$this->connect = $this->connect->connect();
	}

	function __destruct(){
		$this->connect->close();
	}

	public function testArduino($ip){
		if(is_null($ip)){
			return false;
		}

		// ping
		$url = $ip."/?t=7&a=0&b=0&e";

		// we specify a timeout on the request
		$ctx = stream_context_create(array( 
		    'http' => array( 
			'timeout' => 3 
			) 
		    ) 
		); 

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url,0,$ctx)));

		if($rep == '1')
			return true;
		else
			return false;
	}

	public function sendUpdateOutput($array){
		if(is_null($array)){
			return false;
		}

		// we form the url
		$x = $this->encode($array);
		$url = $this->ip."?t=1&a=".$x."&b=0&e";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));

		if($rep == '1')
			return true;
		else
			return false;
	}

	public function sendUpdateRemote($button,$array){
		if(is_null($array)){
			return false;
		}

		// we form the url
		$x = $this->encode($array);
		$url = $this->ip."?t=3&a=$button&b=".$x."&e";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));

		if($rep == '1')
			return true;
		else
			return false;
	}

	public function getOutputs(){

		// we form the url
		$url = $this->ip."?t=0&a=0&b=0&e";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));
		
		return $this->decode($rep);
	}

	public function getRemote($idButton){

		// we form the url
		$url = $this->ip."?t=2&a=$idButton&b=0&e";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));
		
		return $this->decode($rep);
	}

	public function getAlarm($i){

		// we form the url
		$url = $this->ip."?t=4&a=$i&b=0&e";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));
		
		return $this->decodeAlarm($rep);
	}

	public function sendUpdateAlarm($i,$hourAlarm, $minAlarm, $durHour, $durMin, $days, $reverse){


		// we form the url
		$x = $this->encodeAlarm($hourAlarm, $minAlarm, $durHour, $durMin, $days, $reverse);
		
		$url = $this->ip."?t=5&a=".$i."&b=".$x."&e";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));

		if($rep == '1')
			return true;
		else
			return false;
	}

	public function getOutputsNames(){

		$sql = "SELECT o.".OUTPUTS_nb." AS nb, o.".OUTPUTS_name." AS name FROM ".OUTPUTS_table." o WHERE o.".OUTPUTS_userID." = '".mysqli_real_escape_string($this->connect,$this->idUser)."';";

		$executeResults = mysqli_query($this->connect, $sql);
		if(!$executeResults){
			die("Invalid query");
		} else {
			$returnArray = array();
			while($row = mysqli_fetch_array($executeResults, MYSQL_ASSOC)){
				$returnArray[$row['nb']] = $row['name'];
			}
			return $returnArray;
		}
	}

	public function updateOutputName($id, $name){

		$query = "UPDATE ".OUTPUTS_table." SET ".OUTPUTS_name." = '".mysqli_real_escape_string($this->connect,$name)."' WHERE ".OUTPUTS_userID." = '".mysqli_real_escape_string($this->connect,$this->idUser)."' AND ".OUTPUTS_nb." = '".mysqli_real_escape_string($this->connect,$id)."';";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	public function getArduinoURL(){

		$sql = "SELECT u.".USERS_ip." AS ip FROM ".USERS_table." u WHERE u.".USERS_id." = '".mysqli_real_escape_string($this->connect,$this->idUser)."';";

		$executeResults = mysqli_query($this->connect, $sql);
		if(!$executeResults){
			die("Invalid query");
		} else {
			$row = mysqli_fetch_assoc($executeResults);
			return $row["ip"];
		}
	}

	public function updateArduinoURL($ip){

		$query = "UPDATE ".USERS_table." SET ".USERS_ip." = '".mysqli_real_escape_string($this->connect,$ip)."' WHERE ".USERS_id." = '".mysqli_real_escape_string($this->connect,$this->idUser)."';";

		$executeResults = mysqli_query($this->connect, $query);
		session_start();
		$_SESSION['ip'] = mysqli_real_escape_string($this->connect,$ip);

		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	private function encode($array) {

		$out = 0;
		
		for($i = 0;$i<NB_OUTPUT;$i++){
			$out *= 4;
			if($array[$i] >= 1 && $array[$i] <= 3)
				$out += $array[$i];

		}

		return $out;
	}

	private function decode($x) { 

		$out = array();
		
		for($i = 0;$i<NB_OUTPUT;$i++)
		{
			$out[$i] = ($x/pow(4,$i))%4;
		}

		return $out;
	}

	public function encodeAlarm($hourAlarm, $minAlarm, $durHour, $durMin, $days, $reverse) {

		$hourEncodedAlarm = 60*$hourAlarm + $minAlarm; // max value = 1439
		$hourEncodedDur = 60*$durHour + $durMin;

		$out = $hourEncodedAlarm*1440 + $hourEncodedDur;

		// days is coded on 7 bits -> max value = 127

		$out = $out*128+$days;
		$out = 2*$out;

		if($reverse == "true")
			$out += 1;
		

		return $out;
	}

	private function decodeAlarm($x) { 

		$out = array();
		
		$out['reverse'] = $x%2;
		$x = intval($x/2);
		$out['days'] = $x%128;
		$x = intval($x/128);
		$encodedDate = $x%1440;
		$out['duration'] = array();
		$out['duration']['min'] = $encodedDate%60;
		$out['duration']['hour'] = intval($encodedDate/60);
		$x = intval($x/1440);
		$encodedDate = $x%1440;
		$out['alarm'] = array();
		$out['alarm']['min'] = $encodedDate%60;
		$out['alarm']['hour'] = intval($encodedDate/60);


		return $out;
	}

}
?>
