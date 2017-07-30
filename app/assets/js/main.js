var app = angular.module("cmubus", ['ngRoute', 'ngAnimate']);

var appInterval;

app.config(function($routeProvider, $locationProvider, $httpProvider)
{	
	$httpProvider.defaults.cache = true;
	
	$routeProvider
		.when('/', {
			templateUrl: "pages/home.html",
			controller: "homeController"
		})
        .when('/menu', {
            templateUrl: "pages/menu.html",
            controller: "menuController"
        })
		.when('/search', {
			templateUrl: "pages/search.html",
			controller: "searchController"
		})
		.when('/searchfrom/:from_id/', {
			templateUrl: "pages/search.html",
			controller: "searchController"
		})
		.when('/searchto/:to_id', {
			templateUrl: "pages/search.html",
			controller: "searchController"
		})
        .when('/editsearch/:from_id/:to_id', {
            templateUrl: "pages/search.html",
            controller: "searchController"
        })
		.when('/search/:from_id/:to_id', {
			templateUrl: "pages/search_result.html",
			controller: "searchResultController"
		})
		.when('/stop/:id', {
			templateUrl: "pages/stop.html",
			controller: "stopController"
		})
		.when('/session/:id', {
			templateUrl: "pages/session.html",
			controller: "sessionController"
		})
		.when('/route/:id', {
			templateUrl: "pages/route.html",
			controller: "routeController"
		})
		.when('/route/:id/highlight/:from_id/:to_id', {
			templateUrl: "pages/route.html",
			controller: "routeController"
		})
		.when('/routes', {
			templateUrl: "pages/routes.html",
			controller: "routesController"
		})
        .when('/stops', {
            templateUrl: "pages/stops.html",
            controller: "stopsController"
        })
        .when('/buses', {
            templateUrl: "pages/buses.html",
            controller: "busesController"
        })
        .when('/evaluate', {
            templateUrl: "pages/evaluate.html",
            controller: "evaluateController"
        })
        .when('/report', {
            templateUrl: "pages/report.html",
            controller: "reportController"
        })
		.when('/language', {
			templateUrl: "pages/language_settings.html",
			controller: "languageSettingsController"
		})
        .when('/about', {
            templateUrl: "pages/about.html",
            controller: "aboutController"
        });

    $locationProvider.html5Mode(true);
});

app.directive('jpInput', ['$parse', function($parse)
{
	return {
		priority: 2,
		restrict: 'A',
		compile: function(element)
		{
			element.on('compositionstart', function(e)
			{
				e.stopImmediatePropagation();
			});
		}
	};
}]);

app.run(function($rootScope, $location, $anchorScroll)
{
	//when the route is changed scroll to the proper element.
	$rootScope.$on('$routeChangeSuccess', function(newRoute, oldRoute)
	{
		if(variables.gaUsing)
		{
			ga("send", "pageview", {page: $location.path()});
		}

		if($location.hash())
		{
			$anchorScroll();
		}
	});
});

