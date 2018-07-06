<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putTargySzuro($ADAT);
    //if (isset($osztalyId)) 
    putUjTankorBlokk($ADAT);
    //if (count($Csoportok) > 0) putCsoportok($Csoportok, $tankorAdat, $szTankorIds, $osztalyId);
    putTankorBlokkok($ADAT);


?>
