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
     * @param bool $quickSearch
     * @return Node[]
     */
    public function FindPathsTo($destinationID, $datetime = false, $max_result = 1, $quickSearch = false)
	{
		global $connection;

		if($datetime == false)
		{
			$datetime = mktime();
		}

		$this->GetInfo();
		
		$destination_stop = new Stop($destinationID);
		$destination_stop->GetInfo();
		$destination_location = $destination_stop->Location;
		
		// Prepare nodes to be an array which key is its id
		$nodes = array();
        /**
         * @var $nodes Node[]
         */
		
		$sql = "SELECT `id` FROM `stops`";
		$results = sql_query($sql);
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
				&& $nodes[$currentID]->Paths[0]->HopCount > 0
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
			
			if($quickSearch == true && count($nodes[$destinationID]->Paths) >= $max_result)
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

                if($nodes[$connected_id]->Marked == true)
                {
                    continue;
                }
				
				$connect_stop = new Stop($connected_id);
				$connect_stop->GetInfo();
				
				array_push($connected_stops,
					array("id" => $connected_id,
						"distance" => $connect_stop->Location->DistanceTo($destination_location)
					)
				);
			}

			if($quickSearch == true)
            {
			    $connected_stops = sort_by($connected_stops, "distance", SORT_ASC);
            }
						
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

		$day = new Day(mktime(0, 0, 0));

		if($Hr > 21)
        {
            $tomorrow = new Day(mktime(0, 0, 0) + 86400);

            if($day->Type != $tomorrow->Type)
            {
                $day = get_latest_day_on_type($tomorrow->Type);
            }
        }
		
		$info = array();
		if($out)
		{
			$info = $this->DailyTimeTable($day);
		}
		
		$now = mktime();
		
		foreach($this->PassingRoutes($now) as $route)
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

						$estimatedTimeBetweenStops = estimated_time_between($route['id'], $last_stopid, $this->ID, $sessionData['start_datetime']);
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
     * @param Day|bool $day
     * @return array
     */
	public function DailyTimeTable($day = false)
	{
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

		$today = null;

		if($day == false)
        {
		    $today = new Day(mktime(0, 0, 0));
        }
        else
        {
            $today = $day;
        }
		
		$timezone = date("Z");
		
		$days = array();
		
		$sql = "SELECT `date` FROM `days` WHERE type = ? AND `date` < ? ORDER BY `date` DESC LIMIT $day_to_calculate";
        $results = sql_query($sql, "ii", array($today->Type, $today->Timestamp));
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
				$sql = "SELECT MIN(`start_datetime`) AS 'min_dt', MAX(`start_datetime`) AS 'max_dt' FROM `sessions` WHERE `route` = ? AND (`start_datetime` BETWEEN ? AND ?)";
				$result = sql_query($sql, "iii", array($route['id'], $day, $day + 86399));
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
                $estimatedTimeToThisStopOnFirstRound = estimated_time($route['id'], $this->ID, $estimated_first);
                if($estimatedTimeToThisStopOnFirstRound != null)
                {
                    $estimated_first += $estimatedTimeToThisStopOnFirstRound;
                }

                $estimatedTimeToThisStopOnLastRound = estimated_time($route['id'], $this->ID, $estimated_last);
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

		$passRoutes = array();

		$sql = "SELECT DISTINCT `route` FROM `route_paths` WHERE `stop` = ?";
		$result = sql_query($sql, "i", array($this->ID));
		while($routeIDData = mysqli_fetch_array($result))
        {
            array_push($passRoutes, $routeIDData['route']);
        }
		
		foreach(get_routes_at($datetime) as $routeData) if($routeData['available'] == 1)
		{
		    if(in_array($routeData['id'], $passRoutes))
            {
                array_push($routes, $routeData);
            }
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
			"datetime" => null
		);

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

            $last_record['stop'] = $recordData['stop'];
            $last_record['datetime'] = $recordData['datetime'];
		}
		
		if($online == true)
		{
			$now = mktime();

			$sql = "SELECT `stop`, `distance_from_start` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL AND `distance_from_start` > ?";
			$results = sql_query($sql, "id", array($this->Route, $busData['last_distance']));
			while($nextStopData = mysqli_fetch_array($results))
			{
			    $estimatedArrivalTimestamp = null;

				$timeFromLastRecordToThisStop = estimated_time_between($this->Route, $last_record['stop'], $nextStopData['stop'], $this->StartTimeStamp);
				if($timeFromLastRecordToThisStop != null)
                {
				    $estimatedArrivalTimestamp = $last_record['datetime'] + $timeFromLastRecordToThisStop;
                }
				
				$item = array(
					"stop" => $nextStopData['stop'],
					"stopname" => get_text("stop", $nextStopData['stop'], get_language_id()),
					"datetime" => $estimatedArrivalTimestamp,
					"datetime_readable" => date("H:i", $estimatedArrivalTimestamp),
					"remaining_time" => $estimatedArrivalTimestamp - $now,
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
                for($j = count($this->Paths) - 2; $j >= $i; $j--)
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
			if($node->Marked == false)
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

		if($this->TotalTime < $path->TotalTime)
		{
			return true;
		}
		else if($this->TotalTime == $path->TotalTime && $this->HopCount < $path->HopCount)
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
					"stopname" => get_text("stop", $stop->ID, get_language_id()),
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
 * Class Day
 */
class Day
{
    private $Timestamp;
    private $Type;
    private $Detail;

    /**
     * @param $v
     * @return mixed
     */
    public function __get($v)
    {
        return $this->$v;
    }

    /**
     * Day constructor.
     * @param $day
     */
    public function __construct($day)
    {
        if(date("His", $day) != "000000")
        {
            unset($this);
            return false;
        }

        $sql = "SELECT `type`, `detail` FROM `days` WHERE `date` = ?";
        $result = sql_query($sql, "i", array($day));
        $numRows = mysqli_num_rows($result);

        if($numRows == 0)
        {
            if(date("N", $day) > 5) // วันหยุด
            {
                $this->Type = -1;
            }
            else // วันธรรมดา
            {
                $this->Type = 0;
            }

            $sql = "INSERT INTO `days` (`date`, `type`, `detail`) VALUES (?, ?, ?)";
            sql_query($sql, "iis", array($day, $this->Type, ""));

            $this->Timestamp = $day;
            $this->Detail = "";
        }
        else
        {
            $dayData = mysqli_fetch_array($result);

            $this->Timestamp = $day;
            $this->Type = $dayData['type'];
            $this->Detail = $dayData['detail'];
        }
    }

    /**
     * Return day type as string
     * @return mixed
     */
    public function TypeToString()
    {
        global $connection;

        $sql = "SELECT `name` FROM `day_types` WHERE `id` = ?";
        $result = sql_query($sql, "i", array($this->Type));
        $data = mysqli_fetch_array($result);

        return $data['name'];
    }

    /**
     * Set day type
     * @param $newType
     */
    public function SetTypeTo($newType)
    {
        $sql = "UPDATE `days` SET `type` = ? WHERE `date` = ?";
        sql_query($sql, "ii", array($newType, $this->Timestamp));

        $this->Type = $newType;
    }

    /**
     * Set day detail
     * @param $newDetail
     */
    public function SetDetail($newDetail)
    {
        $sql = "UPDATE `days` SET `detail` = ? WHERE `date` = ?";
        sql_query($sql, "si", array($newDetail, $this->Timestamp));

        $this->Detail = $newDetail;
    }

    /**
     * Get the last timestamp of this day (23:59:59)
     * @return mixed
     */
    public function GetFinal()
    {
        return $this->Timestamp + 86399;
    }
}

/**
 * Class Location
 */
class Location
{
    public $lat;
    public $lon;

    /**
     * Location constructor.
     * @param $latitude
     * @param $longitude
     */
    public function __construct($latitude, $longitude)
    {
        $this->lat = $latitude;
        $this->lon = $longitude;
    }

    /**
     * Calculate distance in metres to specified location
     * @param Location $location
     * @return int
     */
    public function DistanceTo(Location $location)
    {
        $lat2 = $location->lat;
        $lon2 = $location->lon;
        return 6371000 * 2 * asin(sqrt(pow(sin(($this->lat - abs($lat2)) * pi() / 180 / 2), 2) + cos($this->lat * pi() / 180) * pow(sin(($this->lon - $lon2) * pi() / 180 / 2), 2)));
    }

    /**
     * Calculate the shortest distance to specified segment which is formed by two points of Location object
     * Algorithm from: https://www.sitepoint.com/community/t/distance-between-long-lat-point-and-line-segment/50583/2s
     * @param Location $seg1
     * @param Location $seg2
     * @return float|int
     */
    public function DistanceToLine(Location $seg1, Location $seg2)
    {
        $ab = $seg1->DistanceTo($seg2);
        $ac = $seg1->DistanceTo($this);
        $bc = $seg2->DistanceTo($this);

        $A = $bc;
        $B = $ac;
        $C = $ab;

        $angle_A = rad2deg(acos((pow($B, 2) + pow($C, 2) - pow($A, 2)) / (2 * $B * $C)));
        $angle_B = rad2deg(acos((pow($C, 2) + pow($A, 2) - pow($B, 2)) / (2 * $C * $A)));
        $angle_C = rad2deg(acos((pow($A, 2) + pow($B, 2) - pow($C, 2)) / (2 * $A * $B)));

        if($ab + $ac == $bc)
        {
            return 0;
        }
        else if($angle_A <= 90 && $angle_B <= 90)
        {
            $s = ($ab + $ac + $bc) / 2;
            $area = sqrt($s * ($s - $ab) * ($s - $ac) * ($s - $bc));
            $height = $area / (0.5 * $ab);
            return $height;
        }
        else
        {
            return ($ac > $bc) ? $bc : $ac;
        }
    }

    /**
     * Calculate the angle to specified location, North is 0º, South is 180º
     * @param Location $location
     * @return float|int
     */
    public function DegreeTo(Location $location)
    {
        $lat2 = $location->lat;
        $lon2 = $location->lon;

        $dlon = $lon2 - $this->lon;

        $y = sin($dlon) * cos($lat2);
        $x = cos($this->lat) * sin($lat2) - sin($this->lat) * cos($lat2) * cos($dlon);

        $result = atan2($y, $x);
        $result = rad2deg($result);
        $result = ($result + 360) % 360;
        return $result;
    }

    /**
     * Calculate the difference of two angles
     * @param $deg1
     * @param $deg2
     * @return int
     */
    public static function DegreeDiff($deg1, $deg2)
    {
        $deg1 %= 360;
        $deg2 %= 360;

        return abs($deg1 - $deg2) % 360;
    }
}

/**
 * Get the latest day object of specified type
 * @param int $type
 * @return Day
 */
function get_latest_day_on_type($type)
{
    $sql = "SELECT `date` FROM `days` WHERE `type` = ? AND `date` < ? ORDER BY `date` DESC LIMIT 1";
    $result = sql_query($sql, "ii", array($type, mktime(0, 0, 0)));
    $dayData = mysqli_fetch_array($result);

    return new Day($dayData['date']);
}

/**
 * Query estimated time from origin from database with specified route, bus stop and time
 * @param int $route
 * @param int $stop
 * @param int $timestamp
 * @param bool $recursivelyCalled = false
 * @return mixed
 */
function estimated_time($route, $stop, $timestamp, $recursivelyCalled = false)
{
    $sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = ? AND `stop` = ? AND (? BETWEEN `start_time` AND `end_time`)";
    $result = sql_query($sql, "iii", array($route, $stop, $timestamp));

    if(mysqli_num_rows($result) == 0)
    {
        if($recursivelyCalled == true)
        {
            return null;
        }
        else
        {
            return estimated_time($route, $stop, $timestamp, true);
        }
    }

    $estimatedData = mysqli_fetch_array($result);

    return $estimatedData['estimated_time'];
}

/**
 * Get estimated time between specified bus stops in specified route and time
 * @param int $route
 * @param int $stop1
 * @param int $stop2
 * @param int $timestamp
 * @param bool $recursivelyCalled = false
 * @return mixed
 */
function estimated_time_between($route, $stop1, $stop2, $timestamp)
{
    $sql = "SELECT `distance_from_start` FROM `route_paths` WHERE `route` = ? AND `stop` = ? ORDER BY `distance_from_start` ASC";
    $result = sql_query($sql, "ii", array($route, $stop1));

    if(mysqli_num_rows($result) == 0)
    {
        return null;
    }

    $stop1DistanceData = mysqli_fetch_array($result);

    if($stop1DistanceData['distance_from_start'] == 0)
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

    if($variableTypes == "")
    {
        return mysqli_query($connection, $sqlParseText);
    }

    if(strlen($variableTypes) != count($variableValuesArray))
    {
        return false;
    }

    $variableReferencesArray = array();
    foreach($variableValuesArray as $key => $value)
    {
        $variableReferencesArray[$key] = &$variableValuesArray[$key];
    }

    $stmtObj = $connection->prepare($sqlParseText);
    if($stmtObj == false)
    {
        return false;
    }
    call_user_func_array(array($stmtObj, "bind_param"), array_merge(array($variableTypes), $variableReferencesArray));

    $stmtObj->execute();

    return $stmtObj->get_result();
}

/**
 * Return approximated waiting time at specified route, busstop and time
 * @param $stop
 * @param $route
 * @param int|bool $datetime
 * @param bool $temporaryWaitTime
 * @return int
 */
function wait_time_at($stop, $route, $datetime = false, $temporaryWaitTime = false)
{	
	global $connection;	
	
	// Default time is NOW
	if($datetime == false)
	{
		$datetime = mktime();
	}

	$sql = "SELECT `waittime` FROM `time_estimation` WHERE `stop` = ? AND `route` = ? AND ? BETWEEN `start_time` AND `end_time`";
	//error_log("SELECT `waittime` FROM `time_estimation` WHERE `stop` = $stop AND `route` = $route AND $datetime BETWEEN `start_time` AND `end_time`");
	$result = sql_query($sql, "iii", array($stop, $route, $datetime));
	if(mysqli_num_rows($result) > 0)
	{
		$waitTimeData = mysqli_fetch_array($result);
	}
	else
	{
	    if($temporaryWaitTime == true)
        {
            return 0;
        }

		return wait_time_at($stop, $route, mktime(12, 0, 0), true);
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
				
				$sql = "SELECT `stop` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL AND `distance_from_start` > ?";
				$results = sql_query($sql, "id", array($routeData['id'], $stop_distance));
				while($stopData = mysqli_fetch_array($results))
				{
				    $time = estimated_time_between($routeData['id'], $stop, $stopData['stop'], $datetime);
					
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

/**
 * Change timestamp to another day by remaining the time
 * @param $timestamp
 * @param Day $day
 * @return mixed
 */
function shift_day($timestamp, $day)
{
    $time = ((date("H", $timestamp) * 3600) + (date("i", $timestamp) * 60) + date("s", $timestamp));
    return $day->Timestamp + $time;
}

/**
 * Change timestamp to day object
 * @param $timestamp
 * @return Day
 */
function get_day_from_timestamp($timestamp)
{
    return new Day($timestamp - (date("H", $timestamp)*3600 + date("i", $timestamp)*60 + date("s", $timestamp)));
}

/**
 * Get routes status at specified time
 * @param $time
 * @return array
 */
function get_routes_at($time)
{
    $routes = array();
    $now = mktime();

    if($now > $time)
    {
        $time = $now;
    }

    $i = 0;
    $sql = "SELECT `id`, `refid`, `color`, `name`, `available`, `detail` FROM `routes` ORDER BY `name` ASC, `detail` ASC";
    $results = sql_query($sql);
    while($routeData = mysqli_fetch_array($results))
    {
        $available = $routeData['available'];

        if($time != $now)
        {
            $sql = "SELECT `set_available_to` FROM `route_available_switchers` WHERE `route` = ? AND `date` BETWEEN ? AND ? ORDER BY `date` DESC LIMIT 1";
            $result = sql_query($sql, "iii", array($routeData['id'], $now, $time));
            $numRows = mysqli_num_rows($result);

            if($numRows == 1)
            {
                $data = mysqli_fetch_array($result);

                $available = $data['set_available_to'];
            }
        }

        $routename = $routeData['name'];
        if(function_exists("get_text"))
        {
            $routename = get_text("route", $routeData['id'], get_language_id());
        }

        $routes[$i] = array(
            "id" => $routeData['id'],
            "refid" => $routeData['refid'],
            "color" => $routeData['color'],
            "name" => $routename,
            "detail" => $routeData['detail'],
            "available" => $available
        );

        $i++;
    }

    return $routes;
}

/**
 * Thai date format
 *  ฿วทวท		วันที่แบบมีเลขศูนย์นำหลักหน่วย
 *  ฿วท		วันที่แบบไม่มีเลขศูนย์นำหลักหน่วย
 *  ฿วว		วันแบบเต็ม
 *  ฿ว		วันแบบย่อ
 *  ฿ดด		เดือนแบบเต็ม
 *  ฿ด		เดือนแบบย่อ
 *  ฿ปปปป		ปี พ.ศ. แบบสี่หลัก
 *  ฿ปป		ปี พ.ศ. แบบสองหลักหลัง
 * @param $format
 * @param bool $timestamp
 * @return false|string
 */
function thai_date($format, $timestamp = false)
{
    // ให้เวลาเป็นเวลาปัจจุบัน ในกรณีที่ไม่มีอินพุตด้านเวลา
    if($timestamp == false)
    {
        $timestamp = mktime();
    }

    // ชื่อเดือนแบบเต็มและแบบย่อเป็นภาษาไทย
    $monthtext = array("",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม");
    $monthtext_short = array("",
        "ม.ค.",
        "ก.พ.",
        "มี.ค.",
        "เม.ย.",
        "พ.ค.",
        "มิ.ย.",
        "ก.ค.",
        "ส.ค.",
        "ก.ย.",
        "ต.ค.",
        "พ.ย.",
        "ธ.ค.");

    // ชื่อวันแบบเต็มและแบบย่อเป็นภาษาไทย
    $daytext = array(
        "อาทิตย์",
        "จันทร์",
        "อังคาร",
        "พุธ",
        "พฤหัสบดี",
        "ศุกร์",
        "เสาร์");
    $daytext_short = array(
        "อา.",
        "จ.",
        "อ.",
        "พ.",
        "พฤ.",
        "ศ.",
        "ส.");

    //สตริงสำหรับค้นหาและแทนที่วันที่ให้เป็นภาษาไทย โดยมีตัวเลือกดังนี้
    ## ฿วทวท		วันที่แบบมีเลขศูนย์นำหลักหน่วย
    ## ฿วท		วันที่แบบไม่มีเลขศูนย์นำหลักหน่วย
    ## ฿วว		วันแบบเต็ม
    ## ฿ว		วันแบบย่อ
    ## ฿ดด		เดือนแบบเต็ม
    ## ฿ด		เดือนแบบย่อ
    ## ฿ปปปป		ปี พ.ศ. แบบสี่หลัก
    ## ฿ปป		ปี พ.ศ. แบบสองหลักหลัง
    $search = array(
        "฿วทวท",
        "฿วท",
        "฿วว",
        "฿ว",
        "฿ดด",
        "฿ด",
        "฿ปปปป",
        "฿ปป"
    );
    $replace = array(
        "d",
        "j",
        $daytext[date("w", $timestamp)],
        $daytext_short[date("w", $timestamp)],
        $monthtext[date("n", $timestamp)],
        $monthtext_short[date("n", $timestamp)],
        date("Y", $timestamp) + 543,
        substr(date("Y", $timestamp) + 543, strlen(date("Y", $timestamp) + 543) - 2, 2)
    );

    $format = str_replace($search, $replace, $format);

    return date($format, $timestamp);
}

/**
 * Get 0:00:00 timestamp
 * @param $timestamp
 * @return mixed
 */
function get_start_day($timestamp)
{
    return $timestamp - ($timestamp % 86400);
}

/**
 * Read session and return user language id (e.g. en, th)
 * Create session if language session does not exist
 * 	Default language is Thai (th)
 * @return string
 */
function get_language_id()
{
    if(isset($_SESSION['user']) && isset($_SESSION['user']['language']))
    {
        return $_SESSION['user']['language'];
    }

    if(isset($_COOKIE['user_language']))
    {
        $sql = "SELECT COUNT(*) AS 'count' FROM `languages` WHERE `id` = ?";
        $result = sql_query($sql, "s", array($_COOKIE['user_language']));
        $languageCountData = mysqli_fetch_array($result);

        if($languageCountData['count'] == 1)
        {
            $_SESSION['user']['language'] = $_COOKIE['user_language'];
            return $_SESSION['user']['language'];
        }
    }

    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
        $accepted_languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    else
    {
        $accepted_languages = "th";
    }

    $accepted_languages_array = explode(",", $accepted_languages);

    foreach($accepted_languages_array as $language)
    {
        $language_ = explode(";", $language);
        $language_ = explode("-", $language_[0]);
        $language = trim($language_[0]);

        $sql = "SELECT `id` FROM `languages` WHERE `id` = ? AND `available` = 1";
        $result = sql_query($sql, "s", array($language));
        if(mysqli_num_rows($result) == 1)
        {
            $_SESSION['user']['language'] = $language;
            return $language;
        }
    }

    $_SESSION['user']['language'] = "th";

    return $_SESSION['user']['language'];
}

/**
 * Set language ID to session
 * @param $language_id
 */
function set_language_id($language_id)
{
    $_SESSION['user']['language'] = $language_id;
}

/**
 * Get text from specified language
 * @param string $ref_type
 * @param int $ref_id
 * @param string $language
 * @return string|bool
 */
function get_text($ref_type, $ref_id, $language)
{
    $ref_type = urlencode($ref_type);
    $ref_id = urlencode($ref_id);

    if($ref_type == "stop_tag")
    {
        return false;
    }

    if($language == "th")
    {
        if($ref_type == "stop")
        {
            $sql = "SELECT `name` FROM `stops` WHERE `id` = ?";
            $result = sql_query($sql, "i", array($ref_id));

            if(mysqli_num_rows($result) == 1)
            {
                $stopdata = mysqli_fetch_array($result);

                return $stopdata['name'];
            }
        }
        else if($ref_type == "route")
        {
            $sql = "SELECT `name` FROM `routes` WHERE `id` = ?";
            $result = sql_query($sql, "i", array($ref_id));

            if(mysqli_num_rows($result) == 1)
            {
                $routedata = mysqli_fetch_array($result);

                return $routedata['name'];
            }
        }

        return false;
    }
    else if($language == "en")
    {
        $sql = "SELECT `text` FROM `texts` WHERE `ref_type` = ? AND `ref_id` = ? AND `language` = ?";
        $result = sql_query($sql, "sis", array($ref_type, $ref_id, $language));
        if(mysqli_num_rows($result) == 0)
        {
            $text = get_text($ref_type, $ref_id, "th");
        }
        else
        {
            $textdata = mysqli_fetch_array($result);
            $text = $textdata['text'];

            if(trim($text) == "")
            {
                $text = get_text($ref_type, $ref_id, "th");
            }
        }

        return $text;
    }
    else
    {
        $sql = "SELECT `text` FROM `texts` WHERE `ref_type` = ? AND `ref_id` = ? AND `language` = ?";
        $result = sql_query($sql, "sis", array($ref_type, $ref_id, $language));
        if(mysqli_num_rows($result) == 0)
        {
            $text = get_text($ref_type, $ref_id, "en");
        }
        else
        {
            $textdata = mysqli_fetch_array($result);
            $text = $textdata['text'];

            if(trim($text) == "")
            {
                $text = get_text($ref_type, $ref_id, "en");
            }
        }

        return $text;
    }
}

/**
 * Human-readable distance
 * @param $distance
 * @return string
 */
function show_distance($distance)
{
    if($distance < 1000)
    {
        return round($distance) . " ม.";
    }
    else
    {
        $distance = round($distance / 1000, 2);
        return $distance . " กม.";
    }
}

/**
 * Search bus stop or place by given keyword
 * @param $keyword
 * @param $language
 * @param int $max_result
 * @return array
 */
function search($keyword, $language, $max_result = 10)
{
    $function_result = array();

    $keyword = strtolower(str_replace("'", "", $keyword));
    $language = urlencode($language);

    foreach(find_where_name_like($keyword . "%", $language) as $result)
    {
        $result['name'] = get_text("stop", $result['id'], $language);

        if(result_push($function_result, $result) >= $max_result)
        {
            return $function_result;
        }
    }

    foreach(find_where_tag_like($keyword . "%", $language) as $result)
    {
        $result['name'] = get_text("stop", $result['id'], $language);

        if(result_push($function_result, $result) >= $max_result)
        {
            return $function_result;
        }
    }

    foreach(find_where_name_like("%" . $keyword . "%", $language) as $result)
    {
        $result['name'] = get_text("stop", $result['id'], $language);

        if(result_push($function_result, $result) >= $max_result)
        {
            return $function_result;
        }
    }

    foreach(find_where_tag_like("%" . $keyword . "%", $language) as $result)
    {
        $result['name'] = get_text("stop", $result['id'], $language);

        if(result_push($function_result, $result) >= $max_result)
        {
            return $function_result;
        }
    }

    if($language != "en")
    {
        foreach(find_where_name_like($keyword . "%", "en") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }

        foreach(find_where_tag_like($keyword . "%", "en") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }

        foreach(find_where_name_like("%" . $keyword . "%", "en") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }

        foreach(find_where_tag_like("%" . $keyword . "%", "en") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }
    }

    if($language != "th")
    {
        foreach(find_where_name_like($keyword . "%", "th") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }

        foreach(find_where_tag_like($keyword . "%", "th") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }

        foreach(find_where_name_like("%" . $keyword . "%", "th") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }

        foreach(find_where_tag_like("%" . $keyword . "%", "th") as $result)
        {
            $result['name'] = get_text("stop", $result['id'], $language);

            if(result_push($function_result, $result) >= $max_result)
            {
                return $function_result;
            }
        }
    }

    return $function_result;
}

/**
 * Select bus stop or place from database where name is like given keyword
 * @param $keyword
 * @param $language
 * @return array
 */
function find_where_name_like($keyword, $language)
{
    $function_result = array();

    if($language == "th")
    {
        $sql = "SELECT `id`, `name`, `busstop` FROM `stops` WHERE `name` LIKE ?";
        $results = sql_query($sql, "s", array($keyword));
        while($stopdata = mysqli_fetch_array($results))
        {
            result_push($function_result, array(
                "id" => $stopdata['id'],
                "cause" => $stopdata['name'],
                "busstop" => $stopdata['busstop']
            ));
        }
    }
    else
    {
        $sql = "SELECT `ref_id`, `text`, `busstop` FROM `texts`, `stops` WHERE `ref_type` = 'stop' AND `ref_id` = `stops`.`id` AND `language` = ? AND `text` LIKE ?";
        $results = sql_query($sql, "ss", array($language, $keyword));
        while($stopdata = mysqli_fetch_array($results))
        {
            result_push($function_result, array(
                "id" => $stopdata['ref_id'],
                "cause" => $stopdata['text'],
                "busstop" => $stopdata['busstop']
            ));
        }
    }

    return $function_result;
}

/**
 * Select bus stop or place from database where name is like given tag
 * @param $keyword
 * @param $language
 * @return array
 */
function find_where_tag_like($keyword, $language)
{
    $function_result = array();

    if($language == "th")
    {
        $sql = "SELECT `stop`, `name` FROM `stops_tags` WHERE `name` LIKE ?";
        $results = sql_query($sql, "s", array($keyword));
        while($tagData = mysqli_fetch_array($results))
        {
            $sql = "SELECT `id`, `name`, `busstop` FROM `stops` WHERE `id` = ?";
            $result = sql_query($sql, "i", array($tagData['stop']));
            $stopData = mysqli_fetch_array($result);

            result_push($function_result, array(
                "id" => $stopData['id'],
                "cause" => $tagData['name'],
                "busstop" => $stopData['busstop']
            ));
        }
    }
    else
    {
        $sql = "SELECT `ref_id`, `text` FROM `texts` WHERE `ref_type` = 'stop_tag' AND `language` = ? AND `text` LIKE ?";
        $results = sql_query($sql, "ss", array($language, $keyword));
        while($tagData = mysqli_fetch_array($results))
        {
            $sql = "SELECT `name`, `busstop` FROM `stops` WHERE `id` = ?";
            $result = sql_query($sql, "i", array($tagData['ref_id']));
            $stopData = mysqli_fetch_array($result);

            result_push($function_result, array(
                "id" => $tagData['ref_id'],
                "cause" => $tagData['text'],
                "busstop" => $stopData['busstop']
            ));
        }
    }

    return $function_result;
}

/**
 * Check if given place id already exists in given array (of result)
 * @param $result
 * @param $stopid
 * @return bool
 */
function result_find($result, $stopid)
{
    foreach($result as $r)
    {
        if($r['id'] == $stopid)
        {
            return true;
        }
    }

    return false;
}

/**
 * Push an element (of place or bus stop) to result array if it is not pushed yet
 * @param $result
 * @param $newelement
 * @return int
 */
function result_push(&$result, $newelement)
{
    if(result_find($result, $newelement['id']) == false)
    {
        return array_push($result, $newelement);
    }

    return count($result);
}

/**
 * Estimate today timetable
 */
function estimate()
{
    $today = new Day(mktime(0, 0, 0));
    estimate_on($today);
}

/**
 * Estimate timetable on specified day
 * @param Day $day
 */
function estimate_on($day)
{
    global $connection;

    ## VARIABLES ##

    $starttime = 23400; // 06:30
    $endtime = 81000; // 22:30
    $interval = 3600; // 30 minutes

    $calculated_day_amount = 15;

    ###############

    /**
     * @var Day $today
     */
    $today = $day;
    $max_today = $today->Timestamp + 86399;

    $today_periods = array();
    for($i = $today->Timestamp + $starttime; $i <= $today->Timestamp + $endtime; $i += $interval)
    {
        array_push($today_periods, array(
            "lower" => $i,
            "upper" => $i + $interval - 1
        ));
    }

    $sql = "DELETE FROM `time_estimation` WHERE `start_time` BETWEEN {$today->Timestamp} AND $max_today OR `end_time` BETWEEN {$today->Timestamp} AND $max_today";
    mysqli_query($connection, $sql);

    $route_ids = array();

    $i = 0;
    foreach(get_routes_at($today->Timestamp) as $route)
    {
        if($route['available'] == 1)
        {
            $route_ids[$i] = $route['id'];
        }

        $i++;
    }

    $sql = "SELECT `route` FROM `route_available_switchers` WHERE `set_available_to` = 1 AND `date` BETWEEN {$today->Timestamp} AND $max_today";
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
    $sql = "SELECT `date` FROM `days` WHERE `type` = {$today->Type} AND `date` < {$today->Timestamp} ORDER BY `date` DESC LIMIT $calculated_day_amount";
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
                        $waittime = calculate_wait_time_at($stop, $route, round(($start_time + $end_time) / 2));
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

    echo "Time Estimation (" . date("Y-m-d", $day->Timestamp) . ") - OK\n";
}

/**
 * Calculate bus waiting time at specified bus stop, route and timestamp
 * @param $stop
 * @param $route
 * @param bool $datetime
 * @return float|null
 */
function calculate_wait_time_at($stop, $route, $datetime = false)
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

    /**
     * @var Day $day
     */
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

/**
 * Return the period of time (lower, upper) where contains given timestamp
 * @param $periods
 * @param $time
 * @return bool
 */
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

/**
 * Generate query text to search period of time (lower, upper) in specified field name
 * @param $array
 * @param $field_name
 * @return string
 */
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
?>