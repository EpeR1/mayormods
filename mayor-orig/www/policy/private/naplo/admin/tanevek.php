<?php

    if (_RIGHTS_OK !== true) die();

    global $Szemeszterek, $ADAT, $tanev, $aktivTanev;

    putUjTanevForm();
    putTanevSzemeszterekForm($Szemeszterek);
    if ($aktivTanev) putTanevLezarasForm($ADAT);
    else putTanevAktivalForm($tanev);
    

?>
