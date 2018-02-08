<?php ob_start(); session_start();
include_once("../../lib/lib.inc.php");
include_once("../../lib/app.inc.php");
get_language_id();
session_write_close();

if(!isset($_GET['id']) || !isset($_GET['route']))
{
    http_response_code(401);
    exit;
}

$stopID = $_GET['id'];
$routeID = $_GET['route'];

$stop = new Stop($stopID);
$stop->GetInfo();
if(!$stop->Name)
{
    http_response_code(404);
    exit;
}

$route = new Route($routeID);
if(!$route->Name)
{
    http_response_code(404);
    exit;
}

$sql = "SELECT COUNT(*) AS 'count' FROM `route_paths` WHERE `route` = ? AND `stop` = ?";
$result = sql_query($sql, 'ii', array($routeID, $stopID));
if(!$result->fetch_array()['count'])
{
    http_response_code(500);
    exit;
}

$stopName = get_text('stop', $stopID, get_language_id());
$routeName = get_text('route', $routeID, get_language_id());

$last_weekday = get_latest_day_on_type(0);
$last_weekend = get_latest_day_on_type(-1);

$weekday_dailyTimetable = $stop->DailyTimeTable($last_weekend);
$weekend_dailyTimetable = $stop->DailyTimeTable($last_weekday);

function get_stats_from_day($stop, $route, $day)
{
    $data = array();

    $sql = 'SELECT * FROM `time_estimation`
        WHERE `route` = ?
            AND `stop` = ?
            AND (`start_time` BETWEEN ? AND ?)
        ORDER BY `start_time` ASC, `end_time` DESC';
    $results = sql_query($sql, 'iiii', array($route, $stop, $day, $day + 86399));
//    echo "$sql, $route, $stop, $day, " . ($day + 86399);
    while($estimatedData = $results->fetch_array())
    {
        $item = array(
            'start_time' => $estimatedData['start_time'],
            'start_time_readable' => date('H:i', $estimatedData['start_time']),
            'end_time' => $estimatedData['end_time'],
            'end_time_readable' => date('H:i', $estimatedData['end_time']),
            'waittime' => $estimatedData['waittime']
        );

        array_push($data, $item);
    }

    return $data;
}

$today = get_day_from_timestamp(time());

$result = array(
    'stop' => array(
        'id' => $stopID,
        'name' => $stopName
    ),
    'route' => array(
        'id' => $routeID,
        'name' => $routeName,
        'color' => '#' . $route->Color
    ),
    'waittime' => array(
        'weekday' => get_stats_from_day($stopID, $routeID, $last_weekday->Timestamp),
        'weekend' => get_stats_from_day($stopID, $routeID, $last_weekend->Timestamp)
    ),
    'stats' => array(
        'weekday' => array(),
        'weekend' => array()
    ),
    'today' => array(
        'timestamp' => $today->Timestamp,
        'type' => $today->GetType()
    ),
    'serverTime' => time()
);

foreach($weekday_dailyTimetable as $item)
{
    if($item['route'] == $routeID)
    {
        $result['stats']['weekday']['estimated_first'] = $item['estimated_first'];
        $result['stats']['weekday']['estimated_first_readable'] = $item['estimated_first_readable'];
        $result['stats']['weekday']['estimated_last'] = $item['estimated_last'];
        $result['stats']['weekday']['estimated_last_readable'] = $item['estimated_last_readable'];
        break;
    }
}

foreach($weekend_dailyTimetable as $item)
{
    if($item['route'] == $routeID)
    {
        $result['stats']['weekend']['estimated_first'] = $item['estimated_first'];
        $result['stats']['weekend']['estimated_first_readable'] = $item['estimated_first_readable'];
        $result['stats']['weekend']['estimated_last'] = $item['estimated_last'];
        $result['stats']['weekend']['estimated_last_readable'] = $item['estimated_last_readable'];
        break;
    }
}

echo json_encode($result);