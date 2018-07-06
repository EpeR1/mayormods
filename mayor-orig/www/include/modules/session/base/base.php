<?php
/*
    Module:	base/session
*/

    // Attribútumok nyelvi konstansai

    if (file_exists('lang/'.$lang.'/share/session/attrs.php')) {
	require('lang/'.$lang.'/share/session/attrs.php');
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/share/session/attrs.php')) {
	require('lang/'._DEFAULT_LANG.'/share/session/attrs.php');
    }

    // Attribútum információk
    if (file_exists('include/share/session/attrs.php')) {
	require('include/share/session/attrs.php');
    }

?>
