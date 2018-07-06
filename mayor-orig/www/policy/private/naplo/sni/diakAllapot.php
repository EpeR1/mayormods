<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['diakId'])) putDiakAllapot($ADAT);
?>
