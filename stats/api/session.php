<?php session_start(); session_write_close();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
include_once("../library.php");

$session = $_GET['id'];

$sql = "SELECT * FROM `sessions` WHERE `id` = ?";
$sessionData = sql_query($sql, "i", array($session))->fetch_array();

$day = get_day_from_timestamp($sessionData['start_datetime']);

$calculatedData = calculateSessionAccuracy($sessionData);

foreach ($calculatedData as $key => $value)
{
    if($value['absoluteDelay'] != null)
    {
        $value['absoluteDelay'] = abs($value['absoluteDelay']);
    }
    if($value['relativeDelay'] != null)
    {
        $value['relativeDelay'] = abs($value['relativeDelay']);
    }

    $sql = "INSERT INTO `stats_estimation_accuracy` (`id`, `session`, `stop`, `date`, `day_type`, `busno`, `route`, `absolute_error`, `relative_error`) VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?)";
    sql_query($sql, "iiiiiidd", array($session, $value['stopID'], $day->GetTimestamp(), $day->GetType(), $sessionData['busno'], $sessionData['route'], $value['absoluteDelay'], $value['relativeDelay']));
}

echo json_encode(array("result" => "OK"));