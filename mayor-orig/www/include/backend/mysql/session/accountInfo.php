<?php
/*
    Module:	base/auth-mysql
    Backend:	mysql

    function mysqlGetAccountInfo($userAccount, $toPolicy = _POLICY)
    function mysqlGetUserInfo($userAccount, $toPolicy = _POLICY)
    function mysqlChangeAccountInfo($userAccount, $toPolicy = _POLICY)
    function mysqlGetGroupInfo($groupCn, $toPolicy = _POLICY)

*/

###########################################################
# mysqlGetAccountInfo - felhasználói információk (backend)
###########################################################

    function mysqlGetAccountInfo($userAccount, $toPolicy = _POLICY, $SET = array()) {

	global $AUTH, $backendAttrs, $backendAttrDef;

        // Keresés
	if (is_array($SET['justThese']) && count($SET['justThese']) > 0) {
	    $_THESE =  '`'.implode('`,`', array_fill(0, count($SET['justThese']), '%s')).'`';
	    $v = $SET['justThese'];
	} else {
	    $_THESE = '*';
	    $v = array();
	}
	$q = "SELECT $_THESE FROM accounts WHERE userAccount='%s' AND policy='%s'";
	array_push($v, $userAccount, $toPolicy);
	$A = db_query($q, array('fv' => 'mysqlGetAccountInfo', 'modul' => "$toPolicy auth", 'result' => 'record', 'values' => $v), $lr);
	if (!is_array($A) || count($A) == 0) return false;

	$data = array();
	foreach ($A as $attr => $value) $data[$attr][] = $value;
	foreach ($data as $attr => $array) $data[$attr]['count'] = count($array);

	return $data;

    }

#############################################################
# mysqlGetUserInfo - felhasználói információk (keretrendszer)
#############################################################

    function mysqlGetUserInfo($userAccount, $toPolicy = _POLICY) {

	global $AUTH, $backendAttrs, $backendAttrDef;

	if (!isset($backendAttrs)) list($backendAttrs, $backendAttrDef) = getBackendAttrs('Account', $toPolicy);

        // Keresés
	$q = "SELECT userAccount,userCn FROM accounts WHERE userAccount='%s' AND policy='%s'";
	$A = db_query($q, array('fv' => 'mysqlGetUserInfo', 'modul' => "$toPolicy auth", 'result' => 'record', 'values' => array($userAccount, $toPolicy)));
	if (!is_array($A) || count($A) == 0) return false;
	$ret = array();
	foreach ($A as $attr => $value) $ret[$attr][] = $value;
	return $ret;

    }

###############################################################
# mysqlChangeAccountInfo - felhasználói információk módosítása
###############################################################

    function mysqlChangeAccountInfo($userAccount, $toPolicy = _POLICY) {

	global $AUTH, $backendAttrs, $backendAttrDef;

        // Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
	$lr = db_connect($modul, array('fv' => 'mysqlChangeAccountInfo'));
        if (!$lr) return false;

	$emptyAttrs = explode(':',$_POST['emptyAttrs']);

	// Attribútumonként módosítunk
	foreach ($backendAttrs as $attr) {

            if ($backendAttrDef[$attr]['rights'] == '') $rigths = _DEFAULT_MYSQL_RIGHTS;
            else $rights = $backendAttrDef[$attr]['rights'];

            if ($rights[_ACCESS_AS] == 'w') {

		$value = '';

        	if ($backendAttrDef[$attr]['type'] == 'int') {
            	    if ($backendAttrDef[$attr]['type'] != '' ) $value = readVariable($_POST[$attr], 'number');
        	} else {
            	    if ($backendAttrDef[$attr]['type'] != '' ) $value = readVariable($_POST[$attr], 'string'); // html túl erős: pl email címben a @ fent akad...
        	}

		if (in_array($attr,$emptyAttrs)) {
                    if ($value != '') {
			$q = "UPDATE accounts SET `%s`='%s' WHERE userAccount='%s' AND policy='%s'";
			$v = array($attr, $value, $userAccount, $toPolicy);
		    }
        	} else {
                    if ($value != '') {
			$q = "UPDATE accounts SET `%s`='%s' WHERE userAccount='%s' AND policy='%s'";
			$v = array($attr, $value, $userAccount, $toPolicy);
                    } else {
			$q = "UPDATE accounts SET `%s`=NULL WHERE userAccount='%s' AND policy='%s'";
			$v = array($attr, $userAccount, $toPolicy);
                    }
        	}
		db_query($q, array('fv' => 'mysqlChangeAccountInfo', 'modul' => $modul, 'values' => $v), $lr);

	    } else {
		// $_alert[] = 'message:insufficient_access:'.$attr;
	    }
	} // foreach

        db_close($lr);
        if (count($_alert) == 0) $_SESSION['alert'][] = 'info:change_success';
        else for ($i = 0; $i < count($_alert); $i++) $_SESSION['alert'][] = $_alert[$i];

    }

