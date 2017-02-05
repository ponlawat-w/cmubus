<?php if(!isset($_SESSION['authentication'])) exit(); ?>

<?php
if(isset($_POST['action']))
{
	if($_POST['action'] == "newlanguage")
	{
		$id = $_POST['id'];
		$name = $_POST['name'];
		
		$sql = "INSERT INTO `languages` (`id`, `name`, `available`) VALUES ('$id', '$name', 0)";
		mysqli_query($connection, $sql);
		
		header("location: admin.php?page=language&id={$id}");
	}
}
?>

<div class="col-lg-12">
	<h3>ตั้งค่าภาษา</h3>
	
	<div class="col-md-12">
		<div class="content-panel">
			<table class="table table-hover">
				<thead>
					<tr>
						<th>รหัส</th>
						<th>ชื่อภาษา</th>
						<th>สถานะ</th>
						<th>จัดการ</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$sql = "SELECT * FROM `languages` ORDER BY `id` ASC";
				$results = mysqli_query($connection, $sql);
				while($languagedata = mysqli_fetch_array($results))
				{
					echo "<tr>";
					
					echo "<td>{$languagedata['id']}</td>";
					echo "<td>{$languagedata['name']}</td>";
					if($languagedata['available'] == 1)
					{
						echo "<td class='text-primary'>กำลังเปิดใช้งาน</td>";
					}
					else
					{
						echo "<td class='text-danger'>กำลังปิดการใช้งาน</td>";
					}
					
					echo "<td>";
					
					if($languagedata['id'] == "th")
					{
						echo "ภาษาเริ่มต้น";
					}
					else
					{
						echo "<a href='admin.php?page=language&id={$languagedata['id']}' class='btn btn-primary btn-xs'><i class='fa fa-wrench'></i> จัดการ</a>";
					}
					
					echo "</td>";
					
					echo "</tr>";
				}
				?>
				
				<form action="admin.php?page=languages" method="post">
					<tr>
						<td><input type="text" name="id" maxlength="2" placeholder="รหัส"></td>
						<td><input type="text" name="name" placeholder="ชื่อภาษา"></td>
						<input type="hidden" name="action" value="newlanguage">
						<td colspan="2"><button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i> เพิ่ม</button></td>
					</tr>
				</form>
				
				</tbody>
			</table>
		</div>
	</div>
	
</div>