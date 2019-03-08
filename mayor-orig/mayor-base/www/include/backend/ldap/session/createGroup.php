<?php
/*
    Modules: base/session
*/

    function ldapCreateGroup($groupCn, $groupDesc, $toPolicy = _POLICY, $SET) {

        global $AUTH;
	$category = ekezettelen($SET['category']);

	// $toPolicy --> ldap backend - ellenőrzés!
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

	$info = $groupinfo = $oinfo = Array();

	// cn ütközés ellenőrzése
	$filter = "(&(objectclass=posixgroup)(cn=$groupCn))";
	$justthese = array('cn');
	$sr = ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
	$ginfo = ldap_get_entries($ds, $sr);
	$gCount = $ginfo['count'];
	ldap_free_result($sr);
	if ($gCount > 0) {
    	    $_SESSION['alert'][] = 'message:multi_uid:'.$groupCn;
    	    return false;
    	}

	// Az következő gidNumber megállapítása
	$filter = '(objectClass=mayorOrganization)';
	$justthese = array('nextgid', 'freegid');
	$sr = ldap_search($ds,$AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
	$ginfo = ldap_get_entries($ds,$sr);
	ldap_free_result($sr);
	if (isset($ginfo[0]['freegid']['count'])) $freeGidCount = $ginfo[0]['freegid']['count'];
	else $freeGidCount = 0;
	if ($freeGidCount == 0) {
    	    $info['gidnumber'] = array($ginfo[0]['nextgid'][0]);
    	    $oinfo['nextgid'] = $info['gidnumber'][0]+1;
	} else {
    	    $info['gidnumber'] = array($ginfo[0]['freegid'][$freeGidCount-1]);
    	    $oinfo['freegid'] = $ginfo[0]['freegid'][$freeGidCount-1];
	}

	// A szokásos attribútumok
	$info['cn'] = array($groupCn);
	$info['description'] = array($groupDesc);

	// A kategória függő attribútumok
        if (isset($SET['container'])) $dn = "cn=$groupCn,".$SET['container'];
        else $dn = "cn=$groupCn,ou=Groups,ou=$category,".$AUTH[$toPolicy]['ldap base dn'];

	// objectum osztályok
	$info['objectclass'] = array('posixGroup', 'mayorGroup');

	// Policy függő attribútumok - LDAP esetén pl a member kötelező
        if (is_array($SET['policyAttrs'])) foreach ($SET['policyAttrs'] as $attr => $value) $info[kisbetus($attr)] = $value;

	// csoport felvétel
	$_r1 = ldap_add($ds,$dn,$info);
    	if (!$_r1) {
       	    printf("LDAP-Error: %s<br>\n", ldap_error($ds));
	    echo $dn.'<hr>';
	    var_dump($info);
	    echo '<hr>';
	    var_dump($SET);
    	}

        // nextuid növelés
	if ($freeGidCount == 0) {
    	    $_r4 = ldap_mod_replace($ds,$AUTH[$toPolicy]['ldap base dn'],$oinfo);
	} else {
    	    $_r4 = ldap_mod_del($ds,$AUTH[$toPolicy]['ldap base dn'],$oinfo);
	}
//    	if (!$_r4) {
//      	printf("LDAP-Error: %s<br>\n", ldap_error($_r4));
//    	}

	ldap_close($ds);

	$_SESSION['alert'][] = 'info:create_group_success:'.$dn;
	return true;

    }

?>
