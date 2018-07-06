<?php
/*
    Modules: base/session

    UNTESTED!!!!
*/

    function ldapCreateAccount(
	$userCn, $userAccount, $userPassword, $toPolicy, $SET
    ) {

        global $AUTH;

	$category = ekezettelen($SET['category']);
	$shadowLastChange = floor(time() / (60*60*24));

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

	// uid ütközés ellenőrzése
	$filter = "(uid=$userAccount)";
	$justthese = array('uid');
	$sr = ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
	$uinfo = ldap_get_entries($ds, $sr);
	$uidCount = $uinfo['count'];
	ldap_free_result($sr);
	if ($uidCount > 0) {
    	    $_SESSION['alert'][] = 'message:multi_uid:'.$userAccount;
    	    return false;
    	}

	// Az következő uidNumber megállapítása
	$filter = '(objectClass=mayorOrganization)';
	$justthese = array('nextuid', 'freeuid');
	$sr = ldap_search($ds,$AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
	$uidinfo = ldap_get_entries($ds,$sr);
	ldap_free_result($sr);
	if (isset($uidinfo[0]['freeuid']['count'])) $freeUidCount = $uidinfo[0]['freeuid']['count'];
	else $freeUidCount = 0;
	if ($freeUidCount == 0) {
    	    $info['uidnumber'] = array($uidinfo[0]['nextuid'][0]);
    	    $info['gidnumber'] = $info['uidnumber'];
    	    $oinfo['nextuid'] = $info['uidnumber'][0]+1;
	} else {
    	    $info['uidnumber'] = array($uidinfo[0]['freeuid'][$freeUidCount-1]);
    	    $info['gidnumber'] = $info['uidnumber'];
    	    $oinfo['freeuid'] = $uidinfo[0]['freeuid'][$freeUidCount-1];
	}

	// shadow attributumok...
	// A shadowLastChange a mai nap // if (isset($AUTH[$toPolicy]['shadowlastchange']) && $AUTH[$toPolicy]['shadowlastchange'] != '') 
	$info['shadowlastchange'] = $shadowLastChange; 
	if (isset($AUTH[$toPolicy]['shadowmin']) && $AUTH[$toPolicy]['shadowmin'] != '') $info['shadowmin'] = $AUTH[$toPolicy]['shadowmin'];
	if (isset($AUTH[$toPolicy]['shadowmax']) && $AUTH[$toPolicy]['shadowmax'] != '') $info['shadowmax'] = $AUTH[$toPolicy]['shadowmax'];
	if (isset($AUTH[$toPolicy]['shadowwarning']) && $AUTH[$toPolicy]['shadowwarning'] != '') $info['shadowwarning'] = $AUTH[$toPolicy]['shadowwarning'];
	if (isset($AUTH[$toPolicy]['shadowinactive']) && $AUTH[$toPolicy]['shadowinactive'] != '') $info['shadowinactive'] = $AUTH[$toPolicy]['shadowinactive'];
	if (isset($AUTH[$toPolicy]['shadowexpire']) && $AUTH[$toPolicy]['shadowexpire'] != '') $info['shadowexpire'] = $AUTH[$toPolicy]['shadowexpire'];

	// A szokásos attribútumok
	$info['uid'] = array($userAccount);
	$info['cn'] = array($userCn);
	$info['sn'] = array('-');
	$info['userpassword'] = array('{crypt}' . crypt($userPassword));
	if (is_array($SET['policyAttrs'])) foreach ($SET['policyAttrs'] as $attr => $value) $info[kisbetus($attr)] = $value;
	if (($pos = strpos($category,',')) !== false)
	    $info['homedirectory'] = "/home/diak/".substr($category,0,$pos)."/$userAccount";
	else
	    $info['homedirectory'] = "/home/$category/$userAccount";

	// A kategória függő attribútumok
        if (isset($SET['container']) && $SET['container'] != '') {
	    $dn = "uid=$userAccount,".$SET['container'];
    	    $group = "cn=$userAccount,ou=Groups,".$SET['container'];
	    $ouDn = $SET['container'];
	} else {
	    $dn = "uid=$userAccount,ou=".$category.','.$AUTH[$toPolicy]['ldap base dn'];
    	    $group = "cn=$userAccount,ou=Groups,ou=".$category.','.$AUTH[$toPolicy]['ldap base dn'];
	    $ouDn = "ou=".$category.",".$AUTH[$toPolicy]['ldap base dn'];
	}

	if ($SET['createContainer']) { // Létrehozza a tároló elemet, benne az OU=Groups tárolót, benne a megfelelő csoportot
	    LDAPcreateContainer($ouDn, $toPolicy);
	}
	// objectum osztályok
	// a mayorPerson a posixAccount és shadowAccount leszármazottja,
	// de kell egy structural object is - ez a person - aminek kötelező paramétere az sn!
	$info['objectclass'] = array('person', 'mayorPerson');

	// user felvétel
	$info['homedirectory'] = ekezettelen($info['homedirectory']); // Nem lehet ékezetes :o(

	$_r1 = ldap_add($ds,$dn,$info);
    	if (!$_r1) {
       	    printf("LDAP-Error: %s<br>\n", ldap_error($ds));
	    echo $dn.'<pre>'; var_dump($info); echo '</pre>';
	    return false;
    	}

	// user csoportja
	$groupinfo['cn'] = $userAccount;
	$groupinfo['gidnumber'] = $info['uidnumber'];
	$groupinfo['memberuid'] = ekezettelen($userAccount); // Nem lehet ékezetes :o(
	$groupinfo['description'] = 'A felhasználó saját csoportja';
	$groupinfo['objectclass'] = 'posixGroup';
	$_r2 = ldap_add($ds, $group, $groupinfo);
    	if (!$_r2) {
	    printf("LDAP-Error (userGroup): %s<br>\n", ldap_error($ds));
	    echo $group.'<pre>'; var_dump($groupinfo); echo '</pre>';
	    return false;
    	}

	// Kategória csoportba rakás vagy tanár csoportba rakás ugye...
	// És nincs diák csoport!
	$ginfo['memberuid'] = ekezettelen($userAccount); // Nem lehet ékezetes :o(
	$ginfo['member'] = $dn;

        // Kategória csoportba és egyéb csoportokba rakás
        if (isset($SET['category'])) {
            if (is_array($SET['groups'])) array_unshift($SET['groups'], $category);
            else $SET['groups'] = array($category);

	    for ($i = 0; $i < count($SET['groups']); $i++) {

		$filter = "(&(objectClass=mayorGroup)(cn=".$SET['groups'][$i]."))";
		$justthese = array('cn');
		$sr = ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
		if (ldap_count_entries($ds, $sr)) {
		    $grpInfo = ldap_get_entries($ds, $sr);
		    $groupDn = $grpInfo[0]['dn'];
		    $_r3 = ldap_mod_add($ds, $groupDn, $ginfo);
    		    if (!$_r3) {
			printf("LDAP-Error (category): %s<br>\n", ldap_error($ds));
			echo $groupDn.'<pre>'; var_dump($ginfo); echo '</pre>';
    		    }
		}

	    }

	}


        // nextuid növelés
	if ($freeUidCount == 0) {
    	    $_r4 = ldap_mod_replace($ds,$AUTH[$toPolicy]['ldap base dn'],$oinfo);
	} else {
    	    $_r4 = ldap_mod_del($ds,$AUTH[$toPolicy]['ldap base dn'],$oinfo);
	}
    	if (!$_r4) {
	    printf("LDAP-Error (freeUid): %s<br>\n", ldap_error($ds));
	    return false;
    	}

	ldap_close($ds);

	if (defined('_DATADIR') 
	    && isset($AUTH[$toPolicy]['createAccountScript'])
	    && file_exists(_DATADIR)
	) {
	    $sfp = fopen(_DATADIR.'/'.$AUTH[$toPolicy]['createAccountScript'],'a+');
	    if ($sfp) {
        	fwrite($sfp,"\n# $userAccount l.trehoz.sa\n");
		fwrite($sfp,'/bin/mkdir -p '.$info['homedirectory']."\n");
    		fwrite($sfp,'/bin/chmod 2755 '.$info['homedirectory']."\n");
    		fwrite($sfp,"/bin/chown $userAccount.$userAccount ".$info['homedirectory']."\n");

		fwrite($sfp,'/bin/mkdir '.$info['homedirectory']."/private\n");
        	fwrite($sfp,"/bin/chown $userAccount.$userAccount ".$info['homedirectory']."/private\n");
		fwrite($sfp,'/bin/chmod 0770 '.$info['homedirectory']."/private\n");

		fwrite($sfp,'/bin/mkdir '.$info['homedirectory']."/public_html\n");
    		fwrite($sfp,"/bin/chown $userAccount.$userAccount ".$info['homedirectory']."/public_html\n");
    		fwrite($sfp,'/bin/chmod 0755 '.$info['homedirectory']."/public_html\n");

    		fwrite($sfp,'/bin/ln -s '.$info['homedirectory']." /home\n");
//                chmod($scriptFile,0770);
		fclose($sfp);
	    }
	}
	$_SESSION['alert'][] = 'info:create_uid_success:'.$dn;
	return true;

    }

?>
