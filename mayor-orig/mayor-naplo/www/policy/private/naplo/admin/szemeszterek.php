<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT)) {
	putSzemeszterAdat($ADAT);
	putUjIdoszak($ADAT['szemeszterAdat']['szemeszterId'], $ADAT['idoszakTipusok']);
    }
    

?>
