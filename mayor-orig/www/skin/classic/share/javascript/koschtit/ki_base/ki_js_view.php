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

$confdir = $reldir."ki_config/";
$galleriesdir = $reldir."ki_galleries/";
$basedir = $reldir."ki_base/";

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

header("Content-Type: application/x-javascript");
?>

// Viewing module 'kiv' --------------------------------------------------------------------

function kiv_module(){
	
	/* -------------------------------- variables ----------------------------------- */

	var cur_gallery = -1;
	var cur_pic = -1;
	var preloading;
	var breakpreloading = 0;
	var cur_load = 0;
	var windowX;
	var windowY;
	var oversizeX = 0;
	var oversizeY = 0;
	var posX;
	var posY;
	var previewing = 0;
	var ss = -1;
	var stop = 0;
    var flipped = 2;
	
	/* -------------------------------- getElementById function --------------------- */
	
	function $(id){
		return document.getElementById(id);
	}

	/* -------------------------------- constructor/descturctor --------------------- */

	this.constr = function(params){
		<?php addEvent("window", "resize", "viewdim"); ?>
        viewdim();    
		var node;
		if(!$("kiv_picdiv")){
			node = document.createElement("div");
			node.id = "kiv_picdiv";
			node.style.position = "<?php echo $posfix ?>";
			node.style.zIndex = 1000;
			node.style.background = "<?php echo $bord_color ?>";
            node.style.padding = "0px";
			node.style.display = "none";
            node.style.overflow = "hidden";
			document.body.appendChild(node);
		}
        
        if(!$("kiv_comdiv")){
			node = document.createElement("div");
			node.id = "kiv_comdiv";
			node.style.position = "<?php echo $posfix ?>";
			node.style.zIndex = 1000;
			node.style.background = "<?php echo $bord_color ?>";
            node.style.padding = "10px";
			node.style.display = "none";
            node.style.overflow = "hidden";
            node.style.font = "<?php echo $comm_text_size."px ".$comm_text_font ?>";
            node.style.color = "<?php echo $comm_text_color ?>";
            fw.dropshadow(node, 1);
			document.body.appendChild(node);
		}
        
        if(!$("kiv_inshadow")){
			node = document.createElement("div");
			node.id = "kiv_inshadow";
			node.style.position = "relative";
			node.style.left = "0%";
            node.style.top = "0%";
			node.style.zIndex = 1000;
			node.style.background = "none";
            node.style.padding = "0px";
            node.style.border = "<?php echo $bord_size ?>px solid <?php echo $bord_color ?>";
            node.style.width = "0px";
            node.style.height = "0px";
			node.style.display = "none";
            node.style.overflow = "hidden";
            node.style.cursor = "pointer";
            node.onclick = function(){
            	 kiv.goon(1);
            }
            fw.dropshadow(node, 3);
			$("kiv_picdiv").appendChild(node);
		}
        if(!$("kiv_closebutton")){
            var tbcontent = "<?php draw_image("ki_nav_close.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.closeImage();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_close), ENT_QUOTES, "UTF-8")."'"); ?>";
            node = document.createElement("div");
            node.id = "kiv_closebutton";
            node.style.position = "absolute";
            node.style.top = "0px";
            node.style.right = "0px";
            node.style.background = "<?php echo $bord_color ?>";
            node.style.zIndex = 10000;
            node.style.padding = "1px 1px 3px 1px";
            node.style.lineHeight = "12px";
            fw.dropshadow(node, 1);
            node.innerHTML = tbcontent;
            $("kiv_inshadow").appendChild(node);
        }
		<?php if($show_help == 1){ ?>
        if(!$("kiv_help")){
            var nodeh = document.createElement("div");
            nodeh.id = "kiv_help";
            nodeh.style.color = "<?php echo $comm_text_color ?>";
            nodeh.style.font = "12px Tahoma, sans-serif";
            nodeh.style.position = "absolute";
            <?php if($help_pos === "left"){ ?>
            nodeh.style.left = "4px";                    
            <?php } else { ?>
            nodeh.style.right = "4px";
            <?php } ?>
            nodeh.style.bottom = "4px";
            nodeh.style.lineHeight = "19px";
            nodeh.style.textAlign = "left";
            nodeh.style.zIndex = "10";
            if(ss == 1)nodeh.style.display = "none";
            var infosymbol = "<?php draw_image("ki_nav_info.png", "", "border:0px; vertical-align:bottom; display:inline;", ""); ?>";
            var infocontent = "<?php echo htmlentities(stripslashes($help_text), ENT_QUOTES, "UTF-8") ?>";
            infocontent = infocontent.replace("[mouse]", "<?php draw_image("ki_mouse_help.png", "", "vertical-align:middle; padding:0px 2px 0px 2px;", ""); ?>");
            nodeh.innerHTML = infosymbol;
            fw.addevent( nodeh, "mouseover", function(){
                nodeh.style.width = "250px";
                nodeh.style.padding = "4px";
                nodeh.style.background = "<?php echo $bord_color ?>";
                nodeh.style.border = "2px solid <?php echo $nav_border_color ?>";
                nodeh.innerHTML = infocontent;
                fw.fade(nodeh, 100);
            } );
            fw.addevent( nodeh, "mouseout", function(){
                nodeh.style.width = "auto";
                nodeh.style.padding = "0px";
                nodeh.style.background = "none";
                nodeh.style.border = "0px";
                nodeh.innerHTML = infosymbol;
                fw.fade(nodeh, 51);
            } );                
            $("kiv_inshadow").appendChild(nodeh);
            setTimeout( function(){
                if(nodeh && ss != 1 && nodeh.style.width != "250px" && nodeh.style.display != "none")fw.fade(nodeh, 51);
            }, 5000);
		}
		<?php } ?>        
        <?php if($checkgps == 1){ ?>
        if(!$("kiv_gpspic")){
            var tbcontent = "<?php draw_image("ki_nav_globe.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.opengps();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_gps_coord), ENT_QUOTES, "UTF-8")."'"); ?>";
            node = document.createElement("div");
            node.id = "kiv_gpspic";
            node.style.position = "absolute";
            node.style.top = "0px";
            node.style.left = "0px";
            node.style.background = "<?php echo $bord_color ?>";
            node.style.zIndex = 10000;
            node.style.padding = "1px 1px 3px 1px";
            node.style.lineHeight = "12px";
            fw.dropshadow(node, 1);
            node.style.display = "none";
            node.innerHTML = tbcontent;
            $("kiv_inshadow").appendChild(node);
        }
        <?php } ?>
        
        
		if(!$("kiv_loading")){
			node = document.createElement("img");
			node.id = "kiv_loading";
			<?php $loadimgsize = getimagesize("ki_loading.gif"); ?>
            node.style.margin = "0px";
			node.style.marginLeft = "-<?php echo 0.5*$loadimgsize[0] ?>px";
			node.style.marginTop = "-<?php echo 0.5*$loadimgsize[1] ?>px";
			node.style.position = "<?php echo $posfix ?>";
			node.style.top = "50%";
			node.style.left = "50%";
			node.style.zIndex = 10000;
			node.src = "<?php echo $basedir."ki_loading.gif" ?>";
            node.style.padding = "0px";
            node.style.border = "0px";
			node.style.display = "none";
			document.body.appendChild(node);
		}
        if(!$("kiv_prevdiv")){
			node = document.createElement("div");
			node.id = "kiv_prevdiv";
			node.style.position = "<?php echo $posfix ?>";
			node.style.zIndex = 10000;
			node.style.background = "<?php echo $bord_color ?>";
			node.style.padding = "4px 2px 4px 2px";
			node.style.height = "100px";
			node.style.overflow = "hidden";
			node.style.display = "none";
            fw.dropshadow(node, 1);
			document.body.appendChild(node);
		}
        <?php if($show_preview == 1){ ?>
        if(!$("kiv_wowdiv")){
			node = document.createElement("div");
			node.id = "kiv_wowdiv";
			node.style.position = "<?php echo $posfix ?>";
			node.style.zIndex = 1001;
            node.style.padding = "0px";
			node.style.display = "none";
            <?php if($preview_style == 1){ ?>
            node.style.padding = "<?php echo $bord_size ?>px";
			node.style.background = "<?php echo $bord_color ?>";            
            <?php } ?>
			document.body.appendChild(node);
		}
        <?php } ?>
        <?php $breite = getimagesize("ki_nav_next.png"); ?>
        <?php if($pwok == 0 && $show_share == 1){ ?>
		if(!$("kiv_share")){
			node = document.createElement("div");
			node.id = "kiv_share";
            node.style.width = "280";
            node.style.position = "<?php echo $posfix ?>";
            node.style.top = windowY + "px";
	        node.style.left = "50%";
            node.style.marginLeft = "-145px";
            node.style.background = "<?php echo $nav_color ?>";
            <?php if($nav_style == 2){ ?>
            node.style.borderRadius = "20px";
            node.style.MozBorderRadius = "20px";
            node.style.WebkitBorderRadius = "20px";
            <?php } ?>
            node.style.border = "2px solid <?php echo $nav_border_color ?>";                        
			node.style.zIndex = 1000;
            node.style.padding = "3px 3px 30px 3px";
            node.style.overflow = "hidden";
            node.style.lineHeight = "12px";
			node.style.display = "none";
            var tbcontent = "<?php draw_image("ki_nav_link.png", "", "border:0px; margin:0px 2px 0px 0px; padding:0px; vertical-align:top; display:inline;", "title='Link'"); ?>";
            node.innerHTML = tbcontent + "<input type='text' readonly='readonly' onclick='this.select()' style='width:<?php echo 276-$breite[0]-2 ?>px; height:17px; padding:1px; line-height:17px; margin:0px; background-color:#fff; font-size:12px; font-weight:normal; color:#000; border:1px solid #000;' />";
			document.body.appendChild(node);
		}
		<?php } ?>
        fw.shadebody(1);
		<?php if($show_image_nav == 1){ ?>
  		if(!$("kiv_tb")){
            var breite = <?php echo $breite[0] ?>;
			var tbcontent = "<?php draw_image("ki_nav_close.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.closeImage();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_close), ENT_QUOTES, "UTF-8")."'"); ?>";
			<?php if($viewercomments == 1){ ?>
            breite += <?php echo ($breite[0]+4) ?>;
            tbcontent += "<?php draw_image("ki_nav_vcomm.png", "vcommbutton", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.flip_vcomm();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_vcomm), ENT_QUOTES, "UTF-8")."'"); ?>";
            <?php } ?>
            <?php if($downloadpics == 1){ ?>
            breite += <?php echo ($breite[0]+4) ?>;
            tbcontent += "<?php draw_image("ki_nav_download.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.download();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_download), ENT_QUOTES, "UTF-8")."'"); ?>";
            <?php } ?>
            if(kib.pics[params[0]].length > 1){
				<?php
                if($slideshow == 1){
					echo "breite += ".($breite[0]+4).";";
				?>
				tbcontent += "<?php draw_image("ki_nav_play.png", "ssbutton", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.slideshow();\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($slideshow_start), ENT_QUOTES, "UTF-8")."'"); ?>";
				<?php
                }
				echo "breite += ".(2*($breite[0]+4)).";";
				?>
				tbcontent += "<?php draw_image("ki_nav_prev.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.goon(-1);\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_back), ENT_QUOTES, "UTF-8")."'"); ?>";
				tbcontent += "<?php draw_image("ki_nav_next.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kiv.goon(1);\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_kiv_next), ENT_QUOTES, "UTF-8")."'"); ?>";
			}
			node = document.createElement("div");
			node.id = "kiv_tb";
			node.style.position = "<?php echo $posfix ?>";
			node.style.top = "-<?php echo 2*$breite[0] ?>px";
	        node.style.left = "50%";
            node.style.marginLeft = -(breite+10)/2+"px";
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
			node.style.display = "none";
			node.innerHTML = tbcontent;
			document.body.appendChild(node);
		}
		<?php } ?>
		<?php
		if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
			if($pwok == 0)addEvent("document", "keypress", "taste");
		} else {
			if($pwok == 0)addEvent("document", "keydown", "taste");
		}
		?>
		<?php if($pwok == 1 && $comm_auto == 1 && $show_warnings == 1){ ?>
		alert("WARNING: If you want to write custom comments you have to set '$comm_auto = 0'. Custom comments won't get saved. Set '$show_warnings = 0' if you don't want to see this warning again.");
		<?php } ?>
        kiv.getImage(params[0], params[1]);
	}
	
	this.destr = function(){
		document.body.removeChild($("kiv_picdiv"));
        document.body.removeChild($("kiv_comdiv"));
		<?php if($show_image_nav == 1){ ?>
        document.body.removeChild($("kiv_tb"));
        <?php } ?>
		document.body.removeChild($("kiv_loading"));
		document.body.removeChild($("kiv_prevdiv"));
        <?php if($show_preview == 1){ ?>document.body.removeChild($("kiv_wowdiv"));<?php } ?>
        <?php if($pwok == 0 && $show_share == 1){ ?>document.body.removeChild($("kiv_share"));<?php } ?>
		<?php removeEvent("window", "resize", "viewdim"); ?>
		<?php
		if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
			if($pwok == 0)removeEvent("document", "keypress", "taste");
		} else {
			if($pwok == 0)removeEvent("document", "keydown", "taste");
		}
		?>
	}
	
	/* -------------------------------- methodes ------------------------------------ */

	<?php if($pwok == 1){ ?>
    this.deleteVComm = function(counter){
    	fw.getHTTP("<?php echo $basedir ?>ki_vcomm.php?file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&gallery=" + kib.dirs[cur_gallery] + "&counter=" + counter + "&get=1", kiv.getvcomm, null);
    }
    
    this.publishVComm = function(counter){
    	fw.getHTTP("<?php echo $basedir ?>ki_vcomm.php?file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&gallery=" + kib.dirs[cur_gallery] + "&publish=" + counter + "&get=1", kiv.getvcomm, null);
    }
    
	this.savecomment = function(aEvent, commstring){
		aEvent = aEvent ? aEvent : window.event;
        var keyCode = aEvent.keyCode;
		if(keyCode == 27){
			kiv.closeImage();
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 33 && !aEvent.shiftKey){
			kiv.goon(-1);
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 34 && !aEvent.shiftKey){
			kiv.goon(1);
			preventDefaultAction(aEvent);
			return false;
		}
		if((aEvent.ctrlKey == true && keyCode == 13) || keyCode == 10){
			fw.getHTTP("<?php echo $basedir ?>ki_savecomment.php?file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&gallery=" + kib.dirs[cur_gallery] + "&comment=" + encodeURIComponent(commstring), saved, null);
			preventDefaultAction(aEvent);
			return false;
		}
	}
	
	function saved(){
		stop = 0;
		kiv.goon(1);
	}
    
    function squarethumbhandling(){
        if($("square_selector")){
            var sths = 0;
            var startx = 0;
            var starty = 0;
            var obj = $("square_selector");
            obj.style.display = "block";
            var sub = fw.findPos(obj);
            var objx = parseInt(obj.style.width);
            var objy = parseInt(obj.style.height);
            if(objx == objy)return;
            var wide = objx > objy ? 1 : 0;
            var square_1 = $("square_1").style;
            var square_2 = $("square_2").style;
            var posx = parseInt(square_1.width);
            var posy = parseInt(square_1.height);
            fw.addevent( obj, "mousedown", function(x, y){ 
            	if(sths == 0){
                	sths = 1;
                    startx = x - sub[0];
                    starty = y - sub[1];
                }
            } );
            fw.addevent( obj, "mousemove", function(x, y){
            	if(sths == 1){
                    var movx = (x - sub[0]) - startx;
                    var movy = (y - sub[1]) - starty;
                    if(wide == 1){
                    	var temp = posx + movx;
                        if(temp < 0)temp = 0;
                        if(temp > (objx - 100))temp = objx - 100;
                        square_1.width = temp + "px";
                        square_2.width = objx - (temp + 100) + "px";
                    } else {
                    	var temp = posy + movy;
                    	if(temp < 0)temp = 0;
                        if(temp > (objy - 100))temp = objy - 100;
						square_1.height = temp + "px";
                        square_2.height = objy - (temp + 100) + "px";                    
                    }
                }
            } );
            fw.addevent( obj, "mouseup", function(x, y){ 
            	if(sths == 1){
                	var final = -1;
                	if(wide == 1){
 						var final = posx + (x - sub[0]) - startx;
	                    if(final < 0)final = 0;
	                    if(final > (objx - 100))final = objx - 100;
                    	final = final / objx;
                    } else {
                        var final = posy + (y - sub[1]) - starty;
                        if(final < 0)final = 0;
                        if(final > (objy - 100))final = objy - 100;
                        final = final / objy;
                    }
                    var params = "?file=" + kib.dirs[cur_gallery] + "/" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&pos=" + final;
                    fw.getHTTP("<?php echo $basedir ?>ki_savesquarethumb.php" + params, null);
                    var long = new Date();
					long.setTime(long.getTime() + (10 * 365 * 24 * 60 * 60 * 1000));
					document.cookie = "koschtit_" + kib.dirs[cur_gallery] + cur_gallery + "_" + kib.pics[cur_gallery][cur_pic].file + "=" + final + "; path=/; expires=" + long.toGMTString();
                    obj.style.background = "#00ff00";
                    setTimeout( function(){
	                    obj.style.background = "#000000";
                    }, 400);
                }
                sths = 0;
            } );
            var custompos = -1;
           	var nameEQ = "koschtit_" + kib.dirs[cur_gallery] + cur_gallery + "_" + kib.pics[cur_gallery][cur_pic].file + "=";
            var ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ')c = c.substring(1,c.length);
                if(c.indexOf(nameEQ) == 0)custompos = c.substring(nameEQ.length,c.length);
            }
            if(custompos != -1){
                if(wide == 1){
	                var temp = Math.round(custompos*objx);
                    square_1.width = temp + "px";
                    square_2.width = objx - (temp + 100) + "px";
                } else {
                    var temp = Math.round(custompos*objy);
                    square_1.height = temp + "px";
                    square_2.height = objy - (temp + 100) + "px";                    
                }            
            }
        }
	}
	<?php } ?>

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
        if($("kiv_share"))$("kiv_share").style.top = windowY + "px";
        if($("kiv_comdiv")){
            var com_div = $("kiv_comdiv");
            var sizes = fw.getDim($("kiv_picdiv"), 1);
            var comm_x = com_div.offsetWidth;
            var comm_y = com_div.offsetHeight;
            if(comm_x > sizes[0]){
                comm_x = sizes[0];                
                com_div.style.width = (sizes[0] - 2*10) + "px";
                comm_y = com_div.offsetHeight;                
            }
            <?php
            if($comm_text_align === "left"){
            ?>	
            var x_pos = $("kiv_picdiv").offsetLeft;
            var y_pos = $("kiv_picdiv").offsetHeight + $("kiv_picdiv").offsetTop + 5;
            if(y_pos + comm_y > windowY){
    			y_pos -= comm_y + 10;
                <?php if($pwok == 0){ ?>if(comm_x != sizes[0])x_pos += 5;<?php } ?>
            }
            <?php
            }elseif($comm_text_align === "right"){
            ?>
            var x_pos = $("kiv_picdiv").offsetLeft + sizes[0] - comm_x;
            var y_pos = $("kiv_picdiv").offsetHeight + $("kiv_picdiv").offsetTop + 5;
            if(y_pos + comm_y > windowY){
    			y_pos -= comm_y + 10;
                <?php if($pwok == 0){ ?>if(comm_x != sizes[0])x_pos -= 5;<?php } ?>
            }
            <?php
            }else{
            ?>
            var x_pos = $("kiv_picdiv").offsetLeft + 0.5*(sizes[0] - comm_x);
            var y_pos = $("kiv_picdiv").offsetHeight + $("kiv_picdiv").offsetTop + 5;
            if(y_pos + comm_y > windowY){
    			y_pos -= comm_y + 10;
            }
            <?php
            }
            ?>
            com_div.style.left = x_pos + "px";
            com_div.style.top = y_pos + "px";
        }
	}

	function preloadgallery(){
		if(breakpreloading == 1)return;
		var files = kib.pics[cur_gallery].length;
		cur_load = cur_pic + 1;
		if(cur_load == files)return;
		var loaded = 0;
		while(preloading[cur_load] != null){
			cur_load++;
			loaded++;
			if(loaded == files){
				return;
			}
			if(cur_load == files || cur_load - cur_pic > 3)return;
		}
        preloading[cur_load] = document.createElement('img');
		preloading[cur_load].onload = function(){
			preloadgallery();
		};
		var picsize = calcpicsize(cur_load);
		if(picsize[0] != kib.pics[cur_gallery][cur_load].x){
			preloading[cur_load].src = "<?php echo $basedir ?>ki_makepic.php?fullimg=1&file=" + kib.dirs[cur_gallery] + "/" + encodeURIComponent(kib.pics[cur_gallery][cur_load].file) + "&width=" + picsize[0] + "&height=" + picsize[1];
		} else {
			preloading[cur_load].src = "<?php echo $galleriesdir ?>" + kib.dirs[cur_gallery] + "/" + kib.pics[cur_gallery][cur_load].file;
		}
	}

	this.getImage = function(gallerynumber, picnumber){
		if(stop == 1)return;
		var firsttoshow = 0;
		if(cur_gallery == -1){
			firsttoshow = 1;
			preloading = new Array(kib.pics[gallerynumber].length);
		}
        if($("kib_helpbox")){
        	kib.hidehelp();
        }
        <?php removeEvent("document", "mousemove", "mousemoved"); ?>
        if($("kiv_vcomments_form")){
        	$("kiv_picdiv").removeChild($("kiv_vcomments_form"));
        	if($("kiv_vcomments_list"))$("kiv_picdiv").removeChild($("kiv_vcomments_list"));
        }
        <?php if($show_preview == 1){ ?>kiv.removewow();<?php } ?>
        <?php if($checkgps == 1){ ?>fw.fade("kiv_gpspic", 0);<?php } ?>
		if(gallerynumber == -1)gallerynumber = cur_gallery;
		breakpreloading = 1;
		preloading[cur_load] = null;
		cur_gallery = gallerynumber;
		cur_pic = picnumber;
		var picsize = calcpicsize(picnumber);
		<?php if($oversize_allowed == 1){ ?>
		oversizeX = posX = 0;
		oversizeY = posY = 0;
		if(picsize[0]+2*<?php echo $bord_size ?> > windowX)oversizeX = Math.round((picsize[0] - windowX)*0.5) + <?php echo $bord_size ?>;
		if(picsize[1]+2*<?php echo $bord_size ?> > windowY)oversizeY = Math.round((picsize[1] - windowY)*0.5) + <?php echo $bord_size ?>;
		<?php } ?>
		var params = "?reldir=<?php echo $reldir ?>&gallery=" + kib.dirs[cur_gallery] + "&file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&x=" + picsize[0] + "&y=" + picsize[1];
		fw.getHTTP("<?php echo $basedir ?>ki_fullimg.php" + params, kiv.gotImage, Array(picsize, firsttoshow, 0));
	}
	
	this.gotImage = function(responseText, params){
		if(stop == 1)return;
		var picdiv = $("kiv_picdiv");
        var inshad = $("kiv_inshadow");
		var picdivsize = params[0];
		var firsttoshow = params[1];
		if(params[2] == 0){ // Bild wird geladen
			picdivsize[0] += <?php echo 2*$bord_size ?>;
			picdivsize[1] += <?php echo 2*$bord_size ?>;
            
			if(firsttoshow == 1){ // Einfügen/Warten von onload beim ersten Öffnen
            
				picdiv.style.top = "50%";
				picdiv.style.left = "50%";
				picdiv.style.width = "0px";
				picdiv.style.height = "1px";
				picdiv.style.display = "block";
                
                var node = document.createElement("div");
                node.innerHTML = responseText;
                var temp = node.childNodes.length;
                for(var i = 0; i < temp; i++){
                    picdiv.appendChild(node.childNodes[0]);
                }

				var thepic = $("thepicture");
                thepic.onload = function(){
                    if(kiv != null && thepic == $("thepicture"))kiv.gotImage("", Array(picdivsize, firsttoshow, 1));
                };
                thepic.src = thepic.alt;
                setTimeout( function(){ if(kiv != null && thepic != null){if(thepic.alt != "" && !thepic.complete){fw.fade("kiv_loading", 100, Array(function(){$("kiv_loading").src = "<?php echo $basedir."ki_loading.gif" ?>";}));$("kiv_picdiv").style.zIndex = 99;}} }, 750);
                
			} else { // Einfügen/Warten von onload beim Weiterklicken
            	
            	<?php if($pwok == 1){ ?>
            	if($("square_selector"))picdiv.removeChild($("square_selector"));
                <?php } ?>
				<?php if($checkgps == 1){ ?>
                if($("kiv_gps"))picdiv.removeChild($("kiv_gps"));
                <?php } ?>
            	<?php if($viewercomments == 1){ ?>
            	if($("kiv_vcomm"))picdiv.removeChild($("kiv_vcomm"));
                <?php } ?>
				if($("thepicture"))$("thepicture").id = "oldpic";
                
                fw.fade("kiv_comdiv", 0);
                
                var node = document.createElement("div");
                node.innerHTML = responseText;
                var temp = node.childNodes.length;
                for(var i = 0; i < temp; i++){
                    picdiv.appendChild(node.childNodes[0]);
                }
                var thepic = $("thepicture");
                thepic.onload = function(){
                    if(kiv != null && thepic == $("thepicture"))kiv.gotImage("", Array(picdivsize, firsttoshow, 1));
                };
                thepic.src = thepic.alt;
                setTimeout( function(){ if(kiv != null && thepic != null){if(thepic.alt != "" && !thepic.complete){fw.fade("kiv_loading", 100, Array(function(){$("kiv_loading").src = "<?php echo $basedir."ki_loading.gif" ?>";}));$("kiv_picdiv").style.zIndex = 99;}} }, 750);

 
			}
            
		} else { // Bild wurde geladen -> Bild anzeigen
        
            $("kiv_loading").style.display = "none";
			if(previewing == 0){
            	$("kiv_picdiv").style.zIndex = 1000;
                $("kiv_comdiv").style.zIndex = 1000;
			}
	        preloading[cur_pic] = 1;
            breakpreloading = 0;
       		preloadgallery();

            <?php if($pwok == 0 && $show_share == 1){ ?>
            var shareadd = location.search;
            if(shareadd.indexOf('kit_code=') != -1)
            	shareadd = shareadd.substr(0, shareadd.indexOf('kit_code=')-1);
            if(shareadd.indexOf('?') == -1)
            	shareadd = "?" + shareadd;
            else
            	shareadd += "&";
            var slash = "";
            if(location.pathname.substr(0, 1) != "/")slash = "/";
			shareadd = "http://" + location.hostname + slash + location.pathname + shareadd + "kit_code=" + cur_gallery+"_"+cur_pic + location.hash;
            $("kiv_share").childNodes[1].value = shareadd;
            <?php } ?>
            
			var thepic = $("thepicture");
            if(thepic != null){
                thepic.alt = "";
				thepic.style.display = "none";
				thepic.style.visibility = "visible";
            }
            flipped = 0;
            
			if(firsttoshow == 1){ // Bild wurde geladen -> Bild anzeigen beim ersten Öffnen
                
				fw.resize( picdiv, 2, picdivsize[0], 1, Array( function(){
                	
                    fw.resize( picdiv, 2, picdivsize[0], picdivsize[1], Array( function(){ 
                        inshad.style.width = picdivsize[0] - 2*<?php echo $bord_size ?> + "px";
                        inshad.style.height = picdivsize[1] - 2*<?php echo $bord_size ?> + "px";
                        inshad.style.display = "block";
                        fw.dropshadow(picdiv, 1);
                        fw.fade("thepicture", 100, Array( function(){
                        
                            <?php if($show_image_nav == 1){ ?>
                            fw.move("kiv_tb", 3, 0, -<?php echo $breite[0] ?>);
                            $("kiv_tb").setAttribute("contr", "stay");
                            setTimeout( function() {
                                if($("kiv_tb"))$("kiv_tb").setAttribute("contr", "");
                            }, 5000);
                            <?php } ?>
                            <?php if($pwok == 0 && $show_share == 1){ ?>
                            fw.move("kiv_share", 3, 0, windowY - 29);
                            $("kiv_share").setAttribute("contr", "stay");
                            setTimeout( function() {
                                if($("kiv_share"))$("kiv_share").setAttribute("contr", "");
                            }, 5000);
                            <?php } ?>
                            viewdim();
                            showcomment();
                            checkgps();
                            checkvcomment();
                            var focme = $("focusme");
                            if(focme){
	                            kib.showhelp(focme, 4, 2);
							}
                            <?php addEvent("document", "mousemove", "mousemoved"); ?>
                            <?php if($pwok == 1){ ?>
                            squarethumbhandling();
                            <?php } ?>
                            if(ss == 1)setTimeout( function(){if(kiv != null){if(ss == 1){ss = 2;kiv.goon(1);}}} , <?php echo $slideshow_time ?>);
                            
                        } ));
                    } ));
				} ));
                
			} else { // Bild wurde geladen -> Bild anzeigen beim Weiterklicken
				
                fw.fade("oldpic", 0, Array( function(){
					var picdiv = $("kiv_picdiv");
					for(var i = 0; i < picdiv.childNodes.length; i++){
						if(picdiv.childNodes[i].id == "oldpic"){
							picdiv.removeChild(picdiv.childNodes[i]);
                            i-=1;
						}
					}
				} ));
				fw.fade("thepicture", 100, Array( function(){
	                inshad.style.display = "block";
                	fw.resize( inshad, 1, picdivsize[0] - 2*<?php echo $bord_size ?>, picdivsize[1] - 2*<?php echo $bord_size ?> );
                    fw.resize( picdiv, 2, picdivsize[0], picdivsize[1], Array( function(){
                        showcomment();
                        checkgps();
                        checkvcomment();                    
                        if(previewing == 0){
                            <?php addEvent("document", "mousemove", "mousemoved"); ?>
                        }
                        <?php if($pwok == 1){ ?>
                        squarethumbhandling();
                        <?php } ?>
                        <?php if($show_help == 1){ ?>if(ss != 1)fw.fade("kiv_help", 51);<?php } ?>
                        if(ss == 1)setTimeout( function(){if(kiv != null){if(ss == 1){ss = 2;kiv.goon(1);}}} , <?php echo $slideshow_time ?>);
                    } ));
				} ));
			}
		}
	}
    
    function checkgps(){
    	<?php if($checkgps == 1){ ?>
    	if(stop == 1 || ss == 1)return;
        if(!$("kiv_gps"))return;
        var coordinates = $("kiv_gps").value;
        if(coordinates != "0,0"){
        	fw.fade("kiv_gpspic", 100);
        } else {
        	<?php if($cellinfo == 1){ ?>
            var params = "?gallery=" + kib.dirs[cur_gallery] + "&file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file);
            fw.getHTTP("<?php echo $basedir ?>ki_cellid.php" + params, gotCellInfo, Array(cur_gallery, cur_pic));
            <?php } ?>
        }
        <?php } ?>
    }
    
    <?php if($cellinfo == 1 && $checkgps == 1){ ?>
    function gotCellInfo(responseText, params){
    	if(stop == 1 || ss == 1)return;
    	if(cur_gallery != params[0] || cur_pic != params[1])return;
		if(responseText == "0,0")return;
      	$("kiv_gps").value = responseText;
		fw.fade("kiv_gpspic", 100);
    }
    <?php } ?>

	<?php if($checkgps == 1){ ?>    
    this.opengps = function(){
    	if($("kiv_gps")){
        	if($("kiv_gps").value != "0,0"){
            	var coordinates = $("kiv_gps").value;
                var filename = kib.pics[cur_gallery][cur_pic].file;
				filename = filename.substr(0, filename.length-4);
                window.open("http://maps.google.com/?q=" + filename + "@" + coordinates);
            }
        }
    }
    <?php } ?>
    
    function checkvcomment(){
    	<?php if($viewercomments == 1 && $show_image_nav == 1){ ?>
    	if(stop == 1 || ss == 1)return;
        if(!$("vcommbutton"))return;
        if(!$("kiv_vcomm"))return;
        var obj = $("vcommbutton");
        if($("kiv_vcomm").value == "1"){
        	var tb = obj.parentNode;
            tb.setAttribute("contr", "stay");
            fw.move(tb, 3, 0, -<?php echo $breite[0] ?>, Array(function(){
				<?php if($browser !== "ie6"){ ?>
                fw.fade(obj, 10, Array(fw.fade, obj, 100, Array(fw.fade, obj, 10, Array(fw.fade, obj, 100, Array(fw.fade, obj, 10, Array(fw.fade, obj, 100, Array(function(){
					if(tb){
                    	tb.setAttribute("contr", "");
					}
                })))))));
                <?php } else { ?>
                setTimeout( function(){
                    if(tb){
                        tb.setAttribute("contr", "");
                    }
				}, 2000);
                <?php } ?>
            }));
        }
        <?php } ?>
    }    
	
    <?php if($show_preview == 1){ ?>
    this.removewow = function(){
	    var wowdiv = $("kiv_wowdiv");
		wowdiv.innerHTML = "";
        wowdiv.style.display = "none";
        fw.dropshadow(wowdiv, 0);
	    <?php if($preview_style == 2){ ?>
        var blende = $("fw_blend");
        blende.style.cursor = "auto";
        blende.onclick = null;
        <?php } ?>
    }
    <?php } ?>
    
	function mousemoved(aEvent) {
    	if(stop == 1)return;
		aEvent = aEvent ? aEvent : window.event;
		var x = aEvent.clientX ? aEvent.clientX : aEvent.pageX;
		var y = aEvent.clientY ? aEvent.clientY : aEvent.pageY;

		<?php if($show_image_nav == 1 && $image_nav_always == 0){ ?>
		var tb = $("kiv_tb");
        if(y > windowY * 0.33 || x > (windowX/2+200) || x < (windowX/2-200)){
        	if(tb.getAttribute("contr") == ""){
            	fw.move(tb, 3, 0, -<?php echo $breite[0]*3 ?>);
                tb.setAttribute("contr", "open");
			}
        } else {
        	if(tb.getAttribute("contr") == "open"){
                fw.move(tb, 3, 20, -<?php echo $breite[0] ?>);
                tb.setAttribute("contr", "");
			}
        }
		<?php } ?>
        
		<?php if($pwok == 0 && $show_share == 1 && $image_nav_always == 0){ ?>
		var sh = $("kiv_share");
        if(y < windowY - 150 || x > (windowX/2+300) || x < (windowX/2-300)){
        	if(sh.getAttribute("contr") == ""){
            	<?php if(in_array($browser, array("ie6", "ie7", "ie9"))){ ?>
            	if(aEvent.srcElement.nodeName == "INPUT")return;
                <?php } ?>
            	fw.move(sh, 3, 0, windowY);
                sh.setAttribute("contr", "open");
			}
        } else {
        	if(sh.getAttribute("contr") == "open"){
                fw.move(sh, 3, 20, windowY - 29);
                sh.setAttribute("contr", "");
			}
        }
		<?php } ?>

		if(!x)x = 1;        
		if(ss == 1)return;

		var dim = fw.getDim($("kiv_picdiv"));
		var wowdiv = $("kiv_wowdiv");

		<?php
        if($show_preview == 1){
			if($preview_style == 1){
		?>
        if(windowX - dim[0] >= 0.33*windowX){ // Bild bedeckt max 2/3 des Bildschirmplatzes
            if(x > (windowX + dim[0])*0.5 && cur_pic != kib.pics[cur_gallery].length-1){
                if(y > (windowY-dim[1])*0.5 && y < (windowY+dim[1])*0.5){
                    if(!wowdiv.firstChild){
                        var picsize = calcpicsize(cur_pic+1);
                        var picsrc = "";
                        if(picsize[0] != kib.pics[cur_gallery][cur_pic+1].x){
                            picsrc = "<?php echo $basedir ?>ki_makepic.php?fullimg=1&file=" + kib.dirs[cur_gallery] + "/" + encodeURIComponent(kib.pics[cur_gallery][cur_pic+1].file) + "&width=" + picsize[0] + "&height=" + picsize[1];
                        } else {
                            picsrc = "<?php echo $galleriesdir ?>" + kib.dirs[cur_gallery] + "/" + kib.pics[cur_gallery][cur_pic+1].file;
                        }
                        wowdiv.style.zIndex = 10000;
                        wowdiv.innerHTML = "<img src='"+picsrc+"' style='vertical-align:middle; visibility:hidden; cursor:pointer;' onload=\"this.style.visibility='visible'\" onclick='kiv.goon(1);' /><div id='kiv_wowdiv_inshadow' style='position:absolute; left:0%; top:0%; background:none; padding:0px; border:<?php echo $bord_size ?>px solid <?php echo $bord_color ?>; cursor:pointer;' onclick='kiv.goon(1);'></div>";
                        wowdiv.style.left = (windowX + dim[0])*0.5 - 30 + "px";
                        fw.dropshadow("kiv_wowdiv_inshadow", 3);
                        fw.dropshadow(wowdiv, 1);
                        fw.fade(wowdiv, 100);
                    }
                    var obj = kib.pics[cur_gallery][cur_pic+1];
                    var breite = x - (windowX + dim[0])*0.5 + 50;
                    var hoehe = (obj.y/obj.x)*breite;
                    var maxh = dim[1]*0.66;
                    if(hoehe > maxh){
                        hoehe = maxh;
                        breite = (obj.x/obj.y)*hoehe;
                    }
                    var pic = wowdiv.firstChild;
                    pic.style.width = breite + "px";
                    pic.style.height = hoehe + "px";
                    wowdiv.style.top = y + "px";                    
                    wowdiv.style.marginTop = -0.5*(hoehe+<?php echo $bord_size ?>*2) + "px";
                    var inshad = $("kiv_wowdiv_inshadow");
                    inshad.style.width = breite + "px";
                    inshad.style.height = hoehe + "px";
                }
            } else if(x < (windowX - dim[0])*0.5 && cur_pic != 0){
                if(y > (windowY-dim[1])*0.5 && y < (windowY+dim[1])*0.5){
                    if(!wowdiv.firstChild){
                        var picsize = calcpicsize(cur_pic-1);
                        var picsrc = "";
                        if(picsize[0] != kib.pics[cur_gallery][cur_pic-1].x){
                            picsrc = "<?php echo $basedir ?>ki_makepic.php?fullimg=1&file=" + kib.dirs[cur_gallery] + "/" + encodeURIComponent(kib.pics[cur_gallery][cur_pic-1].file) + "&width=" + picsize[0] + "&height=" + picsize[1];
                        } else {
                            picsrc = "<?php echo $galleriesdir ?>" + kib.dirs[cur_gallery] + "/" + kib.pics[cur_gallery][cur_pic-1].file;
                        }
                        wowdiv.style.zIndex = 999;
                        wowdiv.innerHTML = "<img src='"+picsrc+"' style='vertical-align:middle; visibility:hidden; cursor:pointer;' onload=\"this.style.visibility='visible'\" onclick='kiv.goon(-1);' /><div id='kiv_wowdiv_inshadow' style='position:absolute; left:0%; top:0%; background:none; padding:0px; border:<?php echo $bord_size ?>px solid <?php echo $bord_color ?>; cursor:pointer;' onclick='kiv.goon(-1);'></div>";
                        fw.dropshadow("kiv_wowdiv_inshadow", 3);
                        fw.dropshadow(wowdiv, 1);
                        fw.fade(wowdiv, 100);
                    }
                    var obj = kib.pics[cur_gallery][cur_pic-1];
                    var breite = (windowX - dim[0])*0.5 - x + 50;
                    var hoehe = (obj.y/obj.x)*breite;
                    var maxh = dim[1]*0.66;
                    if(hoehe > maxh){
                        hoehe = maxh;
                        breite = (obj.x/obj.y)*hoehe;
                    }
                    var pic = wowdiv.firstChild;
                    pic.style.width = breite + "px";
                    pic.style.height = hoehe + "px";
                    wowdiv.style.left = (windowX - dim[0])*0.5 + 30 - breite - <?php echo $bord_size ?>*2 + "px";
                    wowdiv.style.top = y + "px";                    
                    wowdiv.style.marginTop = -0.5*(hoehe+<?php echo $bord_size ?>*2) + "px";
                    var inshad = $("kiv_wowdiv_inshadow");
                    inshad.style.width = breite + "px";
                    inshad.style.height = hoehe + "px";
                }
            } else {
                if(wowdiv.firstChild){
                    kiv.removewow();
                }
            }
		}
        <?php 
			} else {
				$temp = getimagesize("ki_next.png");
		?>
        if(windowX - dim[0] >= 80){ // Mindestens 40 Pixel Platz zu jeder Seite
            if(x > (windowX + dim[0])*0.5 && cur_pic != kib.pics[cur_gallery].length-1){
                if(y > (windowY-dim[1])*0.5 && y < (windowY+dim[1])*0.5){
                    if(!wowdiv.firstChild){
                        wowdiv.innerHTML = "<?php draw_image("ki_next.png", "", "cursor:pointer; vertical-align:middle;", "onclick='kiv.goon(1);' alt='' "); ?>";
                        wowdiv.style.left = (windowX + dim[0])*0.5 + 5 + "px";
                        wowdiv.style.top = "50%";
                        wowdiv.style.marginTop = -0.5*(<?php echo $temp[1] ?>) + "px";
                        fw.fade(wowdiv, 100);
                        var blende = $("fw_blend");
                        blende.style.cursor = "pointer";
                        blende.onclick = function(){
                            kiv.goon(1);
                        }
                    }
                }
            } else if(x < (windowX - dim[0])*0.5 && cur_pic != 0){
                if(y > (windowY-dim[1])*0.5 && y < (windowY+dim[1])*0.5){
                    if(!wowdiv.firstChild){
                        wowdiv.innerHTML = "<?php draw_image("ki_back.png", "", "cursor:pointer; vertical-align:middle;", "onclick='kiv.goon(-1);' alt='' "); ?>";
                        wowdiv.style.left = (windowX - dim[0])*0.5 - 5 - <?php echo $temp[0] ?> + "px";
                        wowdiv.style.top = "50%";
                        wowdiv.style.marginTop = -0.5*(<?php echo $temp[1] ?>) + "px";
                        fw.fade(wowdiv, 100);
                        var blende = $("fw_blend");
                        blende.style.cursor = "pointer";
                        blende.onclick = function(){
                            kiv.goon(-1);
                        }                        
                    }
                }
            
            } else {
                if(wowdiv.firstChild){
                    kiv.removewow();
                }
            }
		}
        <?php 
			}
		}
		?>

		<?php if($preview_pics > 0){ ?>
        if(windowX - dim[0] < 200)return; // Mindestens 100 Pixel Platz zu jeder Seite
		if(x > windowX - 40 && cur_pic != kib.pics[cur_gallery].length-1){
            <?php if($show_preview == 1){ ?>
            if(wowdiv.firstChild){
                fw.dropshadow(wowdiv, 0);
                fw.fade(wowdiv, 0, Array( function(){
                    wowdiv.innerHTML = "";
                } ));
            }
            <?php } ?>
			<?php removeEvent("document", "mousemove", "mousemoved"); ?>
			previewing = 1;
			var prevdiv = $("kiv_prevdiv");
			prevdiv.style.top = y - 54 + "px";
			prevdiv.style.left = windowX + 500 + "px";
			prevdiv.style.width = "20px";
			$("kiv_picdiv").style.zIndex = 99;
            $("kiv_comdiv").style.zIndex = 99;
            prevdiv.style.display = "block";
			fw.move(prevdiv, 2, windowX - 20, 0, Array( function(){
				var params = "?reldir=<?php echo $reldir ?>&gallery=" + kib.dirs[cur_gallery] + "&file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&topic=1";
				fw.getHTTP("<?php echo $basedir ?>ki_preview.php" + params, kiv.showpreview, 1);
				<?php addEvent("$('fw_blend')", "mousemove", "closepreview"); ?>
			} ));
		} else if(x < 40 && cur_pic != 0){
        	<?php if($show_preview == 1){ ?>
        	if(wowdiv.firstChild){
                fw.dropshadow(wowdiv, 0);
                fw.fade(wowdiv, 0, Array( function(){
                	wowdiv.innerHTML = "";
                } ));
            }
            <?php } ?>
			<?php removeEvent("document", "mousemove", "mousemoved"); ?>
			previewing = 1;
			var prevdiv = $("kiv_prevdiv");
			prevdiv.style.top = y - 54 + "px";
			prevdiv.style.left = "-500px";
			prevdiv.style.width = "20px";
			$("kiv_picdiv").style.zIndex = 99;
            $("kiv_comdiv").style.zIndex = 99;
            prevdiv.style.display = "block";
			fw.move(prevdiv, 2, 0, 0, Array( function(){
				var params = "?reldir=<?php echo $reldir ?>&gallery=" + kib.dirs[cur_gallery] + "&file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&topic=-1";
				fw.getHTTP("<?php echo $basedir ?>ki_preview.php" + params, kiv.showpreview, -1);
				<?php addEvent("$('fw_blend')", "mousemove", "closepreview"); ?>
			} ));
		}
		<?php } ?>        
	}
	
	this.showpreview = function(responseText, topic){
		if(stop == 1 || previewing == 0)return;
		var prevdiv = $("kiv_prevdiv");
		prevdiv.innerHTML = responseText;
		var gesbreite = $("gesbreite").value;
		if(topic == 1){
			prevdiv.style.width = gesbreite + "px";
			fw.move(prevdiv, 2, windowX - gesbreite - 4, 0);
		} else {
			prevdiv.style.left = -1*gesbreite + 20 + "px";
			prevdiv.style.width = gesbreite + "px";
			fw.move(prevdiv, 2, 0, 0);
		}	
	}
	
	function closepreview(aEvent){
		if(stop == 1)return;
		aEvent = aEvent ? aEvent : window.event;
		var x = aEvent.clientX ? aEvent.clientX : aEvent.pageX;
		var y = aEvent.clientY ? aEvent.clientY : aEvent.pageY;

		if(x > windowX - 40 || x < 40)return;
               
		<?php removeEvent("$('fw_blend')", "mousemove", "closepreview"); ?>
		previewing = 0;
		var prevdiv = $("kiv_prevdiv");
		if($("kiv_loading").style.display == "none"){
        	$("kiv_picdiv").style.zIndex = 1000;
            $("kiv_comdiv").style.zIndex = 1000;
		}
		if(parseInt(prevdiv.style.left) > 0){
			fw.move(prevdiv, 2, windowX, 0, Array( function(){
				prevdiv.innerHTML = "";
				prevdiv.style.display = "none";
				<?php addEvent("document", "mousemove", "mousemoved"); ?>
			} ));
		} else {
			fw.move(prevdiv, 2, -prevdiv.offsetWidth, 0, Array( function(){
				prevdiv.innerHTML = "";
				prevdiv.style.display = "none";
				<?php addEvent("document", "mousemove", "mousemoved"); ?>			
			} ));
		}
	}
	
	function showcomment(){
		if(stop == 1)return;
        var com_div = $("kiv_comdiv");
        var sizes = fw.getDim($("kiv_picdiv"), 1);
		if($("thecomment")){
			com_div.style.visibility = "hidden";
            com_div.style.display = "block";
            com_div.style.width = "auto";
            
			var commstring = $("thecomment").innerHTML;
			<?php if($comm_auto == 1){ ?>
			commstring = commstring.replace(/%x/, cur_pic+1);
			commstring = commstring.replace(/%X/, kib.pics[cur_gallery].length);
			commstring = commstring.replace(/%g/, kib.dirs[cur_gallery]);
			var filename = kib.pics[cur_gallery][cur_pic].file;
			commstring = commstring.replace(/%f/, filename.substr(0, filename.length-4));
			<?php } ?>
            com_div.innerHTML = commstring;
            
            $("kiv_picdiv").removeChild($("thecomment"));

            var comm_x = com_div.offsetWidth;
            var comm_y = com_div.offsetHeight;
            
            if(comm_x > sizes[0]){
                comm_x = sizes[0];                
                com_div.style.width = (sizes[0] - 2*10) + "px";
                comm_y = com_div.offsetHeight;                
            }
            
            <?php
            if($comm_text_align === "left"){
            ?>	
            var x_pos = $("kiv_picdiv").offsetLeft;
            var y_pos = $("kiv_picdiv").offsetHeight + $("kiv_picdiv").offsetTop + 5;
            if(y_pos + comm_y > windowY){
    			y_pos -= comm_y + 10;
                <?php if($pwok == 0){ ?>if(comm_x != sizes[0])x_pos += 5;<?php } ?>
            }
            <?php
            }elseif($comm_text_align === "right"){
            ?>
            var x_pos = $("kiv_picdiv").offsetLeft + sizes[0] - comm_x;
            var y_pos = $("kiv_picdiv").offsetHeight + $("kiv_picdiv").offsetTop + 5;
            if(y_pos + comm_y > windowY){
    			y_pos -= comm_y + 10;
                <?php if($pwok == 0){ ?>if(comm_x != sizes[0])x_pos -= 5;<?php } ?>
            }
            <?php
            }else{
            ?>
            var x_pos = $("kiv_picdiv").offsetLeft + 0.5*(sizes[0] - comm_x);
            var y_pos = $("kiv_picdiv").offsetHeight + $("kiv_picdiv").offsetTop + 5;
            if(y_pos + comm_y > windowY){
    			y_pos -= comm_y + 10;
            }
            <?php
            }
            ?>
            
            com_div.style.left = x_pos + "px";
            com_div.style.top = y_pos + "px";
            com_div.style.display = "none";
            com_div.style.visibility = "visible";
            fw.fade(com_div, 100);
            
            var focme = $("focusme");
            if(focme){
            	focme.focus();
			}
        }

	}
	
	function calcpicsize(fileno){
		var picx = kib.pics[cur_gallery][fileno].x;
		var picy = kib.pics[cur_gallery][fileno].y;
		var winx = picx + <?php echo 2*$bord_size ?>;
		var winy = picy + <?php echo 2*$bord_size ?>;
		<?php
		$x_limiter = 10000;
		$y_limiter = 10000;
		if($max_pic_width != "none")$x_limiter = $max_pic_width;
		if($max_pic_height != "none")$y_limiter = $max_pic_height;
		if($max_pic_height != "none" || $max_pic_width != "none" || $oversize_allowed == 0){
		?>
		var xlimiter = <?php echo $x_limiter ?>;
		var ylimiter = <?php echo $y_limiter ?>;
        if(xlimiter < 1)xlimiter = parseInt(<?php echo $x_limiter ?>*windowX);
        if(ylimiter < 1)ylimiter = parseInt(<?php echo $y_limiter ?>*windowY);
		<?php if($oversize_allowed == 0){ ?>
		if(xlimiter > windowX)xlimiter = windowX - 20;
		if(ylimiter > windowY)ylimiter = windowY - 34;	
		<?php } ?>
		if(winy > ylimiter || winx > xlimiter){
			var k;
			if( (picx / picy) > 1){
				k = picy / picx;
				picx = xlimiter-<?php echo 2*$bord_size ?>;
				picy = k*picx;
				if(picy > ylimiter-<?php echo 2*$bord_size ?>){
					picy = ylimiter-<?php echo 2*$bord_size ?>;
					picx = (1/k) * picy;
				}
			} else {
				k = picx / picy;
				picy = ylimiter-<?php echo 2*$bord_size ?>;
				picx = k*picy;
				if(picx > xlimiter-<?php echo 2*$bord_size ?>){
					picx = xlimiter-<?php echo 2*$bord_size ?>;
					picy = (1/k) * picx;
				}
			}
			picx = Math.round(picx);
			picy = Math.round(picy);
		}
		<?php } ?>
		return new Array(picx, picy);
	}
	
	this.slideshow = function(){
    	if(stop == 1)return;
		if(ss == 2)ss = 1;
        ss *= -1;
		var sspic = $("ssbutton");
		if(ss == 1){
			<?php if($browser != "ie6"){ ?>
            sspic.src = "<?php echo $basedir ?>ki_nav_stop.png";
            <?php } else { ?>
            sspic.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src=<?php echo $basedir ?>ki_nav_stop.png)";
            <?php } ?>
            sspic.title = "<?php echo html_entity_decode(htmlentities(stripslashes($slideshow_stop), ENT_QUOTES, "UTF-8"), ENT_NOQUOTES, "UTF-8") ?>";
			<?php if($show_help == 1){ ?>$("kiv_help").style.display = "none";<?php } ?>
            ss = 2;
			kiv.goon(1);
		} else {
			<?php if($browser != "ie6"){ ?>
			sspic.src = "<?php echo $basedir ?>ki_nav_play.png";
            <?php } else { ?>
            sspic.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src=<?php echo $basedir ?>ki_nav_play.png)";            
            <?php } ?>
            sspic.title = "<?php echo html_entity_decode(htmlentities(stripslashes($slideshow_start), ENT_QUOTES, "UTF-8"), ENT_NOQUOTES, "UTF-8") ?>";
			<?php if($show_help == 1){ ?>fw.fade("kiv_help", 51);<?php } ?>
		}
	}
    
    this.sshelp = function(val){
    	ss = val;
    }
	
	this.goon = function(topic){
    	if(stop == 1 || ss == 1)return;
        if(ss == 2)ss = 1;
		if(topic > 0){
			if(cur_pic + topic <= kib.pics[cur_gallery].length-1){
				kiv.getImage(cur_gallery, cur_pic+topic);
			} else {
				kiv.getImage(cur_gallery, (cur_pic+topic)-kib.pics[cur_gallery].length);
			}
		} else {
			if(cur_pic + topic >= 0){
				kiv.getImage(cur_gallery, cur_pic+topic);
			} else {
				kiv.getImage(cur_gallery, kib.pics[cur_gallery].length+(cur_pic+topic));
			}
		}
	}
	
	this.closeImage = function(){
		stop = 1;
        if($("kib_helpbox")){
        	kib.hidehelp();
        }
		<?php removeEvent("document", "mousemove", "mousemoved"); ?>
        <?php if($show_preview == 1){ ?>kiv.removewow();<?php } ?>
       	if(window.stop !== undefined){
             window.stop();
        }else if(document.execCommand !== undefined){
             document.execCommand("Stop", false);
        }
		var picdiv = $("kiv_picdiv");
		<?php if($pwok == 1){ ?>
        if($("square_selector")){
            var obj = $("square_selector");
            obj.parentNode.removeChild(obj);
        }
        <?php } ?>
        if($("kiv_vcomments_form"))$("kiv_picdiv").removeChild($("kiv_vcomments_form"));
        if($("kiv_vcomments_list"))$("kiv_picdiv").removeChild($("kiv_vcomments_list"));
        fw.fade("kiv_share", 0);
        fw.fade("kiv_closebutton", 0);
        fw.fade("kiv_help", 0);
        fw.fade("kiv_gpspic", 0);
        fw.fade("kiv_prevdiv", 0);
        fw.fade("kiv_tb", 0);
        fw.fade("kiv_inshadow", 0);
        
        fw.fade("kiv_comdiv", 0, Array( function() {
            fw.fade("thepicture", 0, Array( fw.resize, picdiv, 2, parseInt(picdiv.style.width), 1, Array( fw.resize, picdiv, 2, 0, 1, Array( function(){fw.removejs("kiv");if(typeof(kie_module) == "function"){$("kie_maindiv").style.zIndex = 1000;$("kie_tb").style.display = "block";}else{if(typeof(kis_module) != "function")fw.shadebody(0);}} ))));
        } ));

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
        var charCode = aEvent.charCode ? aEvent.charCode : 0;
        if(keyCode == 27){
			kiv.closeImage();
			preventDefaultAction(aEvent);
			return false;
		}
		if(stop == 1 || flipped != 0)return;
		<?php if($slideshow == 1){ ?>
		if(keyCode == 19 || keyCode == 32 || charCode == 32){
			if(kib.pics[cur_gallery].length > 1)kiv.slideshow($("ssbutton"));
			preventDefaultAction(aEvent);
			return false;
		}
		if(ss == 1)return false;
		<?php } ?>
		<?php if($viewercomments == 1){ ?>
		if(keyCode == 70 || charCode == 102 || keyCode == 102){
			kiv.flip_vcomm();
			preventDefaultAction(aEvent);
			return false;
		}
		<?php } ?>
		if(keyCode == 33 || keyCode == 36){
			kiv.goon(-1);
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 34 || keyCode == 35){
			kiv.goon(1);
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 38){
			<?php if($oversize_allowed == 1){ ?>
			if(kib.pics[cur_gallery][cur_pic].y+2*<?php echo $bord_size ?> > windowY){
				if(posY < oversizeY){
					posY += 20;
					var picdiv = $("kiv_picdiv");
					picdiv.style.marginTop = -(0.5*picdiv.offsetHeight) + posY + "px";
				}
			}
			<?php } ?>
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 40){
			<?php if($oversize_allowed == 1){ ?>
			if(kib.pics[cur_gallery][cur_pic].y+2*<?php echo $bord_size ?> > windowY){
				if(oversizeY + posY > 0){
					posY -= 20;
					var picdiv = $("kiv_picdiv");
					picdiv.style.marginTop = -(0.5*picdiv.offsetHeight) + posY + "px";
				}
			}
			<?php } ?>
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 37){
			<?php if($oversize_allowed == 1){ ?>
			if(kib.pics[cur_gallery][cur_pic].x+2*<?php echo $bord_size ?> > windowX){
				if(posX < oversizeX){
					posX += 20;
					var picdiv = $("kiv_picdiv");
					picdiv.style.marginLeft = -(0.5*picdiv.offsetWidth) + posX + "px";
				}
			} else {
				kiv.goon(-1);
			}
			<?php } else { ?>
			kiv.goon(-1);
			<?php } ?>
			preventDefaultAction(aEvent);
			return false;
		}
		if(keyCode == 39){
			<?php if($oversize_allowed == 1){ ?>
			if(kib.pics[cur_gallery][cur_pic].x+2*<?php echo $bord_size ?> > windowX){
				if(oversizeX + posX > 0){
					posX -= 20;
					var picdiv = $("kiv_picdiv");
					picdiv.style.marginLeft = -(0.5*picdiv.offsetWidth) + posX + "px";
				}
			} else {
				kiv.goon(1);
			}
			<?php } else { ?>
			kiv.goon(1);
			<?php } ?>
			preventDefaultAction(aEvent);
			return false;
		}
	}
    
    <?php if($viewercomments == 1){ ?>
    this.flip_vcomm = function(){
    	if(flipped == 0){
	        if($("kib_helpbox")){
                kib.hidehelp();
            }
            flip(1);
			flipped = 2;
            return;
        }
		if(flipped == 1){
        	flip(-1);
			flipped = 2;
            return;
		}            
    }
    
    function flip(direction){
    	if(stop == 1 || ss == 1 || flipped == 2)return;
        
        stop = 1;
        
		var picsize = calcpicsize(cur_pic);
        picsize[0] += <?php echo 2*$bord_size ?>;
        picsize[1] += <?php echo 2*$bord_size ?>;
        
        if(direction == 1){
	        if(picsize[0] < 700)picsize[0] = 700;
    	    if(picsize[1] < 410)picsize[1] = 410;
        } else {
            $("kiv_picdiv").removeChild($("kiv_vcomments_form"));
            $("kiv_picdiv").removeChild($("kiv_vcomments_list"));
        }
        
        var flipZ = (picsize[0]/(1.2*picsize[1]))*0.25;
        if(flipZ > 0.5)flipZ = 0.5;
        if(flipZ < 0.05)flipZ = 0.05;
        
        <?php if($pwok == 1){ ?>
        if($("square_selector"))$("square_selector").style.display = "none";
        <?php } ?>
        <?php if($checkgps == 1){ ?>
        if($("kiv_gpspic"))$("kiv_gpspic").style.display = "none";
        <?php } ?>
        <?php if($show_help){ ?>
        $("kiv_help").style.display = "none";
        <?php } ?>
        $("kiv_comdiv").style.visibility = "hidden";
        var node = $("kiv_inshadow");
        node.style.display = "none";
        node.style.width = picsize[0] - <?php echo 2*$bord_size ?> + "px";
        node.style.height = picsize[1] - <?php echo 2*$bord_size ?> + "px";
        
        var temp = 0;
        
        var node = document.createElement("div");
        node.innerHTML = "<img id='kiv_flipT1' style='margin:0px; position:absolute; left:0px; top:0px; padding:0px; border:0px; width:100%; height:0px;' src='<?php echo $basedir."ki_flip.php?ot=0&gallery=" ?>"+kib.dirs[cur_gallery]+"' /><img id='kiv_flipB1' style='margin:0px; position:absolute; left:0px; bottom:0px; padding:0px; border:0px; width:100%; height:0px;' src='<?php echo $basedir."ki_flip.php?ot=1&gallery=" ?>"+kib.dirs[cur_gallery]+"' /><img id='kiv_flipT2' style='margin:0px; position:absolute; left:0px; top:0px; padding:0px; border:0px; width:0%; height:"+Math.round(picsize[1]*flipZ)+"px;' src='<?php echo $basedir."ki_flip.php?ot=2&gallery=" ?>"+kib.dirs[cur_gallery]+"' /><img id='kiv_flipB2' style='margin:0px; position:absolute; left:0px; bottom:0px; padding:0px; border:0px; width:0%; height:"+Math.round(picsize[1]*flipZ)+"px;' src='<?php echo $basedir."ki_flip.php?ot=3&gallery=" ?>"+kib.dirs[cur_gallery]+"' />";
        var what = node.childNodes.length;
        for(var i = 0; i < what; i++){
        	$("kiv_picdiv").appendChild(node.childNodes[0]);
        }
        $("kiv_flipT1").onload = function(){
            temp++;
            if(temp == 4)kiv.flip2(direction, picsize, flipZ);
        };
        $("kiv_flipB1").onload = function(){
            temp++;
           	if(temp == 4)kiv.flip2(direction, picsize, flipZ);
        };
        $("kiv_flipT2").onload = function(){
            temp++;
            if(temp == 4)kiv.flip2(direction, picsize, flipZ);
        };
        $("kiv_flipB2").onload = function(){
            temp++;
            if(temp == 4)kiv.flip2(direction, picsize, flipZ);
        };
    }
    
    this.flip2 = function(direction, picsize, flipZ){
     	var obj = $("kiv_picdiv");
        fw.dropshadow(obj, 0);
    	fw.fade("thepicture", 0, Array( function(){
            
            var node = document.createElement("div");
            node.id = "kiv_flip_";
            node.style.margin = "0px";
            node.style.position = "absolute";
            node.style.marginLeft = "-50%";
            node.style.marginTop = "-50%";
            node.style.top = "50%";
            node.style.left = "50%";
            node.style.padding = "0px";
            node.style.border = "0px";
            node.style.width = "100%";
            node.style.height = "100%";
            node.style.background = "<?php echo $bord_color ?>";
            obj.appendChild(node);
     
            obj.style.background = "none";
            
            fw.resize2( "kiv_picdiv", 2, 0, picsize[1], Array( function() {
	            $("kiv_flipT1").style.display = "none";
                $("kiv_flipB1").style.display = "none";
            	$("kiv_flipT2").style.width = "100%";
                $("kiv_flipB2").style.width = "100%";
                fw.resize2( "kiv_picdiv", 2, picsize[0], picsize[1], Array( function() {
                    $("kiv_picdiv").removeChild($("kiv_flipT1"));
                    $("kiv_picdiv").removeChild($("kiv_flipB1"));
                    $("kiv_picdiv").removeChild($("kiv_flipT2"));
                    $("kiv_picdiv").removeChild($("kiv_flipB2"));
                    $("kiv_picdiv").removeChild($("kiv_flip_"));
                    $("kiv_picdiv").style.background = "<?php echo $bord_color ?>";
                    fw.dropshadow(obj, 1);
                    obj = $("thepicture");
                    var picsize = calcpicsize(cur_pic);
					if(direction == 1){
	                    obj.style.left = "<?php echo $bord_size ?>px";
                        obj.style.top = "<?php echo $bord_size ?>px";
                        obj.style.margin = "0px";
                        obj.style.width = "230px";
                        obj.style.padding = "5px";
                        obj.style.height = (picsize[1]/picsize[0])*230 + "px";
                        <?php
                        if($browser !== "ie6" && $browser !== "ie7")
							echo "obj.style.clip = 'rect(0px,240px,247px,0px)';";
						else
							echo "obj.style.clip = 'rect(0px 240px 247px 0px)';";
						?>
                        obj.onclick = function(){
                            kiv.flip_vcomm();
                        };
                        fw.fade(obj, 100);
                        
                         picsize[0] +=  <?php echo 2*$bord_size ?>;
                         picsize[1] +=  <?php echo 2*$bord_size ?>;
                         if(picsize[0] < 700)picsize[0] = 700;
                         if(picsize[1] < 410)picsize[1] = 410;
                         picsize[0] -= <?php echo (2*$bord_size+20+1) ?>;
                         picsize[1] -= <?php echo (2*$bord_size+20+1) ?>;

                        var node = document.createElement("div");
                        node.id = "kiv_vcomments_form";
                        node.style.margin = "0px";
                        node.style.position = "absolute";
                        node.style.top = "<?php echo $bord_size+5 ?>px";
                        node.style.left = "<?php echo $bord_size+230+20 ?>px";
                        node.style.padding = "0px";
                        node.style.textAlign = "left";
                        node.style.border = "0px";
                        node.style.lineHeight = "12px";
                        node.style.font = "<?php echo "12px ".$comm_text_font ?>";
						node.style.color = "<?php echo $vcomm_header_color ?>";
                        node.innerHTML = "<div style='margin:0px 0px 11px; font-size:16px; line-height:16px; color:<?php echo $vcomm_header_color ?>;'><b><?php echo $vcomm_lac ?></b></div><label><?php echo $vcomm_name ?></label><input type='text' style='width:240px; line-height:24px; border:1px solid <?php echo $vcomm_bord_color ?>; padding:2px; margin:9px 0px 0px; font:16px <?php echo $comm_text_font ?>; color:<?php echo $vcomm_box_color ?>; display:block; text-align:left; background:<?php echo $vcomm_back_color ?>;' name='email' id='vcomm_email'/><br /><label><?php echo $vcomm_comm ?></label><textarea style='width:400px; height:80px; line-height:24px; border:1px solid <?php echo $vcomm_bord_color ?>; padding:2px; margin:9px 0px 0px; color:<?php echo $vcomm_box_color ?>; font:16px <?php echo $comm_text_font ?>; display:block; text-align:left; background:<?php echo $vcomm_back_color ?>;' name='assystem' id='vcomm_assystem'></textarea><input type='button' value='<?php echo $vcomm_post ?>' style='line-height:16px; padding:2px; width:110px; margin:9px 9px 0px 0px; font:14px <?php echo $comm_text_font ?>; border:1px solid <?php echo $vcomm_bord_color ?>; color:#000000; background:#eeeeee; cursor:pointer;' onclick='kiv.sendvcomment()' /><?php echo $vcomm_clk ?>";
                        $("kiv_picdiv").appendChild(node);
                        $("vcomm_email").focus();
                        $("vcomm_assystem").style.width = picsize[0]-240 + "px";
                        
                        node = document.createElement("div");
                        node.id = "kiv_vcomments_list";
                        node.style.margin = "0px";
                        node.style.position = "absolute";
                        node.style.top = "<?php echo $bord_size+260+10 ?>px";
                        node.style.left = "<?php echo $bord_size+5 ?>px";
                        node.style.padding = "5px";
                        node.style.textAlign = "left";
                        node.style.lineHeight = "12px";
                        node.style.width = picsize[0] + 10 - 10 + "px";
                        node.style.height = picsize[1] - 260 - 5 + "px";
                        node.style.background = "<?php echo $bord_color ?>";
                        node.style.overflowY = "scroll";
                        node.style.font = "<?php echo "12px ".$comm_text_font ?>";
						node.style.color = "<?php echo $vcomm_text_color ?>";
                        node.innerHTML = "";
                        $("kiv_picdiv").appendChild(node);
                        var params = "?file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&gallery=" + kib.dirs[cur_gallery] + "&get=1";
                        fw.getHTTP("<?php echo $basedir ?>ki_vcomm.php" + params, kiv.getvcomm, null);
                        
                    } else {
                        flipped = 0;
                    	obj.style.left = "50%";
                        obj.style.top = "50%";
                        obj.style.padding = "0px";
                        obj.style.marginLeft = -0.5*picsize[0]+"px";
           				obj.style.marginTop = -0.5*picsize[1]+"px";
                        obj.style.width = picsize[0]+"px";
                        obj.style.height = picsize[1]+"px";
                        <?php
                        if($browser !== "ie6" && $browser !== "ie7")
							echo "obj.style.clip = 'auto';";
						else
							echo "obj.style.clip = 'rect(auto auto auto auto)';";
						?>
                        obj.onclick = function(){
                            kiv.goon(1);
                        };
                        fw.fade(obj, 100, Array( function(){
                        	
                        	<?php if($pwok == 1){ ?>
                            if($("square_selector"))$("square_selector").style.display = "block";
                            <?php } ?>
                            <?php if($checkgps == 1){ ?>
                    		checkgps();
                            <?php } ?>
                            <?php if($show_help){ ?>
                            fw.fade("kiv_help", 51);
                            <?php } ?>
                            
                            $("kiv_comdiv").style.visibility = "visible";
                            $("kiv_inshadow").style.display = "block";
                            
                            if($("focusme"))$("focusme").focus();
                            
                            flipped = 0;
                        } ) );
                    }
                    stop = 0;       
                } ) );
                fw.resize2( "kiv_flipT2", 4, 0, 0 );
                fw.resize2( "kiv_flipB2", 4, 0, 0 );
                fw.resize2( "kiv_flip_", 2, picsize[0], picsize[1] );
            } ) );
            fw.resize2( "kiv_flipT1", 4, 0, Math.round(picsize[1]*flipZ) );
            fw.resize2( "kiv_flipB1", 4, 0, Math.round(picsize[1]*flipZ) );
            fw.resize2( "kiv_flip_", 2, 0, Math.round(picsize[1]*(1-2*flipZ))+8 );
            
        } ) );
    }
        
    this.sendvcomment = function(){
    	var email = $("vcomm_email").value;
        var assystem = $("vcomm_assystem").value;
        if(email != "" && assystem != ""){
	        var shareadd = location.search;
            if(shareadd.indexOf('kit_code=') != -1)
            	shareadd = shareadd.substr(0, shareadd.indexOf('kit_code=')-1);
            if(shareadd.indexOf('?') == -1)
            	shareadd = "?" + shareadd;
            else
            	shareadd += "&";
            var slash = "";
            if(location.pathname.substr(0, 1) != "/")slash = "/";
			shareadd = encodeURIComponent(location.hostname + slash + location.pathname + shareadd + "kit_code=" + cur_gallery+"_"+cur_pic + location.hash);
        	var params = "?file=" + encodeURIComponent(kib.pics[cur_gallery][cur_pic].file) + "&gallery=" + kib.dirs[cur_gallery] + "&email=" + encodeURIComponent(email) + "&assystem=" + encodeURIComponent(assystem) + "&address=" + shareadd;
        	fw.getHTTP("<?php echo $basedir ?>ki_vcomm.php" + params, kiv.savedvcomm, null);
        } else {
        	alert("ERROR: Name and/or comment empty.");
        }
    }
    
    this.savedvcomm = function(responseText){
    	if(responseText == "1"){
        	alert("ERROR: For spam avoiding reasons you are only allowed to post a comment every 3 minutes.");
        } else if(responseText == "2") {
        	alert("ERROR: Your comment has been identified as spam.");
        } else {
			$("vcomm_email").value = "";
            $("vcomm_assystem").value = "";
        	var vlist = $("kiv_vcomments_list");
           	<?php if($moderate_posts == 0){ ?>
            var old = vlist.innerHTML;         
            vlist.style.visibility = "hidden";
            var oldd = parseInt(vlist.style.height);
            vlist.style.height = "auto";
            var olds = fw.getDim(vlist, 1)[1];
        	vlist.innerHTML = responseText;
            var news = fw.getDim(vlist, 1)[1];
            vlist.innerHTML = old;
            vlist.style.height = oldd + "px";
            vlist.style.visibility = "visible";
            fw.move(vlist, 3, 0, 280 + (news - olds), Array(function(){
            	vlist.innerHTML = responseText;
                vlist.style.top = "280px";
            }));
            <?php } else { ?>
            alert("Thanks. Your comment will be visible after moderation.");
            <?php } ?>
        }
    }
    
	this.getvcomm = function(responseText){
    	if(responseText != ""){
        	if($("kiv_vcomments_list")){
	        	$("kiv_vcomments_list").innerHTML = responseText;
			}
        }
        flipped = 1;        
    }
    <?php } ?>

	this.download = function(){
       	var node = document.createElement("form");
        node.action = "<?php echo $basedir ?>ki_download.php";
        node.method = "post";
        node.innerHTML = "<input type='hidden' name='file' value='" + kib.pics[cur_gallery][cur_pic].file + "' /><input type='hidden' name='gallery' value='" + kib.dirs[cur_gallery] + "' />";
        document.body.appendChild(node);
		node.submit();
		document.body.removeChild(node);
    }
}