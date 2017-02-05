<?php

###############################
## REQUIRES mysql connection ##
## REQUIRES calendar.inc.php ##
###############################

class Stop
{
	private $id;
	private $name;
	private $busstop;
	private $location_lat;
	private $location_lon;
	private $connections = array();
	private $wait_time = null;
	
	public function __get($v)
	{
		return $this->$v;
	}
	
	public function __construct($ID)
	{
		$this->id = $ID;
	}
	
	public function GetInfo()
	{
		global $connection;
		$sql = "SELECT `id`, `name`, `busstop`, `location_lat`, `location_lon` FROM `stops` WHERE `id` = {$this->id}";
		$result = mysqli_query($connection, $sql);
		$stopdata = mysqli_fetch_array($result);
		
		foreach($stopdata as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	public function WaitTime($route, $datetime = false)
	{
		if($this->wait_time == null)
		{
			$this->wait_time = wait_time_at($this->id, $route, $datetime);
			return $this->wait_time;
		}
		else
		{
			return $this->wait_time;
		}
	}
	
	public function Connections($datetime = false, $bus = true, $walk = true)
	{
		if(count($this->connections) == 0)
		{
			$this->connections = get_single_path_at($this->id, $datetime, $bus, $walk);
			return $this->connections;
		}
		else
		{
			return $this->connections;
		}
	}
	
	public function Translate($language = false)
	{
		if($language == false)
		{
			$language = get_language_id();
		}
		
		return get_text("stop", $this->id, $language);
	}
	
	public function FindPathsTo($destination, $datetime = false, $mode = "totalTime", $max_result = 2)
	{
		global $connection;
		
		if($datetime == false)
		{
			$datetime = mktime();
		}
		
		$destination_stop = new Stop($destination);
		$destination_stop->GetInfo();
		$destination_location = new Location($destination_stop->location_lat, $destination_stop->location_lon);
		
		$this->GetInfo();
		$departure_location = new Location($this->location_lat, $this->location_lon);
		
		// Prepare nodes to be an array which key is its id
		$nodes = array();
		
		$sql = "SELECT `id` FROM `stops`";
		$results = mysqli_query($connection, $sql);
		while($stopiddata = mysqli_fetch_array($results))
		{
			$nodes[$stopiddata['id']] = new Node($stopiddata['id'], $max_result);
		}
		
		// Start node
		$currentid = $this->id;
		$currentpath = new Path($this->id, $datetime);
		$nodes[$currentid]->CompareAndAdd($currentpath);
		$dt = $datetime;
		
		$connected_ids = array();
		
		while(!Node::AllMarked($nodes))
		{
			$lasttimestamp = $nodes[$currentid]->LastTimeStamp();
			if($lasttimestamp == null)
			{
				$dt = $datetime;
			}
			else
			{
				$dt = $lasttimestamp;
			}
			
			$walk = true;
			if($nodes[$currentid]->paths[0] != null
				&& $nodes[$currentid]->paths[0]->hopCount > 0
				&& $nodes[$currentid]->paths[0]->LastSequence()['route'] == null)
			{
				$walk = false;
			}
			
			$nextid = null;
			$connected_nodes = $nodes[$currentid]->stop->Connections($dt, true, $walk);
			foreach($connected_nodes as $connected_node)
			{				
				$newdt = $dt;
				if($connected_node['time'] > 0)
				{
					$newdt += ceil($connected_node['time']);
				}				
				if($connected_node['waittime'] > 0)
				{
					$newdt += ceil($connected_node['waittime']);
				}
				
				$currentpath = clone($nodes[$currentid]->paths[0]);
				$currentpath->AddSequence($connected_node['to'], $connected_node['route'], $newdt, ceil($connected_node['waittime']));
				
				$nodes[$connected_node['to']]->CompareAndAdd($currentpath);
				
				// Put Connected Ids
				array_push($connected_ids, $connected_node['to']);
			}
			
			$nodes[$currentid]->marked = true;
			
			if($nodes[$destination]->touched >= $max_result)
			{
				break;
			}
			
			$connected_ids = array_unique($connected_ids);
			
			$connected_stops = array();
			// Calculate Distance
			$nodes[$currentid]->stop->GetInfo();
			$current_location = new Location($nodes[$currentid]->stop->location_lat, $nodes[$currentid]->stop->location_lon);
			foreach($connected_ids as $connected_id)
			{
				// Find Distance				
				
				$connect_stop = new Stop($connected_id);
				$connect_stop->GetInfo();
				$connect_location = new Location($connect_stop->location_lat, $connect_stop->location_lon);
				
				array_push($connected_stops,
					array("id" => $connected_id,
						"distance" => $destination_location->DistanceTo($connect_location)
					)
				);
			}
			
			$connected_stops = sort_by($connected_stops, "distance", SORT_ASC);
						
			// Find next node
			$nextid = null;
			foreach($connected_stops as $connected_stop)
			{
				$connected_id = $connected_stop['id'];
				
				if($connected_id == $destination)
				{
					continue;
				}
				
				if($nodes[$connected_id]->marked == false)
				{
					$nextid = $connected_id;
					break;
				}
			}
						
			if($nextid == null)
			{
				break;
			}
			
			$currentid = $nextid;			
		}
		
		return $nodes[$destination]->paths;
	}

	public function TimeTable()
	{
		global $connection;
		
		$function_result = array();
		
		if($this->busstop == null)
		{
			$this->GetInfo();
		}
		
		if($this->busstop == 0) // This is not a bus stop!!!
		{
			return $function_result;
		}
		
		$Hr = date("H");
		
		$out = ($Hr >= 21 || $Hr <= 6);
		
		$info = array();
		if($out)
		{
			$info = $this->DailyTimeTable();
		}
		
		$now = mktime();
		$routes = get_routes_at($now);
		
		foreach($routes as $route) if($route['available'] == 1)
		{
			$count = 0;
			
			$sql = "SELECT `distance_from_start` FROM `route_paths` WHERE `stop` = {$this->id} AND `route` = {$route['id']} ORDER BY `distance_from_start` DESC";
			$results = mysqli_query($connection, $sql);
			$num_in_route_paths = mysqli_num_rows($results);
			if($num_in_route_paths > 0)
			{
				$approximated_time_from_start = null;
				
				$stop_distance_data = mysqli_fetch_array($results);
				
				$sql = "SELECT `id`, `last_distance`, `last_update`, `session` FROM `buses` WHERE `session` > 0 AND `route` = {$route['id']} AND `last_distance` < {$stop_distance_data['distance_from_start']} AND $now - `last_update` < 60";
				$busresults = mysqli_query($connection, $sql);
				while($busdata = mysqli_fetch_array($busresults))
				{
					$estimatedtime = null;
					$last_stopid = null;
					
					$sql = "SELECT `start_datetime` FROM `sessions` WHERE `id` = {$busdata['session']}";
					$result = mysqli_query($connection, $sql);
					$sessiondata = mysqli_fetch_array($result);
					
					$sql = "SELECT `datetime`, `stop` FROM `records` WHERE `session` = {$busdata['session']} ORDER BY `datetime` DESC";
					$lastrecord_result = mysqli_query($connection, $sql);
						
					$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$route['id']} AND `stop` = {$this->id} AND ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`)";
					$estimation_result = mysqli_query($connection, $sql);
					
					if(mysqli_num_rows($lastrecord_result) > 0)
					{
						$last_recorddata = mysqli_fetch_array($lastrecord_result);
						$last_stopid = $last_recorddata['stop'];
					}
					
					if(mysqli_num_rows($estimation_result) > 0)
					{
						$estimateddata = mysqli_fetch_array($estimation_result);
					}
					else
					{
						continue;
					}
					
					if(mysqli_num_rows($lastrecord_result) > 0 && mysqli_num_rows($estimation_result) > 0)
					{
						$sql = "SELECT COUNT(*) AS 'count' FROM `route_paths` WHERE `route` = {$route['id']} AND `stop` = {$last_recorddata['stop']}";
						$result = mysqli_query($connection, $sql);
						$last_stop_num_in_route_paths_data = mysqli_fetch_array($result);
						
						$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$route['id']} AND `stop` = {$last_recorddata['stop']} AND ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`)";
						$result = mysqli_query($connection, $sql);
						if(mysqli_num_rows($result) > 0 && $last_stop_num_in_route_paths_data['count'] == 1)
						{
							$last_record_estimateddata = mysqli_fetch_array($result);
							
							$estimatedtime = $last_recorddata['datetime'] + ($estimateddata['estimated_time'] - $last_record_estimateddata['estimated_time']);
						}
						else
						{
							$estimatedtime = $sessiondata['start_datetime'] + $estimateddata['estimated_time'];
						}
					}
					else
					{
						$estimatedtime = $sessiondata['start_datetime'] + $estimateddata['estimated_time'];
					}
					
					array_push($function_result,
						array(
							"busno" => $busdata['id'],
							"route" => $route['id'],
							"remaining_distance" => $stop_distance_data['distance_from_start'] - $busdata['last_distance'],
							"estimated_time" => $estimatedtime,
							"estimated_time_readable" => date("H:i", $estimatedtime),
							"remaining_time" => $estimatedtime - $now,
							"last_stopid" => $last_stopid,
							"last_update" => $busdata['last_update'],
							"session" => $busdata['session'],
							"origin" => false
						)
					);
					$count++;
				}
				
				if($num_in_route_paths == 1 && $count == 0)
				{					
					$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$route['id']} AND `stop` = {$this->id} AND ($now BETWEEN `start_time` AND `end_time`)";
					$result = mysqli_query($connection, $sql);
					
					if(mysqli_num_rows($result) == 0)
					{
						$dt = mktime(7, 1);
						$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$route['id']} AND `stop` = {$this->id} AND ($dt BETWEEN `start_time` AND `end_time`)";
						$result = mysqli_query($connection, $sql);
					}
					
					if(mysqli_num_rows($result) > 0)
					{
						$estimateddata = mysqli_fetch_array($result);
						$approximated_time_from_start = $estimateddata['estimated_time'];

						
						$sql = "SELECT `start_datetime` FROM `sessions` WHERE `route` = {$route['id']} ORDER BY `start_datetime` DESC LIMIT 1";
						$result = mysqli_query($connection, $sql);
						if(mysqli_num_rows($result) > 0)
						{
							$lastsessiondata = mysqli_fetch_array($result);
							$wait_time = ceil(wait_time_at($this->id, $route['id'], $now));
							$estimatedtime = $lastsessiondata['start_datetime'] + $wait_time + $approximated_time_from_start;
							
							if($estimatedtime - $now < $approximated_time_from_start)
							{
								$estimatedtime = $now + floor($wait_time / 2) + $approximated_time_from_start;
							}
							
							foreach($info as $info_route)
							{
								if($info_route['route'] == $route['id'])
								{
									if($estimatedtime > $info_route['estimated_last'])
									{
										$estimatedtime = $info_route['estimated_first'] + 86400;;
									}
									if($estimatedtime < $info_route['estimated_first'])
									{
										$estimatedtime = $info_route['estimated_first'];
									}
									
									break;
								}
							}
							array_push($function_result,
								array(
									"busno" => null,
									"route" => $route['id'],
									"remaining_distance" => null,
									"estimated_time" => $estimatedtime,
									"estimated_time_readable" => date("H:i", $estimatedtime),
									"remaining_time" => $estimatedtime - $now,
									"last_stopid" => null,
									"last_update" => null,
									"session" => null,
									"origin" => false
								)
							);
						}
					}
				}
				else if($num_in_route_paths == 2 && $count == 0)
				{
					$sql = "SELECT `start_datetime` FROM `sessions` WHERE `route` = {$route['id']} ORDER BY `start_datetime` DESC LIMIT 1";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) > 0)
					{
						$lastsessiondata = mysqli_fetch_array($result);
						$wait_time = ceil(wait_time_at($this->id, $route['id'], $now));
						$estimatedtime = $lastsessiondata['start_datetime'] + $wait_time;
						if($estimatedtime - $now < $wait_time)
						{
							$estimatedtime = $now + $wait_time;
						}
						
						foreach($info as $info_route)
						{
							if($info_route['route'] == $route['id'])
							{
								//if($estimatedtime > $info_route['estimated_last'] || $estimatedtime < $info_route['estimated_first'])
								//{
								//	$estimatedtime = $info_route['estimated_first'];
								//	
								//	if(date("H") > 12)
								//	{
								//		$estimatedtime += 86400;
								//	}
								//}
								
								
								if($estimatedtime > $info_route['estimated_last'])
								{
									$estimatedtime = $info_route['estimated_first'] + 86400;;
								}
								if($estimatedtime < $info_route['estimated_first'])
								{
									$estimatedtime = $info_route['estimated_first'];
								}
								
								break;
							}
						}
						
						array_push($function_result,
							array(
								"busno" => null,
								"route" => $route['id'],
								"remaining_distance" => null,
								"estimated_time" => $estimatedtime,
								"estimated_time_readable" => date("H:i", $estimatedtime),
								"remaining_time" => $estimatedtime - $now,
								"last_stopid" => null,
								"last_update" => null,
								"session" => null,
								"origin" => true
							)
						);
					}
				}
			}
		}
		
		return $function_result;
	}
	
	public function PassedTimetable($max_result = 10)
	{
		global $connection;
		
		$function_result = array();
		
		$sql = "SELECT `session`, `datetime` FROM `records` WHERE `stop` = {$this->id} ORDER BY `datetime` DESC LIMIT $max_result";
		$results = mysqli_query($connection, $sql);
		while($recorddata = mysqli_fetch_array($results))
		{
			$session = new Session($recorddata['session']);
			$route = new Route($session->route);
			
			$item = array(
				"session" => $recorddata['session'],
				"datetime" => $recorddata['datetime'],
				"datetime_readable" => date("H:i", $recorddata['datetime']),
				"route" => $session->route,
				"routename" => get_text("route", $session->route, get_language_id()),
				"routecolor" => $route->color,
				"busno" => $session->busno
			);
			
			array_push($function_result, $item);
		}
		
		return $function_result;
	}
	
	public function DailyTimeTable()
	{
		global $connection;
		
		$function_result = array();
		
		if($this->busstop == null)
		{
			$this->GetInfo();
		}
		
		if($this->busstop == 0) // This is not a bus stop!!!
		{
			return $function_result;
		}
		
		$day_to_calculate = 10;
		$today = new Day(mktime(0, 0, 0));
		
		$timezone = date("Z");
		
		$days = array();
		
		$sql = "SELECT `date` FROM `days` WHERE type = {$today->type} AND `date` < {$today->timestamp} ORDER BY `date` DESC LIMIT $day_to_calculate";
		$results = mysqli_query($connection, $sql);
		while($daydata = mysqli_fetch_array($results))
		{
			array_push($days, $daydata['date']);
		}
		
		foreach($this->PassingRoutes() as $route) if($route['available'] == 1)
		{
			$estimated_first = null;		
			$first_sum = 0;
			$first_count = 0;
			$avg_first = null;
			
			$estimated_last = null;
			$last_sum = 0;
			$last_count = 0;
			$avg_last = null;
			
			foreach($days as $day)
			{				
				$sql = "SELECT MIN(`start_datetime`) AS 'min_dt', MAX(`start_datetime`) AS 'max_dt' FROM `sessions` WHERE `route` = {$route['id']} AND (`start_datetime` BETWEEN {$day} AND " . ($day + 86399) . ")";
				$result = mysqli_query($connection, $sql);
				$data = mysqli_fetch_array($result);
				
				if($data['min_dt'] != null)
				{
					$first_sum += ($data['min_dt'] + $timezone) % 86400;
					$first_count++;
				}
				
				if($data['max_dt'] != null)
				{
					$last_sum += ($data['max_dt'] + $timezone) % 86400;
					$last_count++;
				}
			}
			
			if($first_count > 0)
			{
				$avg_first = $first_sum / $first_count;
				$estimated_first = $today->timestamp + ceil($avg_first);
			}
			
			if($last_count > 0)
			{
				$avg_last = $last_sum / $last_count;
				$estimated_last = $today->timestamp + ceil($avg_last);
			}
			
			if($route['count'] == 1)
			{
				$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$route['id']} AND `stop` = {$this->id} AND ($estimated_first BETWEEN `start_time` AND `end_time`)";
				$result = mysqli_query($connection, $sql);
				if(mysqli_num_rows($result) > 0)
				{
					$estimateddata = mysqli_fetch_array($result);
					$estimated_first += $estimateddata['estimated_time'];
				}
				
				$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$route['id']} AND `stop` = {$this->id} AND ($estimated_last BETWEEN `start_time` AND `end_time`)";
				$result = mysqli_query($connection, $sql);
				if(mysqli_num_rows($result) > 0)
				{
					$estimateddata = mysqli_fetch_array($result);
					$estimated_last += $estimateddata['estimated_time'];
				}
			}
			
			if(date("H") < 7 || date("H") > 21)
			{
				$waittime = wait_time_at($this->id, $route['id'], mktime(12, 0, 0));
			}
			else
			{
				$waittime = wait_time_at($this->id, $route['id'], mktime());
			}
			
			$item = array(
				"route" => $route['id'],
				"routename" => get_text("route", $route['id'], get_language_id()),
				"routecolor" => $route['color'],
				"estimated_first" => $estimated_first,
				"estimated_first_readable" => date("H:i", $estimated_first),
				"estimated_last" => $estimated_last,
				"estimated_last_readable" => date("H:i", $estimated_last),
				"waittime" => $waittime
			);
			
			array_push($function_result, $item);
		}
		
		return $function_result;
	}
	
	public function PassingRoutes($datetime = false)
	{
		global $connection;
		
		$routes = array();
		
		if($datetime == false)
		{
			$datetime = mktime();
		}
		
		foreach(get_routes_at($datetime) as $routedata) if($routedata['available'] == 1)
		{
			$sql = "SELECT COUNT(*) AS 'count' FROM `route_paths` WHERE `route` = {$routedata['id']} AND `stop` = {$this->id}";
			$result = mysqli_query($connection, $sql);
			$data = mysqli_fetch_array($result);
			if($data['count'] > 0)
			{
				$routedata['count'] = $data['count'];
				array_push($routes, $routedata);
			}
		}
		
		return $routes;
	}
	
	public static function Search($keyword, $language = false, $max_result = 10)
	{
		if($language == false)
		{
			$language = get_language_id();
		}
		
		return search($leyword, $language, $max_result);
	}
}

