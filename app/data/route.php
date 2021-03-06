<?php ob_start(); session_start();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$page_result = array();

$id = (int)$_GET['id'];

$routes = get_routes_at(mktime());

$routedata = false;

foreach($routes as $key => $route)
{
	if($route['id'] != $id)
	{
		continue;
	}
	
	$routes[$key]['name'] = get_text("route", $route['id'], get_language_id());
	//unset($routes[$key]['refid']);
	unset($routes[$key]['detail']);
	unset($routes[$key]['available']);
	
	$sql = "SELECT MAX(`distance_from_start`) AS 'total_distance' FROM `route_paths` WHERE `route` = ?";
	$result = sql_query($sql, "i", array($route['id']));
	$routepathdata = mysqli_fetch_array($result);
	
	$routes[$key]['distance'] = $routepathdata['total_distance'];
	
	if($route['id'] == $id)
	{
		$routedata = $routes[$key];
		break;
	}
}

if(!$routedata)
{
    http_response_code(404);
    exit;
}

$path = array();

$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
$results = sql_query($sql, "i", array($id));
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

mysqli_close($connection); ob_end_flush();