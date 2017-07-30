<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");

$id = str_replace("'", "", $_GET['id']);

$sql = "SELECT COUNT(*) AS 'count' FROM `languages` WHERE `id` = ? AND `available` = 1";
$result = sql_query($sql, "s", array($id));
$data = mysqli_fetch_array($result);

if($data['count'] == 1)
{
	$_SESSION['user']['language'] = $id;
	session_write_close();

	echo "OK";
}
else
{
    http_response_code(500);
    exit;
}

mysqli_close($connection); ob_end_flush();