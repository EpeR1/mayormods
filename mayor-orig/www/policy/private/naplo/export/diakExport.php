<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putDiakExportForm($ADAT);
    if (is_array($ADAT['export']) && count($ADAT['export']) > 0) {
	putDiakTabla($ADAT);
    }

?>
