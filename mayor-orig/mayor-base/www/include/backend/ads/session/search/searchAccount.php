<?php
/*
    Module:	base/session
    Backend:	ads

    ! -- Csak publikus mezőkre lehet keresni! -- !
    function ADSSearch($attr, $pattern, $searchAttrs=array('cn'), $filter='(objectclass=*)')
    function adsSearchAccount($attr, $pattern, $searchAttrs = array('userCn'))
    function adsSearchGroup($attr, $pattern, $searchAttrs = array('groupCn, groupDesc'), $toPolicy = '') {

*/

######################################################
# Általános ADS kereső függvény
######################################################

    function ADSSearch($attr, $pattern, $searchAttrs=array('cn'), $filter='(objectclass=*)', $toPolicy = _POLICY) {

        global $AUTH;

        if ($pattern == '') {
            $_SESSION['alert'][] = 'message:empty_field';
	    return false;
        }

	// Kapcsolódás az ADS szerverhez
	$ds = @ldap_connect($AUTH[$toPolicy]['adsHostname']);
    	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return false;
    	}

    	// Csatlakozás a szerverhez
    	$r  = @ldap_bind($ds, BACKEND_CONNECT_DN,BACKEND_CONNECT_PASSWORD);

    	if (!$r) {
    	    $_SESSION['alert'][] = 'message:ldap_bind_failure:ADSSearch';
            ldap_close($ds);
    	    return false;
    	}

	// Keresés
	if (
	    strpos(kisbetus($attr),'number') !== false
	    && $attr != 'serialNumber'
	) $filter = "(&$filter($attr=$pattern))";
	else $filter = "(&$filter($attr=*$pattern*))";

        $filter = "(&$filter($attr=*$pattern*))";
        $sr = @ldap_search($ds, $AUTH[$toPolicy]['adsBaseDn'], $filter, $searchAttrs);
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
# adsSearchAccount - felhasználó kereső függvény
######################################################

    function adsSearchAccount($attr, $pattern, $searchAttrs = array('userCn'), $toPolicy = _POLICY) {

	global $accountAttrToADS;

	// A keresendő attribútum konvertálása ADS attribútummá
	if ($accountAttrToADS[ $attr ] != '') $attrADS = $accountAttrToADS[ $attr ];
	else $attrADS = $attr;
	if ($attrADS == 'dn') $attrADS = 'uid'; // dn-re nem megy a keresés!!

	// A lekérendő attribútumok konvertálása ADS attribútummá
	for ($i = 0; $i < count($searchAttrs); $i++) {
	    if ($accountAttrToADS[ $searchAttrs[$i] ] != '') $searchAttrsADS[$i] = $accountAttrToADS[ $searchAttrs[$i] ];
	    else $searchAttrsADS[$i] = $searchAttrs[$i];
	}
	$result = ADSSearch($attrADS, $pattern, $searchAttrsADS, '(&(objectclass=person)(!(objectclass=computer)))', $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // ADS schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		$result[$i]['dn'] = $return[$i]['userAccount'] = array('count' => 1, 0 => $result[$i]['dn']);
		for ($j = 0; $j < count($searchAttrs); $j++) {
		    $a = $searchAttrs[$j];
		    if (isset($result[$i][ kisbetus($accountAttrToADS[$a]) ])) {
			if ($accountAttrToADS[$a] != '') $return[$i][$a] = $result[$i][ kisbetus($accountAttrToADS[$a]) ];
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
# adsSearchGroup - csoport kereső függvény
######################################################

    function adsSearchGroup($attr, $pattern, $searchAttrs = array('groupCn, groupDesc'), $toPolicy = _POLICY) {

	global $groupAttrToADS;

	// A keresendő attribútum konvertálása ADS attribútummá
	if ($groupAttrToADS[ $attr ] != '') $attrADS = $groupAttrToADS[ $attr ];
	else $attrADS = $attr;
	if ($attrADS == 'dn') $attrADS = 'cn'; // dn-re nem megy a keresés!!

	// A lekérendő adtibútumok konvertálása ADS attribútummá
	for ($i = 0; $i < count($searchAttrs); $i++) {
	    if ($groupAttrToADS[ $searchAttrs[$i] ] != '') $searchAttrsADS[$i] = $groupAttrToADS[ $searchAttrs[$i] ];
	    else $searchAttrsADS[$i] = $searchAttrs[$i];
	}

	$result = ADSSearch($attrADS, $pattern, $searchAttrsADS, '(objectclass=group)', $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // ADS schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		$result[$i]['dn'] = $return[$i]['groupCn'] = array('count' => 1, 0 => $result[$i]['dn']);
		for ($j = 0; $j < count($searchAttrs); $j++) {
		    $a = $searchAttrs[$j];
		    if (!isset($groupAttrToADS[$a]) || $groupAttrToADS[$a] != '') {
			if (isset($result[$i][ $groupAttrToADS[$a] ])) $return[$i][$a] = $result[$i][ $groupAttrToADS[$a] ];
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
# adsDeleteAccount - account törlése
######################################################

    function adsDeleteAccount($userAccount, $toPolicy = _POLICY) {

	global $AUTH;

	// $toPolicy --> ads backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'ads') {
            $_SESSION['alert'][] = 'page:wrong_backend:ads!='.$AUTH[$toPolicy]['backend'];
            return false;
        }

	$userDn = ADSuserAccountToDn($userAccount, $toPolicy);
	if ($userDn === false) return false;

        // Kapcsolódás az ADS szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['adsHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds, BACKEND_CONNECT_DN,BACKEND_CONNECT_PASSWORD);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
            return false;
        }

        // Az uidNumber, a unixHomeDirectory lekerdezése
        $filter = "(&(objectclass=".$AUTH[$toPolicy]['adsUserObjectClass'].")(!(objectclass=computer)))";
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
# adsDeleteGroup - account törlése
######################################################

    function adsDeleteGroup($groupCn, $toPolicy = _POLICY) {

	global $AUTH;

	// $toPolicy --> ads backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'ads') {
            $_SESSION['alert'][] = 'page:wrong_backend:ads!='.$AUTH[$toPolicy]['backend'];
            return false;
        }

	$groupDn = ADSgroupCnToDn($groupCn, $toPolicy);
	if ($groupDn === false) return false;

        // Kapcsolódás az ADS szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['adsHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds, BACKEND_CONNECT_DN,BACKEND_CONNECT_PASSWORD);
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