app.controller("mainController", function($scope, $location, $http, $timeout, $interval)
{
	$interval.cancel(appInterval);
	$scope.$location = $location;

	$scope.showBottomNavbar = false;
	$scope.bottomNavbar = "";
	$scope.settingToThai = false;

	if(getCookieValue("survey_timer") == "" && getCookieValue("survey_timer") != "never")
    {
        setCookie("survey_timer", 0, 5184000000);
    }

    var surveyInterval = $interval(function()
    {
        if(getCookieValue("survey_timer") == "never")
        {
            $interval.cancel(surveyInterval);
        }
        else
        {
            var currentTime = 0;
            if(getCookieValue("survey_timer") == "")
            {
                currentTime = 0;
            }
            else
            {
                currentTime = parseInt(getCookieValue("survey_timer"));
            }

            var newTimeValue = currentTime + 1;
            setCookie("survey_timer", newTimeValue, 5184000000);

            if(newTimeValue > 60 && $scope.bottomNavbar != "suggestSurvey")
            {
                $scope.showBottomNavbar = true;
                $scope.bottomNavbar = "suggestSurvey";
            }
        }
    }, 5000);

	$timeout(function()
    {
        if(getCookieValue("user_language") == "" && language != "th")
        {
            $scope.showBottomNavbar = true;
            $scope.bottomNavbar = "suggestThai";
        }
        else if(getCookieValue("showAppInfo") != "yes")
		{
			$scope.showAppInfo();
		}
    }, 500);

    $scope.setToThai = function()
	{
		$scope.settingToThai = true;

        $http.get("data/set_language.php?id=th").then(function(response)
        {
        	setCookie("user_language", "th", 5184000000);
            window.location.reload();
        }, function(response)
        {
        });
	};

    $scope.neverAskMeSurvey = function()
    {
        $scope.closeSuggestion();

        setCookie("survey_timer", "never", 5184000000);
    };

    $scope.closeSuggestion = function()
	{
	    if($scope.bottomNavbar == "suggestSurvey")
        {
            setCookie("survey_timer", 0, 5184000000);
        }

        $scope.showBottomNavbar = false;

		setCookie("user_language", language, 5184000000);
        $timeout(function()
        {
            $scope.bottomNavbar = "";
            $scope.showAppInfo();
        }, 1000);
	};

    $scope.showAppInfo = function()
	{
		if(getCookieValue("showAppInfo") != "yes")
		{
			$scope.bottomNavbar = "appInfo";
			$scope.showBottomNavbar = true;

			setCookie("showAppInfo", "yes", 5184000000);
		}
	}
});

app.controller("homeController", function($scope, $http, $location, $anchorScroll, $interval)
{
    $interval.cancel(appInterval);

    $scope.requesting = false;
    $scope.loading = true;
    $scope.nearStops = [];
    $scope.nearStopsMore = [];
    $scope.recommendedPlaces = [];
    $scope.routes = [];

    $http.get("data/recommended_places.php").then(function(response)
    {
        $scope.recommendedPlaces = response.data;

    }, function(response)
    {
    });

    $http.get("data/routes.php").then(function(response)
	{
		$scope.routes = response.data;
	}, function(response)
	{
	});

    $scope.loadData = function()
    {
        $scope.loading = true;
        $scope.nearStops = [];
        $scope.nearStopsMore = [];

		$scope.lat = null;
		$scope.lon = null;
		
		if (navigator.geolocation)
		{
			navigator.geolocation.watchPosition(function(position)
			{
				$scope.$apply(function()
				{
                    $scope.lat = position.coords.latitude;
                    $scope.lon = position.coords.longitude;

                    $scope.loadNearStops();
				});
			}, function()
			{
                $scope.loading = false;
			});
		}
		else
		{
            $scope.loading = false;
		}
	};

	$scope.loadNearStops = function()
	{
        if($scope.requesting == false)
        {
            $scope.requesting = true;

            var url = "data/findnearstop.php?temp=" + Date.now() + "&lat=" + $scope.lat + "&lon=" + $scope.lon;

            $http.get(url + "&stoponly=true&limit=3&timetable=true").then(function(response)
            {
                $scope.nearStops = response.data;

                if($scope.nearStops.length > 0)
				{
					$http.get(url + "&stoponly=true&limit=10&timetable=false").then(function(response)
					{
						$scope.nearStopsMore = response.data;

						$scope.loading = false;
						$scope.requesting = false;

					}, function(response)
					{
						$scope.loading = false;
					});
				}
				else
				{
					$scope.loading = false;
				}

            }, function(response)
            {
                $scope.loading = false;
            });
        }
	};
	
	$scope.goSession = function(session)
	{
		if(session != null)
		{
			$location.path("session/" + session);
		}
	};
	
	$scope.goStop = function(stopid)
	{
		$location.path("stop/" + stopid);
	};

	$scope.searchTo = function(stopid)
	{
		$location.path("searchto/" + stopid);
	};
	
	$scope.goRoute = function(routeid)
	{
		$location.path("route/" + routeid);
	};

	appInterval = $interval(function() { $scope.loadNearStops(); }, 7000);

	// SEARCH
	
	$scope.autocompletes = [];
	$scope.keyword = "";
	$scope.clicked = false;
	$scope.search_id = null;
	
	$scope.scrollTo = function(id)
	{
		var old = $location.hash();
		$location.hash(id);
		$anchorScroll();
		$location.hash(old);
	};
	
	$scope.$watch("keyword", function()
	{
		if($scope.keyword == "")
		{
			$scope.autocompletes = [];
		}
		else
		{
			$http.get("data/search.php?keyword=" + $scope.keyword).then(function(response)
			{				
				if($scope.clicked == true)
				{
					$scope.clicked = false;
				}
				else
				{
					$scope.autocompletes = response.data;
					$scope.from_id = null;
				}
				
				if($scope.autocompletes.length == 1)
				{
					$scope.search_id = $scope.autocompletes[0].id;
				}
			}, function(response)
			{
				
			});
		}
	});
	
	$scope.setSearch = function(id)
	{
		$scope.search_id = id;
		
		for(i = 0; i < $scope.autocompletes.length; i++)
		{
			if($scope.autocompletes[i].id == id)
			{
				$scope.keyword = $scope.autocompletes[i].name;
				break;
			}
		}
		
		$scope.clicked = true;
		$scope.autocompletes = [];
	};
	
	$scope.searchSubmit = function()
	{
		$location.path("stop/" + $scope.search_id);
	};
	
	// END SEARCH
	
	$scope.loadData();
});

