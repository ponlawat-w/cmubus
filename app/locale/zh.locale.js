app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
            "search": "Where you want to go in CMU?",
			"viewroutes": "看経路",
			"searchingNear": "附近公交车站加载中",
			"nearStops": "附近公交车站",
			"searchDetail": "公交车站検索",
            "click2cTimeTable": "Click to see timetable",
            "evaluationSurvey": {
                "title": "Do you have some time?",
                "message": "Please help us answer this survey",
                "evaluationButton": "Click Here",
                "laterButton": "Later",
                "neverButton": "Don't ask me again"
            },
            recommendedPlaces: {
                title: "Recommended Places"
            }
		},
		stop: {
			"route": "経路",
			"busno": "称号",
			"eta": "时间",
			"place": "现在的位置",
			"distance": "距离",
			"timeleft": "到达时间",
			"arrivalTimetable": "到达时间表",
			"passedTimetable": "到达了",
			"timetable": "时间表",
			"info": "信息",
			"firstRound": "预计首班车时间",
			"lastRound": "预计末班车时间",
			"waittingTime": "预计等待时间",
			"fromHere": "从这",
			"toHere": "到这",
			"viewMap": "看地图",
			"connections": "附近"
		},
		pleaseWait: "请稍等一下",
		search: {
			"title": "経路搜索",
			"from": "出发地",
			"to": "到达地",
			"detail": "公共汽车站、位置",
			"submit_btn": "搜索",
			"searching": "搜索中",
            "searchingMore": "Searching more",
            "edit": "Edit search"
		},
		settings: {
			"title": "设置",
			"language": "语言 / Language",
			"report": {
				"title": "问题报告",
				"type": "请选择问题的样",
				"applicationUsage": "应用使用的问题",
				"incorrectData": "错误的数据、误传",
				"mistranslation": "误译的问题",
				"bus": "公共汽车的问题（只有泰文）",
				"name": "名字",
				"email": "电子邮件",
				"message": "请输入问题报告的内容（请用英文或泰文写）",
				"submit": "报告",
				"error": {
					"noInput": "请输入问题报告的内容…"
				},
				"success": "报告问题了。谢谢。",
                "optional": "optional"
			}
		},
        menu: {
            "home": "Home",
            "searchPlace": "Search bus stop or places",
            "searchPath": "Transfer information",
            "viewRoutes": "View all routes",
            "viewBuses": "View all buses",
            "evaluateApp": "Evaluate application",
            "problemReport": "Problem report",
            "languageSettings": "Language settings",
            "about": "About"
        },
        evaluationSurvey: {
            "title": "Evaluation Survey",
            "role": "You are…",
            roles: {
                "student": "CMU student",
                "staff": "CMU staff",
                "visitor": "CMU guest or visitor",
                "other": "Other"
            },
            "name": "Name",
            "email": "E-mail",
            "usefulness": "Do you think this app is useful?",
            "easyToUse": "Do you think this app is easy to use?",
            "accuracy": "Are you satisfied by data accuracy in this app?",
            "performance": "Are you satisfied by application performance?",
            "satisfaction": "Total Satisfaction",
            "comment": "Comment",
            "typeHere": "Leave your comment here…",
            "levels": [
                "",
                "Not at all",
                "No",
                "Normal",
                "Yes",
                "Very much"
            ],
            "submit": "Send",
            "success": "You answer has been sent. Thank you."
        },
        buses: {
            "title": "All Buses",
            "busno": "Bus no.",
            "status": "Status",
            "offline": "Offline"
        },
        about: {
            title: "About this app",
            textJustify: "auto",
            message: [
                "The application \"CMU BUS\" is a part of project \"University Bus Information System\" (6/2559) which is in 261491 Project Survey and 261492 Project course in Department of Computer Engineering, Faculty of Engineering, Chiang Mai University in academic year 2559.",
                "All information about time in this application (estimated arrival time, estimated waiting time) are calculated automatically by machine using recorded data in previous days. The mentioned data do not come from the university bus organization (ขส.มช.) directly. Therefore, the developer disclaims any damage that is caused from incorrect data.",
				"This application requires user geolocation in order to calculate the near bus stops. However, user's latitude and longitude will not be collected. But user's search data will be collected for being reference of bus usage."
            ]
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
			distance = distance + "m";
		}
		else
		{
			distance = Math.round(distance / 10);
			distance = distance / 100;
			distance = distance + "km";
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
			return "即将到达";
		}
		else if(round.remaining_time < -30)
		{
			return "没有信息";
		}
		else
		{
			var time = Math.ceil(round.remaining_time / 60);
		
			if(time >= 60)
			{
				time = Math.round(time / 60);
				
				return "剩余" + time + "小时";
			}
			
			return "剩余" + time + "分钟";
		}
	};
});

app.filter("currentStopText", function()
{
    return function(round)
    {
        if((round.remaining_distance != null && round.remaining_distance < 300))
        {
            return "即将到达";
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
		time = Math.ceil(time / 60);
		
		if(time >= 60)
		{
			time = Math.round(time / 60);
			
			return time + "小时";
		}
		
		return time + "分钟";
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
		
		return total + "分钟";
	};
});

app.filter("pathArrivalTime", function()
{
	return function(path)
	{		
		return "到达时间" + path[path.length-1].time;
	};
});

app.filter("busNumber", function()
{
	return function(busno)
	{		
		return "汽车＃" + busno;
	};
});

app.filter("sessionStartDateTime", function()
{
	return function(busno)
	{		
		return "出发时间：" + busno;
	};
});

app.filter("resultNumber", function()
{
	return function(index)
	{
		index++;
		
		return "结果＃" + index;
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
				txt += "　<i class='map-icon map-icon-walking'></i> 步行" + traveltime + "分钟";
			}
			else
			{			
				var waittime = node.waittime;
				waittime = Math.ceil(waittime / 60);
			
				txt += "　<span style='color:#" + node.routecolor + ";'><i class='fa fa-bus'></i><strong> " + node.routename + "</strong><br>";
				txt += "　　<small>等待公交车" + waittime + "分钟</small> /";
				txt += " <small>乘车" + traveltime + "分钟</small></span><br>";
			}
			
			return txt;
		}
		return "";
	};
});
