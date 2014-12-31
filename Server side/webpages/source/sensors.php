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

		$('#lastMeasure$i').empty();
		$('#lastMeasure$i').append(stats['lastMeasure']['value']+' ".$sen[$i]['unit']."');
		$('#yesturday$i').empty();
		$('#yesturday$i').append(stats['yesturday']['value']+' ".$sen[$i]['unit']."');

		$('#textLastDay$i').empty();
		$('#textLastDay$i').append(stats['textDay']);
		$('#textLastNight$i').empty();
		$('#textLastNight$i').append(stats['textNight']);
		$('#lastHourAvg$i').empty();
		$('#lastHourAvg$i').append(stats['lastHour']['average']+' ".$sen[$i]['unit']."');
		$('#last24HourAvg$i').empty();
		$('#last24HourAvg$i').append(stats['last24Hour']['average']+' ".$sen[$i]['unit']."');
		$('#lastDayAvg$i').empty();
		$('#lastDayAvg$i').append(stats['lastDay']['average']+' ".$sen[$i]['unit']."');
		$('#lastNightAvg$i').empty();
		$('#lastNightAvg$i').append(stats['lastNight']['average']+' ".$sen[$i]['unit']."');
		$('#lastHourMin$i').empty();
		$('#lastHourMin$i').append(stats['lastHour']['min']+' ".$sen[$i]['unit']."');
		$('#last24HourMin$i').empty();
		$('#last24HourMin$i').append(stats['last24Hour']['min']+' ".$sen[$i]['unit']."');
		$('#lastDayMin$i').empty();
		$('#lastDayMin$i').append(stats['lastDay']['min']+' ".$sen[$i]['unit']."');
		$('#lastNightMin$i').empty();
		$('#lastNightMin$i').append(stats['lastNight']['min']+' ".$sen[$i]['unit']."');
		$('#lastHourMax$i').empty();
		$('#lastHourMax$i').append(stats['lastHour']['max']+' ".$sen[$i]['unit']."');
		$('#last24HourMax$i').empty();
		$('#last24HourMax$i').append(stats['last24Hour']['max']+' ".$sen[$i]['unit']."');
		$('#lastDayMax$i').empty();
		$('#lastDayMax$i').append(stats['lastDay']['max']+' ".$sen[$i]['unit']."');
		$('#lastNightMax$i').empty();
		$('#lastNightMax$i').append(stats['lastNight']['max']+' ".$sen[$i]['unit']."');
		$('#avgLast5Days$i').empty();
		if(stats['avgLast5Days'] != null)
			$('#avgLast5Days$i').append(stats['avgLast5Days']+' ".$sen[$i]['unit']."');
		else
			$('#avgLast5Days$i').append('No data available');
		$('#lastMonth$i').empty();
		if(stats['lastMonth'] != null)
			$('#lastMonth$i').append(stats['lastMonth']+' ".$sen[$i]['unit']."');
		else
			$('#lastMonth$i').append('No data available');
		$('#lastYear$i').empty();
		if(stats['lastYear'] != null)
			$('#lastYear$i').append(stats['lastYear']+' ".$sen[$i]['unit']."');
		else
			$('#lastYear$i').append('No data available');
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
