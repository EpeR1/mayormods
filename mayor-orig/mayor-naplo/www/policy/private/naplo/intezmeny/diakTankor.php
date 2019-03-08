<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (__USERADMIN || __VEZETOSEG || __TANAR) {

	if (is_array($ADAT['osztalyok'])) putDiakTankorForm($ADAT);

    }

?>
