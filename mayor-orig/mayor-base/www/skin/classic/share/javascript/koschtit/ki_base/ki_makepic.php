<?php
// -------------------------------------------------------- functions --------------------------------------------------------------

//------------------ Start Caching ---------------------------------
if(!ini_get('date.timezone') && function_exists("date_default_timezone_set"))date_default_timezone_set('Europe/Berlin');
$mtime = filemtime($_SERVER['SCRIPT_FILENAME']);
$gmt_mtime = gmdate('D, d M Y H:i:s', $mtime).' GMT';
header('ETag: "'.md5($mtime.$_SERVER['SCRIPT_FILENAME']).'"');
if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
{
	if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime)
	{
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
}
if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
{
	if (str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == md5($mtime.$_SERVER['SCRIPT_FILENAME']))
	{
		header("HTTP/1.1 304 Not Modified");
		exit();
	}
}
//------------------ Ende Caching ---------------------------------

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		if ($handle = @fopen($filename, 'rb')) {
			$data = fread($handle, filesize($filename));
			fclose($fh);
			return $data;
		}
	}
}

function getMemLimit(){
	if(ini_get("memory_limit"))return return_bytes(ini_get("memory_limit")) - memory_get_usage();
	if(ini_get("se_memory_limit"))return return_bytes(ini_get("se_memory_limit")) - memory_get_usage();
	return -1;
}


function return_bytes($size_str){
    switch (substr ($size_str, -1))
    {
        case 'M': case 'm': return (int)$size_str * 1048576;
        case 'K': case 'k': return (int)$size_str * 1024;
        case 'G': case 'g': return (int)$size_str * 1073741824;
        default: return $size_str;
    }
}

function getNeededMemoryForImageCreate($width, $height) {
  return $width*$height*5.2;
}

function imagestringwrap($image, $font, $x, $y, $text, $color, $maxwidth){
	
    $fontwidth = imagefontwidth($font);
    $fontheight = imagefontheight($font);

    if ($maxwidth != NULL){
        $maxcharsperline = floor($maxwidth / $fontwidth);
        $text = wordwrap($text, $maxcharsperline, "\n", 1);
    }

    $lines = explode("\n", $text);
    while (list($numl, $line) = each($lines)) {
        imagestring($image, $font, $x, $y, $line, $color);
        $y += $fontheight;
    }
}

// -------------------------------------------------------- end functions --------------------------------------------------------------

include_once("../ki_config/ki_setup.php");

if(isset($_GET['file']))
	$file = rawurldecode($_GET['file']);
else
	exit();

$gallery = substr($file, 0, strpos($file, "/"));
$file = substr($file, strpos($file, "/")+1);

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $file))exit();
if(preg_match("/[\.]*\//", $gallery))exit();
if(!is_file("../ki_galleries/".$gallery."/".$file))exit();
// ---------- Ende Sicherheitsabfragen!

if(is_file("../ki_config/".$gallery."_ki_setup.php"))
	include_once("../ki_config/".$gallery."_ki_setup.php");

$filename = $file;
$file = "../ki_galleries/".$gallery."/".$file;
$imgsize = getimagesize($file);

if(isset($_GET['height']))
	$hoehe = $_GET['height'];
else
	$hoehe = 100;

if(isset($_GET['width']))
	$breite = $_GET['width'];
else
	$breite = $imgsize[0]/$imgsize[1]*$hoehe;

$src_x = 0;
$src_y = 0;
$src_w = $imgsize[0];
$src_h = $imgsize[1];
	
if($th_to_square == 1 && isset($_GET['width']) && !isset($_GET['fullimg'])){
	if($imgsize[0]/$imgsize[1] >= 1)
		$wide = 1;
	else
		$wide = 0;
	
	$custom = -1;
	if(is_file("custom")){
		$content = @file_get_contents("custom");
		$pictures = explode("|", $content);
		$picname = $gallery."_".$filename;
		for($i = 0; $i < count($pictures); $i++){
			if(strpos($pictures[$i], $picname) !== FALSE){
				$custom = substr($pictures[$i], strpos($pictures[$i], "=") + 1);
			}
		}
	}
	
	if($custom == -1){		
		switch($th_2sq_crop_vert){
			case "top":
				$src_y = 0;
				if($wide == 1)
					$src_h = $imgsize[1];
				else
					$src_h = $imgsize[0];
			break;
			case "middle";
				if($wide == 1){
					$src_y = 0;
					$src_h = $imgsize[1];
				} else {
					$src_y = $imgsize[1]*0.5-0.5*$imgsize[0];
					$src_h = $imgsize[0];
				}
			break;
			case "bottom":
				if($wide == 1){
					$src_y = 0;
					$src_h = $imgsize[1];
				} else {
					$src_y = $imgsize[1]-$imgsize[0];
					$src_h = $imgsize[0];
				}
			break;
		}
		switch($th_2sq_crop_hori){
			case "left":
				$src_x = 0;
				if($wide == 1)
					$src_w = $imgsize[1];
				else
					$src_w = $imgsize[0];
			break;
			case "center";
				if($wide == 1){
					$src_x = $imgsize[0]*0.5-0.5*$imgsize[1];
					$src_w = $imgsize[1];
				} else {
					$src_x = 0;
					$src_w = $imgsize[0];
				}
			break;
			case "right":
				if($wide == 1){
					$src_x = $imgsize[0]-$imgsize[1];
					$src_w = $imgsize[1];
				} else {
					$src_x = 0;
					$src_w = $imgsize[0];
				}
			break;
		}
	} else {
		if($wide == 1){
			$src_x = $custom*$imgsize[0];
			$src_y = 0;
			$src_w = $imgsize[1];
			$src_h = $imgsize[1];
		} else {
			$src_y = $custom*$imgsize[1];
			$src_x = 0;
			$src_h = $imgsize[0];
			$src_w = $imgsize[0];		
		}
	}
}


