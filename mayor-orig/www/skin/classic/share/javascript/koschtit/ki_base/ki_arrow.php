<?php
if(!isset($_GET['ot']))
	exit();
else
	$ot = $_GET['ot'];
	
	
function imageBoldLine($resource, $x1, $y1, $x2, $y2, $Color, $BoldNess=2)
{
 $center = round($BoldNess/2);
 for($i=0;$i<$BoldNess;$i++)
 { 
  $a = $center-$i; if($a<0){$a -= $a;}
  for($j=0;$j<$BoldNess;$j++)
  {
   $b = $center-$j; if($b<0){$b -= $b;}
   $c = sqrt($a*$a + $b*$b);
   if($c<=$BoldNess)
   {
    imageline($resource, $x1 +$i, $y1+$j, $x2 +$i, $y2+$j, $Color);
   }
  }
 }        
} 




	
include_once("../ki_config/ki_setup.php");

if($ot == 0){
	$polygon = array(25,25, 5,15, 25,5);
	$polygon2 = array(26,25, 5,15, 26,5);
}elseif($ot == 1){
	$polygon = array(25,25, 15,5, 5,25);
	$polygon2 = array(25,26, 15,5, 5,26);
}elseif($ot == 2){
	$polygon = array(5,25, 25,15, 5,5);
	$polygon2 = array(4,25, 25,15, 4,5);
}else{
	$polygon = array(25,5, 15,25, 5,5);
	$polygon2 = array(25,4, 15,25, 5,4);
}


header("Content-type: image/png"); //Picture Format 
header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE


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

if($browser === "ie6"){
	$bild = imagecreatetruecolor(30, 30);
} else {
	$bild = imagecreate(30, 30);
}

imagealphablending($bild, false);
imagesavealpha($bild, true);

if(function_exists('imageantialias')){
	imageantialias($bild, true);
}

$transparent = imagecolorallocatealpha($bild, 0, 0, 0, 127);
imagefilledrectangle($bild, 0, 0, 30, 30, $transparent);
imagecolortransparent($bild, $transparent);

$r = hexdec(substr($bord_color, 1, 2));
$g = hexdec(substr($bord_color, 3, 2));
$b = hexdec(substr($bord_color, 5, 2));
$farbe = imagecolorallocatealpha($bild, $r, $g, $b, 0);
imagefilledpolygon($bild, $polygon2, 3, $farbe);

$r = hexdec(substr($nav_border_color, 1, 2));
$g = hexdec(substr($nav_border_color, 3, 2));
$b = hexdec(substr($nav_border_color, 5, 2));
$farbe = imagecolorallocatealpha($bild, $r, $g, $b, 255);

for($i = 0; $i < 3; $i+=2){
	imageBoldLine($bild, $polygon[$i], $polygon[$i+1], $polygon[$i+2], $polygon[$i+3], $farbe, 2);
}

imagepng($bild);
imagedestroy($bild);
?>