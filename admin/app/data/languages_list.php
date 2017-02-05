<?php session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/locale.inc.php");

$languages = array();

$sql = "SELECT `id`, `name` FROM `languages` WHERE `available` = 1 ORDER BY `id` ASC";
$results = mysqli_query($connection, $sql);
while($languagedata = mysqli_fetch_array($results))
{
	array_push($languages, array("id" => $languagedata['id'], "name" => $languagedata['name']));
}

echo json_encode($languages);

mysqli_close($connection); ?>
