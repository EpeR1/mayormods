<?php

    if (_RIGHTS_OK !== true) die();

    define('_TIME',strtotime(date('Y-m-d')));

    if (
        !__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	$ADAT['tanarId'] = $tanarId = readVariable($_GET['tanarId'], 'id');
	$ADAT['tovabbkepzesId'] = $tovabbkepzesId = readVariable($_GET['tovabbkepzesId'], 'id');
	$ADAT['tanev'] = $tanev = readVariable($_GET['tanev'], 'numeric unsigned');

	if (isset($tanarId) && isset($tovabbkepzesId) && isset($tanev)) { // input ok

	    require_once('include/share/print/pdf.php');
	    require_once('include/share/date/names.php');
	    require_once('include/modules/naplo/share/intezmenyek.php');
	    require_once('include/modules/naplo/share/tanar.php');
	    require_once('include/modules/naplo/share/file.php');

	    // Adatok lekérdezése
	    $ADAT['file'] = fileNameNormal('hatarozat-'.$tanarId.'-'.$tovabbkepzesId.'-'.date('Y-m-d'));
	    list($ADAT['tanarAdat']) = getTanarAdatById($tanarId);
	    $ADAT['tanulmanyiEgyseg'] = getTanulmanyiEgyseg($tovabbkepzesId, $tanarId, $tanev);

	    $printFile = tovabbkepzesNyomtatvanyKeszites($ADAT);
            $printFile = fileNameNormal($printFile);

            if ($printFile !== false && file_exists(_DOWNLOADDIR."/$policy/$page/$sub/$f/$printFile"))
                header('Location: '.location('index.php?page=session&f=download&download=true&dir='.$page.'/'.$sub.'/'.$f.'&file='.$printFile));


	}

    }
?>