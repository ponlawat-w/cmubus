<?php session_start();
	include_once("../../mysql_connection.inc.php");
	include_once("../../lib/locale.inc.php");
	include_once("../../lib/search.inc.php");
	
	$keyword = $_GET['keyword'];
	
	echo json_encode(search($keyword, get_language_id(), 10));
mysqli_close($connection); ?>