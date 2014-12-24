<?php
session_start();

require_once('backend/Config.php');
require_once('backend/ArduinoConnect.php');
require_once('menu.php');

if(!isset($_SESSION['username']))
{
  echo '<meta http-equiv="refresh" content="0; url=login.php" />';
} 
else 
{
  $username = $_SESSION['username'];

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
		if($state[$i] == '2')	
			echo "<div class='span6'>".$names[$i]." : </div>
				<div class='span6'><button id='on_$i' class='btn btn-lg btn-success' disabled>On</button>
				<button id='off_$i' class='btn btn-lg btn-danger'>Off</button></div>";
		else
			echo "<div class='span6'>".$names[$i]." : </div>
				<div class='span6'><button id='on_$i' class='btn btn-lg btn-success'>On</button>
				<button id='off_$i' class='btn btn-lg btn-danger' disabled>Off</button></div>";
		echo "</div>";
	}

?>

    </div>
</body>
</html>

<?php } ?>
