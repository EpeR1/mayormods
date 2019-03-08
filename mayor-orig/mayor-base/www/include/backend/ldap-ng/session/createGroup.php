<?php
/*
    Modules: base/session
*/


    function ldapCreateGroup($groupCn, $groupDesc, $toPolicy = _POLICY, $SET = array()) {

        global $AUTH;
	$category = ekezettelen($SET['category']);

	// $toPolicy --> ldap backend - ellenőrzés!
	if ($AUTH[$toPolicy]['backend'] != 'ldap-ng') {
	    $_SESSION['alert'][] = 'page:wrong_backend:'.$AUTH[$toPolicy]['backend'];
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

	$info = $ginfo = Array();

	// cn ütközés ellenőrzése
	$filter = "(&(objectclass=".$AUTH[$toPolicy]['ldapGroupObjectClass'].")(cn=$groupCn))";
	$justthese = array('cn');
	$sr = ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
	$ginfo = ldap_get_entries($ds, $sr);
	$gCount = $ginfo['count'];
	ldap_free_result($sr);
	if ($gCount > 0) {
    	    $_SESSION['alert'][] = 'message:multi_uid:'.$groupCn;
    	    return false;
    	}

	// Az következő gidNumber megállapítása
	$filter = "(&(objectclass=".$AUTH[$toPolicy]['ldapGroupObjectClass'].")(gidNumber=*))";
	$justthese = array('gidNumber', 'msSFU30GidNumber');
	$sr = ldap_search($ds,$AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
	ldap_sort($ds, $sr, 'gidNumber');
	$ginfo = ldap_get_entries($ds, $sr);
	ldap_free_result($sr);
	if (isset($ginfo['count']) && $ginfo['count'] > 0) $info['gidNumber'] = array($ginfo[ $ginfo['count']-1 ]['gidnumber'][0]+1);
	else $info['gidNumber'] = array(1001);

	// A szokásos attribútumok
	$info['sAMAccountName'] = $info['cn'] = array($groupCn);
	$info['description'] = array($groupDesc);

	// A kategória függő attribútumok
	if (isset($SET['container'])) $dn = "CN=$groupCn,".$SET['container'];
	else $dn = "CN=$groupCn,OU=$category,".$AUTH[$toPolicy]['ldapBaseDn'];

	// objectum osztályok
	$info['objectClass'] = array($AUTH[$toPolicy]['ldapGroupObjectClass']);

	// csoport felvétel
	$_r1 = ldap_add($ds,$dn,$info);
    	if (!$_r1) {
       	    printf("LDAP-Error: %s<br>\n", ldap_error($ds));
	    var_dump($info);
    	}

	ldap_close($ds);

	$_SESSION['alert'][] = 'info:create_group_success:'.$dn;
	return true;

    }

?>
