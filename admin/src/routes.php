<?php if(!isset($_SESSION['authentication'])) exit(); ?>
<div class="col-lg-12">
	<h3><i class="fa fa-bus"></i> เส้นทางเดินรถ</h3>
	<div class="row"><div class="col-md-12"><div class="content-panel">
  <div class="col-md-12">
		<p><a href="admin.php?page=newroute"><i class="fa fa-plus"> เส้นทางใหม่</i></a></p>
	</div>
		<table class="table table-hover">
			<thead>
				<tr>
					<th>รหัส</th>
					<th>รหัสอ้างอิง</th>
					<th colspan="2">ชื่อเรียก</th>
					<th>ระยะทาง</th>
					<th>สถานะปัจจุบัน</th>
				</tr>
			</thead>
			<tbody>
				<tr style="cursor: pointer;" ng-repeat="route in routes_data" ng-click="go_route(route.id)">
					<td>{{route.id}}</td>
					<td>{{route.refid}}</td>
					<td style="color: #{{route.color}}">{{route.name}}</td>
					<td>{{route.detail}}</td>
					<td>{{route.total_distance|distance}}</td>
					<td>{{route.available|isavailable}}</td>
				</tr>
			</tbody>
		</table>
		
	</div></div></div>
  </div>
	
	<script>
		var app = angular.module("cmubus", []);
		app.controller("cmubus_controller", function($scope, $http, $timeout)
		{
			$http.get("api/routes.php").then(function(response)
			{
				$scope.routes_data = response.data;
			}, function(response)
			{
				alert("พบข้อผิดพลาด");
			});
			
			$scope.go_route = function(route_id)
			{
				window.location = "admin.php?page=route&id=" + route_id;
			}
		});
	</script>