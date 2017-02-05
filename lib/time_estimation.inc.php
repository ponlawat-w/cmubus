<?php

## calendar.inc.php is required ##

function estimate()
{
	$today = new Day(mktime(0, 0, 0));
	estimate_on($today);
}

function estimate_on($day)
{
	global $connection;
	
	## VARIABLES ##
	
	$starttime = 23400; // 06:30
	$endtime = 81000; // 22:30
	$interval = 3600; // 30 minutes
	
	$calculated_day_amount = 15;
	
	###############
	
	$today = $day;
	$max_today = $today->timestamp + 86399;
	
	$today_periods = array();
	for($i = $today->timestamp + $starttime; $i <= $today->timestamp + $endtime; $i += $interval)
	{
		array_push($today_periods, array(
			"lower" => $i,
			"upper" => $i + $interval - 1
		));
	}
	
	$sql = "DELETE FROM `time_estimation` WHERE `start_time` BETWEEN {$today->timestamp} AND $max_today OR `end_time` BETWEEN {$today->timestamp} AND $max_today";
	mysqli_query($connection, $sql);
	
	$route_ids = array();
	
	$i = 0;
	foreach(get_routes_at($today->timestamp) as $route)
	{
		if($route['available'] == 1)
		{
			$route_ids[$i] = $route['id'];
		}
		
		$i++;
	}
	
	$sql = "SELECT `route` FROM `route_available_switchers` WHERE `set_available_to` = 1 AND `date` BETWEEN {$today->timestamp} AND $max_today";
	$results = mysqli_query($connection, $sql);
	while($switcherdata = mysqli_fetch_array($results))
	{
		if(!in_array($switcherdata['route'], $route_ids))
		{
			$route_ids[$i] = $switcherdata['route'];
			$i++;
		}
	}
	
	$periods = array();
	
	$n = 0;
	$sql = "SELECT `date` FROM `days` WHERE `type` = {$today->type} AND `date` < {$today->timestamp} ORDER BY `date` DESC LIMIT $calculated_day_amount";
	$results = mysqli_query($connection, $sql);
	while($datedata = mysqli_fetch_array($results))
	{
		for($i = $datedata['date'] + $starttime; $i <= $datedata['date'] + $endtime; $i += $interval)
		{
			$upper = $i + $interval - 1;
			
			$periods[$n] = array(
				"lower" => $i,
				"upper" => $upper
			);
			$n++;
		}
	}
	
	$session_ids = array();
	$sessions = array();
	
	$sql = "SELECT `id`, `route`, `start_datetime` FROM `sessions` WHERE " . generate_query($periods, "start_datetime");
	$results = mysqli_query($connection, $sql);
	while($sessiondata = mysqli_fetch_array($results))
	{		
		array_push($session_ids, $sessiondata['id']);
		$sessions[$sessiondata['id']] = $sessiondata;		
	}
	
	$data = array();	// route >> start_time >> end_time >> stop >> time_array
	
	$sessions_str = implode(", ", $session_ids);
	$sql = "SELECT `id`, `stop`, `datetime`, `session` FROM `records` WHERE `session` IN ({$sessions_str}) ORDER BY `datetime` ASC";
	$results = mysqli_query($connection, $sql);
	while($recorddata = mysqli_fetch_array($results))
	{
		$sessiondata = $sessions[$recorddata['session']];
		
		$period = find_period($periods, $sessiondata['start_datetime']);
		
		$lower = shift_day($period['lower'], $today);
		$upper = shift_day($period['upper'], $today);
		
		if(!isset($data[$sessiondata['route']][$lower][$upper][$recorddata['stop']]))
		{
			$data[$sessiondata['route']][$lower][$upper][$recorddata['stop']] = array();
		}
		
		if($recorddata['datetime'] == $sessiondata['start_datetime'])
		{
			continue;
		}
		
		array_push($data[$sessiondata['route']][$lower][$upper][$recorddata['stop']], $recorddata['datetime'] - $sessiondata['start_datetime']);
	}
	
	$i = 0;
	$estimateds = array();
	foreach($data as $route => $route_)
	{
		foreach($route_ as $start_time => $start_time_)
		{
			foreach($start_time_ as $end_time => $end_time_)
			{
				$waittime = null;
				
				$j = 0;
				foreach($end_time_ as $stop => $stop_)
				{
					if($j == 1)
					{
						$waittime = wait_time_at($stop, $route, round(($start_time + $end_time) / 2));
						break;
					}
					$j++;
				}
				
				foreach($end_time_ as $stop => $stop_)
				{
					$sum = 0;
					$n = 0;
					foreach($stop_ as $time)
					{
						$sum += $time;
						$n++;
					}
					
					if($sum > 0 || $n > 0)
					{
						$avg = round($sum / $n);
						
						$estimateds[$i] = array(
							"route" => $route,
							"start_time" => $start_time,
							"end_time" => $end_time,
							"stop" => $stop,
							"estimated_time" => $avg,
							"waittime" => $waittime
						);
						$i++;
					}
				}
			}
		}
	}
	
	foreach($estimateds as $estimated)
	{
		$sql = "INSERT INTO `time_estimation` (`id`, `route`, `start_time`, `end_time`, `stop`, `estimated_time`, `waittime`) VALUES (0, {$estimated['route']}, {$estimated['start_time']}, {$estimated['end_time']}, {$estimated['stop']}, {$estimated['estimated_time']}, {$estimated['waittime']})";
		mysqli_query($connection, $sql);
	}
	
	echo "Time Estimation (" . date("Y-m-d", $day->timestamp) . ") - OK";
}

