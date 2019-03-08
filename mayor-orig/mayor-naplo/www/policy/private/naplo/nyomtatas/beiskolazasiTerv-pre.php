<?php
    if (_RIGHTS_OK !== true) die();

    define('_TIME',strtotime(date('Y-m-d')));

    if (
        !__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	$ADAT['tanev'] = $tanev = readVariable($_GET['tanev'], 'numeric unsigned', 2015);

	if (isset($tanev)) { // input ok

	    require_once('include/share/print/pdf.php');
	    require_once('include/share/date/names.php');
	    require_once('include/modules/naplo/share/intezmenyek.php');
	    require_once('include/modules/naplo/share/tanar.php');
	    require_once('include/modules/naplo/share/file.php');

	    // Adatok lekérdezése
	    $ADAT['file'] = fileNameNormal('beiskolazasiTerv-'.$tanev.'-'.date('Y-m-d'));
	    $ADAT['tanarok'] = getTanarok(array('result'=>'assoc','összes'=>true));
	    $ADAT['tanulmanyiEgyseg'] = getBeiskolazasiTerv($tanev);

	    $printFile = beiskolazasNyomtatvanyKeszites($ADAT);
            $printFile = fileNameNormal($printFile);

            if ($printFile !== false && file_exists(_DOWNLOADDIR."/$policy/$page/$sub/$f/$printFile"))
                header('Location: '.location('index.php?page=session&f=download&download=true&dir='.$page.'/'.$sub.'/'.$f.'&file='.$printFile));


	}

    }
?>