<?php
if(!isset($_GET['ot']))
	exit();
else
	$ot = $_GET['ot'];
	
if(isset($_GET['gallery']))
	$gallery = $_GET['gallery'];
else
	exit;
	
// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $gallery))exit();
// ---------- Ende Sicherheitsabfragen!

include_once("../ki_config/ki_setup.php");
if(is_file("../ki_config/".$gallery."_ki_setup.php"))include_once("../ki_config/".$gallery."_ki_setup.php");

if($ot == 0)
	$polygon = array(0,0, 0,200, 200,200);
elseif($ot == 1)
	$polygon = array(0,0, 0,200, 200,0);
elseif($ot == 2)
	$polygon = array(0,200, 200,200, 200,0);
else
	$polygon = array(0,0, 200,200, 200,0);

header("Content-type: image/gif"); //Picture Format 
header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE

$bild = imagecreate(200, 200);

imagealphablending($bild, false);
imagesavealpha($bild, true);

$transparent = imagecolorallocatealpha($bild, 0, 0, 0, 127);
imagefilledrectangle($bild, 0, 0, 200, 200, $transparent);
imagecolortransparent($bild, $transparent);

$r = hexdec(substr($bord_color, 1, 2));
$g = hexdec(substr($bord_color, 3, 2));
$b = hexdec(substr($bord_color, 5, 2));

$farbe = imagecolorallocatealpha($bild, $r, $g, $b, 0);
imagefilledpolygon($bild, $polygon, 3, $farbe);

imagegif($bild);
imagedestroy($bild);
?>