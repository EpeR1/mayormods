<?php

    global $ADAT;
    if (__EMAIL_ENABLED===true && is_null($ADAT['futarEmail']['futar']) && _ALLOW_SUBSCRIBE===true) putHirnokFeliratkozas_user($ADAT['futarEmail']['naplo']);
    putHirnokFolyam($ADAT);

?>