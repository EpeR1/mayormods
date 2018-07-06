<?php
/*
    Auth-LDAP-NG

    A név-jelszó pár ellenőrzése LDAP adatbázis alapján
*/

/* --------------------------------------------------------------

    Felhasználók azonosítása az LDAP-ban tárolt konfigurálható
    osztályok alapján történik.

    A függvény az előre definiált _AUTH_SUCCESS, _AUTH_EXPIRED, _AUTH_FAILURE
    konstansok valamelyikével tér vissza. (include/modules/auth/base/config.php)

    Sikeres hitelesítés esetén
    az egyéb account információkat (minimálisan a 'cn', azaz 'common name'
    attribútumot) a cím szerint átadott $accountInformation tömbbe helyezi el.

    Sikertelen azonosítás esetén a globális $_SESSION['alert'] változóban jelzi az
    elutasítás okát (ldap_connect_failure, ldap_bind_failure, ldap_search_failure, no_account, multi_uid, 
    account_disabled, bad_pw, account_warning, account_expired, warn_account_disable.

-------------------------------------------------------------- */

######################################################################
# Az LDAP protocol version 3 kötelező, 
# referals=0 nélkül használhatatlanul lassú
######################################################################

    ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option(NULL, LDAP_OPT_REFERRALS, 0);


    function ldap_ngUserAuthentication($userAccount, $userPassword, &$accountInformation, $toPolicy) {

	global $AUTH;

	if ($toPolicy == '') {
	    if ($accountInformation['policy'] != '') $toPolicy = $accountInformation['policy'];
//	    elseif ($_REQUEST['toPolicy'] != '') $toPolicy = $_REQUEST['toPolicy'];
	    else $toPolicy = _POLICY;
	}

	// Kapcsolódás a szerverhez
	$ds = ldap_connect($AUTH[$toPolicy]['ldapHostname']);
	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return _AUTH_FAILURE;
	}

	// Csatlakozás a szerverhez
	$r  = @ldap_bind($ds,$AUTH[$toPolicy]['ldapUser'],$AUTH[$toPolicy]['ldapPw']);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
	    return _AUTH_FAILURE;
        }

	// Van-e adott azonosítójú felhasználó?
        $filter="(&(".$AUTH[$toPolicy]['ldapUserAccountAttr']."=$userAccount)(objectClass=".$AUTH[$toPolicy]['ldapUserObjectClass']."))";
        $justthese = array("sn",$AUTH[$toPolicy]['ldapCnAttr'],$AUTH[$toPolicy]['ldapStudyIdAttr'],"shadowexpire","shadowwarning","shadowinactive","shadowlastchange","shadowmax");
        $sr = ldap_search($ds, $AUTH[$toPolicy]['ldapBaseDn'], $filter, $justthese);
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure";
	    ldap_close($ds);
	    return _AUTH_FAILURE;
        }
        $info = ldap_get_entries($ds,$sr);

	if ( $info['count'] === 0 || is_null($info)) { // http://bugs.php.net/50185 ha nincs megfelelő elem, akkor - hibásan - null-al tér vissza! (~ PHP 5.2.10)
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


	    $accountInformation['cn'] = $info[0][ $AUTH[$toPolicy]['ldapCnAttr'] ][0];
	    $accountInformation['studyId'] = $info[0][ $AUTH[$toPolicy]['ldapStudyIdAttr'] ][0];

	    $accountInformation['dn'] = $info[0]['dn'];
	    $accountInformation['account'] = $userAccount;
	    // Lejárt-e
	    // A lejárat ideje a shadowExpire és shadowLastChange+shadowMax kötül a kisebbik
	    if ($info[0]['pwdlastset'][0] != '') { // A pwdLastSet és shadowLastChange közül a kisebbiket használjuk
//		if ($info[0]['shadowlastchange'][0] != '')
//		    $info[0]['shadowlastchange'][0] = min(pwdLastSet2shadowLastChange($info[0]['pwdlastset'][0]), $info[0]['shadowlastchange'][0]);
//		else
		    $info[0]['shadowlastchange'][0] = pwdLastSet2shadowLastChange($info[0]['pwdlastset'][0]);
	    }
	    if ($info[0]['accountexpires'][0] != '') { // Az accountExpires és a shadowExpire közül a kisebbiket használjuk
//		if ($info[0]['shadowexpire'][0] != '')
//		    $info[0]['shadowexpire'][0] = min(pwdLastSet2shadowLastChange($info[0]['accountexpires'][0]), $info[0]['shadowexpire'][0]);
//		else
		    $info[0]['shadowexpire'][0] = pwdLastSet2shadowLastChange($info[0]['accountexpires'][0]);
	    }
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
		    if ($AUTH[$toPolicy]['onDisabled'] == 'refuse') $_SESSION['alert'][] = 'info:warn_account_disable:'.($info[0]['shadowinactive'][0]+$pwLejar);
		    if ($AUTH[$toPolicy]['onExpired'] == 'warning') {
			return _AUTH_SUCCESS;
		    } elseif ($AUTH[$toPolicy]['onExpired'] == 'force update') {
			return _AUTH_EXPIRED;
		    } else {
			return _AUTH_FAILURE;
		    }
                }
            } // onExpired
	    // Ha idáig eljut, akkor minden rendben.
	    return _AUTH_SUCCESS;

        } // count == 1

    }

?>
