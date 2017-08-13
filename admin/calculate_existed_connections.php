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

$sql = 'SELECT * FROM `connections`';
$results = sql_query($sql);
while($connectionData = $results->fetch_array())
{
    $stop1 = new Stop($connectionData['stop1']);
    $stop1->GetInfo();
    $stop2 = new Stop($connectionData['stop2']);
    $stop2->GetInfo();

    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$stop1->Location->lat},{$stop1->Location->lon}&destinations={$stop2->Location->lat},{$stop2->Location->lon}&mode=walking&language=th&units=metric&key={$gmapAPIKey}";
    $gmapResult = json_decode(file_get_contents($url), true);
    if ($gmapResult['status'] == 'OK') {
        $duration = $gmapResult['rows'][0]['elements'][0]['duration']['value'];

        if(!$duration || $duration == '0')
        {
            continue;
        }

        echo "{$stop1->Name} -> {$stop2->Name}, duration: {$duration}<br>";

        $sql = "UPDATE `connections` SET `connection_time` = ? WHERE `id` = ?";
        sql_query($sql, 'ii', array($duration, $connectionData['id']));
    }
}

mysqli_close($connection);