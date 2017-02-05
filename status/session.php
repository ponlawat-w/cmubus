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
				font-size: 1.2em;
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
		<script src="jquery-3.1.0.min.js"></script>
	</head>
	<body>
		<a href="explorer.php">≪ กลับ</a>
		<?php
			$id = $_GET['id'];
			$sql = "SELECT `route`, `busno`, `start_datetime` FROM `sessions` WHERE `id` = $id";
			$result = mysqli_query($connection, $sql);
			$sessiondata = mysqli_fetch_array($result);
			echo "<h3><a href='route.php?id={$sessiondata['route']}'>ROUTE#{$sessiondata['route']}</a> <a href='bus.php?id={$sessiondata['busno']}'>BUSNO#{$sessiondata['busno']}</a><br>" . date("Y-m-d", $sessiondata['start_datetime']) . "</h3>";

			$sql = "SELECT `session`, `last_distance`, `last_update` FROM `buses` WHERE `id` = {$sessiondata['busno']}";
			$result = mysqli_query($connection, $sql);
			$busdata = mysqli_fetch_array($result);
			if($busdata['session'] == $id)
			{
				echo "<p>● ONLINE<sub> " . date("Y-m-d H:i:s", $busdata['last_update']) . "</sub></p>";
				echo '
					<script>
					$(document).ready(function()
					{
						setTimeout(function()
						{
							window.location.reload(1);
						}, 7000);
					});
					</script>
				';
			}
			else
			{
				echo "<p>✔ FINISHED</p>";
			}
			
			$last_recorded = array(
				"stop" => null,
				"datetime" => $sessiondata['start_datetime']
			);
			
			$recorded_rows = 0;
			
			$sql = "SELECT `stop`, `datetime`, `name` FROM `records`, `stops` WHERE `records`.`session` = $id AND `records`.`stop` = `stops`.`id` ORDER BY `datetime` ASC";
			$results = mysqli_query($connection, $sql);
			while($data = mysqli_fetch_array($results))
			{
				echo date("H:i:s", $data['datetime']) . " - <a href='stop.php?id={$data['stop']}'>{$data['name']}</a><br>";
				
				$last_recorded = array(
					"stop" => $data['stop'],
					"datetime" => $data['datetime']
				);
				
				$recorded_rows++;
			}			
			
			if($last_recorded['stop'] != null)
			{
				$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`) AND `route` = {$sessiondata['route']} AND `stop` = {$last_recorded['stop']}";
				$result = mysqli_query($connection, $sql);
				$data = mysqli_fetch_array($result);
				if(mysqli_num_rows($result) > 0)
				{
					$last_recorded['estimated'] = $data['estimated_time'];
				}
			}
			
			
			if($busdata['session'] == $id)
			{
				$i = 0;
				$ld = 0;
				$sql = "SELECT `stop`, `name`, `distance_from_start` FROM `route_paths`, `stops` WHERE `route_paths`.`stop` = `stops`.`id` AND `route` = {$sessiondata['route']} AND `distance_from_start` >= {$busdata['last_distance']} AND `stop` IS NOT NULL ORDER BY `distance_from_start` ASC";
				$results = mysqli_query($connection, $sql);
				while($data = mysqli_fetch_array($results))
				{
					if($last_recorded['stop'] != null && isset($last_recorded['estimated']) && $recorded_rows > 1)
					{
						$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`) AND `route` = {$sessiondata['route']} AND `stop` = {$data['stop']}";
						$result = mysqli_query($connection, $sql);
						if(mysqli_num_rows($result) > 0)
						{
							$estimateddata = mysqli_fetch_array($result);
							$estimatedtime = $estimateddata['estimated_time'];
							
							$estimated_timestamp = $last_recorded['datetime'] + ($estimatedtime - $last_recorded['estimated']);
							
							echo "<span style='color:#aaaaaa'>" . date("H:i:s", $estimated_timestamp) . "</span> - ";
						}
						else
						{					
							echo "　　×　　";
						}
					}
					else
					{
						$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`) AND `route` = {$sessiondata['route']} AND `stop` = {$data['stop']}";
						$result = mysqli_query($connection, $sql);
						if(mysqli_num_rows($result) > 0)
						{
							$estimateddata = mysqli_fetch_array($result);
							$estimatedtime = $estimateddata['estimated_time'];
							
							$estimated_timestamp = $sessiondata['start_datetime'] + $estimatedtime;
							
							echo "<span style='color:#aaaaaa'>" . date("H:i:s", $estimated_timestamp) . "</span> - ";
						}
						else
						{					
							echo "　　×　　";
						}
					}
					
					echo "<a href='stop.php?id={$data['stop']}'>{$data['name']}</a>";
					
					$left = $data['distance_from_start'] - $busdata['last_distance'];
					echo " <span style='font-size:0.6em;'>(อีก " . show_distance($left) . ")</span>";
					
					echo "<br>";
					$i++;
					
					$ld = $data['distance_from_start'];
				}
				
				$percent = round($busdata['last_distance'] / $ld * 100);
				echo "<p>{$percent}%　" . show_distance($busdata['last_distance']) . " จาก " . show_distance($ld) . "　(อีก " . show_distance($ld - $busdata['last_distance']) . ")</p>";
			}
		?>
	</body>
</html>
<?php mysqli_close($connection); ?>
