<?php
/*
    Module:	base/password

    Ez még teljesen kiforratlan!
    Csak átmásoltam a public-ból, és elkezdtem átírni...
*/

    if (_RIGHTS_OK !== true) die();

    $toPolicy = readVariable($_POST['toPolicy'], 'enum', readVariable($_GET['toPolicy'], 'enum', _POLICY, $POLICIES), $POLICIES);

    // Itt csak a private policy jelszavát lehet módosítani
//    $toPolicy = 'private';
    $userAccount = readVariable($_REQUEST['userAccount'], 'emptystringnull');

    // Ha saját jelszavát szeretné változtatni, akkor átirányítjuk oda
    if ($userAccount == _USERACCOUNT) {
	header('Location: '.location('index.php?policy=public&page=password&f=changePassword&toPolicy='.$toPolicy,array('lang','skin','sessionID')));
	exit;
    }

    // Jogosultság ellenőrzés
    if (
	memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup']) or
	(memberOf($userAccount,'diák') and memberOf(_USERACCOUNT,'diakadmin'))
    ) {

	// Az elküldött név+jelszó ellenőrzése
	if ($action == 'changePassword') {

		if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/password/changePassword.php')) {
    		    require('include/backend/'.$AUTH[$toPolicy]['backend'].'/password/changePassword.php');
		}

		$newPassword = readVariable($_POST['newPassword'], 'emptystringnull');
		$verification = readVariable($_POST['verification'], 'emptystringnull');

    	        if ($verification == '' or $newPassword == '') {
        	    $_SESSION['alert'][] = 'message:empty_field';
    		} elseif ($verification != $newPassword) {
    		    $_SESSION['alert'][] = 'message:pw_not_match';
    		} else {

		    if (changePassword($userAccount, $newPassword, $toPolicy)) {
			// Módosítsuk a bejelentkezett user eltárolt jelszavát? - hagyjuk inkább... legalább látja, hogy változott valami...
			// updateSessionPassword($userAccount, $toPolicy, $verification);
		    } else {
			$_SESSION['alert'][] = 'message:pw_change_failed';
		    }

		}

	} // action

    } else {
	$_SESSION['alert'][] = 'page:insufficient_access';
    }

?>
