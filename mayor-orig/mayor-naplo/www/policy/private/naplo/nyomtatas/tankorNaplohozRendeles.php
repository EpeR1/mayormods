<?php

    if (_RIGHTS_OK !== true) die();

    global $osztalyId, $ADAT;

    if (isset($osztalyId)) {
	if (count($ADAT['osztalyTankorei']) > 0) putOsztalyTankorei($osztalyId, $ADAT);
    } else {
	putHozzarendelesekTorlese();
	putHianyzoTankorok($ADAT);
	putEloszlas($ADAT);
    }

?>
