<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (!isset($ADAT['kerdoivId'])) putKerdoivForm($ADAT);
    else putCimzettForm($ADAT);

?>
