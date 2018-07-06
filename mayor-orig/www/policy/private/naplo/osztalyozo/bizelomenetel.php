<?php

    if (_RIGHTS_OK !== true) die();

    global $diakId, $ADAT;

    if ($diakId != '') {
	    putDiakTanulmanyiElomenetel($diakId, $ADAT);
    }

?>
