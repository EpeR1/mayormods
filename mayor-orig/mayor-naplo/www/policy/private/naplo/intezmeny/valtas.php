<?php

    if (_RIGHTS_OK !== true) die();
    global $ADAT;

    if (__INTEZMENY=='kanizsay') putTanarLista_large($ADAT);
    else putTanarLista($ADAT);
?>
