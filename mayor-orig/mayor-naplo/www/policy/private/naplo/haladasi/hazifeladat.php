<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT,$skin;

    putHazifeladat($ADAT);
    if ($ADAT['title']!='') {
	if (isset($ADAT['osztalyId'])) putHetesForm($ADAT);
    }
?>
