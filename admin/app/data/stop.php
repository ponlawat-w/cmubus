<?php session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/locale.inc.php");
include_once("../../lib/app.inc.php");

$id = $_GET['id'];

$stop = new Stop($id);

echo json_encode(array("name" => $stop->Translate(get_language_id())));
	
mysqli_close($connection);
?>