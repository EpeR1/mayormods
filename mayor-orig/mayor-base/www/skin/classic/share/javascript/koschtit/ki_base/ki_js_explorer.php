<?php
ob_start("ob_gzhandler");

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

header("Content-Type: application/x-javascript");
?>

// Explorer module 'kie' --------------------------------------------------------------------

function kie_module(){

	/* -------------------------------- variables ----------------------------------- */

	var windowX;
	var windowY;
    var gallerynumber = -1;
	
	/* -------------------------------- getElementById function --------------------- */
	
	function $(id){
		return document.getElementById(id);
	}

	/* -------------------------------- constructor/descturctor --------------------- */

	this.constr = function(params){
        gallerynumber = params[0];    
		var node;
		if(!$("kie_maindiv")){
			node = document.createElement("div");
			node.id = "kie_maindiv";
            node.style.background = "<?php echo $fr_color ?>";
			node.style.position = "<?php echo $posfix ?>";
			node.style.left = "0px";
			node.style.top = "0px";
			node.style.zIndex = 1000;
			node.style.overflowX = "hidden";
			node.style.overflowY = "auto";
			node.style.display = "none";
			document.body.appendChild(node);
		}
		if(!$("kie_tb")){
            <?php $breite = getimagesize("ki_nav_close.png"); ?>
			var tbcontent = "<?php draw_image("ki_nav_close.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kie.closeGallery();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_close), ENT_QUOTES, "UTF-8")."'"); ?>";
			node = document.createElement("div");
			node.id = "kie_tb";
			node.style.position = "<?php echo $posfix ?>";
			node.style.top = "-20px";
            node.style.left = "50%";
            node.style.marginLeft = "-<?php echo 0.5*($breite[0]+14) ?>px";
            node.style.background = "<?php echo $nav_color ?>";
            <?php if($nav_style == 2){ ?>
            node.style.borderRadius = "20px";
            node.style.MozBorderRadius = "20px";
            node.style.WebkitBorderRadius = "20px";
            <?php } ?>
            node.style.border = "2px solid <?php echo $nav_border_color ?>";                        
			node.style.zIndex = 10000;
            node.style.padding = "20px 3px 3px 3px";
            node.style.lineHeight = "12px";
			node.innerHTML = tbcontent;
			document.body.appendChild(node);
		}
        if(!$("kie_wait")){
        node = document.createElement("div");
			node.id = "kie_wait";
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
            node.innerHTML = "Please wait, while folder being scanned ...";
			document.body.appendChild(node);
        }
		<?php addEvent("window", "resize", "viewdim"); ?>
		viewdim();
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            addEvent("document", "keypress", "taste");
        } else {
            addEvent("document", "keydown", "taste");
        }
        ?>
        openGallery();
	}
    
	this.destr = function(){
		<?php removeEvent("window", "resize", "viewdim"); ?>
		document.body.removeChild($("kie_maindiv"));
		document.body.removeChild($("kie_tb"));
        document.body.removeChild($("kie_wait"));
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            removeEvent("document", "keypress", "taste");
        } else {
            removeEvent("document", "keydown", "taste");
        }
        ?>

	}
	
	/* -------------------------------- methodes ------------------------------------ */

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
        var maindiv = $("kie_maindiv");
        maindiv.style.width = windowX + "px";
        maindiv.style.height = windowY + "px";
       	var container = $("kie_maindiv").childNodes;
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
		fw.shadebody(1, Array( function(){
			$("kie_tb").style.display = "block";
			var params = "?reldir=<?php echo $reldir ?>&gallery=" + kib.dirs[gallerynumber] + "&gallerynumber=" + gallerynumber;
			fw.getHTTP("<?php echo $basedir ?>ki_explorer.php" + params, kie.gotGallery);
		} ));
	}
	
	this.gotGallery = function(responseText){
    	$("kie_wait").style.display = "none";
		var maindiv = $("kie_maindiv");
		maindiv.innerHTML = responseText;
		maindiv.style.display = "block";
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
    	var container = $("kie_maindiv").childNodes;
        var act_width;
        var act_height;
        for(var i = 0; i < container.length - 1; i++){
            <?php if($th_to_square == 0){ ?>
            var obj = kib.pics[gallerynumber][i];
            if(obj.x >= obj.y){
                var k = obj.y/obj.x;
                act_width = th_width;
                act_height = k*act_width;
                if(act_height > th_height){
                    act_height = th_height;
                    act_width = (1/k) * act_height;
                }
			} else {
                var k = obj.x/obj.y;
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
            
            var bild = container[i];            
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
		$("kie_maindiv").lastChild.style.top = ypos + (0.5*act_height) + "px";
        $("kie_maindiv").lastChild.style.height = 0.5*th_height + <?php echo 2*$th_bord_size+$explorer_padding ?> + "px";
    }

	this.getImage = function(picstring) {
        $("kie_maindiv").style.zIndex = 99;
        $("kie_tb").style.display = "none";
		kib.getImage(picstring);
	}
	
	this.closeGallery = function(){
        var obj = $("kie_maindiv").childNodes;
        for(var i = 0; i < obj.length - 1; i++){
        	obj[i].src = "";
        }
		fw.removejs("kie");
		fw.shadebody(0);	
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
	
	function taste(aEvent) {
		aEvent = aEvent ? aEvent : window.event;
        var keyCode = aEvent.keyCode;
		if(keyCode == 27){
        	if(typeof(kiv_module) == "function")return;
			kie.closeGallery();
			preventDefaultAction(aEvent);
			return false;
		}
	}

}