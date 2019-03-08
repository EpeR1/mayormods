<?php
ob_start("ob_gzhandler");

header("Expires: Mon, 01 Jul 2003 00:00:00 GMT"); // Past date 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Consitnuously modified 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
header("Pragma: no-cache"); // NO CACHE

//----------------------------------------------------------------------------- functions --------------------------------------------------------------------------------

function addEvent($el, $event, $function){
	global $browser;
	if($browser === "ie6" || $browser === "ie7"){
		echo $el.".attachEvent('on".$event."', ".$function.");\n";
	} else {
		echo $el.".addEventListener('".$event."', ".$function.", false);\n";
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

include_once("../ki_config/ki_setup.php");

$admin_ok = 0;
if(isset($_GET['admin']) && $admin === $_GET['admin']){
	$admin_ok = 1;
}
$user_ok = 0;
if(isset($_GET['user']) && $user === $_GET['user']){
	$user_ok = 1;
}

$resizespeed = 5;
$movespeed = 5;
$fadespeed = 8;

header("Content-Type: application/x-javascript");
?>

//-------------------------------------------------------------------------- FRAMEWORK -------------------------------------------------------------------------------

var fw;

(function(){
	(function(){
        <?php if(in_array($browser, array("ie6", "ie7"))) { ?>
        try {
            document.documentElement.doScroll("left");
            if(document.body)
            	fw_ready();
            else {
            	setTimeout(arguments.callee, 5);
            }
        } catch(error) {
            setTimeout(arguments.callee, 5);
        }
        <?php } else if(in_array($browser, array("opera", "gecko", "ie9"))) { ?>
		document.addEventListener("DOMContentLoaded", fw_ready, false);
        <?php } else if($browser === "webkit") { ?>
		var state = document.readyState;
		if (state == 'loaded' || state == 'complete'){
        	fw_ready();
        } else { 
			setTimeout(arguments.callee, 5);
		}
        <?php } else { ?>
		<?php addEvent("window", "load", "function(){fw_ready();}"); ?>
        <?php } ?>	
	})();
})();

function fw_ready(){
	if(fw)return;
	fw = new fw_framework();
	fw.init();
}

function fw_framework(){

	/* intern ----------------------------------------------------------------------------- Globals --------------------------------------------------------------------------- */
	
	var animation = Array();
	var intanimation = 0;
    var bodyscroll = 0;

	/* intern ----------------------------------------------------------------------------- Helper functions --------------------------------------------------------------------------- */

	function $(id){
		return document.getElementById(id);
	}
	
	this.findPos = function(obj){
		var curleft = curtop = 0;
		if (obj.offsetParent) {
			curleft = obj.offsetLeft;
			curtop = obj.offsetTop;
			while (obj = obj.offsetParent) {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			}
		}
		return [curleft,curtop];
	}
	
	// type: 1 = normal, 2 = komplett mit padding 
	this.getDim = function(obj, type){
		var y = obj.offsetHeight;
		var x = obj.offsetWidth;
		<?php if($browser === "ie6" || $browser === "ie7" || $browser === "ie9") { ?>
		if(type == 1){
			x = x - parseInt(getStyle(obj, "paddingLeft")) - parseInt(getStyle(obj, "paddingRight"));
			y = y - parseInt(getStyle(obj, "paddingTop")) - parseInt(getStyle(obj, "paddingBottom"));
			var temp = parseInt(getStyle(obj, "borderLeftWidth"));
			if(!isNaN(temp))x -= temp;
			temp = parseInt(getStyle(obj, "borderRightWidth"));
			if(!isNaN(temp))x -= temp;
			temp = parseInt(getStyle(obj, "borderTopWidth"));
			if(!isNaN(temp))y -= temp;
			temp = parseInt(getStyle(obj, "borderBottomWidth"));
			if(!isNaN(temp))y -= temp;
		}
		<?php } else { ?>
		if(type == 1){
			x = x - parseInt(getStyle(obj, "padding-left")) - parseInt(getStyle(obj, "padding-right"));
			y = y - parseInt(getStyle(obj, "padding-top")) - parseInt(getStyle(obj, "padding-bottom"));
			x = x - parseInt(getStyle(obj, "border-left-width")) - parseInt(getStyle(obj, "border-right-width"));
			y = y - parseInt(getStyle(obj, "border-top-width")) - parseInt(getStyle(obj, "border-bottom-width"));
		}
		<?php } ?>	
		return [x, y];
	}
	
	function getStyle(obj, styleProp){
		<?php if($browser === "ie6" || $browser === "ie7" || $browser === "ie9") { ?>
		return obj.currentStyle[styleProp];
		<?php } else { ?>
		return document.defaultView.getComputedStyle(obj,null).getPropertyValue(styleProp);
		<?php } ?>
	}
	
	function ANIOBJ(type_, aniarray_, original_, after_){
		this.type = type_;
		this.aniarray = aniarray_;
		this.original = original_;
		this.after = after_;
		this.step = 0;
		switch(type_){
			case 1:
				this.aniarraylength = aniarray_.length/2;
			break;
			case 2:
				this.aniarraylength = aniarray_.length/2;
			break;
			case 5:
				this.aniarraylength = aniarray_.length/2;
			break;
			default:
				this.aniarraylength = aniarray_.length;
			break;
		}
	}
	
    function startanimation(type, ma, obj, continueobj){
    	<?php if($disable_animation == 0){ ?>
        var found = 0;
		for(var i = 0; i < animation.length; i++){
            if(animation[i].original == obj){
            	if(animation[i].type == type){
	            	animation[i] = new ANIOBJ(type, ma, obj, continueobj);
    	            found = 1;
	                break;
                }
            }
        }
        if(found == 0)animation.push( new ANIOBJ(type, ma, obj, continueobj) );
        if(intanimation == 0)doanimation();
        <?php } else { ?>
		var actobj = new ANIOBJ(type, ma, obj, continueobj);
        var style = actobj.original.style;
        var last = actobj.aniarraylength - 1;
        switch(actobj.type){
            case 1: // resize normal
                style.width = actobj.aniarray[2*last] + "px";
                style.height = actobj.aniarray[2*last+1] + "px";					
            break;
            case 2: // resize centered
                style.width = actobj.aniarray[2*last] + "px";
                style.height = actobj.aniarray[2*last+1] + "px"
                style.marginLeft = -(0.5*actobj.aniarray[2*last]) + "px";
                style.marginTop = -(0.5*actobj.aniarray[2*last+1]) + "px";
            break;
            case 3: // resize only width
                style.width = actobj.aniarray[last] + "px";
            break;
            case 4: // resize only height
                style.height = actobj.aniarray[last] + "px";	
            break;
            case 5: // move normal
                style.left = actobj.aniarray[2*last] + "px";
                style.top = actobj.aniarray[2*last+1] + "px";
            break;
            case 6: // move only left
                style.left = actobj.aniarray[last] + "px";
            break;
            case 7: // move only top
                style.top = actobj.aniarray[last] + "px";
            break;
            case 8: // fade
                <?php
                if($browser === "ie7" || $browser === "ie6") {
                ?>
                actobj.original.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + actobj.aniarray[last] + ')';
                <?php
                } else {
                ?>
                actobj.original.style.opacity = actobj.aniarray[last]*0.01;
                <?php } ?>
            break;
        }
        if(actobj.type == 8){
            if(actobj.aniarray[last] == 0)
                actobj.original.style.display = "none";
			<?php if($browser == "ie6" || $browser == "ie7"){ ?>
            if(actobj.aniarray[last] == 100){
                actobj.original.style.filter = "";
            }
            <?php } ?>
        }								
        if(actobj.after != null){
            actobj.after[0](actobj.after[1], actobj.after[2], actobj.after[3], actobj.after[4], actobj.after[5], actobj.after[6]);
        }
        <?php } ?>
    }
    
	function doanimation(){
		if(animation.length == 0){
			clearInterval(intanimation);
			intanimation = 0;
			return;
		}
        
		if(intanimation == 0)intanimation = setInterval( doanimation, 30 );
        for(var i = 0; i < animation.length; i++){
            var actobj = animation[i];
            if( actobj.step >= actobj.aniarraylength ){
                animation.splice(i, 1);
                if(i != animation.length-1)i--;
                if(actobj.type == 8){
                    if(actobj.aniarray[actobj.step-1] == 0)
                        actobj.original.style.display = "none";
                    <?php if($browser === "ie6" || $browser === "ie7"){ ?>
                    if(actobj.aniarray[actobj.step-1] == 100){
                        actobj.original.style.filter = "";
                    }
                    <?php } ?>
                }
                if(actobj.after != null){
                    actobj.after[0](actobj.after[1], actobj.after[2], actobj.after[3], actobj.after[4], actobj.after[5], actobj.after[6]);
                }
                continue;
            }
            var style = actobj.original.style;
            switch(actobj.type){
                case 1: // resize normal
                    style.width = actobj.aniarray[2*actobj.step] + "px";
                    style.height = actobj.aniarray[2*actobj.step+1] + "px";					
                break;
                case 2: // resize centered
                    style.width = actobj.aniarray[2*actobj.step] + "px";
                    style.height = actobj.aniarray[2*actobj.step+1] + "px"
                    style.marginLeft = -(0.5*actobj.aniarray[2*actobj.step]) + "px";
                    style.marginTop = -(0.5*actobj.aniarray[2*actobj.step+1]) + "px";
                break;
                case 3: // resize only width
                    style.width = actobj.aniarray[actobj.step] + "px";
                break;
                case 4: // resize only height
                    style.height = actobj.aniarray[actobj.step] + "px";	
                break;
                case 5: // move normal
                    style.left = actobj.aniarray[2*actobj.step] + "px";
                    style.top = actobj.aniarray[2*actobj.step+1] + "px";
                break;
                case 6: // move only left
                    style.left = actobj.aniarray[actobj.step] + "px";
                break;
                case 7: // move only top
                    style.top = actobj.aniarray[actobj.step] + "px";
                break;
                case 8: // fade
                    <?php if($browser !== "ie7" && $browser !== "ie6") { ?>
                    style.opacity = actobj.aniarray[actobj.step]*0.01;
                    <?php } ?>
                break;
            }
            actobj.step++;
        }
	}
	
	/*  ----------------------------------------------------------------------------- Animation functions --------------------------------------------------------------------------- */

	this.shadebody = function(yes, continueobj){
		if(yes == 1){
        	<?php if($browser !== "webkit") { ?>
        	bodyscroll = document.getElementsByTagName("html")[0].scrollTop;
            <?php } else { ?>
            bodyscroll = document.body.scrollTop;
            <?php } ?>
			<?php if($browser === "ie6") { ?>
			document.getElementsByTagName("html")[0].style.overflowX = "hidden";
			document.getElementsByTagName("html")[0].style.overflowY = "hidden";
			window.scrollTo(0,0);
			<?php }	else { ?>
			document.getElementsByTagName("html")[0].style.overflow = "hidden";
          	<?php if($browser !== "webkit") { ?>
        	document.getElementsByTagName("html")[0].scrollTop = bodyscroll;
            <?php } else { ?>
            document.body.scrollTop = bodyscroll;
            <?php } ?>            
			<?php } ?>
			$("fw_blend").style.display = "block";
		} else {
			$("fw_blend").style.display = "none";
			<?php if($browser === "ie6") { ?>
			document.getElementsByTagName("html")[0].style.overflowY = "scroll";
			document.getElementsByTagName("html")[0].style.overflowX = "auto";
			<?php } else { ?>
			document.getElementsByTagName("html")[0].style.overflow = "auto";
			<?php } ?>
   			<?php if($browser == "opera" || $browser == "webkit") { ?>
            window.scrollBy(0, 1);
            window.scrollBy(0, -1);
			<?php } ?>
          	<?php if($browser !== "webkit") { ?>
        	document.getElementsByTagName("html")[0].scrollTop = bodyscroll;
            <?php } else { ?>
            document.body.scrollTop = bodyscroll;
            <?php } ?>            
		}
		if(continueobj != null){
			continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
		}
	}
	
    // type: 0 = none, 1 = outside, 2 = inside, 3 = with divs inside, 4 = none with divs inside
	this.dropshadow = function(id, type, continueobj){
        var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
			if(continueobj != null)continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
			return;
		}
		<?php if(in_array($browser, array("ie9", "opera", "gecko", "webkit"))){ ?>
        if(type == 1){
            obj.style.boxShadow = "0px 0px 10px #000";
        } else if(type == 2){
            obj.style.boxShadow = "inside 0px 0px 10px #000";
        } else if(type == 0){
            obj.style.boxShadow = "none";
        } else if(type == 3){
            for(var i = 0; i < 4; i++){
                for(var j = 0; j < 5; j++){
                    var node = document.createElement("div");
                    node.style.position = "absolute";
                    if(i != 1){
                        node.style.left = "0%";
                    } else {
                        node.style.right = "0%";
                    }
                    if(i != 2){
                        node.style.top = "0%";
                    } else {
                        node.style.bottom = "0%";
                    }
                    if(i == 0 || i == 2){
                        node.style.width = "100%";
                        node.style.height = (j+1) + "px";
                    } else {
                        node.style.width = (j+1) + "px";
                        node.style.height = "100%";
                    }
                    node.style.opacity = 0.1 - j*0.02;
                    node.className = "ki_se";
                    node.style.background = "#000";
                    node.style.padding = "0px";
                    node.style.border = "none";
                    obj.appendChild(node);
                }
            }
        } else if(type == 4){
            var temp = obj.childNodes.length;
            var cn = obj.childNodes;
            for(var i = 0; i < temp; i++){
                if(cn[i].className.indexOf("ki_se") >= 0){
                    obj.removeChild(cn[i]);
                }
            }
        }
        <?php } ?>        
        if(continueobj != null){
            continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
        }
	}    
    
	// type: 1 = normal, 2 = horizontal/vertikal zentriert, 3 = nur horizontal, 4 = nur vertikal 
	this.resize = function(id, type, endwidth, endheight, continueobj){
		var count = <?php echo $resizespeed ?>;
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
			if(continueobj != null)continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
			return;
		}
		var temp = fw.getDim(obj, 1);
		var actw = temp[0];
		var acth = temp[1];
		var step;
		var mawidth = Array();
		var maheight = Array();
		var ma = Array();
		if(type != 4){
			step = Math.round((endwidth - actw)/count);
			while(step != 0){
				if( Math.abs(endwidth-actw) < Math.abs(2*step) ){
					step = Math.round(0.4*step);
				}
				actw += step;
				mawidth.push(actw);
			}
			mawidth.push(endwidth);
		}
		if(type != 3){
			step = Math.round((endheight - acth)/count);
			while(step != 0){
				if( Math.abs(endheight-acth) < Math.abs(2*step) ){
					step = Math.round(0.4*step);
				}
				acth += step; 
				maheight.push(acth);
			}
			maheight.push(endheight);
		}
		if(type < 3){
			if(mawidth.length > maheight.length){
				var diff = mawidth.length - maheight.length;
				for(var i = 0; i < diff; i++)maheight.push(endheight);
			} else {
				var diff = maheight.length - mawidth.length;
				for(var i = 0; i < diff; i++)mawidth.push(endwidth);
			}
			for(var i = 0; i < maheight.length; i++){
				ma.push(mawidth[i]);
				ma.push(maheight[i]);
			}
		}
		switch(type){
			case 3:
				for(var i = 0; i < mawidth.length; i++){
					ma.push(mawidth[i]);
				}
			break;
			case 4:
				for(var i = 0; i < maheight.length; i++){
					ma.push(maheight[i]);
				}
			break;
		}
        startanimation(type, ma, obj, continueobj);
	}

	// type: 1 = normal, 2 = horizontal/vertikal zentriert, 3 = nur horizontal, 4 = nur vertikal
    // resize ohne abbremsen, d.h. kostante geschwindigkeit
	this.resize2 = function(id, type, endwidth, endheight, continueobj){
		var count = 8;
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
			if(continueobj != null)continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
			return;
		}
		var temp = fw.getDim(obj, 1);
		var actw = temp[0];
		var acth = temp[1];
		var stepw;
        var steph;
		var ma = Array();
        stepw = (endwidth - actw)/count;
        steph = (endheight - acth)/count;
        if(type < 3){
            for(var i = 0; i < count; i++){
                actw+=stepw;
                acth+=steph;
                ma.push(actw);
                ma.push(acth);
            }
            ma.push(endwidth);
            ma.push(endheight);
        }
        switch(type){
			case 3:
                for(var i = 0; i < count; i++){
                    actw+=stepw;
                    ma.push(actw);
                }
                ma.push(endwidth);
            break;
            case 4:
                for(var i = 0; i < count; i++){
                    acth+=steph;
                    ma.push(acth);
                }
                ma.push(endheight);
            break;
        }
        startanimation(type, ma, obj, continueobj);
	}

	// type: 1 = normal, 2 = nur horizontal, 3 = nur vertikal 
	this.move = function(id, type, endleft, endtop, continueobj) {
        var count = <?php echo $movespeed ?>;
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
            if(continueobj != null)continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
			return;
		}
		obj.style.display = "block";
		if(getStyle(obj, "position") == "static")obj.style.position = "relative";
		var actx = getStyle(obj, "left");
		var acty = getStyle(obj, "top");
		if(actx == "auto")
			actx = 0;
		else
			actx = parseInt(actx);
		if(acty == "auto")
			acty = 0;
		else
			acty = parseInt(acty);
		if(type != 3)obj.style.left = actx + "px";
		if(type != 2)obj.style.top = acty + "px";
		//obj.style.margin = "0px";
		var step;
		var maleft = Array();
		var matop = Array();
		var ma = Array();
		if(type != 3){
			step = (endleft - actx)/count;
			while(step != 0){
				if( Math.abs(endleft-actx) < Math.abs(2*step) ){
					step = Math.round(0.4*step);
				}
				actx += step; 
				maleft.push(actx);
			}
			maleft.push(endleft);
		}
		if(type != 2){
			step = (endtop - acty)/count;
			while(step != 0){
				if( Math.abs(endtop-acty) < Math.abs(2*step) ){
					step = Math.round(0.4*step);
				}
				acty += step; 
				matop.push(acty);
			}
			matop.push(endtop);
		}
		if(type < 2){
			if(maleft.length > matop.length){
				var diff = maleft.length - matop.length;
				for(var i = 0; i < diff; i++)matop.push(endtop);
			} else {
				var diff = matop.length - maleft.length;
				for(var i = 0; i < diff; i++)maleft.push(endleft);
			}
			for(var i = 0; i < matop.length; i++){
				ma.push(maleft[i]);
				ma.push(matop[i]);
			}
		}
		switch(type){
			case 1:
				type = 5;
			break;
			case 2:
				type = 6;
				for(var i = 0; i < maleft.length; i++){
					ma.push(maleft[i]);
				}
			break;
			case 3:
				type = 7;
				for(var i = 0; i < matop.length; i++){
					ma.push(matop[i]);
				}
			break;
		}
        startanimation(type, ma, obj, continueobj);
	}
    
    // type: 1 = normal, 2 = nur horizontal, 3 = nur vertikal 
	this.move2 = function(id, type, endleft, endtop, continueobj) {
        var count = 6;
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
            if(continueobj != null)continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
			return;
		}
		obj.style.display = "block";
		if(getStyle(obj, "position") == "static")obj.style.position = "relative";
		var actx = getStyle(obj, "left");
		var acty = getStyle(obj, "top");
		if(actx == "auto")
			actx = 0;
		else
			actx = parseInt(actx);
		if(acty == "auto")
			acty = 0;
		else
			acty = parseInt(acty);
		if(type != 3)obj.style.left = actx + "px";
		if(type != 2)obj.style.top = acty + "px";
        var steph;
        var stepv;
        var maleft = Array();
		var matop = Array();
		var ma = Array();
        steph = (endleft - actx)/count;
        stepv = (endtop - acty)/count;
		if(type != 3){
            for(var i = 0; i < count; i++){
                actx+=steph;
                maleft.push(actx);
            }
            maleft.push(endleft);
		}
		if(type != 2){
            for(var i = 0; i < count; i++){
                acty+=stepv;
                matop.push(acty);
            }
            matop.push(endtop);
		}
		if(type < 2){
			for(var i = 0; i < ma.length; i++){
				ma.push(maleft[i]);
				ma.push(matop[i]);
			}
		}
		switch(type){
			case 1:
				type = 5;
			break;
			case 2:
				type = 6;
				ma = maleft;
			break;
			case 3:
				type = 7;
				ma = matop;
			break;
		}
        startanimation(type, ma, obj, continueobj);
	}

	this.fade = function(id, endfade, continueobj){
		var count = <?php echo $fadespeed ?>;
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
			if(continueobj != null)continueobj[0](continueobj[1], continueobj[2], continueobj[3], continueobj[4], continueobj[5], continueobj[6]);
			return;
		}
		var actfade;
		if(getStyle(obj, "display") == "none"){
			actfade = 0;
			obj.style.display = "block";            
		} else {
			<?php
			if($browser === "ie7" || $browser === "ie6") {
			?>
			if(!getStyle(obj, "filter")){
				actfade = 100;
			} else {
                try {
					actfade = obj.filters.item("DXImageTransform.Microsoft.Alpha").opacity;
				} catch(e) {
					actfade = 100;
				};
			}
			<?php
			} else {
			?>
			actfade = parseInt(getStyle(obj, "opacity")*100);
			<?php } ?>
		}
		<?php if($browser !== "ie7" && $browser !== "ie6") { ?>
		obj.style.opacity = actfade*0.01;
		<?php } ?>
		var step;
		var ma = Array();
		step = parseInt((endfade - actfade)/count);
		for(var i = 0; i < count; i++){
			ma.push(actfade+i*step);
		}
		ma.push(endfade);
		<?php
        if($browser === "ie7" || $browser === "ie6") {
			if($disable_animation == 0){
		?>
		if(obj.style.filter){
        	if(obj.filters.item("DXImageTransform.Microsoft.Fade")){
		        if(obj.filters.item("DXImageTransform.Microsoft.Fade").status == 2){
                	obj.filters.item("DXImageTransform.Microsoft.Fade").stop();
                }
			}
        }
        obj.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + actfade + ") progid:DXImageTransform.Microsoft.Fade(duration=" + ma.length * 0.035 + ")";
        obj.filters.item("DXImageTransform.Microsoft.Fade").apply();
        obj.filters.item("DXImageTransform.Microsoft.Alpha").opacity = endfade;
        obj.filters.item("DXImageTransform.Microsoft.Fade").play();
		<?php 
			}
		}
		?>
                        
        startanimation(8, ma, obj, continueobj);
	}

	/* ----------------------------------------------------------------------------- AJAX functions --------------------------------------------------------------------------- */

	function checkforajax(){
		var xmlhttp;
		try { 
			xmlhttp = new XMLHttpRequest();
		} catch (e) { 
			try { 
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) { 
				try	{ 
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) { return -1; }
			}
		}
		return 1;
	}

	// adr = site adress with post variables to send, result = function called when ready, params[] = additional params besides responseText as first param
	this.getHTTP = function(adr, result, params) {
		var xmlhttp;
		try { 
			xmlhttp = new XMLHttpRequest();
		} catch (e) { 
			try { 
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) { 
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		var temp = adr.indexOf("?");
		var sendstring = "";
		if(temp != -1){
			sendstring = adr.substr(temp+1);
			adr = adr.substr(0, temp);
		}
		xmlhttp.onreadystatechange = function() {
			if(xmlhttp.readyState == 4){
				if(xmlhttp.status == 200){
                   	if(result)result(xmlhttp.responseText, params);
				} else {
                	if(xmlhttp.status == 404)alert("ERROR: Site '" + adr + "' not found or internal error during getHTTP-call.");
				}
			}
		}
		xmlhttp.open("POST", adr, true);
		xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xmlhttp.send(sendstring);
	}
	
	/* ----------------------------------------------------------------------------- Dynamicliy add/remove JavaScript functions ----------------------------------------------------- */

	this.addjs = function(adr, modulename, params){
		if($(modulename+"_id"))return;
		var head = document.getElementsByTagName("head")[0];
		var script = document.createElement('script');
		script.id = modulename+"_id";
		script.type = 'text/javascript';
		script.src = adr;
		head.appendChild(script);
		eval( "(function(){ if(typeof "+modulename+"_module == 'function'){ "+modulename+" = new "+modulename+"_module(); "+modulename+".constr(params); return; } setTimeout( arguments.callee, 100); })();" );
	}
	
	this.removejs = function(modulename){
		if(!$(modulename+"_id"))return;
		eval(modulename + ".destr();");
		var head = document.getElementsByTagName("head")[0];
		var script = $(modulename+"_id");
		head.removeChild(script);
        eval(modulename+" = null;"+modulename+"_module = null;");
	}

	/* ----------------------------------------------------------------------------- Event functions --------------------------------------------------------------------------- */
	
    
    
    function on_mouseover(obj, execfunc, offsL, offsT){
		<?php addEvent("obj", "mouseover", "
		function(event){
			event = event ? event : window.event;
			var from = event.relatedTarget ? event.relatedTarget : event.fromElement;
			if(!from)return;
			while(from.parentNode){
				if(from == obj){
					return;
				}
				from = from.parentNode;
			}
			var x = event.clientX ? event.clientX + document.documentElement.scrollLeft : event.pageX;
			var y = event.clientY ? event.clientY + document.documentElement.scrollTop: event.pageY;
			x -= offsL;
			y -= offsT;
			execfunc(x, y);
		}
		"); ?>
	}
	
	function on_mouseout(obj, execfunc, offsL, offsT){
		<?php addEvent("obj", "mouseout", "
		function(event){
			event = event ? event : window.event;
			var to = event.relatedTarget ? event.relatedTarget : event.toElement;
			if(!to)return;
			while(to.parentNode){
				if(to == obj)return;
				to = to.parentNode;
			}
			var x = event.clientX ? event.clientX + document.documentElement.scrollLeft : event.pageX;
			var y = event.clientY ? event.clientY + document.documentElement.scrollTop: event.pageY;
			x -= offsL;
			y -= offsT;
			execfunc(x, y);
		}	
		"); ?>
	}
	
	function on_mouseevent(obj, evnt, execfunc, offsL, offsT){
		<?php if($browser === "ie6" || $browser === "ie7"){
			echo "obj.attachEvent('on'+evnt, 
				function(event){
					event = event ? event : window.event;
					var x = event.clientX + document.documentElement.scrollLeft - offsL;
					var y = event.clientY + document.documentElement.scrollTop - offsT;
					execfunc(x, y);
					return false;
				}
			);";
		} else {
			echo "obj.addEventListener(evnt, 
				function(event){
					event = event ? event : window.event;
					var x = event.pageX - offsL;
					var y = event.pageY - offsT;
					execfunc(x, y);
					preventDefaultAction(event);
				}
			, false);";
		} ?>
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
    
	// type: 1 = normal, 2 = x,y coordinate for static/relative positioned element
	this.addevent = function(id, evnt, execfunc, type){
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
			return;
		}
		var offsL = 0;
		var offsT = 0;
		if(type == 2){
			if(getStyle(obj, "position") == "relative"){
				obj.style.position = "static";
				offsL = obj.offsetLeft;
				offsT = obj.offsetTop;
				obj.style.position = "relative";
			}
			if(getStyle(obj, "position") == "static"){
				offsL = obj.offsetLeft;
				offsT = obj.offsetTop;
			}
		}
		switch(evnt){
			case "mouseover": on_mouseover(obj, execfunc, offsL, offsT); break;
			case "mouseout": on_mouseout(obj, execfunc, offsL, offsT); break;
			case "click": on_mouseevent(obj, evnt, execfunc, offsL, offsT); break;
			case "dblclick": on_mouseevent(obj, evnt, execfunc, offsL, offsT); break;
			case "mousedown": on_mouseevent(obj, evnt, execfunc, offsL, offsT); break;
			case "mousemove": on_mouseevent(obj, evnt, execfunc, offsL, offsT); break;
			case "mouseup": on_mouseevent(obj, evnt, execfunc, offsL, offsT); break;
            case "keycapture":
	            <?php if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
					addEvent("obj", "keypress", "execfunc");
				} else {
					addEvent("obj", "keydown", "execfunc");
				} ?>
            break;
			default:
				<?php if($browser === "ie6" || $browser === "ie7"){
					echo "obj.attachEvent('on'+evnt, execfunc);";
				} else {
					echo "obj.addEventListener(evnt, execfunc, false);";
				} ?>
			break;
		}
	}

	/* extern ----------------------------------------------------------------------------- Toolbars --------------------------------------------------------------------------- */
	
	this.addtoolbar = function(id, content, x, y, permanent){
		var obj;
		if(id.length)
			obj = $(id);
		else
			obj = id;
		if(!obj){
			return;
		}
		id = obj.id;
		if(!$(id+"_tb")){
			var tb = document.createElement("div");
			tb.id = id+"_tb";
			tb.style.position = "absolute";
			if(permanent == 0)tb.style.display = "none";
			if(x > 0)
				tb.style.left = x + "px";
			else
				tb.style.right = x*-1 + "px";
			if(y > 0)
				tb.style.top = y + "px";
			else
				tb.style.bottom = y*-1 + "px";
			tb.innerHTML = content;
			obj.appendChild(tb);
   			if(permanent == 0){
                fw.addevent( obj, "mouseout", function(){ fw.fade(tb, 0); } );
                fw.addevent( obj, "mouseover", function(){ fw.fade(tb, 100); } );
                //fw.addevent( id, "mousemove", function(x, y){ $("tooltip").style.left = x + 20 +  "px"; $("tooltip").style.top = y + 20 + "px"; } );
            }
		} 
	}
	
	/* Init code ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

	this.init = function(){
		var node;
		if(checkforajax() == -1){
            var temp = document.getElementsByTagName("div");
            for(var i = 0; i < temp.length; i++){
                if(temp[i].className.indexOf("koschtitgallery") >= 0){
                	var titlefound = temp[i].title;
                    temp[i].innerHTML = "<p>AJAX not supported. See a plain HTML version of <a href='<?php echo $basedir ?>ki_nojs.php?gallery="+titlefound+"&amp;site="+window.location+"'>"+titlefound+"</a> here.</p>";
                    temp[i].title = "";
                }
            }
			<?php if($show_warnings == 1)echo "alert(\"WARNING: Your browser doesn't support AJAX.\\nPlease upgrade your browser to view this gallery.\");"; ?>
			return;
		}
		if(!$("fw_blend")){
			<?php if($browser === "ie7" || $browser === "opera"){ ?>
			node = document.createElement("img");
			node.src = "<?php echo $basedir ?>ki_shade.php";
			node.alt = "";
			<?php } elseif($browser == "ie6"){ ?>
			node = document.createElement("img");
			node.alt = "";
			node.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src=<?php echo $basedir ?>ki_shade.php, sizingMethod='scale')";
			node.style.width = "2880px";
			node.style.height = "1800px";
			node.src="<?php echo $basedir ?>ki_noimage.gif";
			<?php } else { ?>
			node = document.createElement("div");
			node.style.width = "100%";
			node.style.height = "100%";
			node.style.background = "<?php echo $fade_color ?>";
			node.style.opacity = <?php echo $fade_alpha/10 ?>;
			<?php }  ?>
			node.id = "fw_blend";
			node.style.position = "<?php echo $posfix ?>";
			node.style.left = "0px";
			node.style.top = "0px";
			node.style.zIndex = 100;
			node.style.display = "none";
			document.body.appendChild(node);
		}
       	<?php if($shade_while_loading == 1 || $admin_ok == 1 || $user_ok == 1){ ?>
		this.shadebody(1);
		<?php } ?>
		<?php if($admin_ok == 1){ ?>
		fw.addjs("<?php echo $basedir ?>ki_js_basic.php?reldir=<?php echo $reldir ?>&admin=<?php echo $_GET['admin'] ?>", "kib");
		<?php }	elseif($user_ok == 1){ ?>
        fw.addjs("<?php echo $basedir ?>ki_js_basic.php?reldir=<?php echo $reldir ?>&user=<?php echo $_GET['user'] ?>", "kib");
        <?php } else { ?>
		fw.addjs("<?php echo $basedir ?>ki_js_basic.php?reldir=<?php echo $reldir ?>", "kib");
		<?php } ?>
	}

}
//-------------------------------------------------------------------------------- END FRAMEWORK -----------------------------------------------------------------