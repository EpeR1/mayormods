<?php
    global $ADAT;

    if ($skin != 'ajax') {
	putTovabbkepzesTerv($ADAT);
	putTovabbkepzesek($ADAT);
	if (__NAPLOADMIN || __VEZETOSEG) {
	    putTanarokTovabbkepzesAdatai($ADAT);
	    putUjTovabbkepzes($ADAT);
	}
    }
?>