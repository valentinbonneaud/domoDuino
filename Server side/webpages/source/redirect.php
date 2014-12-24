<?php
	
	function redirect($pageName){
		session_start();
		session_destroy();
		$url='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		//remove any trailing slahshes
		$url=rtrim($url,'/\\');
			
		$url.='/'.$pageName;
				
		//redirect to the page
		header("Location:$url");
		exit();
	}

	function redirectWithoutDestroy($pageName){
		$url='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
		//remove any trailing slahshes
		$url=rtrim($url,'/\\');
			
		$url.='/'.$pageName;
				
		//redirect to the page
		header("Location:$url");
		exit();
	}
?>
