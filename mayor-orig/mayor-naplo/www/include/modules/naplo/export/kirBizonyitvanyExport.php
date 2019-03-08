<?php

    /* ez általános fv. Nem lenne jó mindenhol használni? */

    function nyomtatvanyKeszites($ADAT,$template) {


	// A sablonfile meghatározása
	define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
	if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$template.'.tmpl')) {
	    $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$template.'.tmpl';
	} elseif (file_exists(__TEMPLATE_DIR.'/default/'.$template.'.tmpl')) {
	    $templateFile = __TEMPLATE_DIR.'/default/'.$template.'.tmpl';
	} else {
	    $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$template.'.tmpl';
	    return false;
	}

	return template2file($templateFile, $ADAT);

    }


?>
