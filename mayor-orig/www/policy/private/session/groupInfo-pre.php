<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();


    $toPolicy = readVariable($_POST['toPolicy'], 'enum',
                readVariable($_GET['toPolicy'], 'enum', _POLICY, $POLICIES),
                $POLICIES
    );
	    	    
    $groupCn = readVariable($_GET['groupCn'],'regreplace',null,array("[^a-zA-Z0-9\ \.\,_:;űáéúőóüöíŰÁÉÚŐÓÜÖÍ\-]"));

// egyelőre csak private-ból lehet valaki admin...
//    if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) define('_ACCESS_AS', _ADMIN_ACCESS);

    if (
        _POLICY == 'private' &&
        (
            memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup']) ||
            (
                memberOf(_USERACCOUNT,'diakadmin') &&
                in_array('diak',$userCategories)
            )
        )
    ) define('_ACCESS_AS', _ADMIN_ACCESS);
    else define('_ACCESS_AS', _OTHER_ACCESS); 

    list($backendAttrs,$backendAttrDef) = getBackendAttrs('Group', $toPolicy);

    if ($action == 'changeSettings') {

	changeGroupInfo($groupCn, $toPolicy);

    }
    $groupInfo = getGroupInfo($groupCn, $toPolicy);	// keretrendszer attribútumai
    							// backend attribútumai

    if ($groupInfo===false) $_SESSION['alert'][] = 'page::';

?>
