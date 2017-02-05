var app = angular.module("cmubus", ['ngRoute']);

app.config(function($routeProvider, $locationProvider, $httpProvider)
{	
	$httpProvider.defaults.cache = true;
	
	$locationProvider.hashPrefix('');
	
	$routeProvider
		.when('/', {
			templateUrl: "pages/home.html",
			controller: "homeController"
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
		.when('/settings', {
			templateUrl: "pages/settings.html",
			controller: "settingsController"
		});
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
		if($location.hash()) $anchorScroll();  
	});
});

app.controller("mainController", function($scope, $location)
{
	$scope.$location = $location;
});
// END mainController

app.controller("homeController", function($scope, $http, $location, $anchorScroll)
{
	$scope.loading = true;
	$scope.nearStops = [];
		
	$scope.loadData = function()
	{
		$scope.loading = true;
		$scope.nearStops = [];
		
		if (navigator.geolocation)
		{
			navigator.geolocation.getCurrentPosition(function(position)
			{
				$scope.$apply(function()
				{
					var lat = position.coords.latitude;
					var lon = position.coords.longitude;
					
					var url = "data/findnearstop.php?lat=" + lat + "&lon=" + lon + "&stoponly=true&limit=5&timetable=true";
					
					$http.get(url).then(function(response)
					{
						$scope.loading = false;
						$scope.nearStops = response.data;
					}, function(response)
					{
						$scope.loading = false;
					});
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
	
	$scope.goRoute = function(routeid)
	{
		$location.path("route/" + routeid);
	};
	
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
// END homeController

app.controller("searchController", function($scope, $http, $location, $anchorScroll, $routeParams)
{
	$scope.loading = false;
	
	$scope.clicked = false;
	$scope.from_id = null;
	$scope.from_location = false;
	$scope.to_id = null;
	$scope.to_location = false;
	
	$scope.from_autocompletes = [];
	$scope.to_autocompletes = [];
	
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
		if (navigator.geolocation)
		{
			navigator.geolocation.getCurrentPosition(function(position)
			{
				$scope.$apply(function()
				{
					var lat = position.coords.latitude;
					var lon = position.coords.longitude;
					
					var url = "data/findnearstop.php?lat=" + lat + "&lon=" + lon + "&stoponly=false&limit=1";
					
					$http.get(url).then(function(response)
					{
						$scope.loading = false;
						$scope.clicked = true;
						$scope.from_location = true;
						$scope.from_id = response.data[0].id;
						$scope.from_keyword = response.data[0].name;
					}, function(response)
					{
					});
				});
			}, function()
			{
				$scope.loading = false;
			});
		}
		else
		{
			$scope.loading = false;
			$scope.from_location = true;
		}
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
	}
	
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
		if (navigator.geolocation)
		{
			navigator.geolocation.getCurrentPosition(function(position)
			{
				$scope.$apply(function()
				{
					var lat = position.coords.latitude;
					var lon = position.coords.longitude;
					
					var url = "data/findnearstop.php?lat=" + lat + "&lon=" + lon + "&stoponly=false&limit=1";
					
					$http.get(url).then(function(response)
					{
						$scope.loading = false;
						$scope.clicked = true;
						$scope.to_location = true;
						$scope.to_id = response.data[0].id;
						$scope.to_keyword = response.data[0].name;
					}, function(response)
					{
					});
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
// END searchController

app.controller("searchResultController", function($scope, $routeParams, $http, $location)
{
	$scope.info = {
		fromName: "",
		toName: ""
	};
	$scope.paths = [];
	
	$scope.from_id = $routeParams.from_id;
	$scope.to_id = $routeParams.to_id;
	
	$scope.finished = false;
	
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
			$http.get("data/findpath.php?from=" + $scope.from_id + "&to=" + $scope.to_id).then(function(response)
			{
				$scope.finished = true;
				$scope.paths = response.data;
			}, function(reponse) {  });			
		}, function(response) {});
	}, function(response) {});
		
	$scope.goStop = function(id)
	{
		$location.path("stop/" + id);
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
});
// END searchResultController

app.controller("stopController", function($scope, $http, $routeParams, $location)
{
	$scope.id = $routeParams.id;
	$scope.loading = true;
	$scope.timetableLoading = true;
	$scope.infoLoading = false;
	$scope.infoLoaded = false;
	
	$scope.busstop = null;;
	
	$scope.timetableMode = "timeLeft";
	$scope.view = "timetable";
	
	$scope.stopTimetable = [];
	$scope.stopInfo = {};
	
	$scope.loadTimetable = function()
	{		
		$http.get("data/timetable.php?stopid=" + $scope.id + "&temp=" + Date.now()).then(function(response)
		{
			$scope.timetableLoading = false;		
			$scope.stopTimetable = response.data.arrival_timetable;
			$scope.passedTimetable = response.data.passed_timetable;
			
		}, function(response)
		{			
			$scope.timetableLoading = false;			
		});
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
			setInterval(function() { $scope.loadTimetable(); }, 3000);
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
// END stopController

app.controller("sessionController", function($scope, $http, $routeParams, $location)
{
	$scope.sessionID = $routeParams.id;
	
	$scope.loading = true;
	
	$scope.sessionInfo = {online: true};
	
	$scope.loadSessionInfo = function()
	{
		if($scope.sessionInfo.online == true)
		{
			$http.get("data/session.php?id=" + $scope.sessionID + "&temp=" + Date.now()).then(function(response)
			{
				$scope.sessionInfo = response.data;
				$scope.loading = false;
				
				if($scope.sessionInfo.online == true)
				{
					setInterval(function() { $scope.loadSessionInfo(); }, 5000);
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
});
// END sessionController

app.controller("routesController", function($scope, $http, $location)
{
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
// END routesController

app.controller("routeController", function($scope, $http, $location, $routeParams)
{
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
// END routeController

app.controller("settingsController", function($scope, $http, $location)
{
	// LANGUAGES
	
	$scope.languages = [];
	
	$scope.changeLanguage = function(newLanguageID)
	{
		$http.get("data/set_language.php?id=" + newLanguageID).then(function(response)
		{
			var d = new Date();
			d.setTime(d.getTime() + 5184000000);
			var expires = "expires="+ d.toUTCString();
			document.cookie = "user_language=" + newLanguageID + ";" + expires + "";

			window.location.reload();

			//window.location = "https://app.cmubus.com";
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
// END settingsController

// FILTERS //

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

// END FILTERS//
