<?php
/*
    module:	naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $diakId, $osztalyId, $tanarId, $BEJEGYZESEK, $osztalyBejegyzesek, $Diakok;
    global $DIAKOK;

    if ($diakId != '') putBejegyzesLista($diakId, $BEJEGYZESEK);
    elseif (isset($osztalyId)) putOsztalyBejegyzesek($osztalyBejegyzesek, $Diakok);
    elseif (isset($tanarId)) putBejegyzesLista('',$BEJEGYZESEK, $DIAKOK);

?>
