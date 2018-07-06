<?php
/*
    Module:	base/session
    Backend:	ldap-ng

    ! -- Csak publikus mezőkre lehet keresni! -- !
    function LDAPSearch($attr, $pattern, $searchAttrs=array('cn'), $filter='(objectclass=*)')
    function ldapSearchAccount($attr, $pattern, $searchAttrs = array('userCn'))
    function ldapSearchGroup($attr, $pattern, $searchAttrs = array('groupCn, groupDesc'), $toPolicy = '') {

*/

######################################################
# Általános LDAP kereső függvény
######################################################

    function LDAPSearch($attr, $pattern, $searchAttrs=array('cn'), $filter='(objectclass=*)', $toPolicy = _POLICY) {

        global $AUTH;

        if ($pattern == '') {
            $_SESSION['alert'][] = 'message:empty_field';
	    return false;
        }

	// Kapcsolódás az LDAP szerverhez
	$ds = @ldap_connect($AUTH[$toPolicy]['ldapHostname']);
    	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return false;
    	}

    	// Csatlakozás a szerverhez
    	$r  = @ldap_bind($ds, _USERDN, _USERPASSWORD);

    	if (!$r) {
    	    $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
    	    return false;
    	}

	// Keresés
        $filter = "(&$filter($attr=*$pattern*))";
        $sr = @ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $searchAttrs);
    	if (!$sr) {
    	    $_SESSION['alert'][] = "message:ldap_search_failure:".$filter;
    	    ldap_close($ds);
    	    return false;
    	}

        $info = @ldap_get_entries($ds,$sr);
        ldap_close($ds);

    	return $info;

    }

######################################################
# ldapSearchAccount - felhasználó kereső függvény
######################################################

    function ldapSearchAccount($attr, $pattern, $searchAttrs = array('userCn'), $toPolicy = _POLICY) {

	global $accountAttrToLDAP;

	// A keresendő attribútum konvertálása LDAP attribútummá
	if ($accountAttrToLDAP[ $attr ] != '') $attrLDAP = $accountAttrToLDAP[ $attr ];
	else $attrLDAP = $attr;
	if ($attrLDAP == 'dn') $attrLDAP = 'uid'; // dn-re nem megy a keresés!!

	// A lekérendő attribútumok konvertálása LDAP attribútummá
	for ($i = 0; $i < count($searchAttrs); $i++) {
	    if ($accountAttrToLDAP[ $searchAttrs[$i] ] != '') $searchAttrsLDAP[$i] = $accountAttrToLDAP[ $searchAttrs[$i] ];
	    else $searchAttrsLDAP[$i] = $searchAttrs[$i];
	}
	$result = LDAPSearch($attrLDAP, $pattern, $searchAttrsLDAP, '(&(objectclass=person)(!(objectclass=computer)))', $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // LDAP schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		$result[$i]['dn'] = $return[$i]['userAccount'] = array('count' => 1, 0 => $result[$i]['dn']);
		for ($j = 0; $j < count($searchAttrs); $j++) {
		    $a = $searchAttrs[$j];
		    if (isset($result[$i][ kisbetus($accountAttrToLDAP[$a]) ])) {
			if ($accountAttrToLDAP[$a] != '') $return[$i][$a] = $result[$i][ kisbetus($accountAttrToLDAP[$a]) ];
			else $return[$i][$a] = $result[$i][$a];
		    } else {
			$return[$i][$a] = array('count' => 0)	;
		    }
		}
		$return[$i]['category'] = getAccountCategories($return[$i]['userAccount'][0], $toPolicy);
		$return[$i]['category']['count'] = count($return[$i]['category']);
	    }
	    $return['count'] = $result['count'];

	    return $return;

	}

    }

