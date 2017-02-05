<?php

function thaidate($format, $timestamp = false)
{
	// ให้เวลาเป็นเวลาปัจจุบัน ในกรณีที่ไม่มีอินพุตด้านเวลา
	if($timestamp == false)
	{
		$timestamp = mktime();
	}
	
	// ชื่อเดือนแบบเต็มและแบบย่อเป็นภาษาไทย
	$monthtext = array("",
		"มกราคม",
		"กุมภาพันธ์",
		"มีนาคม",
		"เมษายน",
		"พฤษภาคม",
		"มิถุนายน",
		"กรกฎาคม",
		"สิงหาคม",
		"กันยายน",
		"ตุลาคม",
		"พฤศจิกายน",
		"ธันวาคม");
	$monthtext_short = array("",
		"ม.ค.",
		"ก.พ.",
		"มี.ค.",
		"เม.ย.",
		"พ.ค.",
		"มิ.ย.",
		"ก.ค.",
		"ส.ค.",
		"ก.ย.",
		"ต.ค.",
		"พ.ย.",
		"ธ.ค.");
	
	// ชื่อวันแบบเต็มและแบบย่อเป็นภาษาไทย
	$daytext = array(
		"อาทิตย์",
		"จันทร์",
		"อังคาร",
		"พุธ",
		"พฤหัสบดี",
		"ศุกร์",
		"เสาร์");
	$daytext_short = array(
		"อา.",
		"จ.",
		"อ.",
		"พ.",
		"พฤ.",
		"ศ.",
		"ส.");
	
	//สตริงสำหรับค้นหาและแทนที่วันที่ให้เป็นภาษาไทย โดยมีตัวเลือกดังนี้
	## ฿วทวท		วันที่แบบมีเลขศูนย์นำหลักหน่วย
	## ฿วท		วันที่แบบไม่มีเลขศูนย์นำหลักหน่วย
	## ฿วว		วันแบบเต็ม
	## ฿ว		วันแบบย่อ
	## ฿ดด		เดือนแบบเต็ม
	## ฿ด		เดือนแบบย่อ
	## ฿ปปปป		ปี พ.ศ. แบบสี่หลัก
	## ฿ปป		ปี พ.ศ. แบบสองหลักหลัง
	$search = array(
		"฿วทวท",
		"฿วท",
		"฿วว",
		"฿ว",
		"฿ดด",
		"฿ด",
		"฿ปปปป",
		"฿ปป"
	);
	$replace = array(
		"d",
		"j",
		$daytext[date("w", $timestamp)],
		$daytext_short[date("w", $timestamp)],
		$monthtext[date("n", $timestamp)],
		$monthtext_short[date("n", $timestamp)],
		date("Y", $timestamp) + 543,
		substr(date("Y", $timestamp) + 543, strlen(date("Y", $timestamp) + 543) - 2, 2)
	);
	
	$format = str_replace($search, $replace, $format);
	
	return date($format, $timestamp);
}

function getday($timestamp)
{
	return $timestamp - ($timestamp % 86400);
}

?>