class Session
{
	private $id;
	private $busno;
	private $route;
	private $start_datetime;
	
	public function __get($v)
	{
		return $this->$v;
	}
	
	public function __construct($ID)
	{
		global $connection;
		
		$this->id = $ID;
		
		$sql = "SELECT `busno`, `route`, `start_datetime` FROM `sessions` WHERE `id` = $ID";
		$result = mysqli_query($connection, $sql);
		$sessiondata = mysqli_fetch_array($result);
		$this->busno = $sessiondata['busno'];
		$this->route = $sessiondata['route'];
		$this->start_datetime = $sessiondata['start_datetime'];
	}
	
	public function LastRecord()
	{
		global $connection;
		
		$sql = "SELECT `stop`, `datetime` FROM `records` WHERE `session` = {$this->id} ORDER BY `datetime` DESC LIMIT 1";
		$result = mysqli_query($connection, $sql);
		
		if(mysqli_num_rows($result) == 1)
		{
			$recorddata = mysqli_fetch_array($result);
		
			return array(
				"stop" => $recorddata['stop'],
				"datetime" => $recorddata['datetime']
			);
		}
		
		return null;
	}
	
	public function GetStatus()
	{
		global $connection;
		
		$function_result = array();
		
		$online = false;
		$sql = "SELECT `session`, `last_distance` FROM `buses` WHERE `id` = {$this->busno}";
		$result = mysqli_query($connection, $sql);
		$busdata = mysqli_fetch_array($result);
		if($busdata['session'] == $this->id)
		{
			$online = true;
		}
		
		$time_sequences = array();
		
		$last_record = array(
			"stop" => "",
			"datetime" => null,
			"estimated_time" => 0
		);
		
		$sql = "SELECT `stop`, `datetime` FROM `records` WHERE `session` = {$this->id} ORDER BY `datetime` ASC";
		$results = mysqli_query($connection, $sql);
		while($recorddata = mysqli_fetch_array($results))
		{
			$last_record['stop'] = $recorddata['stop'];
			$last_record['datetime'] = $recorddata['datetime'];
			
			if(count($time_sequences) > 0)
			{
				$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$this->route} AND `stop` = {$recorddata['stop']} AND ({$this->start_datetime} BETWEEN `start_time` AND `end_time`)";
				$result = mysqli_query($connection, $sql);
				
				if(mysqli_num_rows($result) > 0)
				{
					$estimateddata = mysqli_fetch_array($result);
					$last_record['estimated_time'] = $estimateddata['estimated_time'];
				}
			}
			
			$item = array(
				"stop" => $recorddata['stop'],
				"stopname" => get_text("stop", $recorddata['stop'], get_language_id()),
				"datetime" => $recorddata['datetime'],
				"datetime_readable" => date("H:i", $recorddata['datetime']),
				"remaining_time" => null,
				"remaining_distance" => null,
				"estimated" => false
			);
			
			array_push($time_sequences, $item);
		}
		
