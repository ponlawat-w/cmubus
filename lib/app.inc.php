<?php

/**
 * Class Stop
 */
class Stop
{
	private $ID;
	private $Name;
	private $BusStop;
	/** @var Location */
	private $Location;
	private $Connections = array();
	private $WaitTime = null;

    /**
     * @param $v
     * @return mixed
     */
    public function __get($v)
	{
		return $this->$v;
	}

    /**
     * Stop constructor.
     * @param $ID
     */
    public function __construct($ID)
	{
		$this->ID = $ID;
	}

    /**
     * Get Bus Stop Information
     */
    public function GetInfo()
	{
		$result = sql_query("SELECT `name`, `id`, `busstop`, `location_lat`, `location_lon` FROM `stops` WHERE `id` = ?", "i", array($this->ID));
		$stopData = mysqli_fetch_array($result);

        $this->Name = $stopData['name'];
        $this->BusStop = $stopData['busstop'];
        $this->Location = new Location($stopData['location_lat'], $stopData['location_lon']);
    }

    /**
     * Get approximated waiting time at the bus stop at the specified time
     * @param $route
     * @param int|bool $datetime
     * @return float|null
     */
    public function WaitTime($route, $datetime = false)
	{
		if($this->WaitTime == null)
		{
			$this->WaitTime = wait_time_at($this->ID, $route, $datetime);
			return $this->WaitTime;
		}
		else
		{
			return $this->WaitTime;
		}
	}

    /**
     * Get the connections (walkable path) from this bus stop
     * @param int|bool $datetime
     * @param bool $bus
     * @param bool $walk
     * @return array
     */
    public function Connections($datetime = false, $bus = true, $walk = true)
	{
		if(count($this->Connections) == 0)
		{
			$this->Connections = get_single_path_at($this->ID, $datetime, $bus, $walk);
			return $this->Connections;
		}
		else
		{
			return $this->Connections;
		}
	}

    /**
     * Find the best path to specified bus stop or place
     * @param $destinationID
     * @param int|bool $datetime
     * @param int $max_result
     * @return mixed
     */
    public function FindPathsTo($destinationID, $datetime = false, $max_result = 2)
	{
		global $connection;
		
		if($datetime == false)
		{
			$datetime = mktime();
		}
		
		$destination_stop = new Stop($destinationID);
		$destination_stop->GetInfo();
		$destination_location = $destination_stop->Location;
		
		// Prepare nodes to be an array which key is its id
		$nodes = array();
        /**
         * @var $nodes Node[]
         */
		
		$sql = "SELECT `id` FROM `stops`";
		$results = mysqli_query($connection, $sql);
		while($StopIDData = mysqli_fetch_array($results))
		{
			$nodes[$StopIDData['id']] = new Node($StopIDData['id'], $max_result);
		}
		
		// Start node
		$currentID = $this->ID;
		$currentPath = new Path($this->ID, $datetime);
		$nodes[$currentID]->CompareAndAdd($currentPath);
		
		$connected_ids = array();
		
		while(!Node::AllMarked($nodes))
		{
			$lastTimeStamp = $nodes[$currentID]->LastTimeStamp();
			if($lastTimeStamp == null)
			{
				$dt = $datetime;
			}
			else
			{
				$dt = $lastTimeStamp;
			}
			
			$walk = true;
			if($nodes[$currentID]->Paths[0] != null
				&& $nodes[$currentID]->Paths[0]->hopCount > 0
				&& $nodes[$currentID]->Paths[0]->LastSequence()['route'] == null)
			{
				$walk = false;
			}
			
			$nextID = null;
			$connected_nodes = $nodes[$currentID]->Stop->Connections($dt, true, $walk);
			foreach($connected_nodes as $connected_node)
			{				
				$newTimestamp = $dt;
				if($connected_node['time'] > 0)
				{
					$newTimestamp += ceil($connected_node['time']);
				}				
				if($connected_node['waittime'] > 0)
				{
					$newTimestamp += ceil($connected_node['waittime']);
				}
				
				$currentPath = clone($nodes[$currentID]->Paths[0]);
				$currentPath->AddSequence($connected_node['to'], $connected_node['route'], $newTimestamp, ceil($connected_node['waittime']));
				
				$nodes[$connected_node['to']]->CompareAndAdd($currentPath);
				
				// Put Connected Ids
				array_push($connected_ids, $connected_node['to']);
			}
			
			$nodes[$currentID]->Marked = true;
			
			if($nodes[$destinationID]->Touched >= $max_result)
			{
				break;
			}
			
			$connected_ids = array_unique($connected_ids);
			
			$connected_stops = array();
			// Calculate Distance
			$nodes[$currentID]->Stop->GetInfo();
            $connect_location = $this->Location;
			foreach($connected_ids as $connected_id)
			{
				// Find Distance				
				
				$connect_stop = new Stop($connected_id);
				$connect_stop->GetInfo();
				
				array_push($connected_stops,
					array("id" => $connected_id,
						"distance" => $destination_location->DistanceTo($connect_location)
					)
				);
			}
			
			$connected_stops = sort_by($connected_stops, "distance", SORT_ASC);
						
			// Find next node
			$nextID = null;
			foreach($connected_stops as $connected_stop)
			{
				$connected_id = $connected_stop['id'];
				
				if($connected_id == $destinationID)
				{
					continue;
				}
				
				if($nodes[$connected_id]->Marked == false)
				{
					$nextID = $connected_id;
					break;
				}
			}
						
			if($nextID == null)
			{
				break;
			}
			
			$currentID = $nextID;
		}
		
		return $nodes[$destinationID]->Paths;
	}

