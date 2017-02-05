<?php if(!isset($_SESSION['authentication'])) exit(); ?>
<div class="col-lg-12">
	<a href="admin.php?page=routes"><i class="fa fa-arrow-left"></i> กลับ</a>
	<h3><i class="fa fa-plus"></i> เพิ่มเส้นทางใหม่</h3>
	<div class="row"><div class="col-md-12">
		<form action="admin.php?page=newroute" method="post">
			<p>
				<label>เส้นทางต้นแบบ: </label>
				<select name="template" ng-model="selected_template" ng-change="template_changed()">
					<option value="0" selected>--ไม่มี--</option>
					<option ng-repeat="route in routes" value="{{route.id}}" style="color: #{{route.color}};">{{route.name}} ({{route.detail}})</option>
				</select>
			</p>
			<p><label>รหัสอ้างอิง: <input type="text" name="refid" ng-model="template.refid"></label> <span class="text-info">ใช้สำหรับอ้างอิงดึงข้อมูลพิกัดจาก cmutransit.com</span></p>
			<p><label>ชื่อเส้นทาง: <input type="text" name="name" ng-model="template.name"></label></p>
			<p><label>คำอธิบาย: <input type="text" name="detail" ng-model="template.detail"></label></p>
			<p><label>สีเส้นทาง: #<input id="color-txt" type="text" name="color" ng-model="template.color"></label> <span class="text-info">กรุณากรอกเป็นรหัสสี hex</span></p>
			<input type="hidden" name="submit" value="true">
			<button class="btn btn-theme" type="submit">ถัดไป <i class="fa fa-arrow-right"></i></button>
		</form>
	</div></div>
	<?php
	if(isset($_POST['submit']))
	{
		$refid = $_POST['refid'];
		$name = $_POST['name'];
		$color = $_POST['color'];
		$template = $_POST['template'];
		$detail = $_POST['detail'];
		
		$sql = "INSERT INTO `routes` (`id`, `refid`, `name`, `color`, `available`, `detail`) VALUES (0, $refid, '$name', '$color', 0, '$detail');";
		$result = mysqli_query($connection, $sql);
		
		$insert_id = mysqli_insert_id($connection);
		
		if($template > 0)
		{			
			$sql = "INSERT INTO `route_paths` (`id`, `route`, `sequence`, `distance_from_start`, `location_lat`, `location_lon`, `stop`) (SELECT 0, $insert_id, `sequence`, `distance_from_start`, `location_lat`, `location_lon`, `stop` FROM `route_paths` WHERE `route` = $template)";
			mysqli_query($connection, $sql);
			
			header("location:admin.php?page=route&id=" . $insert_id);
		}
	}
	?>
</div>
<script>
var app = angular.module("cmubus", []);
app.controller("cmubus_controller", function($scope, $http)
{
	$scope.template_changed = function()
	{
		if($scope.selected_template == 0)
		{
			$scope.template = {
				"refid": "",
				"name": "",
				"detail": "",
				"color": ""
			};
		}
		else
		{			
			for(i = 0; i < $scope.routes.length; i++)
			{
				if($scope.routes[i].id == $scope.selected_template)
				{
					$scope.template = {
						"refid": $scope.routes[i].refid,
						"name": $scope.routes[i].name,
						"detail": $scope.routes[i].detail,
						"color": $scope.routes[i].color
					};
					
					break;
				}
			}
		}
	};
	
	$scope.$watch("template.color", function()
	{
		$("#color-txt").css("color", "#" + $scope.template.color);
	});
	
	$http.get("api/routes.php").then(function(response)
	{
		$scope.routes = response.data;
	}, function(response)
	{
		alert("พบข้อผิดพลาด");
	});
});	
</script>