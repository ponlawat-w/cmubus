<?php
class Day
{
	private $timestamp;
	private $type;
	private $detail;
		
	public function __get($v)
	{
		return $this->$v;
	}
	
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
		$numrows = mysqli_num_rows($result);
		
		if($numrows == 0)
		{
			if(date("N", $day) > 5) // วันหยุด
			{
				$this->type = -1;
			}
			else // วันธรรมดา
			{
				$this->type = 0;
			}
			
			$sql = "INSERT INTO `days` (`date`, `type`, `detail`) VALUES ($day, {$this->type}, '')";
			mysqli_query($connection, $sql);
			
			$this->timestamp = $day;
			$this->detail = "";
		}
		else
		{
			$daydata = mysqli_fetch_array($result);
			
			$this->timestamp = $day;
			$this->type = $daydata['type'];
			$this->detail = $daydata['detail'];
		}
	}
	
	public function TypeToString()
	{
		global $connection;
		
		$sql = "SELECT `name` FROM `day_types` WHERE `id` = {$this->type}";
		$result = mysqli_query($connection, $sql);
		$data = mysqli_fetch_array($result);
		
		return $data['name'];
	}
	
	public function SetTypeTo($newtype)
	{
		global $connection;
		
		$sql = "UPDATE `days` SET `type` = $newtype WHERE `date` = {$this->timestamp}";
		mysqli_query($connection, $sql);
		
		$this->type = $newtype;
	}
	
	public function SetDetail($newdetail)
	{
		global $connection;
		
		$sql = "UPDATE `days` SET `detail` = '$newdetail' WHERE `date` = {$this->timestamp}";
		mysqli_query($connection, $sql);
		
		$this->detail = $newdetail;
	}
	
	public function GetFinal()
	{
		return $this->timestamp + 86399;
	}
}

function changeday_savetime($timestamp, $day)
{
	$time = ((date("H", $timestamp) * 3600) + (date("i", $timestamp) * 60) + date("s", $timestamp));
	return $day->timestamp + $time;
}

function get_day_from_timestamp($timestamp)
{
	return new Day($timestamp - (date("H", $timestamp)*3600 + date("i", $timestamp)*60 + date("s", $timestamp)));
}

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
	while($routedata = mysqli_fetch_array($results))
	{
		$available = $routedata['available'];
		
		$sql = "SELECT `set_available_to` FROM `route_available_switchers` WHERE `route` = {$routedata['id']} AND `date` BETWEEN $now AND $time ORDER BY `date` DESC LIMIT 1";
		$result = mysqli_query($connection, $sql);
		$numrows = mysqli_num_rows($result);
		
		if($numrows == 1)
		{
			$data = mysqli_fetch_array($result);
			
			$available = $data['set_available_to'];
		}
		
		$routename = $routedata['name'];
		if(function_exists("get_text"))
		{
			$routename = get_text("route", $routedata['id'], get_language_id());
		}
		
		$routes[$i] = array(
			"id" => $routedata['id'],
			"refid" => $routedata['refid'],
			"color" => $routedata['color'],
			"name" => $routename,
			"detail" => $routedata['detail'],
			"available" => $available
		);
		
		$i++;
	}
	
	return $routes;
}
?>