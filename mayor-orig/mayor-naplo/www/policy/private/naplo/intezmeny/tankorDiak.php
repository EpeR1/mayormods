<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (__MODOSITHATO === true) {
	if (is_array($ADAT)) {
	    putTankorDiakForm($ADAT);
	    putUjDiakForm($ADAT);
	    putUjDiakForm2($ADAT);
	} 
    } elseif (is_numeric($ADAT['tankorId'])) {
	    putTankorDiakTablazat($ADAT);
    }

?>
