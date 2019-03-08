<?php

    if (_RIGHTS_OK !== true) die();

    global $diakId, $szemeszterId, $ADAT;

    if ($diakId != '') {
	if (isset($szemeszterId)) {
	    putDiakBizonyitvany($diakId, $ADAT);
	    if (__MODOSITHAT) putZaroJegyModosito($diakId, $ADAT);
	} else {
	    putDiakTanulmanyiOsszesito($diakId, $ADAT);
	}
    }

?>
