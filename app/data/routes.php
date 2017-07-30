<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$page_result = array();

$routes = get_available_route_on(new Day(mktime(0, 0, 0)));

foreach($routes as $key => $route)
{
	$routes[$key]['name'] = get_text("route", $route['id'], get_language_id());
	$routes[$key]['color'] = '#' . $routes[$key]['color'];
//	unset($routes[$key]['refid']);
	unset($routes[$key]['detail']);
	unset($routes[$key]['available']);
	
	$sql = "SELECT MAX(`distance_from_start`) AS 'total_distance' FROM `route_paths` WHERE `route` = ?";
	$result = sql_query($sql, "i", array($route['id']));
	$routepathdata = mysqli_fetch_array($result);
	
	$routes[$key]['distance'] = $routepathdata['total_distance'];
	
	$stops = array();
	$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
	$results = sql_query($sql, "i", array($route['id']));
	while($stopdata = mysqli_fetch_array($results))
	{
		array_push($stops, $stopdata['stop']);
	}
	
	$sampled_stops = array();
	$pushed = array();
	
	$max = 4;
	for($i = 0; $i <= $max; $i ++)
	{
		$percentile = $i / $max;
		
		$order = floor((count($stops) - 1) * $percentile);
		
		if(!isset($stops[$order]))
		{
			continue;
		}
		
		if(!in_array($order, $pushed))
		{
			array_push($pushed, $order);
			array_push($sampled_stops,
				array("id" => $stops[$order], "name" => get_text("stop", $stops[$order], get_language_id()))
			);
		}
	}
	
	$routes[$key]['sampled_stops'] = $sampled_stops;

	array_push($page_result, $routes[$key]);
}

$page_result = sort_by($page_result, "name", SORT_ASC);
$page_result = sort_by($page_result, "refid", SORT_ASC);

echo json_encode($page_result);

mysqli_close($connection); ob_end_flush(); ?>
