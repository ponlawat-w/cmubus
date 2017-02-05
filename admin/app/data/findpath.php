<?php session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/calendar.inc.php");
include_once("../../lib/locale.inc.php");
include_once("../../lib/app.inc.php");

if(!isset($_GET['from']) || !isset($_GET['to']))
{
	exit;
}
$from = $_GET['from'];
$to = $_GET['to'];

$dt = mktime();
if(date("H", $dt) <= 7)
{
	$dt = mktime(12, 0, 0) - 86400;
}
else if(date("H", $dt) >= 22)
{
	$dt = mktime(12, 0, 0);
}

$from_stop = new Stop($from);
$results = $from_stop->findPathsTo($to, $dt, "totalTime", 2);

$page_result = array();

foreach($results as $path)
{
	array_push($page_result, $path->Readable());
}

echo json_encode($page_result);
	
mysqli_close($connection);
?>
