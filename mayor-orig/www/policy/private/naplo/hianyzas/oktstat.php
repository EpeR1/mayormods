<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__NAPLOADMIN && !__DIAK && !__VEZETOSEG)                                                                                                                    
        $_SESSION['alert'][] = 'page:illegal_access';                                     

    global $ADAT;

    if (is_array($ADAT))    
	putOktoberiStatisztika($ADAT);

?>
