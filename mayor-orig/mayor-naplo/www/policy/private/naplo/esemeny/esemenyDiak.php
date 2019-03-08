<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if ($ADAT['esemenyId'] != '') esemenyNevsor($ADAT);
?>