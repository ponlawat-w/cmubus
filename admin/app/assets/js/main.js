var app = angular.module("cmubus", ['ngRoute']);

app.config(function($routeProvider, $locationProvider)
{
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
		.when('/search?from=:from_id', {
			templateUrl: "pages/search.html",
			controller: "searchController"
		})
		.when('/search?to=:to_id', {
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
		.when('/routes', {
			templateUrl: "pages/routes.html",
			controller: "routeController"
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

app.controller("homeController", function($scope, $http, $location)
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
					
					var url = "data/findnearstop.php?lat=" + lat + "&lon=" + lon + "&stoponly=true&limit=3&timetable=true";
					
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
			$scope.from_location = true;
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
	
	$scope.loadData();
});
// END homeController

app.controller("searchController", function($scope, $http, $location, $anchorScroll)
{
	$scope.loading = false;
	
	$scope.clicked = false;
	$scope.from_id = null;
	$scope.from_location = false;
	$scope.to_id = null;
	$scope.to_location = false;
	
	$scope.scrollTo = function(id)
	{
		var old = $location.hash();
		$location.hash(id);
		$anchorScroll();
		$location.hash(old);
	};
	
	// FROM
	$scope.$watch("from_keyword", function()
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
				alert("พบข้อผิดพลาด");
			});
		}
	});
	
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
	}
	// END FROM
	
	// TO
	$scope.$watch("to_keyword", function()
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
				alert("พบข้อผิดพลาด");
			});
		}
	});
	
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
	}
	// END TO
	
	$scope.search = function()
	{
		if($scope.from_id != null && $scope.to_id != null && $scope.from_id != $scope.to_id)
		{
			$location.path("/search/" + $scope.from_id + "/" + $scope.to_id);
		}
	}
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
			}, function(reponse) { alert("พบข้อผิดพลาด"); });			
		}, function(response) {alert("พบข้อผิดพลาด");});
	}, function(response) {alert("พบข้อผิดพลาด");});
	

});
// END searchResultController

app.controller("stopController", function($scope, $http, $routeParams)
{
	$scope.id = $routeParams.id;
	$scope.loading = true;
	$scope.timetableLoading = false;
	
	$scope.stopTimetable = [];
	$scope.stopInfo = {};
	
	$scope.loadTimetable = function()
	{
		if($scope.timetableLoading == false)
		{
			$scope.timetableLoading = true;
			
			$http.get("data/timetable.php?stopid=" + $scope.id + "&temp=" + Date.now()).then(function(response)
			{
				$scope.timetableLoading = false;			
				$scope.stopTimetable = response.data;
				
			}, function(response)
			{
				alert("ERROR ข้อผิดพลาด");
				$scope.timetableLoading = false;			
			});
		}
	};
	
	$http.get("data/stop.php?id=" + $scope.id).then(function(response)
	{
		$scope.stopInfo = response.data;
		$scope.loading = false;
		
		$scope.loadTimetable();
		setInterval(function() { $scope.loadTimetable(); }, 5000);
	}, function(response)
	{
		alert("ERROR ข้อผิดพลาด");
	});
});
// END stopController

app.controller("sessionController", function($scope)
{
});
// END sessionController

app.controller("routeController", function($scope)
{
});
// END routeController

app.controller("settingsController", function($scope, $http, $location)
{
	$scope.languages = [];
	
	$scope.changeLanguage = function(newLanguageID)
	{
		$http.get("data/set_language.php?id=" + newLanguageID).then(function(response)
		{
			window.location.reload();
		}, function(reponse)
		{
			alert("พบข้อผิดพลาด Error");
		});
	};
	
	$http.get("data/languages_list.php").then(function(response)
	{
		$scope.languages = response.data;
	}, function(response)
	{
		alert("พบข้อผิดพลาด Error");
	});
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

// END FILTERS//