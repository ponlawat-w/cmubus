<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/calendar.inc.php");
include_once("../../lib/locale.inc.php");
	get_language_id();
	session_write_close();
include_once("../../lib/app.inc.php");

$stopid = (int)$_GET['stopid'];

$final_timetable = array();

$stop = new Stop($stopid);
$timetable = sort_by($stop->TimeTable(), "estimated_time", SORT_ASC);

foreach($timetable as $key => $round)
{
	if($round['session'] != null && mktime() - $round['last_update'] > 60)
	{
		continue;
	}
	
	$route = new Route($round['route']);
	
	$laststop = null;
	$lastplace = "-";
	if($round['session'] != null)
	{
		$session = new Session($round['session']);
		$last_record = $session->LastRecord();
		
		$laststop = $last_record['stop'];
		
		if($last_record != null)
		{
			$lastplace = get_text("stop", $last_record['stop'], get_language_id());
		}
	}
	
	$timetable[$key]['routename'] = get_text("route", $round['route'], get_language_id());
	$timetable[$key]['routecolor'] = $route->color;
	$timetable[$key]['laststop'] = $laststop;
	$timetable[$key]['laststopname'] = $lastplace;
	
	array_push($final_timetable, $timetable[$key]);
}

$page_result = array(
	"arrival_timetable" => $final_timetable,
	"passed_timetable" => $stop->PassedTimetable(5)
);

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();
?>