<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/share/net/phpmailer.php');

    $ADAT['userAccount'] = readVariable($_POST['userAccount'], 'string', readVariable($_GET['userAccount'], 'string', null));
    $ADAT['toPolicy'] = readVariable($_POST['toPolicy'], 'enum', readVariable($_GET['toPolicy'], 'enum', 'parent', array('parent','private')), array('parent','private'));
    $ADAT['mail'] = readVariable($_POST['mail'], 'string', null);

    if ($AUTH[$ADAT['toPolicy']]['enablePasswordReset']!==true) {
	//$_SESSION['alert'][] = 'info:pw_reset_disabled';
	$ADAT['forgotDisabled'] = true;
    }

/* Under dev
    foreach(array('private','parent','public') as $_policy) {
	if ($ADAT['toPolicy']==$_policy && $AUTH[$_policy]['enablePasswordReset']!==true) $ADAT['forgotDisabled'] = true;
    }
*/
    if ($action == 'sendResetPasswordMail') {

	// TODO - ez a kettő összevonható, kukac tuti nincs a felhasználónévben
	// TODO - megviszgálhatnánk, hogy milyen authentikációs levelen van a user
	// TODO - mármint, ahol megváltoztatható egyáltalán a jelszó...
	require_once('include/modules/session/search/searchAccount.php');
	if (isset($ADAT['userAccount'])) {
	    $ADAT['accounts'] = searchAccount('userAccount', $ADAT['userAccount'], $searchAttrs = array('userCn','mail','userAccount'), $ADAT['toPolicy']);
	    for ($i=0; $i<$ADAT['accounts']['count']; $i++) {
		if ($ADAT['userAccount'] == $ADAT['accounts'][$i]['userAccount'][0] && $ADAT['accounts'][$i]['mail'][0] != '') {
		    $ADAT['account'] = array(
			'policy' => $ADAT['toPolicy'],
			'userAccount' => $ADAT['accounts'][$i]['userAccount'][0],
			'userCn' => $ADAT['accounts'][$i]['userCn'][0],
			'mail' => current(explode(' ',str_replace(';',' ',trim($ADAT['accounts'][$i]['mail'][0])))),
		    );
		    break;
		}
	    }
	} elseif (isset($ADAT['mail'])) {
	    $ADAT['accounts'] = searchAccount('mail', $ADAT['mail'], $searchAttrs = array('userCn','mail','userAccount'), $ADAT['toPolicy']);
	    for ($i=0; $i<$ADAT['accounts']['count']; $i++) {
		if ($ADAT['mail'] == $ADAT['accounts'][$i]['mail'][0] && $ADAT['accounts'][$i]['mail'][0] != '') {
		    $ADAT['account'] = array(
			'policy' => $ADAT['toPolicy'],
			'userAccount' => $ADAT['accounts'][$i]['userAccount'][0],
			'userCn' => $ADAT['accounts'][$i]['userCn'][0],
			'mail' => current(explode(' ',str_replace(';',' ',trim($ADAT['accounts'][$i]['mail'][0])))),
		    );
		    break;
		}
	    }
	}

	// Recovery
	if (is_array($ADAT['account'])) {	     
	    $recoveryRequest = generatePasswordRecoveryRequest($ADAT['account']);
	    if ($recoveryRequest!=false) {
		$ADAT['account']['url'] = $recoveryRequest;
		// levél generálása és kiküldése
		$body = '<html><head><title></title></head><body>
<p>Az alábbi linkre kattintva magadhatod az új MaYoR-jelszavadat.</p>
<p>Ha nem te küldted az igénylést, tekintsd a levelet tárgytalannak!</p>
<p class="link">'.$recoveryRequest.'</p>
<p>'.__SUPPORT_EMAIL_NAME.' ('.__SUPPORT_EMAIL_ADDRESS.')</p>
</body></html>';
		/* MAIL */
		if (__EMAIL_ENABLED===true) {
		    $mail             = new PHPMailer();
		    $mail->CharSet = 'UTF-8';
		    $mail->SetFrom(__SUPPORT_EMAIL_ADDRESS,__SUPPORT_EMAIL_NAME);
		    $mail->AddAddress($ADAT['account']['mail'], $ADAT['account']['userCn']);
        	    $mail->Subject    = "[MaYoR] Jelszóemlékeztető";
    		    $mail->MsgHTML($body);
		    $mail->Send();
		} else {
		    //dump(__EMAIL_ENABLED);
		    //dump(__SUPPORT_EMAIL_ADDRESS);
		    //dump(__SUPPORT_EMAIL_NAME);
		}
		/* -- */
//		$_SESSION['alert'][] = 'info:success';
	    } else {
//		$_SESSION['alert'][] = 'info:success'; // nem üzenünk hibát
	    }
	} else {
//	    $_SESSION['alert'][] = 'message:wrong_data:Nincs ilyen azonosító, vagy nincs rögzítve e-mail cím az azonosítóhoz! ('.$ADAT['userAccount'].')';
	}

    }

?>