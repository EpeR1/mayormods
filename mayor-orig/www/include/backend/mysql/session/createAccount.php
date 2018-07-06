<?php
/*
    Module:	base/session
    Backend:	mysql

    function mysqlCreateAccount($userCn, $userAccount, $studyId, $userPassword, $category, $toPolicy = _POLICY) {

*/

    /*
        $SET = array( 
            container => a konténer elem - MySQL backend esetén nincs értelme
            category => tanár, diák... egy kiemelt fontosságú csoport tagság 
            groups => egyéb csoportok 
            policyAttrs => policy függő attribútumok 
	    createGroup => létrehozza az adott nevű csoportokat, ha nincsenek
        ) 

    */
    function mysqlCreateAccount(
	$userCn, $userAccount, $userPassword, $toPolicy, $SET
    ) {

        global $AUTH;

        $shadowlastchange = floor(time() / (60*60*24));
	$modul = "$toPolicy auth";
        $lr = db_connect($modul, array('fv' => 'mysqlCreateAccount'));
        if (!$lr) return _AUTH_FAILURE;

	// ütközés ellenőrzése
	$q = "SELECT COUNT(userCn) FROM accounts WHERE userAccount = '%s' AND policy = '%s'";
	$v = array($userAccount, $toPolicy);
	$num = db_query($q, array('fv' => 'mysqlCreateAccount', 'modul' => $modul, 'result' => 'value', 'values' => $v), $lr);
	if ($num > 0) {
	    db_close($lr);
	    $_SESSION['alert'][] = 'message:multi_uid'.":$userAccount:$toPolicy";
	    return false;
	}

        // A shadowLastChange a mai nap // if (isset($AUTH[$toPolicy]['shadowlastchange']) && $AUTH[$toPolicy]['shadowlastchange'] != '') $shadowlastchange = $AUTH[$toPolicy]['shadowlastchange'];
	$shadowmin = readVariable($AUTH[$toPolicy]['shadowmin'], 'numeric unsigned', 'null'); // null szöveg
	$shadowmax = readVariable($AUTH[$toPolicy]['shadowmax'], 'numeric unsigned', 'null'); // null szöveg
	$shadowwarning = readVariable($AUTH[$toPolicy]['shadowwarning'], 'numeric unsigned', 'null'); // null szöveg
	$shadowinactive = readVariable($AUTH[$toPolicy]['shadowinactive'], 'numeric unsigned', 'null'); // null szöveg
	$shadowexpire = readVariable($AUTH[$toPolicy]['shadowexpire'], 'numeric unsigned', 'null'); // null szöveg

	// A $SET['policyAttrs'] feldolgozása
	$attrList = array_keys($SET['policyAttrs']); 
	$valueList = array_values($SET['policyAttrs']);

	// user felvétele
	if (count($attrList) > 0) {
	    $q = "INSERT INTO accounts (
			policy, userAccount, userCn, userPassword, shadowLastChange, shadowMin, shadowMax, shadowWarning, shadowInactive, shadowExpire,
			`".implode('`, `', array_fill(0, count($attrList), '%s'))."`
		    ) VALUES (
			'%s', '%s', '%s', sha('%s'), %u, %u, %u, %u, %u, %u, '".implode("', '", array_fill(0, count($valueList), '%s'))."'
		    )";
	} else{
	    $q = "INSERT INTO accounts (
			policy, userAccount, userCn, userPassword, shadowLastChange, shadowMin, shadowMax, shadowWarning, shadowInactive, shadowExpire
		    ) VALUES ('%s', '%s', '%s', sha('%s'), %u, %u, %u, %u, %u, %u)";
	}
	$v = array_merge(
	    $attrList, 
	    array($toPolicy, $userAccount, $userCn, $userPassword, $shadowlastchange, $shadowmin, $shadowmax, $shadowwarning, $shadowinactive, $shadowexpire),
	    $valueList
	);
	$uid = db_query($q, array('fv' => 'mysqlCreateAccount', 'modul' => $modul, 'result' => 'insert', 'values' => $v), $lr);
	if ($uid === false) { db_close($lr); return false; }
	// user berakása a kategóriájának megfelelő csoportokba

        if (isset($SET['category'])) {
            if (is_array($SET['groups'])) array_unshift($SET['groups'], $SET['category']);
            else $SET['groups'] = array($SET['category']);
 
	    for ($i = 0; $i < count($SET['groups']); $i++) {
		$category = $SET['groups'][$i];
		$groupCn = kisbetus(ekezettelen($category));
		if ($category == '') continue;
		$q = "SELECT gid FROM groups WHERE groupCn='%s'";
		$gid = db_query($q, array('fv' => 'mysqlCreateAccount', 'modul' => $modul, 'result' => 'value', 'values' => array($groupCn)), $lr);
		if ($gid === false || is_null($gid)) { // --FIXME -- ez jó így BENCE radyx
		    if ($SET['createGroup']) {
			require_once('include/modules/session/createGroup.php');
			//createGroup($groupCn, "$category csoport", $category, $toPolicy = _POLICY);
			createGroup($groupCn, "$category csoport", $toPolicy = _POLICY, array('category'=>$category));
			$gid = db_query($q, array('fv' => 'mysqlCreateAccount', 'modul' => $modul, 'result' => 'value', 'values' => array($groupCn)), $lr);
		    } else {
			$_SESSION['alert'][] = 'message:wrong_data:mysqlCreateAccount - nincsmegadva/hibás kategória:'.$category.':'.$groupCn;
			db_close($lr); return false;
		    }
		}
		$q = "INSERT INTO members (uid,gid) VALUES (%u, %u)";
		$r = db_query($q, array('fv' => 'mysqlCreateAccount', 'modul' => $modul, 'values' => array($uid, $gid)), $lr);
		if (!$r) { db_close($lr); return false; }
	    }
	}
	$_SESSION['alert'][] = 'info:create_account_success:'.$userAccount;
	db_close($lr);
	return true;

    }

?>
