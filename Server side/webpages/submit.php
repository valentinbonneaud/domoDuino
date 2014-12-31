<?php

require_once(dirname(__FILE__).'/source/backend/DBUser.php');
require_once(dirname(__FILE__).'/source/backend/Sensors.php');

// To submit, we use get data, we need to form an url like this one :
// http://path/to/server/submit.php?apikey=APIKEY&address=ADDRESS_OF_SENSOR&value=VALUE_TO_SUBMIT

// The page display 1 if the new records has been correctly inserted and 0 if not
// If one of the parameter is missing or if the api key is wrong, the page display nothing

if(isset($_GET['apikey']) && isset($_GET['data'])) {

	$dbUser = new DBUser();
	$id = $dbUser->getID($_GET['apikey']);
	
	if($id != false) {
		$sensors = new Sensors();

		$data = $_GET['data'];

		for($i=0;$i<sizeof($data);$i++) {
			if(!$sensors->sendNewMeasure($id,$data[$i]['address'],$data[$i]['value'])) {
				echo '0';
				exit;	
			}
		}

		echo '1';
	}

}

?>
