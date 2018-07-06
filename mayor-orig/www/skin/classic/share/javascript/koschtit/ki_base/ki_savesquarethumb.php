<?php
session_start();

//----------------------------------------------------------------------------- functions --------------------------------------------------------------------------------

if (!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		if ($handle = @fopen($filename, 'rb')) {
			$data = fread($handle, filesize($filename));
			fclose($fh);
			return $data;
		}
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

//-------------------------------------------------------------------------- end functions --------------------------------------------------------------------------------

include_once("../ki_config/ki_setup.php");

if(isset($_POST['file']))
	$file = rawurldecode($_POST['file']);
else
	exit();

if(isset($_POST['pos']))
	$pos = $_POST['pos'];
else
	exit();

$gallery = substr($file, 0, strpos($file, "/"));
$file = substr($file, strpos($file, "/")+1);

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $file))exit();
if(preg_match("/[\.]*\//", $gallery))exit();
if(!is_file("../ki_galleries/".$gallery."/".$file))exit();
// ---------- Ende Sicherheitsabfragen!

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}

if($pwok == 1){
	if(is_file("../ki_config/".$gallery."_ki_setup.php")){
		include_once("../ki_config/".$gallery."_ki_setup.php");
		$configfile = "../ki_config/".$gallery."_ki_setup.php";
	} else {
		$configfile = "../ki_config/ki_setup.php";
	}
	
	/*------------------- change config settings ------------------*/
	$savefile = $gallery."_lastmodified";
	$writestring  = "<?php\r\n\$lm_saved = \"1\";\r\n";
	$writestring .= "\$lm_lastmodified = \"1\";\r\n?>";
	@file_put_contents($savefile, $writestring);
	$oldcontent = file_get_contents($configfile);
	if(substr($oldcontent, -1) === "\n"){
		$oldcontent = substr($oldcontent, 0, -1);
	} else {
		$oldcontent .= "\n";	
	}
    @file_put_contents($configfile, $oldcontent);
	/*------------------- end change config settings ------------------*/
	
	$picname = $gallery."_".$file;
	if(!is_file("custom")){
		@file_put_contents("custom", $picname."=".$pos);
	} else {
		$content = @file_get_contents("custom");
		$pictures = explode("|", $content);
		$newcontent = "";
		$found = 0;
		for($i = 0; $i < count($pictures); $i++){
			if(strpos($pictures[$i], $picname) !== FALSE){
				$pictures[$i] = $picname."=".$pos;
				$found = 1;
			}
			$newcontent .= $pictures[$i]."|";
		}
		if($found == 0){
			$newcontent .= $picname."=".$pos."|";
		}
		$newcontent = substr($newcontent, 0, -1);
		@file_put_contents("custom", $newcontent);
	}
}
?>