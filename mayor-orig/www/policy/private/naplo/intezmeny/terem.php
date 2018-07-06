<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['teremId'])) {
	putTeremForm($ADAT);
    } else {
	putTeremLista($ADAT);
//NEM TESZTELT!	putTeremForm($ADAT);
    }
?>
