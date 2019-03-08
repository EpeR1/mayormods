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
	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	$shadowLastChange = floor(time()/(60*60*24));

	$ds = ldap_connect($AUTH[$toPolicy]['ldap hostname']);
	if ($ds) {
		$b_ok = ldap_bind($ds,$userDn,$userPassword);
		if ($b_ok) {
			$info['userPassword'][0] = '{crypt}' . crypt($newPassword);
			// Ezekre nincs jogosultsága a felhasználónak, nem változnak:
			//         _SHADOWMIN, _SHADOWMAX, _SHADOWWARNING, _SHADOWINACTIVE
			$info['shadowlastchange'][0] = $shadowLastChange;
			if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
				$info['shadowexpire'][0] = $AUTH[$toPolicy]['shadowExpire'];
			} elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
				$info['shadowexpire'][0] = $shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
			}
			$r = ldap_mod_replace($ds,$userDn,$info);
			ldap_close($ds);
			if ($r) {
				$_SESSION['alert'][] = 'info:pw_change_success';
				return true;
			} else {
				$_SESSION['alert'][] = 'message:ldap_modify_failure';
				return false;
			}
		} else {
			$_SESSION['alert'][] = 'message:ldap_bind_failure:'.$userDn;
			ldap_close($ds);
			return false;
		}
	} else {
		$_SESSION['alert'][] = 'message:ldap_failure';
		return false;
	}

}

############################################################################
# Adminisztrátori jelszó változtatás
############################################################################

function changePassword($userAccount, $newPassword, $toPolicy = '') {

	global $AUTH;

	if ($toPolicy == '') $toPolicy = _POLICY;
	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	$shadowLastChange = floor(time()/(60*60*24));
	
	$ds = ldap_connect($AUTH[$toPolicy]['ldap hostname']);
	if ($ds) {
		$b_ok = ldap_bind($ds,_USERDN,_USERPASSWORD);
		if ($b_ok) {
			$info['userPassword'][0] = '{crypt}' . crypt($newPassword);
			// Ezekre nincs jogosultsága a felhasználónak, nem változnak:
			//         _SHADOWMIN, _SHADOWMAX, _SHADOWWARNING, _SHADOWINACTIVE
			$info['shadowlastchange'][0] = $shadowLastChange;
			if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
				$info['shadowexpire'][0] = $AUTH[$toPolicy]['shadowExpire'];
			} elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
				$info['shadowexpire'][0] =	$shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
			}
			$r = @ldap_mod_replace($ds,$userDn,$info);
			ldap_close($ds);
			if ($r) {
				$_SESSION['alert'][] = 'info:pw_change_success';
				return true;
			} else {
				$_SESSION['alert'][] = 'message:ldap_modify_failure';
				return false;
			}
		} else {
			$_SESSION['alert'][] = 'message:ldap_bind_failure:'._USERDN;
			ldap_close($ds);
			return false;
		}
	} else {
		$_SESSION['alert'][] = 'message:ldap_failure';
		return false;
	}

}

?>
