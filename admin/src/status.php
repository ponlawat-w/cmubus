<?php if(!isset($_SESSION['authentication'])) exit(); ?>
  <div class="col-lg-12">
	<h3><i class="fa fa-circle"></i> สถานะรถ</h3>
	<div class="row"><div class="col-md-12"><div class="content-panel">
		<table class="table table-hover">
			<thead>
				<tr>
					<th>เส้นทาง</th>
					<th>หมายเลขรถ</th>
					<th>การวิ่ง</th>
					<th>ป้ายถัดไป</th>
					<th>ระยะทาง</th>
					<th>ข้อมูลเมื่อ</th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="bus in bus_status" style="cursor: pointer;" ng-click="go_session(bus.session)">
					<td style="color: #{{bus.route_color}};">{{bus.route}}</td>
					<td>{{bus.busno}}</td>
					<td>{{bus.percent}}%</td>
					<td>{{bus.nextstop}}</td>
					<td> อีก {{bus.distance_to_nextstop|distance}}</td>
					<td>{{bus.last_update|relativeTime}}</td>
				</tr>
			</tbody>
		</table>
	</div></div></div>
  </div>

  
	
	<script>
		var app = angular.module("cmubus", []);
		app.controller("cmubus_controller", function($scope, $http, $timeout)
		{
			$scope.get_data = function()
			{
				$http.get("api/status.php").then(function(response)
				{
					$scope.bus_status = response.data;
					$timeout(function()
					{
						$scope.get_data();
					}, 5000);
				}, function(response)
				{
					alert("พบข้อผิดพลาด");
				});
			};
			
			$scope.get_data();
			
			$scope.go_session = function(id)
			{
				window.location = "session.php?id=" + id;
			};
		});
	</script>