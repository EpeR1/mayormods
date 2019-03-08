<?php

    if (_RIGHTS_OK !== true) die();

    if (!__TANAR && !__NAPLOADMIN && !__DIAK)
        $_SESSION['alert'][] = 'page:illegal_access';


    global $ORAADAT,$ADAT;

    if (is_array($ADAT))    
	putHianyzok($ADAT);

?>
