<?php session_start(); ob_start(); ?>
<!DOCTYPE html>
<html ng-app="cmubus" ng-controller="cmubus_controller">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
	
	<?php
	include_once("../mysql_connection.inc.php");
	include_once("library.inc.php");
	
	check_authentication();
	
	if(isset($_GET['action']) && $_GET['action'] == "signout")
	{
		deauthenticate();
		header("location: index.php");
	}
	?>

    <title>ระบบให้ข้อมูลรถ ขส.มช. - จัดการระบบ</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">    
    
    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <script src="assets/js/chart-master/Chart.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.0/angular.min.js"></script>
    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

  <section id="container" >
      <!-- **********************************************************************************************************************************************************
      TOP BAR CONTENT & NOTIFICATIONS
      *********************************************************************************************************************************************************** -->
      <!--header start-->
      <header class="header black-bg">
              <div class="sidebar-toggle-box">
                  <div class="fa fa-bars tooltips" data-placement="right" data-original-title="เปิด/ปิด เมนู"></div>
              </div>
            <!--logo start-->
            <a href="admin.php" class="logo"><b>CMU Bus</b></a>
            <!--logo end-->
            <div class="nav notify-row" id="top_menu">
            </div>
            <div class="top-menu">
            	<ul class="nav pull-right top-menu">
                    <li><a class="logout" href="admin.php?action=signout">ออกจากระบบ</a></li>
            	</ul>
            </div>
        </header>
      <!--header end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN SIDEBAR MENU
      *********************************************************************************************************************************************************** -->
      <!--sidebar start-->
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <!-- sidebar menu start-->
              <ul class="sidebar-menu" id="nav-accordion">
              
              	  <p class="centered"><a href="admin.php"><img src="assets/img/bus-icon.jpg" class="img-circle" width="60"></a></p>
              	  <h5 class="centered"><?php echo $_SESSION['authentication']['name']; ?></h5>
              	  	
                  <li class="mt">
                      <a <?php echo (!isset($_GET['page']))?'class="active"':''; ?> href="admin.php">
                          <i class="fa fa-circle"></i>
                          <span>ดูสถานะปัจจุบัน</span>
                      </a>
                  </li>

                  <li class="sub-menu">
                      <a href="javascript:;" <?php echo (isset($_GET['page']) && in_array($_GET['page'], array("routes", "stops", "calendar", "route", "newroute", "newstop", "stop")))?'class="active"':''; ?> >
                          <i class="fa fa-bus"></i>
                          <span>จัดการข้อมูลเดินรถ</span>
                      </a>
                      <ul class="sub">
                          <li <?php echo (isset($_GET['page']) && $_GET['page'] == "routes")?'class="active"':''; ?>><a href="admin.php?page=routes">เส้นทางเดินรถ</a></li>
                          <li <?php echo (isset($_GET['page']) && $_GET['page'] == "stops")?'class="active"':''; ?>><a href="admin.php?page=stops">ป้ายหยุดรถและสถานที่</a></li>
                          <li <?php echo (isset($_GET['page']) && $_GET['page'] == "calendar")?'class="active"':''; ?>><a href="admin.php?page=calendar">ปฏิทินการเดินรถ</a></li>
                      </ul>
                  </li>

                  <li class="sub-menu">
                      <a href="javascript:;" <?php echo (isset($_GET['page']) && in_array($_GET['page'], array("languages", "accounts", "language")))?'class="active"':''; ?> >
                          <i class="fa fa-gear"></i>
                          <span>ตั้งค่าระบบ</span>
                      </a>
                      <ul class="sub">
                          <li <?php echo (isset($_GET['page']) && $_GET['page'] == "languages")?'class="active"':''; ?>><a href="admin.php?page=languages">ภาษาและข้อความ</a></li>
                          <!--<li <?php echo (isset($_GET['page']) && $_GET['page'] == "accounts")?'class="active"':''; ?>><a href="admin.php?page=accounts">จัดการบัญชีผู้ดูแลระบบ</a></li>-->
                      </ul>
                  </li>
              </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>
      <!--sidebar end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper">
              <div class="row">
				<?php
					if(isset($_GET['page']))
					{
						$required_file = "src/{$_GET['page']}.php";
						
						if(file_exists($required_file))
						{
							include_once($required_file);
						}
						else
						{
							echo "<h2 class='text-danger'>　<strong>ผิดพลาด</strong> 404 ไม่พบหน้าที่ระบุ</h2>";
						}
					}
					else
					{
						include_once("src/status.php");
					}
				?>
              </div>
          </section>
      </section>

      <!--main content end-->
      <!--footer start-->
      <footer class="site-footer">
          <div class="text-center">
				  ระบบให้ข้อมูลรถ ขส.มช.　การตกแต่งโดยธีมของ Alvarez.is (2557)
				  <a href="#" class="go-top">
					  <i class="fa fa-angle-up"></i>
				  </a>
          </div>
      </footer>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/jquery-1.8.3.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="assets/js/jquery.scrollTo.min.js"></script>
    <!--<script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>-->
    <script src="assets/js/jquery.sparkline.js"></script>

    <!--common script for all pages-->
    <script src="assets/js/common-scripts.js"></script>
    
    <script type="text/javascript" src="assets/js/gritter/js/jquery.gritter.js"></script>
    <script type="text/javascript" src="assets/js/gritter-conf.js"></script>

    <!--script for this page-->
    <!-- <script src="assets/js/sparkline-chart.js"></script> -->    
	<!-- <script src="assets/js/zabuto_calendar.js"></script> -->
	<script src="assets/js/angular/filters.js"></script>
  </body>
</html>
<?php mysqli_close($connection); ob_end_flush(); ?>