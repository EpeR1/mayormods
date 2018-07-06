<?php
/*
 Auth-MySQL

 A név-jelszó pár ellenőrzése MySQL adattábla alapján
 */

/* --------------------------------------------------------------

Az adattábla szerkezete:

create table userAccounts (
userId int unsigned primary key auto_increment not null,
userAccount varchar(32),
policy varchar(10),
userPassword varchar(32),
userCn varchar(64)
);

A függvény az előre definiált _AUTH_SUCCESS, _AUTH_EXPIRED, _AUTH_FAILURE
konstansok valamelyikével tér vissza.

Sikeres hitelesítés esetén
az egyéb account információkat (minimálisan a 'cn', azaz 'teljes név
attribútumot) a cím szerint átadott $accountInformation tömbbe helyezi el.

Sikertelen azonosítás esetén a globális $_SESSION['alert'] változóban jelzi az
elutasítás okát.

Shadow attribútumok:

Login name
Encrypted password
shadowLastChanged
1970. január 1-étől az utolsó jelszó módosításig eltelt napok száma
Days since Jan 1, 1970 that password was last changed
shadowMin
Jelszóváltoztatás után ennyi napig nem lehet ismét jelszót változtatni
Days before password may be changed
shadowMax
Jelszóváltoztatás után ennyi nappal már kötelező a jelszóváltoztatás
Days after which password must be changed
shadowWarning
A jelszó érvényességének lejártát ennyi nappal előbb jelezi a rendsze
Days before password is to expire that user is warned
shadowInactive
A jelszó érvényességének lejárta után ennyi nappal az felhasználói fiók letiltásra kerül
Days after password expires that account is disabled
shadowExpire
Az előzőektől függetlenül a felhasználói fiók letiltásra kerül 1970. január 1-étől számított ennyiedik napo
Days since Jan 1, 1970 that account is disabled

-------------------------------------------------------------- */

function mysqlUserAuthentication($userAccount, $userPassword, &$accountInformation, $toPolicy = _POLICY) {

	global $AUTH;

	$modul = "$toPolicy auth";
	$lr = db_connect($modul, array('fv' => 'userAuthentication/sql'));
	if (!$lr) return _AUTH_FAILURE;

	// Van-e ilyen azonosító
	$q = "SELECT COUNT(*) FROM accounts WHERE userAccount='%s' AND policy='%s'";
	$num = db_query($q, array('fv' => 'userAuthentication', 'modul' => $modul, 'result' => 'value', 'values' => array($userAccount, $toPolicy)), $lr);
	if ($num == 0) {
		// Nincs ilyen azonosító
		$_SESSION['alert'][] = 'message:no_account:'."$userAccount:$toPolicy";
		db_close($lr);
		return _AUTH_FAILURE_1;
	} elseif ($num > 1) {
		// Több ilyen azonosító is va
		$_SESSION['alert'][] = 'message:multy_uid';
		db_close($lr);
		return _AUTH_FAILURE_2;
	}

	// Ha csak egy van, akkor jó-e a jelszava
	$q = "SELECT userCn, studyId, shadowLastChange, shadowMin, shadowMax, shadowWarning, shadowInactive, shadowExpire
		FROM accounts WHERE userAccount='%s' AND userPassword=sha('%s')	AND policy='%s'";
	$ret = db_query($q, array('fv' => 'userAuthentication', 'modul' => 'login', 'result' => 'record', 'values' => array($userAccount, $userPassword, $toPolicy)), $lr);
	db_close($lr);
	if  (!is_array($ret) || count($ret) == 0) {
		// Nincs ilyen rekord => rossz a jelszó
		$_SESSION['alert'][] = 'message:bad_pw';
		return _AUTH_FAILURE_3;
	} else {
	    // Ha van, akkor csak egy ilyen sor lehet
	    $accountInformation['cn'] = $ret['userCn'];
	    $accountInformation['studyId'] = $ret['studyId'];
	    $shadowLastChange = $ret['shadowLastChange'];
	    $shadowMin = $ret['shadowMin'];
	    $shadowMax = $ret['shadowMax'];
	    $shadowWarning = $ret['shadowWarning'];
	    $shadowInactive = $ret['shadowInactive'];
	    $shadowExpire = $ret['shadowExpire'];

            // A lejárat ideje a shadowExpire és shadowLastChange+shadowMax kötül a kisebbik
            if (intval($shadowExpire) != 0) $expireTimestamp = $shadowExpire;
            if (
                    intval($shadowMax) != 0 &&
                    (
                        !isset($expireTimestamp) ||
                        $expireTimestamp > $shadowLastChange + $shadowMax
                    )
            ) $expireTimestamp = $shadowLastChange + $shadowMax;
            // lejárt, ha lejárat ideje már elmúlt
            $accountExpired = (isset($expireTimestamp) && ($expireTimestamp <= floor(time()/(60*60*24))));

	    // Le van-e tiltva
	    if ( // onDisabled: none | refuse
                    $AUTH[$toPolicy]['onDisabled'] == 'refuse' &&
                    isset($expireTimestamp) &&
                    $expireTimestamp + $shadowInactive <= floor(time()/(60*60*24))
	    ) {
		    // Le van tiltva
		    $_SESSION['alert'][] = 'message:account_disabled:'.strval(floor(time()/(60*60*24)));
		    return _AUTH_FAILURE_4;
	    } // onDisabled

	    // Lejárt-e az azonosító
	    if ($AUTH[$toPolicy]['onExpired'] != 'none' && isset($expireTimestamp)) { // onExpired: none | warning | force update
		    // Lejárt-e
		    $pwLejar = $expireTimestamp - floor(time()/(60*60*24));
		    if (0 < $pwLejar && $pwLejar < $shadowWarning) {
			    $_SESSION['alert'][] = 'info:account_warning:'.$pwLejar;
			    return _AUTH_SUCCESS;
		    } elseif ($pwLejar <= 0) {
			    $_SESSION['alert'][] = 'info:account_expired:'.abs($pwLejar);
			    if ($AUTH[$toPolicy]['onDisabled'] == 'refuse')
				    $_SESSION['alert'][] = 'info:warn_account_disable:'.($shadowInactive+$pwLejar);
			    if ($AUTH[$toPolicy]['onExpired'] == 'warning') {
				    return _AUTH_SUCCESS;
			    } elseif ($AUTH[$toPolicy]['onExpired'] == 'force update') {
				    return _AUTH_EXPIRED;
			    }
		    }
	    } // onExpired
	    return _AUTH_SUCCESS;

	}
}

?>
