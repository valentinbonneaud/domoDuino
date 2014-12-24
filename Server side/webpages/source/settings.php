<?php

require_once('backend/Config.php');
require_once('backend/ArduinoConnect.php');
require_once('backend/Sensors.php');
require_once('menu.php');

session_start();
if(!isset($_SESSION['username']))
{
  echo '<meta http-equiv="refresh" content="0; url=login.php" />';
} 
else 
{

  $username = $_SESSION['username'];

  // Print the menu
  getMenu('settings');

?>

    <!-- we load the ajax script -->
    <script src='settingsAjax.js'></script>
    <script src='settings.js'></script>

    <!-- Content -->
    <div class="container">

    <div id="messages"> </div>
         
<?php

// we print it for the javascript
echo "<div id='NB_OUTPUT' class='hidden'>".NB_OUTPUT."</div>";
echo "<div id='NB_BUTTONS_IR' class='hidden'>".NB_BUTTONS_IR."</div>";

if($_GET['page'] == 'outputs') {

	$arduino = new ArduinoConnect();
	$names = $arduino->getOutputsNames();

	echo "<legend><h3>Change the name of the outputs</h3></legend>";

	for($i=0;$i<NB_OUTPUT;$i++) {	
		echo "<p><div class='input-prepend control-group'>
				<span class='add-on'>Output ".($i+1)."</span>
  				<input class='span2' id='outputName_$i' type='text' value='".$names[$i]."'>
				
			</div><span class='hidden' id='successMessage$i'> The name has been successfully changed.</span></p>";
	}
 
} else if($_GET['page'] == 'sensors') {


	$sensors = new Sensors();
	$names = $sensors->getSensors();

	echo "<legend><h3>Change the name and unit of a sensor</h3></legend>";

	echo "<div id='NB_SENSORS' class='hidden'>".sizeof($names)."</div>";
	
	for($i=0;$i<sizeof($names);$i++) {
		echo "
		<div class = 'control-group' id = 'sensor$i'>
			<label class = 'control-label'>Sensor ".($i+1)." : ".$names[$i]['address']."</label>
  			<input type='text' id='nameSensor_$i' placeholder='Name' value='".$names[$i]['name']."'>
  			<input type='text' id='unitSensor_$i' class='input-small' placeholder='Unit' value='".$names[$i]['unit']."'>
			<span class='help-inline hidden' id='successMessageSensors$i'>Change saved.</span>
		</div>";
	}
 
} else if($_GET['page'] == 'remote') {

	$arduino = new ArduinoConnect();
	$names = $arduino->getOutputsNames();

	echo "<legend><h3>Change the action of a IR remote's button </h3></legend>";

	echo '<table class="table">';

	echo "<tr><td></td>";
	for($i=0;$i<NB_OUTPUT;$i++)
		echo "<td>".$names[$i]."</td>";
	echo "</tr>";

	for($i=0;$i<NB_BUTTONS_IR;$i++) {

		$irState = $arduino->getRemote($i);
	
		echo "<tr><td>Bouton ".($i+1)."</td>";
		for($j=0;$j<NB_OUTPUT;$j++) {
			if($irState[$j] == '1')
				echo "	<td><button id='nothing_".$i."_$j' class='btn btn-small' title=\"Don't do anything on this output\" disabled><i class='icon-ban-circle'></i></button>
					<button id='on_".$i."_$j' class='btn btn-lg btn-success btn-small' title='Switch on this output'><i class='icon-off'></i></button>
					<button id='off_".$i."_$j' class='btn btn-lg btn-danger' title='Switch off this output'><i class='icon-off'></i></button></td>";
			else if($irState[$j] == '2')
				echo "	<td><button id='nothing_".$i."_$j' class='btn btn-small' title=\"Don't do anything on this output\"><i class='icon-ban-circle'></i></button>
					<button id='on_".$i."_$j' class='btn btn-lg btn-success btn-small' title='Switch on this output' disabled><i class='icon-off'></i></button>
					<button id='off_".$i."_$j' class='btn btn-lg btn-danger btn-small' title='Switch off this output'><i class='icon-off'></i></button></td>";
			else
				echo "	<td><button id='nothing_".$i."_$j' class='btn btn-small' title=\"Don't do anything on this output\"><i class='icon-ban-circle'></i></button>
					<button id='on_".$i."_$j' class='btn btn-lg btn-success btn-small' title='Switch on this output'><i class='icon-off'></i></button>
					<button id='off_".$i."_$j' class='btn btn-lg btn-danger btn-small' title='Switch off this output' disabled><i class='icon-off'></i></button></td>";
		}
		echo "</tr>";
	}

	echo '</table>';

} else if($_GET['page'] == 'user') {

	echo "
          <legend><h3>Change your password</h3></legend>
          <div class = 'control-group' id = 'oldPasswordGroup'>
            <label class = 'control-label'>Old password</label>
            <input type='password' placeholder='Type your old password' id = 'oldPassword'>
          </div>
          <div class = 'control-group' id = 'newPasswordGroup'>
            <label class = 'control-label'>New password</label>
            <input type='password' placeholder='Type your new password' id = 'newPassword'>
          </div>
          <div class = 'control-group' id = 'retypedNewPasswordGroup'>
            <label class = 'control-label'>Retype new password</label>
            <input type='password' placeHolder='Retype your new password' id = 'retypedNewPassword'>
          </div>
          <br>
           <ul class = 'inline'>
              <li>
                 <button type='submit' class='btn btn-primary' id = 'buttonPass'>Change password</button>
              </li>
              <li>
                  <p class='text-error hidden' id = 'errorMessage1'>The retyped password is incorrect.</p>
              </li>
              <li>   
                  <p class='text-error hidden' id = 'errorMessage2'>The current password is incorrect.</p>
              </li>
              <li>
                  <p class='text-success hidden' id = 'successMessage'>Password has been successfully changed.</p>
              </li>
          </ul>   
        ";

}
?>

    </div>
</body>
</html>

<?php } ?>
