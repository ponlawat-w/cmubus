<?php ob_start(); session_start();
include_once("../../mysql_connection.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

$page_result = array();

$recommendedStops = array(119, 33, 56, 117, 107, 129, 133);

foreach($recommendedStops as $recommendedStop)
{
    $recommendedStopName = get_text("stop", $recommendedStop, get_language_id());

    array_push($page_result, array(
            "id" => $recommendedStop,
            "name" => $recommendedStopName
        )
    );
}

$page_result = sort_by($page_result, "name", SORT_ASC);

echo json_encode($page_result);

ob_end_flush();