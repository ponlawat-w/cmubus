<?php if(!isset($_SESSION['authentication'])) exit(); ?>

<div class="col-lg-12">
<h3><i class="fa fa-bus"></i> ป้ายหยุดรถและสถานที่</h3>
<div class="row"><div class="col-md-12">
	<div class="col-md-6">
		<div class="content-panel">
			<input type="text" class="form-control" placeholder="ค้นหา" ng-model="search_keyword">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>ชื่อเรียก</th>
						<th>สถานะ</th>
						<th>การจัดการ</th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="stop in stops_data | filter:search_keyword">
						<td>{{stop.name}}</td>
						<td>{{stop.busstop|isstop}}</td>
						<td>
							<a href="javascript:void(0);" ng-click="showmarker(stop.id)"><i class="fa fa-map-marker"></i> ดูตำแหน่ง</a>
							<a href="admin.php?page=stop&id={{stop.id}}"><i class="fa fa-wrench"></i> แก้ไข</a>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-md-6">
		<div class="container" style="position: fixed;">
			<leaflet
				style="width:50%; height:75vh; cursor: crosshair;"
				center="center"
				markers="markers"
				ng-mouseover="load_stop_markers()"
				ng-mouseleave="clear_markers()">
			</leaflet>
		</div>
	</div>
</div></div>

<script src="https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.js"></script>
<script src="assets/js/angularleaflet/angular-leaflet-directive.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/leaflet@1.0.0-rc.3/dist/leaflet.css">
<script>
var app = angular.module("cmubus", ['leaflet-directive']);
app.controller("cmubus_controller", function($scope, $http, $timeout)
{
	$scope.center = {
		"lat": 18.8003261,
		"lng": 98.9521358,
		"zoom": 15
	};
	
	$scope.markers = [];
	
	$scope.place_icon = {
		"iconUrl": "assets/img/icons/place.png",
		"iconSize": [8, 8]
	};
	
	$scope.stop_icon = {
		"iconUrl": "assets/img/icons/stop.png",
		"iconSize": [8, 8]
	};
	
	$scope.showmarker = function(stopid)
	{
		for(i = 0; i < $scope.markers.length; i++)
		{
			if($scope.markers[i].id == stopid)
			{
				$scope.markers[i].focus = true;
				
				break;
			}
		}
	};
	
	$scope.$on("leafletDirectiveMap.click", function(event, args)
	{
		var e = args.leafletEvent;
		
		window.location = "admin.php?page=newstop&lat=" + e.latlng.lat + "&lon=" + e.latlng.lng;
	});
	
	$http.get("api/stops.php").then(function(response)
	{
		$scope.stops_data = response.data;
	}, function(response)
	{
		alert("พบข้อผิดพลาด");
	});
	
	$http.get("api/stops.php").then(function(response)
	{		
		for(i = 0; i < response.data.length; i++)
		{
			$scope.markers[i] = {
				"id": response.data[i].id,
				"lat": response.data[i].location_lat * 1,
				"lng": response.data[i].location_lon * 1,
				"focus": false,
				"message": "<a class='btn btn-default' href='admin.php?page=stop&id=" + response.data[i].id + "'>" + response.data[i].name + "</a>",
				"icon": null
			};
			
			if(response.data[i].busstop == 1)
			{
				$scope.markers[i].icon = $scope.stop_icon;
			}
			else
			{
				$scope.markers[i].icon = $scope.place_icon;
			}
		}
	}, function(response)
	{
		alert("พบข้อผิดพลาด");
	});
});
</script>