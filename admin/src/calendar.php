<?php if(!isset($_SESSION['authentication'])) exit(); ?>
<?php
	include_once("../lib/app.inc.php");
	
	$today = mktime(0, 0, 0);
	
	if(isset($_GET['day']))
	{
		$day = new Day($_GET['day']);
	}
	else
	{
		$day = new Day($today);
	}
	
	if(isset($_POST['action']))
	{
		if($_POST['action'] == "setdetail")
		{
			$detail = $_POST['detail'];
			$day->SetDetail($detail);
			
			header("location: admin.php?page=calendar&day={$day->Timestamp}");
		}
		else if($_POST['action'] == "settype")
		{
			$type = $_POST['type'];
			$day->SetTypeTo($type);
				
			header("location: admin.php?page=calendar&day={$day->Timestamp}");
		}
		else if($_POST['action'] == "newswitcher")
		{
			$date = $_POST['date'];
			$route = $_POST['route'];
			$available = $_POST['available'];
			
			$sql = "";
			
			$routedata = array();
			
			$routes = get_routes_at($date);
			foreach($routes as $_route)
			{
				if($_route['id'] == $route)
				{
					$routedata = $_route;
					break;
				}
			}
			
			if($routedata['available'] != $available)
			{
				$sql = "INSERT INTO `route_available_switchers` (`id`, `date`, `route`, `set_available_to`) VALUES (0, $date, $route, $available)";
				mysqli_query($connection, $sql);
				
				if($available == 1)
				{
					foreach($routes as $_route)
					{
						if($_route['refid'] == $routedata['refid'] && $_route['available'] == 1 && $_route['id'] != $route)
						{
							$sql = "INSERT INTO `route_available_switchers` (`id`, `date`, `route`, `set_available_to`) VALUES (0, $date, {$_route['id']}, 0)";
							mysqli_query($connection, $sql);
						}
					}
				}
			}
			
			header("location: admin.php?page=calendar&day={$day->Timestamp}");
		}
	}
	else if(isset($_GET['removeswitcherid']))
	{
		$removeid = $_GET['removeswitcherid'];
		
		$sql = "DELETE FROM `route_available_switchers` WHERE `id` = $removeid";
		mysqli_query($connection, $sql);
		
		header("location: admin.php?page=calendar&day={$day->Timestamp}");
	}
?>

