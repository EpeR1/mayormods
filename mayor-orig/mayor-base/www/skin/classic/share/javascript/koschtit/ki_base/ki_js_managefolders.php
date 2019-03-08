<?php
ob_start("ob_gzhandler");

session_start();

header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE

//-------------------------------------- functions -------------------------------------
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

function addEvent($el, $event, $function){
	global $browser;
	if($browser === "ie6" || $browser === "ie7"){
		echo $el.".attachEvent('on".$event."', ".$function.");\n";
	} else {
		echo $el.".addEventListener('".$event."', ".$function.", false);\n";
	}
}

function removeEvent($el, $event, $function){
	global $browser;
	if($browser === "ie6" || $browser === "ie7"){
		echo $el.".detachEvent('on".$event."', ".$function.");\n";
	} else {
		echo $el.".removeEventListener('".$event."', ".$function.", false);\n";
	}
}
//-------------------------------------- end functions -----------------------------------

include("../ki_config/ki_setup.php");

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

if(isset($_GET['reldir']))
	$reldir = $_GET['reldir'];
else
	$reldir = "";

$confdir = $reldir."ki_config/";
$galleriesdir = $reldir."ki_galleries/";
$basedir = $reldir."ki_base/";

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}
if($pwok == 0)exit();

header("Content-Type: application/x-javascript");
?>

// Manage folders module 'kim' --------------------------------------------------------------------

