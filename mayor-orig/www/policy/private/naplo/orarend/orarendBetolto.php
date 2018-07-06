<?php

    if (_RIGHTS_OK !== true) die();

    global $ORAK, $ADAT;

    if (is_array($ORAK)) putAdat($ORAK);

    if (isset($file)) {
    } else {
	putFileValaszto($ADAT);
    }

?>
