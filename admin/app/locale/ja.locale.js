app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "ＣＭＵバス",
		home: {
			"search": "乗換案内",
			"viewroutes": "全ルート一覧",
			"searchingNear": "周辺バス停の情報を読み込み中",
			"nearStops": "周辺バス停"
		},
		stop: {
			"route": "ルート",
			"busno": "バス号",
			"eta": "到着時間"
		},
		pleaseWait: "お待ち下さい",
		search: {
			"title": "乗換案内",
			"from": "出発",
			"to": "到着",
			"detail": "バス停、施設、ビル名等",
			"submit_btn": "検索",
			"searching": "検索中"
		},
		settings: {
			"title": "設定",
			"language": "言語 / Language"
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
			distance = distance + "㌔";
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
			return "情報無し";
		}
		else if(round.remaining_distance < 300 && round.remaining_time < 60)
		{
			return "まもなく到着";
		}
		else
		{
			var time = Math.round(round.remaining_time / 60);
			return time + "分後";
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
		
		return total + "分";
	};
});

app.filter("pathArrivalTime", function()
{
	return function(path)
	{		
		return "到着時間：" + path[path.length-1].time;
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
				txt += "　<i class='map-icon map-icon-walking'></i> 徒歩" + traveltime + "分";
			}
			else
			{			
				var waittime = node.waittime;
				waittime = Math.ceil(waittime / 60);
			
				txt += "　<span style='color:#" + node.routecolor + ";'><i class='fa fa-bus'></i><strong> " + node.routename + "</strong><br>";
				txt += "　　<small>" + waittime + "分バス待ち</small> /";
				txt += " <small>" + traveltime + "分乗車</small></span><br>";
			}
			
			return txt;
		}
		return "";
	};
});