		if($online == true)
		{
			$now = mktime();
			
			$sql = "SELECT `stop`, `distance_from_start` FROM `route_paths` WHERE `route` = {$this->route} AND `stop` IS NOT NULL AND `distance_from_start` > {$busdata['last_distance']}";
			$results = mysqli_query($connection, $sql);
			while($nextstopdata = mysqli_fetch_array($results))
			{
				$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$this->route} AND `stop` = {$nextstopdata['stop']} AND ({$this->start_datetime} BETWEEN `start_time` AND `end_time`)";
				$result = mysqli_query($connection, $sql);
				
				$dt = null;
				if(mysqli_num_rows($result) > 0)
				{
					$estimateddata = mysqli_fetch_array($result);
					
					$dt = $last_record['datetime'] + ($estimateddata['estimated_time'] - $last_record['estimated_time']);
				}
				
				$item = array(
					"stop" => $nextstopdata['stop'],
					"stopname" => get_text("stop", $nextstopdata['stop'], get_language_id()),
					"datetime" => $dt,
					"datetime_readable" => date("H:i", $dt),
					"remaining_time" => $dt - $now,
					"remaining_distance" => $nextstopdata['distance_from_start'] - $busdata['last_distance'],
					"estimated" => true
				);
			
				array_push($time_sequences, $item);
			}
		}

		$route = new Route($this->route);
		$function_result['busno'] = $this->busno;
		$function_result['route'] = $this->route;
		$function_result['routename'] = get_text("route", $this->route, get_language_id());
		$function_result['routecolor'] = $route->color;
		$function_result['start_datetime'] = $this->start_datetime;
		$function_result['start_datetime_readable'] = date("H:i", $this->start_datetime);
		$function_result['online'] = $online;
		$function_result['time_sequences'] = $time_sequences;
		
		return $function_result;
	}
}

