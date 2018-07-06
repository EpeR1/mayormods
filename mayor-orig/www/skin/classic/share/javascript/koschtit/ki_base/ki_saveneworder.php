<?php
session_start();

/* --------------------------------------------------------- functions ------------------------------------------------------ */

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

/* ---------------------------------------------------------- end functions -------------------------------------------------- */

include_once("../ki_config/ki_setup.php");

if(isset($_POST['gallery']))
	$gallery = $_POST['gallery'];
else
	exit;
	
if(isset($_POST['oldpos']))
	$oldpos = intval($_POST['oldpos']);
else
	exit;

if(isset($_POST['newpos']))
	$newpos = intval($_POST['newpos']);
else
	exit;
	
// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $gallery))exit();
// ---------- Ende Sicherheitsabfragen!

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}

if($pwok == 1){
	
	$files = array();
	$temp = array();
	$newfiles = array();
	$savedfolderhash = 0;
	if(is_file($gallery."_dir")){
		$temp = explode(PHP_EOL, file_get_contents($gallery."_dir"));
		$savedfolderhash = unserialize($temp[0]);
		$files = unserialize($temp[1]);
		
		if($oldpos > $newpos){
			for($i = 0; $i < sizeof($files); $i++){
				if($i < $newpos || $i > $oldpos){
					$newfiles[] = $files[$i];
					continue;
				}
				if($i == $newpos){
					$newfiles[] = $files[$oldpos];
					continue;
				}
				if($i <= $oldpos){
					$newfiles[] = $files[$i-1];
					continue;
				}
			}			
		} else {
			for($i = 0; $i < sizeof($files); $i++){
				if($i < $oldpos || $i > $newpos){
					$newfiles[] = $files[$i];
					continue;
				}
				if($i < $newpos){
					$newfiles[] = $files[$i+1];
					continue;
				}
				if($i == $newpos){
					$newfiles[] = $files[$oldpos];
					continue;
				}
			}	
		}
		
		if(@file_put_contents($gallery."_dir", serialize($savedfolderhash).PHP_EOL.serialize($newfiles)) > 0){
			$setupfile = "../ki_config/".$gallery."_ki_setup.php";
			$params = "";
			if(is_file($setupfile)){
				$temp = explode(PHP_EOL, file_get_contents($setupfile));
				$set = 0;
				for($i = 1; $i < sizeof($temp) - 1; $i++){
					if(strpos($temp[$i], "\$pic_order") !== FALSE){
						$params .= "\$pic_order = 4;\r\n";
						$set = 1;
					} else {
						$params .= $temp[$i]."\r\n";	
					}
				}
				if($set == 0){
					$params .= "\$pic_order = 4;\r\n";
				}
			} else {
				$params = "\$pic_order = 4;\r\n";
			}
			$params = "<?php\r\n".$params."?>";
			if(@file_put_contents($setupfile, $params) > 0){
				echo "ok";
			}
		}
		
	}

}
?>