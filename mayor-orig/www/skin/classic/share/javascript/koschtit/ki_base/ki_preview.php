<?php
ob_start("ob_gzhandler");

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

$reldir = "";
if(isset($_POST['reldir']))$reldir = $_POST['reldir'];
$confdir = $reldir."ki_config/";
$galleriesdir = $reldir."ki_galleries/";
$basedir = $reldir."ki_base/";

//----------------------------------------------------------------------------- functions --------------------------------------------------------------------------------

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


if(isset($_POST['file']))
	$file = rawurldecode($_POST['file']);
else
	exit;

if(isset($_POST['gallery']))
	$gallery = $_POST['gallery'];
else
	exit;

if(isset($_POST['topic']))
	$topic = $_POST['topic'];
else
	exit;

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $file))exit();
if(preg_match("/[\.]*\//", $gallery))exit();
if(!is_file("../ki_galleries/".$gallery."/".$file))exit();
// ---------- Ende Sicherheitsabfragen!

include_once("../ki_config/ki_setup.php");
if(is_file("../ki_config/".$gallery."_ki_setup.php"))include_once("../ki_config/".$gallery."_ki_setup.php");

/*------------------- error/warning checking ------------------*/
if($pic_order == 3){
	if(!function_exists("exif_read_data")){
		$pic_order = 2;
	}
}
/*------------------- end error/warning checking ------------------*/

$supported = array("jpg","png","gif");
$galleryfolder = "../ki_galleries/".$gallery."/";
if(!is_dir($galleryfolder))exit();

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
			$tfile = $fileInfo->getFilename();
			if(!in_array(strtolower(substr($tfile, -3)), $supported))$continue;
			$imgsize = @getimagesize($galleryfolder.$tfile);
			if($imgsize[0]){
				$files[] = array($tfile, $imgsize[0], $imgsize[1]);
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

$id = -1;
foreach ($files as $picfile) {
	$id++;
	if($picfile[0] == $file){
		break;
	}
}

$gesbreite = 0;

if($topic == 1){
	$id++;
	for($i = 0; $i < $preview_pics; $i++){
		if($id + $i >= count($files))break;
		$srcfile = $basedir."ki_makepic.php?file=".$gallery."/".rawurlencode($files[$id+$i][0]);
		$y = 100;
		$x = floor($files[$id+$i][1]/$files[$id+$i][2]*100);
		$gesbreite += $x + 4;
		echo "<img src='".$srcfile."' style='float:left; display:block; margin:0px 2px 0px 2px; border:0px; padding:0px; cursor:pointer; visibility:hidden; width:".$x."px; height:".$y."px;' onclick=\"kiv.getImage(-1, ".($id+$i).")\" onload=\"this.style.visibility='visible'\" />";	

	}
} else {
	$id--;
	for($i = 0; $i < $preview_pics; $i++){
		if($id - $i < 0)break;
		$srcfile = $basedir."ki_makepic.php?file=".$gallery."/".rawurlencode($files[$id-$i][0]);
		$y = 100;
		$x = floor($files[$id-$i][1]/$files[$id-$i][2]*100);
		$gesbreite += $x + 4;
		echo "<img src='".$srcfile."' style='float:right; display:block; margin:0px 2px 0px 2px; border:0px; padding:0px; cursor:pointer; visibility:hidden; width:".$x."px; height:".$y."px;' onclick=\"kiv.getImage(-1, ".($id-$i).")\" onload=\"this.style.visibility='visible'\" />";	
	}
}

echo "<input id='gesbreite' type='hidden' value='".($gesbreite)."' />";
?>

