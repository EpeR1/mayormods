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

	// A régi és új jelszavak átkódolása
	$newUnicodePwd = base64_encode(LDAPEncodePassword($newPassword));
	$oldUnicodePwd = base64_encode(LDAPEncodePassword($userPassword));
	// A php ldap_mod* függvényei nem tudnak egy lépésben többféle módosítást elküldeni
	// ezért a parancssoros ldapmodify-t kell meghívnunk...
	$ldif=<<<EOT
dn: $userDn
changetype: modify
delete: unicodePwd
unicodePwd:: $oldUnicodePwd
-
add: unicodePwd
unicodePwd:: $newUnicodePwd
-
EOT;
	$cmd = sprintf("/usr/bin/ldapmodify -H %s -D '%s' -x -w %s", $AUTH[$toPolicy]['ldapHostname'], $userDn, $userPassword);

	if (($fh = popen($cmd, 'w')) === false ) {
		// Nem sikerült megnyitni a csatornát - mikor is lehet ilyen? Ha nincs ldapmodify?
		$_SESSION['alert'][] = 'message:popen_failure';
		return false;
	}
	fwrite($fh, "$ldif\n");
	pclose($fh);

	// Sikeres volt-e a jelszóváltoztatás? Próbáljunk újra csatlakozni az új jelszóval!
        if (!@ldap_bind($ds, $userDn, $newPassword)) {
        	$_SESSION['alert'][] = 'message:bad_pw';
            	return false;
        }

	// Shadow attribútumok beállítása
	//   Ezekre nincs jogosultsága a felhasználónak, így csak AccountOperator-ként módosítható
	//   Ráadásul Windoes alatt változtatva a jelszót ezek nem változnak, így nem lehet számítani rájuk...
	if (isset($AUTH[$toPolicy]['ldapAccountOperatorUser'])) {
			    $shadowLastChange = floor(time()/(60*60*24));
			    $info['shadowLastChange'][0] = $shadowLastChange;
			    if (isset($AUTH[$toPolicy]['shadowExpire']) and $AUTH[$toPolicy]['shadowExpire'] != '') {
				$info['shadowExpire'][0] = $AUTH[$toPolicy]['shadowExpire'];
			    } elseif (isset($AUTH[$toPolicy]['shadowMax']) and $AUTH[$toPolicy]['shadowMax'] != '')  {
				$info['shadowExpire'][0] = $shadowLastChange + intval($AUTH[$toPolicy]['shadowMax']);
			    }

			    $b_ok = ldap_bind($ds,$AUTH[$toPolicy]['ldapAccountOperatorUser'],$AUTH[$toPolicy]['ldapAccountOperatorPw']);
			    if (!$b_ok) { $_SESSION['alert'][] = 'message:ldap_bind_failure'; return false; }
			    $r = @ldap_mod_replace($ds, $userDn, $info);
			    if (!$r) {
				$_SESSION['alert'][] = 'message:ldap_modify_failure:changeMyPassword';
				return false;
			    }
	}
	ldap_close($ds);
	$_SESSION['alert'][] = 'info:pw_change_success';
	return true;

}

############################################################################
# Adminisztrátori jelszó változtatás
############################################################################

function changePassword($userAccount, $newPassword, $toPolicy = '') {

	global $AUTH;

	if ($toPolicy == '') $toPolicy = _POLICY;
	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	$shadowLastChange = floor(time()/(60*60*24));
	
	$ds = ldap_connect($AUTH[$toPolicy]['ldapHostname']);
	if ($ds) {
		$b_ok = ldap_bind($ds,_USERDN,_USERPASSWORD);
		if ($b_ok) {
			$info['unicodePwd'][0] = LDAPEncodePassword($newPassword);
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
