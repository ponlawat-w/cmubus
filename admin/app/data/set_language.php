<?php session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/locale.inc.php");

$id = urlencode($_GET['id']);

$sql = "SELECT COUNT(*) AS 'count' FROM `languages` WHERE `id` = '$id' AND `available` = 1";
$result = mysqli_query($connection, $sql);
$data = mysqli_fetch_array($result);

if($data['count'] == 1)
{
	$_SESSION['user']['language'] = $id;

	echo "OK";
}

mysqli_close($connection); ?>