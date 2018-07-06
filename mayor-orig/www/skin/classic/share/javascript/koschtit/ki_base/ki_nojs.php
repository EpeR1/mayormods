<?php
ob_start("ob_gzhandler");

//----------------------------------------------------------------------------- functions --------------------------------------------------------------------------------
function draw_image($filename, $id, $style, $params) {
	global $browser, $basedir;
	$idstring = "";
	if($id != "")$idstring = "id='".$id."' ";
	if($browser == "ie6") {
		$imgsize = getimagesize($filename);
		echo "<img ".$idstring."style='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".$filename."); width:".$imgsize[0]."px; height:".$imgsize[1]."px; ".$style."' src='".$basedir."ki_noimage.gif' ".$params." />";
	} else {
		if($style != "")$style = " style='".$style."' ";
		echo "<img ".$idstring."src='".$filename."'".$style.$params." />";
	}
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $f = @fopen($filename, 'w');
        if (!$f) {
            return false;
        } else {
            $bytes = fwrite($f, $data);
            fclose($f);
            return $bytes;
        }
    }
}

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		if ($handle = @fopen($filename, 'rb')) {
			$data = fread($handle, filesize($filename));
			fclose($fh);
			return $data;
		}
	}
}

function cmp_0($a, $b)
{
	global $galleryfolder;
    return (filemtime($galleryfolder.$a[0]) < filemtime($galleryfolder.$b[0])) ? -1 : 1;
}

function cmp_1($a, $b)
{
	global $galleryfolder;
    return (filemtime($galleryfolder.$a[0]) > filemtime($galleryfolder.$b[0])) ? -1 : 1;
}

function cmp_2($a, $b)
{
	global $galleryfolder;
	
	$exif = @exif_read_data($galleryfolder.$a[0]);
	$date1 = "";
    if (isset($exif['DateTimeOriginal']))
        $date1 = $exif['DateTimeOriginal'];
    if (empty($date1) && isset($exif['DateTime']))
        $date1 = $exif['DateTime'];
    if (!empty($date1)){
        $date1 = explode(':', str_replace(' ',':', $date1));
        $date1 = "{$date1[0]}-{$date1[1]}-{$date1[2]} {$date1[3]}:{$date1[4]}";
		if(sizeof($date1) > 5)$date1 .= ":{$date1[5]}";
		$date1 = strtotime($date1);
	} else {
		$date1 = filemtime($galleryfolder.$a[0]);
	}
	$exif = @exif_read_data($galleryfolder.$b[0]);
	$date2 = "";
    if (isset($exif['DateTimeOriginal']))
        $date2 = $exif['DateTimeOriginal'];
    if (empty($date2) && isset($exif['DateTime']))
        $date2 = $exif['DateTime'];
    if (!empty($date2)){
        $date2 = explode(':', str_replace(' ',':', $date2));
        $date2 = "{$date2[0]}-{$date2[1]}-{$date2[2]} {$date2[3]}:{$date2[4]}";
		if(sizeof($date2) > 5)$date2 .= ":{$date1[5]}";
		$date2 = strtotime($date2);
	} else {
		$date2 = filemtime($galleryfolder.$b[0]);
	}	
	
    return $date1 > $date2 ? 1 : -1;
}

function cmp_3($a, $b)
{
	return strcmp($a[0], $b[0]);
}

//-------------------------------------------------------------------------- end functions --------------------------------------------------------------------------------

@ini_set("default_charset", "utf-8");
header('Content-type: text/html; charset=utf-8'); 

$browser = $_SERVER['HTTP_USER_AGENT'];

if(preg_match("/Opera/",$browser))
	$browser = "opera"; 
elseif(preg_match("/MSIE [9]/",$browser))
	$browser = "ie9";
elseif(preg_match("/MSIE [7-8]/",$browser))
	$browser = "ie7";
elseif(preg_match("/MSIE [1-6]/",$browser))
	$browser = "ie6";
elseif(preg_match("/AppleWebKit/",$browser))
	$browser = "webkit";
else
	$browser = "gecko";
	
if($browser == "ie6")
	$posfix = "absolute";
else
	$posfix = "fixed";

$supported = array("jpg","png","gif");

if(isset($_GET['gallery']))
	$gallery = $_GET['gallery'];
else
	exit();

if(isset($_GET['site']))
	$site = $_GET['site'];
else
	exit();
	
