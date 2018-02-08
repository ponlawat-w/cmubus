<?php ob_start(); session_start();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$id = (int)$_GET['id'];

$page_result = array();

$session = new Session($id);

if(!$session->BusNo)
{
    http_response_code(404);
    exit;
}

$page_result = $session->GetStatus();

$page_result['current_time'] = date('H:i');

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();