<?php
session_start();

include_once("../ki_config/ki_setup.php");

if(isset($_POST['file']))
	$file = rawurldecode($_POST['file']);
else
	exit;
	
if(isset($_POST['thumb']))
	$thumb = rawurldecode($_POST['thumb']);
else
	exit;
	
if(isset($_POST['gallery']))
	$gallery = $_POST['gallery'];
else
	exit;
	
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
	$imgfile = "../ki_galleries/".$gallery."/".$file;
	$commfile = "../ki_galleries/".$gallery."/comments/".substr($file, 0, -4).".txt";
	$vcommfile = "../ki_galleries/".$gallery."/viewercomments/".substr($file, 0, -4).".txt";
	$thumbfile = "../ki_galleries/".$gallery."/thumbs/".$thumb;
	if(is_file($imgfile))@unlink($imgfile);
	if(is_file($commfile))@unlink($commfile);
	if(is_file($vcommfile))@unlink($vcommfile);
	if(is_file($thumbfile))@unlink($thumbfile);
}
?>

