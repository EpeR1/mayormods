<?php

    function generateAuthToken($accountData) {

	if (!defined('AUTHTOKENENABLED') || AUTHTOKENENABLED!==true) return false;

	if (version_compare(PHP_VERSION,'5.3.0')>=0) {
	    $selector = bin2hex(openssl_random_pseudo_bytes(8));
	    $token = openssl_random_pseudo_bytes(32);
	} elseif (version_compare(PHP_VERSION,'7.0.0')>=0) {
	    $selector = bin2hex(random_bytes(8));
	    $token = random_bytes(32);
	} else {
	    return false; // nem támogatjuk
	}

	if (isset($_COOKIE['t_selector'])===true && isset($_COOKIE['t_validator'])===true) return true; // már van selector/validator elmentve 

	$lr = db_connect('login');
	db_start_trans($lr);

	$q = "DELETE FROM authToken WHERE expires <= NOW() - INTERVAL 10 DAY";
	db_query($q, array('debug'=>false,'fv' => 'na', 'modul'=>'login', 'result'=>'delete'),$lr);

	$q = "INSERT INTO authToken (policy, userAccount, 
	    userCn, studyId,
	    selector, token, expires, activity, ipAddress) VALUES ('%s', '%s', '%s', '%s', '%s','%s',NOW() + INTERVAL 30 DAY,NOW(),'%s')";
	$v = array($accountData['policy'], $accountData['userAccount'], 
		$accountData['userCn'], $accountData['studyId'],
		$selector, 
		hash('sha256', $token),
		CLIENTIPADDRESS
	);
	$Id = db_query($q, array('debug'=>false,'fv' => 'na', 'modul'=>'login', 'result'=>'insert', 'values'=>$v),$lr);
	db_commit($lr);
	db_close($lr);

	if ($Id !== false) {
	    setcookie('t_selector',$selector,time()+604800*5,'/','',TRUE,TRUE);
	    setcookie('t_validator',bin2hex($token),time()+604800*5,'/','',TRUE,TRUE);
	    $_SESSION['mayorapiauth'] = true;
	    return true;
	} else {
	    return false;
	}
    }

    function unsetTokenCookies() { // + MS_*
	$selector = readVariable($_COOKIE['t_selector'], 'string', readVariable($_GET['t_selector'], 'hexa', null));
	if ($selector!='') {
	    $q = "DELETE FROM authToken WHERE selector='%s'";
	    $values = array($selector);
	    db_query($q, array('debug'=>false,'fv' => 'na', 'modul'=>'login', 'result'=>'delete', 'values'=>$values),$lr);
	}
        setcookie('t_selector','',time() - 3600,'/','',TRUE,TRUE);
        setcookie('t_validator','',time() - 3600,'/','',TRUE,TRUE);
	if (is_array($_COOKIE)) {
	    foreach($_COOKIE as $key => $value) {
		if (substr($key,0,3) == 'MS_') {
    		    setcookie($key,'',time() - 3600,'/','',TRUE,TRUE);
		}
	    }
	}
	$_SESSION['mayorapiauth'] = false;
    }

    function mayorApiAuth() {

	// $MAYORAPIDATA tömb feltöltése
            $selector = readVariable($_COOKIE['t_selector'], 'string', readVariable($_GET['t_selector'], 'hexa', null));
            $validator = readVariable($_COOKIE['t_validator'], 'string', readVariable($_GET['t_validator'], 'hexa', null));
            if ($selector!='' && $validator!='') {
                $q = "SELECT * FROM authToken WHERE selector = '%s' AND expires >= NOW()";
                $r = db_query($q, array('fv'=>'rights/xltoken','modul'=>'login','result'=>'record','values'=>array($selector)));
            }
            if (is_array($r)) {
                $calc = hash('sha256', hex2bin($validator));
                if (hash_equals($calc, $r['token'])) { // valid token
                        global $sessionMode;
                        $sessionMode = 2;
                        // reauth AS:
                        $toPolicy = $r['policy'];
                        $userAccount = $r['userAccount'];
                        $userCn = $r['userCn'];
                        $studyId = $r['studyId'];
                        $userPassword = ''; // ???
                        $lang = _DEFAULT_LANG;
			$data = $r;
		    $_SESSION['mayorapiauth'] = true;
            	    $q = "UPDATE authToken SET activity=NOW(), ipAddress='%s' WHERE selector = '%s'";
		    $v = array(CLIENTIPADDRESS,$selector);
            	    db_query($q, array('fv'=>'rights/xltoken','modul'=>'login','result'=>'update','values'=>$v));
		    return array('userAccount'=>$data['userAccount'],'toPolicy'=>$data['policy'],'studyId'=>$data['studyId'],'userCn'=>$data['userCn'],'valid'=>true);
                } else {
                    unsetTokenCookies();
                }
	    } else {
                unsetTokenCookies();
	    }
	return false;
    }

    function getMyActivity() {
	$q = "SELECT ipAddress,activity FROM authToken WHERE userAccount ='%s' AND policy='%s'";
	$v = array(_USERACCOUNT,_POLICY);
        return db_query($q, array('fv'=>'rights/getMyActivity','modul'=>'login','result'=>'indexed','values'=>$v));
    }

    function revokeTokens() {
	unsetTokenCookies();
	$q = "DELETE FROM authToken WHERE userAccount ='%s' AND policy='%s'";
	$v = array(_USERACCOUNT,_POLICY);
        return db_query($q, array('fv'=>'rights/revokeTokens','modul'=>'login','result'=>'delete','values'=>$v));
    }

?>
