<?php
include_once("html/cmubus/mysql_connection.inc.php");
include_once("html/cmubus/status/library.inc.php");
include_once("html/cmubus/lib/location.inc.php");

$now = mktime();
$endtime = $now + 58;

do
{
	update_data();
	sleep(5);
}
while(mktime() < $endtime);

mysqli_close($connection);
?>