    /**
     * Get the bus stop timetable
     * [{busno, route, remaining_distance, estimated_time, estimated_time_readable, remaining_time, last_stopid, last_update, session, origin}]
     * @return array
     */
	public function TimeTable()
	{
		global $connection;
		
		$function_result = array();
		
		if($this->BusStop == null)
		{
			$this->GetInfo();
		}
		
		if($this->BusStop == 0) // This is not a bus stop!!!
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
			
			$sql = "SELECT `distance_from_start` FROM `route_paths` WHERE `stop` = ? AND `route` = ? ORDER BY `distance_from_start` DESC";
			$results = sql_query($sql, "ii", array($this->ID, $route['id']));
			$num_in_route_paths = mysqli_num_rows($results);
			if($num_in_route_paths > 0)
			{
				$approximated_time_from_start = null;
				
				$stop_distance_data = mysqli_fetch_array($results);
				
				$sql = "SELECT `id`, `last_distance`, `last_update`, `session` FROM `buses` WHERE `session` > 0 AND `route` = ? AND `last_distance` < ? AND ? - `last_update` < 60";
				$busResults = sql_query($sql, "idi", array($route['id'], $stop_distance_data['distance_from_start'], $now));
				while($busData = mysqli_fetch_array($busResults))
				{
					$estimatedTime = null;
					$last_stopid = null;
					
					$sql = "SELECT `start_datetime` FROM `sessions` WHERE `id` = ?";
					$result = sql_query($sql, "i", array($busData['session']));
					$sessionData = mysqli_fetch_array($result);
					
					$sql = "SELECT `datetime`, `stop` FROM `records` WHERE `session` = ? ORDER BY `datetime` DESC";
                    $lastRecordResult = sql_query($sql, "i", array($busData['session']));
					
					if(mysqli_num_rows($lastRecordResult) > 0)
					{
						$lastRecordData = mysqli_fetch_array($lastRecordResult);
						$last_stopid = $lastRecordData['stop'];

						$estimatedTimeBetweenStops = estimated_time($route['id'], $last_stopid, $this->ID, $sessionData['start_datetime']);
						if($estimatedTimeBetweenStops == null)
                        {
                            continue;
                        }
						$estimatedTime = $lastRecordData['datetime'] + $estimatedTimeBetweenStops;
					}
					else
                    {
                        $estimatedTimeAtThisStop = estimated_time($route['id'], $this->ID, $sessionData['start_datetime']);
                        if($estimatedTimeAtThisStop == null)
                        {
                            continue;
                        }
                        $estimatedTime = $sessionData['start_datetime'] + $estimatedTimeAtThisStop;
                    }

                    array_push($function_result,
                        array(
                            "busno" => $busData['id'],
                            "route" => $route['id'],
                            "remaining_distance" => $stop_distance_data['distance_from_start'] - $busData['last_distance'],
                            "estimated_time" => $estimatedTime,
                            "estimated_time_readable" => date("H:i", $estimatedTime),
                            "remaining_time" => $estimatedTime - $now,
                            "last_stopid" => $last_stopid,
                            "last_update" => $busData['last_update'],
                            "session" => $busData['session'],
                            "origin" => false
                        )
                    );
                    $count++;
				}
				
				if($num_in_route_paths == 1 && $count == 0)
				{
				    $estimatedTimeAtThisStop = estimated_time($route['id'], $this->ID, $now);
					if($estimatedTimeAtThisStop == null)
					{
						$dt = mktime(7, 1);
						$estimatedTimeAtThisStop = estimated_time($route['id'], $this->ID, $dt);
					}
					
					if($estimatedTimeAtThisStop != null)
					{
						$sql = "SELECT `start_datetime` FROM `sessions` WHERE `route` = ? ORDER BY `start_datetime` DESC LIMIT 1";
						$result = sql_query($sql, "i", array($route['id']));
						if(mysqli_num_rows($result) > 0)
						{
							$lastSessionData = mysqli_fetch_array($result);
							$wait_time = ceil(wait_time_at($this->ID, $route['id'], $now));
							$estimatedTime = $lastSessionData['start_datetime'] + $wait_time + $estimatedTimeAtThisStop;
							
							if($estimatedTime - $now < $estimatedTimeAtThisStop)
							{
								$estimatedTime = $now + $estimatedTimeAtThisStop;
							}
							
							foreach($info as $info_route)
							{
								if($info_route['route'] == $route['id'])
								{
									if($estimatedTime > $info_route['estimated_last'])
									{
										$estimatedTime = $info_route['estimated_first'] + 86400;;
									}

									if($estimatedTime < $info_route['estimated_first'])
									{
										$estimatedTime = $info_route['estimated_first'];
									}
									
									break;
								}
							}

							array_push($function_result,
								array(
									"busno" => null,
									"route" => $route['id'],
									"remaining_distance" => null,
									"estimated_time" => $estimatedTime,
									"estimated_time_readable" => date("H:i", $estimatedTime),
									"remaining_time" => $estimatedTime - $now,
									"last_stopid" => null,
									"last_update" => null,
									"session" => null,
									"origin" => false
								)
							);
						}
					}
				}
				//else if($num_in_route_paths == 2 && $count == 0)
				//{
				//	$sql = "SELECT `start_datetime` FROM `sessions` WHERE `route` = ? ORDER BY `start_datetime` DESC LIMIT 1";
				//	$result = sql_query($sql, "i", array($route['id']));
				//	if(mysqli_num_rows($result) > 0)
				//	{
				//		$lastSessionData = mysqli_fetch_array($result);
				//		$wait_time = ceil(wait_time_at($this->ID, $route['id'], $now));
				//		$estimatedTime = $lastSessionData['start_datetime'] + $wait_time;
				//		if($estimatedTime - $now < $wait_time)
				//		{
				//			$estimatedTime = $now + $wait_time;
				//		}
				//
				//		foreach($info as $info_route)
				//		{
				//			if($info_route['route'] == $route['id'])
				//			{
				//				if($estimatedTime > $info_route['estimated_last'])
				//				{
				//					$estimatedTime = $info_route['estimated_first'] + 86400;;
				//				}
				//				if($estimatedTime < $info_route['estimated_first'])
				//				{
				//					$estimatedTime = $info_route['estimated_first'];
				//				}
				//
				//				break;
				//			}
				//		}
				//
				//		array_push($function_result,
				//			array(
				//				"busno" => null,
				//				"route" => $route['id'],
				//				"remaining_distance" => null,
				//				"estimated_time" => $estimatedTime,
				//				"estimated_time_readable" => date("H:i", $estimatedTime),
				//				"remaining_time" => $estimatedTime - $now,
				//				"last_stopid" => null,
				//				"last_update" => null,
				//				"session" => null,
				//				"origin" => true
				//			)
				//		);
				//	}
				//}
			}
		}
		
