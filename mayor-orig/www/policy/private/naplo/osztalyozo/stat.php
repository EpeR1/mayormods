<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT, $osztalyId;

    if (isset($osztalyId) && count($ADAT['diakok'])>=1) {
	if (is_array($ADAT['diakok'])) {
	    putOsztalyBizonyitvany($ADAT);
	}
    } else {
	putIskolaStatisztika($ADAT);
    }
    
?>
