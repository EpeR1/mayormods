<?php

    if (_RIGHTS_OK !== true) die();

    global $tankorAdat, $Csoportok, $szTankorIds, $osztalyId,$ADAT;

    if (isset($osztalyId)) putUjTankorCsoport($tankorAdat, $szTankorIds, $osztalyId);
    if (count($Csoportok) > 0) putCsoportok($Csoportok, $tankorAdat, $szTankorIds, $osztalyId);
    if (count($szTankorIds) > 0) putTankorCsoportKereso($osztalyId);

    putTankorCsoportAdat($ADAT)
?>
