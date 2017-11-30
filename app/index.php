<?php session_start(); ob_start();
    include_once("../mysql_connection.inc.php");
    include_once("../version.php");
    include_once("../lib/app.inc.php");
    get_language_id();
    session_write_close();

    if(isset($_GET['error']))
    {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        if($_GET['error'] == 'cookie_disabled')
        {
            echo '<p>This website requires cookie, please enable it.<br>ขออภัย คุณต้องเปิดการใช้งานคุกกี้เพื่อเข้าถึงเว็บไซต์นี้</p>';
            echo '<a href="/">Retry / ลองใหม่</a>';
            exit;
        }
    }
?>
<!doctype html>
<html ng-app="cmubus" ng-controller="localeController" lang="<?php echo get_language_id(); ?>">
	<head>
		<meta charset="utf-8">

        <base href="/">

        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="application-name" content="CMUBUS">
		<meta name="description" content="Chiang Mai University Bus Information System | ระบบให้ข้อมูลรถ ขส.มช.">
		<meta name="keywords" content="CMU,มช,ม.ช.,รถม่วง,ขสมช,ขส.มช.,Chiang Mai University">
        <link rel="icon" href="favicon.ico?v=<?php echo $version['updated']; ?>">
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/map-icons.min.css">
		<link rel="stylesheet" type="text/css" href="assets/css/styles.css?v=<?php echo $version['updated']; ?>">
		<title>CMU BUS</title>
        <script>
            var language = "<?php echo get_language_id(); ?>";
            var variables = {};
            variables.version = <?php echo json_encode($version); ?>;
            variables.gaUsing = !variables.version.development;

            if(variables.gaUsing)
            {
              (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
              ga('create', 'UA-91670758-1', 'auto');
//              ga('send', 'pageview');
            }
		</script>
        <script src="assets/js/angular.min.js"></script>
        <script src="assets/js/angular-route.min.js"></script>
        <script src="assets/js/angular-animate.min.js"></script>
        <script src="assets/js/main.js?v=<?php echo $version['updated']; ?>"></script>
        <script src="locale/<?php echo get_language_id(); ?>.locale.js?v=<?php echo $version['updated']; ?>"></script>
	</head>
	<body ng-controller="mainController">
		<nav class="navbar navbar-fixed-top">
			<div class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5">
				<div class="nav navbar-header pull-left">
					<div class="navbar-brand"><a href="/" ng-bind="txt.header">cmubus.com</a><?php if($version['development']) { echo ' <small><sub>(DEVELOPER)</sub></small>'; } ?></div>
				</div>
				<ul class="nav navbar-nav navbar-right pull-right">
					<li class="pull-left"><a href="/"><i class='fa fa-home'></i></a></li>
					<li class="pull-right"><a href="/menu"><i class='fa fa-bars'></i></a></li>
				</ul>
			</div>
		</nav>
		<div style="padding: 0;" class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5 bordered">
			<div id="main">
				<div ng-view>
				</div>
			</div>
		</div>
        <div class="modal-bg" ng-if="showBottomNavbar || showError"></div>
        <div class="modal-white-bg" ng-if="stateChanging"></div>
        <div ng-if="stateChanging" class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5" style="position: fixed; top: 70px; z-index: 12;">
            <div class="modal-content">
                <div class="modal-body text-center" style="padding: 2em 0;">
                    <img src="assets/img/big-loading.gif">
                </div>
            </div>
        </div>
        <div ng-if="showError" class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5" style="position: fixed; top: 2vh; z-index: 12;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> {{txt.error.title}}</h4>
                </div>
                <div class="modal-body" style="overflow: auto;">
                    <div class="alert alert-danger">
                        {{txt.error.message}}
                    </div>
                    <div class="btn-group-sm btn-group-vertical" style="width: 100%;">
                        <a href='javascript:void(0);' onclick="window.location.reload(true);" class="btn btn-default" style="text-align: left;"><span class="text-success"><i class="fa fa-refresh"></i> {{txt.error.retry}}</span></a>
                        <a ng-click="closeError(); $location.path('/');" href='javascript:void(0);' class="btn btn-default" style="text-align: left;"><i class="fa fa-home"></i> {{txt.menu.home}}</a>
                        <a ng-click="closeError(); $location.path('/report');" href='javascript:void(0);' class="btn btn-default" style="text-align: left;"><span class="text-warning"><i class="fa fa-flag"></i> {{txt.menu.problemReport}}</span></a>
                    </div>
                </div>
            </div>
        </div>
        <nav class="navbar navbar-fixed-bottom slide-toggle" ng-show="showBottomNavbar">
            <div class="container-fluid col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-4 col-lg-offset-4 col-xl-2 col-xl-offset-5">
                <a href="javascript:void(0);" class="close-button" ng-click="closeSuggestion();"><i class="fa fa-times"></i></a>
                <div id="navbar-suggest-thai" ng-show="bottomNavbar=='suggestThai'">
                    <p>
                        <h4>ใช้งานภาษาไทยหรือไม่</h4>
                        <div ng-show="!settingToThai">
                            <button class="btn btn-default" ng-click="setToThai();">คลิกที่นี่เพื่อเปลี่ยนเป็นภาษาไทย</button>
                            <button class="btn btn-default" ng-click="closeSuggestion();">{{txt.home.useThisLanguage}}</button>
                        </div>
                        <div ng-show="settingToThai">
                            กรุณารอสักครู่…
                        </div>
                    </p>
                </div>
                <div id="navbar-suggest-thai" ng-show="bottomNavbar=='appInfo'">
                    <p>
                        <h4>{{txt.about.index.title}}</h4>
                        <p>
                            {{txt.about.index.message}}
                        </p>
                        <p>
                            <i class="fa fa-exclamation-circle"></i>{{txt.home.announcement.messages[0]}}
                        </p>
                        <div>
                            <a href="/about" class="btn btn-default" ng-click="closeSuggestion();">{{txt.about.index.readMore}}</a>
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
                            <a href="/evaluate" ng-click="closeSuggestion();" class="btn btn-lg btn-success">{{txt.home.evaluationSurvey.evaluationButton}}</a>　
                            <a href="javascript:void(0)" class="btn btn-default" ng-click="closeSuggestion();"">{{txt.home.evaluationSurvey.laterButton}}</a>
                        </p>
                        <p>
                            <a href="javascript:void(0)" class="btn btn-xs btn-secondary" ng-click="neverAskMeSurvey();">{{txt.home.evaluationSurvey.neverButton}}</a>
                        </p>
                    </p>
                </div>
            </div>
        </nav>
	</body>
</html>
<?php mysqli_close($connection); ob_end_flush(); ?>