<?php

    if (_RIGHTS_OK !== true) die();

    global $tools, $diakId, $tankorId, $tanarId, $osztaly;
    global $Diakok, $Jegyek, $Orak, $sulyozas, $nevsor;
    global $Dolgozatok, $tolDt, $igDt;
    global $ADAT; 

    if ($tankorId != '') {
        // tankör osztályzatainak kiírása
        putTankorJegyek($tankorId, $Diakok, $Jegyek, $Orak, $Dolgozatok, $sulyozas, $tolDt, $igDt, $nevsor, $ADAT);
    } else {
	putTankorjegyStatisztika($ADAT);
    }

?>
