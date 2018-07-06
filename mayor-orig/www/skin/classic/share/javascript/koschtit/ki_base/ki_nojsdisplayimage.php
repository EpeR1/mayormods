<?php
ob_start("ob_gzhandler");

/* --------------------------------------------------------- functions ------------------------------------------------------ */

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

function is_utf8($str) {
    $c=0; $b=0;
    $bits=0;
    $len=strlen($str);
    for($i=0; $i<$len; $i++){
        $c=ord($str[$i]);
        if($c > 128){
            if(($c >= 254)) return false;
            elseif($c >= 252) $bits=6;
            elseif($c >= 248) $bits=5;
            elseif($c >= 240) $bits=4;
            elseif($c >= 224) $bits=3;
            elseif($c >= 192) $bits=2;
            else return false;
            if(($i+$bits) > $len) return false;
            while($bits > 1){
                $i++;
                $b=ord($str[$i]);
                if($b < 128 || $b > 191) return false;
                $bits--;
            }
        }
    }
    return true;
}

function utf8_encode_safe($string){
	
	if( is_utf8($string) )
		return $string;
	else
		return utf8_encode($string);
}

/* ---------------------------------------------------------- end functions -------------------------------------------------- */

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

/* --------------------------------------------------------- Display full image ---------------------------------------------------------------------- */

@ini_set("default_charset", "utf-8");
header('Content-type: text/html; charset=utf-8'); 

if(isset($_GET['fileno']))
	$fileno = $_GET['fileno'];
else
	exit;

if(isset($_GET['gallery']))
	$gallery = $_GET['gallery'];
else
	exit;

if(isset($_GET['site']))
	$site = $_GET['site'];
else
	exit;
	
if(isset($_GET['explorer']))
	$explorer = 1;
else
	$explorer = 0;
	
if(isset($_GET['ss']))
	$ss = 1;
else
	$ss = 0;

// -------------- Sicherheitsabfragen!
if(!is_int(intval($fileno)))exit();
if(preg_match("/[\.]*\//", $gallery))exit();
// ---------- Ende Sicherheitsabfragen!

include_once("../ki_config/ki_setup.php");
if(is_file("../ki_config/".$gallery."_ki_setup.php"))include_once("../ki_config/".$gallery."_ki_setup.php");

$supported = array("jpg","png","gif");

$galleryfolder = "../ki_galleries/".$gallery."/";

/*------------------- error/warning checking ------------------*/
if(!is_dir($galleryfolder)) {
	echo "<div style='background:#ffbbbb; color:#000000; padding:4px;'>ERROR: KoschtIT Image Gallery could't find the following folder on the server: '".htmlentities($gallery)."' . Please check if the folder is available in the 'ki_galleries' folder.</div>";
	exit();
}
if($pic_order == 3){
	if(!function_exists("exif_read_data")){
		$pic_order = 2;
	}
}
/*------------------- end error/warning checking ------------------*/

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

$file = $files[$fileno][0];

$x = $files[$fileno][1];
$y = $files[$fileno][2];

$xlimiter = 10000;
$ylimiter = 10000;
if(is_int($max_pic_width)){
	$xlimiter = $max_pic_width;
}
if(is_int($max_pic_height)){
	$ylimiter = $max_pic_height;
}
if($y > $ylimiter || $x > $xlimiter){
	if(($x / $y) > 1){
		$k = $y / $x;
		$x = $xlimiter-2*$bord_size;
		$y = $k*$x;
		if($y > $ylimiter-2*$bord_size){
			$y = $ylimiter-2*$bord_size;
			$x = (1/$k) * $y;
		}
	} else {
		$k = $x / $y;
		$y = $ylimiter-2*$bord_size;
		$x = $k*$y;
		if($x > $xlimiter-2*$bord_size){
			$x = $xlimiter-2*$bord_size;
			$y = (1/$k) * $x;
		}
	}
	$x = round($x);
	$y = round($y);
}

$commfile = "../ki_galleries/".$gallery."/comments/".substr($file, 0, -4).".txt";
$srcfile = $gallery."/".$file;