		return $function_result;
	}

    /**
     * Get the passed buses
     * [{session, datetime, datetime_readable, route, routename, routecolor, busno}]
     * @param int $max_result
     * @return array
     */
	public function PassedTimetable($max_result = 10)
	{
		global $connection;
		
		$function_result = array();


		$sql = "SELECT `session`, `datetime` FROM `records` WHERE `stop` = ? ORDER BY `datetime` DESC LIMIT ?";
		$results = sql_query($sql, "ii", array($this->ID, $max_result));
		while($recordData = mysqli_fetch_array($results))
		{
			$session = new Session($recordData['session']);
			$route = new Route($session->Route);
			
			$item = array(
				"session" => $recordData['session'],
				"datetime" => $recordData['datetime'],
				"datetime_readable" => date("H:i", $recordData['datetime']),
				"route" => $session->Route,
				"routename" => get_text("route", $session->Route, get_language_id()),
				"routecolor" => $route->Color,
				"busno" => $session->BusNo
			);
			
			array_push($function_result, $item);
		}
		
		return $function_result;
	}

    /**
     * Get bus stop daily time table (approximated first round, approximated last round and approximated waiting time from each route)
     * [{route, routename, routecolor, estimated_first, estimated_first_readable, estimated_last, estimated_last_readable, waittime}]
     * @return array
     */
	public function DailyTimeTable()
	{
		global $connection;
		
		$function_result = array();
		
		if($this->BusStop == null)
		{
			$this->GetInfo();
		}
		
		if($this->BusStop == 0) // This is not a bus stop!!!
		{
			return $function_result;
		}
		
		$day_to_calculate = 5;
		$today = new Day(mktime(0, 0, 0));
		
		$timezone = date("Z");
		
		$days = array();
		
		$sql = "SELECT `date` FROM `days` WHERE type = {$today->Type} AND `date` < {$today->Timestamp} ORDER BY `date` DESC LIMIT $day_to_calculate";
		$results = mysqli_query($connection, $sql);
		while($dayData = mysqli_fetch_array($results))
		{
			array_push($days, $dayData['date']);
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
				$result = sql_query($sql, "iii", array($route['id'], $day, $day));
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
				$estimated_first = $today->Timestamp + ceil($avg_first);
			}
			
			if($last_count > 0)
			{
				$avg_last = $last_sum / $last_count;
				$estimated_last = $today->Timestamp + ceil($avg_last);
			}

			$sql = "SELECT COUNT(*) AS `route_count` FROM `route_paths` WHERE `route` = ? AND `stop` = ?";
			$result = sql_query($sql, "ii", array($route['id'], $this->ID));
			$countData = mysqli_fetch_array($result);
			if($countData['route_count'] == 1)
            {
                $estimatedTimeToThisStopOnFirstRound = estimated_time($route, $this->ID, $estimated_first);
                if($estimatedTimeToThisStopOnFirstRound != null)
                {
                    $estimated_first += $estimatedTimeToThisStopOnFirstRound;
                }

                $estimatedTimeToThisStopOnLastRound = estimated_time($route, $this->ID, $estimated_last);
                if($estimatedTimeToThisStopOnLastRound != null)
                {
                    $estimated_last += $estimatedTimeToThisStopOnLastRound;
                }
            }
			
			if(date("H") < 7 || date("H") > 21)
			{
				$waittime = wait_time_at($this->ID, $route['id'], mktime(12, 0, 0));
			}
			else
			{
				$waittime = wait_time_at($this->ID, $route['id'], mktime());
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

    /**
     * Get list of available routes that pass this bus stop
     * @param bool $datetime
     * @return array
     */
	public function PassingRoutes($datetime = false)
	{
		global $connection;
		
		$routes = array();
		
		if($datetime == false)
		{
			$datetime = mktime();
		}
		
		foreach(get_routes_at($datetime) as $routeData) if($routeData['available'] == 1)
		{
            array_push($routes, $routeData);
		}
		
		return $routes;
	}
}

/**
 * Class Session (Bus Round)
 */
class Session
{
	private $ID;
	private $BusNo;
	private $Route;
	private $StartTimeStamp;

    /**
     * @param $v
     * @return mixed
     */
    public function __get($v)
	{
		return $this->$v;
	}

    /**
     * Session constructor.
     * @param $ID
     */
    public function __construct($ID)
	{
		$this->ID = $ID;

        $sql = "SELECT `busno`, `route`, `start_datetime` FROM `sessions` WHERE `id` = ?";
        $result = sql_query($sql, "i", array($ID));
		$sessionData = mysqli_fetch_array($result);
		$this->BusNo = $sessionData['busno'];
		$this->Route = $sessionData['route'];
		$this->StartTimeStamp = $sessionData['start_datetime'];
	}

    /**
     * Get the last time record from the session
     * return {stop, datetime}
     * @return array|null
     */
    public function LastRecord()
	{
		$sql = "SELECT `stop`, `datetime` FROM `records` WHERE `session` = ? ORDER BY `datetime` DESC LIMIT 1";
		$result = sql_query($sql, "i", array($this->ID));
		
		if(mysqli_num_rows($result) == 1)
		{
			$recordData = mysqli_fetch_array($result);
		
			return array(
				"stop" => $recordData['stop'],
				"datetime" => $recordData['datetime']
			);
		}
		
		return null;
	}

    /**
     * Get recorded time and estimated arrival time of each bus stop in the bus session
     * return {busno, route, routename, routecolor, start_datetime, start_datetime_readable, online, time_sequences*}
     *   ->time_sequences [{stop, stopname, datetime, datetime_readable, remaining_time, remaining_distance, bool estimated}]
     * @return array
     */
    public function GetStatus()
	{
		global $connection;
		
		$function_result = array();
		
		$online = false;
		$sql = "SELECT `session`, `last_distance` FROM `buses` WHERE `id` = ?";
		$result = sql_query($sql, "i", array($this->BusNo));
		$busData = mysqli_fetch_array($result);
		if($busData['session'] == $this->ID)
		{
			$online = true;
		}
		
		$time_sequences = array();
		
		$last_record = array(
			"stop" => "",
			"datetime" => null,
			"estimated_time" => 0
		);

		$recordData = null;

		$sql = "SELECT `stop`, `datetime` FROM `records` WHERE `session` = ? ORDER BY `datetime` ASC";
		$results = sql_query($sql, "i", array($this->ID));
		while($recordData = mysqli_fetch_array($results))
		{
			$item = array(
				"stop" => $recordData['stop'],
				"stopname" => get_text("stop", $recordData['stop'], get_language_id()),
				"datetime" => $recordData['datetime'],
				"datetime_readable" => date("H:i", $recordData['datetime']),
				"remaining_time" => null,
				"remaining_distance" => null,
				"estimated" => false
			);
			
			array_push($time_sequences, $item);
		}

        if(count($time_sequences) > 0)
        {
            $last_record['stop'] = $recordData['stop'];
            $last_record['datetime'] = $recordData['datetime'];

            $sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = ? AND `stop` = ? AND (? BETWEEN `start_time` AND `end_time`)";
            $result = sql_query($sql, "iii", array($this->Route, $recordData['stop'], $this->StartTimeStamp));

            if(mysqli_num_rows($result) > 0)
            {
                $estimatedData = mysqli_fetch_array($result);
                $last_record['estimated_time'] = $estimatedData['estimated_time'];
            }
        }
		
		if($online == true)
		{
			$now = mktime();

			$sql = "SELECT `stop`, `distance_from_start` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL AND `distance_from_start` > ?";
			$results = sql_query($sql, "id", array($this->Route, $busData['last_distance']));
			while($nextStopData = mysqli_fetch_array($results))
			{
				$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$this->Route} AND `stop` = {$nextStopData['stop']} AND ({$this->StartTimeStamp} BETWEEN `start_time` AND `end_time`)";
				$result = mysqli_query($connection, $sql);
				
				$dt = null;
				if(mysqli_num_rows($result) > 0)
				{
					$estimatedData = mysqli_fetch_array($result);
					
					$dt = $last_record['datetime'] + ($estimatedData['estimated_time'] - $last_record['estimated_time']);
				}
				
				$item = array(
					"stop" => $nextStopData['stop'],
					"stopname" => get_text("stop", $nextStopData['stop'], get_language_id()),
					"datetime" => $dt,
					"datetime_readable" => date("H:i", $dt),
					"remaining_time" => $dt - $now,
					"remaining_distance" => $nextStopData['distance_from_start'] - $busData['last_distance'],
					"estimated" => true
				);
			
				array_push($time_sequences, $item);
			}
		}

		$route = new Route($this->Route);
		$function_result['busno'] = $this->BusNo;
		$function_result['route'] = $this->Route;
		$function_result['routename'] = get_text("route", $this->Route, get_language_id());
		$function_result['routecolor'] = $route->Color;
		$function_result['start_datetime'] = $this->StartTimeStamp;
		$function_result['start_datetime_readable'] = date("H:i", $this->StartTimeStamp);
		$function_result['online'] = $online;
		$function_result['time_sequences'] = $time_sequences;
		
		return $function_result;
	}
}

