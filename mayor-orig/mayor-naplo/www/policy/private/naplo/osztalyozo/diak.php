<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $diakId, $diakNev, $tolDt, $igDt, $Jegyek, $Targyak, $ADAT;

    if ($diakId != '' && is_array($Jegyek)) putTanuloJegyek($diakId, $diakNev, $Jegyek, $tolDt, $igDt, $ADAT);

?>
