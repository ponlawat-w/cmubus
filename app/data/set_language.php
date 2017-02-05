<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/locale.inc.php");

$id = str_replace("'", "", $_GET['id']);

$sql = "SELECT COUNT(*) AS 'count' FROM `languages` WHERE `id` = '$id' AND `available` = 1";
$result = mysqli_query($connection, $sql);
$data = mysqli_fetch_array($result);

if($data['count'] == 1)
{
	$_SESSION['user']['language'] = $id;
	session_write_close();

	echo "OK";
}

mysqli_close($connection); ob_end_flush(); ?>