<?php

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
		global $connection;
		
		if(date("His", $day) != "000000")
		{
			unset($this);
			return false;
		}
		
		$sql = "SELECT `type`, `detail` FROM `days` WHERE `date` = $day";
		$result = mysqli_query($connection, $sql);
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
			
			$sql = "INSERT INTO `days` (`date`, `type`, `detail`) VALUES ($day, {$this->Type}, '')";
			mysqli_query($connection, $sql);
			
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
		
		$sql = "SELECT `name` FROM `day_types` WHERE `id` = {$this->Type}";
		$result = mysqli_query($connection, $sql);
		$data = mysqli_fetch_array($result);
		
		return $data['name'];
	}

    /**
     * Set day type
     * @param $newType
     */
	public function SetTypeTo($newType)
	{
		global $connection;
		
		$sql = "UPDATE `days` SET `type` = $newType WHERE `date` = {$this->Timestamp}";
		mysqli_query($connection, $sql);
		
		$this->Type = $newType;
	}

    /**
     * Set day detail
     * @param $newDetail
     */
	public function SetDetail($newDetail)
	{
		global $connection;
		
		$sql = "UPDATE `days` SET `detail` = '$newDetail' WHERE `date` = {$this->Timestamp}";
		mysqli_query($connection, $sql);
		
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
	global $connection;
	
	$routes = array();
	$now = mktime();
	
	if($now > $time)
	{
		$time = $now;
	}
	
	$i = 0;
	$sql = "SELECT `id`, `refid`, `color`, `name`, `available`, `detail` FROM `routes` ORDER BY `name` ASC, `detail` ASC";
	$results = mysqli_query($connection, $sql);
	while($routeData = mysqli_fetch_array($results))
	{
		$available = $routeData['available'];

		if($time != $now)
        {
            $sql = "SELECT `set_available_to` FROM `route_available_switchers` WHERE `route` = {$routeData['id']} AND `date` BETWEEN $now AND $time ORDER BY `date` DESC LIMIT 1";
            $result = mysqli_query($connection, $sql);
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
?>