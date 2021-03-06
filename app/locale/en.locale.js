app.controller("localeController", function($scope)
{
	$scope.txt = {
		header: "CMU BUS",
		home: {
			search: "Search bus route in CMU",
            viewTimeTable: "View estimated bus timetables",
			searchingNear: "Searching near bus stops",
            searchingNearError: {
                title: "Unable to find near bus stops",
                message: "Please check your location settings. The device might currently not be able to obtain your certain position, or you are now too far from Chiang Mai University."
            },
			nearStops: "Near bus stops",
			searchDetail: "Search bus stops, places in CMU",
            click2cTimeTable: "Click to see timetable",
			evaluationSurvey: {
				title: "Do you have some time?",
				message: "Please help us evaluate this application",
				evaluationButton: "Evaluate",
				laterButton: "Later",
				neverButton: "Don't ask me again"
			},
            recommendedPlaces: {
                title: "Recommended Places"
            },
            useThisLanguage: "Use English",
            announcement: {
                title: "Website Shutdown Announcement",
                readMore: "Read more",
                messages: [
                    "　This site, cmubus.com, will be closing at 0:00 on 14 January, 2018.",
                    "　Alternatively, you can use the following sites to check Chiang Mai University bus location and timetables (all sites are in Thai language only): <a href='http://cmutransit.bda.co.th'>cmutransit.bda.co.th</a> or <a href='http://cmutransit.com/'>cmutransit.com</a>, or you can scan QR Code which is attached at bus stops to see timetable of the current bus stop. <a href='http://youtu.be/zxbxhWIz_AM'>Click here to watch video to learn more about using QR code.</a>",
                    "　Thank you for using cmubus.com."
                ]
            }
		},
        routes: {
            pleaseSelect: "Please select route",
            since: "Since",
            until: "Until"
        },
        route: {
            viewMap: "View this route on map"
        },
        stops: {
            viewAll: "View bus stops list",
            title: "Select a bus stop to see timetable"
        },
		stop: {
			route: "Route",
			busno: "Bus no.",
			eta: "Time",
			place: "Location",
			distance: "Distance",
			timeleft: "Time left",
			arrivalTimetable: "Estimated Arrival Time",
			passedTimetable: "Past Timetable",
            type: {
                passed: "",
                departed: "Departed",
                arrived: "Arrived"
            },
			timetable: "TIMETABLE",
			info: "INFORMATION",
			firstRound: "Estimated first bus",
			lastRound: "Estimated last bus",
			waittingTime: "Estimated waiting time",
			fromHere: "Search FROM here",
			toHere: "Search TO here",
			viewMap: "View map",
			connections: "Around here",
            viewMoreInfo: "View more"
		},
        stopStats: {
            today: 'Today',
            dayType: {
                weekday: 'Weekdays',
                weekend: 'Weekends/Holidays'
            }
        },
        session: {
            finished: "Finished"
        },
		pleaseWait: "Please wait",
		search: {
			title: "How to go",
			from: "From",
			to: "To",
			detail: "search place",
			submit_btn: "Search",
			searching: "Please wait",
            searchingMore: "Searching more",
            edit: "Edit search",
            viewWalkRouteOnMap: "View walking route on map",
            viewRouteInfo: "View route info",
            viewTimetable: "View timetable",
            noTimetableData: "No timetable data"
		},
		settings: {
			title: "Settings",
			language: "Language",
			report: {
				title: "Problem Report",
				type: "Problem type",
				applicationUsage: "Application Usage",
				incorrectData: "Incorrect Data",
				mistranslation: "Mistranslation",
				bus: "Bus problems (form is in Thai)",
				name: "Name",
				email: "E-mail",
				message: "Type your message here…",
				submit: "Send",
				error: {
					noInput: "Please type the form correctly"
				},
				success: "The problem has been reported to administrator. Thank you.",
                optional: "optional"
			}
		},
		menu: {
			home: "Home",
			searchPlace: "Search bus stop or places",
			searchPath: "Transfer information",
			viewRoutes: "View all routes",
			viewBuses: "View all buses",
			evaluateApp: "Evaluate application",
			problemReport: "Problem report",
			languageSettings: "Language settings",
            about: "About"
		},
        evaluationSurvey: {
			title: "Evaluation Survey",
            role: "You are…",
			roles: {
				student: "CMU student",
				staff: "CMU staff",
				visitor: "CMU guest or visitor",
				other: "Other"
			},
            name: "Name",
            email: "E-mail",
			usefulness: "Do you think this app is useful?",
			easyToUse: "Do you think this app is easy to use?",
			accuracy: "Are you satisfied by data accuracy in this app?",
			performance: "Are you satisfied by application performance?",
			satisfaction: "Total Satisfaction",
			comment: "Comment",
			typeHere: "Leave your comment here…",
			levels: [
				"",
                "Not at all",
                "No",
                "Normal",
                "Yes",
                "Very much"
            ],
			submit: "Send",
			success: "You answer has been sent. Thank you."
		},
        buses: {
            title: "All Buses",
            busno: "Bus no.",
            status: "Status",
            offline: "Offline"
        },
        about: {
            title: "About this app",
            textJustify: "auto",
            message: [
                "The application \"CMU BUS\" is a part of project \"University Bus Information System\" (6/2559) which is in 261491 Project Survey and 261492 Project course in Department of Computer Engineering, Faculty of Engineering, Chiang Mai University in academic year 2559.",
                "All information about time in this application (estimated arrival time, estimated waiting time) are calculated automatically by machine using recorded data in previous days. The mentioned data do not come from the university bus organization (ขส.มช.) directly. Therefore, the developer disclaims any damage that is caused by any incorrect data.",
				"This application requires user geolocation in order to calculate the near bus stops. However, user's latitude and longitude will not be collected. But user's search data will be collected for being reference of bus usage."
            ],
            index: {
                title: "CMUBUS.com Project",
                message: "This project is developed as a part of study in 261492 course, in Department of Computer Engineering, Faculty of Engineering and in order to be reference for university transportation system improvement.",
                readMore: "Read more"
            },
            lastUpdated: 'Last updated'
        },
        error: {
            title: "Error!",
            message: "An error occurred. Please retry your action again, or access another page with below links.",
            retry: "Reload this page"
        }
	};
});

