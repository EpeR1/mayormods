<?php
/*
    Module:	base/session
    Backend:	ldap

    function LDAPuserAccountToDn($userAccount = _USERACCOUNT, $toPolicy = _POLICY)
    function ldapMemberOf($userAccount, $group, $toPolicy = _POLICY)

*/

    require('include/backend/ldap/base/attrs.php');
    require('include/backend/ldap/base/str.php');

    ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);

    if ($AUTH[_POLICY]['backend'] == 'ldap') {
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
        $ds = @ldap_connect($AUTH[$toPolicy]['ldap hostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // Van-e adott azonosítójú felhasználó?
        $filter="(&(uid=$userAccount)(objectClass=posixAccount))";
        $justthese=array('cn');
        $sr = ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
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
        $ds = @ldap_connect($AUTH[$toPolicy]['ldap hostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // Van-e adott azonosítójú felhasználó?
        $filter="(&(cn=$groupCn)(objectClass=posixGroup))";
        $justthese=array('cn');
        $sr = ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
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

	global $AUTH, $LDAP2Mayor;

	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);
	if (in_array($group, $AUTH[$toPolicy]['categories'])) {
	    if (strpos($userDn, ',ou='.ekezettelen($group).',') !== false) return true;
# Ha nincs megfelelő ou-ban, akkor nézzük a csoport tagságot - így berakható időszakosan akárki pl a titkárság kategóriába...
#	    else return false;
	}

	if (substr($group,0,3) != 'cn=') {
	    $groupDn = LDAPgroupCnToDn(ekezettelen($group));
	    if (!$groupDn) return false;	// Ha nincs ilyen csoport az LDAP fában
	} else {
	    $groupDn = $group;
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

        $justthese = array('cn'); // valamit le kell kérdezni...
/*    	$filter =  "(&  (objectClass=mayorGroup)
                	(member=$userDn)
                    )";		    
*/
    	$filter =  "(&  (objectClass=posixGroup)
                	(memberUid=$userAccount)
                    )";		    
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

######################################################
# LDAPcreateContainer - tároló létrehozása
######################################################

    function LDAPcreateContainer($containerDn, $toPolicy) {

        global $AUTH;

	$pos = strpos($containerDn, ',ou=');
	$container = substr($containerDn, 3, $pos-3);
	$rdn = substr($containerDn, $pos+1);
	$cat = substr($containerDn, 3, strlen($containerDn)-4-strlen($AUTH[$toPolicy]['ldap base dn']));

        error_reporting(1);

        // Kapcsolódás a szerverhez
        $ds = @ldap_connect($AUTH[$toPolicy]['ldap hostname']);
        if (!$ds) {
            $_SESSION['alert'][] = 'alert:ldap_connect_failure';
            return false;
        }

        // Csatlakozás a szerverhez
        $r  = @ldap_bind($ds, _USERDN, _USERPASSWORD);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
            return false;
        }

        // OU létrehozása
        $info['ou'][0] = $container;
        $info['objectclass'][0] = 'organizationalUnit';
        $info['description'][0] = $container;

        $_r1 = ldap_add($ds, $containerDn, $info);
        if (!$_r1) {
//	    $_SESSION['alert'][] = 'message:ldap_add_failure:'.$containerDn;
	    return false;
//            printf("LDAP-Error: %s<br>\n", ldap_error($ds));
//            echo '<pre>'; var_dump($info); echo '</pre>';
        }

        // az OU-hoz tartozó csoportok OU-ja
        $info['ou'][0] = 'Groups';
        $info['objectclass'][0] = 'organizationalUnit';
        $info['description'][0] = "$container csoportjai";

        $containerDn = "ou=Groups,$containerDn";
        $_r1 = ldap_add($ds, $containerDn, $info);
        if (!$_r1) {
            printf("LDAP-Error: %s<br>\n", ldap_error($ds));
            echo '<pre>'; var_dump($info); echo '</pre>';
        }

        // Az osztály csoport létrehozása
	require_once('include/modules/session/createGroup.php');
        createGroup($container, "$container csoport", "$cat", $toPolicy);

        ldap_close($ds);

    }

?>