if($x != $files[$fileno][1] || $y != $files[$fileno][2]){
	$srcfile = "ki_makepic.php?fullimg=1&file=".$srcfile."&width=".$x."&height=".$y;
} else {
	$srcfile = "../ki_galleries/".$srcfile;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="koschtit_version" content="KoschtIT Image Gallery v3.1b by Konstantin Tabere" />
<?php
$next = $fileno+1;
if($next == count($files))$next = 0;
if($ss == 1 && $slideshow == 1){ 
?>
<meta http-equiv="refresh" content="<?php echo $slideshow_time/1000 ?>; url=ki_nojsdisplayimage.php?fileno=<?php echo $next ?>&gallery=<?php echo $gallery ?>&site=<?php echo $site;if($explorer == 1)echo "&explorer=1"; ?>&ss=1"> 
<?php
}
?>
<title>KoschtIT Image Gallery: <?php echo $gallery ?></title>
<style type="text/css">
a.nav:hover img{
	top:-2px;
}
</style>
</head>
<body style="background:<?php echo $fade_color ?>; padding:0px; margin:0px;">

<?php
if($show_image_nav == 1){
	$temp = getimagesize("ki_nav_close.png");
	if(count($files > 1))
		$breite = ($temp[0]+4)*3;
	else
		$breite = ($temp[0]+4)*2;
	if($downloadpics == 1)$breite += ($temp[0]+4);
	if($slideshow == 1)$breite += ($temp[0]+4);	
?>
<div style="position:<?php echo $posfix ?>; left:50%; top:-20px; background:<?php echo $nav_color ?>; <?php if($nav_style == 2){ ?>border-radius:20px; -moz-border-radius:20px; -webkit-border-radius:20px;<?php } ?> border:2px solid <?php echo $nav_border_color ?>; z-index:10000; padding:22px 3px 3px 3px; margin-left:-<?php echo 0.5*($breite+10) ?>px;">
    <a class="nav" href="<?php echo ($explorer == 0) ? $site : "ki_nojs.php?gallery=".$gallery."&site=".$site."&explorer=1"; ?>" style="float:left;"><?php draw_image("ki_nav_close.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_kiv_close), ENT_QUOTES, "UTF-8")."'") ?></a>
	<?php 
	if($slideshow == 1){
		if($ss == 1){
			$sstitle = $slideshow_stop;
			$sspic = "ki_nav_stop";
			$sshref = "?fileno=".$fileno."&gallery=".$gallery."&site=".$site;
		} else {
			$sstitle = $slideshow_start;
			$sspic = "ki_nav_play";
			$sshref = "?fileno=".$next."&gallery=".$gallery."&site=".$site."&ss=1";
		}
		if($explorer == 1)$sshref .= "&explorer=1";
	?>
    <a class="nav" href="<?php echo $sshref ?>" style="float:left;"><?php draw_image($sspic.".png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($sstitle), ENT_QUOTES, "UTF-8")."'") ?></a>
	<?php
    }
	?>
	<?php if($downloadpics == 1){ ?>
    <a class="nav" href="ki_download.php?gallery=<?php echo $gallery ?>&file=<?php echo $file ?>" style="float:left;" target="_blank"><?php draw_image("ki_nav_download.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_kiv_download), ENT_QUOTES, "UTF-8")."'") ?></a>
	<?php } ?>
	<?php
	$next = $fileno+1;
    $prev = $fileno-1;
	if(count($files) > 1){
        if($next == count($files))$next = 0;
        if($prev == -1)$prev = count($files)-1;
    ?>
    <a class="nav" href="?fileno=<?php echo $prev ?>&gallery=<?php echo $gallery ?>&site=<?php echo $site;if($explorer == 1)echo "&explorer=1"; ?>" style="float:left;"><?php draw_image("ki_nav_prev.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_kiv_back), ENT_QUOTES, "UTF-8")."'") ?></a>
    <a class="nav" href="?fileno=<?php echo $next ?>&gallery=<?php echo $gallery ?>&site=<?php echo $site;if($explorer == 1)echo "&explorer=1"; ?>" style="float:left;"><?php draw_image("ki_nav_next.png", "", "display:block; border:0px; margin:0px 2px 0px 2px; position:relative;", "title='".htmlentities(stripslashes($nav_kiv_next), ENT_QUOTES, "UTF-8")."'") ?></a>
    <?php 
    }
    ?>
</div>
<?php
}
?>

<div style="position:<?php echo $posfix ?>; left:50%; top:50%; margin-left:-<?php echo round($x*0.5+$bord_size) ?>px; margin-top:-<?php echo round($y*0.5+$bord_size) ?>px; width:<?php echo ($x+2*$bord_size) ?>px; height:<?php echo ($y+2*$bord_size) ?>px; background:<?php echo $bord_color ?>"> 
<a href="?fileno=<?php echo $next ?>&gallery=<?php echo $gallery ?>&site=<?php echo $site;if($explorer == 1)echo "&explorer=1"; ?>" style="display:block; text-decoration:none;">
<?php
$addshadow = "";	
if(in_array($browser, array("ie9", "opera", "gecko", "webkit"))){
$addshadow = "box-shadow:inset 0px 0px 10px #000; ";
}
echo "<img src='".$srcfile."' style='border:none; padding:".$bord_size."px;' /><div style='width:".$x."px; height:".$y."px; z-index:1000; ".$addshadow."position:absolute; top:0px; left:0px; border:".$bord_size."px solid ".$bord_color."'></div>";
?>
</a>
<?php
if(is_file($commfile)){
	$comm = file_get_contents($commfile);
} else {
	$comm = "";
}

if($comm_auto == 1){
	$comm = $comm_auto_string;
	$comm = str_replace("%x", ($fileno+1), $comm);
	$comm = str_replace("%X", count($files), $comm);
	$comm = str_replace("%g", $gallery, $comm);
	$comm = str_replace("%f", substr($file, 0, -4), $comm);
}

if($comments == 1){
	if($comm != ""){
		$pos = "left:0px;";
		if($comm_text_align === "left"){
			echo "<div style='position:absolute; left:0px; top:100%; background:".$bord_color."; padding:10px; overflow:hidden; font:".$comm_text_size."px ".$comm_text_font."; color:".$comm_text_color."; margin-top:5px; text-align:".$comm_text_align.";'>".utf8_encode_safe(stripslashes($comm))."</div>";
		}elseif($comm_text_align === "right"){
			echo "<div style='position:absolute; right:0px; top:100%; background:".$bord_color."; padding:10px; overflow:hidden; font:".$comm_text_size."px ".$comm_text_font."; color:".$comm_text_color."; margin-top:5px; text-align:".$comm_text_align.";'>".utf8_encode_safe(stripslashes($comm))."</div>";
		}else{
			echo "<div style='position:absolute; left:50%; top:100%;'><div style='position:relative; left:-50%; background:".$bord_color."; padding:10px; overflow:hidden; font:".$comm_text_size."px ".$comm_text_font."; color:".$comm_text_color."; margin-top:5px; text-align:".$comm_text_align.";'>".utf8_encode_safe(stripslashes($comm))."</div></div>";
		}
	}
}
?>
</div>
</body>
</html>