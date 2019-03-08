<?php

    if (_RIGHTS_OK !== true) die();
    if (
        _USERACCOUNT != 'mayoradmin' && __FELVETELIADMIN !== true
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/share/date/names.php');
        require_once('include/share/print/pdf.php');

	$q = "SELECT * FROM adatok_"._EV." ORDER BY evfolyam,nev";
	$r = db_query($q, array('modul'=>'felveteli','result'=>'indexed'));
	$ADAT = $r;

        $file = 'boritekok';
        if (pdfBoritek($file, $ADAT))
    	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=felveteli/boritek&file='.$file.'.pdf'));
	else
	    $_SESSION['alert'][] = 'nem sikerült';    
    }
?>
