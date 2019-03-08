<?php
    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN || __VEZETOSEG) {

	global $mkId,$targyId,$tankorId, $tanev;
	global $MKADAT,$TARGYADAT,$TANAROK,$OSZTALYOK,$SZEMESZTEREK,$TOPOST;
	global $ADAT;

	if (isset($tankorId) && $tankorId!='' && $ADAT['tanev']==__TANEV) {
	    putTankornevForm($ADAT);
	}
	putUjTankorForm($MKADAT,$TARGYADAT,$TANAROK,$OSZTALYOK,$SZEMESZTEREK,$TOPOST,$ADAT);
	if (isset($tankorId) && $tankorId!='') {
	    putTankorTargyForm($ADAT);
	    putTankorLezarForm($tankorId, $mkId, $targyId, $tanev);
	    putTankorTorolForm($tankorId, $mkId, $targyId, $tanev);

	}
    }
?>
