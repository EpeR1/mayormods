<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    $toPolicy = readVariable($_POST['toPolicy'], 'enum', _POLICY, $POLICIES);

    define('__ADMIN', memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup']));
    define('__DIAKADMIN', memberOf(_USERACCOUNT, 'diakadmin'));

    $searchAttrs = array('groupCn', 'groupDesc');
    if ($action == 'searchGroup') {
        $attr 	 = readVariable($_POST['attr'], 'enum', 'groupCn', $searchAttrs);
        $pattern = readVariable($_POST['pattern'],'html');
        $searchResult = searchGroup($attr, $pattern, $searchAttrs, $toPolicy);
    } elseif (__ADMIN ===true && $action == 'deleteGroup') {
	$groupCn = readVariable($_POST['groupCn'], 'html'); // nem biztos hogy id - bizos nem id, hanem a csoport neve
	deleteGroup($groupCn, $toPolicy);
    } else {
	echo $action;
    }

?>
