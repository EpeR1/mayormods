<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['tankorId'])) putTankorFeljegyzesek($ADAT);
    elseif (isset($ADAT['diakId'])) putDiakHetiFeljegyzesek($ADAT);

?>
