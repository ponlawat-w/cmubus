<?php
	include_once("../lib/lib.inc.php");
	include_once("../lib/cron.inc.php");
	
	$now = mktime();
	$sql = "UPDATE `data_buses` SET `session` = 0, `last_update` = $now";
	mysqli_query($connection, $sql);
	
	$starttime = mktime(6, 30);
	$endtime = mktime(22, 30);
	
	$num_stops = array();
	
	for($i = 1; $i <= 6; $i++)
	{
		$sql = "SELECT COUNT(*) AS `num_stops` FROM `route_paths` WHERE `route` = $i AND `stop` IS NOT NULL";
		$result = mysqli_query($connection, $sql);
		$num_stops_data = mysqli_fetch_array($result);
		$num_stops[$i] = $num_stops_data['num_stops'];
	}
	
	$sql = "SELECT `id`, `route` FROM `sessions` WHERE `start_datetime` BETWEEN $starttime AND $endtime";
	$results = mysqli_query($connection, $sql);
	while($sessiondata = mysqli_fetch_array($results))
	{		
		$sql = "SELECT COUNT(*) AS `num_stopped` FROM `records` WHERE `session` = {$sessiondata['id']}";
		$result = mysqli_query($connection, $sql);
		$num_stopped_data = mysqli_fetch_array($result);
		
		$completeness = $num_stopped_data['num_stopped'] / $num_stops[$sessiondata['route']];
		
		if($completeness < 0.5)
		{
			$sql = "DELETE FROM `records` WHERE `session` = {$sessiondata['id']}";
			mysqli_query($connection, $sql);
			
			$sql = "DELETE FROM `sessions` WHERE `id` = {$sessiondata['id']}";
			mysqli_query($connection, $sql);
		}
	}
	
	mysqli_close($connection);