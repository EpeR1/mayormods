<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    $userAccount = readVariable($_GET['userAccount'],'userAccount');
    $toPolicy = readVariable($_POST['toPolicy'], 'enum', 
		readVariable($_GET['toPolicy'], 'enum', _POLICY, $POLICIES),
		$POLICIES
    );


    if ($userAccount == '') $userAccount = _USERACCOUNT;

    // Milyen kategóriába sorolható a userAccount, illetve az aktuális user
    $userCategories = getAccountCategories($userAccount, $toPolicy);
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
    elseif ($userAccount == _USERACCOUNT) define('_ACCESS_AS', _SELF_ACCESS);
    else define('_ACCESS_AS', _OTHER_ACCESS); 

    list($backendAttrs, $backendAttrDef) = getBackendAttrs('Account', $toPolicy);
    if ($action == 'changeSettings') {

	changeAccountInfo($userAccount, $toPolicy);

    } elseif ($action=='userSettingsModify') {

	$changeSkinTo = readVariable($_POST['changeSkinTo'],'enum',null,$SKINSSHOW);
	setUserSettings($userAccount, $toPolicy, array('skin'=>$changeSkinTo));

    }



    $userInfo = getUserInfo($userAccount, $toPolicy);		// keretrendszer attribútumai
    $accountInfo = getAccountInfo($userAccount, $toPolicy);	// backend attribútumai

    $ADAT = getUserSettings($userAccount, $toPolicy);

?>
