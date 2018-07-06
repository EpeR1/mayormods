<?php

    if (_RIGHTS_OK !== true) die();

    global $ok, $dt, $ora, $tanarId, $tankorId, $teremId, $Orak;
    global $ADAT;

    if ($ok) putOraFelvetelForm($ADAT);

    if (is_array($Orak)) if (isset($tanarId)) putOrak($Orak, _TANAR);
    else putOrak($Orak, _OSZTALY);

?>
