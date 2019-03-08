<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/file.php');
	$ADAT['fenntartok'] = getEnumField('naplo_base', 'intezmeny', 'fenntarto');
	$ADAT['intezmeny'] = $intezmeny = readVariable($_POST['intezmeny'], 'strictstring', defined('__INTEZMENY') ? __INTEZMENY : null);

	if ($action == 'ujIntezmeny') {

	    require_once('include/modules/naplo/share/mysql.php');

	    $rovidnev = readVariable($_POST['rovidnev'], 'strictstring', null);
	    if (isset($rovidnev)) {
		$dbNev = intezmenyDbNev($rovidnev);
		$rootUser = readVariable($_POST['rootUser'], 'strictstring', null);
		$rootPassword = readVariable($_POST['rootPassword'], 'string', null); // nincs ellenőrzés!
    		if (createDatabase($dbNev, __INTEZMENY_DB_FILE, $rootUser, $rootPassword)) {		
		    $OMKod = readVariable($_POST['OMKod'], 'numeric unsigned', null);
		    $nev = readVariable($_POST['nev'], 'sql', null);
		    $rovidnev = readVariable($_POST['rovidnev'], 'strictstring', null);
		    intezmenyBejegyzese($OMKod, $nev, $rovidnev);
		    updateNaploSession($sessionID,$rovidnev);
		    header('Location: ' . location('index.php?page=naplo&sub=admin&f=tanevek'));
		}
	    }

	} elseif ($action == 'intezmenyModositas') {

	    $ADAT['nev'] = readVariable($_POST['nev'], 'sql', null);
    	    $ADAT['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned', null);
    	    $ADAT['alapertelmezett'] = readVariable($_POST['alapertelmezett'], 'numeric unsigned', 0, array(0,1));
	    $ADAT['fenntarto'] = readVariable($_POST['fenntarto'], 'enum','állami',$ADAT['fenntartok']);
	    // A readVariable hívások a függvénybe kerültek!
	    intezmenyModositas($ADAT);

	} elseif ($action == 'intezmenyTorles') {

	    intezmenyTorles(__INTEZMENY);
            require_once('include/modules/naplo/share/intezmenyek.php');
            if (updateSessionIntezmeny('')) {
                header('Location: '.location('index.php?page=naplo&sub=admin&f=intezmenyek'));
            }
	    header('Location: '.location('index.php?page=naplo&sub=admin&f=intezmenyek'));

        } elseif ($action == 'intezmenyValasztas') {

            if (isset($intezmeny) && $intezmeny !== __INTEZMENY) {
                require_once('include/modules/naplo/share/intezmenyek.php');
                if (updateSessionIntezmeny($intezmeny)) {
                    header('Location: '.location('index.php?page=naplo&sub=admin&f=intezmenyek'));
                }
            }
	} elseif ($action == 'telephelyModositas') {

	    telephelyModositas($_POST);

	} elseif ($action == 'ujTelephely') {

	    ujTelephely($_POST);

        } // action

	// Az aktuális intézmény adatainak lekérdezése
	if (defined('__INTEZMENY') and __INTEZMENY != '') {
	    $ADAT['intezmenyAdat'] = getIntezmeny(__INTEZMENY);
	}

        $TOOL['intezmenySelect'] = array('tipus'=>'cella', 'action' => 'intezmenyValasztas', 'post'=>array());
        getToolParameters();

    }

?>