$limit = getMemLimit();
$need = getNeededMemoryForImageCreate($imgsize[0], $imgsize[1]);
if($need > $limit){
	while($limit < $need){
		if(!@ini_set("memory_limit", strval(round(return_bytes(ini_get("memory_limit"))*2))) || $limit < 0){
			if(!isset($_GET['fullimg'])){
				$im = imagecreate($breite, $hoehe);
				$bg = imagecolorallocate($im, 255, 255, 255);
				imagefill($im, 0, 0, $bg);
				$textcolor = imagecolorallocate($im, 255, 0, 0);
				imagestringwrap($im, 2, 2, 2, "ERROR: '".substr($file, strrpos($file, "/")+1)."' needs too much memory to create this thumb. Please reduce it's size by hand.", $textcolor, $breite-2);
				header('Content-type: image/png');
				imagepng($im);
				imagedestroy($im);
				exit();	
			} else {
				header("Location: ".$file);
				exit();
			}
		} else {
			$limit = return_bytes(ini_get("memory_limit")) - memory_get_usage();
		}
	}
}

ini_set("gd.jpeg_ignore_warning", true);

switch(strtolower(substr($file, -3))){
	case "jpg":
		$image = @imagecreatefromjpeg($file);	
	break;
	case "png":
		$image = @imagecreatefrompng($file);
	break;
	case "gif":
		$image = @imagecreatefromgif($file);
	break;
	default:
	exit;
	break;
}

$limit = getMemLimit();
$need = getNeededMemoryForImageCreate($breite, $hoehe);
if($need > $limit){
	while($limit < $need){
		if(!@ini_set("memory_limit", strval(round(return_bytes(ini_get("memory_limit"))*2))) || $limit < 0){
			if(!isset($_GET['fullimg'])){
				$im = imagecreate($breite, $hoehe);
				$bg = imagecolorallocate($im, 255, 255, 255);
				imagefill($im, 0, 0, $bg);
				$textcolor = imagecolorallocate($im, 255, 0, 0);
				imagestringwrap($im, 2, 2, 2, "ERROR: '".substr($file, strrpos($file, "/")+1)."' needs too much memory to create this thumb. Please reduce it's size by hand.", $textcolor, $breite-2);
				header('Content-type: image/png');
				imagepng($im);
				imagedestroy($im);
				exit();	
			} else {
				header("Location: ".$file);
				exit();
			}
		} else {
			$limit = return_bytes(ini_get("memory_limit")) - memory_get_usage();
		}
	}
}

$bild = imagecreatetruecolor($breite, $hoehe);
imagealphablending($bild, false);
imagesavealpha($bild, true);
imagecopyresampled($bild, $image, 0, 0, $src_x, $src_y, $breite, $hoehe, $src_w, $src_h); 
imagedestroy($image);

if(isset($_GET['picname'])){
	$picname = rawurldecode($_GET['picname']);
	// -------------- Sicherheitsabfragen!
	if(preg_match("/[\.]*\//", $picname))exit();
	// ---------- Ende Sicherheitsabfragen!
	switch(strtolower(substr($file, -3))){
		case "jpg":
			@imagejpeg($bild, "../ki_galleries/".$gallery."/thumbs/".$_GET['picname'], 80);
		break;
		case "png":
			@imagepng($bild, "../ki_galleries/".$gallery."/thumbs/".$_GET['picname']);
		break;
		case "gif":
			@imagegif($bild, "../ki_galleries/".$gallery."/thumbs/".$_GET['picname']);
		break;
	}
} else {
	//------------------ Start Caching ---------------------------------
	header('Last-Modified: '.$gmt_mtime);
	header('Cache-Control: public');
	header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 month')).' GMT');
	//------------------ Ende Caching ---------------------------------
}

switch(strtolower(substr($file, -3))){
	case "jpg":
		header("Content-Type: image/jpeg");
		imagejpeg($bild, "", 80);
	break;
	case "png":
		header("Content-Type: image/png");
		imagepng($bild);
	break;
	case "gif":
		header("Content-Type: image/gif");
		imagegif($bild);
	break;
}

imagedestroy($bild);
?>