function kim_module(){

	/* -------------------------------- variables ----------------------------------- */

	var windowX;
	var windowY;
    var open = 0;

	/* -------------------------------- getElementById function --------------------- */
			
	function $(id){
		return document.getElementById(id);
	}

	/* -------------------------------- constructor/descturctor --------------------- */

	this.constr = function(){
        if(typeof(kiv_module) == "function")kiv.closeImage();
        if(typeof(kie_module) == "function")kie.closeGallery();
        if(typeof(kis_module) == "function")kis.closeSettings();
        if(typeof(kid_module) == "function")kid.close();
        if(typeof(kiu_module) == "function")kiu.closeUpload();
        if(typeof(kic_module) == "function")kic.close();
    	fw.shadebody(1);
		var node;
		if(!$("kim_maindiv")){
			node = document.createElement("div");
			node.id = "kim_maindiv";
            node.style.background = "#aaaaaa";
			node.style.position = "<?php echo $posfix ?>";
			node.style.left = "0px";
			node.style.top = "0px";
			node.style.zIndex = 1000;
			node.style.overflowX = "hidden";
            node.style.overflowY = "auto";
			node.style.display = "block";
            node.style.font = "12px Tahoma, sans-serif";
            node.style.padding = "4px";
            node.innerHTML = "<div id='manage_div' style='background:#D2D2D2; padding:4px; width:960px; margin:auto; border:1px solid #000;'><div style='width:300px; margin:auto; text-align:center;'><input type='button' value='Create new folder' style='margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px; background:#A5ACCF; font-weight:bold;' onclick=\"kim.createnew()\"' /></div><b>Folders in \"ki_galleries\":</b><div id='manage_div_cont' style='padding-top:5px;'></div></div>";
			document.body.appendChild(node);
		}
        if(!$("kim_botdiv")){
        	node = document.createElement("div");
			node.id = "kim_botdiv";
            node.style.background = "#aaaaaa";
			node.style.position = "<?php echo $posfix ?>";
            node.style.zIndex = 1001;
            node.style.padding = "5px";
            node.style.font = "12px Tahoma, sans-serif";
            node.style.color = "#222222";
            node.style.borderTop = "1px solid #000000";
            node.style.borderRight = "1px solid #000000";
            node.style.borderLeft = "1px solid #000000";
            node.style.right = "25px";
            node.style.bottom = "0px";
            node.innerHTML = "<input type='button' value='Close' style='float:right; margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px;' onclick='kim.close()' />";
            document.body.appendChild(node);
        }
		<?php addEvent("window", "resize", "viewdim"); ?>
		viewdim();
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            addEvent("document", "keypress", "taste_kim");
        } else {
            addEvent("document", "keydown", "taste_kim");
        }
        ?>
        <?php addEvent("document", "mousemove", "mousemoved"); ?>
        listFolders();
        fw.move("authorization", 2, -42, 0);
	}

	this.destr = function(){
		<?php removeEvent("window", "resize", "viewdim"); ?>
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            removeEvent("document", "keypress", "taste_kim");
        } else {
            removeEvent("document", "keydown", "taste_kim");
        }
        ?>
        <?php removeEvent("document", "mousemove", "mousemoved"); ?>
		document.body.removeChild($("kim_maindiv"));
        document.body.removeChild($("kim_botdiv"));
        fw.shadebody(0);
        fw.move("authorization", 2, 55, 0);
	}

	/* -------------------------------- methodes ------------------------------------ */
    
    function listFolders(){
	    fw.getHTTP("<?php echo $basedir ?>ki_managefolders.php?list=1", listingFolders, null);
    }
    
    function listingFolders(responseText){
    	var managediv = $("manage_div_cont");
    	var newC = "";
	    var info = eval("(" + responseText + ")");
        if(info.error){
        	alert(info.error);
        } else {
            for(var i = 0; i < info.length; i++){
                newC += "<div style='padding:4px; margin:4px; border:1px solid #000; position:relative; background:#fff;'><input type='button' value='Delete folder' style='float:right; margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px; margin-top:6px; margin-left:10px; background:#f5998f;' onclick=\"kim.delete('" + info[i].folder + "')\"' /><input type='button' value='Empty folder' style='float:right; margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px; margin-top:6px;' onclick=\"kim.empty('" + info[i].folder + "')\"' /><b style='font-size:14px;'>" + info[i].folder + "</b><br /><div style='padding-top:5px; font-size:11px; color:#222;'>Image files: " + info[i].num + "<span style='position:absolute; left:140px;'>Disk usage: " + info[i].size + " MB</span></div></div>";
            }
            managediv.innerHTML = newC;
		}
    }
    
    this.empty = function(folder){
    	fw.getHTTP("<?php echo $basedir ?>ki_managefolders.php?empty=" + folder, listingFolders, null);
    }
    
    this.delete = function(folder){
    	fw.getHTTP("<?php echo $basedir ?>ki_managefolders.php?delete=" + folder, listingFolders, null);
    }
    
    this.createnew = function(){
		var name = prompt("Please enter a folder name", "");   
		if(name != null && name != ""){
           	fw.getHTTP("<?php echo $basedir ?>ki_managefolders.php?createnew=" + name, listingFolders, null);
		}
    }
    
	function viewdim(){
		if(window.innerHeight){
        	windowX = window.innerWidth;
			windowY = window.innerHeight;
        } else if(document.documentElement && document.documentElement.clientHeight){ // Explorer 6 Strict Mode
			windowX = document.documentElement.clientWidth;
			windowY = document.documentElement.clientHeight;
		} else if (document.body){ // other Explorers
			windowX = document.body.clientWidth;
        	windowY = document.body.clientHeight;
		}
        var maindiv = $("kim_maindiv");
        maindiv.style.width = windowX - 8 + "px";
        maindiv.style.height = windowY - 8 + "px";        
	}
    
    this.close = function(){
    	fw.removejs("kim");
    }
   
    function preventDefaultAction(aEvent) {	
		<?php if($browser !== "ie6" && $browser !== "ie7") { ?>
        aEvent.stopPropagation(); 
        <?php } ?>
		<?php if($browser === "gecko") { ?>
		aEvent.preventDefault();
		<?php } ?>
		<?php if($browser === "opera") { ?>
		aEvent.returnValue = false;
		<?php } ?>
	}
	
	function taste_kim(aEvent) {
		aEvent = aEvent ? aEvent : window.event;
        var keyCode = aEvent.keyCode;
		if(keyCode == 27){
			kim.close();
			preventDefaultAction(aEvent);
			return false;
		}
	}
    
    function mousemoved(aEvent){
    	aEvent = aEvent ? aEvent : window.event;
		var x = aEvent.clientX ? aEvent.clientX : aEvent.pageX;
		var y = aEvent.clientY ? aEvent.clientY : aEvent.pageY;
        if(y < 180 && x < 30 && !open){
        	open = 1;
        	fw.move("authorization", 2, 55, 0);
            return;
        }
        if(x > 120 || y > 180){
        	open = 0;
        	fw.move("authorization", 2, -42, 0);
        }
    }    
}