class Route
{
	private $id;
	private $name;
	private $color;
	private $path;
	
	public function __get($v)
	{
		return $this->$v;
	}
	
	public function __construct($ID)
	{
		global $connection;
		
		$this->id = $ID;
		
		$sql = "SELECT `name`, `color` FROM `routes` WHERE `id` = $ID";
		$result = mysqli_query($connection, $sql);
		$routedata = mysqli_fetch_array($result);
		
		$this->name = $routedata['name'];
		$this->color = $routedata['color'];
		
		$this->path = array();
		
		$sql = "SELECT `stop`, `distance_from_start` FROM `route_paths` WHERE `route` = {$this->id} AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
		$results = mysqli_query($connection, $sql);
		while($stopdata = mysqli_fetch_array($results))
		{
			array_push($this->path, array("stop" => new Stop($stopdata['stop']), "distance_from_start" => $stopdata['distance_from_start']));
		}
	}
}

class Node
{
	private $stop;
	private $maxPaths;	
	private $paths;	
	
	public $marked = false;
	public $touched = 0;
	
	public function __get($v)
	{
		return $this->$v;
	}
	
	public function __construct($stopid, $max_path)
	{
		$this->stop = new Stop($stopid);
		$this->maxPaths = $max_path;
	}
	
	public function CompareAndAdd($path, $mode = "totalTime")
	{
		for($i = 0; $i < $this->maxPaths; $i++)
		{
			if($path->IsBetterThan($this->paths[$i], $mode) == true)
			{
				$this->touched++;
				$this->paths[$i] = $path;
				break;
			}
		}
	}
	
