<?php session_start(); session_write_close();
include_once('../mysql_connection.inc.php');
include_once('../lib/app.inc.php');
include_once('library.inc.php');

check_authentication();

$gmapAPIKey = 'AIzaSyDI_WPljKeqHrqZRIEfEMHUTa7C-bWbl-E';

//$sql = 'DELETE * FROM `connections`';
//sql_query($sql);
//
//$sql = 'TRUNCATE TABLE `connections`';
//sql_query($sql);

$i = 1;

$sql = 'SELECT * FROM `stops`';
$results = sql_query($sql);
while($stopdata = $results->fetch_array())
{
    $stopCond = '';
    if(!$stopdata['busstop'])
    {
        $stopCond = 'AND `busstop` = 1';
    }

    echo "<div><strong>{$stopdata['name']}</strong></div>";
    $sql = 'SELECT *,
			6371000 * 2 *
			ASIN(
				SQRT(
					POWER(
						SIN((? - abs(`location_lat`)) * PI() / 180 / 2)
					, 2) +
					COS(? * PI() / 180) *
					POWER(
						SIN((? - `location_lon`) * PI() / 180 / 2)
					, 2)
				)
			) AS `distance`
		FROM `stops` WHERE `id` != ? ' . $stopCond . ' ORDER BY `distance` ASC LIMIT 5';
    $results2 = sql_query($sql, 'dddi', array($stopdata['location_lat'], $stopdata['location_lat'], $stopdata['location_lon'], $stopdata['id']));
    while($stop2data = $results2->fetch_array())
    {
        $sql = "SELECT COUNT(*) AS 'count' FROM `connections` WHERE
                  (`stop1` = ? AND `stop2` = ?)
                  OR (`stop1` = ? AND `stop2` = ?)";
        if(!sql_query($sql, 'iiii', array($stopdata['id'], $stop2data['id'], $stop2data['id'], $stopdata['id']))->fetch_array()['count'])
        {
            echo "{$i}: {$stop2data['name']}";

            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$stopdata['location_lat']},{$stopdata['location_lon']}&destinations={$stop2data['location_lat']},{$stop2data['location_lon']}&mode=walking&language=th&units=metric&key={$gmapAPIKey}";
            $gmapResult = json_decode(file_get_contents($url), true);
//            var_dump($gmapResult); exit;
            if($gmapResult['status'] == 'OK')
            {
                $duration = $gmapResult['rows'][0]['elements'][0]['duration']['value'];
                echo ", duration: {$duration} - ";

                $sql = "INSERT INTO `connections` (`id`, `stop1`, `stop2`, `connection_time`) VALUES (0, ?, ?, ?)";
                sql_query($sql, 'iii', array($stopdata['id'], $stop2data['id'], $duration));
            }

            echo "<br>";

            $i++;
        }
    }
    echo "<hr>";
}

mysqli_close($connection);