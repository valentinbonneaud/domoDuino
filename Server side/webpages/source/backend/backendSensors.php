<?php

require_once(dirname(__FILE__).'/DBConnect.php');
require_once(dirname(__FILE__).'/Config.php');
require_once(dirname(__FILE__).'/Sensors.php');

// We check if the user is logged, if yes, the variables $username, $ip, $idUser are created
include("checkLogin.php");

// Set the JSON header
header("Content-type: text/json");

$sensors = new Sensors();
$arrayRet = array();

// We load the points, if the lasttime is null then no data is on the graph so we have to it all the data
if($_POST['lastTime'] == null)
	$arrayRet['points'] = $sensors->getMeasuresSensor($_POST['address'],RETRIEVE_GRAPH*3600/FREQ_SENSOR);
else // otherwise we laod only the points after the last loaded point
	$arrayRet['points'] = $sensors->getMeasuresSince($_POST['address'],$_POST['lastTime']);

// We compute the statisctics
$stats = array();
$date = new DateTime();
$dateArray = getdate();
$now = ($date->getTimestamp())*1000;

// last hour
$stats['lastHour'] = $sensors->getStatisticsSince($_POST['address'],$now - 3600*1000);

// last 24h
$stats['last24Hour'] = $sensors->getStatisticsSince($_POST['address'],$now - 24*3600*1000);

// last day
 
// if we are during the day, then the previous day is starting at D=day-1,H=END_NIGHT_HOUR
// and finished at D=day-1,H=END_DAY_HOUR otherwise if we are during the night with hour>END_DAY_HOUR
// then the previous day is starting at D=day,H=END_NIGHT_HOUR and finished at D=day,H=END_DAY_HOUR or
// if hour<END_NIGHT_HOUR then the previous day is starting at D=day-1,H=END_NIGHT_HOUR and finished 
// at D=day-1,H=END_DAY_HOUR
// if $dateArray['hours'] < END_NIGHT_HOUR (we are during the night), we have to remove one day

$startLastDay = new DateTime();
$startLastDay->setDate($dateArray['year'],$dateArray['mon'],$dateArray['mday']-1);
$startLastDay->setTime(END_NIGHT_HOUR,0);
 
$endLastDay = new DateTime();
$endLastDay->setDate($dateArray['year'],$dateArray['mon'],$dateArray['mday']-1);
$endLastDay->setTime(END_DAY_HOUR,0);

if($dateArray['hours'] > END_DAY_HOUR) {
  $startLastDay->add(new DateInterval('P1D'));
  $endLastDay->add(new DateInterval('P1D'));	
} 

$stats['lastDay'] = $sensors->getStatisticsBetween($_POST['address'],($startLastDay->getTimestamp())*1000,($endLastDay->getTimestamp())*1000);

// last night

// if we are during the day, then the previous night is starting at D=day-1,H=END_DAY_HOUR
// and finished at D=day,H=END_NIGHT_HOUR otherwise if we are during the night, the previous 
// night is starting at D=day-2,H=END_DAY_HOUR and finished at D=day-1,H=END_NIGHT_HOUR so
// if $dateArray['hours'] < END_NIGHT_HOUR (we are during the night), we have to remove one day

$startLastNight = new DateTime();
$startLastNight->setDate($dateArray['year'],$dateArray['mon'],$dateArray['mday']-1);
$startLastNight->setTime(END_DAY_HOUR,0);
 
$endLastNight = new DateTime();
$endLastNight->setTime(END_NIGHT_HOUR,0);

if($dateArray['hours'] < END_NIGHT_HOUR) {
  $startLastNight->sub(new DateInterval('P1D'));
  $endLastNight->sub(new DateInterval('P1D'));	
} 

$stats['lastNight'] = $sensors->getStatisticsBetween($_POST['address'],($startLastNight->getTimestamp())*1000,($endLastNight->getTimestamp())*1000);

// last value
$stats['lastMeasure'] = $sensors->getClosestMeasure($_POST['address'], $now);

// value of yesturday
$stats['yesturday'] = $sensors->getClosestMeasure($_POST['address'], $now - 24*3600*1000);

// average of the last 5 days
$avg = 0;
$nbAvg = 0;

for($i = 1; $i <= 5; $i++) {
  $current = $sensors->getClosestMeasure($_POST['address'], $now - $i*24*3600*1000);
  if($current != null) { // if some data is available we take it !
    $nbAvg++;
    $avg += $current['value'];
  }
}

if($nbAvg > 0)
	$stats['avgLast5Days'] = round($avg/$nbAvg,2);
else
	$stats['avgLast5Days'] = null;

// last month
$stats['lastMonth'] = $sensors->getClosestMeasure($_POST['address'], $now - 31*24*3600*1000);

// last year
$stats['lastYear'] = $sensors->getClosestMeasure($_POST['address'], $now - 365*24*3600*1000);

$stats['textDay'] = "<abbr title='Between ".$startLastDay->format("F jS\, l H:i")." and ".$endLastDay->format("F jS\, l H:i")."'>Last day</abbr>";
$stats['textNight'] = "<abbr title='Between ".$startLastNight->format("F jS\, l H:i")." and ".$endLastNight->format("F jS\, l H:i")."'>Last night</abbr>";

$arrayRet['stats'] = $stats;

// Create a PHP array and echo it as JSON
echo json_encode($arrayRet);
?>
