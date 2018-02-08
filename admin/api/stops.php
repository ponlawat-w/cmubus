<?php session_start(); ob_start();

include_once("../../lib/lib.inc.php");
include_once("../library.inc.php");

check_authentication();

$now = mktime();

$return_data = array();

$i = 0;
$sql = "SELECT `id`, `name`, `location_lat`, `location_lon`, `busstop` FROM `stops` ORDER BY `name` ASC";
$results = mysqli_query($connection, $sql);
while($stopdata = mysqli_fetch_array($results))
{	
	$return_data[$i] = array(
		"id" => $stopdata['id'],
		"name" => $stopdata['name'],
		"location_lat" => $stopdata['location_lat'],
		"location_lon" => $stopdata['location_lon'],
		"busstop" => $stopdata['busstop']
	);
	
	$i++;
}

echo json_encode($return_data);

mysqli_close($connection); ob_end_flush(); ?>