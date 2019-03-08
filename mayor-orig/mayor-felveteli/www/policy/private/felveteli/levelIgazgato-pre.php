<?php

    if (_RIGHTS_OK !== true) die();
    if (
	_USERACCOUNT != 'mayoradmin' && __FELVETELIADMIN !== true
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/share/date/names.php');
        require_once('include/share/print/pdf.php');
        require_once('include/share/net/class.smtp.php');
        require_once('include/share/net/phpmailer.php');

	$ADAT['generatePDF'] = readVariable($_POST['generatePDF'],'id',null);
	$ADAT['sendMAIL'] = readVariable($_POST['sendMAIL'],'strictstring',null);

	$q = "SELECT * FROM iskolak";
	$ADAT['iskola'] = db_query($q, array('modul'=>'felveteli','result'=>'assoc','keyfield'=>'omkod'));

	$q = "SELECT distinct OM FROM adatok_"._EV." WHERE level2 != 'nem kell értesíteni' AND OM != '' ORDER  BY OM ";
	$ADAT['OM'] = $R = db_query($q, array('modul'=>'felveteli','result'=>'idonly'));
	for ($i=0; $i<count($R); $i++) {
	    $ADAT['diak'] = array();
	    $_om = $R[$i]; // iskola OM kódja
	    $q = "SELECT * FROM adatok_"._EV." WHERE level2 != 'nem kell értesíteni' AND OM='%s' ORDER BY OM,nev";
	    $ADAT['diak'] = db_query($q, array('modul'=>'felveteli','result'=>'indexed','values'=>array($_om)));
	    $file = _EV.'_'.$_om;
	    if ($ADAT['generatePDF']==true) pdfLevel($file, $ADAT);
	    if (is_array($ADAT['sendMAIL']) && in_array($_om,$ADAT['sendMAIL'])) {
    		//echo $ADAT['iskola'][$_om]['email'];
		$mail = new PHPMailer;
//		$mail->isSMTP();
//		$mail->Host = 'smtp.gmail.com';
//		$mail->Port = 587;
//		$mail->SMTPSecure = 'tls';
//		$mail->SMTPAuth = true;
//		$mail->Username = _FELVETELI_SMTP_USERNAME;
//		$mail->Password = _FELVETELI_SMTP_PASSWORD;
		$mail->setFrom(_FELVETELI_SMTP_USERNAME, 'Városmajori Gimnázium');
		$mail->addReplyTo('support@vmg.sulinet.hu', 'Városmajori Gimnázium IT Support');
		$mail->addAddress($ADAT['iskola'][$_om]['email'], 'Igazgató');
//		$mail->addAddress('konczy@gmail.com', 'Igazgató');  //echo $ADAT['iskola'][$_om]['email'];
		$mail->Subject = 'Értesítés felvételi eredményekről - 2018/2019-es beiskolázás';
		$mail->msgHTML("A levelet a MaYoR elektronikus napló generálta PDF csatolmány tartalmazza.");
		$mail->AltBody="A levelet a MaYoR elektronikus napló generálta PDF csatolmány tartalmazza.";
		$mail->addAttachment(_DOWNLOADDIR."/private/felveteli/levelIgazgato/".$file.'.pdf');
		if (!$mail->send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		    echo "Message sent! " . $ADAT['iskola'][$_om]['email'];
		    $qr = "UPDATE adatok_"._EV." SET level2='értesítve' WHERE OM='%s'";
		    db_query($qr, array('modul'=>'felveteli','values'=>array($_om)));
		}
	    }
	    // header('Location: '.location('index.php?page=session&f=download&download=true&dir=felveteli/levelIgazgato&file='.$file.'.pdf'));
	}
    }
?>
