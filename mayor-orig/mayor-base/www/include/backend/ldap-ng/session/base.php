<?php
/*
    Module:	base/session
    Backend:	ldap-ng

    function LDAPuserAccountToDn($userAccount = _USERACCOUNT, $toPolicy = _POLICY)
    function ldapMemberOf($userAccount, $group, $toPolicy = _POLICY)

*/

    require('include/backend/ldap-ng/base/attrs.php');

    ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option(NULL, LDAP_OPT_REFERRALS, 0);

    if ($AUTH[_POLICY]['backend'] == 'ldap-ng') {
	/* why not put into session cache */
	if ($AUTH[_POLICY]['cacheable']=='yes') {
	    $userDn = _queryCache('RDN',_POLICY,'value');
	}
	if (!isset($userDn)) $userDn = LDAPuserAccountToDn();
	define('_USERDN', $userDn);
	if ($AUTH[_POLICY]['cacheable']=='yes') _registerToCache('RDN',$userDn,_POLICY);
	unset($userDn);
    }

######################################################
# A _USERACCOUNT(uid)-hoz tartozó dn lekérdezése
######################################################

    function LDAPuserAccountToDn($userAccount = _USERACCOUNT, $toPolicy = _POLICY) {

	global $AUTH;

        // Kapcsolódás a szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldapHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds,$AUTH[$toPolicy]['ldapUser'],$AUTH[$toPolicy]['ldapPw']);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // Van-e adott azonosítójú felhasználó?
        $filter="(&(".$AUTH[$toPolicy]['ldapUserAccountAttr']."=$userAccount)(objectClass=".$AUTH[$toPolicy]['ldapUserObjectClass']."))";
        $justthese=array($AUTH[$toPolicy]['ldapCnAttr']);
        $sr = ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
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

    function LDAPgroupCnToDn($groupCn, $toPolicy = _POLICY) {

	global $AUTH;

        // Kapcsolódás a szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldapHostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds,$AUTH[$toPolicy]['ldapUser'],$AUTH[$toPolicy]['ldapPw']);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // Van-e ilyen csoport?
        $filter="(&(".$AUTH[$toPolicy]['ldapGroupCnAttr']."=$groupCn)(objectClass=".$AUTH[$toPolicy]['ldapGroupObjectClass']."))";
        $justthese=array($AUTH[$toPolicy]['ldapGroupCnAttr']);
        $sr = ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
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

    function ldapMemberOf($userAccount, $group, $toPolicy = _POLICY) {

	global $AUTH;

	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	/* Kis hack: csoport-tagság helyett vizsgáljuk előbb a megfelelő szervezeti egységet... de ezt nem biztos, hogy érdemes... */
	if (in_array($group, $AUTH[$toPolicy]['categories'])) {
	    if (strpos($userDn, ',ou='.ekezettelen($group).',') !== false) return true;
	}

	if (substr($group,0,3) != 'cn=') {
	    $groupDn = LDAPgroupCnToDn(ekezettelen($group));
	    if (!$groupDn) return false;	// Ha nincs ilyen csoport az LDAP fában
	} else {
	    $groupDn = $group;
	}

	// Kapcsolódás az LDAP szerverhez 
	$ds = @ldap_connect($AUTH[$toPolicy]['ldapHostname']);
    	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return false;
    	}

    	// Csatlakozás a szerverhez
        $r  = @ldap_bind($ds,$AUTH[$toPolicy]['ldapUser'],$AUTH[$toPolicy]['ldapPw']);
    	if (!$r) {
    	    $_SESSION['alert'][] = 'message:ldap_bind_failure';
            ldap_close($ds);
    	    return false;
    	}

        $justthese = array('cn'); // valamit le kell kérdezni...
    	$filter =  "(&(objectClass=".$AUTH[$toPolicy]['ldapGroupObjectClass'].")(member=$userDn))";		    
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