if(isset($_GET['startfrom']))
	$startfrom = $_GET['startfrom'];
else
	$startfrom = 0;
	
if(isset($_GET['explorer']))
	$explorer = 1;
else
	$explorer = 0;

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $gallery))exit();
// ---------- Ende Sicherheitsabfragen!

include_once("../ki_config/ki_setup.php");
if(is_file("../ki_config/".$gallery."_ki_setup.php")){
	include_once("../ki_config/".$gallery."_ki_setup.php");
	$configfile = "../ki_config/".$gallery."_ki_setup.php";
} else {
	$configfile = "../ki_config/ki_setup.php";
}
$galleryfolder = "../ki_galleries/".$gallery."/";
$thumbsfolder = $galleryfolder."thumbs/";
$commentsfolder = $galleryfolder."comments/";

$temp = getimagesize("ki_nav_next.png");
if($nav_always == 1 && $show_nav == 1)$fr_height -= ($temp[1]+18);
if($th_lines == "auto")$th_lines = ceil($thumbs/($th_per_line));
if($th_width == "auto")$th_width = round($fr_width/($th_per_line)) - round($fr_height*0.04) - 4;
if($th_height == "auto")$th_height = round($fr_height/($th_lines)) - round($fr_height*0.04) - 4;
$th_width = $th_width - 2*$th_bord_size;
$th_height = $th_height - 2*$th_bord_size;
if(($th_lines*$th_per_line) < $thumbs)$thumbs = $th_lines*$th_per_line;

$zeile = 1;
$spalte = 0;

$spaltenbreite = $fr_width/($th_per_line);
$zeilenhoehe = $fr_height/($th_lines);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="koschtit_version" content="KoschtIT Image Gallery v3.1b by Konstantin Tabere" />
<title>KoschtIT Image Gallery: <?php echo $gallery ?></title>
<style type="text/css">
a.nav:hover img{
	top:-2px;
}

a.pic {
	<?php if($th_to_square == 0) { ?>
	line-height:<?php echo $th_height+$explorer_padding+2*$th_bord_size ?>px;
	<?php } ?>
}
a.pic img {
	border:<?php echo $th_bord_size ?>px solid <?php echo $th_bord_color ?>;
	<?php if($explorer == 1)echo "margin:".(0.5*$explorer_padding)."px;" ?>
	vertical-align:middle;
}
a.pic:hover img {
	border-color:<?php echo $th_bord_hover_color ?>;
	<?php if(in_array($browser, array("ie9", "opera", "gecko", "webkit"))){ ?>
    box-shadow:0px 0px 10px #000;
	<?php } ?>
}
</style>
</head>
<body style="background:<?php echo $fr_color ?>; padding:0px; margin:0px;">


<?php

/*------------------- error/warning checking ------------------*/
if(!function_exists('imagecreatetruecolor')){
		echo "<div style='background:#ffbbbb; color:#000000; padding:4px;'>ERROR: KoschtIT Image Gallery can't find the PHP GD2 Library available. Please make sure you have removed the semicolon from this line ';extension=php_gd2.dll' in your php.ini and the library is correctly installed.</div>";
}
if(!is_dir($galleryfolder)) {
	echo "<div style='background:#ffbbbb; color:#000000; padding:4px;'>ERROR: KoschtIT Image Gallery can't find the following folder on the server: '".htmlentities($gallery)."' . Please check if the folder is available in the 'ki_galleries' folder.</div>";
	exit();
}
if($pic_order == 3){
	if(!function_exists("exif_read_data")){
		$pic_order = 2;
	}
}
/*------------------- end error/warning checking ------------------*/

/*------------------- config settings ------------------*/
$savefile = $gallery."_lastmodified";
$lastmodified = filemtime($configfile);
$saved = $fr_width.$fr_height.$thumbs.$th_per_line.$th_lines.$th_width.$th_height.$th_bord_size.$th_bord_hover_increase.$th_to_square.$th_2sq_crop_vert.$th_2sq_crop_hori.$show_nav.$nav_always;
$writestring  = "<?php\r\n\$lm_saved = \"".$saved."\";\r\n";
$writestring .= "\$lm_lastmodified = ".$lastmodified.";\r\n?>";
if(!file_exists($savefile))@file_put_contents($savefile, $writestring);
if(!file_exists($savefile)) {
	$thumbs_to_disk = 0;
} else {
	include_once($savefile);
	if($lm_saved !== $saved){
		@file_put_contents($savefile, $writestring);
		$matches = @glob($thumbsfolder."*.*", GLOB_ERR);
		if(is_array($matches)){
			foreach($matches as $sf) {
				if(!is_dir($sf) && !is_link($sf)){
					@unlink($sf);
				} 
			}
		}
	} else {
		$lastmodified = $lm_lastmodified;
	}
}
/*------------------- end config settings ------------------*/

