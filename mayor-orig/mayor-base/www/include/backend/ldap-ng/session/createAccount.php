<?php
/*
    Modules: base/session
*/

    require_once('include/backend/ldap-ng/password/changePassword.php');

    /*
	$SET = array(
	    container => a konténer elem - ha nincs, akkor CN=Users alá rakja
	    category => tanár, diák... egy kiemelt fontosságú csoport tagság
	    groups => egyéb csoportok
	    policyAttrs => policy függő attribútumok
	)
    */
    function ldapCreateAccount(
	$userCn, $userAccount, $userPassword, $toPolicy, $SET
    ) {

        global $AUTH;

	$shadowLastChange = floor(time() / (60*60*24));

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

	// uid ütközés ellenőrzése
	$filter = "(sAMAccountName=$userAccount)";
	$justthese = array('sAMAccountName');
	$sr = ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
	$uinfo = ldap_get_entries($ds, $sr);
	$uidCount = $uinfo['count'];
	ldap_free_result($sr);
	if ($uidCount > 0) {
    	    $_SESSION['alert'][] = 'message:multi_uid:'.$userAccount;
    	    return false;
    	}

	// Az következő uidNumber megállapítása
        $filter = "(&(objectclass=".$AUTH[$toPolicy]['ldapUserObjectClass'].")(uidNumber=*))";
        $justthese = array('uidNumber', 'msSFU30UidNumber');
        $sr = ldap_search($ds,$AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
        ldap_sort($ds, $sr, 'uidNumber');
        $uinfo = ldap_get_entries($ds, $sr);
        ldap_free_result($sr);
        if (isset($uinfo['count']) && $uinfo['count'] > 0) $info['uidNumber'] = array($uinfo[ $uinfo['count']-1 ]['uidnumber'][0]+1);
        else $info['uidNumber'] = array(1001);

	// shadow attributumok...
	// A shadowLastChange a mai nap // if (isset($AUTH[$toPolicy]['shadowlastchange']) && $AUTH[$toPolicy]['shadowlastchange'] != '') 
	$info['shadowLastChange'] = array($shadowLastChange);
	if (isset($AUTH[$toPolicy]['shadowMin']) && $AUTH[$toPolicy]['shadowMin'] != '') $info['shadowMin'] = array($AUTH[$toPolicy]['shadowMin']);
	if (isset($AUTH[$toPolicy]['shadowMax']) && $AUTH[$toPolicy]['shadowMax'] != '') $info['shadowMax'] = array($AUTH[$toPolicy]['shadowMax']);
	if (isset($AUTH[$toPolicy]['shadowWarning']) && $AUTH[$toPolicy]['shadowWarning'] != '') $info['shadowWarning'] = array($AUTH[$toPolicy]['shadowWarning']);
	if (isset($AUTH[$toPolicy]['shadowInactive']) && $AUTH[$toPolicy]['shadowInactive'] != '') $info['shadowInactive'] = array($AUTH[$toPolicy]['shadowInactive']);
	if (isset($AUTH[$toPolicy]['shadowExpire']) && $AUTH[$toPolicy]['shadowWxpire'] != '') $info['shadowExpire'] = array($AUTH[$toPolicy]['shadowExpire']);

	// A szokásos attribútumok
	$Name = explode(' ',$userCn);
	$Dn = ldap_explode_dn($AUTH[$toPolicy]['ldapBaseDn'], 1); unset($Dn['count']);
	$info['userPrincipalName'] = array( $userAccount.'@'.implode('.', $Dn));
	$info['msSFU30Name'] = $info['sAMAccountName'] = $info['cn'] = array($userAccount);
	$info['displayName'] = array($userCn);
	$info['sn'] = array($Name[0]);
	$info['givenName'] = array($Name[ count($Name)-1 ]);
	$info['unixUserPassword'] = array('ABCD!efgh12345$67890');
	$info['unixHomeDirectory'] = array(ekezettelen("/home/$userAccount"));
	$info['loginShell'] = array('/bin/bash');
	$info['objectClass'] = array($AUTH[$toPolicy]['ldapUserObjectClass'], 'user');

	$policyAccountAttrs = $SET['policyAttrs'];
	if (isset($policyAccountAttrs['studyId'])) $info[ $AUTH[$toPolicy]['ldapStudyIdAttr'] ] = array($policyAccountAttrs['studyId']);
	foreach ($policyAccountAttrs as $attr => $value) 
	    if ($attr != 'studyId' && isset($accountAttrToLDAP[$attr]))
		$info[ $accountAttrToLDAP[$attr] ] = array($value);

	if (isset($SET['container'])) $dn = "CN=$userAccount,".$SET['container'];
	else $dn = "CN=$userAccount,CN=Users,".$AUTH[$toPolicy]['ldapBaseDn'];

	// user felvétel
	$_r1 = @ldap_add($ds,$dn,$info);
    	if (!$_r1) {
	    $_SESSION['alert'][] = 'message:ldap_error:Add user:'.ldap_error($ds);
	    //echo $dn.'<pre>'; var_dump($info); echo '</pre>';
	    return false;
    	}

	// Jelszó beállítás
	if (!changePassword($userAccount, $userPassword, $toPolicy)) $_SESSION['alert'][] = 'message:ldap_error:changePassword failed:'.$userAccount;

	// Engedélyezés
	$einfo = array('userAccountControl' => array(512)); /* Normal account = 512 */
	$_r1 = @ldap_mod_replace($ds,$dn,$einfo);
    	if (!$_r1) {
	    $_SESSION['alert'][] = 'message:ldap_error:Enable user:'.ldap_error($ds);
	    //echo $dn.'<pre>'; var_dump($info); echo '</pre>';
	    return false;
    	}

	// Kategória csoportba és egyéb csoportokba rakás
	if (isset($SET['category'])) {
	    if (is_array($SET['groups'])) array_unshift($SET['groups'], $SET['category']);
	    else $SET['groups'] = array($SET['category']);

	    $ginfo['member'] = $dn;

	    for ($i = 0; $i < count($SET['groups']); $i++) {
		$groupDn = LDAPgroupCnToDn($SET['groups'][$i], $toPolicy);
		if ($groupDn !== false) {
		    $_r3 = @ldap_mod_add($ds, $groupDn, $ginfo);
    		    if (!$_r3) {
			$_SESSION['alert'][] = 'message:ldap_error:Add to group '.$SET['groups'][$i].':'.ldap_error($ds);
			//echo $SET['groups'][$i].'<pre>'; var_dump($ginfo); echo '</pre>';
    		    }
		}
	    }
	}

	ldap_close($ds);

	if (defined('_DATADIR') 
	    && isset($AUTH[$toPolicy]['createAccountScript'])
	    && file_exists(_DATADIR)
	) {
	    $sfp = fopen(_DATADIR.'/'.$AUTH[$toPolicy]['createAccountScript'],'a+');
	    if ($sfp) {
		fwrite($sfp,"\n# $userAccount létrehozása: userAccount uidNumber homeDirectory\n");
                fwrite($sfp,"createAccount.sh '$userAccount' '".$info['uidNumber'][0]."' '".$info['unixHomeDirectory'][0]."'\n");
		fclose($sfp);
	    }
	}
	$_SESSION['alert'][] = 'info:create_uid_success:'.$dn;
	return true;

    }

?>
