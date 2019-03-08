<?php

    global $ADAT;
    require_once('skin/classic/module-naplo/html/hirnok/hirnok.phtml');
    require_once('skin/classic/module-naplo/html/share/email.phtml');

    for ($i=0; $i<count($ADAT['hirnokFolyam']); $i++) {
        $D = $ADAT['hirnokFolyam'][$i];
	$_data = $D['hirnokFolyamAdatok'];
	$cn = $_data['cn'];
	for ($j=0; $j<count($ADAT['feliratkozas'][$_data['tipus']][$_data['id']]); $j++) {
	    $_toUser = $ADAT['feliratkozas'][$_data['tipus']][$_data['id']][$j];
	    $_toEmail = $_toUser['email'];
    	    // if ($_toEmail=='') continue;;
	    $body = generateFutarEmailTorzs(array('hirnokFolyam'=>array($D)));
	    if ($body !='') {
		echo "Email cím: ".$_toEmail."\n";
    		$mail = new PHPMailer();
    		$mail->CharSet = 'UTF-8';
		$mail->SetFrom(__SUPPORT_EMAIL_ADDRESS, ''._SITE.'');
		$mail->AddReplyTo(__SUPPORT_EMAIL_ADDRESS,'MaYoR Support');
		$mail->AddAddress($_toEmail, $cn);
    		$mail->Subject    = "[MaYoR] Értesítés – ".$cn;
    		$mail->MsgHTML(emailHead(array(
		    'skin/classic/module-naplo/css/hirnok/hirnok.css',
		    'skin/classic/module-naplo/css/hirnok/cronFutar.css'
		)).$body.emailFoot());
		if(!$mail->Send()) { 
		    echo "PHP Mailer Error: " . $mail->ErrorInfo . "\n";
		} else { 
		    $q = "UPDATE hirnokFeliratkozas SET utolsoEmailDt ='%s' WHERE naploId=%u AND naploTipus='%s' AND userAccount='%s' AND policy='%s'";
		    $v = array($_toUser['setDt'],$_data['id'],$_data['tipus'],$_toUser['userAccount'],$_toUser['policy']);
		    db_query($q,array('modul'=>'naplo_intezmeny','fv'=>'cron','values'=>$v,'result'=>'update'));
		    echo "Email elküldve: ".$_toEmail." - ".$cn." - ".date('Y-m-d H:i:s')."\n";
		}

	    } else {
		// echo "Nincs mit küldeni.\n";
	    }
	}

    }
?>