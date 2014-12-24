<?php

	define ("SUCCESS", 'SUCCESS');
	define ("FAIL", 'FAIL');

	function displayJSON($status, $data = null){
		if(is_null($data)){
			$arr = array('status' => $status);
		} else{
			$arr = array('status' => $status, 'data' => $data);
		}

		$jsonData = json_encode($arr);
		header('Content-Type: application/json');
		echo $jsonData;
	}

?>