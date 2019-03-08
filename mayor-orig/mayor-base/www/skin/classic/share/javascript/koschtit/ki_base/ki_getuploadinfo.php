<?php
session_start();

if(isset($_POST['form']))
	$form = $_POST['form'];
else
	exit();

if($form === "debug"){
	var_dump($_SESSION['uploads']);
	exit();	
}

if($form === "reset"){
	$_SESSION['uploads'] = array(-1, -1, -1, -1, -1, -1, -1, -1, -1);
	exit();
}

$tmpfolder = ini_get('upload_tmp_dir');

function getTempFile(){
	global $tmpfolder;
	global $form;
	
	if($form == -1){
		return 0;
	}
	
	if(is_dir($tmpfolder)) {
		$phptempfiles = glob($tmpfolder."/[p][h][p]*.tmp");
		//$phptempfiles = glob($tmpfolder."/[p][h][p]*");
		if(is_array($phptempfiles)){
			if(sizeof($phptempfiles) != 0){ // es laufen Uploads
				foreach($phptempfiles as $sf){
					if(!in_array($sf, $_SESSION['uploads'])){ // Der zuletzt gestartete Upload läuft, aber wir können den nächsten starten ( laufender upload gespeichert )
						$_SESSION['uploads'][$form] = $sf;
						//return 1; // für alle uploads parallel
					}
				}
			} else { // Alle bisher gestartete Upload sind schon abgeschlossen, d.h. wir können den nächsten starten
				$_SESSION['uploads'][$form] = "finished";
				return 1;
			}
		}
	} else {
		$_SESSION['uploads'][$form] = "error";
		return 1;
	}
	
	return 0;
}

function getPercentage(){
	foreach($_SESSION['uploads'] as $sf){
		$val = 0;
		if($sf === "finished")
			$val = "finished";
		else {
			if($sf == -1){
				$val = 0;
			} else {
				if(is_file($sf)){
					$val = filesize($sf);
				} else {
					$val = "finished";
				}
			}
		}
		echo "<input type='hidden' value='".$val."' />";
	}
}


echo "<input type='hidden' value='".getTempFile()."' />";
getPercentage();
?>