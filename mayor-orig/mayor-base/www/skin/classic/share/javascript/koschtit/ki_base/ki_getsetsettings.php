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

/* ---------------------------------------------------------- end functions -------------------------------------------------- */

include_once("../ki_config/ki_setup.php");

if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value)
    {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

$global_start = -1;
$global_length = -1;
$global_count = -1;

reset($GLOBALS);
while (list($key, $val) = each($GLOBALS)) {
	$global_count++;
    if($global_start == -1){
		if($key === "fr_width"){
			$global_start = $global_count;
			continue;
		}
	}
	if($global_length == -1){
		if($key === "global_start"){
			$global_length = $global_count - $global_start;
			break;
		}
	}
}

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}

if($pwok == 1){
	if(isset($_POST['get'])){
		if(isset($_POST['file'])){
			$file = $_POST['file'];
			
			// -------------- Sicherheitsabfragen!
			if(preg_match("/[\.]*\//", $file))exit();
			// ---------- Ende Sicherheitsabfragen!
			
			if(is_file("../ki_config/".$file."_ki_setup.php")){
				reset($GLOBALS);
				for($i = 0; $i < $global_start; $i++)next($GLOBALS);
				for($i = 0; $i < $global_length; $i++){
					list($key, $val) = each($GLOBALS);
					unset($GLOBALS[$key]);
				}
				include_once("../ki_config/".$file."_ki_setup.php");
			} else
				exit();
		}
		$setting = "";
		echo "{ ";
		reset($GLOBALS);
		for($i = 0; $i < $global_start; $i++)next($GLOBALS);
		for($i = 0; $i < $global_length; $i++){
			list($key, $val) = each($GLOBALS);
			if(isset($GLOBALS[$key]))$setting .=  "\"$key\" : \"".addslashes($val)."\", ";
		}
		$setting = substr($setting, 0, -2)." ";
		echo $setting;
		echo "}";
	}
	if(isset($_POST['set'])){
		if(!isset($_POST['pw']))$_POST['pw'] = $_SESSION['pwquery'];
		if(!isset($_POST['userpw']))$_POST['userpw'] = $userpw;
		if(isset($_POST['file'])){
			$file = $_POST['file'];
			if($file === "default")
				$file = "";
			else
				$file .= "_";
			
			// -------------- Sicherheitsabfragen!
			if(preg_match("/[\.]*\//", $file))exit();
			// ---------- Ende Sicherheitsabfragen!

			$params = "";
			reset($GLOBALS);
			for($i = 0; $i < $global_start; $i++)next($GLOBALS);
			for($i = 0; $i < $global_length; $i++){
				list($key, $val) = each($GLOBALS);
				if(isset($_POST[$key])){
					$val = rawurldecode($_POST[$key]);
					if($key === "pw"){
						if($val !== $_SESSION['pwquery']){
							$val = "\"".md5($val)."\"";
						} else {
							$val = "\"".$val."\"";
						}
					} else if($key === "userpw"){ 
						if($val !== $userpw){
							$val = "\"".md5($val)."\"";
						} else {
							$val = "\"".$val."\"";
						}
					} else {
						if(!is_numeric($val))$val = "\"".addslashes($val)."\"";						
					}
					$params .= "\$$key = $val;\r\n";
				}
			}

			$setupfile = "../ki_config/".$file."ki_setup.php";
			if($file !== ""){
				if($params !== ""){
					$params = "<?php\r\n".$params."?>";
					if(!@file_put_contents($setupfile, $params))
						echo "failure";
					else
						echo "modified";
				} else {
					if(!@unlink($setupfile))
						echo "failure";
					else
						echo "modified";					
				}
			} else {
				$params = "<?php\r\n".$params."?>";
				if(!@file_put_contents($setupfile, $params)){
					echo "failure";
				} else {					
					if($_POST['admin'].$_POST['pw'] !== $admin.$pw){
						echo "adminchanged";
					} else {
						echo "modified";
					}
				}
			}
		}
	}
}
?>