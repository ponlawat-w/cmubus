<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$id = (int)$_GET['id'];

$page_result = array();

$stop = new Stop($id);

$page_result = array("routes" => $stop->DailyTimetable(), "connections" => $stop->Connections(false, false, true));

foreach($page_result['connections'] as $key => $connect)
{
	$s = new Stop($connect['to']);
	$s->GetInfo();
	$page_result['connections'][$key]['name'] = get_text("stop", $connect['to'], get_language_id());
	$page_result['connections'][$key]['busstop'] = $s->BusStop;
}

$page_result['connections'] = sort_by($page_result['connections'], "name", SORT_ASC);

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();
?>