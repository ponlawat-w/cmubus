<?php session_start(); ob_start(); ?>
<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
	
	<?php
	include_once("../mysql_connection.inc.php");
	include_once("library.inc.php");
	
	if(isset($_SESSION['authentication']))
	{
		header("location: admin.php");
	}

	?>

    <title>ระบบให้ข้อมูลรถ ขส.มช. - ผู้ดูแลระบบเข้าสู่ระบบ</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        
    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->

	  <div id="login-page">
	  	<div class="container">
	  	
		      <form class="form-login" action="index.php" method="post">
		        <h2 class="form-login-heading">เข้าสู่ระบบผู้ดูแลระบบ</h2>
		        <div class="login-wrap">
					<?php
					if(isset($_POST['authenticate']))
					{
						$username = $_POST['username'];
						$password = $_POST['password'];
						
						if(authenticate($username, $password) && isset($_SESSION['authentication']))
						{
							header("location: admin.php");
						}
						else
						{
							echo '<p class="text-danger">ไม่สามารถเข้าสู่ระบบได้</p>';
						}
					}
					?>
		            <input type="text" name="username" class="form-control" placeholder="ชื่อบัญชี" autofocus>
		            <br>
		            <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน">
		            <label class="checkbox">
		                <span class="pull-right">
		                    <a data-toggle="modal" href="../"> กลับสู่หน้าปกติ</a>		
		                </span>
		            </label>
					<input type="hidden" name="authenticate" value="true">
		            <button class="btn btn-theme btn-block" type="submit"><i class="fa fa-lock"></i> เข้าสู่ระบบ</button>		
		        </div>		
		      </form>
	  	</div>
	  </div>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!--BACKSTRETCH-->
    <!-- You can use an image of whatever size. This script will stretch to fit in any screen size.-->
    <script type="text/javascript" src="assets/js/jquery.backstretch.min.js"></script>
    <script>
        $.backstretch("assets/img/login-bg.jpg", {speed: 500});
    </script>


  </body>
</html>
<?php mysqli_close($connection); ob_end_flush(); ?>