app.controller("menuController", function($scope, $interval)
{
	$interval.cancel(appInterval);
});

app.controller("searchController", function($scope, $http, $location, $anchorScroll, $routeParams, $interval)
{
    $interval.cancel(appInterval);
	$scope.loading = false;
	
	$scope.clicked = false;
	$scope.from_id = null;
	$scope.from_location = false;
	$scope.to_id = null;
	$scope.to_location = false;

	$scope.usePosition = false;
	$scope.locationError = false;
	$scope.currentLat = null;
	$scope.currentLon = null;
	
	$scope.from_autocompletes = [];
	$scope.to_autocompletes = [];

    if(navigator.geolocation)
    {
        navigator.geolocation.getCurrentPosition(function(position)
        {
            $scope.$apply(function() {
                $scope.usePosition = true;
                $scope.currentLat = position.coords.latitude;
                $scope.currentLon = position.coords.longitude;
            });
        }, function()
        {
            $scope.locationError = true;
        });
    }
	
	$scope.scrollTo = function(id)
	{
		var old = $location.hash();
		$location.hash(id);
		$anchorScroll();
		$location.hash(old);
	};
	
	if($routeParams.from_id != null)
	{
		$scope.loading = true;
		$http.get("data/stop.php?id=" + $routeParams.from_id).then(function(response)
		{
			$scope.clicked = true;
			$scope.from_keyword = response.data.name;
			$scope.from_id = $routeParams.from_id;
			$scope.loading = false;
			$scope.from_autocompletes = [];
		}, function(response)
		{
		});
	}
	
	if($routeParams.to_id != null)
	{
		$scope.loading = true;
		$http.get("data/stop.php?id=" + $routeParams.to_id).then(function(response)
		{
			$scope.clicked = true;
			$scope.to_keyword = response.data.name;
			$scope.to_id = $routeParams.to_id;
			$scope.loading = false;
			$scope.to_autocompletes = [];
		}, function(response)
		{
		});
	}
	
	$scope.switchFromTo = function()
	{
		var temp_from_id = $scope.from_id;
		var temp_from_keyword = $scope.from_keyword;
		
		$scope.from_id = $scope.to_id;
		$scope.from_keyword = $scope.to_keyword;
		
		$scope.to_id = temp_from_id;
		$scope.to_keyword = temp_from_keyword;
		
		$scope.from_autocompletes = [];
		$scope.to_autocompletes = [];
	};
	
	// FROM
	$scope.from_change = function()
	{
		if($scope.from_keyword == "")
		{
			$scope.from_autocompletes = [];
		}
		else
		{
			$http.get("data/search.php?keyword=" + $scope.from_keyword).then(function(response)
			{				
				if($scope.clicked == true)
				{
					$scope.clicked = false;
				}
				else
				{
					$scope.from_autocompletes = response.data;
					$scope.from_location = false;
					$scope.from_id = null;
				}
				
				if($scope.from_autocompletes.length == 1)
				{
					$scope.from_id = $scope.from_autocompletes[0].id;
				}
			}, function(response)
			{
				
			});
		}
	};
	
	$scope.setfrom = function(id)
	{
		$scope.from_id = id;
		
		for(i = 0; i < $scope.from_autocompletes.length; i++)
		{
			if($scope.from_autocompletes[i].id == id)
			{
				$scope.from_keyword = $scope.from_autocompletes[i].name;
				break;
			}
		}
		
		$scope.clicked = true;
		$scope.from_autocompletes = [];
	};
	
	$scope.clear_from = function()
	{
		$scope.from_keyword = "";
		$scope.from_location = false;
		$scope.from_id = null;
	};
	
	$scope.location_from = function()
	{
		$scope.loading = true;

        var url = "data/findnearstop.php?lat=" + $scope.currentLat + "&lon=" + $scope.currentLon + "&stoponly=false&limit=1";

        $http.get(url).then(function(response)
        {
            $scope.loading = false;
            if(response.data.length == 0)
            {
                $scope.locationError = true;
            }
            else
            {
                $scope.locationError = false;
                $scope.clicked = true;
                $scope.from_location = true;
                $scope.from_id = response.data[0].id;
                $scope.from_keyword = response.data[0].name;
            }
        }, function(response)
        {
            $scope.loading = false;
        });
	};
	// END FROM
	
	// TO
	$scope.to_change = function()
	{
		if($scope.to_keyword == "")
		{
			$scope.to_autocompletes = [];
		}
		else
		{
			$http.get("data/search.php?keyword=" + $scope.to_keyword).then(function(response)
			{
				
				if($scope.clicked == true)
				{
					$scope.clicked = false;
				}
				else
				{
					$scope.to_autocompletes = response.data;
					$scope.to_location = false;
					$scope.to_id = null;
				}
				
				if($scope.to_autocompletes.length == 1)
				{
					$scope.to_id = $scope.to_autocompletes[0].id;
				}
			}, function(response)
			{
				
			});
		}
	};
	
	$scope.clear = function()
	{
		$scope.from_autocompletes = [];
		$scope.to_autocompletes = [];
	};
	
	$scope.setto = function(id)
	{
		$scope.to_id = id;
		
		for(i = 0; i < $scope.to_autocompletes.length; i++)
		{
			if($scope.to_autocompletes[i].id == id)
			{
				$scope.to_keyword = $scope.to_autocompletes[i].name;
				break;
			}
		}
		
		$scope.clicked = true;
		$scope.to_autocompletes = [];
	};
	
	$scope.clear_to = function()
	{
		$scope.to_keyword = "";
		$scope.to_location = false;
		$scope.to_id = null;
	};
	
	$scope.location_to = function()
	{
		$scope.loading = true;

        var url = "data/findnearstop.php?lat=" + $scope.currentLat + "&lon=" + $scope.currentLon + "&stoponly=false&limit=1";

        $http.get(url).then(function(response)
        {
            $scope.loading = false;
            if(response.data.length == 0)
            {
                $scope.locationError = true;
            }
            else
            {
                $scope.locationError = false;
                $scope.clicked = true;
                $scope.to_location = true;
                $scope.to_id = response.data[0].id;
                $scope.to_keyword = response.data[0].name;
            }
        }, function(response)
        {
            $scope.loading = false;
        });
	};
	// END TO
	
	$scope.search = function()
	{
		if($scope.from_id != null && $scope.to_id != null && $scope.from_id != $scope.to_id)
		{
			$location.path("/search/" + $scope.from_id + "/" + $scope.to_id);
		}
	};
	
	$scope.goStop = function(id)
	{
		$location.path("/stop/" + id);
	};
});

