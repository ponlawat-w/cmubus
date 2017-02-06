<?php ob_start(); session_start(); session_write_close();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");

$post_data = json_decode(file_get_contents('php://input'));

foreach($post_data as $key => $value)
{
	$post_data->$key = mysqli_real_escape_string($connection, $value);
}

$type = $post_data->type;
$name = $post_data->name;
$email = $post_data->email;
$message = $post_data->message;

$now = mktime();

$sql = "INSERT INTO `reports` (`id`, `type`, `name`, `email`, `message`, `datetime`) VALUES (0, ?, ?, ?, ?, ?)";
sql_query($sql, "ssssi", array($type, $name, $email, $message, $now));

mysqli_close($connection);
ob_end_flush(); ?>