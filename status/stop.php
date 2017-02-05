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
		<script>
			$(document).ready(function()
			{
				setTimeout(function()
				{
					window.location.reload(1);
				}, 5000);
			});
		</script>
	</head>
	<body>
		<a href="explorer.php">≪ กลับ</a>
		<?php
			$id = $_GET['id'];
			
			$sql = "SELECT `name` FROM `stops` WHERE `id` = $id";
			$result = mysqli_query($connection, $sql);
			$data = mysqli_fetch_array($result);
			
			echo "<h2>{$data['name']}</h2><div style='font-size:0.6em'>" . date("Y-m-d H:i:s") . "</div>";
			
			$sql = "SELECT `route`, `distance_from_start` FROM `route_paths` WHERE `stop` = $id AND `distance_from_start` > 0 ORDER BY `route` ASC, `distance_from_start` ASC";
			$results = mysqli_query($connection, $sql);
			while($stoproutedata = mysqli_fetch_array($results))
			{
				echo "<h4><a href='route.php?id={$stoproutedata['route']}'>ROUTE#{$stoproutedata['route']}</a></h4>";
				
				$sql = "SELECT `id`, `last_distance`, `last_update`, `session` FROM `buses` WHERE `session` > 0 AND `route` = {$stoproutedata['route']} AND `last_distance` < {$stoproutedata['distance_from_start']} ORDER BY `last_distance` DESC";
				$results2 = mysqli_query($connection, $sql);
				while($busdata = mysqli_fetch_array($results2))
				{
					if(mktime() - $busdata['last_update'] > 60)
					{
						continue;
					}
					
					$left = $stoproutedata['distance_from_start'] - $busdata['last_distance'];
					
					$sql = "SELECT `id`, `start_datetime`, `route` FROM `sessions` WHERE `id` = {$busdata['session']}";
					$result = mysqli_query($connection, $sql);
					$sessiondata = mysqli_fetch_array($result);
					
					$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$sessiondata['route']} AND `stop` = $id AND ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`)";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) > 0)
					{
						$estimateddata = mysqli_fetch_array($result);
						
						$estimated = null;
						
						$sql = "SELECT `datetime`, `stop` FROM `records` WHERE `session` = {$sessiondata['id']} ORDER BY `datetime` DESC";
						$result = mysqli_query($connection, $sql);
						if(mysqli_num_rows($result) > 1)
						{
							$recorddata = mysqli_fetch_array($result);
							
							$sql = "SELECT `estimated_time` FROM `time_estimation` WHERE `route` = {$sessiondata['route']} AND `stop` = {$recorddata['stop']} AND ({$sessiondata['start_datetime']} BETWEEN `start_time` AND `end_time`)";
							$result = mysqli_query($connection, $sql);
							if(mysqli_num_rows($result) > 0)
							{
								$last_estimateddata = mysqli_fetch_array($result);
								
								$estimated = $recorddata['datetime'] + ($estimateddata['estimated_time'] - $last_estimateddata['estimated_time']);
							}
							else
							{
								$estimated = $sessiondata['start_datetime'] + $estimateddata['estimated_time'];
							}
						}
						else
						{
							$estimated = $sessiondata['start_datetime'] + $estimateddata['estimated_time'];
						}						
					}
					
					echo "　";
					if(isset($estimated) && $estimated != null)
					{
						echo date("H:i", $estimated) . " - ";
					}
					echo "<a href='session.php?id={$busdata['session']}'>BUS#{$busdata['id']} ";
					echo "อีก ";
					echo show_distance($left);
					if(isset($estimated) && $estimated != null)
					{
						$timeleft = $estimated - mktime();
						
						if($timeleft < -30)
						{
							echo " (ไม่ทราบ) ";
						}
						else if($timeleft < 30 || $left < 500)
						{
							echo " (กำลังจะถึง) ";
						}
						else
						{
							$timeleft /= 60;
							$timeleft = ceil($timeleft);
							
							echo " ($timeleft นาที) ";
						}
					}
					echo "</a><br>";
				}
			}
			
			echo "<hr>";
			
			echo "<h3>รถที่เพิ่งผ่าน</h3>";
			$sql = "SELECT `session`, `datetime` FROM `records` WHERE `stop` = $id ORDER BY `datetime` DESC LIMIT 5";
			$results = mysqli_query($connection, $sql);
			while($recorddata = mysqli_fetch_array($results))
			{
				$sql = "SELECT `route`, `busno` FROM `sessions` WHERE `id` = {$recorddata['session']}";
				$result = mysqli_query($connection, $sql);
				$sessiondata = mysqli_fetch_array($result);
				
				echo " <a href='session.php?id={$recorddata['session']}'>" . date("H:i", $recorddata['datetime']);
				echo " - ROUTE#{$sessiondata['route']}";
				echo " BUS#{$sessiondata['busno']}</a>";
				echo "<br>";
			}
		?>
	</body>
</html>
<?php mysqli_close($connection); ?>
