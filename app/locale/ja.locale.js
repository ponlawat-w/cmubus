app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "ＣＭＵバス",
		home: {
			"search": "乗換案内",
			"viewroutes": "ルート一覧",
			"searchingNear": "周辺バス停の情報を読み込み中",
			"nearStops": "周辺バス停",
			"search": "乗換案内",
			"searchDetail": "バス停検索",
			"submit": "決定"
		},
		stop: {
			"route": "ルート",
			"busno": "バス番号",
			"eta": "時間",
			"place": "現在位置",
			"distance": "残りの距離",
			"timeleft": "到着時間",
			"arrivalTimetable": "到着時間",
			"passedTimetable": "到着済み",
			"timetable": "時刻表",
			"info": "情報",
			"firstRound": "始発予定",
			"lastRound": "終電予定",
			"waittingTime": "バス待ち時間",
			"fromHere": "ここから検索",
			"toHere": "ここまで検索",
			"viewMap": "地図で見る",
			"connections": "周辺"
		},
		pleaseWait: "しばらくお待ち下さい",
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
			"language": "言語 / Language",
			"report": {
				"title": "通報",
				"type": "問題の種類をお選びください。",
				"applicationUsage": "アプリの利用問題について",
				"incorrectData": "間違いのデータや誤報などについて",
				"mistranslation": "誤訳について",
				"bus": "バスの問題について（タイ語のみ）",
				"name": "氏名",
				"email": "メール",
				"message": "通報の内容をご入力ください…",
				"submit": "通報",
				"error": {
					"noInput": "全てを入力してください。"
				},
				"success": "問題を通報しました。ご協力ありがとうございます。"
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

app.filter("remainingTimeText", function()
{
	return function(round)
	{
		if((round.remaining_distance != null && round.remaining_distance < 300))
		{
			return "まもなく到着";
		}
		else if(round.remaining_time < -30)
		{
			return "情報無し";
		}
		else
		{
			var time = Math.ceil(round.remaining_time / 60);
		
			if(time >= 60)
			{
				time = Math.round(time / 60);
				
				return "あと" + time + "時間";
			}
			
			return "あと" + time + "分";
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
			
			return time + "時間";
		}
		
		return time + "分";
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

app.filter("busNumber", function()
{
	return function(busno)
	{		
		return "バス" + busno + "号";
	};
});

app.filter("sessionStartDateTime", function()
{
	return function(busno)
	{		
		return busno + "発";
	};
});

app.filter("resultNumber", function()
{
	return function(index)
	{
		index++;
		
		return "結果＃" + index;
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
