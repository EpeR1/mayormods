<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT['hianyzas']) && count($ADAT['hianyzas'])>0) {
	putOsztalyHianyzas($ADAT);
    }
?>
