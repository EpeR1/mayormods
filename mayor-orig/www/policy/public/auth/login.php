<?php

    if (_RIGHTS_OK !== true) die();

    global $userAccount, $userPassword, $toPolicy, $toPSF, $toSkin, $salt;
    global $ADAT;

    if (defined('_USERACCOUNT') && (!is_string(_USERACCOUNT) || _USERACCOUNT=='') )
	putLoginForm($userAccount, $toPolicy, $toPSF, $toSkin, $salt);
    else
	putAlreadyLoggedIn();

    if ($_SESSION['authStatus'] == _AUTH_FAILURE) {
	putElfelejtettJelszoForm($ADAT);
    }

?>
