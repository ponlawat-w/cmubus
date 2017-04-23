<?php

if(!isset($_SESSION['authentication']))
{
    header("location: ../admin");
}

/**
 * @param Day $startDay
 * @param Day $endDay
 * @param array $dayTypes
 * @param array $routes
 * @return int[]
 */
function getSessionsBetween($startDay, $endDay, $dayTypes = array(), $routes = array())
{
    $daysQueryStrArr = array();

    for($i = $startDay->GetTimestamp(); $i <= $endDay->GetFinal(); $i += 86400)
    {
        $dayObj = new Day($i);
        if (!in_array($dayObj->GetType(), $dayTypes)) {
            continue;
        }

        array_push($daysQueryStrArr, "(`start_datetime` BETWEEN " . $dayObj->GetTimestamp() . " AND " . $dayObj->GetFinal() . ")");
    }

    if(count($daysQueryStrArr) == 0)
    {
        return array();
    }

    $daysQueryStr = "(" . implode(" OR ", $daysQueryStrArr) . ")";

    $routeQueryStr = "`route` IN (" . implode(", ", $routes) . ")";

    $functionResult = array();

    $sql = "SELECT `id`, `start_datetime`, `route`, `busno` FROM `sessions` WHERE " . $daysQueryStr . " AND " . $routeQueryStr;
    $result = sql_query($sql);
    while($sessionData = $result->fetch_array())
    {
        array_push($functionResult, $sessionData);
    }

    return $functionResult;
}

/**
 * @param array $sessionData
 * @return array
 */
function calculateSessionAccuracy($sessionData)
{
    $functionResult = array();

    $sql = "SELECT `stop`, `name` FROM `route_paths`, `stops` WHERE `stop` = `stops`.`id` AND `route` = ? AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
    $results = sql_query($sql, "i", array($sessionData['route']));
    while($stopData = $results->fetch_array())
    {
        array_push($functionResult, array(
            "stopID" => $stopData['stop'],
            "stopName" => $stopData['name'],
            "estimated" => 0,
            "absoluteEstimatedTime" => null,
            "relativeEstimatedTime" => null,
            "recordedArrivalTime" => null,
            "adjustedValue" => null,
            "absoluteDelay" => null,
            "relativeDelay" => null
        ));
    }

    $recordedData = array();
    $sql = "SELECT `stop`, `datetime` FROM `records` WHERE `session` = ? ORDER BY `datetime` ASC";
    $results = sql_query($sql, "i", array($sessionData['id']));
    while($data = $results->fetch_array())
    {
        array_push($recordedData, $data);
    }

    if(count($functionResult) != count($recordedData))
    {
        return array();
    }

    foreach ($functionResult as $key => $value)
    {
        $functionResult[$key]['recordedArrivalTime'] = $recordedData[$key]['datetime'];
    }

    $estimatedData = array();
    $sql = "SELECT `stop`, `estimated_time` FROM `time_estimation` WHERE `route` = ? AND (? BETWEEN `start_time` AND `end_time`) ORDER BY `estimated_time` ASC";
    $results = sql_query($sql, "ii", array($sessionData['route'], $sessionData['start_datetime']));
    while($data = $results->fetch_array())
    {
        array_push($estimatedData, $data);
    }

    if(count($functionResult) - 1 != count($estimatedData))
    {
        return array();
    }

    foreach ($estimatedData as $key => $value)
    {
        $functionResult[$key + 1]['estimated'] = $estimatedData[$key]['estimated_time'];
        $functionResult[$key + 1]['absoluteEstimatedTime'] = $sessionData['start_datetime'] + $estimatedData[$key]['estimated_time'];
        $functionResult[$key + 1]['relativeEstimatedTime'] = $functionResult[$key]['recordedArrivalTime'] + ($functionResult[$key + 1]['estimated'] -  $functionResult[$key]['estimated']);

        $functionResult[$key + 1]['adjustedValue'] = $functionResult[$key + 1]['relativeEstimatedTime'] - $functionResult[$key + 1]['absoluteEstimatedTime'];

        $functionResult[$key + 1]['relativeDelay'] = $functionResult[$key + 1]['recordedArrivalTime'] - $functionResult[$key + 1]['relativeEstimatedTime'];
        $functionResult[$key + 1]['absoluteDelay'] = $functionResult[$key + 1]['recordedArrivalTime'] - $functionResult[$key + 1]['absoluteEstimatedTime'];
    }

    return $functionResult;
}