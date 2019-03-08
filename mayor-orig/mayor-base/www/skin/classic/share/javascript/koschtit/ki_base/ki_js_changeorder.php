<?php
ob_start("ob_gzhandler");

session_start();

header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE

//----------------------------------------------------------------------------- functions --------------------------------------------------------------------------------

function draw_image($filename, $id, $style, $params) {
	global $browser, $basedir;
	$idstring = "";
	if($id != "")$idstring = "id='".$id."' ";
	if($browser === "ie6") {
		$imgsize = getimagesize($filename);
		echo "<img ".$idstring."style='filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=".$basedir.$filename."); width:".$imgsize[0]."px; height:".$imgsize[1]."px; ".$style."' src='".$basedir."ki_noimage.gif' ".$params." />";
	} else {
		if($style != "")$style = " style='".$style."' ";
		echo "<img ".$idstring."src='".$basedir.$filename."'".$style.$params." />";
	}
}

function addEvent($el, $event, $function){
	global $browser;
	if($browser == "ie6" || $browser == "ie7"){
		echo $el.".attachEvent('on".$event."', ".$function.");\n";
	} else {
		echo $el.".addEventListener('".$event."', ".$function.", false);\n";
	}
}

function removeEvent($el, $event, $function){
	global $browser;
	if($browser == "ie6" || $browser == "ie7"){
		echo $el.".detachEvent('on".$event."', ".$function.");\n";
	} else {
		echo $el.".removeEventListener('".$event."', ".$function.", false);\n";
	}
}

//------------------------------------------------------------------------ end functions ----------------------------------------------------------------------------------

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
	
if($browser == "ie6")
	$posfix = "absolute";
else
	$posfix = "fixed";

if(isset($_GET['reldir']))
	$reldir = $_GET['reldir'];
else
	$reldir = "";

if(isset($_GET['gallery']))
	$gallery = $_GET['gallery'];
else
	$gallery = "";

// -------------- Sicherheitsabfragen!
if(preg_match("/[\.]*\//", $gallery))exit();
// ---------- Ende Sicherheitsabfragen!

$confdir = $reldir."ki_config/";
$galleriesdir = $reldir."ki_galleries/";
$basedir = $reldir."ki_base/";

include_once("../ki_config/ki_setup.php");
if(is_file("../ki_config/".$gallery."_ki_setup.php"))include_once("../ki_config/".$gallery."_ki_setup.php");

$pwok = 0;
if(isset($_SESSION['pwquery'])){
	if($_SESSION['pwquery'] === $pw)$pwok = 1;
}
if($pwok == 0)exit();

header("Content-Type: application/x-javascript");
?>

// Change image order module 'kic' --------------------------------------------------------------------

