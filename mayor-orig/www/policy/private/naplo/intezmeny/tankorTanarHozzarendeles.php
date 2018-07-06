<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putStatuszSor($ADAT);

    if (is_array($ADAT['tankorok']) && count($ADAT['tankorok'])>0) putTankorTanarMatrix($ADAT);
?>