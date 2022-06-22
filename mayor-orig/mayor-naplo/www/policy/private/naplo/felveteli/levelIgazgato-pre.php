<?php

    define('_EV',date('Y'));
    if (_RIGHTS_OK !== true) die();
    if (
	_USERACCOUNT != 'mayoradmin' && __FELVETELIADMIN !== true && __PORTAL_CODE!=='kanizsay'
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/share/date/names.php');
        require_once('include/share/print/pdf.php');
        require_once('include/share/net/class.smtp.php');
        require_once('include/share/net/phpmailer.php');

	$ADAT['generatePDF'] = readVariable($_POST['generatePDF'],'id',null);
	$ADAT['sendMAIL'] = readVariable($_POST['sendMAIL'],'strictstring',null);

	$q = "SELECT *, iskolaEmail as email, iskolaTelepules AS telepules, iskolaIrsz as irsz, iskolaNev as nev, iskolaCim as cim FROM felveteli_iskolak";
	$ADAT['iskola'] = db_query($q, array('modul'=>'naplo','result'=>'assoc','keyfield'=>'omkod'));

	$q = "SELECT distinct omkod FROM felveteli WHERE level2 != 'nem kell értesíteni' AND omkod != '' ORDER  BY omkod";
	$ADAT['OM'] = $R = db_query($q, array('modul'=>'naplo','result'=>'idonly'));

	for ($i=0; $i<count($R); $i++) {
	    $ADAT['diak'] = array();
	    $_om = $R[$i]; // iskola OM kódja
	    $q = "SELECT * FROM felveteli WHERE level2 != 'nem kell értesíteni' AND omkod='%s' ORDER BY omkod,nev";
	    $ADAT['diak'] = db_query($q, array('modul'=>'naplo','result'=>'indexed','values'=>array($_om)));
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
		$mail->setFrom('ig@kanizsay.edu.hu', 'Kanizsay Dorottya Katolikus Gimnázium, Egészségügyi Technikum és Szakképző Iskola');
		$mail->addReplyTo('titkarsag@kanizsay.edu.hu', 'Kanizsay Dorottya');
//!		$mail->addAddress($ADAT['iskola'][$_om]['email'], 'Igazgató');
		$mail->addAddress('konczy@gmail.com', 'Igazgató');
		$mail->Subject = 'Értesítés felvételi eredményekről - 2022/2023-as beiskolázás';
		$mail->msgHTML("A levelet a MaYoR elektronikus napló generálta PDF csatolmány tartalmazza.");
		$mail->AltBody="A levelet a MaYoR elektronikus napló generálta PDF csatolmány tartalmazza.";
		$mail->addAttachment(_DOWNLOADDIR."/private/naplo/felveteli/levelIgazgato/".$file.'.pdf');
		if (!$mail->send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		    echo "Message sent! " . $ADAT['iskola'][$_om]['email'];
		    $qr = "UPDATE felveteli SET level2='értesítve' WHERE omkod='%s'";
		    db_query($qr, array('modul'=>'naplo','values'=>array($_om)));
		}
	    }
	    // header('Location: '.location('index.php?page=session&f=download&download=true&dir=felveteli/levelIgazgato&file='.$file.'.pdf'));
	}
    }
?>
