<?php

  require_once('menu.php');
  require_once('backend/Config.php');
  require_once('backend/Sensors.php');

  // We check if the user is logged, if yes, the variables $username, $ip, $idUser are created
  include("backend/checkLogin.php");

  // Print the menu
  getMenu('sensors');

  $sensors = new Sensors();
  $sen = $sensors->getSensors();
  $nbSensors = sizeof($sen);

?>
  <!DOCTYPE html>
<html>
  <head>
    <meta charset = 'UTF-8'>
    <title>Main</title>
    <!--<script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>-->
    <script src="../bootstrap/js/highcharts.js"></script>
    <script src="../bootstrap/js/exporting.js"></script>
  </head>
  <body>

    <!-- Content -->
<div class="container">
<?php

$sensors = new Sensors();

// We print the containers and the script handling it
for($i=0;$i<$nbSensors;$i++) {
  
  echo "<legend><h3>".$sen[$i]['name']."</h3></legend>";
  echo "<h4>Statistics : </h4>";
  echo "<table class='table table-striped'>
              <thead>
                <tr>
                  <th>Last Measure</th>
                  <th><abbr title='Yesturday measure at the same hour'>Yesturday</abbr></th>
                  <th><abbr title='Average of the last 5 days at the same hour'>Average of the last 5 days</abbr></th>
                  <th><abbr title='Last month measure at the same hour'>Last month</abbr></th>
                  <th><abbr title='Last year measure at the same hour'>Last Year</abbr></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td id='lastMeasure$i'></td>
                  <td id='yesturday$i'></td>
                  <td id='avgLast5Days$i'></td>
                  <td id='lastMonth$i'></td>
                  <td id='lastYear$i'></td>
                </tr>
              </tbody>
            </table>";
  echo "<table class='table table-striped'>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Last hour</th>
                  <th>Last 24 hours</th>
                  <th id=textLastDay$i></th>
                  <th id=textLastNight$i></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Average</td>
                  <td id='lastHourAvg$i'></td>
                  <td id='last24HourAvg$i'></td>
                  <td id='lastDayAvg$i'></td>
                  <td id='lastNightAvg$i'></td>
                </tr>
                <tr>
                  <td>Minimum</td>
                  <td id='lastHourMin$i'></td>
                  <td id='last24HourMin$i'></td>
                  <td id='lastDayMin$i'></td>
                  <td id='lastNightMin$i'></td>
                </tr>
                <tr>
                  <td>Maximum</td>
                  <td id='lastHourMax$i'></td>
                  <td id='last24HourMax$i'></td>
                  <td id='lastDayMax$i'></td>
                  <td id='lastNightMax$i'></td>
                </tr>
              </tbody>
            </table>";
  echo "<h4>Plot : </h4></br><div id='container$i' style='min-width: 310px; height: 400px; margin: 0 auto'></div></br>";

   

  echo "<script type='text/javascript'>
// global
var chart$i;
var last$i = null; // the first time that the script will run this
// variable will be null but it's okay because no data is in the graph
// but after the variable will be updated to ask only the last measurements
// The first time, the post value will be null and like that the php script
// will now that all the data is needed

function addMeasure(data, place, unit) {

	$(place).empty();

	if(data != null)
		$(place).append(data+' '+unit);
	else
		$(place).append('No data available');
}


/**
 * Request data from the server, add it to the graph and set a timeout 
 * to request again
 */
function requestData$i() {
    $.ajax({
        url: 'backend/backendSensors.php',
	type: 'POST',
	data: {
		'lastTime': last$i,
		'address': '".$sen[$i]['address']."',
		'unit': '".$sen[$i]['unit']."'
	},
        success: function(data) {

	if(data != null && data.length != 0 && data['points'] != null && data['points'].length != 0)
	{

		var series = chart$i.series[0];
		var points = data['points'];
		var stats = data['stats'];

		///// Graph part ///////

		// We add also the point (except the last one) without redrawing and animatation
		for(var i =0;i<points.length-1;i++)
			chart$i.series[0].addPoint([parseFloat(points[i][0]) , parseFloat(points[i][1])], false, false,false);
		
		var shift = series.data.length > ".(RETRIEVE_GRAPH*3600/FREQ_SENSOR)."; // shift if the series is longer than 20

		// add the point
		chart$i.series[0].addPoint([parseFloat(points[points.length-1][0]) , parseFloat(points[points.length-1][1])], true, false);

		// we save the timestamp of the last added point to start the search in the DB after this one
		last$i = parseFloat(points[0][0]);

		///// Statistics part //////

		addMeasure((stats['lastMeasure'] == null) ? null : stats['lastMeasure']['value'], '#lastMeasure$i', '".$sen[$i]['unit']."');
		addMeasure((stats['yesturday'] == null) ? null : stats['yesturday']['value'], '#yesturday$i', '".$sen[$i]['unit']."');
		addMeasure(stats['textNight'], '#textLastNight$i', '".$sen[$i]['unit']."');
		addMeasure(stats['textDay'], '#textLastDay$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastHour'] == null) ? null : stats['lastHour']['average'], '#lastHourAvg$i', '".$sen[$i]['unit']."');
		addMeasure((stats['last24Hour'] == null) ? null : stats['last24Hour']['average'], '#last24HourAvg$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastDay'] == null) ? null : stats['lastDay']['average'], '#lastDayAvg$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastNight'] == null) ? null : stats['lastNight']['average'], '#lastNightAvg$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastHour'] == null) ? null : stats['lastHour']['min'], '#lastHourMin$i', '".$sen[$i]['unit']."');
		addMeasure((stats['last24Hour'] == null) ? null : stats['last24Hour']['min'], '#last24HourMin$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastDay'] == null) ? null : stats['lastDay']['min'], '#lastDayMin$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastNight'] == null) ? null : stats['lastNight']['min'], '#lastNightMin$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastHour'] == null) ? null : stats['lastHour']['max'], '#lastHourMax$i', '".$sen[$i]['unit']."');
		addMeasure((stats['last24Hour'] == null) ? null : stats['last24Hour']['max'], '#last24HourMax$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastDay'] == null) ? null : stats['lastDay']['max'], '#lastDayMax$i', '".$sen[$i]['unit']."');
		addMeasure((stats['lastNight'] == null) ? null : stats['lastNight']['max'], '#lastNightMax$i', '".$sen[$i]['unit']."');
		addMeasure(stats['avgLast5Days'], '#avgLast5Days$i', '".$sen[$i]['unit']."');
		addMeasure(stats['lastMonth'], '#lastMonth$i', '".$sen[$i]['unit']."');
		addMeasure(stats['lastYear'], '#lastYear$i', '".$sen[$i]['unit']."');

	}
            
	// call it again after one second	
	setTimeout(requestData$i, 6000);
 
        },
        cache: false
    });
}

$(function () {
    chart$i = new Highcharts.Chart({
        chart: {
		renderTo: 'container$i',
            type: 'spline',
	zoomType: 'x',
		events: {
                load: requestData$i
            }
        },
        title: {
            text: '".$sen[$i]['name']."'
        },
        xAxis: {
            type: 'datetime',
           // dateTimeLabelFormats: { // don't display the dummy year
             //   month: '%e. %b %y',
               // year: '%b'
            //},
            title: {
                text: 'Date'
            }
        },
        yAxis: {
            title: {
                text: '".$sen[$i]['name']." (".$sen[$i]['unit'].")'
            }
        },
        tooltip: {
            headerFormat: '<b>{series.name}</b><br>',
            pointFormat: '{point.x:%e. %b}: {point.y:.2f} ".$sen[$i]['unit']."'
        },

        series: [{
            name: '".$sen[$i]['name']."',
            data: []
        }]
    });
});
</script>";

}
?>
</div>
</body>

</html>
