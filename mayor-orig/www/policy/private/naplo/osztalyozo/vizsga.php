<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['diakId']) && isset($ADAT['targyId']) && isset($ADAT['evfolyamJel']))
	putVizsgaJelentkezesForm($ADAT);
    if (is_array($ADAT['vizsga']) && count($ADAT['vizsga']) > 0) putVizsgaLista($ADAT);

?>