/**
 * Class Route
 */
class Route
{
	private $ID;
	private $Name;
	private $Color;
    /**
     * [{stop (obj), distance_from_start}]
     * @var array
     */
	private $Path;

    /**
     * @param $v
     * @return mixed
     */
    public function __get($v)
	{
		return $this->$v;
	}

    /**
     * Route constructor.
     * @param $ID
     */
    public function __construct($ID)
	{
		global $connection;
		
		$this->ID = $ID;

        $sql = "SELECT `name`, `color` FROM `routes` WHERE `id` = ?";
        $result = sql_query($sql, "i", array($ID));

		$routeData = mysqli_fetch_array($result);
		
		$this->Name = $routeData['name'];
		$this->Color = $routeData['color'];
		
		$this->Path = array();
		
		$sql = "SELECT `stop`, `distance_from_start` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
		$results = sql_query($sql, "i", array($this->ID));
		while($stopdata = mysqli_fetch_array($results))
		{
			array_push($this->Path, array("stop" => new Stop($stopdata['stop']), "distance_from_start" => $stopdata['distance_from_start']));
		}
	}
}

/**
 * Class Node
 */
class Node
{
    /** @var Stop  */
	private $Stop;
	/** @var Path[] */
    private $Paths;

    public $MaxPath = 2;
    public $Marked = false;
    public $Touched = 0;

