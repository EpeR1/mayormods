<?php
/*
    module:	naplo
    version:	3.1
*/

    if (_RIGHTS_OK !== true) die();

    global $ADAT,$skin;

    putHaladasiBejegyzesek($ADAT);
    if ($ADAT['title']!='') {
	if (isset($ADAT['osztalyId'])) putHetesForm($ADAT);
    }
?>
