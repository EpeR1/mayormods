<?php

    if (_RIGHTS_OK !== true) die();
    if (
	_USERACCOUNT != 'mayoradmin' && __FELVETELIADMIN !== true
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/share/date/names.php');
        require_once('include/share/print/pdf.php');

	$q = "select distinct omkod, nev, megye, telepules, cim, irsz
		from iskolak where omkod in (select distinct OM from adatok_"._EV." where level2 != 'nem kell értesíteni') 
		order by omkod";
	$r = db_query($q, array('modul'=>'felveteli','result'=>'indexed'));
	$ADAT = $r;

        $file = 'boritekokIgazgato';
        if (pdfBoritek($file, $ADAT))
    	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=felveteli/boritekIgazgato&file='.$file.'.pdf'));
	else
	    $_SESSION['alert'][] = 'nem sikerült';    
    }
?>