###########################################################
# mysqlGetGroupInfo - csoport információk (backend)
###########################################################

    function mysqlGetGroupInfo($groupCn, $toPolicy = _POLICY, $SET = array()) {

	global $AUTH, $backendAttrs, $backendAttrDef;

	if (!isset($backendAttrs)) list($backendAttrs, $backendAttrDef) = getBackendAttrs('Group', $toPolicy);

        // Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
	$lr = db_connect($modul, array('fv' => 'mysqlGetGroupInfo'));
        if (!$lr) return false;

        // Keresés
	if (is_array($SET['justThese']) && count($SET['justThese']) > 0) {
	    $_THESE = '`'.implode('`,`', array_fill(0, count($SET['justThese']), '%s')).'`';
	    $v = $SET['justThese'];
	} else {
	    $_THESE = '*';
	    $v = array();
	}
	$q = "SELECT $_THESE FROM groups WHERE groupCn='%s' AND policy='%s'";

	array_push($v, $groupCn, $toPolicy);
	$A = db_query($q, array('fv' => 'mysqlGetGroupInfo', 'modul' => $modul, 'result' => 'record', 'values' => $v), $lr);
	if (!is_array($A) || count($A) == 0) { db_close($lr); return false; }
	// Megfelelő formátum kialakítása
	foreach ($A as $attr => $value) $data[$attr][] = $value;
	foreach ($data as $attr => $array) $data[$attr]['count'] = count($array);

	// tagok lekérdezése
	$q = "SELECT 'member' AS type, uid AS value, userCn AS txt FROM members LEFT JOIN accounts USING (uid) WHERE gid = '%s'";
	$v = array($A['gid']);
	$data2 = db_query($q, array('fv' => 'mysqlGetGroupInfo', 'modul' => $modul, 'result' => 'multiassoc', 'keyfield' => 'type', 'values' => $v), $lr);
	if ($data2 === false) { db_close($lr); return false; }
	$data = array_merge($data, $data2);
	
	// Lehetséges tagok
	if ($SET['withNewAccounts']===true) {
	    $q = "SELECT userCn AS txt, uid AS value FROM accounts WHERE policy='%s' ORDER BY userCn";
	    $data['member']['new'] = db_query($q, array(
		'fv' => 'mysqlGetGroupInfo', 'modul' => $modul, 'result' => 'indexed', 'values' => array($toPolicy)
	    ), $lr);
	}

	db_close($lr);
	return $data;

    }


###############################################################
# mysqlChangeGroupInfo - csoport információk módosítása
###############################################################

    function mysqlChangeGroupInfo($groupCn, $toPolicy = _POLICY) {

// !!!! A memberuid / member szinkronjára nem figyel!!

	global $AUTH, $backendAttrs, $backendAttrDef;

        // Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
	$lr = db_connect($modul, array('fv' => 'mysqlChangeGroupInfo'));
        if (!$lr) return false;

	$q = "SELECT gid FROM groups WHERE groupCn='%s' AND policy='%s'";
	$v = array($groupCn, $toPolicy);
	$gid = db_query($q, array('fv' => 'mysqlChangeGroupInfo', 'modul' => $modul, 'result' => 'value', 'values' => $v), $lr);
	if ($gid === false) { db_close($lr); return false; }

	$emptyAttrs = explode(':', $_POST['emptyAttrs']);

	// Attribútumonként módosítunk
	foreach ($backendAttrs as $attr) {

            if ($backendAttrDef[$attr]['rights'] == '') $rigths = _DEFAULT_LDAP_RIGHTS;
            else $rights = $backendAttrDef[$attr]['rights'];

            if ($rights[_ACCESS_AS] == 'w') {

		$Mod = $Add = $Del = $V = $v = array();
		$values = array();

            	if ($backendAttrDef[$attr]['type'] != '')
		    if (isset($_POST[$attr])) $values[0] = readVariable($_POST[$attr],'html');
		    else $values[0] = '';

        	if ($backendAttrDef[$attr]['type'] == 'select') {
		    if ($attr == 'member') {
            		if (isset($_POST['new-'.$attr][0]) && $_POST['new-'.$attr][0] != '') {
			    for ($i = 0; $i < count($_POST['new-'.$attr]); $i++) {
				$V[] = "(%u, %u)";
				array_push($v, $_POST['new-'.$attr][$i], $gid);
			    }
			    $q = "INSERT INTO members (uid, gid) VALUES ".implode(',', $V);
			    db_query($q, array('fv' => 'mysqlChangeGroupInfo', 'modul' => $modul, 'values' => $v), $lr);
			}
            		if (isset($_POST['del-'.$attr][0]) && $_POST['del-'.$attr][0] != '') {
			    $q = "DELETE FROM members WHERE gid=%u
				    AND uid IN (".implode(',', array_fill(0, count($_POST['del-'.$attr]), '%u')).")";
			    $v = array_merge(array($gid), $_POST['del-'.$attr]);
			    $r = db_query($q, array('fv' => 'mysqlChangeGroupInfo', 'modul' => $modul, 'values' => $v), $lr);
			}
		    } else {
			$_SESSION['alert'][] = 'message:invalid_type:select:'.$attr;
		    }
        	} else {
		    if (in_array($attr, $emptyAttrs)) {
                	if ($values[0] != '') {
			    $W = "`%s`='%s'";
			    $v = array($attr, $values[0]);
			}
        	    } else {
                	if ($values[0] != '') {
			    $W = "`%s`='%s'";
			    $v = array($attr, $values[0]);
                	} else {
			    $W = "`%s`=NULL";
			    $v = array($attr);
			}
                    }
		    $q = "UPDATE groups SET $W WHERE groupCn='%s' AND policy='%s'";
		    array_push($v, $groupCn, $toPolicy);
		    db_query($q, array('fv' => 'mysqlChangeGroupInfo', 'modul' => $modul, 'values' => $v), $lr);
        	}
	    } else {
		$_alert[] = 'message:insufficient_access:'.$attr;
	    }
	} // foreach

	db_close($lr);
	return true;

    }

?>