app.controller("searchResultController", function($scope, $routeParams, $http, $location, $interval)
{
    $interval.cancel(appInterval);

    $scope.info = {
        fromName: "",
        toName: ""
    };
    $scope.paths = [];

    $scope.from_id = $routeParams.from_id;
    $scope.to_id = $routeParams.to_id;

    $scope.showedInfo = {
        pathIndex: null,
        sequenceIndex: null
    };

    $scope.showLoading = {
        pathIndex: null,
        sequenceIndex: null
    };

    $scope.timetable = {
        pathIndex: null,
        sequenceIndex: null,
        estimatedTime: []
    };

    $scope.finished = false;
    $scope.searchingMore = false;

    $scope.showInfo = function (pathIndex, sequenceIndex) {
        if ($scope.showedInfo.pathIndex == pathIndex && $scope.showedInfo.sequenceIndex == sequenceIndex) {
            $scope.showedInfo.pathIndex = null;
            $scope.showedInfo.sequenceIndex = null;
        }
        else {
            $scope.showedInfo.pathIndex = pathIndex;
            $scope.showedInfo.sequenceIndex = sequenceIndex;
        }
    };

    $scope.loadTimetable = function (routeID, stopID, pathIndex, sequenceIndex) {
        $scope.showLoading.pathIndex = pathIndex;
        $scope.showLoading.sequenceIndex = sequenceIndex;

        $scope.timetable.estimatedTime = [];
        $scope.timetable.pathIndex = pathIndex;
        $scope.timetable.sequenceIndex = sequenceIndex;

        $http.get("data/route.php?id=" + routeID).then(function(response)
		{
			if(response.data.path[0].stop == stopID)
			{
                $scope.showLoading.pathIndex = null;
                $scope.showLoading.sequenceIndex = null;
                $scope.showedInfo.pathIndex = null;
                $scope.showedInfo.sequenceIndex = null;
			}
			else
			{
                $http.get("data/timetable.php?stopid=" + stopID + "&passed=false&temp=" + Date.now()).then(function (response) {
                    $scope.showLoading.pathIndex = null;
                    $scope.showLoading.sequenceIndex = null;

                    var j;
                    j = 0;
                    for (i = 0; i < response.data.arrival_timetable.length; i++) {
                        if (response.data.arrival_timetable[i].route == routeID) {
                            $scope.timetable.estimatedTime[j] = response.data.arrival_timetable[i];

                            j++;
                        }
                    }

                    $scope.showedInfo.pathIndex = null;
                    $scope.showedInfo.sequenceIndex = null;
                }, function (response) {
                    $scope.showLoading.pathIndex = null;
                    $scope.showLoading.sequenceIndex = null;
                });
            }

		}, function(response)
		{

		});
    };

    $scope.goSession = function (sessionID)
	{
		if(sessionID > 0)
		{
			$location.path("session/" + sessionID);
		}
	};

	$scope.switchFromTo = function()
	{
		$location.path("search/" + $scope.to_id + "/" + $scope.from_id);
	};
	
	$http.get("data/stop.php?id=" + $scope.from_id).then(function(response)
	{
		$scope.info.fromName = response.data.name;
	
		$http.get("data/stop.php?id=" + $scope.to_id).then(function(response)
		{
			$scope.info.toName = response.data.name;
			$http.get("data/findpath.php?from=" + $scope.from_id + "&to=" + $scope.to_id + "&limit=1&quick=false").then(function(response)
			{
				$scope.finished = true;
				$scope.paths = response.data;

				$scope.searchingMore = true;

                $http.get("data/findpath.php?from=" + $scope.from_id + "&to=" + $scope.to_id + "&limit=3&quick=false").then(function(response)
				{
					$scope.searchingMore = false;

					//$scope.paths = response.data;

					for(i = $scope.paths.length; i < response.data.length; i++)
					{
						$scope.paths.push(response.data[i]);
					}

					//$scope.paths.sort(function(a, b)
					//{
					//	return totalTravelTime(a).localeCompare(totalTravelTime(b));
					//});

				}, function(response) { });
			}, function(reponse) {  });			
		}, function(response) {});
	}, function(response) {});

	$scope.goStop = function(id)
	{
		$location.path("stop/" + id);
	};

	$scope.editSearch = function()
	{
		$location.path("editsearch/" + $scope.from_id + "/" + $scope.to_id);
	};
	
	$scope.goHow = function(route_id, from_id, to_id, from_location, to_location)
	{
		if(route_id != null)
		{
			$location.path("route/" + route_id + "/highlight/" + from_id + "/" + to_id);
		}
		else if(from_location != null && to_location != null)
		{
			window.location = "http://maps.apple.com/?saddr=" + from_location.lat + "," + from_location.lon + "&daddr=" + to_location.lat + "," + to_location.lon + "&dirflg=w";
		}
	};

	$scope.openMap = function(stopLocation)
	{
        window.location = "https://maps.apple.com/?q=" + stopLocation.lat + "," + stopLocation.lon;
	};
});

