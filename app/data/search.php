<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$keyword = $_GET['keyword'];
$keyword = str_replace("'", "", $keyword);

echo json_encode(search($keyword, get_language_id(), 10));
mysqli_close($connection);
ob_end_flush();