	public function LastTimeStamp()
	{
		if(count($this->paths[0]) == null)
		{
			return null;
		}
		return $this->paths[0]->LastSequence()['timestamp'];
	}
	
	public static function AllMarked($nodes)
	{
		foreach($nodes as $node)
		{
			if($node->marked == false)
			{
				return false;
			}
		}
		
		return true;
	}
}

class Path
{
	private $sequences;
	private $startID;
	private $startTime;
	private $totalTime = 0;
	private $hopCount = 0;
	
	public function __get($v)
	{
		return $this->$v;
	}
	
	public function __construct($start_id, $start_time)
	{
		$this->sequences = array();
		$this->startID = $start_id;
		$this->startTime = $start_time;
	}
	
	public function LastSequence()
	{
		if(count($this->sequences) > 0)
		{
			return end($this->sequences);
		}
		
		return null;
	}
	
	public function AddSequence($stop, $route, $timestamp = null, $waittime = 0)
	{
		if($timestamp == null)
		{
			$timestamp = mktime();
		}
		
		array_push($this->sequences, array("to" => $stop, "route" => $route, "timestamp" => $timestamp, "waittime" => $waittime));
		$this->totalTime = ($timestamp - $this->startTime);
		$this->hopCount++;
	}
	
	public function IsBetterThan(&$path, $mode = "totalTime")
	{
		if($path == null)
		{
			return true;
		}
		
		if($mode == "totalTime")
		{
			if($this->totalTime < $path->totalTime)
			{
				return true;
			}
			else if($this->totalTime == $path->totalTime && $this->hopCount < $path->hopCount)
			{
				return true;
			}
			return false;
		}
		else if($mode == "hopCount")
		{
			if($this->hopCount < $path->hopCount)
			{
				return true;
			}
			else if($this-> hopCount == $path->hopCount && $this->totalTime < $path->totalTime)
			{
				return true;
			}
			return false;
		}
		
		return null;
	}
	