$files = array();
$temp = array();
$savedfolderhash = 0;
if(is_file($gallery."_dir")){
	$temp = explode(PHP_EOL, file_get_contents($gallery."_dir"));
	$savedfolderhash = unserialize($temp[0]);
}
//$folderhash = pic_order + MTime of $galleryfolder + fileSize of all files
$folderhash = $pic_order;
$iterator = new DirectoryIterator($galleryfolder);
foreach ($iterator as $fileInfo) {
    if($fileInfo->isDot()){
		$folderhash += $fileInfo->getMTime();
		continue;
	} elseif($fileInfo->isFile()) {
		$folderhash += $fileInfo->getSize();
	}
}
if($folderhash != $savedfolderhash){
	if($pic_order == 4 && sizeof($temp) > 1){
		$files = unserialize($temp[1]);
		$iterator->rewind();
		foreach ($iterator as $fileInfo) {
			$file = $fileInfo->getFilename();
			if(!in_array(strtolower(substr($file, -3)), $supported))$continue;
			$imgsize = @getimagesize($galleryfolder.$file);
			if($imgsize[0]){
				$newcandidate = array($file, $imgsize[0], $imgsize[1]);
				if(!in_array($newcandidate, $files))$files[] = $newcandidate;
			}
		}
		reset($files);
	} else {
		$iterator->rewind();
		foreach ($iterator as $fileInfo) {
			$file = $fileInfo->getFilename();
			if(!in_array(strtolower(substr($file, -3)), $supported))$continue;
			$imgsize = @getimagesize($galleryfolder.$file);
			if($imgsize[0]){
				$files[] = array($file, $imgsize[0], $imgsize[1]);
			}
		}
		switch($pic_order){
			case 0:
				usort($files, "cmp_1");
			break;
			case 1:
				usort($files, "cmp_0");
			break;
			case 2:
				usort($files, "cmp_3");
			break;
			case 3:
				usort($files, "cmp_2");
			break;
			default:
				usort($files, "cmp_1");
			break;
		}
		reset($files);
	}
} else {
	$files = unserialize($temp[1]);
}

