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

if(isset($_POST['file']))
	$file = rawurldecode($_POST['file']);
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

if(isset($_POST['comment']))
	$comment = addslashes(rawurldecode($_POST['comment']));
else
	$comment = "";

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}

if($pwok == 1){
	$commfile = "../ki_galleries/".$gallery."/comments/".substr($file, 0, -4).".txt";
	file_put_contents($commfile, $comment);
}
?>

