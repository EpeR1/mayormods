<?php
/*
    Module:	base/auth-ldap-ng
    Backend:	ldap-ng

    function getLDAPInfo($userDn, $attrList=array('cn'), $toPolicy = '')
    function ldapGetAccountInfo($userAccount, $toPolicy = _POLICY)
    function ldapGetUserInfo($userAccount, $toPolicy = _POLICY)
    function ldapChangeAccountInfo($userAccount, $toPolicy = _POLICY)
    function ldapGetGroupInfo($groupCn, $toPolicy = _POLICY)

*/

######################################################
# getLDAPInfo - általános LDAP lekérdezés
######################################################


    function getLDAPInfo($Dn, $attrList=array('cn'), $toPolicy = _POLICY) {

        global $AUTH;

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
        $filter = '(objectclass=*)';
        $sr = @ldap_search($ds, $Dn, $filter, $attrList);
        if (!$sr) {
	    $_SESSION['alert'][] = "message:ldap_search_failure:".$Dn;
            ldap_close($ds);
            return false;
        }

        $info = @ldap_get_entries($ds,$sr);
        ldap_close($ds);

	return $info;

    }

###########################################################
# ldapGetAccountInfo - felhasználói információk (backend)
###########################################################

    function ldapGetAccountInfo($userAccount, $toPolicy = _POLICY) {

	global $backendAttrs, $backendAttrDef;

	if (!isset($backendAttrs)) list($backendAttrs, $backendAttrDef) = getBackendAttrs('Account', $toPolicy);

	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);

	$result = getLDAPInfo($userDn, $backendAttrs, $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // LDAP schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		foreach ($backendAttrDef as $attr => $def) {
            	    // Egységes szerkezetre alakítjuk, azaz a dn is indexelt
            	    if ($attr == 'dn') $return[$i]['dn'] =  array('count' => 1, 0 => $result[$i]['dn']);
		    elseif (isset($result[$i][$attr])) $return[$i][$attr] = $result[$i][$attr];
		    else $return[$i][$attr] = array('count' => 0);
		}
	    }
	    return $return[0];

	}

    }

#############################################################
# ldapGetUserInfo - felhasználói információk (keretrendszer)
#############################################################

    function ldapGetUserInfo($userAccount, $toPolicy = _POLICY) {

	global $accountAttrToLDAP, $ldapAttrDef;
	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);

	$result = getLDAPInfo($userDn, array_values($accountAttrToLDAP), $toPolicy);
	if ($result === false) {
	    return false;
	} else {

    	    $result[0]['dn'] =  array('count' => 1, 0 => $result[0]['dn']);
	    // Egységes szerkezetre alakítjuk, azaz a dn is indexelt + LDAP --> MaYoR schema
	    foreach ($accountAttrToLDAP as $attr => $ldapAttr) {
		$ldapAttr = kisbetus($ldapAttr);
		if (isset($result[0][$ldapAttr])) $return[$attr] = $result[0][$ldapAttr];
		else $return[$attr] = array('count' => 0);
	    }
	    return $return;

	}

    }