function find_period($periods, $time)
{
	foreach($periods as $period)
	{
		if($time >= $period['lower'] && $time <= $period['upper'])
		{
			return $period;
		}
	}
	
	return false;
}

function generate_query($array, $field_name)
{
	$txts = array();
	
	$i = 0;
	foreach($array as $data)
	{
		$txts[$i] = "(`{$field_name}` BETWEEN {$data['lower']} AND {$data['upper']})";
		$i++;
	}
	
	return implode(" OR ", $txts);
}

## Function Wait Time At
#### Return approximated waiting time at specificed route, busstop and time
function wait_time_at($stop, $route, $datetime = false)
{	
	global $connection;	
	
	// Default time is NOW
	if($datetime == false)
	{
		$datetime = mktime();
	}
	
	// Calculate using 30 minutes bound
	$time = ($datetime + date("Z", $datetime)) % 86400;
	$bound = 1800;
	
	$lower = $time - ($bound / 2);
	$upper = $time + ($bound / 2);
	
	$calculated_day_amount = 10;
	
	$day = get_day_from_timestamp($datetime);
	
	$days = array();	
	$sqltxts = array();
	
	$sql = "SELECT `date` FROM `days` WHERE `type` = {$day->Type} AND `date` < {$day->Timestamp} ORDER BY `date` DESC LIMIT {$calculated_day_amount}";
	$results = mysqli_query($connection, $sql);
	while($daydata = mysqli_fetch_array($results))
	{
		$day = new Day($daydata['date']);
		array_push($sqltxts, "(`datetime` BETWEEN " . ($day->Timestamp + $lower) . " AND " . ($day->Timestamp + $upper) . ")");
	}
	
	$sqltxt = implode(" OR ", $sqltxts);
	
	$sql = "SELECT COUNT(*) AS 'count' FROM `records`, `sessions` WHERE `records`.`session` = `sessions`.`id` AND `route` = $route AND `stop` = $stop AND ($sqltxt)";
	$result = mysqli_query($connection, $sql);
	$recordcountdata = mysqli_fetch_array($result);
	
	if($recordcountdata['count'] == 0)
	{
		return null;
	}
	
	$frequency = $recordcountdata['count'] / count($sqltxts);
	
	$sql = "SELECT COUNT(*) AS 'count' FROM `route_paths` WHERE `route` = $route AND `stop` = $stop";
	$result = mysqli_query($connection, $sql);
	$countdata = mysqli_fetch_array($result);
	if($countdata['count'] == 2)
	{
		$frequency /= 2;
	}
	
	$waiting_time = $bound / $frequency;
	
	return $waiting_time;
}
?>
