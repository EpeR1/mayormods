<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putAddNodeForm($ADAT);
    putPrivilegesForm($ADAT);

?>