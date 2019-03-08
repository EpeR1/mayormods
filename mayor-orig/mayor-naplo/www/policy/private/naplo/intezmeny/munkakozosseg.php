<?php

    if (_RIGHTS_OK !== true) die();

    global $targyId;
    global $TANAROK,$TANAROK_INMK;
    global $ADAT;

    if (__NAPLOADMIN) {
	if (isset($targyId) && $targyId!='') {
	    putTargyValtoztatForm($ADAT);
	    putTargyAtnevezes($ADAT);
	    putTargyMkValtas($ADAT);
	    putTargyTorolForm($targyId,$ADAT['mkId']);
	    putTargyBeolvasztasForm($ADAT);
	    putTargyTargyForm($ADAT);
	} else {
	    putUjMunkakozossegForm($TANAROK);
	    if (isset($ADAT['mkId']) && $ADAT['mkId']!='') {
		putMunkakozossegForm($ADAT['mkAdat'],$TANAROK,$TANAROK_INMK);
		putMunkakozossegTorolForm($ADAT['mkId']);
		putUjTargyForm($ADAT['mkId'], $ADAT);
	    }
	}
    }

?>
