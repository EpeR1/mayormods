<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $id, $jegyId, $jegy, $Orak, $Dolgozatok, $ADAT;

    if (isset($jegyId)) putJegyInfo($jegy, $Orak, $Dolgozatok, $ADAT);

?>
