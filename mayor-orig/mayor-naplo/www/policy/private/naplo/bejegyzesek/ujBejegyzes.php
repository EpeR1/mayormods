<?php
/*
    module:	naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $diakId, $osztalyId;

    if (is_numeric($diakId)) putUjBejegyzesForm($diakId);

?>