var pageTitles = {
    header: "CMU BUS - ",
    home: 'Home',
    menu: 'Menu',
    about: 'About',
    buses: 'Buses',
    error: 'Error',
    evaluate: 'Evaluation Survey',
    language: 'Language Settings',
    report: 'Problem Report',
    route: 'Route Information',
    routes: 'Route List',
    search: 'Search Transfer Information',
    searchResult: 'Search Result',
    session: 'Bus Round Record',
    stopStats: 'Statistics Information',
    stops: 'Busstop List'
};

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

app.filter("currentStopText", function()
{
    return function(round)
    {
        if((round.remaining_distance != null && round.remaining_distance < 300))
        {
            return "arriving";
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
            return "N/A";
        }

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
					txt += "　<i class='map-icon map-icon-walking'></i> walk " + traveltime + " minute";
				}
				else
				{
					txt += "　<i class='map-icon map-icon-walking'></i> walk " + traveltime + " minutes";
				}
			}
			else
			{			
				var waittime = node.waittime;
				waittime = Math.ceil(waittime / 60);
			
				txt += "　<span style='color:#" + node.routecolor + ";'><i class='fa fa-bus'></i><strong> " + node.routename + "</strong><br>";

				txt += "　　";
				
				if(waittime > 0)
				{
                    if (waittime == 1) {
                        txt += "<small>wait " + waittime + " min</small> / ";
                    }
                    else {
                        txt += "<small>wait " + waittime + " mins</small> / ";
                    }
                }
				
				if(traveltime == 1)
				{
					txt += "<small>bus " + traveltime + " min</small></span><br>";
				}
				else
				{
					txt += "<small>bus " + traveltime + " mins</small></span><br>";
				}
			}
			
			return txt;
		}
		return "";
	};
});

app.filter('dateFormat', function()
{
	return function(timestamp)
	{
        var inputDate = new Date(timestamp * 1000);
        inputDate = inputDate.getTime() + (inputDate.getTimezoneOffset() * 60000);
        inputDate = new Date(inputDate + (3600000 * 7));

        var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

		var date = inputDate.getDate().toString();
		var month = months[inputDate.getMonth()];
		var year = inputDate.getFullYear().toString();

		return date + ' ' + month + ' ' + year;
	};
});