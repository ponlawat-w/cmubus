app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
			"search": "How to go",
			"viewroutes": "View all routes",
			"searchingNear": "Searching data around here",
			"nearStops": "Bus stops around here"
		},
		stop: {
			"route": "Route",
			"busno": "Bus no.",
			"eta": "Arrival time"
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
			"language": "Language"
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

app.filter("remainingText", function()
{
	return function(round)
	{
		if(round.remaining_time < -30)
		{
			return "N/A";
		}
		else if(round.remaining_distance < 300 && round.remaining_time < 60)
		{
			return "arriving";
		}
		else
		{
			var time = Math.round(round.remaining_time / 60);
			if(time == 1)
			{
				return time + " min left";
			}
			return time + " mins left";
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