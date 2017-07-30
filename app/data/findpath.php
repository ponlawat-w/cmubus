<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

if(!isset($_GET['from']) || !isset($_GET['to']))
{
	echo json_encode(array());
	exit;
}
$from = (int)$_GET['from'];
$to = (int)$_GET['to'];

$limit = 1;
if(isset($_GET['limit']))
{
	$limit = $_GET['limit'];
}

$quick = false;
if(isset($_GET['quick']) && $_GET['quick'] == "true")
{
	$quick = true;
}

$record = false;
if(isset($_GET['record']) && $_GET['record'] == "true")
{
	$record = true;
}

$dt = mktime();

if($record)
{
	$sql = "INSERT INTO `search_logs` (`id`, `datetime`, `search_from`, `search_to`) VALUES (0, ?, ?, ?)";
	sql_query($sql, "iii", array($dt, $from, $to));
}

if(date("H", $dt) <= 7 || date("H", $dt) >= 22)
{
	$dt = mktime(12, 0, 0);
}

$from_stop = new Stop($from);
$results = $from_stop->findPathsTo($to, $dt, $limit, $quick);

$page_result = array();

foreach($results as $path)
{
	array_push($page_result, $path->Readable());
}

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();