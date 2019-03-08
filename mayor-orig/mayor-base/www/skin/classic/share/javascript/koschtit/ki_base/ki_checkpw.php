<?php
header("Content-Type: application/x-javascript");


//----------------------------------------------------------------------------- functions --------------------------------------------------------------------------------

function draw_image($filename, $id, $style, $params) {
	global $browser, $basedir;
	$idstring = "";
	if($id != "")$idstring = "id='".$id."' ";
	if($browser == "ie6") {
		$imgsize = getimagesize($filename);
		echo "<img ".$idstring."style='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".$basedir.$filename."); width:".$imgsize[0]."px; height:".$imgsize[1]."px; ".$style."' src='".$basedir."ki_noimage.gif' ".$params." />";
	} else {
		if($style != "")$style = " style='".$style."' ";
		echo "<img ".$idstring."src='".$basedir.$filename."'".$style.$params." />";
	}
}

$browser = $_SERVER['HTTP_USER_AGENT'];

if(preg_match("/Opera/",$browser))
	$browser = "opera"; 
elseif(preg_match("/MSIE [9]/",$browser))
	$browser = "ie9";
elseif(preg_match("/MSIE [7-8]/",$browser))
	$browser = "ie7";
elseif(preg_match("/MSIE [1-6]/",$browser))
	$browser = "ie6";
elseif(preg_match("/AppleWebKit/",$browser))
	$browser = "webkit";
else
	$browser = "gecko";

if($browser === "ie6")
	$posfix = "absolute";
else
	$posfix = "fixed";

include_once("../ki_config/ki_setup.php");

if(isset($_POST['query']))
	$query = $_POST['query'];
else
	exit;
	
if(isset($_POST['reldir']))
	$reldir = $_POST['reldir'];
else
	$reldir = "";
	
$confdir = $reldir."ki_config/";
$galleriesdir = $reldir."ki_galleries/";
$basedir = $reldir."ki_base/";

if(isset($_POST['against']))
	$against = $_POST['against'];
else
	exit;

if($against === "adm"){
	if(md5($query) === $pw){
		session_start();
		$_SESSION["pwquery"] = $pw;
	?>
	var params = location.search.substr(1).split('&');
	var newparams = "";
	for(var i = 0; i < params.length; i++){
		var param = params[i].split('=');
		if(param[0] != 'admin'){
			if(newparams.length == 0)
				newparams += "?";
			else
				newparams += "&";
			newparams += params[i];
		}
	}
	kib.checkpw = null;
	var slash = "";
	if(location.pathname.substr(0, 1) != "/")slash = "/";
	$('authorization').innerHTML = "<a href='javascript:kib.edit_ki_setup();' style='color:#ffffff; display:block; margin:5px;'>Settings</a><a href='javascript:kib.fileupload();' style='color:#ffffff; display:block; margin:5px;'>Upload images</a><a href='javascript:kib.deleteexplorer();' style='color:#ffffff; display:block; margin:5px;'>Delete images</a><a href='javascript:kib.changeorder();' style='color:#ffffff; display:block; margin:5px;'>Change order</a><a href='javascript:return false;' onmouseover='kib.showhelp(this, 1, 1)' onmouseout='kib.hidehelp()' style='color:#ffffff; display:block; margin:5px;'>Label images</a><a href='javascript:kib.managefolders();' style='color:#ffffff; display:block; margin:5px;'>Manage folders</a><a href='http://"+location.hostname+slash+location.pathname+newparams+"' style='color:#ffffff; display:block; margin:5px;'>Logout</a>";
	fw.resize( 'authorization', 2, 100, 132, Array( fw.move, 'authorization', 1, 50 + 5, 66 + 5, Array( fw.shadebody, 0 ) ) );
	setTimeout( function(){
		var node = document.createElement("div");
		node.id = "kit_docu";
		node.style.position = "<?php echo $posfix ?>";
		node.style.zIndex = 999;
		node.style.padding = "0px";
		node.style.border = "1px dashed #fff";
		node.style.left = "-300px";
		node.style.bottom = "2px";
		node.innerHTML = "<a href='http://koschtit.tabere.net/en/documentation/adminpanel' target='_blank' style='border:none; padding:0px; margin:0px;'><img src='<?php echo $basedir ?>ki_online_docu.jpg' alt='Online documentation' style='border:none; padding:0px; margin:0px;' /></a>";
		document.body.appendChild(node);
		fw.move2(node, 2, 26, 0, Array( fw.move2, "kit_docu", 2, -14, 0, Array( fw.move2, "kit_docu", 2, 5, 0, Array( fw.move2, "kit_docu", 2, 1, 0, Array( fw.move, "kit_docu", 2, 2, 0 ) ) ) ) );  
	}, 1300);
	
	if(!$("kib_helpbox")){
		var node = document.createElement("div");
		node.id = "kib_helpbox";
		node.style.color = "<?php echo $comm_text_color ?>";
		node.style.font = "12px Tahoma, sans-serif";
		node.style.position = "<?php echo $posfix ?>";
		node.style.left = "100px";                    
		node.style.top = "200px";
		node.style.lineHeight = "19px";
		node.style.textAlign = "left";
		node.style.zIndex = "10001";
		node.style.width = "150px";
		node.style.padding = "8px";
		node.style.visibility = "hidden";
		node.style.background = "<?php echo $bord_color ?>";
		node.style.border = "2px solid <?php echo $nav_border_color ?>";    
		document.body.appendChild(node);
	}
	<?php
	} else {
	?>
	$("pwform").style.border = "1px solid #ff0000";
	<?php
	}
} else {
	if(md5($query) === $userpw){
		session_start();
		$_SESSION["pwquery"] = $pw;
	?>
	var params = location.search.substr(1).split('&');
	var newparams = "";
	for(var i = 0; i < params.length; i++){
		var param = params[i].split('=');
		if(param[0] != 'user'){
			if(newparams.length == 0)
				newparams += "?";
			else
				newparams += "&";
			newparams += params[i];
		}
	}
	kib.checkpw = null;
	var slash = "";
	if(location.pathname.substr(0, 1) != "/")slash = "/";
	$('authorization').innerHTML = "<a href='javascript:kib.fileupload();' style='color:#ffffff; display:block; margin:5px;'>Upload images</a><a href='http://"+location.hostname+slash+location.pathname+newparams+"' style='color:#ffffff; display:block; margin:5px;'>Logout</a>";
	fw.resize( 'authorization', 2, 100, 44, Array( fw.move, 'authorization', 1, 50 + 5, 22 + 5, Array( fw.shadebody, 0 ) ) );
	
	if(!$("kib_helpbox")){
		var node = document.createElement("div");
		node.id = "kib_helpbox";
		node.style.color = "<?php echo $comm_text_color ?>";
		node.style.font = "12px Tahoma, sans-serif";
		node.style.position = "<?php echo $posfix ?>";
		node.style.left = "100px";                    
		node.style.top = "200px";
		node.style.lineHeight = "19px";
		node.style.textAlign = "left";
		node.style.zIndex = "10001";
		node.style.width = "150px";
		node.style.padding = "8px";
		node.style.visibility = "hidden";
		node.style.background = "<?php echo $bord_color ?>";
		node.style.border = "2px solid <?php echo $nav_border_color ?>";    
		document.body.appendChild(node);
	}
	<?php
	} else {
	?>
	$("pwform").style.border = "1px solid #ff0000";
	<?php
	}
}
?>