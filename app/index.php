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
		include_once("../lib/locale.inc.php");
		get_language_id();
		session_write_close();
		?>
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/map-icons.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/styles.css">
		<title>CMU BUS</title>
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
					<li class="pull-right"><a href="#/settings"><i class='fa fa-gear'></i></a></li>
				</ul>
			</div>
		</nav>
		<div style="padding: 0px;" class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5 bordered">
			<div id="main">
				<div ng-view>
				</div>
			</div>
		</div></div>
	</body>
	<script src="assets/js/angular.min.js"></script>
	<script src="assets/js/angular-route.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="locale/<?php echo get_language_id(); ?>.locale.js"></script>
</html>
<?php mysqli_close($connection); ob_end_flush(); ?>