    /**
     * @param $v
     * @return mixed
     */
	public function __get($v)
	{
		return $this->$v;
	}

    /**
     * Node constructor.
     * @param $stopID
     * @param int $max_path
     */
	public function __construct($stopID, $max_path = 2)
	{
		$this->Stop = new Stop($stopID);
		$this->MaxPath = $max_path;
	}

    /**
     * Compare introduced path with current path in this node and add to the node if the path is better
     * @param $path
     * @param string $mode
     */
	public function CompareAndAdd(Path $path)
	{
		for($i = 0; $i < $this->MaxPath; $i++)
		{
			if($path->IsBetterThan($this->Paths[$i]) == true)
			{
                for($j = $this->MaxPath - 2; $j <= $i; $j--)
                {
                    $this->Paths[$j + 1] = $this->Paths[$j];
                }

                $this->Touched++;
                $this->Paths[$i] = $path;

				break;
			}
		}
	}

    /**
     * Get the last node time stamp from best path
     * @return null
     */
	public function LastTimeStamp()
	{
		if(count($this->Paths[0]) == null)
		{
			return null;
		}
		return $this->Paths[0]->LastSequence()['timestamp'];
	}

    /**
     * Check if all nodes in given array are marked as traveled
     * @param $nodes
     * @return bool
     */
	public static function AllMarked($nodes)
	{
		foreach($nodes as $node)
		{
		    /** @var $node Node */
			if($node->marked == false)
			{
				return false;
			}
		}
		
		return true;
	}
}

