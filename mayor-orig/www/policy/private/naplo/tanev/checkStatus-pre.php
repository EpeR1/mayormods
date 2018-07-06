<?php
    if (_RIGHTS_OK !== true) die();                                                                                                                                             
    if (__NAPLOADMIN !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    $ADAT = checkStatus();

?>
