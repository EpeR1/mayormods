<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    $toPolicy = readVariable($_POST['toPolicy'], 'enum', _POLICY, $POLICIES);

    define('__ADMIN', memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup']));
    define('__DIAKADMIN', memberOf(_USERACCOUNT, 'diakadmin'));

    // valójában így sem jó, mert a lekérdezett backend-től kellene függővé teni a keresés mezőket...
    if ($AUTH[_POLICY]['backend'] == 'ad') $searchAttrList = array('userCn', 'userAccount', 'uidNumber', 'studyId');
    else $searchAttrList = array('userCn', 'userAccount', 'studyId');

    if ($action == 'searchAccount') {
        $attr = readVariable($_POST['attr'], 'enum', 'userCn', $searchAttrList);
        $pattern = readVariable($_POST['pattern'], 'string');
        $searchResult = searchAccount($attr, $pattern, $searchAttrList, $toPolicy);
    } elseif ($action == 'deleteAccount' and __ADMIN === true) {
        $userAccount = readVariable($_POST['userAccount'], 'string');
	deleteAccount($userAccount, $toPolicy);
    } else {
	echo $action;
    }


?>
