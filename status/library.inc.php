<?php
	######################
	### Util Functions ###
	######################
	
// Sequence to Location
function seq2loc($route, $sequence)
{
	global $connection;
	
	$sql = "SELECT `id`, `location_lat`, `location_lon`, `stop` FROM `route_paths` WHERE `route` = $route AND `sequence` = $sequence";
	$result = mysqli_query($connection, $sql);
	$numrows = mysqli_num_rows($result);
	if($numrows > 0)
	{
		$data = mysqli_fetch_array($result);
		if($data['stop'] == NULL)
		{
			return new Location($data['location_lat'], $data['location_lon']);
		}
		else
		{
			$sql = "SELECT `location_lat`, `location_lon` FROM `stops` WHERE `id` = {$data['stop']}";
			$result = mysqli_query($connection, $sql);
			$stopdata = mysqli_fetch_array($result);
			
			return new Location($stopdata['location_lat'], $stopdata['location_lon']);
		}
	}
	
	return new Location(0, 0);
}

// Update Bus Data
// $data -> [id, route, session, last_sequence, last_distance, location_lat, location_lon, rotation]
function update_bus($data)
{
	global $connection;
	
	$now = mktime();
	$sql = "UPDATE `buses` SET `route` = {$data['route']}, `session` = {$data['session']}, `last_sequence` = {$data['last_sequence']}, `last_distance` = {$data['last_distance']}, `location_lat` = {$data['location_lat']}, `location_lon` = {$data['location_lon']}, `rotation` = {$data['rotation']}, `last_update` = $now WHERE `id` = {$data['id']}";
	$result = mysqli_query($connection, $sql);
	
	if(mysqli_affected_rows($connection) == 0)
	{
		$sql = "INSERT INTO `buses` (`id`, `route`, `session`, `last_sequence`, `last_distance`, `location_lat`, `location_lon`, `rotation`, `last_update`) VALUES ({$data['id']}, {$data['route']}, {$data['session']} {$data['last_sequence']}, {$data['last_distance']}, {$data['location_lat']}, {$data['location_lon']}, {$data['rotation']}, $now)";
		mysqli_query($connection, $sql);
	}
}

function check_if_on_route($location, $rotation, $route, $min_distance_from_start = 0)
{
	global $connection;
	
	$location_lat = $location->lat;
	$location_lon = $location->lon;
	
	$max_range = $min_distance_from_start + 500;
	
	$sql = "SELECT
		`sequence`, `distance_from_start`, `location_lat`, `location_lon`
		FROM `route_paths`
		WHERE `route` = $route
			AND `distance_from_start` BETWEEN $min_distance_from_start AND $max_range
		ORDER BY `distance_from_start` ASC";
	//6371000 * 2 * ASIN(SQRT(POWER(SIN(($location_lat - abs(`location_lat`)) * PI() / 180 / 2) , 2) + COS($location_lat * PI() / 180) * POWER(SIN(($location_lon - `location_lon`) * PI() / 180 / 2), 2))) AS `distance`
	$results = mysqli_query($connection, $sql);
	while($nearest_sequence_data = mysqli_fetch_array($results))
	{
		$nearest_sequence_location = seq2loc($route, $nearest_sequence_data['sequence']);
		$next_sequence = $nearest_sequence_data['sequence'] + 1;
		
		$sql = "SELECT `sequence`, `distance_from_start`, `location_lat`, `location_lon` FROM `route_paths` WHERE `route` = $route AND `sequence` = $next_sequence LIMIT 1";
		$result = mysqli_query($connection, $sql);
		if(mysqli_num_rows($result) == 1)
		{
			$next_sequence_data = mysqli_fetch_array($result);
			$next_sequence_location = seq2loc($route, $next_sequence_data['sequence']);
			
			$nearest_to_next_rotation = $nearest_sequence_location->DegreeTo($next_sequence_location);
			
			if(Location::DegreeDiff($rotation, $nearest_to_next_rotation) < 60 && $location->DistanceToLine($nearest_sequence_location, $next_sequence_location) < 30)
			{
				return array(
					"result" => true,
					"distance_from_start" => $nearest_sequence_data['distance_from_start'] + $nearest_sequence_location->DistanceTo($location)
				);
			}
		}
	}
	
	return array("result" => false);
}

	##############################
	### Data Download Function ###
	##############################

function download_data($route)
{
	$results = array();
	
	$postdata = http_build_query(array("route" => $route));
	
	$options = array("http" => array(
		"method" => "POST",
		"header" => "Content-type: application/x-www-form-urlencoded",
		"content" => $postdata
	));
	
	$context = stream_context_create($options);
	$data = file_get_contents("http://cmutransit.com/loaddata/bus.php", false, $context);
	
	$edata = explode("<br>", $data);
	
	$i = 0;
	foreach($edata as $busstrdata)
	{
		## Data Formatting ##		
		if(trim($busstrdata) == "")
		{
			continue;
		}
		
		$busdata = explode(",", $busstrdata);
		if(count($busdata) != 6)
		{
			continue;
		}
		
		$results[$i] = array(
			"busno" => substr($busdata[0], strlen($busdata[0]) - 2, 2),
			"location_lat" => $busdata[1],
			"location_lon" => $busdata[2],
			"speed" => $busdata[3],
			"rotation" => $busdata[4]
		);
		
		$i++;
	}
	
	return $results;
}

	############################
	### Data Update Function ###
	############################

