<?php session_start(); ob_start();

include_once("../../mysql_connection.inc.php");
include_once("../library.inc.php");
include_once("../../lib/location.inc.php");

check_authentication();

$route_id = $_GET['route_id'];

$sql = "DELETE FROM `route_paths` WHERE `route` = $route_id";
mysqli_query($connection, $sql);

$post_data = json_decode(file_get_contents('php://input'));

$name = $post_data->name;
$color = $post_data->color;
$detail = $post_data->detail;
$data = $post_data->data;
$refid = $post_data->refid;

$sql = "UPDATE `routes` SET `name` = '$name', `color` = '$color', `detail` = '$detail', `refid` = '$refid' WHERE `id` = $route_id";
mysqli_query($connection, $sql);

$previous_location = null;

$distance_from_start = 0;

for($i = 0; $i < count($data); $i++)
{
	$sequence = $i + 1;
	
	$this_location = new Location($data[$i]->location_lat, $data[$i]->location_lon);
	
	if($data[$i]->stop == NULL)
	{
		$location_lat = $data[$i]->location_lat;
		$location_lon = $data[$i]->location_lon;
		$stop = "NULL";
	}
	else
	{
		$location_lat = "NULL";
		$location_lon = $location_lat;
		$stop = $data[$i]->stop;
	}
	
	if($i > 0)
	{
		$distance_from_start += $previous_location->DistanceTo($this_location);
	}
	
	$previous_location = $this_location;
	
	$sql = "INSERT INTO `route_paths` (`id`, `route`, `sequence`, `distance_from_start`, `location_lat`, `location_lon`, `stop`) VALUES (0, $route_id, $sequence, $distance_from_start, $location_lat, $location_lon, $stop)";
	mysqli_query($connection, $sql);	
}

echo json_encode(array(
	"result" => true
));

mysqli_close($connection); ob_end_flush(); ?>