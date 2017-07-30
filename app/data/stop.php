<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$id = (int)$_GET['id'];

$stop = new Stop($id);
$stop->GetInfo();

if(!$stop->Name)
{
    http_response_code(404);
    exit;
}

echo json_encode(
	array(
		"name" => get_text("stop", $stop->ID, get_language_id()),
		"busstop" => $stop->BusStop,
		"location" => array(
			"lat" => $stop->Location->lat,
			"lon" => $stop->Location->lon
		)
	)
);
	
mysqli_close($connection);
ob_end_flush();