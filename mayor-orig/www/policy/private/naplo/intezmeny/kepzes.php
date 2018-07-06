<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT['kepzesAdat'])) {
	putKepzesForm($ADAT);
	kepzesElesForm($ADAT);
    }
    ujKepzesForm($ADAT);


?>
