<?php
session_start();

/* --------------------------------------------------------- functions ------------------------------------------------------ */

function calcSize($dir){
	$size = 0;
	$num = 0;
	$iterator = new DirectoryIterator($dir);
	foreach ($iterator as $fileInfo){
		if($fileInfo->isDot()){
			continue;
		} else if($fileInfo->isDir()){
			$info = calcSize($dir.$fileInfo->getBasename()."/");
			$size += $info[1];
		} else if($fileInfo->isFile()){
			$size += $fileInfo->getSize();
			$num += 1;
		}
	}
	return array($num, $size);
}

function listFolders(){
	$setting = "";
	$iterator = new DirectoryIterator("../ki_galleries/");
	foreach ($iterator as $fileInfo) {
		if($fileInfo->isDot()){
			continue;
		} else if($fileInfo->isDir()) {
			$info = calcSize("../ki_galleries/".$fileInfo->getBasename()."/");
			$setting .=  "{\"folder\":\"".addslashes($fileInfo->getBasename())."\", \"size\":".round(($info[1]/1024/1024), 2).", \"num\":".$info[0]." }, ";
		}
	}
	$setting = substr($setting, 0, -2)." ";
	return $setting;
}

function deleteAll($folder){
	$iterator = new DirectoryIterator($folder);
	foreach ($iterator as $fileInfo) {
		if($fileInfo->isDot()){
			continue;
		} else if($fileInfo->isFile()){
			@unlink($folder.$fileInfo->getBasename());
		} else if($fileInfo->isDir()){
			deleteAll($folder.$fileInfo->getBasename()."/");
		}
	}
	return @rmdir($folder);
}

function emptyAll($folder){
	$iterator = new DirectoryIterator($folder);
	foreach ($iterator as $fileInfo) {
		if($fileInfo->isDot()){
			continue;
		} else if($fileInfo->isFile()){
			if(!@unlink($folder.$fileInfo->getBasename()))return false;
		} else if($fileInfo->isDir()){
			deleteAll($folder.$fileInfo->getBasename()."/");
		}
	}
	return true;
}

/* ---------------------------------------------------------- end functions -------------------------------------------------- */

include_once("../ki_config/ki_setup.php");

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}

if($pwok == 1){
	if(isset($_POST['list'])){
		echo "[ ".listFolders()." ]";
	}
	if(isset($_POST['createnew'])){
		$error = "";
		$folder = "../ki_galleries/".$_POST['createnew']."/";
		if(!is_dir($folder)){
			if( !@mkdir($folder, 0777) ) {
				$error = "ERROR: Could not create new folder. Check permissions.";
			}
		} else {
			$error = "ERROR: Folder already exists.";
		}
		if($error === "")
			echo "[ ".listFolders()." ]";
		else
			echo "{\"error\":\"".$error."\" }";
	}
	if(isset($_POST['delete'])){
		$error = "";
		$folder = "../ki_galleries/".$_POST['delete']."/";
		if(is_dir($folder)){
			if(!deleteAll($folder))$error = "ERROR: Could not delete folder. Check permissions.";
		} else {
			$error = "ERROR: Folder not existent.";
		}
		if($error === "")
			echo "[ ".listFolders()." ]";
		else
			echo "{\"error\":\"".$error."\" }";
	}
	if(isset($_POST['empty'])){
		$error = "";
		$folder = "../ki_galleries/".$_POST['empty']."/";
		if(is_dir($folder)){
			if(!emptyAll($folder))$error = "ERROR: Could not empty folder. Check permissions.";
		} else {
			$error = "ERROR: Folder not existent.";
		}
		if($error === "")
			echo "[ ".listFolders()." ]";
		else
			echo "{\"error\":\"".$error."\" }";
	}
}
?>