###############################################################
# ldapChangeAccountInfo - felhasználói információk módosítása
###############################################################

    function ldapChangeAccountInfo($userAccount, $toPolicy = _POLICY) {

	global $AUTH,  $backendAttrs, $backendAttrDef;

	$userDn = LDAPuserAccountToDn($userAccount, $toPolicy);

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

	$emptyAttrs = explode(':',$_POST['emptyAttrs']);
	$_alert = array();

	// Attribútumonként módosítunk
	foreach ($backendAttrs as $attr) {

            if ($backendAttrDef[$attr]['rights'] == '') $rigths = _DEFAULT_LDAP_RIGHTS;
            else $rights = $backendAttrDef[$attr]['rights'];

            if ($rights[_ACCESS_AS] == 'w') {
		$mod_info = $add_info = $del_info = Array();
		$values = array();

        	if ($backendAttrDef[$attr]['type'] == 'image') {
            	    $file = $_FILES[$attr]['tmp_name'];
            	    if (file_exists($file)) {
                	$fd = fopen($file,'r');
                	$values[0]=fread($fd,filesize($file));
                	fclose($fd);
            	    } else {
                	// Sose töröljük!
                	$emptyAttrs[] = $attr;
            	    }
        	} elseif ($backendAttrDef[$attr]['type'] == 'timestamp') {
            	    if ($_POST[$attr][0] != '' and $_POST[$attr][1] != '' and $_POST[$attr][2] != '') {
                	$values[0] = $_POST[$attr][0].$_POST[$attr][1].$_POST[$attr][2].'010101Z';
            	    }
        	} else {
            	    if ($backendAttrDef[$attr]['type'] != '' ) $values[0] = $_POST[$attr];
        	}

        	if ($backendAttrDef[$attr]['type'] == 'select') {
            	    if ($_POST['new-'.$attr][0] != '') $add_info[$attr] = $_POST['new-'.$attr];
            	    if ($_POST['del-'.$attr][0] != '') $del_info[$attr] = $_POST['del-'.$attr];
        	} elseif (in_array($attr,$emptyAttrs)) {
                    if ($values[0] != '') $add_info[$attr] = $values;
        	} else {
                    if ($values[0] != '') {
                        $mod_info[$attr] = $values;
                    } else {
                        $del_info[$attr] = Array();
                    }
        	}

        	if (count($add_info)!=0) {
            	    if (!@ldap_mod_add($ds,$userDn,$add_info)) {
                	$_alert[] = 'message:insufficient_access:add:'.$attr;
            	    }
        	}
        	if (count($mod_info)!=0) {
            	    if (!@$r = ldap_mod_replace($ds,$userDn,$mod_info)) {
                	$_alert[] =  'message:insufficient_access:mod:'.$attr;
            	    }
        	}
        	if (count($del_info)!=0) {
            	    if (!@ldap_mod_del($ds,$userDn,$del_info)) {
                	$_alert[] = 'message:insufficient_access:del:'.$attr;
            	    }
        	}

	    } else {
//		$_alert[] = 'message:insufficient_access:'.$attr;
	    }
	} // foreach

        ldap_close($ds);
        if (count($_alert) == 0) $_SESSION['alert'][] = 'info:change_success';
        else for ($i = 0;$i < count($_alert);$i++) $_SESSION['alert'][] = $_alert[$i];

    }

###########################################################
# ldapGetGroupInfo - csoport információk (backend)
###########################################################

    function ldapGetGroupInfo($groupCn, $toPolicy = _POLICY) {

	global $backendAttrs, $backendAttrDef;


	if (!isset($backendAttrs)) list($backendAttrs, $backendAttrDef) = getBackendAttrs('Group', $toPolicy);

	$groupDn = LDAPgroupCnToDn($groupCn, $toPolicy);

	$result = getLDAPInfo($groupDn, $backendAttrs, $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // Accountok lekérdezése
	    $info = getLDAPaccounts($toPolicy);
	    for ($i = 0; $i < $info['count']; $i++) {
		$accountUid[] = array(
		    'value' => $info[$i]['uid'][0],
		    'txt' => $info[$i]['displayname'][0]
		);
		$accountDn[] = array(
		    'value' => $info[$i]['dn'],
		    'txt' => $info[$i]['displayname'][0]
		);
	    }

	    // LDAP schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		foreach ($backendAttrDef as $attr => $def) {
            	    // Egységes szerkezetre alakítjuk, azaz a dn is indexelt
            	    if ($attr == 'dn') $return[$i]['dn'] =  array('count' => 1, 0 => $result[$i]['dn']);
		    elseif (isset($result[$i][$attr])) $return[$i][$attr] = $result[$i][$attr];
		    else $return[$i][$attr] = array('count' => 0);
		}
		$return[$i]['member']['new'] = $accountDn;
		$return[$i]['memberuid']['new'] = $accountUid;
	    }

	    return $return[0];

	}

    }