######################################################
# ldapSearchGroup - csoport kereső függvény
######################################################

    function ldapSearchGroup($attr, $pattern, $searchAttrs = array('groupCn, groupDesc'), $toPolicy = _POLICY) {

	global $groupAttrToLDAP;

	// A keresendő attribútum konvertálása LDAP attribútummá
	if ($groupAttrToLDAP[ $attr ] != '') $attrLDAP = $groupAttrToLDAP[ $attr ];
	else $attrLDAP = $attr;
	if ($attrLDAP == 'dn') $attrLDAP = 'cn'; // dn-re nem megy a keresés!!

	// A lekérendő adtibútumok konvertálása LDAP attribútummá
	for ($i = 0; $i < count($searchAttrs); $i++) {
	    if ($groupAttrToLDAP[ $searchAttrs[$i] ] != '') $searchAttrsLDAP[$i] = $groupAttrToLDAP[ $searchAttrs[$i] ];
	    else $searchAttrsLDAP[$i] = $searchAttrs[$i];
	}

	$result = LDAPSearch($attrLDAP, $pattern, $searchAttrsLDAP, '(objectclass=group)', $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // LDAP schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		$result[$i]['dn'] = $return[$i]['groupCn'] = array('count' => 1, 0 => $result[$i]['dn']);
		for ($j = 0; $j < count($searchAttrs); $j++) {
		    $a = $searchAttrs[$j];
		    if (!isset($groupAttrToLDAP[$a]) || $groupAttrToLDAP[$a] != '') {
			if (isset($result[$i][ $groupAttrToLDAP[$a] ])) $return[$i][$a] = $result[$i][ $groupAttrToLDAP[$a] ];
			else $return[$i][$a] = '';
		    } else {
			$return[$i][$a] = $result[$i][$a];
		    }
		}
	    }
	    $return['count'] = $result['count'];

	    return $return;

	}

    }

######################################################
# ldapDeleteAccount - account törlése
######################################################

    function ldapDeleteAccount($userAccount, $toPolicy = _POLICY) {

	global $AUTH;

	// $toPolicy --> ldap-ng backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'ldap-ng') {
            $_SESSION['alert'][] = 'page:wrong_backend:ldap-ng!='.$AUTH[$toPolicy]['backend'];
            return false;
        }

	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	if ($userDn === false) return false;

        // Kapcsolódás az LDAP szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldapHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds, _USERDN, _USERPASSWORD);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
            return false;
        }

        // Az uidNumber, a unixHomeDirectory lekerdezése
        $filter = "(&(objectclass=".$AUTH[$toPolicy]['ldapUserObjectClass'].")(!(objectclass=computer)))";
        $justthese = array('uidNumber','unixHomedirectory');
        $sr = @ldap_search($ds,$userDn,$filter,$justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure:".$userDn;
            ldap_close($ds);
            return false;
        }                                                                   ;

        $info = @ldap_get_entries($ds,$sr);
        $uidNumber = $info[0]['uidnumber'][0];
        $homeDirectory = $info[0]['unixhomedirectory'][0];
        $uid=$userAccount;

        // user törlése
        if (!@ldap_delete($ds,$userDn)) {
	    $_SESSION['alert'][] = 'message:ldap_delete_failure:user:'.$userAccount;
	}

        ldap_close($ds);

	/*
	    Ha van megadva deleteAccountScript paraméter, akkor abba bejegyzi a törölt felhasználó adatait.
	    A meghívott deleteAccount.sh nincs definiálva, testreszabható, megkötés egyedül a paraméter
	    lista: userAccount, uidNumber, homeDirectory
	*/
        if (defined('_DATADIR')
            && isset($AUTH[$toPolicy]['deleteAccountScript'])
            && file_exists(_DATADIR)
        ) {
            $sfp = fopen(_DATADIR.'/'.$AUTH[$toPolicy]['deleteAccountScript'],'a+');
            if ($sfp) {
                fwrite($sfp,"\n# $userAccount törlése: userAccount uidNumber homeDirectory\n");
                fwrite($sfp,"deleteAccount.sh '$userAccount' '$uidNumber' '$homeDirectory'\n");
                fclose($sfp);
            }
        }

        $_SESSION['alert'][] = 'info:delete_uid_success:'.$userDn;
        return true;     

    }

######################################################
# ldapDeleteGroup - account törlése
######################################################

    function ldapDeleteGroup($groupCn, $toPolicy = _POLICY) {

	global $AUTH;

	// $toPolicy --> ldap-ng backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'ldap-ng') {
            $_SESSION['alert'][] = 'page:wrong_backend:ldap-ng!='.$AUTH[$toPolicy]['backend'];
            return false;
        }

	$groupDn = LDAPgroupCnToDn($groupCn, $toPolicy);
	if ($groupDn === false) return false;

        // Kapcsolódás az LDAP szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldapHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds, _USERDN, _USERPASSWORD);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
            return false;
        }

	if (!@ldap_delete($ds, $groupDn)) {
	    $_SESSION['alert'][] = 'message:ldap_delete_failure:group:'.$groupCn;
	}

        ldap_close($ds);

        $_SESSION['alert'][] = 'info:delete_group_success:'.$groupCn;
        return true;     

    }


?>
