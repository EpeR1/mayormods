<?php
/*
    Auth-LDAP

    A név-jelszó pár ellenőrzése LDAP adatbázis alapján
*/

/* --------------------------------------------------------------

    Felhasználók azonosítása LDAP-ban tárolt posixAccount
    osztályok alapján történik.

    A függvény az előre definiált _AUTH_SUCCESS, _AUTH_EXPIRED, _AUTH_FAILURE
    konstansok valamelyikével tér vissza. (include/modules/auth/base/config.php)

    Sikeres hitelesítés esetén
    az egyéb account információkat (minimálisan a 'cn', azaz 'teljes név'
    attribútumot) a cím szerint átadott $accountInformation tömbbe helyezi el.

    Sikertelen azonosítás esetén a globális $_SESSION['alert'] változóban jelzi az
    elutasítás okát.

-------------------------------------------------------------- */

######################################################################
# Az LDAP protocol version szerinti csatlakozás
######################################################################
    ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);

    function ldapUserAuthentication($userAccount, $userPassword, &$accountInformation, $toPolicy) {

	global $AUTH;

	if ($toPolicy == '') {
	    if ($accountInformation['policy'] != '') $toPolicy = $accountInformation['policy'];
//	    elseif ($_REQUEST['toPolicy'] != '') $toPolicy = $_REQUEST['toPolicy'];
	    else $toPolicy = _POLICY;
	}

	// Kapcsolódás a szerverhez
	$ds = ldap_connect($AUTH[$toPolicy]['ldap hostname']);
	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return _AUTH_FAILURE;
	}

	// Csatlakozás a szerverhez
	$r  = ldap_bind($ds);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
	    return _AUTH_FAILURE;
        }

	// Van-e adott azonosítójú felhasználó?
        $filter="(&(uid=$userAccount)(objectClass=posixAccount))";
        $justthese = array("sn","cn","studyId","shadowexpire","shadowwarning","shadowinactive","shadowlastchange","shadowmax");
        $sr = ldap_search($ds, $AUTH[$toPolicy]['ldap base dn'], $filter, $justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure";
	    ldap_close($ds);
	    return _AUTH_FAILURE;
        }
        $info=ldap_get_entries($ds,$sr);

	if ( $info['count'] === 0 ) {
            // Nincs ilyen userAccount (uid)
            $_SESSION['alert'][] = "message:no_account:$userAccount";
	    ldap_close($ds);
	    return _AUTH_FAILURE_1;
        }

	if ( $info['count'] > 1 ) {
            // Több ilyen uid is van
            $_SESSION['alert'][] = "message:multi_uid";
	    ldap_close($ds);
	    return _AUTH_FAILURE_2;
        }

        if ($info['count']==1) { // Van - egy - ilyen felhasználó

	    $accountInformation['cn'] = $info[0]['cn'][0];
	    $accountInformation['studyId'] = $info[0]['studyid'][0];
	    $accountInformation['dn'] = $info[0]['dn'];
	    $accountInformation['account'] = $userAccount;
	    // Lejárt-e
	    // A lejárat ideje a shadowExpire és shadowLastChange+shadowMax kötül a kisebbik
	    if ($info[0]['shadowexpire'][0] != '') $expireTimestamp = $info[0]['shadowexpire'][0];
	    if (
		    $info[0]['shadowmax'][0] != '' && 
		    (
			!isset($expireTimestamp) ||
			$expireTimestamp > $info[0]['shadowlastchange'][0] + $info[0]['shadowmax'][0]
		    )
	    ) $expireTimestamp = $info[0]['shadowlastchange'][0] + $info[0]['shadowmax'][0];
	    // lejárt, ha lejárat ideje már elmúlt
	    $accountExpired = (isset($expireTimestamp) && ($expireTimestamp <= floor(time()/(60*60*24))));
	    
	    // Le van-e tiltva
	    // Ha több mint shadowInactive napja lejárt
            if ( // onDisabled: none | refuse
		    $AUTH[$toPolicy]['onDisabled'] == 'refuse' &&
		    isset($expireTimestamp) &&
		    $expireTimestamp + $info[0]['shadowinactive'][0] <= floor(time()/(60*60*24))
            ) {
                // Le van tiltva
                $_SESSION['alert'][] = 'message:account_disabled';
		ldap_close($ds);
		return _AUTH_FAILURE_4;
            } // onDisabled

	    // Jelszó ellenőrzés - lehet-e csatlakozni
    	    if (!@ldap_bind($ds, $accountInformation['dn'], $userPassword)) {
        	$_SESSION['alert'][] = 'message:bad_pw';
		return _AUTH_FAILURE_3;
    	    }

	    ldap_close($ds);
	    // Lejárt-e az azonosító
	    if ($AUTH[$toPolicy]['onExpired'] != 'none' && isset($expireTimestamp)) { // onExpired: none | warning | force update
		// Lejárt-e
                $pwLejar = $expireTimestamp - floor(time()/(60*60*24));
                if (0 < $pwLejar && $pwLejar < $info[0]['shadowwarning'][0]) {
                    $_SESSION['alert'][] = 'info:account_warning:'.$pwLejar;
		    return _AUTH_SUCCESS;
                } elseif ($pwLejar <= 0) {
            	    $_SESSION['alert'][] = 'info:account_expired:'.abs($pwLejar);
		    if ($AUTH[$toPolicy]['onDisabled'] == 'refuse')
			$_SESSION['alert'][] = 'info:warn_account_disable:'.($info[0]['shadowinactive'][0]+$pwLejar);
		    if ($AUTH[$toPolicy]['onExpired'] == 'warning') {
			return _AUTH_SUCCESS;
		    } elseif ($AUTH[$toPolicy]['onExpired'] == 'force update') {
			return _AUTH_EXPIRED;
		    }
                }
            } // onExpired

	    // Ha idáig eljut, akkor minden rendben.
	    return _AUTH_SUCCESS;

        } // count == 1

    }

?>
