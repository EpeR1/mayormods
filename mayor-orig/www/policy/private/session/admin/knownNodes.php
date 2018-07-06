<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putGetNodeData();
    putKnownNodes($ADAT);

?>
