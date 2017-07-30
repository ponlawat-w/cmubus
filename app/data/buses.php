<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$page_result = array();

$sql = "SELECT `id`, `route`, `session`, `last_distance` FROM `buses` ORDER BY `id` ASC";
$results = sql_query($sql);

$buses = array();

while($busData = mysqli_fetch_array($results))
{
    $lastStopID = 0;
    $lastStop = "";

    if($busData['session'] > 0)
    {
        $sql = "SELECT `stop` FROM `route_paths` WHERE `route` = ? AND `stop` IS NOT NULL AND `distance_from_start` < ? ORDER BY `distance_from_start` DESC LIMIT 1";
        $result = sql_query($sql, "id", array($busData['route'], $busData['last_distance']));
        $stopData = mysqli_fetch_array($result);

        $lastStopID = $stopData['stop'];
        $lastStop = get_text("stop", $lastStopID, get_language_id());
    }

    $route = new Route($busData['route']);

    array_push($buses, array(
            "busno" => $busData['id'],
            "session" => $busData['session'],
            "route" => $busData['route'],
            "route_name" => get_text("route", $busData['route'], get_language_id()),
            "route_color" => $route->Color,
            "stop" => $lastStopID,
            "stopname" => $lastStop
        )
    );
}

$page_result = array(
    "buses" => $buses,
    "current_time" => date("H:i")
);

echo json_encode($page_result);