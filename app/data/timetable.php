<?php ob_start(); session_start();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$stopid = (int)$_GET['stopid'];

$passedTimetable = "false";
if(isset($_GET['passed']))
{
    $passedTimetable = $_GET['passed'];
}

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
	$timetable[$key]['routecolor'] = '#' . $route->Color;
	$timetable[$key]['laststop'] = $laststop;
	$timetable[$key]['laststopname'] = $lastplace;
	
	array_push($final_timetable, $timetable[$key]);
}

if($passedTimetable == "true")
{
    $page_result = array(
        "arrival_timetable" => $final_timetable,
        "passed_timetable" => $stop->PassedTimetable(10)
    );
}
else
{
    $page_result = array(
        "arrival_timetable" => $final_timetable
    );
}

$page_result['current_time'] = date("H:i");

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();