if($explorer == 1){
	$temp = getimagesize("ki_nav_close.png");
?>
<div style="position:<?php echo $posfix ?>; left:50%; top:-20px; background:<?php echo $nav_color ?>; <?php if($nav_style == 2){ ?>border-radius:20px; -moz-border-radius:20px; -webkit-border-radius:20px;<?php } ?> border:2px solid <?php echo $nav_border_color ?>; z-index:10000; padding:22px 3px 3px 3px; margin-left:-<?php echo 0.5*($temp[0]+14) ?>px;">
	<a class="nav" href="<?php echo $site ?>"><?php draw_image("ki_nav_close.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_kiv_close), ENT_QUOTES, "UTF-8")."'") ?></a>
</div>
<?php	
	echo "<div style='text-align:center; padding:".($explorer_padding*0.5)."px;'>";
}


$id = 0;
foreach ($files as $file) {
	$id++;
	if($id > $startfrom) {
	
		$spalte++;
		if($spalte == $th_per_line+1){
			$zeile++;
			$spalte = 1;
		}

		$breite = $file[1];
		$hoehe = $file[2];
		
		if( ($breite / $hoehe) > 1){
			$k = $hoehe / $breite;
			$breite = $th_width;
			$hoehe = $k*$breite;
			if($hoehe > $th_height){
				$hoehe = $th_height;
				$breite = (1/$k) * $hoehe;
			}
		} else {
			$k = $breite / $hoehe;
			$hoehe = $th_height;
			$breite = $k*$hoehe;
			if($breite > $th_width){
				$breite = $th_width;
				$hoehe = (1/$k) * $breite;
			}
		}
								
		if($th_to_square == 1) {
			if($th_width < $th_height){
				$breite = $th_width;
			} else {
				$breite = $th_height;
			}
			$hoehe = $breite;
		}
		
		$x_pos = round($spaltenbreite*($spalte - 0.5) - 0.5*$breite) - $th_bord_size;
		$y_pos = round($zeilenhoehe*($zeile - 0.5) - 0.5*$hoehe) - $th_bord_size;
		
		$breite = round($breite);
		$hoehe = round($hoehe);
	
		if($th_bord_hover_increase > 1){
			$inc_breite = round($breite*$th_bord_hover_increase);
			$inc_hoehe = round($breite*$th_bord_hover_increase);
		} else {
			$inc_breite = $breite;
			$inc_hoehe = $hoehe;	
		}

		if(($zeile <= $th_lines && ($id-$startfrom) <= $thumbs) || $explorer == 1)
		{
			$src = "ki_makepic.php?file=".$gallery."/".rawurlencode($file[0])."&width=".$inc_breite."&height=".$inc_hoehe;
			$style = "width:".$breite."px; height:".$hoehe."px;";
			$exp = "";
			if($explorer == 0){
				$style .= " position:absolute; left:".$x_pos."px; top:".$y_pos."px;";
			} else {
				$exp .= "&explorer=1";	
			}
			if($thumbs_to_disk == 1){
				if(!is_file($thumbsfolder.$lastmodified.$file[0])){
					$src .= "&picname=".$lastmodified.rawurlencode($file[0]);
				} else {
					$src = "../ki_galleries/".$gallery."/thumbs/".$lastmodified.$file[0];
				}
			}
			echo "<a class='pic' href='ki_nojsdisplayimage.php?fileno=".($id-1)."&gallery=".$gallery."&site=".$site.$exp."' target='_top'><img src='".$src."' style='".$style."' /></a>";
		}
	}
}


if($explorer == 0 && $show_nav == 1){
	$breite = 0;
	if($nav_pos === "right"){
		$xpos = "right:3px;";
	} elseif($nav_pos === "left"){
		$xpos = "left:3px;";
	} else {
		$temp = getimagesize("ki_nav_close.png");
		$temp = $temp[0]+4;
		$breite = $temp;
		if($slideshow == 1 && $id > 1)$breite += $temp;
		if($startfrom != 0)$breite += $temp;
		if(($id-$startfrom) > $thumbs)$breite += $temp;
		$xpos = "left:50%; margin-left:-".(0.5*($breite+10))."px;";
	}
?>
<div style="position:<?php echo $posfix ?>; <?php echo $xpos ?> bottom:4px; background:<?php echo $nav_color ?>; <?php if($nav_style == 2){ ?>border-radius:20px; -moz-border-radius:20px; -webkit-border-radius:20px;<?php } ?> border:2px solid <?php echo $nav_border_color ?>; z-index:10000; padding:3px;">
<a class='nav' href='ki_nojs.php?gallery=<?php echo $gallery ?>&amp;site=<?php echo $site ?>&explorer=1' target='_top' style='float:left;'><?php draw_image("ki_nav_full.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_maxi), ENT_QUOTES, "UTF-8")."'") ?></a>
<?php
if($slideshow == 1 && $id > 1){
?>
<a class='nav' href='ki_nojsdisplayimage.php?fileno=0&amp;gallery=<?php echo $gallery ?>&amp;site=<?php echo $site ?>&ss=1' target='_top' style='float:left;'><?php draw_image("ki_nav_play.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($slideshow_start), ENT_QUOTES, "UTF-8")."'") ?></a>
<?php
}
if($startfrom != 0){
?>
<a class='nav' href='ki_nojs.php?gallery=<?php echo $gallery ?>&amp;site=<?php echo $site ?>&startfrom=<?php echo ($startfrom-$thumbs) ?>' style='float:left;'><?php draw_image("ki_nav_prev.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_back), ENT_QUOTES, "UTF-8")."'") ?></a>
<?php
}
if(($id-$startfrom) > $thumbs){
?>
<a class='nav' href='ki_nojs.php?gallery=<?php echo $gallery ?>&amp;site=<?php echo $site ?>&startfrom=<?php echo ($startfrom+$thumbs) ?>' style='float:left;'><?php draw_image("ki_nav_next.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_next), ENT_QUOTES, "UTF-8")."'") ?></a>
<?php
}
?>
</div>
<?php
}


if($explorer == 1)echo "</div>";
?>


</body>
</html>