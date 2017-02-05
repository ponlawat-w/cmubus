<?php session_start(); ob_start();

include_once("../../mysql_connection.inc.php");
include_once("../library.inc.php");

check_authentication();

$route_id = $_GET['route_id'];

$return_result = array();

$i = 0;
$sql = "SELECT `id`, `distance_from_start`, `location_lat`, `location_lon`, `stop` FROM `route_paths` WHERE `route` = $route_id ORDER BY `distance_from_start` ASC";
$results = mysqli_query($connection, $sql);
while($sequencedata = mysqli_fetch_array($results))
{	
	$return_result[$i] = array(
		"id" => $sequencedata['id'],
		"order" => $i + 1,
		"distance_from_start" => $sequencedata['distance_from_start'],
		"location_lat" => $sequencedata['location_lat'],
		"location_lon" => $sequencedata['location_lon'],
		"stop" => $sequencedata['stop'],
		"display_location" => ""
	);
		
	if($sequencedata['stop'] == null)
	{
		$return_result[$i]['display_location'] = "{$sequencedata['location_lat']},{$sequencedata['location_lon']}";
	}
	else
	{
		$sql = "SELECT `name`, `location_lat`, `location_lon` FROM `stops` WHERE `id` = {$sequencedata['stop']}";
		$result = mysqli_query($connection, $sql);
		$stopdata = mysqli_fetch_array($result);
		
		$return_result[$i]['display_location'] = $stopdata['name'];
		$return_result[$i]['location_lat'] = $stopdata['location_lat'];
		$return_result[$i]['location_lon'] = $stopdata['location_lon'];
	}
	
	$i++;
}

echo json_encode($return_result);

mysqli_close($connection); ob_end_flush(); ?>