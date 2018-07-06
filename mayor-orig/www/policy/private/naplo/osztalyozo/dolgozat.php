<?php

    if (_RIGHTS_OK !== true) die();

    global $tankorId, $diakId, $tanarId, $osztalyId, $Dolgozat, $dolgozatId, $valaszthatoTankorok, $Tanarok;
    global $ADAT;
    
    if ($dolgozatId != '') {
	putDolgozat($Dolgozat, $valaszthatoTankorok, $Tanarok, $ADAT);
    } elseif (is_array($Dolgozat)) {
	if (defined('__USERTANARID') && __USERTANARID == $tanarId && isset($tankorId))
	    putDolgozatBejelento($tankorId);
	putDolgozatLista($Dolgozat);
    }

?>
