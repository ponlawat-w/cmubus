<?php if(!isset($_SESSION['authentication'])) exit(); ?>
<?php
	$id = $_GET['id'];
	$sql = "SELECT `name`, `location_lat`, `location_lon`, `busstop` FROM `stops` WHERE `id` = $id";
	$result = mysqli_query($connection, $sql);
	$stopdata = mysqli_fetch_array($result);
	
	if(isset($_POST['action']))
	{
		if($_POST['action'] == "editstop")
		{
			$name = $_POST['name'];
			$lat = $_POST['lat'];
			$lon = $_POST['lon'];
			$busstop = $_POST['busstop'];
			
			$sql = "UPDATE `stops` SET `name` = '$name', `location_lat` = $lat, `location_lon` = $lon, `busstop` = $busstop WHERE `id` = $id";
			mysqli_query($connection, $sql);
			
			header("location:admin.php?page=stop&id={$id}");			
		}
		else if($_POST['action'] == "newtag")
		{
			$name = $_POST['name'];
			
			$sql = "INSERT INTO `stops_tags` (`id`, `stop`, `name`) VALUES (0, $id, '$name')";
			mysqli_query($connection, $sql);
			
			header("location:admin.php?page=stop&id={$id}#tags");
		}
		else if($_POST['action'] == "newconnection")
		{
			$stop2 = $_POST['stop2'];
			$connection_time = $_POST['connection_time'] * 60;
			
			$sql = "INSERT INTO `connections` (`id`, `stop1`, `stop2`, `connection_time`) VALUES (0, $id, $stop2, $connection_time)";
			mysqli_query($connection, $sql);
			
			header("location:admin.php?page=stop&id={$id}#connections");
		}
		else if($_POST['action'] == "removestop")
		{
			$sql = "DELETE FROM `stops_tags` WHERE `stop` = $id";
			mysqli_query($connection, $sql);
			
			$sql = "DELETE FROM `connections` WHERE `stop1` = $id OR `stop2` = $id";
			mysqli_query($connection, $sql);
			
			$sql = "UPDATE `route_paths` SET `location_lat` = {$stopdata['location_lat']}, `location_lon` = {$stopdata['location_lon']}, `stop` = NULL WHERE `stop` = $id";
			mysqli_query($connection, $sql);
			
			$sql = "DELETE FROM `stops` WHERE `id` = $id";
			mysqli_query($connection, $sql);
			header("location:admin.php?page=stops");
		}
	}
	else if(isset($_GET['removetagid']))
	{
		$tagremove = $_GET['removetagid'];
		
		$sql = "DELETE FROM `stops_tags` WHERE `stop` = $id AND `id` = $tagremove";
		mysqli_query($connection, $sql);
			
		header("location:admin.php?page=stop&id={$id}#tags");
	}
	else if(isset($_GET['removeconnectionid']))
	{
		$connectionremove = $_GET['removeconnectionid'];
		
		$sql = "DELETE FROM `connections` WHERE (`stop1` = $id OR `stop2` = $id) AND `id` = $connectionremove";
		mysqli_query($connection, $sql);
			
		header("location:admin.php?page=stop&id={$id}#tags");
	}
