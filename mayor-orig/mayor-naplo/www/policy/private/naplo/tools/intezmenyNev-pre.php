<?php

    /* Model */
    require_once('include/modules/naplo/share/intezmenyek.php');

    /* Controller */
    global $intezmeny;
    $intezmeny = getIntezmenyByRovidnev(__INTEZMENY);

?>