app.controller("stopController", function($scope, $http, $routeParams, $location, $interval)
{
    $interval.cancel(appInterval);

	$scope.id = $routeParams.id;
	$scope.loading = true;
	$scope.timetableLoading = true;
	$scope.infoLoading = false;
	$scope.infoLoaded = false;
	
	$scope.busstop = null;

	$scope.requesting = false;
	
	$scope.timetableMode = "timeLeft";
	$scope.view = "timetable";
	
	$scope.stopTimetable = [];
	$scope.stopInfo = {};
	
	$scope.loadTimetable = function()
	{
		if($scope.requesting == false)
		{
			$scope.requesting = true;

			$http.get("data/timetable.php?stopid=" + $scope.id + "&passed=true&temp=" + Date.now()).then(function(response)
			{
				$scope.requesting = false;
				$scope.timetableLoading = false;
				$scope.stopTimetable = response.data.arrival_timetable;
				$scope.passedTimetable = response.data.passed_timetable;

			}, function(response)
			{
				$scope.timetableLoading = false;
			});
		}
	};
	
	$scope.loadInfo = function()
	{
		if($scope.infoLoaded == false)
		{
			$scope.infoLoading = true;
					
			$http.get("data/stop_info.php?id=" + $scope.id).then(function(response)
			{
				$scope.infoLoaded = true;
				$scope.infoLoading = false;
				$scope.stopGeneralInfo = response.data;				
			}, function(response)
			{
				$scope.timetableLoading = false;			
			});
		}
	};
	
	$scope.goStop = function(id)
	{
		$location.path("stop/" + id);
	};
	
	$scope.goSession = function(id)
	{
		if(id != null)
		{
			$location.path("session/" + id);
		}
	};
	
	$scope.goRoute = function(id)
	{
		$location.path("route/" + id);
	};
	
	$scope.goSearch = function(mode, id)
	{
		$location.path("search" + mode + "/" + id);
	};
	
	$scope.openMap = function()
	{
		window.location = "https://maps.apple.com/?q=" + $scope.stopInfo.location.lat + "," + $scope.stopInfo.location.lon;
	};
	
	$http.get("data/stop.php?id=" + $scope.id).then(function(response)
	{
		$scope.stopInfo = response.data;
		
		if($scope.stopInfo.busstop == 1)
		{
			$scope.busstop = true;
			$scope.loadTimetable();
			appInterval = $interval(function() { $scope.loadTimetable(); }, 3000);
		}
		else
		{
			$scope.busstop = false;
			$scope.view = "info";
			
			$scope.loadInfo();
		}
		
		$scope.loading = false;
	}, function(response)
	{
		
	});
});

