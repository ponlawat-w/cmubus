<?php
	######################
	### CLASS Location ###
	######################

class Location
{
	public $lat;
	public $lon;
	
	public function __construct($latitude, $longitude)
	{
		$this->lat = $latitude;
		$this->lon = $longitude;
	}
	
	public function DistanceTo(Location $location)
	{
		$lat2 = $location->lat;
		$lon2 = $location->lon;
		return 6371000 * 2 * asin(sqrt(pow(sin(($this->lat - abs($lat2)) * pi() / 180 / 2), 2) + cos($this->lat * pi() / 180) * pow(sin(($this->lon - $lon2) * pi() / 180 / 2), 2)));
	}
	
	// SOURCE CODE from: https://www.sitepoint.com/community/t/distance-between-long-lat-point-and-line-segment/50583/2s
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
	
	public static function DegreeDiff($deg1, $deg2)
	{
		$deg1 %= 360;
		$deg2 %= 360;
		
		return abs($deg1 - $deg2) % 360;
	}
}

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
?>