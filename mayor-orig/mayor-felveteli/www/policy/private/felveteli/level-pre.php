<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/share/date/names.php');
    require_once('include/share/print/pdf.php');

    $token = readVariable($_GET['token'],'strictstring');
    if (
	(_USERACCOUNT === 'mayoradmin' || __FELVETELIADMIN === true) && $token==''
    ) {
	$q = "SELECT * FROM adatok_"._EV." ORDER BY evfolyam,nev";
	$r = db_query($q, array('modul'=>'felveteli','result'=>'indexed'));
	$ADAT['level'] = $r;
    	$file = 'VarosmajoriGimnazium_'._EV.'_teljes';
    	    if (pdfLevel($file, $ADAT)) {
    		    header('Location: '.location('index.php?page=session&f=download&download=true&dir=felveteli/level&file='.$file.'.pdf'));
	    } else {
		    $_SESSION['alert'][] = 'info::Hiba a file-generáláskor!';
	    }

    } else {

	if (strlen($token)==40) {

	    $q = "SELECT oktid FROM levelLog_"._EV." WHERE token='%s' AND generalasDt>= NOW() - interval 10 MINUTE";
	    $v = array($token);
	    $oktid = db_query($q, array('modul'=>'felveteli','result'=>'value','values'=>$v,'debug'=>false));

	    if ($oktid=='') {
		$_SESSION['alert'][] = 'info::Ez a token már nem érvényes!';
	    } else {
		//$q = "SELECT * FROM adatok_"._EV." ORDER BY evfolyam,nev";
		$q = "SELECT * FROM adatok_"._EV." WHERE oktid='%s'";
		$v = array($oktid);
		$r = db_query($q, array('modul'=>'felveteli','result'=>'indexed','values'=>$v));
		$ADAT['level'] = $r;

    		$file = "VarosmajoriGimnazium"._EV.'_'.$oktid.$token;
    		if (pdfLevel($file, $ADAT)) {
		    $q = "UPDATE levelLog_"._EV." SET letoltesDt=NOW() WHERE token='%s'";
		    $v = array($token);
		    db_query($q, array('modul'=>'felveteli','values'=>$v,'debug'=>false));
    		    header('Location: '.location('index.php?page=session&f=download&download=true&dir=felveteli/level&file='.$file.'.pdf'));
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