app.controller("sessionController", function($scope, $http, $routeParams, $location, $interval)
{
    $interval.cancel(appInterval);

	$scope.sessionID = $routeParams.id;
	
	$scope.loading = true;

	$scope.requesting = false;
	
	$scope.sessionInfo = {online: true};
	
	$scope.loadSessionInfo = function()
	{
		if($scope.sessionInfo.online == true && $scope.requesting == false)
		{
			$scope.requesting = true;

			$http.get("data/session.php?id=" + $scope.sessionID + "&temp=" + Date.now()).then(function(response)
			{
				$scope.sessionInfo = response.data;
				$scope.loading = false;
				$scope.requesting = false;
				
				if($scope.sessionInfo.online == false)
				{
					$interval.cancel(appInterval);
				}
			}, function(response)
			{
			});
		}
	};
	
	$scope.goStop = function(id)
	{
		$location.path("stop/" + id);
	};
	
	$scope.goRoute = function(id)
	{
		$location.path("route/" + id);
	};

    $scope.loadSessionInfo();
    appInterval = $interval(function() { $scope.loadSessionInfo(); }, 3000);
});

app.controller("routesController", function($scope, $http, $location, $interval)
{
    $interval.cancel(appInterval);

	$scope.routes = [];
	
	$scope.loading = true;
	
	$http.get("data/routes.php").then(function(response)
	{
		$scope.routes = response.data;
		$scope.loading = false;
	}, function(response)
	{
	});
	
	$scope.goRoute = function(id)
	{
		$location.path("route/" + id);
	};
});

