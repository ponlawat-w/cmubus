app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "ＣＭＵバス",
		home: {
			search: "チェンマイ大学のバス乗換案内",
            viewTimeTable: "予想時刻表を見る",
			searchingNear: "周辺バス停の情報を読み込み中",
            searchingNearError: {
                title: "周辺バス停の情報を読み込めません。",
                message: "只今ＧＰＳからの現在位置の情報を読み込めません、位置情報の設定を確認してください。又は、チェンマイ大学の近くにいません。"
            },
			nearStops: "周辺バス停",
			searchDetail: "バス停、施設、ビルの検索",
            click2cTimeTable: "時刻表を見る",
            evaluationSurvey: {
                title: "アプリ評価",
                message: "アプリ評価のご協力をお願いします。",
                evaluationButton: "　評価　",
                laterButton: "後で",
                neverButton: "今後、表示しない"
            },
            recommendedPlaces: {
                title: "おすすめスポット"
            },
            useThisLanguage: "日本語で利用します",
            welcome: {
                title: "２５６０年度、新学期歓迎！",
                messages: ["新入生の皆さん、チェンマイ大学へようこそ", "この下で学内の設置、ビルなどの情報を検索できます！↴"]
            }
		},
        routes: {
            pleaseSelect: "ルートを選択してください"
        },
        route: {
            viewMap: "ルートを地図で見る"
        },
        stops: {
            viewAll: "バス停一覧",
            title: "バス停の予想時刻表を見る"
        },
		stop: {
			route: "ルート",
			busno: "バス番号",
			eta: "時間",
			place: "現在位置",
			distance: "残りの距離",
			timeleft: "到着時刻",
			arrivalTimetable: "到着予想時刻",
			passedTimetable: "過去の到着時刻",
            type: {
                passed: "",
                departed: "出発",
                arrived: "終着"
            },
			timetable: "到着時刻",
			info: "情報",
			firstRound: "予想始発時刻",
			lastRound: "予想終発時刻",
			waittingTime: "予想バス待ち時間",
			fromHere: "ここから検索",
			toHere: "ここまで検索",
			viewMap: "地図で見る",
			connections: "周辺スポット"
		},
        session: {
            finished: "終点に到着"
        },
		pleaseWait: "しばらくお待ち下さい",
		search: {
			title: "乗換案内",
			from: "出発",
			to: "到着",
			detail: "バス停、施設、ビル名等",
			submit_btn: "検索",
			searching: "検索中",
            searchingMore: "さらに検索中",
            edit: "戻る",
            viewWalkRouteOnMap: "地図で見る",
            viewRouteInfo: "ルートの情報",
            viewTimetable: "時刻表",
            noTimetableData: "時刻情報なし"
		},
		settings: {
			title: "設定",
			language: "言語 / Language",
			report: {
				title: "問題報告",
				type: "問題の種類をお選びください。",
				applicationUsage: "アプリの利用問題について",
				incorrectData: "間違いのデータや誤報などについて",
				mistranslation: "誤訳について",
				bus: "バスの問題について（タイ語のみ）",
				name: "氏名",
				email: "メール",
				message: "報告の内容をご入力ください…",
				submit: "報告",
				error: {
					noInput: "全てを入力してください。"
				},
				success: "問題を報告しました。ご協力ありがとうございます。",
                optional: "任意"
			}
		},
        menu: {
            home: "ホーム",
            searchPlace: "バス停・場所検索",
            searchPath: "乗換案内",
            viewRoutes: "ルート一覧",
            viewBuses: "バス一覧",
            evaluateApp: "アプリ評価",
            problemReport: "問題報告",
            languageSettings: "言語設定 (Language)",
			about: "このアプリについて"
        },
        evaluationSurvey: {
            title: "アプリ評価",
            role: "あなたは…",
            roles: {
                student: "チェンマイ大学の大学生",
                staff: "チェンマイ大学の先生・スタッフ",
                visitor: "チェンマイ大学の客・訪問者",
                other: "その他"
            },
            name: "氏名",
            email: "メール",
            usefulness: "このアプリはどのくらい役に立ちますか。",
            easyToUse: "このアプリは使いやすいですか。",
            accuracy: "このアプリのデータ正確性がどの程度認められますか。",
            performance: "このアプリの性能はどの程度だと思いますか。",
            satisfaction: "総合評価",
            comment: "コメント",
            typeHere: "ご意見があれば、こちらにご入力ください…",
            levels: [
                "",
                "とても悪い",
                "悪い",
                "普通",
                "良い",
                "とても良い"
            ],
            submit: "　評価　",
            success: "ご協力ありがとうございます。"
        },
        buses: {
            title: "バス一覧",
            busno: "番号",
            status: "状態",
            offline: "情報無し"
        },
        about: {
            title: "このアプリについて",
            textJustify: "auto",
            message: [
                "当アプリケーション、「ＣＭＵ　ＢＵＳ」は、2559年度のチェンマイ大学工学部計算機工学科の261491 Project Surveyと261492 Projectの「University Bus Information System」（6/2559）というプロジェクトの一部です。",
                "当アプリケーションの時刻情報（予想到着時刻やバス待ち時間など）はチェンマイ大学のバス運行会社（ขส.มช.）からではなく、前日保存した時刻のデータを利用して機械で自動的に計算された情報です。そのため、当アプリケーションの開発者は予想時刻の間違いから生じた出来事について一切の責任を負いません。",
                "当アプリケーションでは、周辺バス停を探す為、ユーザーの位置情報が要求されていますが、位置情報が保存される事はありません。しかし、バスの利用の参考の為にユーザーの検索情報を保存させていただきます。"
            ],
            index: {
                title: "CMUBUS.comプロジェクト",
                message: "This project is developed as a part of study in 261492 course, in Department of Computer Engineering, Faculty of Engineering and in order to be reference for university transportation system improvement.",
                readMore: "続きを読む"
            }
        },
        error: {
            title: "エラーが発生しました。",
            message: "システムにエラーが発生しました。やり直し、又は下記のリンクで他のページをアクセスしてください。"
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

app.filter("currentStopText", function()
{
    return function(round)
    {
        if((round.remaining_distance != null && round.remaining_distance < 300))
        {
            return "まもなく到着";
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
            return "情報なし";
        }

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
		return "予想到着時刻：" + path[path.length-1].time;
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
		
		return "検索結果＃" + index;
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
				
				txt += "　　";
				
				if(waittime > 0)
				{
                    txt += "<small>" + waittime + "分バス待ち</small> / ";
                }
				txt += "<small>" + traveltime + "分乗車</small></span><br>";
			}
			
			return txt;
		}
		return "";
	};
});
