<?php

    if (_RIGHTS_OK !== true) die();
    global $ADAT;

    $ADAT['kulsosview'] = false;
    putTanarLista_large($ADAT);

    $ADAT['kulsosview'] = true;
    putTanarLista_large($ADAT);

?>