app.controller("stopsController", function($scope, $http, $location, $anchorScroll, $interval)
{
    $interval.cancel(appInterval);

    $scope.allStops = [];
    $scope.keyword = "";

    $scope.loading = true;

    $scope.scrollTo = function(id)
    {
        var old = $location.hash();
        $location.hash(id);
        $anchorScroll();
        $location.hash(old);
    };

    $scope.$watch("keyword", function()
    {
        if($scope.keyword == "")
        {
            for(i = 0; i < $scope.allStops.length; i++)
            {
                $scope.allStops[i].show = true;
            }
        }
        else
        {
            $http.get("data/search.php?keyword=" + $scope.keyword).then(function(response)
            {
                for(i = 0; i < $scope.allStops.length; i++)
                {
                    $scope.allStops[i].show = false;
                }

                for(i = 0; i < response.data.length; i++)
                {
                    for(j = 0; j < $scope.allStops.length; j++)
                    {
                        if(response.data[i].id == $scope.allStops[j].id)
                        {
                            $scope.allStops[j].show = true;
                        }
                    }
                }
            }, function(response)
            {
            });
        }
    });

    $http.get("data/stops.php").then(function(response)
    {
        $scope.allStops = response.data;

        for(i = 0; i < $scope.allStops.length; i++)
        {
            $scope.allStops[i].show = true;
        }

        $scope.loading = false;
    }, function(response)
    {

    });
});

app.controller("routeController", function($scope, $http, $location, $routeParams, $interval)
{
    $interval.cancel(appInterval);

	$scope.route = {};
	
	$scope.loading = true;
	
	$scope.highlightIndex = {
		from: -1,
		to: -1
	};
	
	$http.get("data/route.php?id=" + $routeParams.id).then(function(response)
	{
		$scope.route = response.data;
		
		if($routeParams.from_id != null && $routeParams.to_id != null)
		{
			for(i = 0; i < $scope.route.path.length; i++)
			{
				if($scope.route.path[i].stop == $routeParams.from_id)
				{
					$scope.highlightIndex.from = i;
					continue;
				}				
				if($scope.highlightIndex.from > -1 && $scope.route.path[i].stop == $routeParams.to_id)
				{
					$scope.highlightIndex.to = i;
					break;
				}
			}
		}
		else
		{		
			$scope.highlightIndex.from = 0;
			$scope.highlightIndex.to = $scope.route.path.length - 1;
		}
		
		$scope.loading = false;
	}, function(response)
	{
	});
	
	$scope.goStop = function(id)
	{
		$location.path("stop/" + id);
	};
	
	$scope.goRoute = function()
	{
		$location.path("route/" + $routeParams.id);
	};
});

