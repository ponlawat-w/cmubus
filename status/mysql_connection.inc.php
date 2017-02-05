<?php
	$mysql_host = "localhost";
	$mysql_user = "root";
	$mysql_password = "Ec34Ft5h4mKBS7KG_";
	$mysql_dbname = "cmubus";
	
	$connection = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_dbname);
	mysqli_query($connection, "SET CHARSET UTF8");
?>