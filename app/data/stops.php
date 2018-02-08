<?php ob_start(); session_start();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$page_result = array();

$sql = "SELECT `id` FROM `stops` WHERE `busstop` = 1";
$results = sql_query($sql);
while($stopData = mysqli_fetch_array($results))
{
    $stopObj = new Stop($stopData['id']);

    $passingRoutes = $stopObj->PassingRoutes();
    $cleansedPassingRoutes = array();

    foreach ($passingRoutes as $routeData) if($routeData['available'] == 1)
    {
        array_push($cleansedPassingRoutes, array(
            "id" => $routeData['id'],
            "name" => get_text("route", $routeData['id'], get_language_id()),
            "color" => $routeData['color']
        ));
    }

    $cleansedPassingRoutes = sort_by($cleansedPassingRoutes, "name", SORT_ASC);

    array_push($page_result, array(
        "id" => $stopData['id'],
        "name" => get_text("stop", $stopData['id'], get_language_id()),
        "passing_routes" => $cleansedPassingRoutes
    ));
}

$page_result = sort_by($page_result, "name", SORT_ASC);

echo json_encode($page_result);

mysqli_close($connection); ob_end_flush();