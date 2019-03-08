<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['zaradekIndex'])) {
	putZaradekForm($ADAT);
    } elseif (is_array($ADAT['diakZaradekok']) && count($ADAT['diakZaradekok']) > 0) {
	putDiakZaradekok($ADAT);
    }
