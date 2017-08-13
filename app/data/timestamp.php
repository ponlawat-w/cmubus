<?php
	$now = mktime();
	echo json_encode(array("timestamp" => $now, "readable" => date("H:i", $now)));