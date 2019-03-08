<?php

    if (_RIGHTS_OK !== true) die();

    $_SESSION['alert'][] = 'page:insufficient_access';
    
?>
