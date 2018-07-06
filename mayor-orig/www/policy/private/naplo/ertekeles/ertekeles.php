<?php

    if (_RIGHTS_OK !== true) die();

    global $kerdoivId, $ADAT;

    if (isset($kerdoivId) && isset($ADAT['cimzettId'])) putKerdoiv($ADAT);

?>
