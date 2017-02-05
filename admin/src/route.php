<?php if(!isset($_SESSION['authentication'])) exit();

$id = $_GET['id'];

$sql = "SELECT `name`, `color`, `detail`, `available`, `refid` FROM `routes` WHERE `id` = $id";
$result = mysqli_query($connection, $sql);
$routedata = mysqli_fetch_array($result);

if(isset($_GET['action']) && $_GET['action'] == "remove")
{
	$sql = "DELETE FROM `route_paths` WHERE `route` = $id";
	mysqli_query($connection, $sql);
	
	$sql = "DELETE FROM `routes` WHERE `id` = $id";
	mysqli_query($connection, $sql);
	
	header("location: admin.php?page=routes");
}

?>
<div class="col-lg-12">
	<div class="row">
		<div class="col-md-6 content-panel">
			<a href="admin.php?page=routes"><i class="fa fa-arrow-left"></i> กลับ</a>
			<h3><input id="route_name" type="text" ng-model="route_name" style="color: #<?php echo $routedata['color']; ?>;"></h3>
			คำอธิบาย: <input type="text" ng-model="route_detail"><br>
			สีประจำเส้นทาง: #<input type="text" ng-model="route_color"><br>
			หมายเลขอ้างอิง: <input type="text" ng-model="route_refid"><br>
			สถานะ:
			<?php
			if($routedata['available'] == 1)
			{
				echo "<span class='text-success'>ปกติ</span>";
			}
			else if($routedata['available'] == 0)
			{
				echo "<span class='text-danger'>หยุดเดินรถ</span>";
			}
			?>
			<br><a href="admin.php?page=calendar" class="btn btn-default"><i class="fa fa-calendar"></i> ตั้งค่าปฏิทินการเดินรถ</a>
			<hr>
			<table class="table table-hover">
				<thead>
					<tr>
						<th>ลำดับ</th>
						<th>ระยะทางที่</th>
						<th>ตำแหน่ง</th>
						<th>จัดการ</th>
					</tr>
				</thead>
				<tbody ng-mouseleave="clear_markers()">
					<tr ng-repeat="sequence in sequences" ng-mouseover="showseq(sequence.order - 1)">
						<td>{{sequence.order}}</td>
						<td>{{sequence.distance_from_start|distance}}</td>
						<td>{{sequence.display_location}}</td>
						<td><a class="text-danger" href="javascript:void(0);" ng-click="removefrom(sequence.order - 1)">ลบ ↴</a></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="col-md-6">
			<div class="container" style="position: fixed;">
				<leaflet
					style="width:50%; height:75vh; cursor: crosshair;"
					center="center"
					paths="path"
					markers="markers"
					ng-mouseover="load_stop_markers()"
					ng-mouseleave="clear_markers()">
				</leaflet>
				
				<div class="row"><div class="col-lg-12">
					<button class="btn btn-primary" ng-click="save()"><i class="fa fa-save"></i> บันทึก</button>
					<a class="btn btn-danger" href="admin.php?page=route&id=<?php echo $id; ?>&action=remove" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบเส้นทางนี้\n* ข้อมูลทั้งหมดที่เกี่ยวข้องกับเส้นทางนี้จะถูกลบออกไปด้วย');"><i class="fa fa-trash"></i> ลบ</a>
					<span ng-bind="status"></span>
				</div></div>
			</div>
		</div>
	</div>
</div>

<script src="https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js"></script>
<script src="assets/js/angularleaflet/angular-leaflet-directive.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css">
<script>
var app = angular.module("cmubus", ['leaflet-directive']);

