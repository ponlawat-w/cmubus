<?php session_start(); ob_start(); ?>
<!doctype html>
<html ng-app="cmubus" ng-controller="localeController">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<meta name="application-name" content="CMUBUS">
		<meta name="description" content="Chiang Mai University Bus Information System | ระบบให้ข้อมูลรถ ขส.มช.">
		<meta name="keywords" content="CMU,มช,ม.ช.,รถม่วง,ขสมช,ขส.มช.,Chiang Mai University">
		<?php
		include_once("../mysql_connection.inc.php");
		include_once("../lib/app.inc.php");
		get_language_id();
		session_write_close();
		?>
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/map-icons.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/styles.css">
		<title>CMU BUS</title>
        <script>
            var language = "<?php echo get_language_id(); ?>";
        </script>
        <script src="assets/js/angular.min.js"></script>
        <script src="assets/js/angular-route.min.js"></script>
        <script src="assets/js/angular-animate.min.js"></script>
        <script src="assets/js/main.js"></script>
        <script src="locale/<?php echo get_language_id(); ?>.locale.js"></script>
	</head>
	<body ng-controller="mainController">
		<nav class="navbar navbar-fixed-top">
			<div class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5">
				<div class="nav navbar-header pull-left">
					<div class="navbar-brand"><a href="#/" ng-bind="txt.header">cmubus.com</a> <small><sub>BETA</sub></small></div>
				</div>
				<ul class="nav navbar-nav navbar-right pull-right">
					<li class="pull-left"><a href="#/"><i class='fa fa-home'></i></a></li>
					<li class="pull-left"><a href="#/search"><i class='fa fa-search'></i></a></li>
					<li class="pull-right"><a href="#/menu"><i class='fa fa-bars'></i></a></li>
				</ul>
			</div>
		</nav>
		<div style="padding: 0px;" class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5 bordered">
			<div id="main">
				<div ng-view>
				</div>
			</div>
		</div>
        <nav class="navbar navbar-fixed-bottom slide-toggle" ng-show="showBottomNavbar">
            <div class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5">
                <a href="javascript:void(0);" class="close-button" ng-click="closeSuggestion()"><i class="fa fa-times"></i></a>
                <div id="navbar-suggest-thai" ng-show="bottomNavbar=='suggestThai'">
                    <p>
                        <h4>ใช้งานภาษาไทยหรือไม่</h4>
                        <div ng-show="!settingToThai">
                            <button class="btn btn-default" ng-click="setToThai();">คลิกที่นี่เพื่อเปลี่ยนเป็นภาษาไทย</button>
                        </div>
                        <div ng-show="settingToThai">
                            กรุณารอสักครู่…
                        </div>
                    </p>
                </div>
                <div id="navbar-survey" ng-show="bottomNavbar=='suggestSurvey'">
                    <p>
                        <h3>{{txt.home.evaluationSurvey.title}}</h3>
                        <p>
                            {{txt.home.evaluationSurvey.message}}
                        </p>
                        <p style="padding-bottom: 0.7em;">
                            <a href="#/evaluate" ng-click="closeSuggestion()" class="btn btn-lg btn-success">{{txt.home.evaluationSurvey.evaluationButton}}</a>　
                            <a href="javascript:void(0)" class="btn btn-default" ng-click="closeSuggestion()"">{{txt.home.evaluationSurvey.laterButton}}</a>
                        </p>
                        <p>
                            <a href="javascript:void(0)" class="btn btn-xs btn-secondary" ng-click="neverAskMeSurvey()">{{txt.home.evaluationSurvey.neverButton}}</a>
                        </p>
                    </p>
                </div>
            </div>
        </nav>
	</body>
</html>
<?php mysqli_close($connection); ob_end_flush(); ?>