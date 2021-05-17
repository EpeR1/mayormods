<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/share/date/names.php');
    require_once('include/share/print/pdf.php');

    $token = readVariable($_GET['token'],'strictstring');
    if (
	(_USERACCOUNT === 'mayoradmin' || __FELVETELIADMIN === true) && $token==''
    ) {

    } else {

	if (strlen($token)==40) {

	    $q = "SELECT oId FROM felveteli_levelLog WHERE token='%s' AND generalasDt>= NOW() - interval 10 MINUTE";
	    $v = array($token);
	    $oId = db_query($q, array('modul'=>'naplo','result'=>'value','values'=>$v,'debug'=>false));

	    if ($oId=='') {
		$_SESSION['alert'][] = 'info::Ez a token már nem érvényes!';
	    } else {
		//$q = "SELECT * FROM adatok_"._EV." ORDER BY evfolyam,nev";
		$q = "SELECT * FROM felveteli WHERE oId='%s'";
		$v = array($oId);
		$r = db_query($q, array('modul'=>'naplo','result'=>'indexed','values'=>$v));
		$ADAT['level'] = $r;
		$ADAT['iktsz'] = 'C8-62/2021';
    		$file = __INTEZMENY.'_'.date('Y').'_'.$oktid.$token;
    		if (pdfLevel($file, $ADAT)) {
		    $q = "UPDATE felveteli_levelLog SET letoltesDt=NOW() WHERE token='%s'";
		    $v = array($token);
		    db_query($q, array('modul'=>'naplo','values'=>$v,'debug'=>false));
    		    header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/felveteli/level&file='.$file.'.pdf'));
		    exit;
		} else {
		    $_SESSION['alert'][] = 'info::Hiba a file-generáláskor!';
		}
	    }
	} else {
	    $_SESSION['alert'][] = 'info::Nem adtál meg érvényes kulcsot a letöltéshez!';
	}
    }
    // ha eljutottunk idáig, visszairányíthatnánk a lekérdező oldalra
    //header('Location: '.location('index.php?page=felveteli&f=kozponti'));

?>
