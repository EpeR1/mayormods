<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;
    global $tools,$tolDt,$igDt,$diakId,$igazolas;

    if (is_array($ADAT) && $diakId!='') putDiakHianyzasLista($ADAT);

?>
