<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT['szempontRendszer'])) {
	if (__MODOSITHAT || is_array($ADAT['szovegesErtekeles']))
	    putErtekeloForm($ADAT);
    } elseif (is_array($ADAT['osszes'])) {
	foreach ($ADAT['diakTargyak'] as $targyId => $tAdat)
	    if (is_array($ADAT['osszes'][$targyId]))
		if ($ADAT['tolDt'] <= $ADAT['osszes'][$targyId]['szovegesErtekeles']['dt'])  putErtekeloForm($ADAT['osszes'][$targyId]);
    }
?>
