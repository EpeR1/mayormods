<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (defined('__INTEZMENY') && __INTEZMENY != '') {
        putIntezmenyModositasForm($ADAT);
	putIntezmenyTorlesForm($ADAT);
    }
    putUjIntezmenyForm();

?>
