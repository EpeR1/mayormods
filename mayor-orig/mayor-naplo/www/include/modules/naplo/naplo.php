<?php

//    require_once('include/share/net/ertesites.php');

    function getBeirasiAdatok() {
	if (defined('__USERTANARID') && is_numeric(__USERTANARID)) {
	    $q = "SELECT COUNT(*) FROM ora WHERE ki=".__USERTANARID." AND dt <= CURDATE() AND (leiras IS NULL OR leiras='')";
	    return db_query($q, array('fv' => 'getBeirasiAdatok', 'modul' => 'naplo', 'result' => 'value'));
	} else return 0;
    }

?>
