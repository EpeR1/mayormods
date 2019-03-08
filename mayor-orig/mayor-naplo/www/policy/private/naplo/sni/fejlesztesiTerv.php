<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['diakId']) && isset($ADAT['dt'])) putHaviOsszesites($ADAT);

?>
