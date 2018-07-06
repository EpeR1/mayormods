<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    putOsztalyValaszto($ADAT);
    if (is_array($ADAT['tankorTanar']) && count($ADAT['tankorTanar']) > 0) 
	putDiakTanarLista($ADAT);

?>