###############################################################
# ldapChangeGroupInfo - csoport információk módosítása
###############################################################

    function ldapChangeGroupInfo($groupCn, $toPolicy = _POLICY) {

// !!!! A memberuid / member szinkronjára nem figyel!!

	global $AUTH, $backendAttrs, $backendAttrDef;

	$groupDn = LDAPgroupCnToDn($groupCn, $toPolicy);

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

	$emptyAttrs = explode(':',$_POST['emptyAttrs']);
	$_alert = array();

	// Attribútumonként módosítunk
	foreach ($backendAttrs as $attr) {

            if ($backendAttrDef[$attr]['rights'] == '') $rigths = _DEFAULT_LDAP_RIGHTS;
            else $rights = $backendAttrDef[$attr]['rights'];

            if ($rights[_ACCESS_AS] == 'w') {

		$mod_info = $add_info = $del_info = Array();
		$values = array();

        	if ($backendAttrDef[$attr]['type'] == 'image') {
            	    $file = $_FILES[$attr]['tmp_name'];
            	    if (file_exists($file)) {
                	$fd = fopen($file,'r');
                	$values[0]=fread($fd,filesize($file));
                	fclose($fd);
            	    } else {
                	// Sose töröljük!
                	$emptyAttrs[] = $attr;
            	    }
        	} elseif ($backendAttrDef[$attr]['type'] == 'timestamp') {
            	    if ($_POST[$attr][0] != '' and $_POST[$attr][1] != '' and $_POST[$attr][2] != '') {
                	$values[0] = $_POST[$attr][0].$_POST[$attr][1].$_POST[$attr][2].'010101Z';
            	    }
        	} else {
            	    if ($backendAttrDef[$attr]['type'] != '')
			if (isset($_POST[$attr])) $values[0] = $_POST[$attr];
			else $values[0] = '';
        	}

        	if ($backendAttrDef[$attr]['type'] == 'select') {
            	    if (isset($_POST['new-'.$attr][0]) && $_POST['new-'.$attr][0] != '') $add_info[$attr] = $_POST['new-'.$attr];
            	    if (isset($_POST['del-'.$attr][0]) && $_POST['del-'.$attr][0] != '') $del_info[$attr] = $_POST['del-'.$attr];
        	} elseif (in_array($attr,$emptyAttrs)) {
                    if ($values[0] != '') $add_info[$attr] = $values;
        	} else {
                    if ($values[0] != '') {
                        $mod_info[$attr] = $values;
                    } else {
                        $del_info[$attr] = Array();
                    }

        	}

        	if (count($add_info)!=0) {
            	    if (!@ldap_mod_add($ds,$groupDn,$add_info)) {
                	$_alert[] = 'message:insufficient_access:add:'.$attr;
            	    }
        	}
        	if (count($mod_info)!=0) {
            	    if (!@ldap_mod_replace($ds,$groupDn,$mod_info)) {
                	$_alert[] =  'message:insufficient_access:mod:'.$attr;
            	    }
        	}
        	if (count($del_info)!=0) {
            	    if (!@ldap_mod_del($ds,$groupDn,$del_info)) {
                	$_alert[] = 'message:insufficient_access:del:'.$attr;
            	    }
        	}

	    } else {
//		$_alert[] = 'message:insufficient_access:'.$attr;
	    }
	} // foreach

        ldap_close($ds);
        if (count($_alert) == 0) $_SESSION['alert'][] = 'info:change_success';
        else for ($i=0;$i<count($_alert);$i++) $_SESSION['alert'][] = $_alert[$i];

    }

    function getLDAPaccounts($toPolicy = _POLICY) {

	global $AUTH;

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
	$attrList = array('cn','uid','displayName','samaccountname');
        $filter = '(&(objectclass=person)(!(objectclass=computer)))';
        $sr = @ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $attrList);
        if (!$sr) {
	    $_SESSION['alert'][] = "message:ldap_search_failure:".$userDn;
            ldap_close($ds);
            return false;
        }

	ldap_sort($ds, $sr, 'displayname');
        $info = @ldap_get_entries($ds,$sr);
        ldap_close($ds);

	return $info;

    }


?>
