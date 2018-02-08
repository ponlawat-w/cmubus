<?php session_start(); session_write_close();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
include_once("../library.php");

$sql = "TRUNCATE `stats_estimation_accuracy`";
sql_query($sql);

$onlineSessions = array();
$sql = "SELECT `session` FROM `buses` WHERE `session` > 0";
$results = sql_query($sql);
while($onlineSessionData = $results->fetch_array())
{
    array_push($onlineSessions, $onlineSessionData['session']);
}

$pageResult = array();

$sql = "SELECT `id` FROM `sessions`";
$results = sql_query($sql);
while($sessionData = $results->fetch_array()) if(!in_array($sessionData['id'], $onlineSessions))
{
    array_push($pageResult, $sessionData['id']);
}

echo json_encode($pageResult);