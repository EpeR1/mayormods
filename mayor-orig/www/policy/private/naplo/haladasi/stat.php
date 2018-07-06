<?php

    if (_RIGHTS_OK !== true) die();

    global $Tankorok, $tankorIds, $tankorStat;

    if (is_array($tankorStat)) putTankorStat($tankorStat);

?>
