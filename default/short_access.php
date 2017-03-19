<?php

include_once("../mysql_connection.inc.php");
include_once("../lib/app.inc.php");

if(isset($_GET['stopid']))
{
    $sql = "INSERT INTO `short_access` (`id`, `stop_id`, `timestamp`) VALUES (0, ?, ?)";
    sql_query($sql, "ii", array($_GET['stopid'], mktime()));

    header("location: https://app.cmubus.com/stop/{$_GET['stopid']}");
}