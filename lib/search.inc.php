<?php

### Library for searching stops/places by given keyword ###

###############################
## REQUIRE locale.inc.php    ##
## REQUIRE mysqli connection ##
###############################

function search($keyword, $language, $max_result = 10)
{	
	$function_result = array();
	
	$keyword = strtolower(str_replace("'", "", $keyword));
	$language = urlencode($language);
	
	foreach(find_where_name_like($keyword . "%", $language) as $result)
	{
		$result['name'] = get_text("stop", $result['id'], $language);
		
		if(result_push($function_result, $result) >= $max_result)
		{
			return $function_result;
		}
	}
	
	foreach(find_where_tag_like($keyword . "%", $language) as $result)
	{
		$result['name'] = get_text("stop", $result['id'], $language);
		
		if(result_push($function_result, $result) >= $max_result)
		{
			return $function_result;
		}
	}
	
	foreach(find_where_name_like("%" . $keyword . "%", $language) as $result)
	{
		$result['name'] = get_text("stop", $result['id'], $language);
		
		if(result_push($function_result, $result) >= $max_result)
		{
			return $function_result;
		}
	}
	
	foreach(find_where_tag_like("%" . $keyword . "%", $language) as $result)
	{
		$result['name'] = get_text("stop", $result['id'], $language);
		
		if(result_push($function_result, $result) >= $max_result)
		{
			return $function_result;
		}
	}
	
	if($language != "en")
	{	
		foreach(find_where_name_like($keyword . "%", "en") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
		
		foreach(find_where_tag_like($keyword . "%", "en") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
		
		foreach(find_where_name_like("%" . $keyword . "%", "en") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
		
		foreach(find_where_tag_like("%" . $keyword . "%", "en") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
	}
	
	if($language != "th")
	{
		foreach(find_where_name_like($keyword . "%", "th") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
		
		foreach(find_where_tag_like($keyword . "%", "th") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
		
		foreach(find_where_name_like("%" . $keyword . "%", "th") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
		
		foreach(find_where_tag_like("%" . $keyword . "%", "th") as $result)
		{
			$result['name'] = get_text("stop", $result['id'], $language);
			
			if(result_push($function_result, $result) >= $max_result)
			{
				return $function_result;
			}
		}
	}
	
	return $function_result;
}

function find_where_name_like($keyword, $language)
{
	global $connection;
	
	$function_result = array();
	
	if($language == "th")
	{
		$sql = "SELECT `id`, `name`, `busstop` FROM `stops` WHERE `name` LIKE '$keyword'";
		$results = mysqli_query($connection, $sql);
		while($stopdata = mysqli_fetch_array($results))
		{
			result_push($function_result, array(
				"id" => $stopdata['id'],
				"cause" => $stopdata['name'],
				"busstop" => $stopdata['busstop']
			));
		}
	}
	else
	{
		$sql = "SELECT `ref_id`, `text`, `busstop` FROM `texts`, `stops` WHERE `ref_type` = 'stop' AND `ref_id` = `stops`.`id` AND `language` = '$language' AND `text` LIKE '$keyword'";
		$results = mysqli_query($connection, $sql);
		while($stopdata = mysqli_fetch_array($results))
		{
			result_push($function_result, array(
				"id" => $stopdata['ref_id'],
				"cause" => $stopdata['text'],
				"busstop" => $stopdata['busstop']
			));
		}		
	}
	
	return $function_result;
}

function find_where_tag_like($keyword, $language)
{
	global $connection;
	
	$function_result = array();
	
	if($language == "th")
	{
		$sql = "SELECT `stop`, `name` FROM `stops_tags` WHERE `name` LIKE '$keyword'";
		$results = mysqli_query($connection, $sql);
		while($tagdata = mysqli_fetch_array($results))
		{
			$sql = "SELECT `id`, `name`, `busstop` FROM `stops` WHERE `id` = {$tagdata['stop']}";
			$result = mysqli_query($connection, $sql);
			$stopdata = mysqli_fetch_array($result);
			
			result_push($function_result, array(
				"id" => $stopdata['id'],
				"cause" => $tagdata['name'],
				"busstop" => $stopdata['busstop']
			));
		}
	}
	else
	{
		$sql = "SELECT `ref_id`, `text` FROM `texts` WHERE `ref_type` = 'stop_tag' AND `language` = '$language' AND `text` LIKE '$keyword'";
		$results = mysqli_query($connection, $sql);
		while($tagdata = mysqli_fetch_array($results))
		{
			$sql = "SELECT `name`, `busstop` FROM `stops` WHERE `id` = {$tagdata['ref_id']}";
			$result = mysqli_query($connection, $sql);
			$stopdata = mysqli_fetch_array($result);
			
			result_push($function_result, array(
				"id" => $tagdata['ref_id'],
				"cause" => $tagdata['text'],
				"busstop" => $stopdata['busstop']
			));
		}		
	}
	
	return $function_result;
}

function result_find($result, $stopid)
{
	foreach($result as $r)
	{
		if($r['id'] == $stopid)
		{
			return true;
		}
	}
	
	return false;
}

function result_push(&$result, $newelement)
{
	if(result_find($result, $newelement['id']) == false)
	{
		return array_push($result, $newelement);
	}
	
	return count($result);
}
?>