<?php
include_once("html/cmubus/mysql_connection.inc.php");

$now = mktime();
$sql = "SELECT `id`, `route`, `set_available_to` FROM `route_available_switchers` WHERE `date` <= $now ORDER BY `date` ASC, `id` ASC";
$results = mysqli_query($connection, $sql);
while($switcherdata = mysqli_fetch_array($results))
{
	$sql = "UPDATE `routes` SET `available` = {$switcherdata['set_available_to']} WHERE `id` = {$switcherdata['route']}";
	mysqli_query($connection, $sql);

	$sql = "DELETE FROM `route_available_switchers` WHERE `id` = {$switcherdata['id']}";
	mysqli_query($connection, $sql);
}

mysqli_close($connection);
?>

