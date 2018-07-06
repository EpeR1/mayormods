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

$global_start = -1;
$global_length = -1;
$global_count = -1;

reset($GLOBALS);

while (list($key, $val) = each($GLOBALS)) {
	$global_count++;
    if($global_start == -1){
		if($key === "fr_width"){
			$global_start = $global_count;
			continue;
		}
	}
	if($global_length == -1){
		if($key === "global_start"){
			$global_length = $global_count - $global_start;
			break;
		}
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

// Settings module 'kis' --------------------------------------------------------------------

function kis_module(){

	/* -------------------------------- variables ----------------------------------- */

	var windowX;
	var windowY;
    var currentfile = "";
    var open = 0;

	/* -------------------------------- getElementById function --------------------- */
			
	function $(id){
		return document.getElementById(id);
	}

	/* -------------------------------- constructor/descturctor --------------------- */

	this.constr = function(){
        if(typeof(kiv_module) == "function")kiv.closeImage();
        if(typeof(kie_module) == "function")kie.closeGallery();
        if(typeof(kiu_module) == "function")kiu.closeUpload();
        if(typeof(kid_module) == "function")kid.close();
        if(typeof(kic_module) == "function")kic.close();
        if(typeof(kim_module) == "function")kim.close();
    	fw.shadebody(1);
		var node;
		if(!$("kis_maindiv")){
			node = document.createElement("div");
			node.id = "kis_maindiv";
            node.style.background = "#aaaaaa";
			node.style.position = "<?php echo $posfix ?>";
			node.style.left = "0px";
			node.style.top = "0px";
			node.style.zIndex = 1000;
			node.style.overflowX = "hidden";
            node.style.overflowY = "auto";
			node.style.display = "block";
            node.style.padding = "4px";
            node.innerHTML = "<div id='settings_div' style='background:#D2D2D2; width:960px; margin:auto; font:12px Tahoma, sans-serif; color:#222222; border:1px solid #000000; padding:46px 4px 36px 4px;'></div>";
			document.body.appendChild(node);
		}
        if(!$("kis_topdiv")){
        	node = document.createElement("div");
			node.id = "kis_topdiv";
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
            	dirs = dirs + "<span style='cursor:pointer; text-decoration:underline; margin-right:5px; line-height:14px;' onclick=\"kis.getSettings('" + kib.dirs[i] + "')\">" + kib.dirs[i] + "</span>";
            }
            node.innerHTML = "<div style='margin-bottom:5px;'><span style='font-size:14px; line-height:18px; font-weight:bold; color:#003; margin-right:5px;'>Config Files</span><span style='background:#003; border:1px solid #CCC; padding:3px; color:#ffffff;' id='kis_selfile'></span></div><span style='cursor:pointer; text-decoration:underline; font-weight:bold; margin-right:5px; line-height:14px;' onclick='kis.getSettings()'>default</span>" + dirs;
            document.body.appendChild(node);
        }
        if(!$("kis_botdiv")){
        	node = document.createElement("div");
			node.id = "kis_botdiv";
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
            node.innerHTML = "<input type='button' value='Close' style='float:right; margin:0px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px;' onclick='kis.closeSettings()' /><input type='button' value='Reset' style='float:right; margin:0px; margin-right:10px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px; display:none;' onclick='kis.resetSettings()' id='kis_resetbutton' /><input type='button' value='Save' style='float:left; margin:0px; margin-right:10px; padding:0px; cursor:pointer; border:1px solid #000; height:26px; width:150px; background:#b8e8d4;' onclick='kis.saveSettings()' />";
            document.body.appendChild(node);
        }
        
		node = $("settings_div");
        node.innerHTML = "<table style='padding:0px; color:#006; font-size:11px;' cellpadding='4' cellspacing='0'><tr><td style='width:150px;' class='kis_paramname'>$fr_width</td><td style='width:200px;'><input id='fr_width' class='kis_param' /></td><td rowspan='2'>This two parameters set the dimension of the gallery in pixels. They are equivallent to css-width and height of the koschtitgallery-div.</td></tr><tr><td class='kis_paramname'>$fr_height</td><td><input id='fr_height' class='kis_param' /></td></tr><tr><td class='kis_paramname'>$fr_color</td><td><input id='fr_color' class='kis_param' /></td><td>The background color of the gallery. Set a hex-color '#rrggbb' or 'none'.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$thumbs</td><td><input id='thumbs' class='kis_param' /></td><td>Sets the amount of thumbnails that are visible on one page of the gallery.</td></tr><tr><td class='kis_paramname'>$th_per_line</td><td><input id='th_per_line' class='kis_param' /></td><td>Sets the amout of thumbnails in one row. This should be less or equal to the <b>$thumbs</b> - parameter.</td></tr><tr><td class='kis_paramname'>$th_lines</td><td><input id='th_lines' class='kis_param' /></td><td>Here you can set how many thumbnail rows you want to have. Set to 'auto' or a number.</td></tr><tr><td class='kis_paramname'>$th_width</td><td><input id='th_width' class='kis_param' /></td><td rowspan='2'>This two parameters set the maximum dimension of the thumbnails in pixels. Set to 'auto' or a number.</td></tr><tr><td class='kis_paramname'>$th_height</td><td><input id='th_height' class='kis_param' /></td></tr><tr><td class='kis_paramname'>$th_bord_size</td><td><input id='th_bord_size' class='kis_param' /></td><td>The border width of the thumbnails in pixels. Set to a number greater or equal to zero.</td></tr><tr><td class='kis_paramname'>$th_bord_color</td><td><input id='th_bord_color' class='kis_param' /></td><td>The border color of the thumbnails. Set a hex-color '#rrggbb' or 'none'.</td></tr><tr><td class='kis_paramname'>$th_bord_hover_color</td><td><input id='th_bord_hover_color' class='kis_param' /></td><td>If you hover over the thumbnails their border color will change to this. Set a hex-color '#rrggbb' or 'none'.</td></tr><tr><td class='kis_paramname'>$th_bord_hover_increase</td><td><input id='th_bord_hover_increase' class='kis_param' /></td><td>You can also change the size of the thumbnails when you hover over them. They will be increased by this value. Set to a decimal with a dot.</td></tr><tr><td class='kis_paramname'>$th_shadow</td><td><input id='th_shadow' class='kis_param' yn='1' /></td><td>Enables or disables the shadow which displays when mouse hovers a thumbnail.</td></tr><tr><td class='kis_paramname'>$th_to_square</td><td><input id='th_to_square' class='kis_param' yn='1' /></td><td>For square thumbs set to 1, or else set to 0.</td></tr><tr><td class='kis_paramname'>$th_2sq_crop_hori</td><td><input id='th_2sq_crop_hori' class='kis_param' /></td><td>If $th_to_square is set to 1, you can change the horizontal cropping of the thumbmails with this parameter. Set to 'left', 'center' or 'right'.</td></tr><tr><td class='kis_paramname'>$th_2sq_crop_vert</td><td><input id='th_2sq_crop_vert' class='kis_param' /></td><td>If $th_to_square is set to 1, you can change the vertical cropping of the thumbmails with this parameter. Set to 'top', 'middle' or 'bottom'.</td></tr><tr><td class='kis_paramname'>$thumbs_to_disk</td><td><input id='thumbs_to_disk' class='kis_param' yn='1' /></td><td>Set this parameter to 1, if you want your thumbnails to be saved on the server. If you want to generate your thumbnails 'on the fly', set this to 0.</td></tr><tr><td colspan='3' style='color:#666;'><b>Remember</b>: If $th_to_square is set to 1, you can also adjust the cropping for each thumbnail individually by clicking on the respective thumbnail when logged in as administrator.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$pic_order</td><td><input id='pic_order' class='kis_param' /></td><td>This parameter sets the order of your pictures in your gallery. Set to 0 for newest pictures first, set to 1 for oldest pictures first, set to 2 for alphabetical order or set to 3 for image creation order (uses EXIF). If you want to adjust the order of your pictures manually with the admin panel (Change order) set this to 4.</td></tr><tr><td class='kis_paramname'>$resize_auto</td><td><input id='resize_auto' class='kis_param' yn='1' /></td><td>If set to 1 the gallery resizes automatically to smaller size if vertical space is available. If set to 0 the gallery stays always fixed size.</td></tr><tr><td class='kis_paramname'>$bord_size</td><td><input id='bord_size' class='kis_param' /></td><td>Sets the border size of displayed pictures in pixels. Set to a number greater or equal to zero.</td></tr><tr><td class='kis_paramname'>$bord_color</td><td><input id='bord_color' class='kis_param' /></td><td>The border color of displayed pictures. Set a hex-color '#rrggbb' or 'none'.</td></tr><tr><td class='kis_paramname'>$max_pic_width</td><td><input id='max_pic_width' class='kis_param' /></td><td rowspan='2'>You can set a maximum width and height for displayed pictures. Set to a number (for total pixel size) or leave 'none' for no limits. You can also set a value between 0 and 1.0 to indicate a percentage of window size.</td></tr><tr><td class='kis_paramname'>$max_pic_height</td><td><input id='max_pic_height' class='kis_param' /></td></tr><tr><td class='kis_paramname'>$oversize_allowed</td><td><input id='oversize_allowed' class='kis_param' yn='1' /></td><td>Set to 0, if you want to adjust the size of displayed pictures to the browser window size. If you want to show your displayed pictures in full resolution set to 1.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$comments</td><td><input id='comments' class='kis_param' yn='1' /></td><td>1 enables picture comments and 0 disables them.</td></tr><tr><td class='kis_paramname'>$comm_text_size</td><td><input id='comm_text_size' class='kis_param' /></td><td>Set the font size in pixels for comments here.</td></tr><tr><td class='kis_paramname'>$comm_text_color</td><td><input id='comm_text_color' class='kis_param' /></td><td>Sets the font color for comments. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$comm_text_font</td><td><input id='comm_text_font' class='kis_param' /></td><td>The font face for picture- and viewer comments . This is equal to css-font-face.</td></tr><tr><td class='kis_paramname'>$comm_text_align</td><td><input id='comm_text_align' class='kis_param' /></td><td>You can change the text alignment for comments with this parameter. Set to 'left', 'center' or 'right'.</td></tr><tr><td class='kis_paramname'>$comm_auto</td><td><input id='comm_auto' class='kis_param' yn='1' /></td><td>If you want to have automatic picture comments set this to 1, or else set to 0.</td></tr><tr><td class='kis_paramname'>$comm_auto_string</td><td><input id='comm_auto_string' class='kis_param' /></td><td>If <b>$comm_auto</b> is set to 1, you can enter your automatic picture comment here. There are some variables available: '%x' = picture number / '%X' = number of all pictures / '%f' = filename / '%g' = gallery folder</td></tr><tr><td colspan='3' style='color:#666;'><b>Remember</b>: If $comm_auto is set to 1, all custom comments won't be displayed or saved.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$viewercomments</td><td><input id='viewercomments' class='kis_param' yn='1' /></td><td>1 = viewer comments are turned on / 0 = viewer comments are turned off</td></tr><tr><td class='kis_paramname'>$moderate_posts</td><td><input id='moderate_posts' class='kis_param' yn='1' /></td><td>1 = moderate new viewer comments / 0 = no moderation<br />Enable $admin_mail if you want to receive notification for new picture comments.</td></tr><tr><td class='kis_paramname'>$vcomm_header_color</td><td><input id='vcomm_header_color' class='kis_param' /></td><td>This font color is used for viewer comment form descriptions. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$vcomm_box_color</td><td><input id='vcomm_box_color' class='kis_param' /></td><td>This is the text color that is used for the comment form. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$vcomm_text_color</td><td><input id='vcomm_text_color' class='kis_param' /></td><td>Defines the text color for viewer comments and their name. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$vcomm_timedate_color</td><td><input id='vcomm_timedate_color' class='kis_param' /></td><td>Defines the text color for date and time line on the viewer comments page. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$vcomm_back_color</td><td><input id='vcomm_back_color' class='kis_param' /></td><td>The background color of the viewer comment form is defined here. Set a hex-color '#rrggbb' or 'none'.</td></tr><tr><td class='kis_paramname'>$vcomm_bord_color</td><td><input id='vcomm_bord_color' class='kis_param' /></td><td>Sets the border color for all forms and inputs on the viewer comments page. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$vcomm_horline_color</td><td><input id='vcomm_horline_color' class='kis_param' /></td><td>Defines the color for the horizontal lines between viewer comments. Set a hex-color '#rrggbb'.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$slideshow</td><td><input id='slideshow' class='kis_param' yn='1' /></td><td>Set to 1 if you want to enable a slideshow, or set to 0 else.</td></tr><tr><td class='kis_paramname'>$downloadpics</td><td><input id='downloadpics' class='kis_param' yn='1' /></td><td>Set to 1 if you want to allow full resolution picture downloads, or set to 0 else.</td></tr><tr><td class='kis_paramname'>$checkgps</td><td><input id='checkgps' class='kis_param' yn='1' /></td><td>If set to 1, a link to Googlemaps is displayed when an image contains geodetic coordinates in the exif metadata. Set to 0 if you don't need this feature.</td></tr><tr><td class='kis_paramname'>$cellinfo</td><td><input id='cellinfo' class='kis_param' yn='1' /></td><td>If <b>$checkgps</b> is set to 1, you can additionally check the image metadata for cellid information added by mobile phones. This information is then send to opencellid.org and if the cell gps position is deposed there a link to Googlemaps will be displayed too.</td></tr><tr><td class='kis_paramname'>$show_nav</td><td><input id='show_nav' class='kis_param' yn='1' /></td><td>Set this parameter to 0, if you don't need the navigation icons below the thumbnails. Otherwise set to 1.</td></tr><tr><td class='kis_paramname'>$nav_always</td><td><input id='nav_always' class='kis_param' yn='1' /></td><td>If this is set to 0, the navigation icons will only be visible if you hover with your mouse over the gallery.</td></tr><tr><td class='kis_paramname'>$nav_pos</td><td><input id='nav_pos' class='kis_param' /></td><td>Aligns the navigation icons below the thumbnails. Set to 'left', 'center' or 'right'.</td></tr><tr><td class='kis_paramname'>$nav_color</td><td><input id='nav_color' class='kis_param' /></td><td>Defines the background color of the navigation icons toolbar. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$nav_border_color</td><td><input id='nav_border_color' class='kis_param' /></td><td>Defines the border color of the navigation icons toolbar. Set a hex-color '#rrggbb'.</td></tr><tr><td class='kis_paramname'>$nav_style</td><td><input id='nav_style' class='kis_param' /></td><td>1 = rectangular navigation icons / 2 = round navigation icons (doesn't work for Internet Explorer <= 8)</td></tr><tr><td class='kis_paramname'>$show_image_nav</td><td><input id='show_image_nav' class='kis_param' yn='1' /></td><td>Enables or disables the navigation icons for displayed pictures. Set to 1 or either to 0.</td></tr><tr><td class='kis_paramname'>$image_nav_always</td><td><input id='image_nav_always' class='kis_param' yn='1' /></td><td>If this is set to 0, the image navigation icons and the image sharing link will slide out if mouse moves away from them. Otherwise they stay visible (set to 1).</td></tr><tr><td class='kis_paramname'>$show_share</td><td><input id='show_share' class='kis_param' yn='1' /></td><td>Enables or disables the display of the image sharing link. Set to 1 or either to 0. (not visible when logged in as admin)</td></tr><tr><td class='kis_paramname'>$show_help</td><td><input id='show_help' class='kis_param' yn='1' /></td><td>If set to 1, a small help icon is displayed on bottom right/left of displayed pictures. Set to 0 if you don't need this.</td></tr><tr><td class='kis_paramname'>$help_pos</td><td><input id='help_pos' class='kis_param' /></td><td>Set to 'left' or 'right' to define where to place the help icon.</td></tr><tr><td class='kis_paramname'>$show_preview</td><td><input id='show_preview' class='kis_param' yn='1' /></td><td>You can disable/enable preview pictures that are displayed when you move your mouse beyond the sides of displayed pictures. Set to 1 or 0.</td></tr><tr><td class='kis_paramname'>$preview_style</td><td><input id='preview_style' class='kis_param' /></td><td>If set to 1, it displays the next/previous picture as preview. The further you move your mouse, the bigger the preview images get. If set to 2, only the icons 'ki_next.png'/'ki_back.png' are displayed. This parameter is only valid, when $show_preview is set to 1.</td></tr><tr><td class='kis_paramname'>$preview_pics</td><td><input id='preview_pics' class='kis_param' /></td><td>You can adjust the number of preview pictures that are displayed, if you move your mouse to the border of the browser window. If this is set to 0 no preview pictures will be displayed.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$show_explorer</td><td><input id='show_explorer' class='kis_param' yn='1' /></td><td>This parameter enables/disables the gallery explorer (maximized full window view).</td><tr><td class='kis_paramname'>$explorer_padding</td><td><input id='explorer_padding' class='kis_param' /></td><td>Sets the padding ( in pixels ) between pictures in 'maximized' view.</td></tr><tr><td colspan='3' class='kis_spacer'></td></tr><tr><td class='kis_paramname'>$watermark_hori</td><td><input id='watermark_hori' class='kis_param' /></td><td>Defines the horicontal position of the watermark on images. Set to 'left', 'center' or 'right'.</td></tr><tr><td class='kis_paramname'>$watermark_vert</td><td><input id='watermark_vert' class='kis_param' /></td><td>Defines the vertical position of the watermark on images. Set to 'top', 'middle' or 'bottom'.</td></tr><tr><td class='kis_paramname'>$watermark_size</td><td><input id='watermark_size' class='kis_param' /></td><td>You can adjust the size of the watermark. If you want to spread the watermark over the whole image set this to 1. Leave this at 0 if you want to leave the watermark size as it is. Otherwise set to a decimal from 0.01 to 0.99 .</td></tr><tr class='kis_global'><td colspan='3' class='kis_spacer'></td></tr><tr class='kis_global'><td class='kis_paramname'>$fade_color</td><td><input id='fade_color' class='kis_param' /></td><td>Adjust the shade color with this parameter. Set a hex-color '#rrggbb'.</td></tr><tr class='kis_global'><td class='kis_paramname'>$fade_alpha</td><td><input id='fade_alpha' class='kis_param' /></td><td>Changes the opacity of the shade. Set a number from 0 to 10, where 10 is for full opacity and 0 for no opacity at all.</td></tr><tr class='kis_global'><td class='kis_paramname'>$shade_while_loading</td><td><input id='shade_while_loading' class='kis_param' yn='1' /></td><td>If this is set to 1, the website will be shaded during the galleries will be initialized. Set to 0, if you don't need this.</td></tr><tr class='kis_global'><td class='kis_paramname'>$disable_animation</td><td><input id='disable_animation' class='kis_param' yn='1' /></td><td>You can disable all script animations if you set this to 1. If you wish to enable animations, set this to 0.</td></tr><tr class='kis_global'><td class='kis_paramname'>$slideshow_time</td><td><input id='slideshow_time' class='kis_param' /></td><td>This is the time an image will be displayed during slideshow. Enter a value in milliseconds.</td></tr><tr class='kis_global'><td colspan='3' style='color:#666;'><b>Remember</b>: These are global settings. The changes will only be applied if you refresh your website.</td></tr><tr class='kis_global'><td colspan='3' class='kis_spacer'></td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_next</td><td><input id='nav_next' class='kis_param' /></td><td>Next thumbnails page icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_back</td><td><input id='nav_back' class='kis_param' /></td><td>Previous thumbnails page icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_maxi</td><td><input id='nav_maxi' class='kis_param' /></td><td>Maximize gallery icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_kiv_next</td><td><input id='nav_kiv_next' class='kis_param' /></td><td>Next gallery picture icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_kiv_back</td><td><input id='nav_kiv_back' class='kis_param' /></td><td>Previous gallery picture icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_kiv_close</td><td><input id='nav_kiv_close' class='kis_param' /></td><td>Close icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_gps_coord</td><td><input id='nav_gps_coord' class='kis_param' /></td><td>Googlemaps icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_kiv_vcomm</td><td><input id='nav_kiv_vcomm' class='kis_param' /></td><td>Show/Add viewer comments icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$nav_kiv_download</td><td><input id='nav_kiv_download' class='kis_param' /></td><td>Download full picture icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$slideshow_start</td><td><input id='slideshow_start' class='kis_param' /></td><td>Start slideshow icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$slideshow_stop</td><td><input id='slideshow_stop' class='kis_param' /></td><td>Stop slideshow icon description.</td></tr><tr class='kis_global'><td class='kis_paramname'>$help_text</td><td><input id='help_text' class='kis_param' /></td><td>The help text, that is displayed when you hover over the info icon. The following variable is available: '[mouse]' = mouse moving image</td></tr><tr class='kis_global'><td class='kis_paramname'>$vcomm_lac</td><td><input id='vcomm_lac' class='kis_param' /></td><td>\"Leave a comment\"</td></tr><tr class='kis_global'><td class='kis_paramname'>$vcomm_name</td><td><input id='vcomm_name' class='kis_param' /></td><td>\"Name\"</td></tr><tr class='kis_global'><td class='kis_paramname'>$vcomm_comm</td><td><input id='vcomm_comm' class='kis_param' /></td><td>\"Comment\"</td></tr><tr class='kis_global'><td class='kis_paramname'>$vcomm_post</td><td><input id='vcomm_post' class='kis_param' /></td><td>\"Post comment\"</td></tr><tr class='kis_global'><td class='kis_paramname'>$vcomm_clk</td><td><input id='vcomm_clk' class='kis_param' /></td><td>\"Click on the image to flip back to the full image.\"</td></tr><tr class='kis_global'><td class='kis_paramname'>$vcomm_ncy</td><td><input id='vcomm_ncy' class='kis_param' /></td><td>\"No comments yet.\"</td></tr><tr class='kis_global'><td colspan='3' class='kis_spacer'></td></tr><tr class='kis_global'><td class='kis_paramname'>$admin_mail</td><td><input id='admin_mail' class='kis_param' yn='1' /></td><td>Enable or disable Email notifications for new image viewer comments.</td></tr><tr class='kis_global'><td class='kis_paramname'>$admin_mail_from</td><td><input id='admin_mail_from' class='kis_param' /></td><td>Email-address from where the Emails are send.</td></tr><tr class='kis_global'><td class='kis_paramname'>$admin_mail_to</td><td><input id='admin_mail_to' class='kis_param' /></td><td>Email-address where the Emails are send to.</td></tr><tr class='kis_global'><td colspan='3' style='color:#666;'><b>Remember</b>: The Email notifications system may only work if the '$admin_mail_from'-address actually belongs to this server. The PHP mail()-method needs to be working on your server.</td></tr><tr class='kis_global'><td colspan='3' class='kis_spacer'></td></tr><tr class='kis_global'><td class='kis_paramname'>$show_warnings</td><td><input id='show_warnings' class='kis_param' yn='1' /></td><td>Set to 0, if you don't need to get script warnings displayed.</td></tr><tr class='kis_global'><td colspan='3' class='kis_spacer'></td></tr><tr class='kis_global'><td class='kis_paramname'>$user</td><td><input id='user' class='kis_param' /></td><td>Change the user login name. Users can only upload new images.</td></tr><tr class='kis_global'><td class='kis_paramname'>$userpw</td><td><input id='userpw' class='kis_param' type='password' /></td><td>Change the user login password.</td></tr><tr class='kis_global'><td colspan='3' class='kis_spacer'></td></tr><tr class='kis_global'><td class='kis_paramname'>$admin</td><td><input id='admin' class='kis_param' /></td><td>Change the admin login name.</td></tr><tr class='kis_global'><td class='kis_paramname'>$pw</td><td><input id='pw' class='kis_param' type='password' /></td><td>Change the admin login password.</td></tr><tr class='kis_global'><td colspan='3' style='color:#666;'><b>Remember</b>: If you change your username and/or password you have to logout first and then login again before you can change any other settings.</td></tr></table>";
        placeparams();
		<?php addEvent("window", "resize", "viewdim"); ?>
		viewdim();
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            addEvent("document", "keypress", "taste_kis");
        } else {
            addEvent("document", "keydown", "taste_kis");
        }
        ?>
        <?php addEvent("document", "mousemove", "mousemoved"); ?>
        kis.getSettings();
        fw.move("authorization", 2, -42, 0);
        fw.move("kis_topdiv", 2, -96, 0);
	}

	this.destr = function(){
		<?php removeEvent("window", "resize", "viewdim"); ?>
		<?php
        if(!in_array($browser, array("ie6", "ie7", "ie9", "webkit"))){
            removeEvent("document", "keypress", "taste_kis");
        } else {
            removeEvent("document", "keydown", "taste_kis");
        }
        ?>
		<?php removeEvent("document", "mousemove", "mousemoved"); ?>
		document.body.removeChild($("kis_maindiv")); 
        document.body.removeChild($("kis_topdiv"));
        document.body.removeChild($("kis_botdiv"));        
        fw.shadebody(0);
        fw.move("authorization", 2, 55, 0);
       	fw.move("kis_topdiv", 2, 0, 0);
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
        var maindiv = $("kis_maindiv");
        maindiv.style.width = windowX - 8 + "px";
        maindiv.style.height = windowY - 8 + "px";
	}
    
    this.closeSettings = function(){
    	fw.removejs("kis");
    }
    
    this.resetSettings = function(){
    	if(currentfile != "default"){
        	var nodes = document.getElementsByTagName("input");
            var counter = 0;
            for(var i = 0; i < nodes.length; i++){
                if(nodes[i].className == 'kis_param'){
                    nodes[i].value = "";
                    if(nodes[i].getAttribute("yn")){
                        document.getElementsByName("kis_group"+counter)[0].checked = false;
                        document.getElementsByName("kis_group"+counter)[1].checked = false;
                       	counter++;
                    }
                }
            }
        }
    }
    
    function placeparams(){
    	var nodes = document.getElementsByTagName("input");
        var counter = 0;
        for(var i = 0; i < nodes.length; i++){
        	if(nodes[i].className == 'kis_param'){
            	nodes[i].id = "kis_" + nodes[i].id;
                if(nodes[i].getAttribute("yn")){
	                nodes[i].style.display = "none";
                	nodes[i].parentNode.style.color = "#000000";
					var node = document.createElement("label");
                    node.style.border = "1px solid #8c8c8c";
                    node.style.padding = "2px 0px 2px 15px";
                    node.style.background = "#D6ECA6";
                    node.style.width = "74px";
                    node.style.height = "16px";
                    node.style.lineHeight = "15px";
                    node.style.display = "block";
                    <?php if(in_array($browser, array("ie6", "ie7"))){ ?>
                    node.style.styleFloat = "left";
                    <?php } else { ?>
                    node.style.cssFloat = "left";
                    <?php } ?>
                    node.style.marginRight = "13px";
                    node.innerHTML = "<input type='radio' style='float:left; vertical-align:middle; height:16px; width:16px; padding:0px; margin:0px 3px 0px 0px;' name='kis_group"+counter+"' onclick=\"document.getElementById('"+nodes[i].id+"').value = '1';\" /><span style='float:left;'>Enable</span>";
                    nodes[i].parentNode.appendChild(node);
					node = document.createElement("label");
                    node.style.border = "1px solid #8c8c8c";
                    node.style.padding = "2px 0px 2px 15px";
                    node.style.background = "#ECA6D6";
                    node.style.width = "74px";
                    node.style.height = "16px";
                    node.style.lineHeight = "15px";
                    node.style.display = "block";
                    <?php if(in_array($browser, array("ie6", "ie7"))){ ?>
                    node.style.styleFloat = "left";
                    <?php } else { ?>
                    node.style.cssFloat = "left";
                    <?php } ?>
                    node.innerHTML = "<input type='radio' style='float:left; vertical-align:middle; height:16px; width:16px; padding:0px; margin:0px 3px 0px 0px;' name='kis_group"+counter+"' onclick=\"document.getElementById('"+nodes[i].id+"').value = '0';\" /><span style='float:left;'>Disable</span>";
                    nodes[i].parentNode.appendChild(node);
                    counter++;
                } else {
                    nodes[i].style.width = "190px";
                    nodes[i].style.border = "1px solid #8c8c8c";
                    nodes[i].style.padding = "2px";
                    nodes[i].style.margin = "0px";
                    nodes[i].style.background = "#e1edfc";
                    nodes[i].style.color = "#000000";
                    nodes[i].style.textAlign = "center";
				}
            }
        }
    	nodes = document.getElementsByTagName("td");
        for(var i = 0; i < nodes.length; i++){
            nodes[i].style.padding = "4px";
        	if(nodes[i].className == 'kis_paramname'){
            	nodes[i].style.fontWeight = "bold";
                nodes[i].style.color = "#5f443b";
                nodes[i].style.fontSize = "12px";
            }
        	if(nodes[i].className == 'kis_spacer'){
            	nodes[i].innerHTML = "<hr style='color:#398789;' />";
                nodes[i].style.height = "19px";
			}
		}
	}
   
	this.getSettings = function(setupfile){
    	var params = "?file=";
        if(setupfile)
        	params += setupfile + "&get=1";
		else
        	params = "?get=1";
		var nodes = document.getElementsByTagName("input");
        var counter = 0;
        for(var i = 0; i < nodes.length; i++){
            if(nodes[i].className == 'kis_param'){
                nodes[i].value = "";
                if(nodes[i].getAttribute("yn")){
                	document.getElementsByName("kis_group"+counter)[0].checked = false;
					document.getElementsByName("kis_group"+counter)[1].checked = false;
                    counter++;
                }
            }
        }
        nodes = document.getElementsByTagName("tr");
        if(setupfile){
            for(var i = 0; i < nodes.length; i++){
                if(nodes[i].className == 'kis_global'){
                    nodes[i].style.display = "none";
                }
            }
        } else {
            for(var i = 0; i < nodes.length; i++){
                if(nodes[i].className == 'kis_global'){
                	<?php if($browser === "ie6"){ ?>
                    nodes[i].style.display = "block";
                    <?php } else { ?>
                    nodes[i].style.display = "table-row";
                    <?php } ?>
                }
            }            
        }
        $("kis_selfile").innerHTML = "";
        $("kis_resetbutton").style.display = "none";
		fw.getHTTP("<?php echo $basedir ?>ki_getsetsettings.php" + params, gotSettings, setupfile);
	}
    
    function gotSettings(responseText, setupfile){
        if(!setupfile)setupfile = "default";
        currentfile = setupfile;
        if(currentfile == "default"){
            $("kis_selfile").innerHTML = "ki_setup.php";
        } else {
            $("kis_selfile").innerHTML = setupfile + "_ki_setup.php";
        }
        if(currentfile != "default"){
        	$("kis_resetbutton").style.display = "block";
        }
    	if(responseText != ""){
 			var jsontxt = responseText;
			var settings = eval("(" + jsontxt + ")");
            
            <?php
            reset($GLOBALS);
            for($i = 0; $i < $global_start; $i++)next($GLOBALS);
            for($i = 0; $i < $global_length; $i++){
                list($key, $val) = each($GLOBALS);
				echo "\$(\"kis_$key\").value = settings.$key ? stripslashes(settings.$key) : \"\";\r\n";
            }
			?>
            $("kis_pw").value = "";
            $("kis_userpw").value = "";
            
            var nodes = document.getElementsByTagName("input");
            var counter = 0;
            for(var i = 0; i < nodes.length; i++){
                if(nodes[i].className == 'kis_param'){
                    if(nodes[i].getAttribute("yn")){
                    	if(nodes[i].value == "1")document.getElementsByName("kis_group"+counter)[0].checked = true;
                        if(nodes[i].value == "0")document.getElementsByName("kis_group"+counter)[1].checked = true;
	                	counter++;                        
                    }
				}
			}

        }
    }
    
    function stripslashes(str) {
        str = str.replace(/\\'/g,'\'');
        str = str.replace(/\\"/g,'"');
        str = str.replace(/\\0/g,'\0');
        str = str.replace(/\\\\/g,'\\');
	    return str;
    }
      
    this.saveSettings = function(){
    	if(currentfile != ""){
            var params = "?file=" + currentfile + "&set=1";
            <?php
            reset($GLOBALS);
            for($i = 0; $i < $global_start; $i++)next($GLOBALS);
            for($i = 0; $i < $global_length; $i++){
                list($key, $val) = each($GLOBALS);
				echo "params += helpsave(\"$key\");\r\n";
            }
			?>
            var errors = lastcheck();
            if(errors == 0){
	            fw.getHTTP("<?php echo $basedir ?>ki_getsetsettings.php" + params, savedSettings, currentfile);
			} else {
            	alert("Error: All fields must be set for 'ki_setup.php'!");
            }
        }
    }
    
    function lastcheck(){
    	if(currentfile == "default"){
            var nodes = document.getElementsByTagName("input");
            for(var i = 0; i < nodes.length; i++){
                if(nodes[i].className == 'kis_param'){
                    if(nodes[i].value == "" && nodes[i].id != "kis_pw" && nodes[i].id != "kis_userpw")return 1; // check all fields, except pw
                }
            }
		}
        return 0;
    }
    
    function helpsave(param){
    	return $("kis_"+param).value != "" ? "&"+param+"=" + encodeURIComponent($("kis_"+param).value) : "";
    }
    
    function savedSettings(responseText, savedfile){
    	var dirname = savedfile;
    	if(savedfile == "default")
        	savedfile = "ki_setup.php";
		else
        	savedfile += "_ki_setup.php";
        if(responseText == 'adminchanged'){
        	alert("File '"+savedfile+"' modified. You changed also your login username and/or password. You will now be logged out. Please login with your new data.");
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
                } else {
                	if(newparams.length == 0)
                        newparams += "?";
                    else
                        newparams += "&";
                    newparams += "admin=" + $("kis_admin").value;
                }
            }
            location.href = "./" + newparams;
        } else if(responseText == 'modified'){
	    	alert("File '"+savedfile+"' modified.");
            kib.reloadcsandreinit();
		} else {
        	alert("ERROR: File '"+savedfile+"' could not be saved/modified.");
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
	
	function taste_kis(aEvent) {
		aEvent = aEvent ? aEvent : window.event;
        var keyCode = aEvent.keyCode;
		if(keyCode == 27){
			kis.closeSettings();
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
        	fw.move("kis_topdiv", 2, 0, 0);        
            return;
        }
        if(x > 120 || y > 180){
        	open = 0;
        	fw.move("authorization", 2, -42, 0);
        	fw.move("kis_topdiv", 2, -96, 0);
        }
    }    
}

