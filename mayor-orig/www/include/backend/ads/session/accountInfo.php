<?php
/*
    Module:	base/auth-ads
    Backend:	ads

    function getADSInfo($userDn, $attrList=array('cn'), $toPolicy = '')
    function adsGetAccountInfo($userAccount, $toPolicy = _POLICY)
    function adsGetUserInfo($userAccount, $toPolicy = _POLICY)
    function adsChangeAccountInfo($userAccount, $toPolicy = _POLICY)
    function adsGetGroupInfo($groupCn, $toPolicy = _POLICY)

*/

######################################################
# getADSInfo - általános ADS lekérdezés
######################################################


    function getADSInfo($userDn, $attrList=array('cn'), $toPolicy = _POLICY) {

        global $AUTH;

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

        // Keresés
        $filter = '(objectclass=*)';
        $sr = @ldap_search($ds, $userDn, $filter, $attrList);
        if (!$sr) {
	    $_SESSION['alert'][] = "message:ldap_search_failure:".$userDn;
            ldap_close($ds);
            return false;
        }

        $info = @ldap_get_entries($ds,$sr);
        ldap_close($ds);

	return $info;

    }

###########################################################
# adsGetAccountInfo - felhasználói információk (backend)
###########################################################

    function adsGetAccountInfo($userAccount, $toPolicy = _POLICY) {

	global $backendAttrs, $backendAttrDef;

	if (!isset($backendAttrs)) list($backendAttrs, $backendAttrDef) = getBackendAttrs('Account', $toPolicy);

	$userDn = ADSuserAccountToDn($userAccount, $toPolicy);

	$result = getADSInfo($userDn, $backendAttrs, $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // ADS schema --> mayor schema konverzió
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
# adsGetUserInfo - felhasználói információk (keretrendszer)
#############################################################

    function adsGetUserInfo($userAccount, $toPolicy = _POLICY) {

	global $accountAttrToADS, $adsAttrDef;
	$userDn = ADSuserAccountToDn($userAccount, $toPolicy);

	$result = getADSInfo($userDn, array_values($accountAttrToADS), $toPolicy);
	if ($result === false) {
	    return false;
	} else {

    	    $result[0]['dn'] =  array('count' => 1, 0 => $result[0]['dn']);
	    // Egységes szerkezetre alakítjuk, azaz a dn is indexelt + ADS --> MaYoR schema
	    foreach ($accountAttrToADS as $attr => $adsAttr) {
		$adsAttr = kisbetus($adsAttr);
		if (isset($result[0][$adsAttr])) $return[$attr] = $result[0][$adsAttr];
		else $return[$attr] = array('count' => 0);
	    }
	    return $return;

	}

    }

###############################################################
# adsChangeAccountInfo - felhasználói információk módosítása
###############################################################

    function adsChangeAccountInfo($userAccount, $toPolicy = _POLICY) {

	global $AUTH,  $backendAttrs, $backendAttrDef;
	$userDn = ADSuserAccountToDn($userAccount, $toPolicy);

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

	$emptyAttrs = explode(':',$_POST['emptyAttrs']);
	$_alert = array();

	// Attribútumonként módosítunk
	foreach ($backendAttrs as $attr) {

            if ($backendAttrDef[$attr]['rights'] == '') $rigths = _DEFAULT_ADS_RIGHTS;
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
# adsGetGroupInfo - csoport információk (backend)
###########################################################

    function adsGetGroupInfo($groupCn, $toPolicy = _POLICY, $SET = array()) {

	global $backendAttrs, $backendAttrDef;


	if (!isset($backendAttrs)) list($backendAttrs, $backendAttrDef) = getBackendAttrs('Group', $toPolicy);

	$groupDn = ADSgroupCnToDn($groupCn, $toPolicy);

	$result = getADSInfo($groupDn, $backendAttrs, $toPolicy);
	if ($result === false) {
	    return false;
	} else {

	    // Accountok lekérdezése
	    $info = getADSaccounts($toPolicy);
	    for ($i = 0; $i < $info['count']; $i++) {
		$accountUid[] = array(
		    'value' => $info[$i]['uid'][0],
		    'txt' => $info[$i]['displayname'][0]
		);
		$accountDn[] = array(
		    'value' => $info[$i]['dn'],
		    'txt' => $info[$i]['displayname'][0]
		);
		$DN2CN[$info[$i]['dn']] = $info[$i]['displayname'][0];
	    }

	    // ADS schema --> mayor schema konverzió
	    for ($i = 0; $i < $result['count']; $i++) {
		// Egységes szerkezetre alakítjuk, azaz a dn is indexelt
		foreach ($backendAttrDef as $attr => $def) {
            	    // Egységes szerkezetre alakítjuk, azaz a dn is indexelt
            	    if ($attr == 'dn') $return[$i]['dn'] =  array('count' => 1, 0 => $result[$i]['dn']);
		    elseif($attr == 'member') {
			$_TMP = array();
			for ($j=0; $j<$result[$i][$attr]['count']; $j++) {
			    $_dn = $result[$i][$attr][$j];
			    $_TMP[] = array(
			    'type'=>'member',
			    'value'=>$_dn,
			    'txt'=>($DN2CN[$_dn]==''?str_replace(',',' ',$_dn):$DN2CN[$_dn])
			    );
			}
			$return[$i][$attr] = $_TMP;
		    }
		
		    elseif (isset($result[$i][$attr])) $return[$i][$attr] = $result[$i][$attr];
		    else $return[$i][$attr] = array('count' => 0);
		}

		if ($SET['withNewAccounts']===true) {
		    $return[$i]['member']['new'] = $accountDn;
		    $return[$i]['memberuid']['new'] = $accountUid;
		}
	    }

	    return $return[0];

	}

    }

###############################################################
# adsChangeGroupInfo - csoport információk módosítása
###############################################################

    function adsChangeGroupInfo($groupCn, $toPolicy = _POLICY) {

// !!!! A memberuid / member szinkronjára nem figyel!!

	global $AUTH,  $backendAttrs, $backendAttrDef;
	$groupDn = ADSgroupCnToDn($groupCn, $toPolicy);

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

	$emptyAttrs = explode(':',$_POST['emptyAttrs']);
	$_alert = array();

	// Attribútumonként módosítunk
	foreach ($backendAttrs as $attr) {

            if ($backendAttrDef[$attr]['rights'] == '') $rigths = _DEFAULT_ADS_RIGHTS;
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

    function getADSaccounts($toPolicy = _POLICY) {

	global $AUTH;

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

        // Keresés
	$attrList = array('cn','uid','displayName','samaccountname');
        $filter = '(&(objectclass=person)(!(objectclass=computer)))';
        $sr = @ldap_search($ds, $AUTH[$toPolicy]['adsBaseDn'], $filter, $attrList);
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
