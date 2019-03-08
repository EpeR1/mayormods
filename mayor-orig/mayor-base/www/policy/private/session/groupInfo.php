<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    global $groupCn, $groupInfo, $backendAttrDef, $toPolicy;

    putGroupInfoForm($groupCn, $groupInfo, $backendAttrDef, $toPolicy);

?>
