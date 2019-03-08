<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    global $accountInfo, $userInfo, $backendAttrDef, $toPolicy;
    global $ADAT;

    putUserSettingsForm($ADAT);

    putEduroamForm($ADAT);

    putAccountActivityForm($ADAT);

    putAccountInfoForm($userInfo, $accountInfo, $backendAttrDef, $toPolicy);

?>
