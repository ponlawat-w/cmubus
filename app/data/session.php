<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$id = (int)$_GET['id'];

$page_result = array();

$session = new Session($id);

$page_result = $session->GetStatus();

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();
?>