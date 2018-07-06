<?php

    function nyomtatvanyKeszites($ADAT) {


	// A sablonfile meghatározása
	define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
	if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/diakAdatlap.tmpl')) {
	    $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/diakAdatlap.tmpl';
	} elseif (file_exists(__TEMPLATE_DIR.'/default/diakAdatlap.tmpl')) {
	    $templateFile = __TEMPLATE_DIR.'/default/diakAdatlap.tmpl';
	} else {
	    $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/szovegesErtekeles.tmpl';
	    return false;
	}

	return template2file($templateFile, $ADAT);

    }


?>