	public function Readable()
	{
		$function_result = array();
		$stop = new Stop($this->startID);
		$stop->GetInfo();
		
		array_push($function_result,
			array(
				"stopid" => $stop->id,
				"stopname" => $stop->Translate(get_language_id()),
				"stoplocation" => array(
					"lat" => $stop->location_lat,
					"lon" => $stop->location_lon
				),
				"route" => null,
				"routename" => null,
				"routecolor" => "cdcdcd",
				"timestamp" => $this->startTime,
				"traveltime" => 0,
				"waittime" => 0,
				"time" => date("H:i", $this->startTime)
			)
		);
		
		$lasttimestamp = $this->startTime;
		
		foreach($this->sequences as $sequence)
		{
			$stop = new Stop($sequence['to']);
			$stop->GetInfo();
			$route = $sequence['route'];
			$timestamp = $sequence['timestamp'];
			$waittime = $sequence['waittime'];
			$traveltime = $sequence['timestamp'] - $lasttimestamp - $waittime;
			
			$routename = null;
			$routecolor = "cdcdcd";
			if($route != null)
			{
				$r = new Route($route);
				$routename = get_text("route", $r->id, get_language_id());
				$routecolor = $r->color;
			}
			
			array_push($function_result,
				array(
					"stopid" => $stop->id,
					"stopname" => $stop->Translate(get_language_id()),
					"stoplocation" => array(
						"lat" => $stop->location_lat,
						"lon" => $stop->location_lon
					),
					"route" => $route,
					"routename" => $routename,
					"routecolor" => $routecolor,
					"timestamp" => $timestamp,
					"traveltime" => $traveltime,
					"waittime" => $waittime,
					"time" => date("H:i", $timestamp)
				)
			);
			
			$lasttimestamp = $timestamp;
		}
		
		return($function_result);
	}
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
	
