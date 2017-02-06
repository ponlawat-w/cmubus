app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
			"search": "ค้นหาเส้นทาง",
			"viewroutes": "ดูเส้นทางทั้งหมด",
			"searchingNear": "กำลังค้นหาข้อมูลป้ายที่อยู่ใกล้",
			"nearStops": "ป้ายที่อยู่ใกล้",
			"searchDetail": "ค้นหาสถานที่",
            "evaluationSurvey": {
                "title": "ประเมินแอปพลิเคชัน",
                "message": "ขอความร่วมมือตอบแบบประเมินการใช้งานแอปฯ",
                "evaluationButton": "ประเมิน",
                "laterButton": "ถามทีหลัง",
                "neverButton": "อย่าถามอีก"
            }
		},
		stop: {
			"route": "เส้นทาง",
			"busno": "เลขรถ",
			"eta": "เวลา",
			"place": "ตำแหน่งรถ",
			"distance": "ระยะทาง",
			"timeleft": "เวลา",
			"arrivalTimetable": "รถที่จะถึง",
			"passedTimetable": "รถที่เพิ่งผ่าน",
			"timetable": "ตารางเวลา",
			"info": "ข้อมูล",
			"firstRound": "เวลารอบแรกโดยประมาณ",
			"lastRound": "เวลารอบสุดท้ายโดยประมาณ",
			"waittingTime": "เวลารอรถโดยประมาณ",
			"fromHere": "ค้นหาเส้นทางจากที่นี่",
			"toHere": "ค้นหาเส้นทางถึงที่นี่",
			"viewMap": "ดูแผนที่",
			"connections": "สถานที่ใกล้เคียง"
		},
		pleaseWait: "กรุณารอสักครู่",
		search: {
			"title": "ค้นหาเส้นทาง",
			"from": "จาก",
			"to": "ถึง",
			"detail": "ค้นหาสถานที่",
			"submit_btn": "ค้นหา",
			"searching": "กำลังค้นหา"
		},
		settings: {
			"title": "ตั้งค่า",
			"language": "ภาษา / Language",
			"report": {
				"title": "รายงานปัญหา",
				"type": "กรุณาเลือกชนิดปัญหา",
				"applicationUsage": "การใช้งานแอปฯ",
				"incorrectData": "ข้อมูลผิดพลาด",
				"mistranslation": "การแปลไม่ถูกต้อง",
				"bus": "ปัญหาเกี่ยวกับรถโดยสาร",
				"name": "ชื่อ",
				"email": "อีเมล",
				"message": "กรอกข้อความที่นี่…",
				"submit": "ส่ง",
				"error": {
					"noInput": "กรุณากรอกข้อมูลให้ครบ"
				},
				"success": "ขอบคุณสำหรับข้อมูล ข้อความถูกส่งไปยังผู้ดูแลระบบแล้ว"
			}
		},
        menu: {
            "home": "หน้าหลัก",
            "searchPlace": "ค้นหาสถานที่",
            "searchPath": "ค้นหาเส้นทาง",
            "viewRoutes": "ดูเส้นทางทั้งหมด",
            "viewBuses": "ดูรถทั้งหมด",
            "evaluateApp": "ประเมินแอปพลิเคชัน",
            "problemReport": "รายงานปัญหา",
            "languageSettings": "ตั้งค่าภาษา (Language)"
        }
	};
});

app.filter("distance", function()
{
	return function(distance)
	{
		if(distance == null)
		{
			return "-";
		}
		
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

app.filter("remainingTimeText", function()
{
	return function(round)
	{
		if((round.remaining_distance != null && round.remaining_distance < 300))
		{
			return "กำลังจะถึง";
		}
		else if(round.remaining_time < -30)
		{
			return "ไม่ทราบ";
		}
		else
		{
			var time = Math.ceil(round.remaining_time / 60);
		
			if(time >= 60)
			{
				time = Math.round(time / 60);
				
				return "อีก " + time + " ชั่วโมง";
			}
			
			return "อีก " + time + " นาที";
		}
	};
});

app.filter("timeText", function()
{
	return function(time)
	{
		time = Math.ceil(time / 60);
		
		if(time >= 60)
		{
			time = Math.round(time / 60);
			
			return time + " ชั่วโมง";
		}
		
		return time + " นาที";
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

app.filter("busNumber", function()
{
	return function(busno)
	{		
		return "รถหมายเลข " + busno;
	};
});

app.filter("sessionStartDateTime", function()
{
	return function(busno)
	{		
		return "รอบเวลา " + busno;
	};
});

app.filter("resultNumber", function()
{
	return function(index)
	{
		index++;
		
		return "เส้นทางที่  " + index;
	};
});

app.filter("connectionInfo", function()
{
	return function(node)
	{
		if(node != null)
		{
			var txt = "";
			
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
