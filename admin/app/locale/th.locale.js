app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
			"search": "ค้นหาการเดินทาง",
			"viewroutes": "ดูเส้นทางเดินรถ",
			"searchingNear": "กำลังค้นหาข้อมูลป้ายที่อยู่ใกล้",
			"nearStops": "ป้ายที่อยู่ใกล้"
		},
		stop: {
			"route": "เส้นทาง",
			"busno": "เลขรถ",
			"eta": "เวลาถึง"
		},
		pleaseWait: "กรุณารอสักครู่",
		search: {
			"title": "ค้นหาการเดินทาง",
			"from": "จาก",
			"to": "ถึง",
			"detail": "ค้นหาสถานที่",
			"submit_btn": "ค้นหา",
			"searching": "กำลังค้นหา"
		},
		settings: {
			"title": "ตั้งค่า",
			"language": "ภาษา / Language"
		}
	};
});

app.filter("distance", function()
{
	return function(distance)
	{
		if(distance < 1000)
		{
			distance = Math.round(distance);
			distance = distance + " ม.";
		}
		else
		{
			distance = Math.round(distance / 10);
			distance = distance / 100;
			distance = distance + " กม.";
		}
		return distance;
	};
});

app.filter("remainingText", function()
{
	return function(round)
	{
		if(round.remaining_time < -30)
		{
			return "ไม่ทราบ";
		}
		else if(round.remaining_distance < 300 && round.remaining_time < 60)
		{
			return "กำลังจะถึง";
		}
		else
		{
			var time = Math.round(round.remaining_time / 60);
			return time + " นาที";
		}
	};
});

app.filter("totalTravelTime", function()
{
	return function(path)
	{
		var total = 0;
		for(i = 0; i < path.length; i++)
		{
			total += path[i].traveltime + path[i].waittime;
		}
		
		total = Math.ceil(total / 60);
		
		return total + " นาที";
	};
});

app.filter("pathArrivalTime", function()
{
	return function(path)
	{		
		return "ถึงประมาณ " + path[path.length-1].time;
	};
});

app.filter("connectionInfo", function()
{
	return function(node)
	{
		if(node != null)
		{
			var txt = "<br>";
			
			var traveltime = node.traveltime;
			traveltime = Math.ceil(traveltime / 60);
			
			if(node.route == null)
			{
				txt += "　<i class='map-icon map-icon-walking'></i> เดิน " + traveltime + " นาที";
			}
			else
			{			
				var waittime = node.waittime;
				waittime = Math.ceil(waittime / 60);
			
				txt += "　<span style='color:#" + node.routecolor + ";'><i class='fa fa-bus'></i><strong> " + node.routename + "</strong><br>";
				txt += "　　<small>รอรถ " + waittime + " นาที</small> /";
				txt += " <small>เดินทาง " + traveltime + " นาที</small></span><br>";
			}
			
			return txt;
		}
		return "";
	};
});