function update_data()
{
	global $connection;
	
	$buses = array();
	$i = 0;
	
	$sql = "SELECT `refid` FROM `routes` WHERE `available` = 1 AND `refid` > 0 ORDER BY `refid` ASC";
	$results = mysqli_query($connection, $sql);
	while($routedata = mysqli_fetch_array($results))
	{
		$route = $routedata['refid'];
		$buses_data = download_data($routedata['refid']);
		/*$buses_data = download_data(1);
		$buses_data = array_merge($buses_data, download_data(2));
		$buses_data = array_merge($buses_data, download_data(3));
		$buses_data = array_merge($buses_data, download_data(4));
		$buses_data = array_merge($buses_data, download_data(5));
		$buses_data = array_merge($buses_data, download_data(6));*/
		$now = mktime();
		
		## Get the final distance of the route ##
		$sql = "SELECT MAX(`distance_from_start`) AS `final_distance` FROM `route_paths` WHERE `route` = $route";
		$result = mysqli_query($connection, $sql);
		$data = mysqli_fetch_array($result);
		$final_distance = $data['final_distance'];
		
		## Get the first sequence location ##
		$sql = "SELECT `sequence`, `stop` FROM `route_paths` WHERE `route` = $route ORDER BY `sequence` ASC LIMIT 1";
		$result = mysqli_query($connection, $sql);
		$first_sequence_data = mysqli_fetch_array($result);
		$first_sequence_location = seq2loc($route, $first_sequence_data['sequence']);
		
		## Get the last sequence location ##
		$sql = "SELECT `sequence`, `stop` FROM `route_paths` WHERE `route` = $route ORDER BY `sequence` DESC LIMIT 1";
		$result = mysqli_query($connection, $sql);
		$last_sequence_data = mysqli_fetch_array($result);
		$last_sequence_location = seq2loc($route, $first_sequence_data['sequence']);
		
		foreach($buses_data as $busdata)
		{
			$buses[$i] = $busdata['busno'];
			$i++;
			
			## Setup variables ##
			$location = new Location($busdata['location_lat'], $busdata['location_lon']);
			$session = 0;
			
			## Read from database, update bus location if neccessary ##
			### insert row if not exist ###			
			$sql = "SELECT `route`, `session`, `last_sequence`, `last_distance`, `location_lat`, `location_lon`, `last_update` FROM `buses` WHERE `id` = {$busdata['busno']}";
			$result = mysqli_query($connection, $sql);
			if(mysqli_num_rows($result) == 0)
			{
				$sql = "INSERT INTO `buses` (`id`, `route`, `session`, `last_sequence`, `last_distance`, `location_lat`, `location_lon`, `rotation`, `last_update`) VALUES ({$busdata['busno']}, $route, 0, 0, 0, {$busdata['location_lat']}, {$busdata['location_lon']}, {$busdata['rotation']}, $now)";
				mysqli_query($connection, $sql);
			}
			else
			{
				$last_busdata = mysqli_fetch_array($result);
					
				## Skip data if bus is not moved ##
				if($busdata['location_lat'] == $last_busdata['location_lat'] && $busdata['location_lon'] == $last_busdata['location_lon'])
				{
					continue;
				}
				
				$session = $last_busdata['session'];
			}
			
			## If bus session is not started yet, wait for bus to start moving ##
			if($session == 0)
			{
				## Start new session if bus jest leave the first sequence (distance between 70 and 500 metres) ##
				$bus_distance_from_start = $location->DistanceTo($first_sequence_location);
				$check_on_route = check_if_on_route($location, $busdata['rotation'], $route);
				
				if($check_on_route['result'] == true && $check_on_route['distance_from_start'] > 70 && $check_on_route['distance_from_start'] < 500)
				{
					$sql = "INSERT INTO `sessions` (`id`, `busno`, `route`, `start_datetime`) VALUES (0, {$busdata['busno']}, $route, $now)";
					mysqli_query($connection, $sql);
					$session = mysqli_insert_id($connection);
					
					## Record to timetable if the first sequence is a bus stop ##
					if($first_sequence_data['stop'] != NULL)
					{
						$sql = "INSERT INTO `records` (`id`, `session`, `datetime`, `stop`) VALUES (0, $session, $now, {$first_sequence_data['stop']})";
						mysqli_query($connection, $sql);
					}
					else
					{
						$sql = "INSERT INTO `records` (`id`, `session`, `datetime`, `stop`) VALUES (0, $session, $now, -1)";
						mysqli_query($connection, $sql);
					}
					
					update_bus(array(
						"id" => $busdata['busno'],
						"route" => $route,
						"session" => $session,
						"last_sequence" => 0,
						"last_distance" => $check_on_route['distance_from_start'],
						"location_lat" => $busdata['location_lat'],
						"location_lon" => $busdata['location_lon'],
						"rotation" => $busdata['rotation']
					));
					
					continue;
				}
				## Session can not start, but at least update the location ##
				else
				{
					update_bus(array(
						"id" => $busdata['busno'],
						"route" => $route,
						"session" => 0,
						"last_sequence" => 0,
						"last_distance" => 0,
						"location_lat" => $busdata['location_lat'],
						"location_lon" => $busdata['location_lon'],
						"rotation" => $busdata['rotation']
					));
				}
			}
			## If session is already started, calculate ##
			else
			{
				## Stop the session, if the bus has not been updated for 5 minutes or route does not match ##
				if(isset($last_busdata) && ($now - $last_busdata['last_update'] > 300 || $last_busdata['route'] != $route))
				{
					update_bus(array(
						"id" => $busdata['busno'],
						"route" => $route,
						"session" => 0,
						"last_sequence" => 0,
						"last_distance" => 0,
						"location_lat" => $busdata['location_lat'],
						"location_lon" => $busdata['location_lon'],
						"rotation" => $busdata['rotation']
					));
					
					continue;
				}
				## Nothing wrong, continue the session ##
				else if(isset($last_busdata))
				{
					$check_on_route = check_if_on_route($location, $busdata['rotation'], $route, $last_busdata['last_distance']);
					
					if($check_on_route['result'] == true)
					{
						## FINISH! End session if in 70 metres near final sequence ##
						if($final_distance - $check_on_route['distance_from_start'] < 100)
						{
							## Update ##
							update_bus(array(
								"id" => $busdata['busno'],
								"route" => $route,
								"session" => 0,
								"last_sequence" => 0,
								"last_distance" => 0,
								"location_lat" => $busdata['location_lat'],
								"location_lon" => $busdata['location_lon'],
								"rotation" => $busdata['rotation']
							));
							
							## Record ##
							if($last_sequence_data['stop'] == NULL)
							{
								$last_sequence_data['stop'] = -1;
							}
							
							$sql = "INSERT INTO `records` (`id`, `session`, `stop`, `datetime`) VALUES (0, $session, {$last_sequence_data['stop']}, $now)";
							mysqli_query($connection, $sql);
						}
						else
						{
							## Update ##
							update_bus(array(
								"id" => $busdata['busno'],
								"route" => $route,
								"session" => $session,
								"last_sequence" => 0,
								"last_distance" => $check_on_route['distance_from_start'],
								"location_lat" => $busdata['location_lat'],
								"location_lon" => $busdata['location_lon'],
								"rotation" => $busdata['rotation']
							));
							
							## Check if record ##
							### Get the just-passed stop ###
							$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = $route AND `stop` IS NOT NULL AND `distance_from_start` < {$check_on_route['distance_from_start']} ORDER BY `distance_from_start` DESC LIMIT 1";
							$result = mysqli_query($connection, $sql);
							$data = mysqli_fetch_array($result);
							$just_passed_stop = $data['stop'];
							### Check if recorded, if not, record it ###
							$sql = "SELECT `stop` FROM `records` WHERE `session` = $session ORDER BY `datetime` DESC LIMIT 1";
							$result = mysqli_query($connection, $sql);
							$data = mysqli_fetch_array($result);
							if($data['stop'] != $just_passed_stop)
							{
								$sql = "INSERT INTO `records` (`id`, `session`, `stop`, `datetime`) VALUES (0, $session, $just_passed_stop, $now)";
								mysqli_query($connection, $sql);
							}
						}
					}
					## Bus is off the route, but at least update its location ##
					else
					{
						$sql = "UPDATE `buses` SET `location_lat` = {$busdata['location_lat']}, `location_lon` = {$busdata['location_lon']}, `rotation` = {$busdata['rotation']} WHERE `id` = {$busdata['busno']}";
						mysqli_query($connection, $sql);
						
						if($location->DistanceTo($last_sequence_location) < 50)
						{
							## Update ##
							update_bus(array(
								"id" => $busdata['busno'],
								"route" => $route,
								"session" => 0,
								"last_sequence" => 0,
								"last_distance" => 0,
								"location_lat" => $busdata['location_lat'],
								"location_lon" => $busdata['location_lon'],
								"rotation" => $busdata['rotation']
							));
							
							## Record ##
							if($last_sequence_data['stop'] == NULL)
							{
								$last_sequence_data['stop'] = -1;
							}
							
							$sql = "INSERT INTO `records` (`id`, `session`, `stop`, `datetime`) VALUES (0, $session, {$last_sequence_data['stop']}, $now)";
							mysqli_query($connection, $sql);
						}
					}
				}
			}
		}
	}
	
	## Deal with no-data buses
	if(count($buses) > 0)
	{
		$buses_str = implode(", ", $buses);
		$sql = "SELECT `id` FROM `buses` WHERE `id` NOT IN ($buses_str) AND $now - `last_update` > 300";
		$results = mysqli_query($connection, $sql);
		while($data = mysqli_fetch_array($results))
		{
			$sql = "UPDATE `buses` SET `session` = 0, `last_distance` = 0 WHERE `id` = {$data['id']}";
			mysqli_query($connection, $sql);
		}
	}
}
?>
