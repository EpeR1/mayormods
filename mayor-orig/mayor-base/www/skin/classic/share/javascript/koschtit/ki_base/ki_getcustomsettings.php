<?php
ob_start("ob_gzhandler");

header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE

$verz = @opendir("../ki_config/");
while($file = @readdir($verz))
{
	if($file != "." && $file != ".." && strtolower(substr($file, -12)) === "ki_setup.php"){
		include("../ki_config/ki_setup.php");
		if($file === "ki_setup.php")
			$temp = "default";
		else {
			$temp = substr($file, 0, -13);
			include("../ki_config/".$file);				
		}
		
		echo "kib.customsettings['".$temp."'] = Array(".$fr_width.", ".$fr_height.", \"".$fr_color."\", \"".$th_bord_color."\", \"".$th_bord_hover_color."\", ".$th_bord_hover_increase.", ".$resize_auto.", ".$nav_style.", ".$th_bord_size.", ".$show_nav.", ".$nav_always.", ".$slideshow.", \"".$nav_color."\", \"".$nav_border_color."\", \"".$nav_pos."\", ".$show_explorer.", ".$th_shadow.");\r\n";
	}
}
@closedir($verz);
?>