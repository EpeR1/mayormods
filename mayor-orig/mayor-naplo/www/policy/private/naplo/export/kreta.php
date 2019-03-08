<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    echo '<img src="skin/classic/module-naplo/img/io/kreta.svg" style="height:50px; padding:10px; display:table-cell; margin:auto;"/>';
    putKretaTanarExportForm($ADAT);
    putKretaTankorTanarExportForm($ADAT);

?>