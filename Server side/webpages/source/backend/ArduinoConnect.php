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
// ob11 = off

class ArduinoConnect{

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
		$this -> connect -> close();
	}

	public function sendUpdateOutput($array){
		if(is_null($array)){
			return false;
		}

		// we form the url
		$x = $this->encode($array);
		$url = $this->ip."?type=setOutput&output=".$x;

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
		$url = $this->ip."?type=setRemote&remote=$button&output=".$x;

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));

		if($rep == '1')
			return true;
		else
			return false;
	}

	public function getOutputs(){

		// we form the url
		$url = $this->ip."?type=getOutputs";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));
		
		return $this->decode($rep);
	}

	public function getRemote($idButton){

		// we form the url
		$url = $this->ip."?type=getRemote&remote=$idButton";

		// We take the answer of the arduino
		$rep = preg_replace('/\s+/', ' ', trim(file_get_contents($url)));
		
		return $this->decode($rep);
	}

	public function getOutputsNames(){

		$sql = "SELECT o.".OUTPUTS_nb." AS nb, o.".OUTPUTS_name." AS name FROM ".OUTPUTS_table." o, ".USERS_table." u WHERE o.".OUTPUTS_userID." = u.".USERS_id." AND u.".USERS_username." = '".$this->username."';";

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

		$query = "UPDATE ".OUTPUTS_table." SET ".OUTPUTS_name." = '$name' WHERE ".OUTPUTS_userID." IN (SELECT ".USERS_id." FROM ".USERS_table." WHERE ".USERS_username." = '".$this->username."') AND ".OUTPUTS_nb." = '$id';";

		$executeResults = mysqli_query($this -> connect, $query);
		if($executeResults){
			return true;
		} else {
			return false;
		}
	}

	private function encode($array) {

		$out = 0;
		for($i=NB_OUTPUT-1;$i>=0;$i--) {
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

}
?>