app.controller("busesController", function($scope, $http, $interval, $location)
{
	$interval.cancel(appInterval);

	$scope.requesting = false;
	$scope.loading = true;
    $scope.busesData = [];

	$scope.loadBusData = function()
	{
		if($scope.requesting == false)
		{
			$scope.requesting = true;

			$http.get("data/buses.php?temp=" + Date.now()).then(function(response)
			{
				$scope.loading = false;
				$scope.requesting = false;

                $scope.busesData = response.data;

			}, function(response)
			{
			});
		}
	};

	$scope.goSession = function(sessionID)
	{
		if(sessionID > 0)
		{
			$location.path("session/" + sessionID);
		}
	};

	$scope.loadBusData();
    appInterval = $interval(function() { $scope.loadBusData(); }, 3000);
});

app.controller("evaluateController", function($scope, $http, $interval)
{
	$interval.cancel(appInterval);

	setCookie("survey_timer", "never", 5184000000);

	$scope.surveySendStatus = 0;

	$scope.surveyFormData = {
		role: "",
		name: "",
		email: "",
		usefulness: 0,
		easyToUse: 0,
		accuracy: 0,
		performance: 0,
		satisfaction: 0,
		comment: ""
	};

	$scope.sendData = function()
	{
        $scope.surveySendStatus = 1;

        $http.post("data/send_survey.php", $scope.surveyFormData).then(function(response)
        {
            $scope.surveySendStatus = 2;
        }, function(response)
        {
        });
	};
});

app.controller("reportController", function($scope, $http, $interval)
{
    $interval.cancel(appInterval);

    // REPORT

    $scope.reportSendStatus = 0;

    $scope.report = {
        "type": "",
        "name": "",
        "email": "",
        "message": ""
    };

    $scope.reportSend = function()
    {
        $scope.reportSendStatus = 1;

        $http.post("data/send_report.php", $scope.report).then(function(response)
        {
            $scope.reportSendStatus = 2;
        }, function(response)
        {
        });
    };
});

app.controller("aboutController", function($scope, $interval)
{
    $interval.cancel(appInterval);
});

app.controller("languageSettingsController", function($scope, $http, $location, $interval)
{
	$interval.cancel(appInterval);

	// LANGUAGES
	
	$scope.languages = [];
	
	$scope.changeLanguage = function(newLanguageID)
	{
		$http.get("data/set_language.php?id=" + newLanguageID).then(function(response)
		{
			setCookie("user_language", newLanguageID, 5184000000);
			window.location.reload();
		}, function(reponse)
		{			
		});
	};
	
	$http.get("data/languages_list.php").then(function(response)
	{
		$scope.languages = response.data;
	}, function(response)
	{
		
	});
});

app.filter('trustedHTML', function($sce)
{ 
    return $sce.trustAsHtml; 
});

app.filter("timetableTimeClass", function()
{
	return function(session)
	{
		if(session == null)
		{
			return "time time-estimated";
		}
		else
		{
			return "time time-default";
		}
	};
});

app.filter("busNumberText", function()
{
	return function(busno)
	{
		if(busno == null)
		{
			return "-";
		}
		else
		{
			return busno;
		}
	};
});

app.filter("arrivalClassHandler", function()
{
	return function(round)
	{
		if((round.remaining_distance != null && round.remaining_distance < 300))
		{
			return "bg-success arriving";
		}
		
		return "";
	};
});

app.filter("busDataColor", function()
{
    return function(busData)
    {
        if(busData.session > 0)
        {
        	return "color: #" + busData.route_color + "; cursor: pointer;";
        }
        else
        {
            return "color: #999999;";
        }
    };
});

function setCookie(name, value, expireTimeInMillisecond)
{
    var d = new Date();
    d.setTime(d.getTime() + expireTimeInMillisecond);
    var expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + "";
}

function getCookieValue(param)
{
    var readCookie = document.cookie.match('(^|;)\\s*' + param + '\\s*=\\s*([^;]+)');
    return readCookie ? readCookie.pop() : '';
}

function totalTravelTime(path)
{
    var total = 0;
    for(i = 0; i < path.length; i++)
    {
        total += path[i].traveltime + path[i].waittime;
    }

    total = Math.ceil(total / 60);

    return total;
}