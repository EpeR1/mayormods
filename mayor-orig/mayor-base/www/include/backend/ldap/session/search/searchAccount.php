<?php
/*
    Module:	base/session
    Backend:	ldap

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
	$ds = @ldap_connect($AUTH[$toPolicy]['ldap hostname']);
    	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return false;
    	}

    	// Csatlakozás a szerverhez
    	$r  = @ldap_bind($ds);
    	if (!$r) {
    	    $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
    	    return false;
    	}

	// Keresés
        $filter = "(&$filter($attr=*$pattern*))";
        $sr = @ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $searchAttrs);
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

	$result = LDAPSearch($attrLDAP, $pattern, $searchAttrsLDAP, '(objectclass=posixaccount)', $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // LDAP schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		$result[$i]['dn'] = $return[$i]['userAccount'] = array('count' => 1, 0 => $result[$i]['dn']);
		for ($j = 0; $j < count($searchAttrs); $j++) {
		    $a = $searchAttrs[$j];
		    if (isset($result[$i][ $accountAttrToLDAP[$a] ])) {
			if ($accountAttrToLDAP[$a] != '') $return[$i][$a] = $result[$i][ $accountAttrToLDAP[$a] ];
			else $return[$i][$a] = $result[$i][$a];
		    } else {
			$return[$i][$a] = array('count' => 0)	;
		    }
		}
		$return[$i]['category'] = getAccountCategories($result[$i]['uid'][0], $toPolicy);
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

	$result = LDAPSearch($attrLDAP, $pattern, $searchAttrsLDAP, '(objectclass=posixgroup)', $toPolicy);
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

	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);

	// $toPolicy --> ldap backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'ldap') {
            $_SESSION['alert'][] = 'page:wrong_backend:'.$AUTH[$toPolicy]['backend'];
            return false;
        }

        // Kapcsolódás az LDAP szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldap hostname']);
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

        // Az uidNumber, a homeDirectory lekerdezése
        $filter = "(objectclass=posixAccount)";
        $justthese = array('uidNumber','homedirectory');
        $sr = @ldap_search($ds,$userDn,$filter,$justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure:".$userDn;
            ldap_close($ds);
            return false;
        }                                                                   ;

        $uidinfo = @ldap_get_entries($ds,$sr);
        $uidNumber = $uidinfo[0]['uidnumber'][0];
        if (isset($uidinfo[0]['homedirectory'][0])) $homeDirectory = $uidinfo[0]['homedirectory'][0];
	else $homeDirectory = '';
        $uid=$userAccount;

        // GroupDn, freeuid
        $groupDn = "cn=$uid,ou=Groups".strstr($userDn,',');
        $oinfo['freeuid'] = $uidNumber;

        // user törlése
        if (!@ldap_delete($ds,$userDn)) {
	    $_SESSION['alert'][] = 'message:ldap_delete_failure:user:'.$userAccount;
	}

        // freeuid felvétele
        if (!@ldap_mod_add($ds,$AUTH[$toPolicy]['ldap base dn'],$oinfo)) {
	    $_SESSION['alert'][] = 'message:ldap_modify_failure:freeuid:'.$oinfo['freeuid'];
	}

        // csoport törlése
        if (!@ldap_delete($ds,$groupDn)) {
	    $_SESSION['alert'][] = 'message:ldap_delete_failure:group:'.$groupDn;
	}

        // törlés a csoportból
        $filter = "(memberuid=$uid)";
        $justthese = array('cn','objectclass','member');
        $sr = @ldap_search($ds,$AUTH[$toPolicy]['ldap base dn'],$filter,$justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure:groups:".$userAccount;
            ldap_close($ds);
            return false;
        }                                                                   ;

        $groupinfo = ldap_get_entries($ds,$sr);

        for ($i = 0; $i < $groupinfo['count']; $i++) {
            $grpinfo = array('memberuid' => $uid);
            if (@in_array($userDn,$groupinfo[$i]['member'])) {
                $grpinfo['member']=$userDn;
            }
            if (!@ldap_mod_del($ds,$groupinfo[$i]['dn'],$grpinfo)) {
		$_SESSION['alert'][] = 'message:ldap_delete_failure:member:'.$groupinfo[$i]['dn'];
	    }
        }

        ldap_close($ds);

        $_SESSION['alert'][] = 'info:delete_uid_success:'.$userDn;
        return true;     

    }

######################################################
# ldapDeleteGroup - account törlése
######################################################

    function ldapDeleteGroup($groupCn, $toPolicy = _POLICY) {

	global $AUTH;

	$groupDn = LDAPgroupCnToDn($groupCn, $toPolicy);

	// $toPolicy --> ldap backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'ldap') {
            $_SESSION['alert'][] = 'page:wrong_backend:'.$AUTH[$toPolicy]['backend'];
            return false;
        }

        // Kapcsolódás az LDAP szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldap hostname']);
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

        // Az uidNumber, a homeDirectory lekerdezése
        $filter = '(objectclass=posixGroup)';
        $justthese = array('gidNumber');
        $sr = @ldap_search($ds, $groupDn, $filter, $justthese);
        if (!$sr) {
            $_SESSION['alert'][] = 'message:ldap_search_failure:'.$userDn;
            ldap_close($ds);
            return false;
        }                                                                   ;

	$gidinfo = ldap_get_entries($ds, $sr);
        $gidNumber = $gidinfo[0]['gidnumber'][0];

        // freeGid
        $oinfo['freegid'] = $gidNumber;

	if (!@ldap_delete($ds, $groupDn)) {
	    $_SESSION['alert'][] = 'message:ldap_delete_failure:group:'.$groupCn;
	}

        // freeuid felvétele
        if (!@ldap_mod_add($ds, $AUTH[$toPolicy]['ldap base dn'], $oinfo)) {
	    $_SESSION['alert'][] = 'message:ldap_modify_failure:freeGid:'.$oinfo['freegid'];
	}

        ldap_close($ds);

        $_SESSION['alert'][] = 'info:delete_group_success:'.$groupCn;
        return true;     

    }



?>
