<?php

    if (_RIGHTS_OK !== true) die();

    global $osztalyId, $Csoportok, $csoportId, $Tankorok, $csoportAdatok;

    if (is_array($Csoportok) && count($Csoportok) > 0) putCsoportLista($Csoportok, $osztalyId, $csoportId);
    if (isset($csoportId) && $csoportId != '') {
	putCsoportModositoForm($csoportAdatok, $Tankorok, $osztalyId);
	putCsoportTorlesForm($csoportId, $osztalyId);
    }
    putUjCsoportForm($osztalyId, $Tankorok);

?>
