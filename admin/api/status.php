<?php session_start(); ob_start();

include_once("../../lib/lib.inc.php");
include_once("../library.inc.php");

check_authentication();

$now = mktime();

$return_data = array();

$i = 0;
$sql = "SELECT `buses`.`id`, `route`, `routes`.`name`, `color`, `session`, `last_distance`, `rotation`, `last_update` FROM `buses`, `routes` WHERE `buses`.`route` = `routes`.`id` AND `session` > 0 ORDER BY `route` ASC, `id` ASC";
$results = mysqli_query($connection, $sql);
while($busdata = mysqli_fetch_array($results))
{
	$busno = $busdata['id'];
	$route = $busdata['name'];
	$route_color = $busdata['color'];
	$last_update = $busdata['last_update'];
	$session = $busdata['session'];
	
	$sql = "SELECT MAX(`distance_from_start`) AS `final_distance` FROM `route_paths` WHERE `route` = {$busdata['route']}";
	$result = mysqli_query($connection, $sql);
	$finaldata = mysqli_fetch_array($result);
	$finaldistance = $finaldata['final_distance'];
	
	$percent = round(($busdata['last_distance'] / $finaldistance) * 100);
	$distance_left = $finaldistance - $busdata['last_distance'];
	
	$sql = "SELECT `stop`, `sequence`, `distance_from_start`, `name` FROM `route_paths`, `stops` WHERE `stop` = `stops`.`id` AND `distance_from_start` > {$busdata['last_distance']} AND `route` = {$busdata['route']} AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC LIMIT 1";
	$result = mysqli_query($connection, $sql);
	$nextstopdata = mysqli_fetch_array($result);
	$nextstopname = $nextstopdata['name'];
	
	$distance_to_nextstop = $nextstopdata['distance_from_start'] - $busdata['last_distance'];
	
	$return_data[$i] = array(
		"busno" => $busno,
		"route" => $route,
		"route_color" => $route_color,
		"last_update" => $last_update,
		"session" => $session,
		"percent" => $percent,
		"distance_left" => $distance_left,
		"nextstop" => $nextstopname,
		"distance_to_nextstop" => $distance_to_nextstop
	);
	
	$i++;
}

echo json_encode($return_data);

mysqli_close($connection); ob_end_flush(); ?>