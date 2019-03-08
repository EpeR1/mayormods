<?php
include("../ki_config/ki_setup.php");

$browser = $_SERVER['HTTP_USER_AGENT'];

if(preg_match("/Opera/",$browser))
	$browser = "opera"; 
elseif(preg_match("/MSIE [7-9]/",$browser))
	$browser = "ie7";
elseif(preg_match("/MSIE [1-6]/",$browser))
	$browser = "ie6";
elseif(preg_match("/AppleWebKit/",$browser))
	$browser = "webkit";
else
	$browser = "gecko";

header("Content-type: image/png"); //Picture Format
header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE 

if($browser === "ie6"){
	$bild = imagecreatetruecolor(1, 1);
} else {
	$bild = imagecreate(2880, 1800);
}
imagealphablending($bild, false);
imagesavealpha($bild, true);
$r = hexdec(substr($fade_color, 1, 2));
$g = hexdec(substr($fade_color, 3, 2));
$b = hexdec(substr($fade_color, 5, 2));
$farbe = imagecolorallocatealpha($bild, $r, $g, $b, ((10-$fade_alpha)/10)*127);
if($browser === "ie6"){
	ImageFilledRectangle($bild,0,0,1,1,$farbe);
} else {
	ImageFilledRectangle($bild,0,0,2880,1800,$farbe);
}
imagepng($bild);
imagedestroy($bild);
?>