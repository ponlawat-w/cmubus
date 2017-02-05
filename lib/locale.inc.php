<?php

###############################
## REQUIRE sessions          ##
## REQUIRE mysql $connection ##
###############################

#### INITIAL FUNCTION ####

## FUNCTION get_language_id
#### Read session and return user language id (e.g. en, th)
#### Create session if language session does not exist
###### Default language is Thai (th)
function get_language_id()
{
	global $connection;
	
	if(isset($_SESSION['user']) && isset($_SESSION['user']['language']))
	{
		return $_SESSION['user']['language'];
	}
	
	if(isset($_COOKIE['user_language']))
	{
		$sql = "SELECT COUNT(*) AS 'count' FROM `languages` WHERE `id` = '{$_COOKIE['user_language']}'";
		$result = mysqli_query($connection, $sql);
		$languagecountdata = mysqli_fetch_array($result);
		
		if($languagecountdata['count'] == 1)
		{
			$_SESSION['user']['language'] = $_COOKIE['user_language'];
			return $_SESSION['user']['language'];
		}
	}
	
	$accepted_languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	$accepted_languages_array = explode(",", $accepted_languages);
	
	foreach($accepted_languages_array as $language)
	{
		$language_ = explode(";", $language);
		$language_ = explode("-", $language_[0]);
		$language = trim($language_[0]);
		
		$sql = "SELECT `id` FROM `languages` WHERE `id` = '$language' AND `available` = 1";
		$result = mysqli_query($connection, $sql);
		if(mysqli_num_rows($result) == 1)
		{
			$_SESSION['user']['language'] = $language;
			return $language;
		}
	}
	
	$_SESSION['user']['language'] = "th";
	
	return $_SESSION['user']['language'];
}

function set_language_id($language_id)
{
	$_SESSION['user']['language'] = $language_id;
}

## FUNCTION get_route_name
#### get_route_name in language in session
#### stop tag is not supported
function get_text($ref_type, $ref_id, $language)
{
	global $connection;
	
	$ref_type = urlencode($ref_type);
	$ref_id = urlencode($ref_id);
	
	if($ref_type == "stop_tag")
	{
		return false;
	}
	
	if($language == "th")
	{
		if($ref_type == "stop")
		{
			$sql = "SELECT `name` FROM `stops` WHERE `id` = $ref_id";//
			$result = mysqli_query($connection, $sql);
			
			if(mysqli_num_rows($result) == 1)
			{
				$stopdata = mysqli_fetch_array($result);
				
				return $stopdata['name'];
			}
		}
		else if($ref_type == "route")
		{
			$sql = "SELECT `name` FROM `routes` WHERE `id` = $ref_id";
			$result = mysqli_query($connection, $sql);
			
			if(mysqli_num_rows($result) == 1)
			{
				$routedata = mysqli_fetch_array($result);
				
				return $routedata['name'];
			}
		}
		
		return false;
	}
	else if($language == "en")
	{
		$sql = "SELECT `text` FROM `texts` WHERE `ref_type` = '$ref_type' AND `ref_id` = $ref_id AND `language` = '$language'";
		$result = mysqli_query($connection, $sql);
		if(mysqli_num_rows($result) == 0)
		{
			$text = get_text($ref_type, $ref_id, "th");
		}
		else
		{
			$textdata = mysqli_fetch_array($result);			
			$text = $textdata['text'];

			if(trim($text) == "")
			{
				$text = get_text($ref_type, $ref_id, "th");
			}
		}
		
		return $text;
	}
	else
	{
		$sql = "SELECT `text` FROM `texts` WHERE `ref_type` = '$ref_type' AND `ref_id` = $ref_id AND `language` = '$language'";
		$result = mysqli_query($connection, $sql);
		if(mysqli_num_rows($result) == 0)
		{
			$text = get_text($ref_type, $ref_id, "en");
		}
		else
		{
			$textdata = mysqli_fetch_array($result);			
			$text = $textdata['text'];

			if(trim($text) == "")
			{
				$text = get_text($ref_type, $ref_id, "en");
			}
		}
		
		return $text;
	}
}
?>
