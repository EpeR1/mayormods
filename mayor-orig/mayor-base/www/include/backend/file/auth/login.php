<?php
/*
    Auth-File

    A név-jelszó pár ellenőrzése file-ból történik
*/

/* --------------------------------------------------------------

    Felhasználók azonosítása egyszerű szöveges file-ból

    A file szerkezete:
    Soronként egy account adatai, egymástól kettősponttal elválasztott mezők:
	azonosító:név:jelszó:oktAzon:shadowLastChange:shadowMin:shadowMax:shadowWarning:shadowInactive:shadowExpire

    A függvény az előre definiált _AUTH_SUCCESS, _AUTH_EXPIRED, _AUTH_FAILURE
    konstansok valamelyikével tér vissza.

    Sikeres hitelesítés esetén
    az egyéb account információkat (minimálisan a 'cn', azaz 'teljes név'
    attribútumot) a cím szerint átadott $accountInformation tömbbe helyezi el.

    Sikertelen azonosítás esetén a globális $_SESSION['alert'] változóban jelzi az
    elutasítás okát.

-------------------------------------------------------------- */
    function fileUserAuthentication($userAccount, $userPassword, &$accountInformation) {

	global $AUTH;

	$toPolicy = $accountInformation['policy'];
	$fp = @fopen($AUTH[$toPolicy]['file account file'],'r');
	if (!$fp) {
	    // nem lehet megnyitni a file-t
	    $_SESSION['alert'][] = 'message:file_open_failure:'.$AUTH[$toPolicy]['file account file'];
	    return _AUTH_FAILURE;
	}

	$valid = false;
	while (!$valid and $sor = chop(fgets($fp, 1024))) {

	    list(
		$_userAccount,
		$_userCn,
		$_userPassword,
		$_studyId,
		$shadowLastChange,
		$shadowMin,
		$shadowMax,
		$shadowWarning,
		$shadowInactive,
		$shadowExpire
	    ) = explode(':',$sor);
	    $valid = ($_userAccount == $userAccount and $_userPassword == $userPassword); // itt lehetne a kódolt jelszót eltárolni és azzal hasonlítani

	}

	fclose($fp);

	if ($valid) {

	    $accountInformation['cn'] = $_userCn;
	    $accountInformation['studyId'] = $_studyId;

	    if ( // onDisabled: none | refuse
                    $AUTH[$toPolicy]['onDisabled'] == 'refuse' &&
                    (
                        (
                            $shadowExpire != '' &&
                            $shadowExpire <= floor(time()/(60*60*24))
                        ) ||
                        (
                            $shadowLastChange != '' &&
                            $shadowMax != ''        &&
                            $shadowInactive != ''   &&
                            (   $shadowLastChange
                                + $shadowMax
                                + $shadowInactive  ) <= floor(time()/(60*60*24))
                        )
                    )
            ) {
                // Le van tiltva
                $_SESSION['alert'][] = 'message:account_disabled';
                return _AUTH_FAILURE_4;
            } // onDisabled

            // Lejárt-e az azonosító
            if (
		$AUTH[$toPolicy]['onExpired'] != 'none' && // onExpired: none | warning | force update
                $shadowLastChange != '' &&
                $shadowMax != ''
	    ) {
                // Lejárt-e
                $pwLejar = ($shadowLastChange + $shadowMax) - floor(time()/(60*60*24));
                if (0 < $pwLejar && $shadowWarning != '' && $pwLejar < $shadowWarning) {
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

	} else {

	    $_SESSION['alert'][] = 'message:bad_pw';
	    return _AUTH_FAILURE_3;

	}

    }

?>