?>
<div class="col-lg-12">
	<a href="admin.php?page=stops"><i class="fa fa-arrow-left"></i> กลับ</a>
	<form action="admin.php?page=stop&id=<?php echo $id; ?>" method="post">
		<h3>ข้อมูลทั่วไป</h3>
		ชื่อ: <input type="text" name="name" value="<?php echo $stopdata['name']; ?>"><br>
		<input type="hidden" name="lat" ng-value="lat">
		<input type="hidden" name="lon" ng-value="lon">
		ตำแหน่ง: <span ng-bind="locationtxt"></span><br>
		<leaflet
			style="width:50%; height:30vh; cursor: crosshair;"
			center="center"
			markers="markers">
		</leaflet><br>
		ชนิด:
			<label><input type="radio" name="busstop" value="1"<?php echo ($stopdata['busstop'] == 1)?" checked":""; ?>> ป้ายหยุด</label>
			<label><input type="radio" name="busstop" value="0"<?php echo ($stopdata['busstop'] == 0)?" checked":""; ?>> สถานที่</label><br>
		<input type="hidden" name="action" value="editstop">
		<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> บันทึกการแก้ไข</button>
	</form>
	
	<hr>
	
	<h3 id="tags">ชื่ออื่น ๆ</h3>
	<div class="content-panel">
		<ul><?php
			$sql = "SELECT `id`, `name` FROM `stops_tags` WHERE `stop` = $id ORDER BY `name` ASC";
			$results = mysqli_query($connection, $sql);
			while($tagdata = mysqli_fetch_array($results))
			{
				echo "<li>{$tagdata['name']} <a href='admin.php?page=stop&id={$id}&removetagid={$tagdata['id']}' class='text-danger' onclick='return confirm(\"แน่ใจหรือไม่ว่าต้องการลบ\");'><i class='fa fa-trash'></i> ลบ</a></li>";
			}
		?></ul>
		<form action="admin.php?page=stop&id=<?php echo $id; ?>" method="post">
			<input type="hidden" name="action" value="newtag">
			<input type="text" name="name" placeholder="เพิ่มใหม่"> <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> เพิ่ม</button>
		</form>
	</div>
	
	<hr>
	
	<h3 id="connections">การเชื่อมต่อ</h3>
	<div class="content-panel">
		<ul><?php
			$sql = "SELECT `id`, `stop1`, `stop2`, `connection_time` FROM `connections` WHERE `stop1` = $id OR `stop2` = $id";
			$results = mysqli_query($connection, $sql);
			while($connectiondata = mysqli_fetch_array($results))
			{
				if($connectiondata['stop1'] == $id)
				{
					$connectionid = $connectiondata['stop2'];
				}
				else if($connectiondata['stop2'] == $id)
				{
					$connectionid = $connectiondata['stop1'];
				}
				
				$sql = "SELECT `name` FROM `stops` WHERE `id` = $connectionid";
				$result = mysqli_query($connection, $sql);
				$connectionstopdata = mysqli_fetch_array($result);
				$connectionname = $connectionstopdata['name'];
				
				$connectiondata['connection_time'] = floor($connectiondata['connection_time'] / 60);
				
				echo "<li><i class='fa fa-arrow-right'></i> $connectionname ({$connectiondata['connection_time']} นาที) <a href='admin.php?page=stop&id={$id}&removeconnectionid={$connectiondata['id']}' class='text-danger' onclick='return confirm(\"แน่ใจหรือไม่ว่าต้องการลบ\");'><i class='fa fa-trash'></i> ลบ</a></li>";
			}
		?></ul>
		<form action="admin.php?page=stop&id=<?php echo $id; ?>" method="post">
			<input type="hidden" name="action" value="newconnection">
			ไปยัง
			<select name="stop2">
				<option ng-repeat="stop in stops" value="{{stop.id}}">{{stop.name}}</option>
			</select>
			ใช้เวลาประมาณ
			<input type="number" name="connection_time" style="width: 4em;"> นาที
			<button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> เพิ่ม</button>
		</form>
	</div>
	
	<hr>
	
	<h3>สายที่ผ่าน</h3>
	
	<div class="content-panel">
		<ul>
		<?php
		$sql = "SELECT DISTINCT `routes`.`id`, `name`, `detail`, `color` FROM `route_paths`, `routes` WHERE `route_paths`.`route` = `routes`.`id` AND `stop` = $id AND `available` = 1 ORDER BY `name`, `detail`";
		$results = mysqli_query($connection, $sql);
		while($routedata = mysqli_fetch_array($results))
		{
			echo "<li><a href='admin.php?page=route&id={$routedata['id']}' style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</a></li>";
		}
		?>
		</ul>
	</div>
	
	<hr>
	
	<form action="admin.php?page=stop&id=<?php echo $id; ?>" method="post">
		<input type="hidden" name="action" value="removestop">
		<button type="submit" class="btn btn-danger" onclick="return confirm('แน่ใจหรือไม่ว่าต้องการลบสถานที่นี้');"><i class="fa fa-trash"></i> ลบ</button>
	</form>
</div>

<script src="https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js"></script>
<script src="assets/js/angularleaflet/angular-leaflet-directive.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css">
<script>
var app = angular.module("cmubus", ['leaflet-directive']);

app.controller("cmubus_controller", function($scope, $http)
{
	$scope.center = {
		"lat": <?php echo $stopdata['location_lat']; ?>,
		"lng": <?php echo $stopdata['location_lon']; ?>,
		"zoom": 16
	};
	
	$scope.set_location = function(lat, lon)
	{
		$scope.locationtxt = lat + "," + lon;
		
		$scope.markers = [{
			"lat": lat,
			"lng": lon
		}];
		
		$scope.lat = lat;
		$scope.lon = lon;
	};
	
	$scope.set_location(<?php echo $stopdata['location_lat']; ?>, <?php echo $stopdata['location_lon']; ?>);
	
	$scope.$on("leafletDirectiveMap.click", function(event, args)
	{
		var e = args.leafletEvent;
		
		$scope.set_location(e.latlng.lat, e.latlng.lng);
	});
	
	$http.get("api/stops.php").then(function(response)
	{
		$scope.stops = [];
		
		var j = 0;
		for(i = 0; i < response.data.length; i++)
		{
			<?php
			if($stopdata['busstop'] == 0)
			{
				?>
				if(response.data[i].id == <?php echo $id; ?> || response.data[i].busstop == 0)
				{				
					continue;
				}
				<?php
			}
			else
			{
				?>
				if(response.data[i].id == <?php echo $id; ?>)
				{				
					continue;
				}
				<?php
			}
			?>

			$scope.stops[j] = response.data[i];
			j++;
		}
	}, function(reponse)
	{
		alert("พบข้อผิดพลาด");
	});
});
</script>