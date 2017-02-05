<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/locale.inc.php");
	get_language_id();
	session_write_close();
include_once("../../lib/app.inc.php");

$id = (int)$_GET['id'];

$stop = new Stop($id);
$stop->GetInfo();

echo json_encode(
	array(
		"name" => $stop->Translate(get_language_id()),
		"busstop" => $stop->busstop,
		"location" => array(
			"lat" => $stop->location_lat,
			"lon" => $stop->location_lon
		)
	)
);
	
mysqli_close($connection);
ob_end_flush();
?>