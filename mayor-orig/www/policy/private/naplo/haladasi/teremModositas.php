<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if ($ADAT['oraId']>0) putTeremModositas($ADAT);

?>
