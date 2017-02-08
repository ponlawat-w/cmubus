<?php ob_start(); session_start(); session_write_close();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");

$post_data = json_decode(file_get_contents('php://input'));

$role = $post_data->role;
$name = $post_data->name;
$email = $post_data->email;
$usefulness = $post_data->usefulness;
$easyToUse = $post_data->easyToUse;
$accuracy = $post_data->accuracy;
$performance = $post_data->performance;
$satisfaction = $post_data->satisfaction;
$comment = $post_data->comment;

$now = mktime();

$sql = "INSERT INTO `evaluation_survey` (`id`, `role`, `name`, `email`, `usefulness`, `easy_to_use`, `accuracy`, `performance`, `satisfaction`, `comment`, `datetime`) VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
sql_query($sql, "sssiiiiisi", array($role, $name, $email, $usefulness, $easyToUse, $accuracy, $performance, $satisfaction, $comment, mktime()));

mysqli_close($connection);
ob_end_flush(); ?>