<?php

    function mysqlCreateGroup($groupCn, $groupDesc, $toPolicy = _POLICY, $SET = null) {

        global $AUTH;

	// $toPolicy --> backend - ellenőrzés!
	if ($AUTH[$toPolicy]['backend'] != 'mysql') {
	    $_SESSION['alert'][] = 'page:wrong_backend:'.$AUTH[$toPolicy]['backend'];
	    return false;
	}

        // Kapcsolódás az MySQL szerverhez
	$modul = "$toPolicy auth";
        $lr = @db_connect($modul, array('fv' => 'mysqlCreateGroup'));
        if (!$lr) return false;

	// cn ütközés ellenőrzése
	$q = "SELECT COUNT(*) FROM groups WHERE policy='%s' AND groupCn='%s'";
	$v = array($toPolicy, $groupCn);
	$num = db_query($q, array('fv' => 'mysqlCreateGroup', 'modul' => $modul, 'result' => 'value', 'values' => $v), $lr);
	if ($num === false) { db_close($lr); return false; }
	if ($num > 0) { $_SESSION['alert'][] = 'message:multi_uid:'.$groupCn; db_close($lr); return false; }

	// csoport felvétel
	$q = "INSERT INTO groups (groupCn, groupDesc, policy) VALUES ('%s', '%s','%s')";
	$v = array($groupCn, $groupDesc, $toPolicy);
	$gid = db_query($q, array('fv' => 'mysqlCreateGroup', 'modul' => $modul, 'result' => 'insert', 'values' => $v), $lr);
	if ($gid === false) { db_close($lr); return false; }

	$_SESSION['alert'][] = 'info:create_group_success:'.$dn;
	db_close($lr);
	return true;

    }

?>
