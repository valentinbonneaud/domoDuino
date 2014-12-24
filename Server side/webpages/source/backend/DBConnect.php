<?php

require_once(dirname(__FILE__).'/Config.php');


class DBConnect{
	
	private $con;
	function __construct(){}

	function __destruct(){}

	// Create connection
	public function connect(){
		
		$this -> con = mysqli_connect(HOST_SERVER, USERNAME, PASSWORD, DATABASE_NAME);

		// Check connection
		if(mysqli_connect_errno()){
			return false;
		}

		return $this -> con;
	}

	// Close connection
	public function close(){
		mysqli_close($this -> con);
	}
}

?>
