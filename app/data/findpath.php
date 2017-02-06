<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

if(!isset($_GET['from']) || !isset($_GET['to']))
{
	exit;
}
$from = (int)$_GET['from'];
$to = (int)$_GET['to'];


$dt = mktime();
$sql = "INSERT INTO `search_logs` (`id`, `datetime`, `search_from`, `search_to`) VALUES (0, ?, ?, ?)";
sql_query($sql, "iii", array($dt, $from, $to));

if(date("H", $dt) <= 7 || date("H", $dt) >= 22)
{
	$dt = mktime(12, 0, 0);
}

$from_stop = new Stop($from);
$results = $from_stop->findPathsTo($to, $dt, 2);

$page_result = array();

foreach($results as $path)
{
	array_push($page_result, $path->Readable());
}

echo json_encode($page_result);
	
mysqli_close($connection);
ob_end_flush();
?>
