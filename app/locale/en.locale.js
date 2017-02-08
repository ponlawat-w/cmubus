app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
			"search": "How to go",
			"viewroutes": "View all routes",
			"searchingNear": "Searching data around here",
			"nearStops": "Bus stops around here",
			"search": "Search",
			"searchDetail": "Search",
			"evaluationSurvey": {
				"title": "Do you have some time?",
				"message": "Please help us evaluate this application",
				"evaluationButton": "Evaluate",
				"laterButton": "Later",
				"neverButton": "Don't ask me again"
			}
		},
		stop: {
			"route": "Route",
			"busno": "Bus no.",
			"eta": "Time",
			"place": "Location",
			"distance": "Distance",
			"timeleft": "Time left",
			"arrivalTimetable": "Arrivals",
			"passedTimetable": "Departed",
			"timetable": "TIMETABLE",
			"info": "INFORMATION",
			"firstRound": "Estimated first bus",
			"lastRound": "Estimated last bus",
			"waittingTime": "Estimated waiting time",
			"fromHere": "Search FROM here",
			"toHere": "Search TO here",
			"viewMap": "View map",
			"connections": "Around here"
		},
		pleaseWait: "Please wait",
		search: {
			"title": "How to go",
			"from": "From",
			"to": "To",
			"detail": "search place",
			"submit_btn": "Search",
			"searching": "Please wait"
		},
		settings: {
			"title": "Settings",
			"language": "Language",
			"report": {
				"title": "Problem Report",
				"type": "Problem type",
				"applicationUsage": "Application Usage",
				"incorrectData": "Incorrect Data",
				"mistranslation": "Mistranslation",
				"bus": "Bus problems (form is in Thai)",
				"name": "Name",
				"email": "E-mail",
				"message": "Type your message here…",
				"submit": "Send",
				"error": {
					"noInput": "Please type the form correctly"
				},
				"success": "The problem has been reported to administrator. Thank you."
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
			"languageSettings": "Language settings"
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
			return "arriving";
		}
		else if(round.remaining_time < -30)
		{
			return "N/A";
		}
		else
		{
			var time = Math.ceil(round.remaining_time / 60);
					
			if(time >= 60)
			{
				time = Math.round(time / 60);
			
				if(time == 1)
				{
					return "in " + time + " hour";
				}
				return "in " + time + " hours";
			}
			
			if(time == 1)
			{
				return "in " + time + " min";
			}
			return "in " + time + " mins";
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
			
			if(time == 1)
			{
				return time + " hr";
			}
			else
			{
				return time + " hrs";
			}
		}
		
		if(time == 1)
		{
			return time + " min";
		}
		else
		{
			return time + " mins";
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
		
		if(total == 1)
		{
			return total + " minute";
		}
		else
		{
			return total + " minutes";
		}
	};
});

app.filter("pathArrivalTime", function()
{
	return function(path)
	{		
		return "ETA " + path[path.length-1].time;
	};
});

app.filter("busNumber", function()
{
	return function(busno)
	{		
		return "Bus Number #" + busno;
	};
});

app.filter("sessionStartDateTime", function()
{
	return function(busno)
	{		
		return "Departure: " + busno;
	};
});

app.filter("resultNumber", function()
{
	return function(index)
	{
		index++;
		
		return "Result #" + index;
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
				if(traveltime == 1)
				{
					txt += "　<i class='map-icon map-icon-walking'></i> walking " + traveltime + " minute";
				}
				else
				{
					txt += "　<i class='map-icon map-icon-walking'></i> walking " + traveltime + " minutes";
				}
			}
			else
			{			
				var waittime = node.waittime;
				waittime = Math.ceil(waittime / 60);
			
				txt += "　<span style='color:#" + node.routecolor + ";'><i class='fa fa-bus'></i><strong> " + node.routename + "</strong><br>";
				if(waittime == 1)
				{
					txt += "　　<small>wait " + waittime + " min</small> /";
				}
				else
				{
					txt += "　　<small>wait " + waittime + " mins</small> /";
				}
				
				if(traveltime == 1)
				{
					txt += " <small>bus " + traveltime + " min</small></span><br>";
				}
				else
				{
					txt += " <small>bus " + traveltime + " mins</small></span><br>";
				}
			}
			
			return txt;
		}
		return "";
	};
});