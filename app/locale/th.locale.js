app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
			search: "ค้นหาเส้นทางใน มช.",
			viewTimeTable: "ดูเวลารถ",
            searchingNear: "กำลังค้นหาข้อมูลป้ายใกล้เคียง",
			searchingNearError: {
				title: "ไม่สามารถค้นหาป้ายใกล้เคียงได้",
				message: "กรุณาตรวจสอบการตั้งค่าการใช้ตำแหน่งของอุปกรณ์ หรือในขณะนี้อุปกรณ์ไม่สามารถรับข้อมูลพิกัดที่แน่นอนได้"
			},
			nearStops: "ป้ายใกล้เคียง",
			searchDetail: "ค้นหาป้ายหยุดรถ สถานที่ ใน มช.",
			click2cTimeTable: "คลิกเพื่อดูตารางเวลา",
            evaluationSurvey: {
                title: "ประเมินแอปพลิเคชัน",
                message: "ขอความร่วมมือตอบแบบประเมินการใช้งานแอปฯ",
                evaluationButton: "ประเมิน",
                laterButton: "ถามทีหลัง",
                neverButton: "อย่าถามอีก"
            },
			recommendedPlaces: {
				title: "สถานที่แนะนำ"
			},
			useThisLanguage: ""
		},
		routes: {
			pleaseSelect: "กรุณาเลือกเส้นทาง"
		},
		route: {
			viewMap: "ดูเส้นทางบนแผนที่"
		},
		stops: {
			viewAll: "ดูจุดจอดรถทั้งหมด",
			title: "กรุณาเลือกป้ายที่ต้องการดูเวลารถ"
		},
		stop: {
			route: "เส้นทาง",
			busno: "เลขรถ",
			eta: "เวลา",
			place: "ตำแหน่งรถ",
			distance: "ระยะทาง",
			timeleft: "เวลา",
			arrivalTimetable: "รถที่จะถึง",
			passedTimetable: "รถที่เพิ่งผ่าน",
			timetable: "ตารางเวลา",
			info: "ข้อมูล",
			firstRound: "เวลารอบแรกโดยประมาณ",
			lastRound: "เวลารอบสุดท้ายโดยประมาณ",
			waittingTime: "เวลารอรถโดยประมาณ",
			fromHere: "ค้นหาเส้นทางจากที่นี่",
			toHere: "ค้นหาเส้นทางถึงที่นี่",
			viewMap: "ดูแผนที่",
			connections: "สถานที่ใกล้เคียง"
		},
		pleaseWait: "กรุณารอสักครู่",
		search: {
			title: "ค้นหาเส้นทาง",
			from: "จาก",
			to: "ถึง",
			detail: "ค้นหาสถานที่",
			submit_btn: "ค้นหา",
			searching: "กำลังค้นหา",
			searchingMore: "กำลังค้นหาเพิ่มเติม",
			edit: "แก้ไขการค้นหา",
			viewWalkRouteOnMap: "ดูเส้นทางเดินบนแผนที่",
			viewRouteInfo: "ดูข้อมูลเส้นทาง",
			viewTimetable: "ดูเวลารถ",
			noTimetableData: "ไม่มีข้อมูลเวลารถ"
		},
		settings: {
			title: "ตั้งค่า",
			language: "ภาษา / Language",
			report: {
				title: "รายงานปัญหา",
				type: "กรุณาเลือกชนิดปัญหา",
				applicationUsage: "การใช้งานแอปฯ",
				incorrectData: "ข้อมูลผิดพลาด",
				mistranslation: "การแปลไม่ถูกต้อง",
				bus: "ปัญหาเกี่ยวกับรถโดยสาร",
				name: "ชื่อ",
				email: "อีเมล",
				message: "กรอกข้อความที่นี่…",
				submit: "ส่ง",
				error: {
					noInput: "กรุณากรอกข้อมูลให้ครบ"
				},
				success: "ขอบคุณสำหรับข้อมูล ข้อความถูกส่งไปยังผู้ดูแลระบบแล้ว",
				optional: "ไม่บังคับ"
			}
		},
        menu: {
            home: "หน้าหลัก",
            searchPlace: "ค้นหาสถานที่",
            searchPath: "ค้นหาเส้นทาง",
            viewRoutes: "ดูเส้นทางทั้งหมด",
            viewBuses: "ดูรถทั้งหมด",
            evaluateApp: "ประเมินแอปพลิเคชัน",
            problemReport: "รายงานปัญหา",
            languageSettings: "ตั้งค่าภาษา (Language)",
            about: "เกี่ยวกับ"
        },
        evaluationSurvey: {
            title: "ประเมินแอปพลิเคชัน",
            role: "คุณคือ…",
            roles: {
                student: "นักศึกษามหาวิทยาลัยเชียงใหม่",
                staff: "อาจารย์ บุคลากร มหาวิทยาลัยเชียงใหม่",
                visitor: "แขก ผู้เยี่ยมชมมหาวิทยาลัยเชียงใหม่",
                other: "อื่น ๆ"
            },
            name: "ชื่อ",
            email: "อีเมล",
            usefulness: "คุณคิดว่าแอปพลิเคชันนี้มีประโยชน์มากเท่าใด",
            easyToUse: "คุณคิดว่าแอปพลิเคชันนี้ใช้งานง่ายหรือไม่",
            accuracy: "คุณพึงพอใจกับความแม่นยำของข้อมูลมากเท่าใด",
            performance: "คุณพึงพอใจกับประสิทธิภาพของแอปพลิเคชันมากเท่าใด",
            satisfaction: "ความพึงพอใจโดยรวม",
            comment: "ความคิดเห็นเพิ่มเติม",
            typeHere: "กรอกความคิดเห็นที่นี่",
            levels: [
                "",
                "น้อยที่สุด",
                "น้อย",
                "ปานกลาง",
                "มาก",
                "มากที่สุด"
            ],
            submit: "　ส่ง　",
            success: "ส่งข้อมูลแบบประเมินแล้ว ขอขอบพระคุณสำหรับความร่วมมือ"
        },
		buses: {
			title: "ดูรถทั้งหมด",
			busno: "เลขรถ",
			status: "สถานะ",
			offline: "ไม่มีข้อมูล"
		},
		about: {
			title: "เกี่ยวกับ CMU BUS",
			textJustify: "distribute",
			message: [
				"แอปพลิเคชัน CMU BUS เป็นส่วนหนึ่งของโครงงาน ระบบให้ข้อมูลเพื่อการใช้งานรถประจำทางในมหาวิทยาลัย (6/2559) ในกระบวนวิชา 261491 การสำรวจโครงงาน และ 261492 โครงงาน ภาควิชาวิศวกรรมคอมพิวเตอร์ คณะวิศวกรรมศาสตร์ มหาวิทยาลัยเชียงใหม่ ปีการศึกษา 2559 พัฒนาเพื่อเป็นกรณีศึกษาแอปพลิเคชันสำหรับกระบวนวิชาดังกล่าว และเพื่อเป็นแนวทางการพัฒนาการให้บริการระบบขนส่งมวลชน",
				"ข้อมูลเวลารถที่แสดงในแอปพลิเคชันนี้ (เวลาประมาณรถถึง เวลารอรถโดยประมาณ เป็นต้น) เกิดจากการคำนวณเพื่อประมาณเวลาโดยใช้ข้อมูลของวันก่อนหน้าที่ได้เก็บบันทึก ดังนั้น ข้อมูลเวลาประมาณรถถึง หรือ ข้อมูลเวลารอรถที่แสดงบนแอปพลิเคชัน เกิดจากการคำนวณอัตโนมัติโดยคอมพิวเตอร์ มิได้มาจากทางขนส่งมวลชนมหาวิทยาลัยเชียงใหม่ (ขส.มช.) โดยตรงแต่อย่างใด ทางผู้พัฒนาจึงขอไม่รับผิดชอบในความเสียหายที่เกิดขึ้นจากความผิดพลาดของข้อมูลใด ๆ",
				"แอปพลิเคชันนี้ มีการเรียกขอข้อมูลพิกัดตำแหน่งจากผู้ใช้งาน เพื่อใช้ในการคำนวณหาป้ายที่ใกล้ที่สุด ซึ่งระบบจะไม่มีการเก็บพิกัดของผู้ใช้งานโดยตรง แต่จะมีการเก็บบันทึกข้อมูลการค้นหาสถานที่ เพื่อใช้ในการอ้างอิงในการสรุปผลการใช้งานรถโดยสารของมหาวิทยาลัย"
			],
			index: {
				title: "โครงงาน CMUBUS.com",
				message: "แอปพลิเคชันนี้ พัฒนาขึ้นเพื่อสำหรับเป็นกรณีศึกษาสำหรับกระบวนวิชา 261492 ภาควิชาวิศวกรรมคอมพิวเตอร์ คณะวิศวกรรมศาสตร์ และเพื่อเป็นแนวทางการพัฒนาการให้บริการระบบขนส่งมวลชน",
				readMore: "อ่านเพิ่มเติม"
			}
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

app.filter("currentStopText", function()
{
    return function(round)
    {
        if((round.remaining_distance != null && round.remaining_distance < 300))
        {
            return "กำลังจะถึง";
        }
        else
        {
            return round.laststopname;
        }
    };
});

app.filter("timeText", function()
{
	return function(time)
	{
		if(time == null)
		{
			return "ไม่ทราบ";
		}


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
		return totalTravelTime(path) + " นาที";
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
				
				txt += "　　";
				
				if(waittime > 0)
				{
                    txt += "<small>รอรถ " + waittime + " นาที</small> / ";
                }
				txt += "<small>เดินทาง " + traveltime + " นาที</small></span><br>";
			}
			
			return txt;
		}
		return "";
	};
});