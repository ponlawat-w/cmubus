<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<?php
			include_once("../lib/lib.inc.php");
        include_once("../lib/cron.inc.php");
        include_once("../lib/app.inc.php");
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
		<script src="jquery-3.1.0.min.js"></script>
		<script>
			$(document).ready(function()
			{
				setTimeout(function()
				{
					window.location.reload(1);
				}, 7000);
			});
		</script>
	</head>
	<body>
		<?php			
			$now = mktime();
			echo "<h2>" . date("Y-m-d H:i:s", $now) . "</h2>";
			
			echo "<table border=1 cellspacing=0 cellpadding=0 style='font-size:0.9em;'><tr>
				<th style='width:4em;'>เส้นทาง</th>
				<th style='width:3.5em;'>เลขรถ</th>
				<th style='width:3em;'>สถานะ</th>
				<th colspan='2'>ติดตาม</th>
				<th colspan='3'>สถานที่</th>
				<th style='width:11em;'>ข้อมูล</th>
			</tr>";
			
			$sql = "SELECT `id`, `route`, `session`, `last_distance`, `rotation`, `last_update` FROM `buses` WHERE $now - `last_update` < 600 ORDER BY `route` ASC, `id` ASC";
			$results = mysqli_query($connection, $sql);
			while($busdata = mysqli_fetch_array($results))
			{
				echo "<tr>";
				
				$sql = "SELECT `name`, `color` FROM `routes` WHERE `id` = {$busdata['route']}";
				$result = mysqli_query($connection, $sql);
				$routedata = mysqli_fetch_array($result);
				echo "<td><span style='color:#{$routedata['color']};'>▪</span> <a href='route.php?id={$busdata['route']}' target='_blank'>{$routedata['name']}</a></td>";
				
				echo "<td class='center'><a href='bus.php?id={$busdata['id']}' target='_blank'>{$busdata['id']}</a></td>";
				
				echo "<td class='center'>";
				$utime = mktime() - $busdata['last_update'];
				if($utime < 60)
				{
					echo "○";
				}
				else if($utime < 300)
				{
					echo "△";
				}
				else
				{
					echo "×";
				}
				echo "</td>";				
				echo "<td class='center'>";					
					if($busdata['session'] > 0)
					{
						echo "○";
						$sql = "SELECT MAX(`distance_from_start`) AS `final_distance` FROM `route_paths` WHERE `route` = {$busdata['route']}";
						$result = mysqli_query($connection, $sql);
						$finaldata = mysqli_fetch_array($result);
						$finaldistance = $finaldata['final_distance'];
						$percent = round(($busdata['last_distance'] / $finaldistance) * 100);
						$left = $finaldistance - $busdata['last_distance'];
						echo "</td><td style='font-size:0.7em;'><a href='session.php?id={$busdata['session']}' target='_blank'>{$percent}% (อีก " . show_distance($left) . ")</a>";
					}
					else
					{
						echo "×</td><td>";
					}
				echo "</td>";
				
				if($busdata['session'] > 0)
				{
					$name1 = "";
					$name2 = "";
					
					$sql = "SELECT `stop`, `sequence`, `distance_from_start`, `name` FROM `route_paths`, `stops` WHERE `distance_from_start` < {$busdata['last_distance']} AND `route` = {$busdata['route']} AND `stop` IS NOT NULL AND `stop` = `stops`.`id` ORDER BY `distance_from_start` DESC LIMIT 1";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) == 1)
					{
						$stopdata1 = mysqli_fetch_array($result);
						$name1 = $stopdata1['name'];
					}
					$sql = "SELECT `stop`, `sequence`, `distance_from_start`, `name` FROM `route_paths`, `stops` WHERE `distance_from_start` > {$busdata['last_distance']} AND `route` = {$busdata['route']} AND `stop` IS NOT NULL AND `stop` = `stops`.`id` ORDER BY `distance_from_start` ASC LIMIT 1";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) == 1)
					{
						$stopdata2 = mysqli_fetch_array($result);
						$name2 = $stopdata2['name'];
					}
					
					$all = $stopdata2['distance_from_start'] - $stopdata1['distance_from_start'];
					$gone = $busdata['last_distance'] - $stopdata1['distance_from_start'];
					$st = ceil(($gone / $all) * 8);
					echo "<td style='text-align: right;'><a href='stop.php?id={$stopdata1['stop']}' target='_blank'>{$name1}</a>&nbsp;</td><td style='text-align: center;'>";
					for($i = 1; $i <= 8; $i++)
					{
						if($i == $st)
						{
							echo "●";
						}
						else
						{
							echo "○";
						}
					}
					echo "</td><td style='text-align: left;'>&nbsp;<a href='stop.php?id={$stopdata2['stop']}' target='_blank'>{$name2}</a></td>";
				}
				else
				{
					echo "<td colspan='3' style='text-align: center;'>×</td>";
				}
				
				echo "<td>" . date("H:i:s", $busdata['last_update']) . " <span style='font-size:0.7em'>(";
					$ag = $now - $busdata['last_update'];
					if($ag < 10)
					{
						echo "ตอนนี้";
					}
					else
					{
						$min = floor($ag / 60);
						$sec = $ag % 60;
						if($min > 0)
						{
							echo "$min นาที ";
						}
						echo "$sec วินาที";
					}
				echo ")</span></td>";
				
				echo "</tr>";
			}
			
			echo "</table>";
		?>
		<div style="font-size: 0.6em"><strong>สถานะ</strong> ○ อยู่ในระบบ, △ ขาดการติดต่อเกิน 1 นาที, × ขาดการติดต่อเกิน 5 นาที</div>
		<hr>
		<h1><a href="explorer.php">** EXPLORER **</a></h1>
		<h1>&nbsp;</h1>
	</body>
</html>
<?php mysqli_close($connection); ?>
