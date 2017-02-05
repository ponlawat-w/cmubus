<?php session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/calendar.inc.php");
include_once("../../lib/locale.inc.php");
include_once("../../lib/app.inc.php");

$stopid = $_GET['stopid'];

$page_result = array();

$stop = new Stop($stopid);
$timetable = sort_by($stop->TimeTable(), "estimated_time", SORT_ASC);

foreach($timetable as $key => $round)
{
	if($session != null && mktime() - $round['last_update'] > 60)
	{
		continue;
	}
	
	$route = new Route($round['route']);
	
	//$lastplace = "";
	//if($round['session'] != null)
	//{
	//	$session = new Session($round['session']);
	//	$last_record = $session->LastRecord();
	//	if($last_record != null)
	//	{
	//		$lastplace = get_text("stop", $last_record['stop'], get_language_id());
	//	}
	//}
	
	$timetable[$key]['routename'] = get_text("route", $round['route'], get_language_id());
	$timetable[$key]['routecolor'] = $route->Color;
	
	array_push($page_result, $timetable[$key]);
}

echo json_encode($page_result);
	
mysqli_close($connection);
?>