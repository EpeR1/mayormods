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

    } elseif ($action=='tokenLogout') {

	revokeTokens();

    } elseif ($action=='userSettingsModify') {

	$changeSkinTo = readVariable($_POST['changeSkinTo'],'enum',null,$SKINSSHOW);
	setUserSettings($userAccount, $toPolicy, array('skin'=>$changeSkinTo));

    } elseif ($action=='generateEduroamId') {
	$eduroamDOMAIN = readVariable($_POST['eduroamDOMAIN'],'enum',null,$eduroamDOMAINS);
	$eduroamPASSWORD =  @exec('pwgen');
        if (__TANAR===true) {
            $eduroamAFFILIATION = 'faculty';
        } elseif (__DIAK===true) {
            $eduroamAFFILIATION = 'student';
        } else {
            $eduroamAFFILIATION = 'staff';
        }
	createEduroamSettings(array('userAccount'=>$userAccount,'policy'=> $toPolicy, 
	    'eduroamUID' => $userAccount,
	    'eduroamDOMAIN'=>$eduroamDOMAIN, 
	    'eduroamAFFILIATION'=>$eduroamAFFILIATION, 
	    'eduroamPASSWORD'=>$eduroamPASSWORD));
    } elseif ($action=='modoifyEduroamId') {
	
    }

    $userInfo = getUserInfo($userAccount, $toPolicy);		// keretrendszer attribútumai
    $accountInfo = getAccountInfo($userAccount, $toPolicy);	// backend attribútumai

    $ADAT = getUserSettings($userAccount, $toPolicy);
    $ADAT['activity'] = getMyActivity();

    if ($toPolicy=='private' && _POLICY ==='private') {
	//DISABLED $ADAT['eduroamAdat'] = getEduroamSettings($userAccount, $toPolicy, array());
    }
?>
