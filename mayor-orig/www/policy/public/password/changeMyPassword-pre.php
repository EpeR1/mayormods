<?php

    if (_RIGHTS_OK !== true) die();

    _clearSessionCache($sessionID);
    unsetTokenCookies(); // ha valaki ide tévedne, visszavonjuk a Tokenjét és a cache-t is vissza kell

    $toPolicy = readVariable($_REQUEST['toPolicy'], 'enum', 'private', $POLICIES);
    $userAccount = readVariable($_REQUEST['userAccount'], 'emptystringnull', (defined('_USERACCOUNT'))?_USERACCOUNT:null);

    if (file_exists(_CONFIGDIR."/$toPolicy-conf.php")) {
    	require_once(_CONFIGDIR."/$toPolicy-conf.php");
    }

    if ($AUTH[$toPolicy]['enableSelfPasswordChange']) {

    // Az elküldött név+jelszó ellenőrzése
	if ($action == 'changePassword') {


    	    require_once('include/modules/auth/base/login.php');

	    if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/base.php')) {
    		require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/base.php');
	    }
	    if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/password/changePassword.php')) {
    		require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/password/changePassword.php');
	    }
	    if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/auth/login.php')) {
    		require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/auth/login.php');
	    }

	    $userPassword = readVariable($_POST['userPassword'], 'emptystringnull');
	    $newPassword = readVariable($_POST['newPassword'], 'emptystringnull');
	    $verification = readVariable($_POST['verification'], 'emptystringnull');

    	    if ($verification == '' or $newPassword == '') {
        	$_SESSION['alert'][] = 'message:empty_field';
    	    } elseif ($verification != $newPassword) {
    		$_SESSION['alert'][] = 'message:pw_not_match';
    	    } elseif ($userPassword == $newPassword) {
    		$_SESSION['alert'][] = 'message:pw_not_changed';
    	    } else {

		$result = userAuthentication($userAccount, $userPassword, $accountInformation, $toPolicy);
		if ($result >= _AUTH_FAILURE) {
    		    $_SESSION['alert'][] = 'message:auth_failure'; // megj: a hibaüzenetet a userAuthentication egyébként generálja. kell ez?
		} else {

		    if (changeMyPassword($userAccount, $userPassword, $newPassword, $toPolicy)) {
// Újra be kell jelentkezni mindenképp...
//			updateSessionPassword($userAccount, $toPolicy, $verification);
//			if (validUser($sessionID,$policy))
//			    header('Location: '.location("index.php?policy=$toPolicy&sessionID=".$sessionID, array('alert')));
//			else
			    header('Location: '.location("index.php?page=auth&f=login&toPolicy=$toPolicy", array('alert')));
		    } else {
			$_SESSION['alert'][] = 'message:pw_change_failed';
		    }
		}

	    }

	} // action

    } else {

	$_SESSION['alert'][] = 'page:pw_change_disabled';

    }

?>
