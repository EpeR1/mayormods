<?php

    if ($action == 'mayorGlobalLogin' || $action == 'facebooklogin' || $action== 'googleapilogin') {

        $toPolicy = readVariable($_REQUEST['toPolicy'], 'enum', 'private', $POLICIES);
        $policyOrderIndex = readVariable($_POST['policyOrderIndex'], 'id', 0);

	$__POLICYORDER[0] = array('private','parent','public');
	$__POLICYORDER[1] = array('private');
	$__POLICYORDER[2] = array('parent');
	$__POLICYORDER[3] = array('public');

	$toSkin = readVariable($_POST['toSkin'], 'enum', readVariable($_GET['toSkin'], 'enum', null, $SKINSSHOW), $SKINSSHOW);
	@list($toPage,$toSub,$toF) = readVariable(explode(':',$_REQUEST['toPSF']), 'strictstring');
	$toPSF = "$toPage:$toSub:$toF";
	// Autentikáció - alapok
	$fbAuth = false;
	if (file_exists('include/share/auth/base.php')) {
    	    require_once('include/share/auth/base.php');
	}
	require_once('include/modules/auth/base/login.php');

	// lejart session-ok torlese
	require_once('include/share/session/close.php');
	closeOldAndIdleSessions();

	if (__FBCONNECT_ENABLED===true && $action=='facebooklogin') {
	    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        	$_SESSION['alert'][] = 'info::facebook:szerver konfigurációs hiba, legalább 5.4-es php verzió szükséges';
        	return false;
	    }
	    require_once('include/share/net/facebook.php');
	    $FBDATA = mayorFacebookAuth(); // preAuth
	    if (is_array($FBDATA) && $FBDATA['accessToken']!="") {
		$userAccount = $FBDATA['userAccount'];
		$fbAuth = true;
	    }
	} elseif (__GOOGLEAPI_ENABLED===true && $action=='googleapilogin') {
	    if (version_compare(PHP_VERSION, '5.5.0', '<')) {
        	$_SESSION['alert'][] = 'info::googleapi:szerver konfigurációs hiba, legalább 5.5-ös php verzió szükséges';
        	return false;
	    }
	    require_once('include/share/net/googleapi.php');
	    $GOOGLEAPIDATA = mayorGoogleApiAuth(); // preAuth
	    if (($GOOGLEAPIDATA) && $GOOGLEAPIDATA['userAccount']!="") {
		$userAccount = $GOOGLEAPIDATA['userAccount'];
		$googleapiAuth = true;
	    }
	} else {
	    $userPassword = readVariable($_POST['userPassword'], 'string');
	    // $userAccount = readVariable($_POST['userAccount'], 'regexp', null, array("^([a-z]|[A-Z]|[0-9]| |\.|,|_|[űáéúőóüöíŰÁÉÚŐÓÜÖÍäÄ]|-|@)*$"));
	    $userAccount = readVariable($_POST['userAccount'], 'userAccount', null);
	}

	if (defined('_BOLONDOS') && _BOLONDOS===true) $userAccount = visszafele($userAccount);
	if (is_array($AUTH[$toPolicy]['allowOnly']) && !in_array($userAccount,$AUTH[$toPolicy]['allowOnly'])) $userAccount='';

	if ($sessionID != '') $accountInformation['sessionID'] = $sessionID;
	if ($userAccount != '' and ($userPassword != '' or $fbAuth===true or $googleapiAuth===true)) {

	  for ($i=0; $i<count($__POLICYORDER[$policyOrderIndex]); $i++) {
	    $toPolicy=$__POLICYORDER[$policyOrderIndex][$i];
	    if (!in_array($AUTH[$toPolicy]['authentication'],array('required','try'))) {
		continue;;
	    }
	    $accountInformation = array('account' => $userAccount, 'password' => $userPassword, 'policy' => $toPolicy, 'skin'=>$toSkin);
	    if ($fbAuth===true) {
		if ($FBDATA['toPolicy']==$toPolicy) {
		    $result = _AUTH_SUCCESS; // az authentikációt a mayorFacebookAuth() csinálta
		    $accountInformation['cn'] = $FBDATA['fbUserCn'];
		    $accountInformation['mail'] = $FBDATA['fbUserEmail'];
        	    $accountInformation['studyId'] = $FBDATA['studyId'];
		} else {
		    $_SESSION['alert'][] = 'info:A facebook azonosító nincs még összekötve! Először lépj be a MaYoR-ba, és kezdeményezd a facebook connectet!';
		    continue;;
		}
	    } elseif ($googleapiAuth===true) {
		if ($GOOGLEAPIDATA['toPolicy']==$toPolicy) {
		    $result = _AUTH_SUCCESS; // az authentikációt a mayorGoogleapiAuth() csinálta
		    $accountInformation['cn'] = $GOOGLEAPIDATA['googleUserCn'];
		    $accountInformation['mail'] = $GOOGLEAPIDATA['googleUserEmail'];
        	    $accountInformation['studyId'] = $GOOGLEAPIDATA['studyId'];
		} else {
		    $_SESSION['alert'][] = 'info:A google azonosító nincs még összekötve! Először lépj be a MaYoR-ba, és kezdeményezd!';
		    continue;;
		}
	    } else {
		$result = userAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy); // ??? toPolicy benne van az AccountInformation-ben!!! Ldap backend only?
	    }
	    logLogin($toPolicy, $userAccount, $result);

	    define('_MAYORAUTHRESULT',$result);
	    if ($result === _AUTH_SUCCESS) {
		$_SESSION['alert'] = array();
		$sessionID = newSession($accountInformation, $toPolicy);
		if ($toSkin == '') $toSkin = $skin;
		header('Location: '.location("index.php?page=$toPage&sub=$toSub&f=$toF&sessionID=$sessionID&policy=$toPolicy&lang=$lang&skin=$toSkin", array('alertOLD')));
		break;
	    } elseif ($result === _AUTH_EXPIRED) {
		$_SESSION['alert'][] = 'message:force_pw_update';
		header('Location: '.location("index.php?policy=public&page=password&f=changeMyPassword&userAccount=".$userAccount."&toPolicy=$toPolicy&skin=$toSkin", array('alertOLD')));
		break;
	    } elseif ($result === _AUTH_FAILURE_1) {
		// nincs ilyen user, megpróbáljuk beauthentikálni parent-tel is.
	    } elseif ($result >= _AUTH_FAILURE) {
		// sikertelen azonosítás - a hibaüzenetet a függvény generálja
		// megpróbáljuk beauthentikálni parent-tel is.
		break;
	    } else {
		// Ilyen csak hibás függvényműködés esetén lehet:
		$_SESSION['alert'][] = "message:default:hibás visszatérési érték:userAuthentication:(".serialize($result).")";
		break;
	    }
	  }
	} else {
	    $_SESSION['alert'][] = 'message:empty_field';
	}
    } 

?>