/**
 * Class Path
 */
class Path
{
    /**
     * [{stopid, stopname, stoplocation: {lat, lon}, route, routename, routecolor, timestamp, traveltime, waittime, time]
     * @var array
     */
    private $Sequences;
    private $StartID;
    private $StartTime;
	private $TotalTime = 0;
	private $HopCount = 0;

    /**
     * @param $v
     * @return mixed
     */
	public function __get($v)
	{
		return $this->$v;
	}

    /**
     * Path constructor.
     * @param $start_id
     * @param $start_time
     */
	public function __construct($start_id, $start_time)
	{
		$this->Sequences = array();
		$this->StartID = $start_id;
		$this->StartTime = $start_time;
	}

    /**
     * Get the last sequence of the path
     * @return mixed|null
     */
	public function LastSequence()
	{
		if(count($this->Sequences) > 0)
		{
			return end($this->Sequences);
		}
		
		return null;
	}

    /**
     * Add a new sequence to this path
     * @param $stop
     * @param $route
     * @param null|int $timestamp
     * @param int $waitTime
     */
	public function AddSequence($stop, $route, $timestamp = null, $waitTime = 0)
	{
		if($timestamp == null)
		{
			$timestamp = mktime();
		}
		
		array_push($this->Sequences, array("to" => $stop, "route" => $route, "timestamp" => $timestamp, "waittime" => $waitTime));
		$this->TotalTime = ($timestamp - $this->StartTime);
		$this->HopCount++;
	}

