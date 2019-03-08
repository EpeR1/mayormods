<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putTanevIntezmenySelect($ADAT);
    if (is_array($ADAT['intezmeny']) && count($ADAT['intezmeny'])>0) putStat($ADAT);


?>