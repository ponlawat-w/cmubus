<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<?php
			include_once("../mysql_connection.inc.php");
			include_once("library.inc.php");
			include_once("../lib/location.inc.php");
		?>
		<style>
			body
			{
				background-color: #000000;
				color: #ffffff;
				font-family: Leelawadee, Tahoma, Helvetica;
			}
			a
			{
				color: #ffffff;
				text-decoration: none;
			}
				a:hover
				{
					text-decoration: underline;
				}
			.center
			{
				text-align: center;
			}
		</style>
	</head>
	<body>
		<h4>ALL ROUTES</h4>
		<?php
			$sql = "SELECT `id` FROM `routes` ORDER BY `id` ASC";
			$results = mysqli_query($connection, $sql);
			while($data = mysqli_fetch_array($results))
			{
				echo "　<a href='route.php?id={$data['id']}'>ROUTE#{$data['id']}</a><br>";
			}
		?>
		<hr>
		<h4>ALL STOPS</h4>
		<?php
			$sql = "SELECT `id`, `name` FROM `stops` WHERE `busstop` = 1 ORDER BY `name` ASC";
			$results = mysqli_query($connection, $sql);
			while($data = mysqli_fetch_array($results))
			{
				echo "　<a href='stop.php?id={$data['id']}'>{$data['name']}</a><br>";
			}
		?>
		<hr>
		<h4>ALL BUSES</h4>
		<?php
			$sql = "SELECT `id` FROM `buses` ORDER BY `id` ASC";
			$results = mysqli_query($connection, $sql);
			while($data = mysqli_fetch_array($results))
			{
				echo "　<a href='bus.php?id={$data['id']}'>BUSNO#{$data['id']}</a><br>";
			}
		?>
	</body>
</html>
<?php mysqli_close($connection); ?>
