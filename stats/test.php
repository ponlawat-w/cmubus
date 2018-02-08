<?php
include_once("../lib/lib.inc.php");
include_once("../lib/app.inc.php");
include_once("library.php");

$sessions = getSessionsBetween(new Day(mktime(0, 0, 0, 4, 3, 2017)), new Day(mktime(0, 0, 0, 4, 3, 2017)), array(0), array(1));

foreach ($sessions as $session)
{
    var_dump(calculateSessionAccuracy($session));
    break;
}