function kic_module(){

	/* -------------------------------- variables ----------------------------------- */

	var windowX;
	var windowY;
    var currentfolder = "";
    var gallerynumber = -1;
    
    var waitforanim = 0;
    var dragobj = -1;
    var hoverobj = -1;
    var direction = 0;
	
	/* -------------------------------- getElementById function --------------------- */
	
	function $(id){
		return document.getElementById(id);
	}

	/* -------------------------------- constructor/descturctor --------------------- */

	this.constr = function(params){
        if(typeof(kiv_module) == "function")kiv.closeImage();
        if(typeof(kie_module) == "function")kie.closeGallery();
        if(typeof(kis_module) == "function")kis.closeSettings();
        if(typeof(kid_module) == "function")kid.close();
        if(typeof(kiu_module) == "function")kiu.closeUpload();
        if(typeof(kim_module) == "function")kim.close();
    	fw.shadebody(1);    
		var node;
		if(!$("kic_maindiv")){
			node = document.createElement("div");
			node.id = "kic_maindiv";
            node.style.background = "#aaaaaa";
			node.style.position = "<?php echo $posfix ?>";
			node.style.left = "0px";
			node.style.top = "0px";
			node.style.zIndex = 1000;
			node.style.overflowX = "hidden";
            node.style.overflowY = "auto";
			node.style.display = "block";
            node.style.padding = "4px";
            node.innerHTML = "<div id='kic_changediv' style='background:#D2D2D2; border:1px solid #000000; display:none; position:relative;'></div>";
			document.body.appendChild(node);
		}
        if(!$("kic_topdiv")){
        	node = document.createElement("div");
			node.id = "kic_topdiv";
            node.style.background = "#aaaaaa";
			node.style.position = "<?php echo $posfix ?>";
            node.style.height = "45px";
            node.style.zIndex = 1001;
            node.style.padding = "5px 20px 0px 120px";
            node.style.font = "12px Tahoma, sans-serif";
            node.style.color = "#222222";
            node.style.borderBottom = "1px solid #000000";
            node.style.borderRight = "1px solid #000000";
            node.style.left = "0px";
            node.style.top = "0px";
            var dirs = "";
            for(var i = 0; i < kib.dirs.length; i++){
            	dirs = dirs + "<span style='cursor:pointer; text-decoration:underline;margin-right:5px; line-height:14px;' onclick=\"kic.setFolder(" + i + ")\">" + kib.dirs[i] + "</span>";
                if(kib.dirs[i] == "<?php echo $gallery ?>")gallerynumber = i;
            }
            currentfolder = "<?php echo $gallery ?>";
            node.innerHTML = "<div style='margin-bottom:5px;'><span style='font-size:14px; line-height:18px; font-weight:bold; color:#003; margin-right:5px;'>Selected gallery folder</span><span style='background:#003; border:1px solid #CCC; padding:3px; color:#ffffff;' id='kic_selfolder'>" + currentfolder + "</span></div>" + dirs;
            document.body.appendChild(node);
        }
		if(!$("kic_botdiv")){
        	node = document.createElement("div");
			node.id = "kic_botdiv";
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
            node.innerHTML = "<input type='button' value='Close' style='float:right; margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px;' onclick='kic.close()' />";
            document.body.appendChild(node);
            kib.showhelp(node, 4, 3);
			node.onmouseover = function(){
            	kib.hidehelp();
            }
        }
        if(!$("kic_wait")){
        	node = document.createElement("div");
			node.id = "kic_wait";
            node.style.background = "<?php echo $bord_color ?>";
			node.style.position = "<?php echo $posfix ?>";
			node.style.left = "50%";
			node.style.top = "50%";
			node.style.zIndex = 10000;
            node.style.width = "200px";
            node.style.height = "40px";
            node.style.marginLeft = "-106px";
            node.style.marginTop = "-26px";
            node.style.border = "2px solid <?php echo $nav_border_color ?>";
            node.style.color = "<?php echo $comm_text_color ?>";
            node.style.font = "14px normal <?php echo $comm_text_font ?>";
            node.style.padding = "8px 4px 0px 4px";
            node.style.textAlign = "center";
            node.style.lineHeight = "17px";
            node.style.display = "none";
            node.innerHTML = "Please wait, while folder being scanned ...";
			document.body.appendChild(node);
        }
		<?php addEvent("window", "resize", "viewdim"); ?>
		viewdim();
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            addEvent("document", "keypress", "kic_taste");
        } else {
            addEvent("document", "keydown", "kic_taste");
        }
        ?>
        <?php addEvent("document", "mousemove", "mousemoved"); ?>
        openGallery();
	}
    
	this.destr = function(){
		<?php removeEvent("window", "resize", "viewdim"); ?>
		document.body.removeChild($("kic_maindiv"));
        document.body.removeChild($("kic_wait"));
        document.body.removeChild($("kic_topdiv"));
        document.body.removeChild($("kic_botdiv"));          
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            removeEvent("document", "keypress", "kic_taste");
        } else {
            removeEvent("document", "keydown", "kic_taste");
        }
        ?>
        <?php removeEvent("document", "mousemove", "mousemoved"); ?>
		fw.shadebody(0);
	}
	
	/* -------------------------------- methodes ------------------------------------ */

	function viewdim(){
		if(window.innerHeight){
        	windowX = window.innerWidth - 10;
			windowY = window.innerHeight - 10;
        } else if(document.documentElement && document.documentElement.clientHeight){ // Explorer 6 Strict Mode
			windowX = document.documentElement.clientWidth - 10;
			windowY = document.documentElement.clientHeight - 10;
		} else if (document.body){ // other Explorers
			windowX = document.body.clientWidth - 10;
        	windowY = document.body.clientHeight - 10;
		}
        var maindiv = $("kic_maindiv");
        maindiv.style.width = windowX + 2 + "px";
        maindiv.style.height = windowY + 2 + "px";
       	var container = $("kic_changediv").childNodes;
        var laenge = container.length - 1;
		if(laenge != -1){
            for(var i = 0; i < laenge; i++){
                var temp = container[i].alt.split("_");
                container[i].alt = temp[0] + "_" + temp[1];
            }
            placeimages();
		}
	}

	function openGallery(){
	    $("kic_wait").style.display = "block";
		var params = "?reldir=<?php echo $reldir ?>&gallery=" + currentfolder + "&gallerynumber=" + gallerynumber + "&tochange=1";
		fw.getHTTP("<?php echo $basedir ?>ki_explorer.php" + params, kic.gotGallery);
	}
	
	this.gotGallery = function(responseText){
    	$("kic_wait").style.display = "none";
		var changediv = $("kic_changediv");
		changediv.innerHTML = responseText;
		changediv.style.display = "block";
        placeimages();
	}
    
    function placeimages(){
<?php
$temp = getimagesize("ki_nav_next.png");
if($nav_always == 1 && $show_nav == 1)$fr_height -= ($temp[1]+18);
if($th_lines == "auto")$th_lines = ceil($thumbs/($th_per_line));
if($th_width == "auto")$th_width = round($fr_width/($th_per_line)) - round($fr_height*0.04) - 4;
if($th_height == "auto")$th_height = round($fr_height/($th_lines)) - round($fr_height*0.04) - 4;
if($th_to_square == 1) {
	if($th_width >= $th_height){
		$th_width = $th_height;
	}
	$th_height = $th_width;
}
echo "var th_width = ".($th_width-2*$th_bord_size).";";
echo "var th_height = ".($th_height-2*$th_bord_size).";";
?>
    	var prozeile = Math.floor(windowX/(th_width+<?php echo $explorer_padding+2*$th_bord_size ?>));
        var offsetx = (windowX - (prozeile*(th_width+<?php echo $explorer_padding+2*$th_bord_size ?>)))*0.5;
        var spaltenbreite = (windowX - 2*offsetx)/prozeile;
        var zeilenhoehe = th_height+<?php echo $explorer_padding+2*$th_bord_size ?>;
        var offsety = <?php echo $explorer_padding*0.5 ?>;

        var zeile = 1;
        var spalte = 1;
        
        var zaehler = 0;
    	var container = $("kic_changediv").childNodes;
		var act_width;
        var act_height;
        for(var i = 0; i < container.length - 1; i++){
            var bild = container[i];
            <?php if($th_to_square == 0){ ?>
            var bildalt = bild.alt.split("_");
            if(bildalt[0] >= bildalt[1]){
                var k = bildalt[1]/bildalt[0];
                act_width = th_width;
                act_height = k*act_width;
                if(act_height > th_height){
                    act_height = th_height;
                    act_width = (1/k) * act_height;
                }
			} else {
                var k = bildalt[0]/bildalt[1];
                act_height = th_height;
                act_width = k*act_height;
                if(act_width > th_width){
                    act_width = th_width;
                    act_height = (1/k) * act_width;
                }
            }
            <?php } else { ?>
            if(th_width < th_height){
				act_width = th_width;
			} else {
				act_width = th_height;
			}
			act_height = act_width;
            <?php } ?>
            xpos = Math.round(offsetx + spaltenbreite*(spalte-0.5) - 0.5*act_width - <?php echo $th_bord_size ?>);
            ypos = Math.round(offsety + zeilenhoehe*(zeile-0.5) - 0.5*act_height - <?php echo $th_bord_size ?>);
            
            bild.style.left = xpos + "px";
            bild.style.top = ypos + "px";
            bild.alt = bild.alt + "_"+xpos+"_"+ypos;
			zaehler++;
			spalte++;
            if(zaehler >= prozeile){
            	zaehler = 0;
                zeile++;
                spalte = 1;
            }
        }
        $("kic_changediv").lastChild.style.position = "static";
		$("kic_changediv").lastChild.style.paddingTop = ypos + (0.5*act_height) + "px";
        $("kic_changediv").lastChild.style.height = 0.5*th_height + <?php echo 2*$th_bord_size+$explorer_padding ?> + "px";
    }

    this.setFolder = function(folder){
    	if(kib.dirs[folder] !== currentfolder){
            fw.removejs("kic");
        	fw.addjs("<?php echo $basedir ?>ki_js_changeorder.php?reldir=<?php echo $reldir ?>&gallery=" + kib.dirs[folder], "kic");
		}
    }

	this.selectForDrag = function(obj){
    	if(dragobj != -1)return;
		var temp = obj.id.indexOf("_");
        var picnumber = Number(obj.id.substr(temp+1));
        obj.style.zIndex = 0;
        fw.fade(obj, 50);
        dragobj = obj;
    }

	function mousemoved(aEvent) {
    	if(dragobj == -1)return;
		aEvent = aEvent ? aEvent : window.event;
		var x = aEvent.clientX ? aEvent.clientX : aEvent.pageX;
		var y = aEvent.clientY ? aEvent.clientY : aEvent.pageY;
        
        var maindiv = $("kic_maindiv");
        y += maindiv.scrollTop;
        
        var objalt = dragobj.alt.split("_");
        
        dragobj.style.left = x - 0.5*objalt[0] + "px";
        dragobj.style.top = y - 0.5*objalt[1] + "px";
	}

	this.releaseForDrag = function(obj){
    	if(dragobj == -1 || obj != dragobj){
        	return;
        }
        var objalt = dragobj.alt.split("_");
        if(hoverobj == -1){
            obj.style.zIndex = 1;
            fw.fade(dragobj, 100);
            fw.move(dragobj, 1, objalt[2], objalt[3]);        
        } else {
        	var temp = hoverobj.id.indexOf("_");
	        var picnumber = Number(hoverobj.id.substr(temp+1))-1;
            var newpos = -1;
        	var hoverobjalt = hoverobj.alt.split("_");
            if(direction == 1){
				if(hoverobj.getAttribute("moved") == 0){
					newpos = picnumber;
                    hoverobjalt = hoverobj.previousSibling.alt.split("_");      		
            	} else {
                	newpos = picnumber + 1;
                }         
            }
            if(direction == -1){
            	if(hoverobj.getAttribute("moved") == 0){
					newpos = picnumber + 2;
                    hoverobjalt = hoverobj.nextSibling.alt.split("_");
            	} else {
                	newpos = picnumber + 1;
                } 
            }
            temp = dragobj.id.indexOf("_");
	        var oldpos = Number(dragobj.id.substr(temp+1));
            if(oldpos == newpos){
            	obj.style.zIndex = 1;
                fw.fade(dragobj, 100);
                fw.move(dragobj, 1, objalt[2], objalt[3]);
            } else {
            	obj.style.zIndex = 1;
                fw.fade(dragobj, 100);
                var left = parseInt(hoverobjalt[2]) + 0.5*(parseInt(hoverobjalt[0])-parseInt(objalt[0]));
                var top = parseInt(hoverobjalt[3]) + 0.5*(parseInt(hoverobjalt[1])-parseInt(objalt[1]));
                var forcopy = dragobj;
                fw.move(dragobj, 1, left, top, Array(function(){
                	var clone = forcopy.cloneNode(true);
                    clone.id = gallerynumber + "_" + newpos;
                    if(direction == -1){
                    	forcopy.parentNode.insertBefore(clone, forcopy.parentNode.childNodes[newpos]);
					} else {
                    	forcopy.parentNode.insertBefore(clone, forcopy.parentNode.childNodes[newpos+1]);
                    }
                    forcopy.parentNode.removeChild(forcopy);
                    for(var i = 0; i < clone.parentNode.childNodes.length-1; i++){
                    	var tempobj = clone.parentNode.childNodes[i];
                        var tempobjalt = tempobj.alt.split("_");
                        tempobj.alt = tempobjalt[0]+"_"+tempobjalt[1];
                        tempobj.id = gallerynumber + "_" + i;
                        tempobj.setAttribute("moved", 0);
                    }
                    placeimages();
                }));
                saveneworder(oldpos, newpos);
            }
        }
		dragobj = -1;
		hoverobj = -1;
    }
    
	this.hoverDrag = function(obj){
    	if(dragobj == -1)return;
        if(obj == dragobj)return;
        
        var temp = obj.id.indexOf("_");
        var picnumber = Number(obj.id.substr(temp+1))+1;
        
        temp = dragobj.id.indexOf("_");
        var dragpicnumber = Number(dragobj.id.substr(temp+1))+1;

		if(dragpicnumber > picnumber){
        	if(direction == 1){
            	for(var i = dragpicnumber; i < obj.parentNode.childNodes.length; i++){
                	var tempobj = obj.parentNode.childNodes[i-1];
                	if(tempobj.getAttribute("moved") == 1){
	                    tempobj.setAttribute("moved", 0);
                    	var tempobjalt = tempobj.alt.split("_");
                    	var left = tempobjalt[2];
                        var top = tempobjalt[3];
                        fw.move(tempobj, 1, left, top);
                    }
                }
            }
        	direction = -1;
        	if(obj.getAttribute("moved") == 0){
                for(var i = picnumber; i < dragpicnumber; i++){
                    var tempobj = obj.parentNode.childNodes[i-1];
                    tempobj.setAttribute("moved", 1);
                    var tempobjalt = tempobj.alt.split("_");
                    var movethumb = obj.parentNode.childNodes[i];
                    var movethumbalt = movethumb.alt.split("_");
                    var left = parseInt(movethumbalt[2]) + 0.5*(parseInt(movethumbalt[0])-parseInt(tempobjalt[0]));
                    var top = parseInt(movethumbalt[3]) + 0.5*(parseInt(movethumbalt[1])-parseInt(tempobjalt[1]));
                    fw.move(tempobj, 1, left, top);
                }
            } else {
            	for(var i = picnumber; i > 0; i--){
                	var tempobj = obj.parentNode.childNodes[i-1];
                	if(tempobj.getAttribute("moved") == 1){
	                    tempobj.setAttribute("moved", 0);
                    	var tempobjalt = tempobj.alt.split("_");
                    	var left = tempobjalt[2];
                        var top = tempobjalt[3];
                        fw.move(tempobj, 1, left, top);
                    } else {
                    	break;
                    }
                }
            }
        } else {
        	if(direction == -1){
            	for(var i = dragpicnumber; i > 0; i--){
                	var tempobj = obj.parentNode.childNodes[i-1];
                	if(tempobj.getAttribute("moved") == 1){
	                    tempobj.setAttribute("moved", 0);
                    	var tempobjalt = tempobj.alt.split("_");
                    	var left = tempobjalt[2];
                        var top = tempobjalt[3];
                        fw.move(tempobj, 1, left, top);
                    }
                }
            }
        	direction = 1;
        	if(obj.getAttribute("moved") == 0){
                for(var i = picnumber; i > dragpicnumber; i--){
                    var tempobj = obj.parentNode.childNodes[i-1];
                    tempobj.setAttribute("moved", 1);
                    var tempobjalt = tempobj.alt.split("_");
                    var movethumb = obj.parentNode.childNodes[i-2];
                    var movethumbalt = movethumb.alt.split("_");
                    var left = parseInt(movethumbalt[2]) + 0.5*(parseInt(movethumbalt[0])-parseInt(tempobjalt[0]));
                    var top = parseInt(movethumbalt[3]) + 0.5*(parseInt(movethumbalt[1])-parseInt(tempobjalt[1]));
                    fw.move(tempobj, 1, left, top);
                }
            } else {
            	for(var i = picnumber; i < obj.parentNode.childNodes.length; i++){
                	var tempobj = obj.parentNode.childNodes[i-1];
                	if(tempobj.getAttribute("moved") == 1){
	                    tempobj.setAttribute("moved", 0);
                    	var tempobjalt = tempobj.alt.split("_");
                    	var left = tempobjalt[2];
                        var top = tempobjalt[3];
                        fw.move(tempobj, 1, left, top);
                    } else {
                    	break;
                    }
                }
            }
        }

        hoverobj = obj;
    }
    
    function saveneworder(oldpos, newpos){
    	var params = "?gallery=" + currentfolder + "&oldpos=" + oldpos + "&newpos=" + newpos;
		fw.getHTTP("<?php echo $basedir ?>ki_saveneworder.php" + params, savedneworder);
    }
    
    function savedneworder(responseText){
    	if(responseText != "ok"){
        	var savedfile = currentfolder + "_dir";
        	alert("ERROR: File '"+savedfile+"' could not be saved/modified.");
        }
    }
    
	this.close = function(){
	    kib.hidehelp();
        var obj = $("kic_changediv").childNodes;
        for(var i = 0; i < obj.length - 1; i++){
        	obj[i].src = "";
        }
		fw.removejs("kic");
        kib.reinit();
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
	
	function kic_taste(aEvent) {
		aEvent = aEvent ? aEvent : window.event;
        var keyCode = aEvent.keyCode;
		if(keyCode == 27){
			kic.close();
			preventDefaultAction(aEvent);
			return false;
		}
	}

}