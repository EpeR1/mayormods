<?php
/*
    Auth-ADS

    A név-jelszó pár ellenőrzése Active Directory adatbázis alapján
*/

/* --------------------------------------------------------------

    Felhasználók azonosítása az AD-ban tárolt person (konfigurálható)
    osztályok alapján történik.

    A függvény az előre definiált _AUTH_SUCCESS, _AUTH_EXPIRED, _AUTH_FAILURE
    konstansok valamelyikével tér vissza. (include/modules/auth/base/config.php)

    Sikeres hitelesítés esetén
    az egyéb account információkat (minimálisan a 'cn', azaz 'common name'
    attribútumot) a cím szerint átadott $accountInformation tömbbe helyezi el.

    Sikertelen azonosítás esetén a globális $_SESSION['alert'] változóban jelzi az
    elutasítás okát.

-------------------------------------------------------------- */

######################################################################
# Az LDAP protocol version 3 kötelező, 
# referals=0 nélkül használhatatlanul lassú
######################################################################

    ldap_set_option(NULL, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option(NULL, LDAP_OPT_REFERRALS, 0);

    /**
     * A userAccountControl pár fontos flag-e:
     * 
     * Forrás: http://msdn.microsoft.com/en-us/library/windows/desktop/ms680832%28v=vs.85%29.aspx
     * 
     *	512 	Enabled Account
     *	514 	Disabled Account
     *	544 	Enabled, Password Not Required
     *	546 	Disabled, Password Not Required
     *	66048 	Enabled, Password Doesn't Expire
     *	66050 	Disabled, Password Doesn't Expire
     *	66080 	Enabled, Password Doesn't Expire & Not Required
     *	66082 	Disabled, Password Doesn't Expire & Not Required
     *	590336	Enabled, User Cannot Change Password, Password Never Expires
     *
     * Ha pwdLastSet=0 és UF_DONT_EXPIRE_PASSWD=0, akkor következő bejelentkezéskor jelszót _kell_ változtatni.
    **/
    define('ADS_UF_ACCOUNTDISABLE',0x00000002); // The user account is disabled.
    define('ADS_UF_PASSWD_NOTREQD',0x00000020); // No password is required.
    define('ADS_UF_PASSWD_CANT_CHANGE',0x00000040); // The user cannot change the password. 
    define('ADS_UF_ENCRYPTED_TEXT_PASSWORD_ALLOWED',0x00000080); // The user can send an encrypted password.
    define('ADS_UF_NORMAL_ACCOUNT',0x00000200); // This is a default account type that represents a typical user.
    define('ADS_UF_DONT_EXPIRE_PASSWD',0x00010000); // The password for this account will never expire.
    define('ADS_UF_PASSWORD_EXPIRED',0x00800000); // The user password has expired.

    /**
     * Ha az accountExpires = 0 or 0x7FFFFFFFFFFFFFFF (9223372036854775807), akkor az account sose jár le. (nem a jelszó! az account.)
    **/
    define('ADS_ACCOUNTEXPIRES_NEVER','9223372036854775807');

    /**
     * Forrás: http://msdn.microsoft.com/en-us/library/windows/desktop/ms724284%28v=VS.85%29.aspx
     *  - unixDays 	- Az eltelt napok száma 1970-01-01-től
     *  - unixTimestamp - Az eltelt másodpercek száma 1970-01-01 00:00:00-től
     *  - msFileTime	- A 1601-01-01 00:00:00-tól elteltt 100 nanosecundum-os intervallumok száma (1/10000000 sec)
    **/
    function msFileTime2unixDays($pwdLastSet) {
	return floor((($pwdLastSet / 10000000) - 11644406783) / 86400);
    }
    function msFileTime2unixTimestamp($pwdLastSet) {
	return bcsub(bcdiv($pwdLastSet, '10000000'), '11644473600');
    }

    function getAccountStatus($userAccount, $toPolicy, $userinfo, $ds) {

    /**
     * Meghatározza a felhasználói jelszó lejárati dátumát és az account egyéb fontos jellemzőit
     * 
     * @params: $userAccount - a lekérdezendő account
     * @params: $userinfo - A user adatait tartalmazó korábbi LDAP lekérdezés eredménye (useraccountcontrol, pwdlastchange)
     * @params: $ds - LDAP csatlakozás azonosító
     * @requires: bcmath http://www.php.net/manual/en/book.bc.php
     *     MSDN: http://msdn.microsoft.com/en-us/library/ms974598.aspx - a pwdLastSet 64 bites integer
     * @return:  array
     * @param book $isGUID Is the username passed a GUID or a samAccountName
    **/
	global $AUTH;

	if ($toPolicy == '') $toPolicy = _POLICY;
	if (!function_exists('bcmod')) { 
	    $_SESSION['alert'][] = 'message:system_error:Nem támogatott függvényhívás [bcmod]! http://www.php.net/manual/en/book.bc.php';
	    return false;
	};

	if (!$ds) {
	    $closeLDAP = true;
	    // Csatlakozzunk az LDAP kiszolgálóhoz!
	    // Kapcsolódás a szerverhez
	    $ds = ldap_connect($AUTH[$toPolicy]['adsHostname']);
	    if (!$ds) {
    		$_SESSION['alert'][] = 'alert:ldap_connect_failure';
    		return false;
	    }

	    // Csatlakozás a szerverhez
	    $r  = @ldap_bind($ds,$AUTH[$toPolicy]['adsUser'],$AUTH[$toPolicy]['adsPw']);
    		if (!$r) {
        	$_SESSION['alert'][] = 'message:ldap_bind_failure';
		return false;
    	    }
	}

	if (!is_array($userinfo)) {
	    // Kérdezzük le az account adatait!
    	    $filter="(&(sAMAccountName=$userAccount)(objectClass=".$AUTH[$toPolicy]['adsUserObjectClass']."))";
    	    $justthese = array("sn","cn",$AUTH[$toPolicy]['adsStudyIdAttr'],"shadowexpire","shadowwarning","shadowinactive","shadowlastchange","shadowmax","pwdlastset","accountexpires","useraccountcontrol");
    	    $sr = ldap_search($ds, $AUTH[$toPolicy]['adsBaseDn'], $filter, $justthese);
    	    if (!$sr) {
        	$_SESSION['alert'][] = "message:ldap_search_failure";
		if ($closeLDAP) ldap_close($ds);
		return false;
    	    }
    	    $userinfo = ldap_get_entries($ds,$sr);
	    if ( $userinfo['count'] === 0 || is_null($userinfo)) { // http://bugs.php.net/50185 ha nincs megfelelő elem, akkor - hibásan - null-al tér vissza! (~ PHP 5.2.10)
        	// Nincs ilyen userAccount (uid)
        	$_SESSION['alert'][] = "message:no_account:$userAccount";
		if ($closeLDAP) ldap_close($ds);
		return false;
    	    }
	    if ( $userinfo['count'] > 1 ) {
        	// Több ilyen uid is van
        	$_SESSION['alert'][] = "message:multi_uid";
		if ($closeLDAP) ldap_close($ds);
		return false;
    	    }
	}
	$pwdlastset = $userinfo[0]['pwdlastset'][0];
	$userAccountControl = $userinfo[0]['useraccountcontrol'][0];

	$status = array();
     
	$status['pwdLastSet'] = $pwdlastset;
	$status['pwdLastSetDt'] = date('Y-m-d H:i:s',msFileTime2unixTimestamp($pwdlastset));
	$status['accountExpires'] = $userinfo[0]['accountexpires'][0];
	$status['accountNeverExpires'] = (ADS_ACCOUNTEXPIRES_NEVER==$userinfo[0]['accountexpires'][0]) || ($userinfo[0]['accountexpires'][0] == 0);
	if (!$status['accountNeverExpires']) {
	    $status['accountExpiresDt'] = date('Y-m-d H:i:s',msFileTime2unixTimestamp($userinfo[0]['accountexpires'][0]));
	    $status['accountExpiresTimestamp'] = msFileTime2unixTimestamp($userinfo[0]['accountexpires'][0]);
	}
	$status['accountDisabled'] 	= (bool)($userAccountControl & ADS_UF_ACCOUNTDISABLE); 
	$status['noPasswordRequired'] 	= (bool)($userAccountControl & ADS_UF_PASSWD_NOTREQD);
	$status['cannotChangePassword'] = (bool)($userAccountControl & ADS_UF_PASSWD_CANT_CHANGE);
	$status['normalAccount'] 	= (bool)($userAccountControl & ADS_UF_NORMAL_ACCOUNT);
	$status['passwordNeverExpire'] 	= (bool)($userAccountControl & ADS_UF_DONT_EXPIRE_PASSWD);
	$status['passwordExpired'] 	= (bool)($userAccountControl & ADS_UF_PASSWORD_EXPIRED); // Ez mintha nem működne...
	$status['mustChangePassword'] = ($pwdlastset === '0' && $status['passwordNeverExpire']);

         // A jelszó lejárati dátum az AD-ben két értékből számítható ki:
         //   - A felhasználó saját pwdLastSet atribútuma: ez tárolja a jelszó utolsó módosításának időpontját
         //   - A tartomány maxPwdAge atribútuma: milyen hosszú ideig lehet érvényes a jelszó a tartományban
         //
         // A Microsoft persze saját kiindulási időpontot és lépési egységet használ az idő tárolására.
         // Ez a függvény konvertálja ezt az értéket Unix időbélyeggé

	// Kérdezzük le a tartomány maxPwdAge attribútumát!
        $sr = ldap_read($ds, $AUTH[$toPolicy]['adsBaseDn'], 'objectclass=domain', array('maxPwdAge'));
        if (!$sr) {
            $_SESSION['alert'][] = "message:ldap_search_failure:getAccountStatus (ads backend)";
            if ($closeLDAP) ldap_close($ds);
            return false;
        }
        $info = ldap_get_entries($ds, $sr);
        $maxpwdage = $info[0]['maxpwdage'][0];

         // Lásd MSDN: http://msdn.microsoft.com/en-us/library/ms974598.aspx
         //
         // pwdLastSet tartalmazza az 1601 (UTC) január 1 óta eltelt 100 nanoszekundumos időintervallumok számát
         // 64 bit-es integer típusú értékként
         //
         // Ettől az időponttól a Unix időszámítás kezdetéig eltelt másodpercek száma 11644473600.
         //
         // maxPwdAge szintén large integer, ami a jelszóváltoztatás és a jelszó lejárat közötti 100 nanoszekundumos időintervallumok számát tárolja

	$status['maxPwdAgeInDays'] = bcdiv(bcsub(0,$maxpwdage),'36000000000')/24;

         // Ezt az étéket át kell váltanunk másodpercekre, de ez egy negatív mennyiség!
         //
	 // Ha a maxPwdAge alsó 32 bites része 0, akkor a jelszavak nem járnak le
         //
	 // Sajnos ezek a számok túl nagyok a PHP integer típusához, ezért kell a BCMath függvényeit használnunk

	$status['passwordsDoNotExpireInDomain'] = (bcmod($maxpwdage, 4294967296) === '0');

	// Adjuk össze a pwdlastset és maxpwdage értékeket (pontosabban az utóbbi negatív értéket
	// vonjuk ki az előbbiből), így megkapjuk a jelszó lejáratának időpontját a Microsoft féle
	// egységekben.
        $pwdexpire = bcsub($pwdlastset, $maxpwdage);
        
        // Konvertáljuk az MS féle időt unix időre
        $status['expiryTimestamp'] = bcsub(bcdiv($pwdexpire, '10000000'), '11644473600');
        $status['expiryDate'] = date('Y-m-d H:i:s', bcsub(bcdiv($pwdexpire, '10000000'), '11644473600'));

	if ($closeLDAP) ldap_close($ds);

	$status['userAccount'] = $userAccount;
	$status['usetAccountControl'] = $userAccountControl;
	$status['shadowLastChange'] = $userinfo[0]['shadowlastchange'][0];
	$status['shadowWarning'] = $userinfo[0]['shadowwarning'][0];
	$status['shadowInactive'] = $userinfo[0]['shadowinactive'][0];
        return array_merge($status);

	
    }

    function adsUserAuthentication($userAccount, $userPassword, &$accountInformation, $toPolicy) {

	global $AUTH;

	if ($toPolicy == '') {
	    if ($accountInformation['policy'] != '') $toPolicy = $accountInformation['policy'];
//	    elseif ($_REQUEST['toPolicy'] != '') $toPolicy = $_REQUEST['toPolicy'];
	    else $toPolicy = _POLICY;
	}

	// Kapcsolódás a szerverhez
	$ds = ldap_connect($AUTH[$toPolicy]['adsHostname']);
	if (!$ds) {
    	    $_SESSION['alert'][] = 'alert:ldap_connect_failure';
    	    return _AUTH_FAILURE;
	}

	// Csatlakozás a szerverhez
	$r  = @ldap_bind($ds,$AUTH[$toPolicy]['adsUser'],$AUTH[$toPolicy]['adsPw']);
        if (!$r) {
            $_SESSION['alert'][] = 'message:ldap_bind_failure';
	    return _AUTH_FAILURE;
        }

	// Van-e adott azonosítójú felhasználó?
        $filter="(&(sAMAccountName=$userAccount)(objectClass=".$AUTH[$toPolicy]['adsUserObjectClass']."))";
        $justthese = array("sn","cn",$AUTH[$toPolicy]['adsStudyIdAttr'],"shadowexpire","shadowwarning","shadowinactive","shadowlastchange","shadowmax","pwdlastset","accountexpires","useraccountcontrol");
        $sr = ldap_search($ds, $AUTH[$toPolicy]['adsBaseDn'], $filter, $justthese);
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

	    $status = getAccountStatus($userAccount, $toPolicy, $info, $ds);
	    // Lejárt-e
	    // A lejárat ideje a shadowExpire és shadowLastChange+shadowMax kötül a kisebbik
	    // Esetünkben
	    if ($info[0]['pwdlastset'][0] != '') { // A pwdLastSet és shadowLastChange közül a kisebbiket használjuk
		    $info[0]['shadowlastchange'][0] = msFileTime2unixDays($info[0]['pwdlastset'][0]);
    	    }

	    // A globális beállítással kikényszeríthető a nagyobb warning időszak	    
	    $shadowWarning = ($status['shadowWarning']<$AUTH[$toPolicy]['shadowWarning']) ? $AUTH[$toPolicy]['shadowWarning'] : $status['shadowWarning'];


	    $disabled = ( // Ha az jelszavak lejárhatnak a domain-ben és a user jellszava is lejárhat és le is járt...
		!$status['passwordNeverExpire']
		&& !$status['passwordsDoNotExpireInDomain']
		&& $status['expiryTimestamp'] < time()
	    ) || ( // vagy az account lejárhat és le is járt
		!$status['accountNeverExpires']
		&& $status['accountExpiresTimestamp']<time()
	    ); // Akkor már nem lehet belépni/jelszót változtatni...
	    $expired = ( // Ha a jelszavak lejárhatnak és a user jelszava is lejárhat, és shadowwarning-on belül le fog járni a jelszó
		!$status['passwordNeverExpire']
		&& !$status['passwordsDoNotExpireInDomain']
		&& $status['expiryTimestamp'] - ($shadowWarning*24*60*60) < time()
	    ) || ( // Ha az account lejárhat és shadow warning-on belül le is fog járni az account
		!$status['accountNeverExpires']
		&& $status['accountExpiresTimestamp'] - ($shadowWarning*24*60*60) < time()
	    ); // ...

	    /**
	     * Más backend-ben csak $AUTH[$toPolicy]['onDisabled'] == 'refuse' esetén utasítanánk el, de itt nincs más lehetőség...
	    **/
	    if ($disabled) {
                $_SESSION['alert'][] = 'message:account_disabled';
		ldap_close($ds);
		return _AUTH_FAILURE_4;
	    }

	    $accountInformation['cn'] = $info[0]['cn'][0];
	    $accountInformation['studyId'] = $info[0][ $AUTH[$toPolicy]['adsStudyIdAttr'] ][0];
	    $accountInformation['dn'] = $info[0]['dn'];
	    $accountInformation['account'] = $userAccount;
	    // Jelszó ellenőrzés - lehet-e csatlakozni
    	    if (!@ldap_bind($ds, $accountInformation['dn'], $userPassword)) {
        	$_SESSION['alert'][] = 'message:bad_pw';
		return _AUTH_FAILURE_3;
    	    }

	    ldap_close($ds);
	    if (!$expired || $AUTH[$toPolicy]['onExpired'] == 'none') {
		return _AUTH_SUCCESS;
	    } else {
		$pwLejar = floor(($status['expiryTimestamp'] - time()) / 86400);
            	$_SESSION['alert'][] = 'info:account_warning:'.$pwLejar;
		$_SESSION['alert'][] = 'info:warn_account_disable:'.$pwLejar; // más backend esetén csak onDisable=refuse esetén szoktuk...
		if ($AUTH[$toPolicy]['onExpired'] == 'warning') {
		    return _AUTH_SUCCESS;
		} elseif ($AUTH[$toPolicy]['onExpired'] == 'force update') {
		    return _AUTH_EXPIRED;
		} else {
		    return _AUTH_FAILURE;
		}
	    }

/*
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
*/
        } // count == 1

    }

?>