	$sql = "SELECT `waittime` FROM `time_estimation` WHERE `stop` = $stop AND `route` = $route AND $datetime BETWEEN `start_time` AND `end_time`";
	$result = mysqli_query($connection, $sql);
	if(mysqli_num_rows($result) > 0)
	{
		$waittimedata = mysqli_fetch_array($result);
	}
	else
	{
		return wait_time_at($stop, $route, mktime(12, 0, 0));
	}
	
	return $waittimedata['waittime'];
}

## Function Get Path at
#### Get available path at specificed stop
function get_single_path_at($stop, $datetime = false, $bus = true, $walk = true)
{
	global $connection;
	
	if($datetime == false)
	{
		$datetime = mktime();
	}
	
	$function_result = array();
	
	if($walk == true)
	{
		$sql = "SELECT `stop1`, `stop2`, `connection_time` FROM `connections` WHERE `stop1` = $stop OR `stop2` = $stop";
		$results = mysqli_query($connection, $sql);
		while($connectiondata = mysqli_fetch_array($results))
		{
			if($connectiondata['stop1'] == $stop)
			{
				array_push($function_result,
					array(
						"to" => $connectiondata['stop2'],
						"time" => $connectiondata['connection_time'] * 1,
						"waittime" => null,
						"route" => null
					)
				);
			}
			else if($connectiondata['stop2'] == $stop)
			{
				array_push($function_result,
					array(
						"to" => $connectiondata['stop1'],
						"time" => $connectiondata['connection_time'] * 1,
						"waittime" => null,
						"route" => null
					)
				);
			}
		}
	}
	
	if($bus == true)
	{
		foreach(get_routes_at($datetime) as $routedata) if($routedata['available'] == 1)
		{			
			$sql = "SELECT `distance_from_start` FROM `route_paths` WHERE `route` = {$routedata['id']} AND `stop` = $stop ORDER BY `distance_from_start` ASC LIMIT 1";
			$result = mysqli_query($connection, $sql);
			if(mysqli_num_rows($result) == 1)
			{
				$waittime = wait_time_at($stop, $routedata['id'], $datetime);
				$datetime += $waittime;
			
				$distancedata = mysqli_fetch_array($result);
				$stop_distance = $distancedata['distance_from_start'];
				
				$time_to_stop = 0;
				if($stop_distance > 0)
				{
					$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$routedata['id']} AND `stop` = $stop AND ($datetime BETWEEN `start_time` AND `end_time`)";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) > 0)
					{
						$estimateddata = mysqli_fetch_array($result);
						$time_to_stop = $estimateddata['estimated_time'];
					}
				}
				
				$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = {$routedata['id']} AND `stop` IS NOT NULL AND `distance_from_start` > $stop_distance";
				$results = mysqli_query($connection, $sql);
				while($stopdata = mysqli_fetch_array($results))
				{
					$time = 0;
					
					if($time_to_stop >= 0)
					{
						$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$routedata['id']} AND `stop` = {$stopdata['stop']} AND ($datetime BETWEEN `start_time` AND `end_time`)";
						$result = mysqli_query($connection, $sql);
						if(mysqli_num_rows($result) > 0)
						{
							$estimateddata = mysqli_fetch_array($result);
							$time = $estimateddata['estimated_time'] - $time_to_stop;
						}
					}
					
					array_push($function_result,
						array(	"to" => $stopdata['stop'],
								"time" => $time,
								"waittime" => $waittime,
								"route" => $routedata['id']
							)
					);
				}
			}
		}
	}
	
	return $function_result;
}

function sort_by($array, $key, $sort_type = SORT_ASC)
{
	$temp = array();
	foreach ($array as $i => $row)
	{
		$temp[$i] = $row[$key];
	}
	array_multisort($temp, $sort_type, $array);
	
	return $array;
}
?>