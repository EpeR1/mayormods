<?php

    if (_RIGHTS_OK !== true) die();

    global $ok, $dt, $ora, $tanarId, $tankorId, $teremId, $Orak, $ADAT;

    if ($ok) putOraFelvetelForm($tankorId ,$tanarId, $dt, $ora, $teremId, $ADAT);

    if (is_array($Orak)) if (isset($tanarId)) putOrak($Orak, _TANAR, $ADAT);
    else { 
	putOrak($Orak, _OSZTALY, $ADAT);
    }
    if ($dt!='' && $ora!='') putLila($ADAT);

?>
