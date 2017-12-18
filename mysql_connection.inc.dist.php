<?php
/**
 * Template for /mysql_connection.inc.php
 */

$mysql_host = '';
$mysql_user = '';
$mysql_password = '';
$mysql_dbname = '';

$connection = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_dbname);
mysqli_query($connection, 'SET CHARSET UTF8');