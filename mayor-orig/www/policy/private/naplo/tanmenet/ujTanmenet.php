<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['targyId']) && isset($ADAT['evfolyamJel'])) putUjTanevForm($ADAT);
?>
