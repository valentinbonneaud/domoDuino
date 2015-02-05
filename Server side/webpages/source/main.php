<?php

  require_once('backend/Config.php');
  require_once('backend/ArduinoConnect.php');
  require_once('menu.php');

  // We check if the user is logged, if yes, the variables $username, $ip, $idUser are created
  include("backend/checkLogin.php");

  // Print the menu
  getMenu('main');

  $days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

?>

    <!-- we load the ajax script -->
    <script src='domoticAjax.js'></script>
    <script src='domotic.js'></script>

    <!-- Content -->
    <div class="container">

    <div id="errorMessages"> </div>

<style type="text/css">
body .modal {
    /* new custom width */
    width: 590px;
    /* must be half of the width, minus scrollbar on the left (30px) */
    margin-left: -295px;
}
</style>

    <legend><h3>Output management</h3></legend>
         
<?php   

	// Get the state of each button
	$arduino = new ArduinoConnect();
	$state = $arduino->getOutputs();
	$names = $arduino->getOutputsNames();

	$hours;	$min;

	for($i=0;$i<NB_OUTPUT;$i++) {
		
		$getAlarms = $arduino->getAlarm($i);

		if($getAlarms['alarm']['hour'] < 10) $hour = '0'.$getAlarms['alarm']['hour'];
			else $hour = $getAlarms['alarm']['hour'];

		if($getAlarms['alarm']['min'] < 10) $min = '0'.$getAlarms['alarm']['min'];
			else $min = $getAlarms['alarm']['min'];

		echo "<div class='row-fluid'>";
		echo "<div class='span6'>".$names[$i]." : </div>";
		if($state[$i] == '2')	
			echo "	<div class='span6'><button id='on_$i' class='btn btn-lg btn-success' disabled>On</button>
				<button id='off_$i' class='btn btn-lg btn-danger'>Off</button>";
		else
			echo "	<div class='span6'><button id='on_$i' class='btn btn-lg btn-success'>On</button>
				<button id='off_$i' class='btn btn-lg btn-danger' disabled>Off</button>";

		//echo "<button id='time_$i' class='btn btn-lg btn-info'><i class='icon-time'></i></button></div>";
		echo " <a href='#modal$i' role='button' class='btn btn-lg btn-info' data-toggle='modal'><i class='icon-time'></i></a>";
		echo "</div>";
		echo "<div class='row-fluid'><div class='span12'></div></div>";
	
		echo "	<div id='modal$i' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
				<div class='modal-header'>
					<button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>
					<h3>Program the output '".$names[$i]."'</h3>
				</div>
				<div class='modal-body'>
					<h4>Set time :</h4>
					<table border='0' style='margin: 0px auto; text-align: center;'> 
						<tr> 
							<td> <button id='time_hour_up_$i' class='btn btn-small'><i class='icon-arrow-up'></i> </td> 
							<td>  </td> 
							<td> <button id='time_min_up_$i' class='btn btn-small'><i class='icon-arrow-up'></i> </td> 
						</tr> 
						<tr> 
							<td><h4><div id='time_hour_$i' class='span3'>".$hour."</div></h4></td>
							<td><h4>:</h4></td>
							<td><h4><div id='time_min_$i' class='span3'>".$min."</div></h4></td>
						</tr>
						<tr> 
							<td> <button id='time_hour_down_$i' class='btn btn-small'><i class='icon-arrow-down'></i> </td> 
							<td>  </td> 
							<td> <button id='time_min_down_$i' class='btn btn-small'><i class='icon-arrow-down'></i> </td> 
						</tr> 
					</table>
					<h4>Set duration :</h4>
					<table border='0' style='margin: 0px auto; text-align: center;'> 
						<tr> 
							<td> <button id='dur_hour_up_$i' class='btn btn-small'><i class='icon-arrow-up'></i> </td> 
							<td>  </td> 
							<td> <button id='dur_min_up_$i' class='btn btn-small'><i class='icon-arrow-up'></i> </td> 
						</tr> 
						<tr> 
							<td><h4><div id='dur_hour_$i' class='span3'>".$getAlarms['duration']['hour']."</div> h</h4></td>
							<td><h4></h4></td>
							<td><h4><div id='dur_min_$i' class='span3'>".$getAlarms['duration']['min']."</div> min</h4></td>
						</tr>
						<tr> 
							<td> <button id='dur_hour_down_$i' class='btn btn-small'><i class='icon-arrow-down'></i> </td> 
							<td>  </td> 
							<td> <button id='dur_min_down_$i' class='btn btn-small'><i class='icon-arrow-down'></i> </td> 
						</tr> 
					</table>
					<h4><abbr title=\"If you didn't select a day, then the alarm will be disabled\">Activate</abbr> :</h4>
					<div class='btn-group' data-toggle='buttons-checkbox'>";

				for($j=6;$j>=0;$j--) {
					if($getAlarms['days']%2 == 1)
						echo "<button id='".(6-$j)."_$i' type='button' class='btn active'>".$days[(6-$j)]."</button>";
					else
						echo "<button id='".(6-$j)."_$i' type='button' class='btn'>".$days[(6-$j)]."</button>";
					$getAlarms['days'] = intval($getAlarms['days']/2);
				}

				echo "	</div>
					</br></br>
					<label class='checkbox'>";
				if($getAlarms['reverse'] == 1)
					echo "<input name='inverse$i' type='checkbox' checked> <abbr title=\"By default the action are activate the output and after the given duration the output is deactive, you can inverse by checking this checkbox !\">Inverse the output</abbr>";
				else
					echo "<input name='inverse$i' type='checkbox'> <abbr title=\"By default the action are activate the output and after the given duration the output is deactive, you can inverse by checking this checkbox !\">Inverse the output</abbr>";
				echo "	</label>
					
				</div>
				<div class='modal-footer'>
					<button class='btn' data-dismiss='modal' aria-hidden='true'>Close</button>
					<button id='submitAlarm_$i' data-dismiss='modal' aria-hidden='true' class='btn btn-primary'>Save changes</button>
				</div>
			</div>";
	}

?>

    </div>
</body>
</html>
