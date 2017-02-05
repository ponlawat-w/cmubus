<?php session_start(); ob_start();

include_once("../../mysql_connection.inc.php");
include_once("../library.inc.php");

check_authentication();

$now = mktime();

$return_data = array();

$i = 0;
$sql = "SELECT `id`, `refid`, `color`, `name`, `available`, `detail` FROM `routes` ORDER BY `id` ASC";
$results = mysqli_query($connection, $sql);
while($routedata = mysqli_fetch_array($results))
{
	$sql = "SELECT MAX(`distance_from_start`) AS `total_distance` FROM `route_paths` WHERE `route` = {$routedata['id']}";
	$result = mysqli_query($connection, $sql);
	$distancedata = mysqli_fetch_array($result);
	
	$return_data[$i] = array(
		"id" => $routedata['id'],
		"refid" => $routedata['refid'],
		"color" => $routedata['color'],
		"name" => $routedata['name'],
		"total_distance" => $distancedata['total_distance'],
		"available" => $routedata['available'],
		"detail" => $routedata['detail']
	);
	
	$i++;
}

echo json_encode($return_data);

mysqli_close($connection); ob_end_flush(); ?>