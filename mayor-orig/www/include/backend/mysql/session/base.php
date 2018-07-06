<?php
/*
    Module:	base/session
    Backend:	mysql

    function mysqlMemberOf($userAccount, $groupCn, $toPolicy = _POLICY)
*/

    require_once('include/backend/mysql/base/attrs.php');


    function mysqlMemberOf($userAccount, $groupCn, $toPolicy = _POLICY) {

        global $AUTH;

	$modul = "$toPolicy auth";
        $lr = db_connect($modul, array('fv' => 'mysqlMemberOf'));
        if (!$lr) return _AUTH_FAILURE;

	// Az uid lekérdezése
	if (!defined(('__'.$toPolicy.'_UID')) || _USERACCOUNT != $userAccount) { // egy policy-hez csak egy uid tartozik
	    $q = "SELECT uid FROM accounts WHERE userAccount = '%s' AND policy = '%s'";
	    $v = array($userAccount, $toPolicy);
	    $uid = db_query($q, array('fv' => 'mysqlMemberOf', 'modul' => $modul, 'result' => 'value', 'values' => $v), $lr);
	    if ($uid === false) { 
		$_SESSION['alert'][] = 'message:no_account:'."$userAccount:$toPolicy";
		db_close($lr); return false;
	    }
	    if (!defined('__'.$toPolicy.'_UID')) define('__'.$toPolicy.'_UID',$uid);
	} else {
	    $uid=constant('__'.$toPolicy.'_UID');
	}

	// Az gid lekérdezése
	$q = "SELECT gid FROM groups WHERE groupCn = '%s' AND policy = '%s'";
	$v = array($groupCn, $toPolicy);
	$gid = db_query($q, array('fv' => 'mysqlMemberOf', 'modul' => $modul, 'result' => 'value', 'values' => $v), $lr);
	if ($gid === false) {
	    $_SESSION['alert'][] = 'message:no_group:'."$groupCn:$toPolicy";
	    db_close($lr); return false;
	}

	// Benne van-e a csoportban
	$q = "SELECT COUNT(*) FROM members WHERE uid = %u AND gid = %u";
	$v = array($uid, $gid);
	$num = db_query($q, array('fv' => 'mysqlMemberOf', 'modul' => $modul, 'result' => 'value', 'values' => $v), $lr);
	db_close($lr);
	return ($num > 0);

    }

?>
