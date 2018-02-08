<?php
include_once("../lib/lib.inc.php");
include_once("../lib/cron.inc.php");
include_once("../lib/app.inc.php");

$now = mktime();
$endtime = $now + 58;

do
{
	update_data();
	sleep(5);
}
while(mktime() < $endtime);

mysqli_close($connection);