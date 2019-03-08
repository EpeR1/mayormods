<?php

    function generatePasswordRecoveryRequest($accountData) {

	$URL = 'https://'.$_SERVER['SERVER_NAME'].'/index.php?page=password&f=resetPassword&';

	if (version_compare(PHP_VERSION,'5.3.0')>=0) {
	    $selector = bin2hex(openssl_random_pseudo_bytes(8));
	    $token = openssl_random_pseudo_bytes(32);
	} elseif (version_compare(PHP_VERSION,'7.0.0')>=0) {
	    $selector = bin2hex(random_bytes(8));
	    $token = random_bytes(32);
	} else {
	    return false; // nem támogatjuk
	}
	$urlToEmail = href($URL.http_build_query(array(
	    'selector' => $selector,
	    'validator' => bin2hex($token)
	),'','&'));

	$expires = new DateTime('NOW');
	$expires->add(new DateInterval('PT01H')); // 1 hour

	// rate limiting és karbantartás
	$lr = db_connect('login');
	db_start_trans($lr);

	$q = "DELETE FROM accountRecovery WHERE expires <= NOW() - INTERVAL 10 DAY";
	db_query($q, array('debug'=>false,'fv' => 'generatePasswordRecoveryRequest', 'modul'=>'login', 'result'=>'delete'),$lr);

	$q = "SELECT count(*) as db FROM accountRecovery WHERE policy='%s' AND userAccount='%s'";
	$v = array($accountData['policy'], $accountData['userAccount']);
	$recoveryRequestDb = db_query($q, array('debug'=>false,'fv' => 'generatePasswordRecoveryRequest', 'modul'=>'login', 'result'=>'value', 'values'=>$v),$lr);

	if ($recoveryRequestDb<5) {
	    $q = "INSERT INTO accountRecovery (policy, userAccount, selector, token, expires) VALUES ('%s', '%s', '%s', '%s', '%s');";
	    $v = array($accountData['policy'], $accountData['userAccount'], 
		$selector, 
		hash('sha256', $token), 
		$expires->format('Y-m-d\TH:i:s'));
	    $recoveryId = db_query($q, array('debug'=>false,'fv' => 'generatePasswordRecoveryRequest', 'modul'=>'login', 'result'=>'insert', 'values'=>$v),$lr);
	} else {
	    return false;
	}
	db_commit($lr);
	db_close($lr);

	if ($recoveryId !== false) return $urlToEmail;
	else return false;

    }

?>