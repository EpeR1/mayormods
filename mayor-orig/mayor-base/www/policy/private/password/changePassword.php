<?php

    if (_RIGHTS_OK !== true) die();

    global $userAccount, $toPolicy;

    putChangePasswordForm($userAccount, $toPolicy, true); // rögzített policy

?>
