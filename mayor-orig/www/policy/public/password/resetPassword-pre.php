<?php
    if (_RIGHTS_OK !== true) die();

    $selector = readVariable($_POST['selector'], 'string', readVariable($_GET['selector'], 'hexa', null));
    $validator = readVariable($_POST['validator'], 'string', readVariable($_GET['validator'], 'hexa', null));

    if ($validator!='') {
        $q = "SELECT * FROM accountRecovery WHERE selector = '%s' AND expires >= NOW()";
        $r = db_query($q, array('debug'=>false,'fv'=>'getPasswordRecoveryRequest','modul'=>'login','result'=>'record','values'=>array($selector)));
    }
    if (!is_array($r)) {
	$_SESSION['alert']['page'] = 'message:wrong_data:A jelszó-helyreállítási kérelem nem létezik, vagy lejárt!';
    } else {

	$calc = hash('sha256', hex2bin($validator));
	if (hash_equals($calc, $r['token'])) {
    	    // The reset token is valid. Authenticate the user.
	    //dump($r);
	    $ADAT = $r;
	    $ADAT['validator'] = $validator;

	    $toPolicy = $r['policy'];
	    $userAccount = $r['userAccount'];

	    if (file_exists(_CONFIGDIR."/$toPolicy-conf.php")) {
    		require_once(_CONFIGDIR."/$toPolicy-conf.php");
	    }

	    if ($AUTH[$toPolicy]['enablePasswordReset']) {

		if ($action == 'resetPassword') {

		    if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/base.php')) {
    			require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/base.php');
		    }
		    if (file_exists('include/backend/'.$AUTH[$toPolicy]['backend'].'/password/changePassword.php')) {
    			require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/password/changePassword.php');
		    }

		    $newPassword = readVariable($_POST['newPassword'], 'emptystringnull');
		    $verification = readVariable($_POST['verification'], 'emptystringnull');

    		    if ($verification == '' or $newPassword == '') {
        		$_SESSION['alert'][] = 'message:empty_field';
    		    } elseif ($verification != $newPassword) {
    			$_SESSION['alert'][] = 'message:pw_not_match';
    		    } else {
			if (changePassword($userAccount, $newPassword, $toPolicy)) {
    			    $q = "DELETE FROM accountRecovery WHERE userAccount = '%s'";
    			    db_query($q, array('debug'=>false,'fv'=>'getPasswordRecoveryRequest','modul'=>'login','result'=>'delete','values'=>array($userAccount)));
			    header('Location: '.location("index.php?page=auth&f=login&toPolicy=$toPolicy", array('alert')));
			} else {
			    $_SESSION['alert'][] = 'message:pw_change_failed';
			}
		    }
		}

	    } else {
		$_SESSION['alert'][] = 'page:pw_reset_disabled';
	    }

	} else {
	    $_SESSION['alert']['page'] = 'message:insufficient_access:A jelszó-helyreállítási kérelem nem érvényes!';
	}

    }

?>
