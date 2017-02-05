<?php session_start(); ob_start(); ?>
<!doctype html>
<html ng-app="cmubus" ng-controller="localeController">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php
		include_once("../mysql_connection.inc.php");
		include_once("../lib/locale.inc.php");
		?>
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/map-icons.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/styles.css">
		<title>{{txt.header}}</title>
	</head>
	<body ng-controller="mainController">
		<nav class="navbar navbar-fixed-top">
			<div class="container-fluid">
				<div class="nav navbar-header pull-left">
					<a href="#/" class="navbar-brand">{{txt.header}} <small><sub>BETA</sub></small></a>
				</div>
				<ul class="nav navbar-nav navbar-right pull-right">
					<li class="pull-left"><a href="#/"><i class='fa fa-home'></i></a></li>
					<li class="pull-left"><a href="#/search"><i class='fa fa-search'></i></a></li>
					<li class="pull-right"><a href="#/settings"><i class='fa fa-gear'></i></a></li>
				</ul>
			</div>
		</nav>
		<div id="main">
			<div ng-view>
			</div>
		</div>
	</body>
	<script src="assets/js/angular.min.js"></script>
	<script src="assets/js/angular-route.min.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="locale/<?php echo get_language_id(); ?>.locale.js"></script>
</html>
<?php mysqli_close($connection); ob_end_flush(); ?>