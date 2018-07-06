<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['evfolyamJel']) 
//	&& (isset($ADAT['targyId']) || isset($ADAT['targyTipus']))
    ) {
	if (is_array($ADAT['szempontRendszer'])) putSzempontRendszer($ADAT);
	else putUjSzempontRendszerForm($ADAT);

	if (is_array($ADAT['szempontRendszerek'])) putSzempontRendszerLista($ADAT);
    }

?>