<div class="col-lg-12">
	<h3><i class="fa fa-calendar"></i> ปฏิทินการเดินรถ</h3>
	<div class="content-panel col-lg-12">
		<?php
			$previous_day = $day->Timestamp - 86400;
			$next_day = $day->Timestamp + 86400;
		?>
		<a href="admin.php?page=calendar&day=<?php echo $previous_day; ?>"><i class="fa fa-arrow-left"></i> วันก่อนหน้า</a>　
		<a href="admin.php?page=calendar&day=<?php echo $next_day; ?>">วันถัดไป <i class="fa fa-arrow-right"></i></a>
		
		<h3><?php echo thai_date("฿วว ฿วท ฿ดด ฿ปปปป", $day->Timestamp) . " (" . $day->TypeToString() . ")"; ?></h3>
		<?php
			if($day->Detail != "")
			{
				echo "<h4>{$day->Detail}</h4>";
			}
		?>
		
		<form action="admin.php?page=calendar&day=<?php echo $day->Timestamp; ?>" method="post"><p>
			คำอธิบายวัน: <input type="text" name="detail" value="<?php echo $day->Detail; ?>">
			<input type="hidden" name="action" value="setdetail">
			<button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-save"></i> บันทึก</button>
		</p></form>
		
		<p><form action="admin.php?page=calendar&day=<?php echo $day->Timestamp; ?>" method="post" class="display: inline-block">
			แก้ไขประเภทวัน: <select name="type">
				<?php
				$sql = "SELECT `id`, `name` FROM `day_types` ORDER BY `name` ASC";
				$results = mysqli_query($connection, $sql);
				while($typedata = mysqli_fetch_array($results))
				{
					echo "<option value='{$typedata['id']}'";
					if($day->Type == $typedata['id'])
					{
						echo " selected";
					}
					echo ">{$typedata['name']}</option>";
				}
				?>
			</select>
			<input type="hidden" name="action" value="settype">
			<button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-save"></i> บันทึก</button>
		</form>
		
		<?php
		if($day->Timestamp == $today)
		{
			echo "<hr>";
			
			echo "<h4>" . date("H:i") . " (ปัจจุบัน)</h4>";
			
			echo "<ul>";
			
			$routes = get_routes_at(mktime());
			foreach($routes as $routedata)
			{
				if($routedata['available'])
				{
					echo "<li style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</li>";
				}
			}
			
			echo "</ul>";
		}
		else if($day->Timestamp > $today)
		{
			echo "<hr>";
			
			echo "<h4>ต้นวัน</h4>";
			
			echo "<ul>";
			
			$routes = get_routes_at($day->Timestamp);
			foreach($routes as $routedata)
			{
				if($routedata['available'])
				{
					echo "<li style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</li>";
				}
			}
			
			echo "</ul>";
		}
		
		if($day->Timestamp >= $today)
		{
			if($day->Timestamp == $today)
			{
				$start = mktime();
			}
			else
			{
				$start = $day->Timestamp;
			}
			
			$end = $day->Timestamp + 86399;
			
			$sql = "SELECT `date` FROM `route_available_switchers` GROUP BY `date` HAVING `date` BETWEEN $start AND $end";
			$results = mysqli_query($connection, $sql);
			while($datedata = mysqli_fetch_array($results))
			{
				echo "<hr><h4>" . date("H:i", $datedata['date']) . "</h4>";
				$routes = get_routes_at($datedata['date']);
				
				echo "<div class='row'><div class='col-lg-4'><ul>";
				foreach($routes as $routedata)
				{
					if($routedata['available'] == 0)
					{
						continue;
					}
					echo "<li style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</li>";
				}
				echo "</ul></div>";
				echo "<div class='col-lg-8'>";
				echo "<h5>คำสั่ง</h5><ul>";
				$sql = "SELECT `id`, `route`, `set_available_to` FROM `route_available_switchers` WHERE `date` = {$datedata['date']}";
				$results2 = mysqli_query($connection, $sql);
				while($switcherdata = mysqli_fetch_array($results2))
				{
					$sql = "SELECT `name`, `detail`, `color` FROM `routes` WHERE `id` = {$switcherdata['route']}";
					$result = mysqli_query($connection, $sql);
					$routedata = mysqli_fetch_array($result);
					
					echo "<li><a href='admin.php?page=calendar&day={$day->Timestamp}&removeswitcherid={$switcherdata['id']}' class='btn btn-default btn-xs' onclick='return confirm(\"แน่ใจหรือไม่\");'><i class='fa fa-trash'></i> ลบ</a> <span style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</span> ";
					
					if($switcherdata['set_available_to'] == 1)
					{
						echo "เปิดการเดินรถ";
					}
					else
					{
						echo "หยุดการเดินรถ";
					}
					
					echo "</li>";
				}
				echo "</ul></div>";
				echo "</div>";
			}
			
			?>
			<form action="admin.php?page=calendar&day=<?php echo $day->Timestamp; ?>" method="post">
				<hr>
				<h4>เพิ่มคำสั่งใหม่</h4>
				เวลา
				<select name="date">
					<?php
					$now = mktime();
					
					$i = $day->Timestamp;
					if($i > $now)
					{
						echo "<option value='$i'>" . date("H:i", $i) . "</option>";
					}
					
					for($i = $day->Timestamp + 21600; $i < $day->Timestamp + 82801; $i += 1800)
					{
						if($i < $now)
						{
							continue;
						}
						echo "<option value='$i'>" . date("H:i", $i) . "</option>";
					}
					?>
				</select>
				ให้
				<select name="route">
					<?php
					$sql = "SELECT `id`, `name`, `detail`, `color` FROM `routes` ORDER BY `name` ASC, `detail` ASC";
					$results = mysqli_query($connection, $sql);
					while($routedata = mysqli_fetch_array($results))
					{
						echo "<option value='{$routedata['id']}' style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</option>";
					}
					?>
				</select>
				<select name="available">
					<option value="0">งดการเดินรถ</option>
					<option value="1">เปิดการเดินรถ</option>
				</select>
				<input type="hidden" name="action" value="newswitcher">
				<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> ตกลง</button>
			</form>
			<?php
		}
		?>
	</div>	
</div>