<?php
if(isset($_POST['file']))
	$file = rawurldecode($_POST['file']);
else if(isset($_GET['file']))
	$file = rawurldecode($_GET['file']);
else
	exit;
	
if(isset($_POST['gallery']))
	$gallery = $_POST['gallery'];
else if(isset($_GET['gallery']))
	$gallery = $_GET['gallery'];
else
	exit;

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $file))exit();
if(preg_match("/[\.]*\//", $gallery))exit();
if(!is_file("../ki_galleries/".$gallery."/".$file))exit();
// ---------- Ende Sicherheitsabfragen!

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".$file);
header("Content-Type: image");
header("Content-Transfer-Encoding: binary");
    
readfile("../ki_galleries/".$gallery."/".$file);
?>



