<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
	get_language_id();
	session_write_close();

$lat = (double)$_GET['lat'];
$lon = (double)$_GET['lon'];

$limit = 1;
if(isset($_GET['limit']))
{
    $limit = $_GET['limit'];
}

$stoponly = "true";
if(isset($_GET['limit']))
{
    $stoponly = $_GET['$stoponly'];
}

$timetable = "false";
if(isset($_GET['$timetable']))
{
    $timetable = $_GET['$timetable'];
}

if($stoponly == "true")
{
	$busstop_str = "`busstop` = 1";
}
else
{
	$busstop_str = "TRUE";
}

$page_result = array();

$sql = "SELECT `id`,
			6371000 * 2 *
			ASIN(
				SQRT(
					POWER(
						SIN((? - abs(`location_lat`)) * PI() / 180 / 2)
					, 2) +
					COS(? * PI() / 180) *
					POWER(
						SIN((? - `location_lon`) * PI() / 180 / 2)
					, 2)
				)
			) AS `distance`
		FROM `stops` WHERE $busstop_str
		ORDER BY `distance` LIMIT ?;";
$results = sql_query($sql, "dddi", array($lat, $lat, $lon, $limit));
while($stopdata = mysqli_fetch_array($results))
{
	if($stopdata['distance'] > 5000)
	{
		continue;
	}
	
	$stop = new Stop($stopdata['id']);
	
	$routes = $stop->PassingRoutes();
	
	$stop_timetable = array();
	
	if($timetable == "true")
	{
		$stop_timetable = $stop->TimeTable();
		
		foreach($routes as $key => $route)
		{
			$routes[$key]['timetable'] = array();
			
			foreach($stop_timetable as $round)
			{
				if($round['route'] == $routes[$key]['id'])
				{
					array_push($routes[$key]['timetable'], $round);
				}
			}
			
			$routes[$key]['timetable'] = sort_by($routes[$key]['timetable'], "estimated_time", SORT_ASC);
		}
	}
	
	array_push($page_result,
		array("id"=>$stopdata['id'],
				"distance" => $stopdata['distance'],
				"routes" => $routes,
				"name"=>get_text("stop", $stopdata['id'], get_language_id())
			)
	);
}

echo json_encode($page_result);
mysqli_close($connection);
ob_end_flush(); ?>