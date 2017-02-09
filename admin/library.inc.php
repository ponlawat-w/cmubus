<?php

function password_encode($password_str)
{
	return md5($password_str) . sha1($password_str);
}

function authenticate($username, $password)
{
	global $connection;
	
	unset($_SESSION['authentication']);
	
	$username = urlencode($username);
	$password = password_encode($password);
	
	$sql = "SELECT `id`, `display_name` FROM `accounts` WHERE `username` = '$username' AND `password` = '$password' LIMIT 1";
	$result = mysqli_query($connection, $sql);
	if(mysqli_num_rows($result) == 1)
	{
		$data = mysqli_fetch_array($result);
		
		$_SESSION['authentication'] = array(
			"id" => $data['id'],
			"name" => $data['display_name']
		);
		
		return true;
	}
	else
	{
	    sleep(5);
		return false;
	}
}

function deauthenticate()
{
	unset($_SESSION['authentication']);
}

function change_password($oldpassword, $newpassword)
{
	global $connection;
	
	$oldpassword = password_encode($oldpassword);
	$newpassword = password_encode($newpassword);
	
	$sql = "SELECT `password` FROM `accounts` WHERE `id` = {$_SESSION['authentication']['id']}";
	$result = mysqli_query($connection, $sql);
	$data = mysqli_fetch_array($result);
	
	if($oldpassword == $data['password'])
	{
		$sql = "UPDATE `accounts` SET `password` = '$newpassword' WHERE `id` = {$_SESSION['authentication']['id']}";
		mysqli_query($connection, $sql);
		
		return true;
	}
	else
	{
		return false;
	}
}

function check_authentication()
{
	if(!isset($_SESSION['authentication']))
	{
		header("location: index.php");
		exit();
	}
}

?>