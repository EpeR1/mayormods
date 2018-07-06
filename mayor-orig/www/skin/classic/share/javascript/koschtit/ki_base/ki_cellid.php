<?php
if(isset($_POST['file']))
	$file = rawurldecode($_POST['file']);
else
	exit;

if(isset($_POST['gallery']))
	$gallery = $_POST['gallery'];
else
	exit;

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $file))exit();
if(preg_match("/[\.]*\//", $gallery))exit();
if(!is_file("../ki_galleries/".$gallery."/".$file))exit();
// ---------- Ende Sicherheitsabfragen!

// ---------------------------------------------- Try to find Position -------------------------------------------
$latitude = 0;
$longitude = 0;

if(function_exists("curl_init")){
	ob_start();
	readfile("../ki_galleries/".$gallery."/".$file);
	$source = ob_get_contents();
	ob_end_clean();
	$xmpdata_start = strpos($source, "<x:xmpmeta");
	$xmpdata_end = strpos($source, "</x:xmpmeta>");
	$xmplength = $xmpdata_end - $xmpdata_start;
	$xmpdata = substr($source, $xmpdata_start, $xmplength + 12);
	if(preg_match("/<cell:mcc>(.+)<\/cell:mcc>/", $xmpdata, $temp)){ // country code
		$mcc = $temp[1];
		if(preg_match("/<cell:mnc>(.+)<\/cell:mnc>/", $xmpdata, $temp)){ // network code 
			$mnc = $temp[1];
			if(preg_match("/<cell:cellid>(.+)<\/cell:cellid>/", $xmpdata, $temp)){
				$cellid = $temp[1];
				$myapikey = "b07aa008a74d3d0dc81d77dac2c8aaeb"; // opencellid api key
				$lac = ""; // location area code
				if(preg_match("/<cell:lac>(.+)<\/cell:lac>/", $xmpdata, $temp)){
					$lac = $temp[1];
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://www.opencellid.org/cell/get?key=".$myapikey."&mcc=".$mcc."&mnc=".$mnc."&cellid=".$cellid."&lac=".$lac."&fmt=txt");
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$response = curl_exec($ch);
				curl_close($ch);
				$temp = explode(",", $response);
				if(count($temp) > 1){
					if($temp[0] != "0.0" && $temp[1] != "0.0"){
						$latitude = $temp[0];
						$longitude = $temp[1];
					}
				}
			}
		}
	}
}
		
echo $latitude.",".$longitude;
//----------------------------------------- thats what I found --------------------------
?>