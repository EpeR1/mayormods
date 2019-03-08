<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT,$action;

    if ($ADAT['forgotDisabled']===true) 
	putForgotDisabled();
    elseif (!is_array($ADAT['account']) && $action=='') {
	putForgotPasswordForm($ADAT);
	putForgotUserAccountForm($ADAT);
    } else {
	putForgotThankyou();
    }
    putBackToLogin($ADAT);
?>