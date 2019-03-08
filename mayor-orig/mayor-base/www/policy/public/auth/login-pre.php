<?php

    if (_RIGHTS_OK !== true) die();

    if (defined('_ALLOW_SULIX_SSO') && _ALLOW_SULIX_SSO===true) { // kompatibilitási okokból
	$toPolicy = readVariable($_REQUEST['toPolicy'], 'enum', 'private', $POLICIES);
    } else {
	$toPolicy = 'private'; // force
    }
    $toSkin = readVariable($_POST['toSkin'], 'enum', readVariable($_GET['toSkin'], 'enum', null, $SKINSSHOW), $SKINSSHOW);
    @list($toPage,$toSub,$toF) = readVariable(explode(':',$_REQUEST['toPSF']), 'strictstring');
    $toPSF = "$toPage:$toSub:$toF";

// Ha már az adott sessionID-vel belépett az adott policy-ra, akkor ne lépjen be újra
//    if ($sessionID != '' and validUser($sessionID, $toPolicy)) {
//	header('Location: '.location("index.php?policy=$toPolicy&page=$toPage&sub=$toSub&f=$toF&sessionID=$sessionID", array('alert')));
//	die();
//    }

    if ($toPolicy=='private' && isset($_SESSION['portalLoggedUsername']) && defined('_ALLOW_SULIX_SSO') && _ALLOW_SULIX_SSO===true) {
	$action='autologin';
	//A SuliX-osok kérésére ezt sajnos kihagyjuk :( session_regenerate_id(true);
    }

    // Az elküldött név+jelszó ellenőrzése
    if ($action == 'login' || $action=='autologin') {

	// A toPolicy hibaüzenetei
	if (file_exists('include/alert/'.$lang.'/'.$AUTH[$toPolicy]['backend'].'.php')) {
	    require('include/alert/'.$lang.'/'.$AUTH[$toPolicy]['backend'].'.php');
	} elseif (file_exists('include/alert/'._DEFAULT_LANG.'/'.$AUTH[$toPolicy]['backend'].'.php')) {
	    require('include/alert/'._DEFAULT_LANG.'/'.$AUTH[$toPolicy]['backend'].'.php');
	}
	// Autentikáció - alapok
	if (file_exists('include/share/auth/base.php')) {
    	    require('include/share/auth/base.php');
	}
	// Autentikáció - toPolicy
	if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/auth/login.php')) {
    	    require('include/backend/'.$AUTH[$toPolicy]['backend'].'/auth/login.php');
	}

	// lejart session-ok torlese
	require('include/share/session/close.php');
	closeOldAndIdleSessions();

	if ($action=='autologin' && defined('_ALLOW_SULIX_SSO') && _ALLOW_SULIX_SSO===true) {
	    $userPassword = readVariable($_SESSION['portalLoggedPassword'], 'string');
	    //$userAccount  = readVariable($_SESSION['portalLoggedUsername'], 'regexp', null, array("^([a-z]|[A-Z]|[0-9]| |\.|,|_|[űáéúőóüöíŰÁÉÚŐÓÜÖÍäÄ]|-|@)*$"));
	    $userAccount  = readVariable($_SESSION['portalLoggedUsername'], 'userAccount', null);
	} else {
	    $userPassword = readVariable($_POST['userPassword'], 'string');
	    //$userAccount = readVariable($_POST['userAccount'], 'regexp', null, array("^([a-z]|[A-Z]|[0-9]| |\.|,|_|[űáéúőóüöíŰÁÉÚŐÓÜÖÍäÄ]|-|@)*$"));
	    $userAccount  = readVariable($_SESSION['portalLoggedUsername'], 'userAccount', null);
	}

	if (defined('_BOLONDOS') && _BOLONDOS===true) $userAccount = visszafele($userAccount);

	$accountInformation = array('account' => $userAccount, 'password' => $userPassword, 'policy' => $toPolicy, 'skin'=>$toSkin);
	if ($sessionID != '') $accountInformation['sessionID'] = $sessionID;

	if ($userAccount != '' and $userPassword != '') {

	    $result = userAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy); // ??? toPolicy benne van az AccountInformation-ben!!! Ldap backend only?
	    logLogin($toPolicy, $userAccount, $result);
	    if ($result === _AUTH_SUCCESS) {
		$sessionID = newSession($accountInformation, $toPolicy);
		if ($toSkin == '') $toSkin = $skin;
		header('Location: '.location("index.php?page=$toPage&sub=$toSub&f=$toF&sessionID=$sessionID&policy=$toPolicy&lang=$lang&skin=$toSkin", array('alertOLD')));
	    } elseif ($result === _AUTH_EXPIRED) {
		$_SESSION['alert'][] = 'message:force_pw_update';
		header('Location: '.location("index.php?policy=public&page=password&f=changeMyPassword&userAccount=".$userAccount."&toPolicy=$toPolicy&skin=$toSkin", array('alertOLD')));
	    } elseif ($result >= _AUTH_FAILURE) {
		// sikertelen azonosítás - a hibaüzenetet a függvény generálja
		//$NOF = @getFailedLoginCount($toPolicy,$userAccount);
		//if ($NOF>1) sleep(min($NOF,10,rand(1,10))); // harden brute force attempts
	    } else {
		// Ilyen csak hibás függvényműködés esetén lehet:
		$_SESSION['alert'][] = "message:default:hibás visszatérési érték:userAuthentication:($result)";
	    }
	} else {
	    $_SESSION['alert'][] = 'message:empty_field';
	}
    } 

?>
