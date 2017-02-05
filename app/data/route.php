<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$page_result = array();

$id = (int)$_GET['id'];

$routes = get_routes_at(mktime());

$routedata = array();

foreach($routes as $key => $route)
{
	if($route['id'] != $id)
	{
		continue;
	}
	
	$routes[$key]['name'] = get_text("route", $route['id'], get_language_id());
	unset($routes[$key]['refid']);
	unset($routes[$key]['detail']);
	unset($routes[$key]['available']);
	
	$sql = "SELECT MAX(`distance_from_start`) AS 'total_distance' FROM `route_paths` WHERE `route` = {$route['id']}";
	$result = mysqli_query($connection, $sql);
	$routepathdata = mysqli_fetch_array($result);
	
	$routes[$key]['distance'] = $routepathdata['total_distance'];
	
	if($route['id'] == $id)
	{
		$routedata = $routes[$key];
		break;
	}
}

$path = array();

$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = $id AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
$results = mysqli_query($connection, $sql);
while($stopdata = mysqli_fetch_array($results))
{
	array_push($path,
		array("stop" => $stopdata['stop'],
			"name" => get_text("stop", $stopdata['stop'], get_language_id())
		)
	);
}

$page_result['route_info'] = $routedata;
$page_result['path'] = $path;

echo json_encode($page_result);

mysqli_close($connection); ob_end_flush(); ?>