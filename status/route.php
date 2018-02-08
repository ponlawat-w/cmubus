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
                $("#date").change(function()
                {
                    window.location = $(this).val();
                });
            });
        </script>
	</head>
	<body>
		<a href="explorer.php">≪ กลับ</a>
		<?php
			$id = $_GET['id'];
			
			echo "<h3>" . get_text("route", $id, "th") . "</h3><h4>SELECT DAY</h4>";
			
			$sql = "SELECT MIN(`start_datetime`) AS `min_datetime` FROM `sessions` WHERE `route` = $id";
			$results = mysqli_query($connection, $sql);
			$mindata = mysqli_fetch_array($results);

			$year = date("Y", $mindata['min_datetime']);
			$month = date("n", $mindata['min_datetime']);
			$date = date("j", $mindata['min_datetime']);

			$mintime = mktime(0, 0, 0, $month, $date, $year);

			echo "<select id='date'>";
			echo "<option>เลือกวันที่</option>";
			for($i = mktime(0, 0, 0); $i >= $mintime; $i -= 86400)
			{
				$start = $i;
				$stop = $i + 86400;
				
				$sql = "SELECT COUNT(*) AS `session_num` FROM `sessions` WHERE `route` = $id AND `start_datetime` BETWEEN $start AND $stop ORDER BY `start_datetime` ASC";
				$result = mysqli_query($connection, $sql);
				$data = mysqli_fetch_array($result);

				if($data['session_num'] == 0)
                {
                    continue;
                }

				echo "<option value='route.php?id=$id&dt=$i'";
                if(isset($_GET['dt']) && $_GET['dt'] == $i)
                {
                    echo " selected";
                }
				echo ">" . thai_date("฿วทวท ฿ด ฿ปป", $i) . " ({$data['session_num']})</option><br>";
			}
			echo "</select>";
			
			if(isset($_GET['dt']))
			{
				echo "<hr>";
				$dt = $_GET['dt'];
				$dt_ = $dt + 86400;
				$lasth = null;
				
				echo "<h4>" . thai_date("วัน ฿วว ที่ ฿วท ฿ดด พ.ศ.฿ปปปป", $dt) . "</h4>";
				
				echo "<div style='font-size: 0.8em'>";
				$hrCount = -1;
				$sql = "SELECT `id`, `start_datetime`, `busno` FROM `sessions` WHERE `route` = $id AND `start_datetime` BETWEEN $dt AND $dt_";
				$results = mysqli_query($connection, $sql);
				while($sessiondata = mysqli_fetch_array($results))
				{
                    $thish = date("G", $sessiondata['start_datetime']);
                    $hrCount++;

                    if($lasth == null)
					{
						$lasth = $thish - 1;
					}
					
					if($lasth != $thish)
					{
						$lasth = $thish;
						if($hrCount > 0)
                        {
						    echo "<br><small>　　(Sum: $hrCount)</small>";
						    $hrCount = 0;
                        }
						echo "<div style='font-size:1.5em; font-weight:bold; margin-top: 0.5em;'>$lasth</div>　";
					}
					echo " <a href='session.php?id={$sessiondata['id']}'>" . date("i", $sessiondata['start_datetime']) . "<sup style='font-size:0.6em;'>#{$sessiondata['busno']}</sup></a>";
				}
                echo "<br><small?　　(Sum: " . ($hrCount + 1) . ")</small>";
				echo "</div>";
			}
		?>
	</body>
</html>
<?php mysqli_close($connection); ?>