app.controller("cmubus_controller", function($scope, $http)
{
	var stop_markers = {};
	
	// MAP //
	
	$scope.center = {
		"lat": 18.8003261,
		"lng": 98.9521358,
		"zoom": 15
	};
	
	$scope.markers = {
	};
	
	$scope.stop_icon = {
		"iconUrl": "assets/img/icons/stop.png",
		"iconSize": [8, 8]
	};
	
	$scope.path = {
		"line": {
			"color": "#<?php echo $routedata['color']; ?>",
			"type": "polyline",
			"weight": 2,
			"latlngs": []
		}
	};
	
	// EVENT FUNCTIONS //
	
	$scope.showseq = function(id)
	{
		$scope.markers = [
			{
				"lat": $scope.sequences[id].location_lat * 1,
				"lng": $scope.sequences[id].location_lon * 1
			}
		];
		
		if($scope.sequences[id].stop == null)
		{
			$scope.markers[0].focus = false;
		}
		else
		{
			$scope.markers[0].message = $scope.sequences[id].display_location;
			$scope.markers[0].focus = true;
		}
	};
	
	$scope.removefrom = function(order)
	{
		if(confirm("ยืนยันการลบจุด **ตั้งแต่** จุดนี้ไปจะถูกลบทั้งหมด"))
		{
			var newsequences = [];
			for(i = 0; i < order; i++)
			{
				newsequences[i] = $scope.sequences[i];
			}
		
			$scope.sequences = newsequences;
		}
	};
	
	$scope.load_stop_markers = function()
	{
		$scope.markers = stop_markers;
	};
	
	$scope.clear_markers = function()
	{
		$scope.markers = [];
	};
	
	$scope.updatepath = function()
	{
		$scope.status = "";
		
		if($scope.sequences != null)
		{		
			$scope.path.line.latlngs = [];
			
			for(i = 0; i < $scope.sequences.length; i++)
			{
				$scope.path.line.latlngs[i] = {"lat": $scope.sequences[i].location_lat * 1, "lng": $scope.sequences[i].location_lon * 1};
			}
		}
	};
	
	$scope.stopon = function(stopid)
	{		
		for(i = 0; i < $scope.stops.length; i++)
		{
			if(stopid == $scope.stops[i].id)
			{
				$scope.sequences.push({
					"id": $scope.sequences.length,
					"order": $scope.sequences.length + 1,
					"distance_from_start": null,
					"location_lat": $scope.stops[i].location_lat,
					"location_lon": $scope.stops[i].location_lon,
					"stop": $scope.stops[i].id,
					"display_location": $scope.stops[i].name
				});
				
				window.scrollTo(0, document.body.scrollHeight);
				$scope.updatepath();
				
				break;
			}
		}
	};
	
	$scope.save = function()
	{
		$scope.status = "กำลังบันทึก…";
		
		$http.post(
			"api/route_paths_save.php?route_id=<?php echo $id; ?>",
			{
				"name": $scope.route_name,
				"detail": $scope.route_detail,
				"color": $scope.route_color,
				"refid": $scope.route_refid,
				"data": $scope.sequences
			}
		).then(function(response)
		{
			if(response.data.result == true)
			{
				$scope.status = "บันทึกเรียบร้อยแล้ว";
			}
			
			$scope.sequences = [];
			$scope.load_sequences();			
		}, function(response)
		{
			alert("พบข้อผิดพลาด");
		});
	};
	
	// EVENT LISTENERS //
	
	$scope.$watch("sequences", function()
	{
		$scope.updatepath();
	});
	
	$scope.$watch("route_color", function()
	{
		$("#route_name").css("color", "#" + $scope.route_color);
	});
	
	$scope.$on("leafletDirectiveMap.click", function(event, args)
	{		
		var e = args.leafletEvent;
		
		$scope.sequences.push({
			"id": 0,
			"order": $scope.sequences.length + 1,
			"distance_from_start": null,
			"location_lat": e.latlng.lat,
			"location_lon": e.latlng.lng,
			"stop": null,
			"display_location": e.latlng.lat + "," + e.latlng.lng
		});
		
		window.scrollTo(0, document.body.scrollHeight);
		
		$scope.updatepath();
	});
	
	// INITIAL FUNCTIONS //
	
	$scope.route_name = "<?php echo $routedata['name']; ?>";
	$scope.route_color = "<?php echo $routedata['color']; ?>";
	$scope.route_detail = "<?php echo $routedata['detail']; ?>";
	$scope.route_refid = <?php echo $routedata['refid']; ?>;
	
	$scope.load_sequences = function() {
			$http.get("api/route_paths.php?route_id=<?php echo $id; ?>").then(function(response)
		{
			$scope.sequences = response.data;
		}, function(response)
		{
			alert("พบข้อผิดพลาด");
		});
	};
	$scope.load_sequences();
	
	$http.get("api/stops.php").then(function(response)
	{
		$scope.stops = response.data;
		
		for(i = 0; i < response.data.length; i++)
		{
			if(response.data[i].busstop == 0)
			{
				continue;
			}
			
			stop_markers[i] = {
				"lat": response.data[i].location_lat * 1,
				"lng": response.data[i].location_lon * 1,
				"compileMessage": true,
				"getMessageScope": function() { return $scope; },
				"message": "<button class='btn btn-default' ng-click='stopon(" + response.data[i].id + ")'>" + response.data[i].name + "</button>",
				"icon": $scope.stop_icon
			};
		}
	}, function(response)
	{
		alert("พบข้อผิดพลาด");
	});
	
});
</script>