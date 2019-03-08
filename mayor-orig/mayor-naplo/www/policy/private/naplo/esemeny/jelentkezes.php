<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT['esemenyIds'])) putJelentkezesForm($ADAT);
?>