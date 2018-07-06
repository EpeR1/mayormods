<?php
/*
 Module:	base/password

 function changeMyPassword($userAccount, $userPassword, $newPassword, $verification)
	A függvény nem vizsgálja, hogy jogosultak vagyunk-e a jelszó megváltoztatására.
	Ennek eldöntése a függvényt hívó program feladata
	*/

############################################################################
# Saját jelszó megváltoztatása
############################################################################

function changeMyPassword($userAccount, $userPassword, $newPassword, $toPolicy = '') {

	global $AUTH;

	if ($toPolicy == '') $toPolicy = $_REQUEST['toPolicy'];
	$shadowLastChange = floor(time()/(60*60*24));

	$modul = "$toPolicy auth";
	$lr = db_connect($modul, array('fv' => 'changeMyPassword'));

	if (!$lr) return false;

	// Stimmel-e az azonosító/jelszó/policy hármas
	$q = "SELECT COUNT(*) FROM accounts WHERE userAccount='%s' AND userPassword=sha('%s') AND policy='%s'";
	$num = db_query($q, array('fv' => 'changeMyPassword', 'modul' => $modul, 'result' => 'value', 'values' => array($userAccount, $userPassword, $toPolicy)), $lr);
	if ($num != 1) {
	    $_SESSION['alert'][] = 'message:bad_pw:changeMyPassword';
	    db_close($lr);
	    return false;
	}
		
	if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
	    $shadowExpire = $AUTH[$toPolicy]['shadowExpire'];
	} elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
	    $shadowExpire =	$shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
	}
	$q = "UPDATE accounts SET userPassword=sha('%s'), shadowLastChange=%u, shadowExpire=%u
			WHERE userAccount='%s' and policy='%s'";
	$v = array($newPassword, $shadowLastChange, $shadowExpire, $userAccount, $toPolicy);
	$r = db_query($q, array('fv' => 'changeMyPassword', 'modul' => $modul, 'values' => $v), $lr);
	db_close($lr);
	if ($r) $_SESSION['alert'][] = 'info:pw_change_success';
	return $r;

}

############################################################################
# Adminisztrátori jelszó változtatás
############################################################################

function changePassword($userAccount, $newPassword, $toPolicy = '') {

	global $AUTH;

	if ($toPolicy == '') $toPolicy = _POLICY;
	$shadowLastChange = floor(time()/(60*60*24));
	if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
		$shadowExpire = $AUTH[$toPolicy]['shadowExpire'];
	} elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
		$shadowExpire =	$shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
	}
	$shadowExpire = intval($shadowExpire);
	$q = "UPDATE accounts SET userPassword=sha('%s'), shadowLastChange=%u, shadowExpire=%u
			WHERE userAccount='%s' and policy='%s'";
	$v = array($newPassword, $shadowLastChange, $shadowExpire, $userAccount, $toPolicy);
	$r = db_query($q, array('fv' => 'changePassword', 'modul' => "$toPolicy auth", 'values' => $v));
	if ($r) $_SESSION['alert'][] = 'info:pw_change_success';
	return $r;

}

?>
