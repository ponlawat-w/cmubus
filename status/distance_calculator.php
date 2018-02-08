<?php
	function status($str)
	{
		echo "$str<br>";
	}

	include_once("../lib/lib.inc.php");
include_once("../lib/cron.inc.php");
include_once("../lib/app.inc.php");
	
	status("READ ALL ROUTES");
	
	$sql = "SELECT `id` FROM `routes` ORDER BY `id` ASC";
	$results = mysqli_query($connection, $sql);
	while($routedata = mysqli_fetch_array($results))
	{
		status("ROUTE #{$routedata['id']}");
		
		$prevloc = null;
		$distance = 0;
		
		$i = 0;
		$sql = "SELECT `id`, `sequence`, `location_lat`, `location_lon` FROM `route_paths` WHERE `route` = {$routedata['id']} ORDER BY `sequence` ASC";
		$results2 = mysqli_query($connection, $sql);
		while($sequencedata = mysqli_fetch_array($results2))
		{
			$newloc = seq2loc($routedata['id'], $sequencedata['sequence']);
			$nowid = $sequencedata['id'];
			
			if($i > 0)
			{
				$distance += $prevloc->DistanceTo($newloc);
				
				$sql = "UPDATE `route_paths` SET `distance_from_start` = $distance WHERE `id` = $nowid";
				mysqli_query($connection, $sql);
			}
			
			$prevloc = $newloc;
			
			$i++;
		}
	}
	
	mysqli_close($connection);
?>