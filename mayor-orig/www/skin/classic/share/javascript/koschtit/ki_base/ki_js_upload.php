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

// Settings module 'kiu' --------------------------------------------------------------------

function kiu_module(){

	/* -------------------------------- variables ----------------------------------- */

	var windowX;
	var windowY;
    var currentfolder = "";
    var finalsizes = Array();

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
        if(typeof(kic_module) == "function")kic.close();
        if(typeof(kim_module) == "function")kim.close();
    	fw.shadebody(1);
		var node;
		if(!$("kiu_maindiv")){
			node = document.createElement("div");
			node.id = "kiu_maindiv";
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
            node.innerHTML = "<div id='upload_div' style='background:#D2D2D2; position:absolute; left:50%; top:50%; margin-left:-481px; margin-top:-266px; border:1px solid #000000; padding:4px; height:530px; width:960px;'></div><div style='position:absolute; left:50%; top:50%; margin-left:-481px; margin-top:-266px; width:960px; height:480px; padding:4px; z-index:3; background:#D2D2D2; text-align:center; border:1px solid #000000; padding-top:54px; display:none;' id='kiu_progress'><span style='font-size:14px; font-weight:bold;'>Uploading files</span><div style='position:absolute; left:180px; top:150px; height:24px; width:600px; border:1px dashed #fff; line-height:24px; background:#afafaf;'><div id='kiu_progress_cur' style='position:absolute; left:0px; top:0px; width:0%; height:100%; background:#F63;'></div><div style='position:relative; color:#fff; overflow:hidden;'></div></div><div style='position:absolute; left:180px; top:250px; height:24px; width:600px; border:1px dashed #fff; line-height:24px; background:#afafaf;'><div id='kiu_progress_tot' style='position:absolute; left:0px; top:0px; width:0%; height:100%; background:#F63;'></div><div style='position:relative; color:#fff; overflow:hidden;'></div></div></div><div id='kiu_uploaddata' style='display:none;'></div>";
			document.body.appendChild(node);
            node = $("upload_div");
            var uploadfield = "";
            if(supportAjaxUploadWithProgress()){
            	 uploadfield += "<form id='kiu_m_file_form'><div style='font-size:12px; text-align:center; padding-bottom:40px; padding-top:40px;'>Your browser supports uploading multiple files. Select all files from the \"Browse files\" upload field.</div><div style='position:relative; width:580px; margin:auto;'><input type='file' size='74' style='width:575px; padding:2px; margin:0px; border:1px solid #000; display:block; position:relative; <?php echo in_array($browser, array("ie6", "ie7")) ? "filter:alpha(opacity:0);" : "opacity:0;" ?>  z-index:2;' onchange='kiu.choseMFiles(this)' multiple='multiple' name='pic' id='kiu_m_file' /><div style='position:absolute; left:0px; top:0px; z-index:1;'><div style='width:475px; height:18px; padding:2px; margin:0px; border:1px solid #8c8c8c; float:left; margin-right:5px; background:#fff; line-height:18px; margin-bottom:20px;'></div><input type='button' value='Browse files' style='float:left; display:block; margin:0px; padding:2px; border:1px solid #8c8c8c; height:24px; line-height:18px; width:90px;' /></div></div></form>";
            } else {
                for(var i = 0; i < 9; i++){
                    uploadfield += "<div style='position:relative; width:580px; margin:auto; margin-top:18px; height:27px; overflow:hidden;'><form action='<?php echo $basedir ?>ki_upload.php' target='kiu_uploadframe"+i+"' name='kiu_upform"+i+"' enctype='multipart/form-data' method='post'><input type='hidden' value='' name='dir'><input type='hidden' value='0' name='addwatermark'><input type='hidden' value='' name='maxx'><input type='hidden' value='' name='maxy'><iframe name='kiu_uploadframe"+i+"' src='about:blank' style='display:none;'></iframe><input type='file' size='74' style='width:575px; padding:2px; margin:0px; border:1px solid #000; display:block; position:relative; <?php echo in_array($browser, array("ie6", "ie7")) ? "filter:alpha(opacity:0);" : "opacity:0;" ?> z-index:2;' onchange='kiu.choseFiles(this)' name='pic' /><div style='position:absolute; left:0px; top:0px; z-index:1;'><input type='text' style='width:475px; height:18px; padding:2px; margin:0px; border:1px solid #8c8c8c; float:left; margin-right:5px;' /><input type='button' value='Browse file' style='float:left; display:block; margin:0px; padding:2px; border:1px solid #8c8c8c; height:24px; line-height:18px; width:90px;' /></div></form></div>";
                }
			}
            uploadfield += "<div style='position:absolute; bottom:20px; left:195px; width:580px; margin:0px; line-height:22px; vertical-align:middle;'><span style='float:left;'>Upload a watermark:</span><div style='position:relative; width:450px; margin:auto; height:27px; overflow:hidden; float:right;'><form action='<?php echo $basedir ?>ki_upload.php' target='kiu_watermark' name='kiu_watermark_form' enctype='multipart/form-data' method='post'><iframe name='kiu_watermark' src='about:blank' style='display:none;'></iframe><input type='file' size='74' style='width:445px; padding:2px; margin:0px; border:1px solid #000; display:block; position:relative; <?php echo in_array($browser, array("ie6", "ie7")) ? "filter:alpha(opacity:0);" : "opacity:0;" ?> z-index:2;' onchange='kiu.choseFiles(this)' name='pic' /><div style='position:absolute; left:0px; top:0px; z-index:1;'><input type='text' style='width:345px; height:18px; padding:2px; margin:0px; border:1px solid #8c8c8c; float:left; margin-right:5px;' /><input type='button' value='Browse file' style='float:left; display:block; margin:0px; padding:2px; border:1px solid #8c8c8c; height:24px; line-height:18px; width:90px;' /></div><input type='hidden' name='watermark' value='1' /></form></div><span style='float:left; margin-right:15px; margin-top:8px; width:150px;'>Add watermark to images:</span><input type='checkbox' style='display:block; margin:0px; margin-top:10px; float:left;' onclick='kiu.toggleWatermark(this)' id='kiu_wmcb' /><br /><span style='float:left; margin-right:15px; margin-top:8px; clear:both;'>Reduce maximum image size to:</span><input type='text' style='width:50px; height:18px; padding:2px; margin:0px; margin-top:8px; margin-right:8px; border:1px solid #8c8c8c; float:left; text-align:center;' id='kiu_maxx' /><span style='float:left; margin-top:8px;'>x</span><input type='text' style='width:50px; height:18px; padding:2px; margin:0px; margin-top:8px; margin-left:10px; border:1px solid #8c8c8c; float:left; text-align:center;' id='kiu_maxy' /><div style='float:right; margin-left:10px; margin-top:-20px; margin-right:5px; height:52px; width:220px;'><?php draw_image("ki_watermark.pic", "kiu_wm_preview", "max-height:100%; max-width:100%; float:right; visibility:hidden;", "alt='Watermark'"); ?></div></div>";
	        node.innerHTML = uploadfield;
            <?php if(is_file("ki_watermark.pic"))echo "$('kiu_wm_preview').style.visibility = 'visible';"; ?>
		}
 		if(!$("kiu_topdiv")){
        	node = document.createElement("div");
			node.id = "kiu_topdiv";
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
            	dirs = dirs + "<span style='cursor:pointer; text-decoration:underline; margin-right:5px; line-height:14px;' onclick=\"kiu.setFolder('" + kib.dirs[i] + "')\">" + kib.dirs[i] + "</span>";
            }
            currentfolder = kib.dirs[0];
            node.innerHTML = "<div style='margin-bottom:5px;'><span style='font-size:14px; line-height:18px; font-weight:bold; color:#003; margin-right:5px;'>Selected gallery upload folder</span><span style='background:#003; border:1px solid #CCC; padding:3px; color:#ffffff;' id='kiu_selfolder'>" + currentfolder + "</span></div>" + dirs;
            document.body.appendChild(node);
        }
        if(!$("kiu_botdiv")){
        	node = document.createElement("div");
			node.id = "kiu_botdiv";
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
            node.innerHTML = "<input type='button' value='Close' style='float:right; margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px;' onclick='kiu.closeUpload()' /><input type='button' value='Upload files' style='margin:0px; margin-right:10px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px; background:#B8E8D4;' onclick='kiu.startUpload()' /><input type='button' value='Upload watermark' style='margin:0px; margin-right:10px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px;' onclick='kiu.uploadWatermark()' />";
            document.body.appendChild(node);
            kib.showhelp(node, 4, 5);
			node.onmouseover = function(){
            	kib.hidehelp();
            }
        }
		<?php addEvent("window", "resize", "viewdim"); ?>
		viewdim();
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            addEvent("document", "keypress", "taste_kiu");
        } else {
            addEvent("document", "keydown", "taste_kiu");
        }
        ?>
        kiu.setFolder(currentfolder);
	}

	this.destr = function(){
		<?php removeEvent("window", "resize", "viewdim"); ?>
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            removeEvent("document", "keypress", "taste_kiu");
        } else {
            removeEvent("document", "keydown", "taste_kiu");
        }
        ?>
		document.body.removeChild($("kiu_maindiv"));
        document.body.removeChild($("kiu_topdiv"));
        document.body.removeChild($("kiu_botdiv"));          
        fw.shadebody(0);
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
        var maindiv = $("kiu_maindiv");
        maindiv.style.width = windowX - 8 + "px";
        maindiv.style.height = windowY - 8 + "px";        
	}
    
    this.closeUpload = function(){
    	kib.hidehelp();
    	fw.removejs("kiu");
        kib.reinit();
    }
    
    function supportAjaxUploadWithProgress() {
        var xhr = new XMLHttpRequest();
        if (window.File && window.FileReader && window.FileList && ('upload' in xhr) && ('onprogress' in xhr.upload)) {
            if(window.FormData){
                return 1;
            } else if('sendAsBinary' in xhr){
                return 1;
            }
        }
        return 0;
    }
    
    this.choseFiles = function(obj){
    	var fakefield = obj.nextSibling.firstChild;
        var fileInput = obj;
    	var ret = "";
		<?php if(in_array($browser, array("ie6", "ie7", "ie9", "opera"))) { ?>
        var temp = fileInput.value.split("\\");
        ret = temp[temp.length-1];
        <?php } else { ?>
		ret = fileInput.files[0].name;
        <?php } ?>
        fakefield.value = ret;
    }
    
     this.choseMFiles = function(obj){
    	var fakefield = obj.nextSibling.firstChild;
        var fileInput = obj;
    	var ret = "";

        for(var i = 0; i < fileInput.files.length; i++){
        	if(i != 0)ret += ", ";
            ret += fileInput.files[i].name;
        }

		var temp = fw.getDim(fakefield, 1);
		fakefield.style.visibility = "hidden";
		fakefield.style.height = "auto";
        fakefield.style.overflow = "visible";
        fakefield.innerHTML = ret;
        temp2 = fw.getDim(fakefield, 1);
        fakefield.style.overflow = "hidden";
        fakefield.style.height = temp[1] + "px";
		fakefield.style.visibility = "visible";
        fw.resize(fakefield, 4, 0, temp2[1]);
    }
    
    this.setFolder = function(folder){
    	currentfolder = folder;
        var i = 0;
        while(document.getElementsByName("kiu_upform"+i)[0]){
	        var obj = document.getElementsByName("kiu_upform"+i)[0];
            obj.dir.value = folder;
            i++;
		}
        $("kiu_selfolder").innerHTML = folder;
    }
    
    this.uploadWatermark = function(){
    	var myform = document.getElementsByName("kiu_watermark_form")[0];
        myform.submit();
    }
    
    this.uploadedWatermark = function(){
    	var obj = $("kiu_wm_preview");
        obj.src = "<?php echo $basedir ?>ki_watermark.pic?" + Math.round(Math.random()*10000);
        obj.style.visibility = 'visible';
    }
    
    this.toggleWatermark = function(wm){
        var i = 0;
        while(document.getElementsByName("kiu_upform"+i)[0]){
	        var obj = document.getElementsByName("kiu_upform"+i)[0];
            if(wm.checked == true){
				obj.addwatermark.value = "1";
       		} else {
            	obj.addwatermark.value = "0";
            }
            i++;
		}
    }
    
    this.startUpload = function(){
		$("kiu_progress").style.display = "block";   
    	if(supportAjaxUploadWithProgress()){
        	sendfile(0);
        } else {
            $("kiu_progress_cur").style.width = "0%";
            $("kiu_progress_tot").style.width = "0%";
            var myform = document.getElementsByName("kiu_upform0");
            var loop = 0;
            while(myform[0]){
	            myform[0].maxx.value = $("kiu_maxx").value;
	            myform[0].maxy.value = $("kiu_maxy").value;
                <?php if( in_array($browser, array("gecko", "webkit")) ){ ?>
                if(myform[0].pic.files[0]){
                    finalsizes.push(myform[0].pic.files[0].size);
                } else {
                    finalsizes.push(0);
                }
                <?php } else { ?>
                if(myform[0].pic.value){
                    finalsizes.push(1);
                } else {
                    finalsizes.push(0);
                }
                <?php } ?>
                loop++;
                myform = document.getElementsByName("kiu_upform"+loop);
            }
            fw.getHTTP("<?php echo $basedir ?>ki_getuploadinfo.php?form=reset", submitform, 0);
		}
    }
    
    function sendfile(no){
        var fileInput = $('kiu_m_file');
        var file = fileInput.files[no];
    
        if(!file){
            allfinished();
            return -1;
        }
    
        var allsize = 0;
        var completesize = 0;
        for(var i = 0; i < no; i++){
            completesize += fileInput.files[i].size;
        }
        for(var i = 0; i < fileInput.files.length; i++){
            allsize += fileInput.files[i].size;
        }
    
        var reader = new FileReader();
        
        reader.onloadend = function(evt){
            
            var xhr = new XMLHttpRequest();
            
            var bar_cur = $("kiu_progress_cur");
	        var bar_tot = $("kiu_progress_tot");
    		
            bar_cur.nextSibling.innerHTML = "Current '" + file.name + "'";
    
            xhr.upload.addEventListener("progress", function(e) {
                if(e.lengthComputable){
                    var curfilecomplete = Math.round((e.loaded / e.total)*file.size);
                    var barsize = Math.round(((completesize+curfilecomplete)/allsize)*100);
                    bar_cur.style.width =  Math.round((e.loaded / e.total)*100) + "%";
                    bar_tot.style.width = barsize + "%";
                    bar_tot.nextSibling.innerHTML = "Total: " + barsize + " %";  
                }
            }, false);
    
            xhr.onload = function(e) { 
                completesize += file.size;
    			bar_cur.style.width = "100%";      
                bar_cur.nextSibling.innerHTML = "'" + file.name + "' uploaded.";
                var barsize = Math.round((completesize/allsize)*100);
	            bar_tot.style.width = barsize + "%";
	            bar_tot.nextSibling.innerHTML = "Total: " + barsize + " %";   
                setTimeout(function(){
                	sendfile(no+1);
				}, 1000);
            };
    
            xhr.open('POST', '<?php echo $basedir ?>ki_upload.php', true);
    
    		var wm = $("kiu_wmcb");
            if(wm.checked == true){
            	wm = "1";
            }else{
            	wm = "0";
            }
    
            if(window.FormData){
                var formData = new FormData();
                formData.append("dir", currentfolder);
                formData.append("addwatermark", wm);
                formData.append("pic", file);
                formData.append("maxx", $("kiu_maxx").value);
                formData.append("maxy", $("kiu_maxy").value);
                xhr.send(formData);
            } else {
                //xhr.setRequestHeader("Cache-Control", "no-cache");
                //xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        
                var CRLF  = "\r\n";
                var boundary = "AJAX-----------------------" + (new Date).getTime();
            
                var requestBody = "Content-Type: multipart/form-data; boundary="+boundary+CRLF+CRLF;
                //requestBody += "Content-Length: "+file.size+CRLF+CRLF;
                requestBody += "--"+boundary+CRLF;
                                
                requestBody += "Content-Disposition: form-data; name=\"dir\""+CRLF+CRLF;
                requestBody += currentfolder+CRLF;
                requestBody += "--"+boundary+CRLF;

                requestBody += "Content-Disposition: form-data; name=\"addwatermark\""+CRLF+CRLF;
                requestBody += wm+CRLF;
                requestBody += "--"+boundary+CRLF;

                requestBody += "Content-Disposition: form-data; name=\"maxx\""+CRLF+CRLF;
                requestBody += $("kiu_maxx").value+CRLF;
                requestBody += "--"+boundary+CRLF;
 
                requestBody += "Content-Disposition: form-data; name=\"maxy\""+CRLF+CRLF;
                requestBody += $("kiu_maxy").value+CRLF;
                requestBody += "--"+boundary+CRLF;
                
                requestBody += "Content-Disposition: form-data; name=\"pic\"; filename=\""+file.name+"\""+CRLF;
                requestBody += "Content-Type: application/octet-stream"+CRLF+CRLF;
                requestBody += evt.target.result+CRLF;
                requestBody += "--"+boundary+"--"+CRLF;
                
                xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + boundary);
                xhr.sendAsBinary(requestBody);
            }
        };
        reader.readAsBinaryString(file);
    }
    
    
    function submitform(responseText, nextform){
        for(var i = nextform; i < finalsizes.length; i++){
        	if(finalsizes[i] != 0){
            	//alert("started " + i);
            	document.getElementsByName("kiu_upform"+i)[0].submit();
                setTimeout( function(){
                    fw.getHTTP("<?php echo $basedir ?>ki_getuploadinfo.php?form="+i, gotuploadinfo, i);
                }, 1000);
                return;
            }
        }
        var counter = 0;
        for(var i = 0; i < finalsizes.length; i++){
            if(finalsizes[i] == 0)counter++;	
        }
        if(counter == finalsizes.length){
            allfinished();
        } else { // alle gestartet, aber es laufen noch uploads
        	setTimeout( function(){
                fw.getHTTP("<?php echo $basedir ?>ki_getuploadinfo.php?form="+(nextform-1), gotuploadinfo, nextform-1);
            }, 1000);
        }
	}
    
    function allfinished(){
    	$("kiu_progress").style.display = "none";
        var bar_cur = $("kiu_progress_cur");
        var bar_tot = $("kiu_progress_tot");
		bar_cur.nextSibling.innerHTML = "";
        bar_tot.nextSibling.innerHTML = "";
        bar_cur.style.width = "0%";
        bar_tot.style.width = "0%";
        if(!supportAjaxUploadWithProgress()){
            finalsizes = Array();
            var myform = document.getElementsByName("kiu_upform0");
            var loop = 0;
            while(myform[0]){
                myform[0].reset();
                loop++;
                myform = document.getElementsByName("kiu_upform"+loop);
            }
		} else {
        	$('kiu_m_file_form').reset();
            var fakefield = $('kiu_m_file').nextSibling.firstChild;
            fakefield.innerHTML = "";
	        fw.resize(fakefield, 4, 0, 18);
        }
    }
    
    function gotuploadinfo(responseText, nextform){
        var obj = $("kiu_uploaddata");
        var counter;
        var currentsizes = Array();

		obj.innerHTML = responseText;
        
        for(var i = 1; i < obj.childNodes.length; i++){
            currentsizes.push(obj.childNodes[i].value);
        }
    	
        var bar_cur = $("kiu_progress_cur");
        var bar_tot = $("kiu_progress_tot");
        <?php if( in_array($browser, array("gecko", "webkit")) ){ ?>
        var fileName = document.getElementsByName("kiu_upform"+nextform)[0].pic.files[0].name;
        if(currentsizes[nextform] != "finished"){
            var barsize = Math.round((currentsizes[nextform]/finalsizes[nextform])*100);
            bar_cur.style.width = barsize + "%";
            bar_cur.nextSibling.innerHTML = "Current '" + fileName + "'";
            var total = 0;
            var current = 0;
            for(var i = 0; i < finalsizes.length; i++){
			    total += finalsizes[i];
               	if(currentsizes[i] != "finished"){                    
                    current += parseInt(currentsizes[i]);
				} else {
                	current += parseInt(finalsizes[i]);
                }
            }
            barsize = Math.round((current/total)*100);
            bar_tot.style.width = barsize + "%";
            bar_tot.nextSibling.innerHTML = "Total: " + barsize + " %";            
		} else {
        	bar_cur.style.width = "100%";
            bar_cur.nextSibling.innerHTML = "'" + fileName + "' uploaded.";
        }
        <?php } else { ?> 
        var fileName = document.getElementsByName("kiu_upform"+nextform)[0].pic.value.split("\\");
        fileName = fileName[fileName.length-1];
        if(currentsizes[nextform] != "finished"){
            bar_cur.style.width = "100%";
            bar_cur.nextSibling.innerHTML = "Current '" + fileName + "' " + Math.round(currentsizes[nextform]/1024) + " KB uploaded";
            var total = 0;
            var current = 0;
            for(var i = 0; i < finalsizes.length; i++){
			    total += finalsizes[i];
               	if(currentsizes[i] == "finished"){                    
                    current += 1;
				}
            }
            var barsize = Math.round((current/total)*100);
            bar_tot.style.width = barsize + "%";
            bar_tot.nextSibling.innerHTML = "Total: " + barsize + " %";
		} else {
        	bar_cur.style.width = "100%";
            bar_cur.nextSibling.innerHTML = "'" + fileName + "' uploaded.";
        }
		<?php } ?>
        
        counter = 0;
        for(var i = 0; i < currentsizes.length; i++){
            if(currentsizes[i] == "finished" || finalsizes[i] == 0)counter++;	
        }
        if(counter == currentsizes.length){
            allfinished();
        } else {
            for(var i = 0; i < currentsizes.length; i++){
                if(currentsizes[i] != "finished" && finalsizes[i] != 0){
                    if(obj.firstChild.value == 1){
						submitform("", nextform+1);
                    } else { // upload läuft, aber nächster kann noch nicht gestartet werden
                        setTimeout( function(){
                            fw.getHTTP("<?php echo $basedir ?>ki_getuploadinfo.php?form="+nextform, gotuploadinfo, nextform);
                        }, 1000);
					}
                    return;
                }
            }
        }
    
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
	
	function taste_kiu(aEvent) {
		aEvent = aEvent ? aEvent : window.event;
        var keyCode = aEvent.keyCode;
		if(keyCode == 27){
			kiu.closeUpload();
			preventDefaultAction(aEvent);
			return false;
		}
	}
}