    /**
     * Check if the current path is better than parameterized path
     * @param $path
     * @return bool
     */
	public function IsBetterThan(&$path)
	{
		if($path == null)
		{
			return true;
		}

		if($this->TotalTime < $path->totalTime)
		{
			return true;
		}
		else if($this->TotalTime == $path->totalTime && $this->HopCount < $path->hopCount)
		{
			return true;
		}

		return false;
	}

    /**
     * Check path format to be easier to read from API
     * FORMAT: [{stopid, stopname, stoplocation: {lat, lon}, route, routename, routecolor, timestamp, traveltime, waittime, time}]
     * @return array
     */
	public function Readable()
	{
		$function_result = array();
		$stop = new Stop($this->StartID);
		$stop->GetInfo();
		
		array_push($function_result,
			array(
				"stopid" => $stop->ID,
				"stopname" => get_text("stop", $stop->ID, get_language_id()),
				"stoplocation" => array(
					"lat" => $stop->Location->lat,
					"lon" => $stop->Location->lon
				),
				"route" => null,
				"routename" => null,
				"routecolor" => "cdcdcd",
				"timestamp" => $this->StartTime,
				"traveltime" => 0,
				"waittime" => 0,
				"time" => date("H:i", $this->StartTime)
			)
		);
		
		$lastTimestamp = $this->StartTime;
		
		foreach($this->Sequences as $sequence)
		{
			$stop = new Stop($sequence['to']);
			$stop->GetInfo();
			$route = $sequence['route'];
			$timestamp = $sequence['timestamp'];
			$waittime = $sequence['waittime'];
			$traveltime = $sequence['timestamp'] - $lastTimestamp - $waittime;
			
			$routename = null;
			$routecolor = "cdcdcd";
			if($route != null)
			{
				$r = new Route($route);
				$routename = get_text("route", $r->ID, get_language_id());
				$routecolor = $r->Color;
			}
			
			array_push($function_result,
				array(
					"stopid" => $stop->ID,
					"stopname" => $stop->Translate(get_language_id()),
					"stoplocation" => array(
						"lat" => $stop->Location->lat,
						"lon" => $stop->Location->lon
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
			
			$lastTimestamp = $timestamp;
		}
		return($function_result);
	}
}

/**
 * Query estimated time from origin from database with specified route, bus stop and time
 * @param $route
 * @param $stop
 * @param $timestamp
 * @return mixed
 */
function estimated_time($route, $stop, $timestamp)
{
    $sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = ? AND `stop` = ? AND (? BETWEEN `start_time` AND `end_time`)";
    $result = sql_query($sql, "iii", array($route, $stop, $timestamp));
    $estimatedData = mysqli_fetch_array($result);

    if(mysqli_num_rows($result) == 0)
    {
        return null;
    }

    return $estimatedData['estimated_time'];
}

/**
 * Get estimated time between specified bus stops in specified route and time
 * @param $route
 * @param $stop1
 * @param $stop2
 * @param $timestamp
 * @return mixed
 */
function estimated_time_between($route, $stop1, $stop2, $timestamp)
{
    $sql = "SELECT `distance_from_start` FROM `route_paths` WHERE `route` = ? AND `stop` = ? ORDER BY `distance_from_start` ASC";
    $result = sql_query($sql, "ii", array($route, $stop1));
    $stop1DistanceData = mysqli_fetch_array($result);

    if($stop1DistanceData['distance_form_start'] == 0)
    {
        return estimated_time($route, $stop2, $timestamp);
    }

    $stop2EstimatedTime = estimated_time($route, $stop2, $timestamp);
    if($stop2EstimatedTime == null)
    {
        return null;
    }

    $stop1EstimatedTime = estimated_time($route, $stop1, $timestamp);
    if($stop1EstimatedTime == null)
    {
        return null;
    }

    return $stop2EstimatedTime - $stop1EstimatedTime;
}

/**
 * Make SQL query with stmt bind parameter
 * @param $sqlParseText
 * @param string $variableTypes
 * @param array $variableValuesArray
 * @return bool|mysqli_result
 */
function sql_query($sqlParseText, $variableTypes = "", $variableValuesArray = array())
{
    global $connection;

    $stmtObj = $connection->prepare($sqlParseText);
    call_user_func_array(array($stmtObj, "bind_param"), array_merge(array($variableTypes), $variableValuesArray));

    $stmtObj->execute();

    return $stmtObj->get_result();
}

/**
 * Return approximated waiting time at specified route, busstop and time
 * @param $stop
 * @param $route
 * @param bool $datetime
 * @return mixed
 */
function wait_time_at($stop, $route, $datetime = false)
{	
	global $connection;	
	
	// Default time is NOW
	if($datetime == false)
	{
		$datetime = mktime();
	}

	$sql = "SELECT `waittime` FROM `time_estimation` WHERE `stop` = ? AND `route` = ? AND ? BETWEEN `start_time` AND `end_time`";
	$result = sql_query($sql, "iii", array($stop, $route, $datetime));
	if(mysqli_num_rows($result) > 0)
	{
		$waitTimeData = mysqli_fetch_array($result);
	}
	else
	{
		return wait_time_at($stop, $route, mktime(12, 0, 0));
	}
	
	return $waitTimeData['waittime'];
}

/**
 * Get available path at specified stop
 * [{to, time, waittime, route=int|null}]
 * @param $stop
 * @param bool $datetime
 * @param bool $bus
 * @param bool $walk
 * @return array
 */
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
		$sql = "SELECT `stop1`, `stop2`, `connection_time` FROM `connections` WHERE `stop1` = ? OR `stop2` = ?";
		$results = sql_query($sql, "ii", array($stop, $stop));
		while($connectionData = mysqli_fetch_array($results))
		{
			if($connectionData['stop1'] == $stop)
			{
				array_push($function_result,
					array(
						"to" => $connectionData['stop2'],
						"time" => $connectionData['connection_time'] * 1,
						"waittime" => null,
						"route" => null
					)
				);
			}
			else if($connectionData['stop2'] == $stop)
			{
				array_push($function_result,
					array(
						"to" => $connectionData['stop1'],
						"time" => $connectionData['connection_time'] * 1,
						"waittime" => null,
						"route" => null
					)
				);
			}
		}
	}
	
	if($bus == true)
	{
		foreach(get_routes_at($datetime) as $routeData) if($routeData['available'] == 1)
		{			
			$sql = "SELECT `distance_from_start` FROM `route_paths` WHERE `route` = ? AND `stop` = ? ORDER BY `distance_from_start` ASC LIMIT 1";
			$result = sql_query($sql, "ii", array($routeData['id'], $stop));
			if(mysqli_num_rows($result) == 1)
			{
				$waittime = wait_time_at($stop, $routeData['id'], $datetime);
				$datetime += $waittime;
			
				$distanceData = mysqli_fetch_array($result);
				$stop_distance = $distanceData['distance_from_start'];
				
				$time_to_stop = 0;
				if($stop_distance > 0)
				{
					$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = ? AND `stop` = ? AND (? BETWEEN `start_time` AND `end_time`)";
					$result = sql_query($sql, "iii", array($routeData['id'], $stop, $datetime));
					if(mysqli_num_rows($result) > 0)
					{
						$estimatedData = mysqli_fetch_array($result);
						$time_to_stop = $estimatedData['estimated_time'];
					}
				}
				
				$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL AND `distance_from_start` > ?";
				$results = sql_query($sql, "id", array($routeData['id'], $stop_distance));
				while($stopData = mysqli_fetch_array($results))
				{
					$time = 0;
					
					if($time_to_stop >= 0)
					{
						$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = ? AND `stop` = ? AND (? BETWEEN `start_time` AND `end_time`)";
						$result = sql_query($sql, "iii", array($routeData, $stopData, $datetime));
						if(mysqli_num_rows($result) > 0)
						{
							$estimatedData = mysqli_fetch_array($result);
							$time = $estimatedData['estimated_time'] - $time_to_stop;
						}
					}
					
					array_push($function_result,
						array(	"to" => $stopData['stop'],
								"time" => $time,
								"waittime" => $waittime,
								"route" => $routeData['id']
							)
					);
				}
			}
		}
	}
	
	return $function_result;
}

/**
 * Sort array by given key
 * @param $array
 * @param $key
 * @param int $sort_type
 * @return mixed
 */
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