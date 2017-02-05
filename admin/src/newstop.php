<?php if(!isset($_SESSION['authentication'])) exit(); ?>
<?php	
if(isset($_POST['submit']))
{
	$name = $_POST['name'];
	$lat = $_POST['lat'];
	$lon = $_POST['lon'];
	$busstop = $_POST['busstop'];
	
	$sql = "INSERT INTO `stops` (`id`, `name`, `location_lat`, `location_lon`, `busstop`) VALUES (0, '$name', $lat, $lon, $busstop)";
	//echo $sql;
	mysqli_query($connection, $sql);
	
	$id = mysqli_insert_id($connection);
	
	header("location: admin.php?page=stop&id=$id");
}
else
{
	$lat = $_GET['lat'];
	$lon = $_GET['lon'];
}
?>
<div class="col-lg-12">	
	<a href="admin.php?page=stops"><i class="fa fa-arrow-left"></i> กลับ</a>
	<h3>เพิ่มสถานที่ใหม่</h3>
	<form action="admin.php?page=newstop" method="post">
		ชื่อสถานที่: <input type="text" name="name"><br>
		ตำแหน่ง: <?php echo $lat . "," . $lon; ?><br>
		<input type="hidden" name="lat" value="<?php echo $lat; ?>">
		<input type="hidden" name="lon" value="<?php echo $lon; ?>">
		ชนิด:
			<label><input type="radio" name="busstop" value="1" checked> ป้ายหยุด</label>
			<label><input type="radio" name="busstop" value="0"> สถานที่</label><br>
		<input type="submit" name="submit" value="ตกลง" class="btn btn-primary">
	</form>
</div>