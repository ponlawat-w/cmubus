<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<?php
			include_once("../mysql_connection.inc.php");
			include_once("../lib/cron.inc.php");
			include_once("../lib/app.inc.php");
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
			
			echo "<h3>ROUTE#$id</h3><h4>SELECT DAY</h4>";
			echo "<p>";
			echo "<a href='data/runtime.php?route={$id}&dt=all' target='_blank'>[AllRunTime]</a> ";
			echo "<a href='data/avgspeed.php?route={$id}&dt=all' target='_blank'>[AllAvgSpeed]</a>";
			echo "</p>";
			
			$sql = "SELECT MIN(`start_datetime`) AS `min_datetime` FROM `sessions` WHERE `route` = $id";
			$results = mysqli_query($connection, $sql);
			$mindata = mysqli_fetch_array($results);

			$year = date("Y", $mindata['min_datetime']);
			$month = date("n", $mindata['min_datetime']);
			$date = date("j", $mindata['min_datetime']);

			$mintime = mktime(0, 0, 0, $month, $date, $year);

			for($i = mktime(0, 0, 0); $i >= $mintime; $i -= 86400)
			{
				$start = $i;
				$stop = $i + 86400;
				
				$sql = "SELECT COUNT(*) AS `session_num` FROM `sessions` WHERE `route` = $id AND `start_datetime` BETWEEN $start AND $stop ORDER BY `start_datetime` ASC";
				$result = mysqli_query($connection, $sql);
				$data = mysqli_fetch_array($result);
				
				echo "<a href='route.php?id=$id&dt=$i'>" . date("Y-m-d", $i) . " ({$data['session_num']})</a><br>";
			}
			
			if(isset($_GET['dt']))
			{
				echo "<hr>";
				$dt = $_GET['dt'];
				$dt_ = $dt + 86400;
				$lasth = null;
				
				echo "<h4>" . date("Y-m-d", $dt) . "</h4>";
				
				echo "<p><a href='data/runtime.php?route={$id}&dt={$dt}' target='_blank'>[RunTime]</a>";
				echo "　<a href='data/avgspeed.php?route={$id}&dt={$dt}' target='_blank'>[AvgSpeed]</a></p>";
				
				echo "<div style='font-size: 0.8em'>";
				$sql = "SELECT `id`, `start_datetime`, `busno` FROM `sessions` WHERE `route` = $id AND `start_datetime` BETWEEN $dt AND $dt_";
				$results = mysqli_query($connection, $sql);
				while($sessiondata = mysqli_fetch_array($results))
				{
					$thish = date("G", $sessiondata['start_datetime']);
					
					if($lasth == null)
					{
						$lasth = $thish - 1;
					}
					
					if($lasth != $thish)
					{
						$lasth = $thish;
						echo "<div style='font-size:1.5em; font-weight:bold; margin-top: 0.5em;'>$lasth</div>　";
					}
					echo " <a href='session.php?id={$sessiondata['id']}'>" . date("i", $sessiondata['start_datetime']) . "<sup style='font-size:0.6em;'>#{$sessiondata['busno']}</sup></a>";
				}
				echo "</div>";
			}
		?>
	</body>
</html>
<?php mysqli_close($connection); ?>