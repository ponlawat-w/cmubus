<?php include_once(__DIR__ . '/../config.inc.php');

$connection = mysqli_connect($MYSQL_HOST, $MYSQL_USERNAME, $MYSQL_PASSWORD, $MYSQL_DATABASE);
mysqli_query($connection, 'SET CHARSET UTF8');

date_default_timezone_set('Asia/Bangkok');