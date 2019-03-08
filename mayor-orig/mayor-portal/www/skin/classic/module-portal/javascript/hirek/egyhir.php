<?php

    if ($_GET['lang'] != '') $lang = substr($_GET['lang'],0,2);
    else $lang = 'hu';

    if ($lang == 'jp') $lang = 'ja';

    echo '
	tinyMCE.init({
    	    mode : "specific_textareas",
	    editor_selector : "wysiwyg",
	    theme : "advanced",
	    language : "'.$lang.'",
	    plugins : "table,advimage,style,visualchars",
	    entity_encoding : "raw",
	    inline_styles : true,
	    theme_advanced_buttons1_add : "fontsizeselect,fontselect",
	    theme_advanced_buttons2_add : "separator,forecolor,backcolor,visualchars",
	    theme_advanced_buttons3_add_before : "tablecontrols,separator,styleprops"
	});
    ';

?>