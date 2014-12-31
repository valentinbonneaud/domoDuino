<?php

// Function to print the menu, the input is used to select the selected item in the menu
// $page can take the following values : {main, sensors, settings}
function getMenu($page) {

	echo "
<!DOCTYPE html>
<html>
  <head>
    <meta charset = 'UTF-8'>
    <title>domoDuino</title>

    <link rel='stylesheet' type='text/css' href='../bootstrap/css/bootstrap.min.css'>
    <link rel='stylesheet' type='text/css' href='css/main.css'>
    <link href='../bootstrap/css/bootstrap-responsive.min.css' rel='stylesheet'>

    <script src='//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>
    <script src='../bootstrap/js/bootstrap.min.js'></script>

  </head>
  <body>
    <!-- Navigation Bar -->
    <div class='navbar navbar-inverse navbar-fixed-top'>
      <div class='navbar-inner'>
        <div class='container'>
          <a class='brand' href='main.php' style = 'color: white'>domoDuino</a>
          <div class='nav nav-collapse collapse'>";
	
	if($page == "main") echo "<li class='active'><a href='main.php'>Domotic</a></li>";
	else echo "<li><a href='main.php'>Domotic</a></li>";
            
	if($page == "sensors") echo "<li class='active'><a href='sensors.php'>Sensors</a></li>";
	else echo "<li><a href='sensors.php'>Sensors</a></li>";
	
	if($page == "settings") echo "<li class='dropdown active'>";
	else echo "<li class='dropdown'>";

	echo "  <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Settings <b class='caret'></b></a>
                <ul class='dropdown-menu'>
		  <li class='nav-header'>Website</li>
                  <li><a href='settings.php?page=user'>User Settings</a></li>
                  <li><a href='settings.php?page=outputs'>Outputs names</a></li>
                  <li class='divider'></li>
                  <li class='nav-header'>Arduino</li>
                  <li><a href='settings.php?page=remote'>IR Remote</a></li>
		  <li><a href='settings.php?page=sensors'>Sensors</a></li>
                </ul>
              </li>
          </div>
          <ul class='nav pull-right'>
            <li><a href='logout.php'>Log Out</a></li>
          </ul>
        </div>
      </div>
    </div>";

}

?>
