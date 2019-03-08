<?php
ob_start("ob_gzhandler");

session_start();
if(isset($_SESSION['pwquery']))unset($_SESSION['pwquery']); 

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
//-------------------------------------- end functions -----------------------------------

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

include("../ki_config/ki_setup.php");

$admin_ok = 0;
if(isset($_GET['admin']) && $admin === $_GET['admin']){
	$admin_ok = 1;
}
$user_ok = 0;
if(isset($_GET['user']) && $user === $_GET['user']){
	$user_ok = 1;
}

header("Content-Type: application/x-javascript");
?>

// Basic module 'kib' --------------------------------------------------------------------

function kib_module(){

	/* -------------------------------- variables ----------------------------------- */

	var galleries;						// gallery objects
	this.dirs; 							// gallery folders
	this.pics;							// pic names and dimensions
    this.customsettings = new Array();	// individual width, heights and backgrounds
    var mouseover;						// Array for mouseover
    
<?php
	$verz = @opendir("../ki_config/");
	while($file = @readdir($verz))
	{
		if($file != "." && $file != ".." && strtolower(substr($file, -12)) === "ki_setup.php"){
			include("../ki_config/ki_setup.php");
			if($file === "ki_setup.php")
				$temp = "default";
			else {
				$temp = substr($file, 0, -13);
				include("../ki_config/".$file);				
			}
			echo "this.customsettings['".$temp."'] = Array(".$fr_width.", ".$fr_height.", \"".$fr_color."\", \"".$th_bord_color."\", \"".$th_bord_hover_color."\", ".$th_bord_hover_increase.", ".$resize_auto.", ".$nav_style.", ".$th_bord_size.", ".$show_nav.", ".$nav_always.", ".$slideshow.", \"".$nav_color."\", \"".$nav_border_color."\", \"".$nav_pos."\", ".$show_explorer.", ".$th_shadow.");\r\n";
		}
	}
	@closedir($verz);
?>

	/* -------------------------------- getElementById function --------------------- */
			
	function $(id){
		return document.getElementById(id);
	}

	/* -------------------------------- constructor/descturctor --------------------- */

	this.constr = function() {
		galleries = new Array();
        this.dirs = new Array();
		mouseover = new Array();      
		var temp = document.getElementsByTagName("div");
		var found = 0;
		for(var i = 0; i < temp.length; i++){
			if(temp[i].className.indexOf("koschtitgallery") >= 0){
				temp[i].id = temp[i].title + "_" + found;
                temp[i].style.position = "relative";
                temp[i].style.padding = "0px";
                temp[i].style.overflow = "hidden";
                var obj = kib.customsettings[temp[i].title] ? kib.customsettings[temp[i].title] : kib.customsettings['default'];
                temp[i].style.width = obj[0] + "px";
                temp[i].style.height = obj[1] + "px";
                temp[i].style.minHeight = "0";
                temp[i].style.background = obj[2];
				this.dirs.push(temp[i].title);
				temp[i].title = "";
				galleries.push(temp[i]);
                mouseover.push(0);
				found++;
			}
		}
		if(galleries.length == 0){
			<?php if($shade_while_loading == 1)echo "shadebody(0);" ?>
			<?php if($show_warnings == 1)echo "alert(\"WARNING: KoschtIT Image Gallery didn't find any gallery to display. Set '\$show_warnings = 0' if you don't want to see this warning again.\");"; ?>
			return;
		} else {
			this.pics = new Array(galleries.length);
			showgallery(0);
		}
	}

	this.destr = function(){
		
	}

	/* -------------------------------- methodes ------------------------------------ */
    
    this.reinit = function(){
    	var count = this.dirs.length;
        for(var i = 0; i < count; i++){
        	$(this.dirs[i]+"_"+i).title = this.dirs[i];
        }
        for(var i = 0; i < count; i++){
        	var temp = galleries[i].cloneNode(false);
            galleries[i].parentNode.insertBefore(temp, galleries[i]);
            galleries[i].parentNode.removeChild(galleries[i]);
        }
    	kib.constr();
    }

	this.reloadcsandreinit = function(){
    	fw.getHTTP("<?php echo $basedir ?>ki_getcustomsettings.php", gotcustomsettings);
    }

	function gotcustomsettings(responseText){
    	if(responseText != ""){
        	eval(responseText);
            kib.reinit();
		}
    }

	function showgallery(gallerynumber){
	
		var gallery = galleries[gallerynumber];
		gallery.innerHTML = "<div style='background:<?php echo $bord_color ?>; position:absolute; left:50%; top:50%; width:200px; height:40px; margin-left:-106px; margin-top:-26px; border:2px solid <?php echo $nav_border_color ?>; color:<?php echo $comm_text_color ?>; font: 14px normal <?php echo $comm_text_font ?>; padding:8px 4px 0px; text-align:center; line-height:17px;' id='" + gallery.id + "_wait" + "'>Please wait, while folder being scanned ...</div>";
        
		var node = document.createElement("div");
		node.id = gallery.id + "_see";
		node.style.position = "absolute";
		node.style.height = "100%";
		node.style.width = "100%";
		node.style.overflow = "hidden";
		node.style.display = "none";
		gallery.appendChild(node);
		
		node = document.createElement("div");
		node.id = gallery.id + "_hide";
		node.style.position = "absolute";
		node.style.height = "100%";
		node.style.width = "100%";
		node.style.overflow = "hidden";
		node.style.display = "none";
		gallery.appendChild(node);

       	kib.inc(gallerynumber, 0, 0, 1);
        
        fw.addevent( gallery, "mousemove", function(){ if(mouseover[gallerynumber] == 0)expandfornav(gallerynumber, 1); } );
        fw.addevent( gallery, "mouseout", function(){ expandfornav(gallerynumber, 0); } );
        
		if(gallerynumber+1 < galleries.length){
			showgallery(gallerynumber+1);
		} else {
			<?php if($admin_ok == 1 || $user_ok == 1){ ?>
			adminlogin();
			<?php } ?>
		}
	}
    
    function expandfornav(gallerynumber, type){
    	var gallery = galleries[gallerynumber];
        var container = $(gallery.id + "_hide");
        
        if(type == 1){
            mouseover[gallerynumber] = 1;
            container.style.overflow = "visible";
            gallery.style.overflow = "visible";
        } else {
            mouseover[gallerynumber] = 0;
            container.style.overflow = "hidden";
            gallery.style.overflow = "hidden";        
        }
        
        var cs = kib.customsettings[kib.dirs[gallerynumber]] ? kib.customsettings[kib.dirs[gallerynumber]] : kib.customsettings['default'];
        if(cs[9] == 1 && cs[10] == 0){
			<?php
            $temp = getimagesize("ki_nav_next.png");
            ?>
            var ysize = cs[1];
            if(cs[6] == 1){
                for(var i = container.childNodes.length-1; i > 0; i--){
                    if(container.childNodes[i]){
                        if(container.childNodes[i].nodeName == "IMG"){
                            var temp2 = container.childNodes[i].alt.split("_");
                            ysize = parseInt(parseInt(temp2[3]) + 0.5*(parseInt(temp2[1]) + cs[8]*2));
                            break;
                        }
                    }
                }
                for(var j = 0; j < 10; j++){
                    if(container.childNodes[j]){
                        if(container.childNodes[j].nodeName == "IMG"){
                            temp2 = container.childNodes[j].alt.split("_");
                            ysize = ysize + parseInt(parseInt(temp2[3]) + 0.5*(parseInt(temp2[1]) + cs[8]*2));
                            if(ysize > cs[1])ysize = cs[1];
                            break;
                        }
                    }
                }
            }
            var tb = $(container.id + "_tb");
            if(tb != null){
                if(type == 1){
                    if(cs[10] == 0)fw.fade(tb, 100);
                    fw.resize(gallery, 4, 0, ysize + <?php echo ($temp[1] + 18) ?>);
                } else {
                    if(cs[10] == 0)fw.fade(tb, 0);
                    fw.resize(gallery, 4, 0, ysize);
                }
            }
		}
    }

	<?php if($admin_ok == 1 || $user_ok == 1){ ?>
	
	function adminlogin(){
		if(!$("authorization")){
			node = document.createElement("div");
			node.id = "authorization";
			node.style.position = "<?php echo $posfix ?>";
			node.style.top = "50%";
			node.style.left = "50%";
			node.style.width = "150px";
			node.style.height = "70px";
			node.style.margin = "-35px 0px 0px -75px";
			node.style.background = "#444444";
			node.style.border = "1px dashed #ffffff";
			node.style.textAlign = "center";
			node.style.color = "#ffffff";
			node.style.font = "11px Tahoma, sans-serif";
            node.style.lineHeight = "13px";
			node.style.zIndex = 2000;
			document.body.appendChild(node);
			$("authorization").appendChild(document.createElement("br"));
			node = document.createTextNode("Enter password here:");
			$("authorization").appendChild(node);
			$("authorization").appendChild(document.createElement("br"));
			$("authorization").appendChild(document.createElement("br"));
			node = document.createElement("span");
			node.innerHTML = "<input type='password' id='pwform' style='border:1px solid #ffffff; background:#000; width:140px; color:#ffffff; text-align:center; float:none; font:11px Tahoma, sans-serif; padding:1px;' />";
			$("authorization").appendChild(node);
			$("pwform").onkeyup = function() { kib.checkpw(this.value) };
			$("pwform").focus();
		}
	}
	
	this.checkpw = function(query){
		if(query != "")fw.getHTTP("<?php echo $basedir ?>ki_checkpw.php?&query="+query+"&reldir=<?php echo $reldir ?>&against=<?php echo $admin_ok == 1 ? "adm" : "usr" ?>", gotcheck, null);
	}

	function gotcheck(responseText){
		if(responseText != "")eval(responseText);
	}
    
    this.edit_ki_setup = function(){
    	fw.addjs("<?php echo $basedir ?>ki_js_settings.php?reldir=<?php echo $reldir ?>", "kis");
    }
    
    this.fileupload = function(){
    	fw.addjs("<?php echo $basedir ?>ki_js_upload.php?reldir=<?php echo $reldir ?>", "kiu");
    }
    
    this.deleteexplorer = function(){
    	fw.addjs("<?php echo $basedir ?>ki_js_delete.php?reldir=<?php echo $reldir ?>&gallery=" + this.dirs[0], "kid");
    }
    
    this.changeorder = function(){
    	fw.addjs("<?php echo $basedir ?>ki_js_changeorder.php?reldir=<?php echo $reldir ?>&gallery=" + this.dirs[0], "kic");
    }
    
    this.managefolders = function(){
	    fw.addjs("<?php echo $basedir ?>ki_js_managefolders.php?reldir=<?php echo $reldir ?>", "kim");
    }
    
    this.showhelp = function(obj, direction, messCode){
    	
        var message = "";
        var helpBox = $("kib_helpbox");
        
        var pointer;
		var infosymbol = "<?php draw_image("ki_nav_info.png", "", "border:0px; vertical-align:bottom; display:inline; float:right; padding:0px 0px 8px 8px;", ""); ?>";
        
        switch(messCode){
        	case 1:
            	message = "If you want to label an image just click on it and start typing. Press \"CTRL + Enter\" to save it. The next image will open automatically and you can keep labeling ...";
            break;
            case 2:
	            message = "Press \"CTRL + Enter\" to save your label.";
            break;
            case 3:
	            message = "Drag'n'Drop an image to change it's position in the gallery. There is also the '$pic_order'-parameter in the Settings-section which sets the main image order strategy for individual galleries.";
            break;
            case 4:
	            message = "Click on an image to delete it from the gallery.";
            break;
            case 5:
	            message = "You can upload a watermark first. Afterwards you can add this watermark to images which you upload to the gallery. The watermark position and size are controlled by the $watermark_hori/vert/size - parameters.";
            break;
        }
   			
        var pos = fw.findPos(obj);
        var dim = fw.getDim(obj);
        
        helpBox.style.visibility = "hidden";
        helpBox.innerHTML = infosymbol + message;
        var boxH = fw.getDim(helpBox)[1];
        
    	switch(direction){
        	case 1:
				pointer = "<?php draw_image("ki_arrow.php?ot=0", "", "border:0px; vertical-align:bottom; position:absolute; left:-27px; top:0px; padding:0px;", ""); ?>";
                helpBox.style.left = pos[0] + dim[0] + 15 + "px";
                helpBox.style.top = pos[1] + 0.5*dim[1] - 15 + "px";
            break;
            case 2:
				pointer = "<?php draw_image("ki_arrow.php?ot=1", "", "border:0px; vertical-align:bottom; position:absolute; left:70px; top:-27px; padding:0px;", ""); ?>";
                helpBox.style.left = pos[0] + 0.5*dim[0] - 85 + "px";
                helpBox.style.top = pos[1] + dim[1] + 30 + "px";
            break;
            case 3:
				pointer = "<?php draw_image("ki_arrow.php?ot=2", "", "border:0px; vertical-align:bottom; position:absolute; right:-26px; top:0px; padding:0px;", ""); ?>";
                helpBox.style.left = pos[0] - 170 - 15 + "px";
                helpBox.style.top = pos[1] + 0.5*dim[1] - 15 + "px";
            break;
            case 4:
				pointer = "<?php draw_image("ki_arrow.php?ot=3", "", "border:0px; vertical-align:bottom; position:absolute; left:70px; bottom:-26px; padding:0px;", ""); ?>";
                helpBox.style.left = pos[0] + 0.5*dim[0] - 85 + "px";
                helpBox.style.top = pos[1] - boxH - 30 + "px";
            break;
        }
        
        helpBox.innerHTML += pointer;
        
        helpBox.style.display = "none";
        helpBox.style.visibility = "visible";
        fw.fade(helpBox, 100);
        
    }
    
    this.hidehelp = function(){
    	var helpBox = $("kib_helpbox");
        fw.fade(helpBox, 0, Array(function(){
        	helpBox.style.visibility = "hidden";
            helpBox.style.display = "block";
        }));
    }
	<?php } ?>

	//direction: 0 = static 1 = left, 2 = right, 3 = top, 4 = bottom
	this.inc = function(gallerynumber, direction, startfrom, collectinfo) {
		var params = "?reldir=<?php echo $reldir ?>&gallery=" + this.dirs[gallerynumber] + "&gallerynumber=" + gallerynumber;
		if(startfrom)params += "&startfrom="+startfrom;
		if(collectinfo)
			params += "&collectinfo=1";
		else {
            collectinfo = 0;
		}
		fw.getHTTP("<?php echo $basedir ?>ki_koschtit.php" + params, gotinc, new Array(direction, gallerynumber, collectinfo));
	}

	function gotinc(responseText, params) {
        var direction = params[0];
		var gallerynumber = params[1];
		var collectinfo = params[2];
		
		var container = $(kib.dirs[gallerynumber]+"_"+gallerynumber+"_see");
		var switcher = $(kib.dirs[gallerynumber]+"_"+gallerynumber+"_hide");
		container.id = kib.dirs[gallerynumber]+"_"+gallerynumber+"_hide";
		switcher.id = kib.dirs[gallerynumber]+"_"+gallerynumber+"_see";

		if(collectinfo == 1)container.parentNode.removeChild($(kib.dirs[gallerynumber]+"_"+gallerynumber+"_wait"));
        
        container.innerHTML = responseText;
        
		if(collectinfo == 1){
			var jsontxt = $(gallerynumber+"_info").value;
			kib.pics[gallerynumber] = eval("(" + jsontxt + ")");
			<?php if($shade_while_loading == 1){ ?>
			if(gallerynumber == kib.dirs.length - 1){
				setTimeout( function(){
					fw.shadebody(0);
				}, 1000);
			}
			<?php } ?>
            if(gallerynumber == galleries.length-1){
	            if(typeof(kit_opensharedpic) == "function")kit_opensharedpic();
			}
		}
		
		var x = parseInt(container.parentNode.style.width);
		var y = parseInt(container.parentNode.style.height);
		var xto = 0;
		var yto = 0;
		if(direction > 2)
			var onlydir = 3;
		else
			var onlydir = 2;
		if(direction == 1){xto = x;}
		if(direction == 2){xto = -x;}	
		if(direction == 3){yto = y;}
		if(direction == 4){yto = -y;}
		
        container.style.overflow = "hidden";
        container.parentNode.style.overflow = "hidden";
 		container.style.left = -1*xto+"px";
		container.style.top = -1*yto+"px";
        container.style.display = "block";
        
       	fw.move(container, onlydir, 0, 0);
        fw.move(switcher, onlydir, xto, yto, Array( function(){
			var node = document.createElement("div");
			node.id = switcher.id;
			node.style.position = "absolute";
			node.style.height = "100%";
			node.style.width = "100%";
			node.style.overflow = "hidden";
			node.style.lineHeight = "12px";
			node.style.display = "none";
			var parnt = switcher.parentNode;			
			parnt.removeChild(switcher);
			parnt.appendChild(node);
            
            container.style.overflow = "visible";
            container.parentNode.style.overflow = "visible";
            
            <?php $breite = getimagesize("ki_nav_next.png"); ?>
            var cs = kib.customsettings[kib.dirs[gallerynumber]] ? kib.customsettings[kib.dirs[gallerynumber]] : kib.customsettings['default'];
   			var ysize = cs[1];
            if(cs[6] == 1){
				for(var i = container.childNodes.length-1; i > 0; i--){
                    if(container.childNodes[i]){
                        if(container.childNodes[i].nodeName == "IMG"){
                            var temp2 = container.childNodes[i].alt.split("_");
                            ysize = parseInt(parseInt(temp2[3]) + 0.5*(parseInt(temp2[1]) + cs[8]*2));
                            break;
                        }
                    }
				}
                for(var j = 0; j < 10; j++){
	                if(container.childNodes[j]){
                        if(container.childNodes[j].nodeName == "IMG"){
                            temp2 = container.childNodes[j].alt.split("_");
                            ysize = ysize + parseInt(parseInt(temp2[3]) + 0.5*(parseInt(temp2[1]) + cs[8]*2));
							if((cs[9] == 1 && cs[10] == 1) || (mouseover[gallerynumber] == 1 && cs[9] == 1 && cs[10] == 0))ysize += <?php echo $breite[1]+18 ?>;
                            if(ysize <= cs[1]){
                            	fw.resize(parnt, 4, 0, ysize);
							} else {
                            	var temp = fw.getDim(parnt, 1)[1];
                                if(cs[9] == 1)temp += <?php echo $breite[1]+18 ?>;
                            	if(ysize > temp){
                                	fw.resize(parnt, 4, 0, ysize);
								}
                            }
                            if(mouseover[gallerynumber] == 1 && cs[9] == 1 && cs[10] == 0)ysize -= <?php echo $breite[1]+18 ?>;
                            break;
                        }
                    }
                }
            }
            if(cs[9] == 1){
				var tbcontent = "";
                var breite = 0;
                if(cs[15] == 1){
					tbcontent += "<?php draw_image("ki_nav_full.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kib.startExplorer(\" + gallerynumber + \");\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_maxi), ENT_QUOTES, "UTF-8")."'"); ?>";
					breite += (4 + <?php echo $breite[0] ?>);
                }
				if(cs[11] == 1 && kib.pics[gallerynumber].length > 0){
	               	tbcontent += "<?php draw_image("ki_nav_play.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kib.slideshow(\" + gallerynumber + \");\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($slideshow_start), ENT_QUOTES, "UTF-8")."'"); ?>";
                    breite += (4 + <?php echo $breite[0] ?>);
                }
                if($(gallerynumber+"_prev")){
	               	tbcontent += "<?php draw_image("ki_nav_prev.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kib.inc(\" + gallerynumber + \", 1, \" + $(gallerynumber+\"_prev\").innerHTML + \");\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_back), ENT_QUOTES, "UTF-8")."'"); ?>";
                    breite += (4 + <?php echo $breite[0] ?>);
				}
				if($(gallerynumber+"_next")){
                	tbcontent += "<?php draw_image("ki_nav_next.png", "", "cursor:pointer; border:0px; margin:0px 2px 0px 2px; padding-top:2px; display:inline;", "onclick=\\\"kib.inc(\" + gallerynumber + \", 2, \" + $(gallerynumber+\"_next\").innerHTML + \");\\\" onmouseover=\\\"this.style.padding = '0px 0px 2px 0px';\\\" onmouseout=\\\"this.style.padding = '2px 0px 0px 0px';\\\" title='".htmlentities(stripslashes($nav_next), ENT_QUOTES, "UTF-8")."'"); ?>";
                    breite += (4 + <?php echo $breite[0] ?>);
				}
                if(breite != 0){
                	breite -= 4;
                } else {
                	return;
                }
                var navpos = 0;
                if(cs[14] == "right"){
                	navpos = -4;
                } else if(cs[14] == "left"){
                	navpos = 4;
                } else {
                	breite += 16;
                	navpos = (cs[0]-breite+2)*0.5;
                }
                if(cs[9] == 1 && cs[10] == 0)ysize += (<?php echo $breite[1] ?> + 18);
                fw.addtoolbar(container, tbcontent, navpos, ysize - <?php echo $breite[1] ?> - 14, 1);
                var tb = $(container.id + "_tb");
                tb.style.background = cs[12];
                if(cs[7] == 2){
                    tb.style.borderRadius = "20px";
                    tb.style.MozBorderRadius = "20px";
                    tb.style.WebkitBorderRadius = "20px";
                }
                tb.style.border = "2px solid " + cs[13];                        
                tb.style.padding = "1px 3px 3px 3px";
                tb.style.lineHeight = "12px";
                if(collectinfo == 1 && cs[10] == 0)tb.style.display = "none";
			}
		} ));
	}
	
	this.getImage = function(picstring) {
		var temp = picstring.indexOf("_");
        var gallerynumber = Number(picstring.substr(0, temp));
        var picnumber =  Number(picstring.substr(temp+1));
        var params = Array( gallerynumber, picnumber );
        var error = 0;
        if(gallerynumber >= this.pics.length){
			error = 1;
		} else {
        	if(picnumber >= this.pics[gallerynumber].length)error = 1;
        }
        if(error == 1 || isNaN(picnumber)|| isNaN(gallerynumber)){
        	alert("ERROR: KoschtIT Image Gallery didn't find the picture you want to display.");
            return;
        }
		<?php if($admin_ok == 1){ ?>
		fw.addjs("<?php echo $basedir ?>ki_js_view.php?reldir=<?php echo $reldir ?>&gallery="+kib.dirs[gallerynumber]+"&admin=<?php echo $_GET['admin'] ?>", "kiv", params);
		<?php } else { ?>
		fw.addjs("<?php echo $basedir ?>ki_js_view.php?reldir=<?php echo $reldir ?>&gallery="+kib.dirs[gallerynumber], "kiv", params);
		<?php } ?>
	}
	
    this.slideshow = function(gallerynumber) {
    	kib.getImage(gallerynumber + "_0");
        (function(){
			if($("thepicture")){
            	var sspic = $("ssbutton");
      			<?php if($browser != "ie6"){ ?>
                sspic.src = "<?php echo $basedir ?>ki_nav_stop.png";
                <?php } else { ?>
                sspic.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src=<?php echo $basedir ?>ki_nav_stop.png)";            
                <?php } ?>
                sspic.title = "<?php echo htmlentities(stripslashes($slideshow_stop), ENT_QUOTES, "UTF-8") ?>";
                if($("kiv_gps"))$("kiv_gps").value = "0,0";
				if($("kiv_help"))$("kiv_help").style.display = "none";                
				kiv.sshelp(1);
				return;
			}
			setTimeout(arguments.callee, 10);
		})();
    }
    
	this.startExplorer = function(gallerynumber) {
    	var params = Array();
        params.push(gallerynumber);
		fw.addjs("<?php echo $basedir ?>ki_js_explorer.php?reldir=<?php echo $reldir ?>&gallery="+kib.dirs[gallerynumber], "kie", params);
	}
    
    this.makebigger = function(obj){
	    var temp = obj.id.split("_");
        var cs = kib.customsettings[kib.dirs[temp[0]]] ? kib.customsettings[kib.dirs[temp[0]]] : kib.customsettings['default'];
		var faktor = cs[5];
       	var temp = obj.alt.split("_");
        var size = Array( temp[0], temp[1] );
		var newsize = Array( Math.round(size[0]*faktor), Math.round(size[1]*faktor) );
 	   	var pos = Array( temp[2], temp[3] );
 		var newpos = Array( pos[0]-0.5*(newsize[0]-size[0]), pos[1]-0.5*(newsize[1]-size[1]) );
        fw.move(obj, 1, newpos[0], newpos[1]);
        fw.resize(obj, 1, newsize[0], newsize[1]);
        obj.style.zIndex = 100;
        obj.style.borderColor = cs[4];
        if(cs[16] == 1)fw.dropshadow(obj, 1);
    }
    
    this.makesmaller = function(obj){
        var temp = obj.alt.split("_");
        var newsize = Array( temp[0], temp[1] );
	   	var newpos = Array( temp[2], temp[3] );
	    fw.move(obj, 1, newpos[0], newpos[1]);
	    fw.resize(obj, 1, newsize[0], newsize[1]);
        obj.style.zIndex = 0;
        temp = obj.id.split("_");
		var cs = kib.customsettings[kib.dirs[temp[0]]] ? kib.customsettings[kib.dirs[temp[0]]] : kib.customsettings['default'];
        obj.style.borderColor = cs[3];
        if(cs[16] == 1)fw.dropshadow(obj, 0);
    }
}

