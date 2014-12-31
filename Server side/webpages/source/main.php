<?php

  require_once('backend/Config.php');
  require_once('backend/ArduinoConnect.php');
  require_once('menu.php');

  // We check if the user is logged, if yes, the variables $username, $ip, $idUser are created
  include("backend/checkLogin.php");

  // Print the menu
  getMenu('main');

?>

    <!-- we load the ajax script -->
    <script src='domoticAjax.js'></script>
    <script src='domotic.js'></script>

    <!-- Content -->
    <div class="container">

    <div id="errorMessages"> </div>

    <legend><h3>Output management</h3></legend>
         
<?php   

	// Get the state of each button
	$arduino = new ArduinoConnect();
	$state = $arduino->getOutputs();
	$names = $arduino->getOutputsNames();

	

	for($i=0;$i<NB_OUTPUT;$i++) {
		echo "<div class='row-fluid'>";
		echo "<div class='span6'>".$names[$i]." : </div>";
		if($state[$i] == '2')	
			echo "	<div class='span6'><button id='on_$i' class='btn btn-lg btn-success' disabled>On</button>
				<button id='off_$i' class='btn btn-lg btn-danger'>Off</button>
				<button id='time_$i' class='btn btn-lg btn-info'><i class='icon-time'></i></button></div>";
		else
			echo "	<div class='span6'><button id='on_$i' class='btn btn-lg btn-success'>On</button>
				<button id='off_$i' class='btn btn-lg btn-danger' disabled>Off</button>
				<button id='time_$i' class='btn btn-lg btn-info'><i class='icon-time'></i></button></div>";
		echo "</div>";
		echo "<div class='row-fluid'><div class='span12'></div></div>";
	}

?>

    </div>
</body>
</html>
