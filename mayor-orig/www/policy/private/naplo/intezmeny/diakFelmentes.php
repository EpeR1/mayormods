<?php

    global $ADAT;

    if ($ADAT['diakId']!='') {
	if (__NAPLOADMIN===true || __VEZETOSEG===true) {
	    putDiakFelmentesForm($ADAT);
	} else {
	    putDiakFelmentesAdatok($ADAT);
	}
    }

?>
