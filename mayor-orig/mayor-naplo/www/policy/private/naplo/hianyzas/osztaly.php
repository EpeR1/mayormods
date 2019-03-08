<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $ADAT, $View;

    if (isset($ADAT['osztalyId']) && count($ADAT['stat']['névsor']) > 0)
	putOsztalyOsszesito($ADAT, $View);

?>
