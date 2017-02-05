<?php if(!isset($_SESSION['authentication'])) exit();

$id = $_GET['id'];

if($id == "th")
{
	header("location: admin.php?page=languages");
	exit;
}

$sql = "SELECT * FROM `languages` WHERE `id` = '$id'";
$result = mysqli_query($connection, $sql);
$languagedata = mysqli_fetch_array($result);
?>

<a href="admin.php?page=languages"><i class="fa fa-arrow-left"></i> กลับ</a>

<div class="col-lg-12">
	<div class="row col-lg-12">
		<div class="col-md-12 content-panel">
			<h3><?php echo $languagedata['name']; ?></h3>
			<a href="admin.php?page=language&id=<?php echo $languagedata['id']; ?>" class='btn btn-default btn-sm'>ข้อมูลภาษา</a>
			<a href="admin.php?page=language&id=<?php echo $languagedata['id']; ?>&do=routes" class='btn btn-default btn-sm'>เส้นทาง</a>
			<a href="admin.php?page=language&id=<?php echo $languagedata['id']; ?>&do=stops" class='btn btn-default btn-sm'>ป้ายหยุดรถ</a>
			<?php
			if(!isset($_GET['do']))
			{
				echo "<p><form action='admin.php?page=language&id={$languagedata['id']}' method='post'>";
				
				echo "<p>รหัสภาษา: {$languagedata['id']}<br>";
				echo "ชื่อภาษา: <input type='text' name='name' value='{$languagedata['name']}'></p>";
				echo "<input type='hidden' name='action' value='editinfo'>";
				echo "<button type='submit' class='btn btn-primary'>บันทึก</button>";
				
				echo "</form></p>";
				
				echo "<hr>";
				
				echo "<p>สถานะ: ";
				if($languagedata['available'] == 1)
				{
					echo "<span class='text-primary'>กำลังใช้งาน</span> <a href='admin.php?page=language&id={$languagedata['id']}&action=changeavailable' class='btn btn-danger btn-xs'>ปิดการใช้งาน</a>";
				}
				else
				{
					echo "<span class='text-danger'>กำลังปิดการใช้งาน</span> <a href='admin.php?page=language&id={$languagedata['id']}&action=changeavailable' class='btn btn-primary btn-xs'>เปิดการใช้งาน</a>";
				}
				echo "</p>";
				
				echo "<hr>";
				
				echo "<a href='admin.php?page=language&id={$languagedata['id']}&action=remove' onclick='return confirm(\"แน่ใจหรือไม่ว่าต้องการลบ\");' class='btn btn-danger'><i class='fa fa-trash'></i> ลบ</a>";
				
				echo "<br>";
				echo "<br>";

				if(isset($_POST['action']))
				{
					if($_POST['action'] == "editinfo")
					{
						$name = $_POST['name'];
						
						$sql = "UPDATE `languages` SET `name` = '$name' WHERE `id` = '$id'";						
						mysqli_query($connection, $sql);
						
						header("location: admin.php?page=language&id={$id}");
					}
				}
				else if(isset($_GET['action']))
				{
					if($_GET['action'] == "changeavailable")
					{
						$sql = "UPDATE `languages` SET `available` = 1 - `available` WHERE `id` = '$id'";
						mysqli_query($connection, $sql);
												
						header("location: admin.php?page=language&id={$id}");
					}
					else if($_GET['action'] == "remove")
					{
						$sql = "DELETE FROM `texts` WHERE `language` = '$id'";
						mysqli_query($connection, $sql);
						
						$sql = "DELETE FROM `languages` WHERE `id` = '$id'";
						mysqli_query($connection, $sql);
						
						header("location: admin.php?page=languages");
					}
				}
			}
			else if($_GET['do'] == "routes")
			{
				echo "<h4>จัดการชื่อเส้นทาง</h4>";
				
				echo "<form action='admin.php?page=language&id={$id}&do=routes' method='post' class='col-lg-6 col-sm-12'>";
				echo "<table class='table'>";
			
				$sql = "SELECT `id`, `name`, `detail`, `color` FROM `routes` ORDER BY `name`, `detail`";
				$results = mysqli_query($connection, $sql);
				while($routedata = mysqli_fetch_array($results))
				{
					echo "<tr>";
					
					echo "<td style='color:#{$routedata['color']};'>{$routedata['name']} ({$routedata['detail']})</td>";
					
					$default = "";
					$sql = "SELECT `id`, `text` FROM `texts` WHERE `ref_type` = 'route' AND `ref_id` = {$routedata['id']} AND `language` = '$id' LIMIT 1";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) == 0)
					{						
						$sql = "INSERT INTO `texts` (`id`, `ref_type`, `ref_id`, `language`, `text`) VALUES (0, 'route', {$routedata['id']}, '$id', '')";
						mysqli_query($connection, $sql);
						$text_id = mysqli_insert_id($connection);
					}
					else
					{
						$data = mysqli_fetch_array($result);
						$default = $data['text'];
						$text_id = $data['id'];
					}
					
					echo "<td><input type='text' name='text[$text_id]' value='$default' placeholder='{$languagedata['name']} {$routedata['name']}'></td>";
					
					echo "</tr>";
				}
				
				echo "</table>";
				
				echo "<input type='hidden' name='action' value='saveroutes'>";
				echo "<button type='submit' class='btn btn-primary'>บันทึก</button>";
				
				echo "</form>";
			}
			else if($_GET['do'] == "stops")
			{
				echo "<h4>จัดการชื่อเส้นทาง</h4>";
				
				echo "<table class='table'>";

				$focused = false;
				
				$sql = "SELECT `id`, `name`, `busstop` FROM `stops` ORDER BY `name`";
				$results = mysqli_query($connection, $sql);
				while($stopdata = mysqli_fetch_array($results))
				{
					echo "<form action='admin.php?page=language&id={$id}&do=stops' method='post'>";
					echo "<tr>";
					
					echo "<td>{$stopdata['name']}</td>";
					
					$default = "";
					$sql = "SELECT `id`, `text` FROM `texts` WHERE `ref_type` = 'stop' AND `ref_id` = {$stopdata['id']} AND `language` = '$id' LIMIT 1";
					$result = mysqli_query($connection, $sql);
					if(mysqli_num_rows($result) == 0)
					{						
						$sql = "INSERT INTO `texts` (`id`, `ref_type`, `ref_id`, `language`, `text`) VALUES (0, 'stop', {$stopdata['id']}, '$id', '')";
						mysqli_query($connection, $sql);
						$text_id = mysqli_insert_id($connection);
					}
					else
					{
						$data = mysqli_fetch_array($result);
						$default = $data['text'];
						$text_id = $data['id'];
					}
					
					$tags = array();
					$sql = "SELECT `text` FROM `texts` WHERE `ref_type` = 'stop_tag' AND `ref_id` = {$stopdata['id']} AND `language` = '$id'";
					$tagsresults = mysqli_query($connection, $sql);
					while($tagdata = mysqli_fetch_array($tagsresults))
					{
						array_push($tags, $tagdata['text']);
					}
					
					$tags_txt = implode("; ", $tags);
					
					if($stopdata['busstop'] == 0)
					{
						echo "<td>สถานที่</td>";
					}
					else
					{
						echo "<td>ป้ายหยุด</td>";
					}
					
					echo "<td><input type='text' name='text' value='$default' placeholder='{$stopdata['name']}' style='width: 20em;'";
					if($focused == false && $default == "")
					{
						echo " autofocus";
						$focused = true;
					}
					echo "></td>";
					echo "<td><input type='text' name='tags' value='$tags_txt' placeholder='ชื่ออื่น ๆ คั่นด้วย ;' style='width: 15em;'></td>";
					echo "<td><button type='submit' class='btn btn-primary btn-xs'>บันทึก</button></td>";
					
					echo "</tr>";
					
					echo "<input type='hidden' name='id' value='$text_id'>";
					echo "<input type='hidden' name='stop' value='{$stopdata['id']}'>";
					echo "<input type='hidden' name='oldtags' value='$tags_txt'>";
					echo "<input type='hidden' name='action' value='savestop'>";
					
					echo "</form>";
				}
				
				echo "</table>";
				
			}
			
			if(isset($_POST['action']))
			{
				if($_POST['action'] == "saveroutes")
				{
					foreach($_POST['text'] as $text_id => $text)
					{
						$sql = "UPDATE `texts` SET `text` = '$text' WHERE `id` = $text_id";
						mysqli_query($connection, $sql);
					}
					
					header("location: admin.php?page=language&id={$id}&do=routes");
				}
				else if($_POST['action'] == "savestop")
				{
					$text_id = $_POST['id'];
					$text = $_POST['text'];
					
					$sql = "UPDATE `texts` SET `text` = '$text' WHERE `id` = $text_id";
					mysqli_query($connection, $sql);
					
					$stop = $_POST['stop'];
					$tags_txt = $_POST['tags'];		
					$oldtags = $_POST['oldtags'];
					
					if($oldtags != $tags_txt)
					{						
						$sql = "DELETE FROM `texts` WHERE `ref_type` = 'stop_tag' AND `ref_id` = $stop AND `language` = '$id'";
						mysqli_query($connection, $sql);
						
						if(trim($tags_txt) != "")
						{
							$tags = explode(";", $tags_txt);
							
							foreach($tags as $tag)
							{
								$tag = strtolower(trim($tag));
							
								$sql = "INSERT INTO `texts` (`id`, `ref_type`, `ref_id`, `language`, `text`) VALUES (0, 'stop_tag', $stop, '$id', '$tag')";
								mysqli_query($connection, $sql);
							}
						}
					}
					
					header("location: admin.php?page=language&id={$id}&do=stops");
				}
			}
			?>
		</div>
	</div>
</div>