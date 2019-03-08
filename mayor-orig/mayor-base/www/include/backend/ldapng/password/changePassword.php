<?php
/*

 Module:	base/password

 function changeMyPassword($userAccount, $userPassword, $newPassword, $verification)
	A függvény nem vizsgálja, hogy jogosultak vagyunk-e a jelszó megváltoztatására.
	Ennek eldöntése a függvényt hívó program feladata
*/

############################################################################
# Jelszó kódolása (az Active Directory ezt használja....)
############################################################################

function LDAPEncodePassword($password) {

	return mb_convert_encoding("\"".$password."\"", "UTF-16LE", "UTF-8");

}

############################################################################
# Saját jelszó megváltoztatása
############################################################################

/* *************************************************************************
 A leírások szerint a felhasználó maga is megváltoztathatja jelszavát.
 Ennek módja az unicodePw attribútum törlése (a régi jelszó értéke szerint),
 és felvétele új értékkel - mindenz elvileg egy lépésben.

 A PHP ldap_mod* függvények ezt az egy lépésben kétféle módosítást nem 
 támogatják. De a helyzet az, hogy a módosítás perl-ből és parancssorból
 sem működik...
************************************************************************* */

function changeMyPassword($userAccount, $userPassword, $newPassword, $toPolicy = '') {

	global $AUTH;

	if ($toPolicy == '') $toPolicy = $_REQUEST['toPolicy'];
	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	$shadowLastChange = floor(time()/(60*60*24));
	
	// Csatlakozzás az AD kiszolgálóhoz (SSL szükséges!)
	$ds = ldap_connect($AUTH[$toPolicy]['ldapHostname']);
	if (!$ds) {
		// nem sikerült csatlakozni
		$_SESSION['alert'][] = 'message:ldap_failure';
		return false;
	}

	// Az eredeti jelszó ellenőrzése - csatlakozással
	$b_ok = ldap_bind($ds,$userDn,$userPassword);
	if (!$b_ok) {
		// Talán a régi jelszót elgépelte, vagy le van tiltva...
		$_SESSION['alert'][] = 'message:ldap_bind_failure:'.$userDn.':changeMyPassword - hibás a régi jelszó?';
		ldap_close($ds);
		return false;
	}
	$salt = generateSalt(8);
        $info['userPassword'][0] = "{smd5}".base64_encode(md5($newPassword.$salt, true).$salt); // Az LDAP ezt majd még egyszer base64 encod-olja...
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
}

############################################################################
# Adminisztrátori jelszó változtatás
############################################################################

function generateSalt($len=8) {
// https://github.com/splitbrain/dokuwiki/blob/master/inc/PassHash.class.php
// Ez adja vissza a salt-ot (ha nincs benne sortörés...):
// echo e3NtZDV9U3lNbnNGQ05OUHV6L2J4dHovekpzVVpFUVZGQw== | base64 -d | sed s/{smd5}// | base64 -d | cut -f 15-
        $salt = '';
        //$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        //for($i=0;$i<$len;$i++) $salt .= $chars[mt_rand(0,61)];
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for($i=0;$i<$len;$i++) $salt .= $chars[mt_rand(0,25)];
        return $salt;
}

function changePassword($userAccount, $newPassword, $toPolicy = '') {

	global $AUTH;

	if ($toPolicy == '') $toPolicy = _POLICY;
	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	$shadowLastChange = floor(time()/(60*60*24));
	
	$ds = ldap_connect($AUTH[$toPolicy]['ldapHostname']);
	if ($ds) {
		$b_ok = ldap_bind($ds,_USERDN,_USERPASSWORD);
		if ($b_ok) {
			$salt = generateSalt(8);
			$info['userPassword'][0] = "{smd5}".base64_encode(md5($newPassword.$salt, true).$salt); // Az LDAP ezt majd még egyszer base64 encod-olja...
			// Ezekre nincs jogosultsága a felhasználónak, nem változnak:
			//         _SHADOWMIN, _SHADOWMAX, _SHADOWWARNING, _SHADOWINACTIVE
			$info['shadowlastchange'][0] = $shadowLastChange;
			if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
				$info['shadowexpire'][0] = $AUTH[$toPolicy]['shadowExpire'];
			} elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
				$info['shadowexpire'][0] = $shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
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

		/* *************** */
/*			$info['unicodePwd'][0] = LDAPEncodePassword($newPassword);
			// Ezekre nincs jogosultsága a felhasználónak, nem változnak:
			//         _SHADOWMIN, _SHADOWMAX, _SHADOWWARNING, _SHADOWINACTIVE
			$info['shadowLastChange'][0] = $shadowLastChange;
			if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
				$info['shadowExpire'][0] = $AUTH[$toPolicy]['shadowExpire'];
			} elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
				$info['shadowExpire'][0] = $shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
			}
			$r = @ldap_mod_replace($ds,$userDn,$info);
			ldap_close($ds);
			if ($r) {
				$_SESSION['alert'][] = 'info:pw_change_success';
				return true;
			} else {
				$_SESSION['alert'][] = 'message:ldap_modify_failure:changePassword';
				return false;
			}
*/
		} else {
			$_SESSION['alert'][] = 'message:ldap_bind_failure:'._USERDN.':changePassword';
			ldap_close($ds);
			return false;
		}
	} else {
		$_SESSION['alert'][] = 'message:ldap_failure';
		return false;
	}
}

?>
