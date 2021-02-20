<?php
/*
    Module:	base/session
    Backend:	ads (for Active Directory)

    function ADSuserAccountToDn($userAccount = _USERACCOUNT, $toPolicy = _POLICY)
    function adsMemberOf($userAccount, $group, $toPolicy = _POLICY)

*/

    require('include/backend/ads/base/attrs.php');

    ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option(NULL, LDAP_OPT_REFERRALS, 0);

    if ($AUTH[_POLICY]['backend'] == 'ads') {
	/* why not put into session cache */
	if ($AUTH[_POLICY]['cacheable']=='yes') {
	    $userDn = _queryCache('RDN',_POLICY,'value');
	}
	if (!isset($userDn)) $userDn = ADSuserAccountToDn();
	define('_USERDN', $userDn); // --TODO DEPRECATED
	define('BACKEND_CONNECT_DN', $AUTH[_POLICY]['adsUser']);
	define('BACKEND_CONNECT_PASSWORD', $AUTH[_POLICY]['adsPw']);
	if ($AUTH[_POLICY]['cacheable']=='yes') _registerToCache('RDN',$userDn,_POLICY);
	unset($userDn);
    }

######################################################
# A _USERACCOUNT(uid)-hoz tartozó dn lekérdezése
######################################################

    function ADSuserAccountToDn($userAccount = _USERACCOUNT, $toPolicy = _POLICY) {

	global $AUTH;

        // Kapcsolódás a szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['adsHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds,$AUTH[$toPolicy]['adsUser'],$AUTH[$toPolicy]['adsPw']);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // Van-e adott azonosítójú felhasználó?
        $filter="(&(sAMAccountName=$userAccount)(objectClass=".$AUTH[$toPolicy]['adsUserObjectClass']."))";
        $justthese=array('cn','sn','givenName');
        $sr = ldap_search($ds, $AUTH[$toPolicy]['adsBaseDn'], $filter, $justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure";
            ldap_close($ds);
            return false;
        }
        $info=ldap_get_entries($ds,$sr);
        ldap_close($ds);

        if ( $info['count'] === 0 ) {
            // Nincs ilyen userAccount (uid)
            $_SESSION['alert'][] = "message:no_account:$userAccount";
            return false;
        } elseif ( $info['count'] > 1 ) {
            // Több ilyen uid is van
            $_SESSION['alert'][] = "message:multi_uid:$userAccount";
            return false;
        }

        if ($info['count']==1) { // Van - egy - ilyen felhasználó
            return $info[0]['dn'];
	}

    }


######################################################
# A groupCn(cn)-hez tartozó dn lekérdezése
######################################################

    function ADSgroupCnToDn($groupCn, $toPolicy = _POLICY) {

	global $AUTH;

        // Kapcsolódás a szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['adsHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds,$AUTH[$toPolicy]['adsUser'],$AUTH[$toPolicy]['adsPw']);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // Van-e ilyen csoport?
        $filter="(&(cn=$groupCn)(objectClass=".$AUTH[$toPolicy]['adsGroupObjectClass']."))";
        $justthese=array('cn');
        $sr = ldap_search($ds, $AUTH[$toPolicy]['adsBaseDn'], $filter, $justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure";
            ldap_close($ds);
            return false;
        }
        $info=ldap_get_entries($ds,$sr);
        ldap_close($ds);

        if ( $info['count'] === 0 ) {
            // Nincs ilyen groupCn (cn) - hibaüzenet csak akkor, ha nem kategóriáról van szó...
	    if (!in_array($groupCn, array_map('ekezettelen', $AUTH[$toPolicy]['categories']))) $_SESSION['alert'][] = "message:no_group:$groupCn";
            return false;
        } elseif ( $info['count'] > 1 ) {
            // Több ilyen cn is van
            $_SESSION['alert'][] = "message:multi_gid:$groupCn";
            return false;
        }

        if ($info['count']==1) { // Van - egy - ilyen csoport
            return $info[0]['dn'];
	}

    }

######################################################
# memberOf - csoport tag-e
######################################################

    function adsMemberOf($userAccount, $group, $toPolicy = _POLICY) {

	global $AUTH;
	//global $ADS2Mayor;

	$userDn = ADSuserAccountToDn($userAccount, $toPolicy);
	if (in_array($group, $AUTH[$toPolicy]['categories'])) {
	    if (strpos(kisbetus($userDn), ',ou='.ekezettelen($group).',') !== false) return true;
# Ha nincs megfelelő ou-ban, akkor nézzük a csoport tagságot - így berakható időszakosan akárki pl a titkárság kategóriába...
#	    else return false;
	}

	if (substr($group,0,3) != 'cn=') {
	    $groupDn = ADSgroupCnToDn(ekezettelen($group));
	    if (!$groupDn) return false;	// Ha nincs ilyen csoport az ADS fában
	} else {
	    $groupDn = $group;
	}

	// Kapcsolódás az ADS szerverhez 
	$ds = @ldap_connect($AUTH[$toPolicy]['adsHostname']);
    	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return false;
    	}

    	// Csatlakozás a szerverhez
        $r  = @ldap_bind($ds,$AUTH[$toPolicy]['adsUser'],$AUTH[$toPolicy]['adsPw']);
    	if (!$r) {
    	    $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
    	    return false;
    	}

        $justthese = array('cn'); // valamit le kell kérdezni...
    	$filter =  "(&(objectClass=".$AUTH[$toPolicy]['adsGroupObjectClass'].")(member=$userDn))";		    
    	$sr = @ldap_search($ds, $groupDn, $filter, $justthese);
    	if (!$sr) {
    	    $_SESSION['alert'][] = "message:ldap_search_failure:".$filter;
    	    ldap_close($ds);
    	    return false;
    	}

        $info = ldap_get_entries($ds, $sr);
        ldap_close($ds);

        if ($info['count'] > 0) {
            return true;
        } else {
            return false;
        }

    }

?>
