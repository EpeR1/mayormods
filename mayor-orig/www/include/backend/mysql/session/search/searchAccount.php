<?php
/*
    Module:	base/session
    Backend:	mysql

*/

######################################################
# MySQL account kereső függvény
######################################################

    function mysqlSearchAccount($attr, $pattern, $searchAttrs = array('userCn'), $toPolicy = _POLICY) {

        global $AUTH;

        if ($pattern == '') {
            $_SESSION['alert'][] = 'message:empty_field:mysqlSerachAccount, pattern';
	    return false;
        }

	// Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
	$lr = @db_connect($modul, array('fv' => 'mysqlSearchAccount'));
    	if (!$lr) return false;

	// Keresés
	$q = "SELECT `".implode('`,`', array_fill(0, count($searchAttrs), '%s'))."` FROM accounts WHERE `%s` LIKE '%%%s%%' AND policy='%s'";
	$v = array_merge($searchAttrs, array($attr, $pattern, $toPolicy));
	$r = db_query($q, array('fv' => 'mysqlSearchAccount', 'modul' => $modul, 'result' => 'indexed', 'values' => $v), $lr);
	db_close($lr);
	if ($r === false) return false;
	$ret = array('count' => count($r));
	foreach ($r as $key => $A) {
	    $data = array();
	    foreach ($A as $attr => $value) {
		$data[$attr] = array($value);
		$data[$attr]['count']++;
	    }
	    $data['category'] = getAccountCategories($data['userAccount'][0], $toPolicy);
	    $data['category']['count'] = count($data['category']);
	    $ret[] = $data;
	}
	
    	return $ret;

    }

######################################################
# MySQL group kereső függvény
######################################################

    function mysqlSearchGroup($attr, $pattern, $searchAttrs = array('userCn'), $toPolicy = _POLICY) {

        global $AUTH;

        if ($pattern == '') {
            $_SESSION['alert'][] = 'message:empty_field:mysqlSearchGroup, pattern';
	    return false;
        }

	// Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
	$lr = db_connect($modul, array('fv' => 'mysqlSearchGroup'));
    	if (!$lr) return false;
	// Keresés
	if ($attr == 'member') {
	    $q = "SELECT `".implode('`,`', array_fill(0, count($searchAttrs), '%s'))."` FROM groups LEFT JOIN members 
		ON members.gid=groups.gid
		LEFT JOIN accounts USING (uid)
		WHERE gid IN 
		    (SELECT DISTINCT gid FROM accounts LEFT JOIN members USING(uid) WHERE userAccount LIKE '%%%s%%' AND policy='%s')
		AND groups.policy='%s'";
	    $v = array_merge($searchAttrs, array($pattern, $toPolicy, $toPolicy));
	} else {
	    $q = "SELECT DISTINCT `".implode('`,`', array_fill(0, count($searchAttrs), '%s'))."` FROM groups LEFT JOIN members 
		ON members.gid=groups.gid
		LEFT JOIN accounts USING (uid)
		WHERE `%s` LIKE '%%%s%%' AND groups.policy='%s'";
	    $v = array_merge($searchAttrs, array($attr, $pattern, $toPolicy));
	}
	$r = db_query($q, array('fv' => 'mysqlSearchGroup', 'modul' => $modul, 'result' => 'indexed', 'values' => $v), $lr);
	db_close($lr);
	if ($r === false) return false;
	$ret = array('count' => count($r));
	foreach ($r as $key => $A) {
	    $data = array();
	    foreach ($A as $attr => $value) {
		$data[$attr] = array($value);
	    }
	    $ret[] = $data;
	}

    	return $ret;

    }

######################################################
# mysqlDeleteAccount - account törlése
######################################################

    function mysqlDeleteAccount($userAccount, $toPolicy = _POLICY) {

	global $AUTH;

	// $toPolicy --> mysql backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'mysql') {
            $_SESSION['alert'][] = 'page:wrong_backend:'.$AUTH[$toPolicy]['backend'];
            return false;
        }

	// Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
	$lr = @db_connect($modul, array('fv' => 'mysqlDeleteAccount'));
    	if (!$lr) return false;

        // Az uidNumber, a homeDirectory lekerdezése - és mire használjuk, ha szabad kérdeznem???
	if ($AUTH[$toPolicy]['createHomeDir']) {
	    $q = "SELECT homeDirectory, uid FROM accounts WHERE policy='%s' AND userAccount='%s'";
	    $v = array($toPolicy, $userAccount);
	    $ret = db_query($q, array('fv' => 'mysqlDeleteAccount', 'modul' => $modul, 'result' => 'record', 'values' => $v), $lr);
	    if ($ret === false) { db_close($lr); return false; }

	    $homeDirectory = $ret['homeDirectory']; // de nem használjuk semmire...
	    // A user csoport törlése
	    $q = "DELETE FROM groups WHERE gid=%u";
	    $v = array($ret['uid']);
	    $r = db_query($q, array('fv' => 'mysqlDeleteAccount', 'modul' => $modul, 'values' => $v), $lr);
	    if (!$r) { db_close($lr); return false; }
	}

        // user törlése
	$q = "DELETE FROM accounts WHERE policy='%s' AND userAccount='%s'";
	$v = array($toPolicy, $userAccount);
	$r = db_query($q, array('fv' => 'mysqlDeleteAccount', 'modul' => $modul, 'values' => $v), $lr);
	db_close($lr);
        // törlés a csoportból - Ha innoDb - akkor nincs ezzel tennivaló!!
        if ($r) $_SESSION['alert'][] = 'info:delete_uid_success:'.$userDn;

	return $r;

    }

######################################################
# mysqlDeleteGroup - group törlése
######################################################

    function mysqlDeleteGroup($groupCn, $toPolicy = _POLICY) {

	global $AUTH;

	// $toPolicy --> mysql backend - ellenőrzés
        if ($AUTH[$toPolicy]['backend'] != 'mysql') {
            $_SESSION['alert'][] = 'page:wrong_backend:'.$AUTH[$toPolicy]['backend'];
            return false;
        }

        // csoport törlése
	$q = "DELETE FROM groups WHERE policy='%s' AND groupCn='%s'";
	$v = array($toPolicy, $groupCn);
	$r = db_query($q, array('fv' => 'mysqlDeleteGroup', 'modul' => "$toPolicy auth", 'values' => $v));

	if ($r) $_SESSION['alert'][] = 'info:delete_uid_success:'.$userDn;

        // tagok törlése a csoportból - Ha innoDb - akkor nincs ezzel tennivaló!!
        return $r;

    }

?>
