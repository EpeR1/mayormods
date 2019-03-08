<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT['orak']) && count($ADAT['orak']) > 0) putOralatogatasForm($ADAT);

?>
