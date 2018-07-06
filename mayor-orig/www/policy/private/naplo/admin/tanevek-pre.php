<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/nap.php');
	require_once('include/modules/naplo/share/osztalyModifier.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/diakModifier.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');
	require_once('include/modules/naplo/share/jegyModifier.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/mysql.php');

	$Tanevek = getTanevek($tervezett = true);
	$Intezmenyek = getIntezmenyek();
	$IntezmenyRoividNevek = array();
	for ($i = 0; $i < count($Intezmenyek); $i++) $IntezmenyRovidNevek[] = $Intezmenyek[$i]['rovidNev'];
	$intezmeny = readVariable($_POST['intezmeny'], 'strictstring', defined('__INTEZMENY') ? __INTEZMENY : null, $IntezmenyRovidNevek);

	$tanev = readVariable($_POST['tanev'], 'numeric unsigned', null, $Tanevek);
	if (!isset($tanev) && defined('__TANEV')) $tanev = __TANEV;
	if (isset($tanev)) {
	    $ADAT['tanev'] = $tanev;
	    $ADAT['tanevAdat'] = getTanevAdat($tanev);
	    if ($ADAT['tanevAdat']['statusz'] == 'aktív') {
		$Osztalyok = getOsztalyok($tanev);
		$ADAT['vegzoOsztalyok'] = array();
		for ($i = 0; $i < count($Osztalyok); $i++) {
		    if ($Osztalyok[$i]['vegzoTanev'] == $tanev) $ADAT['vegzoOsztalyok'][] = $Osztalyok[$i];
		}
		$ADAT['dt'] = readVariable($_POST['dt'], 'datetime', date('Y-m-d', strtotime('+7 days', strtotime($ADAT['tanevAdat']['zarasDt']))));
	    }
	}
	$rootUser = readVariable($_POST['rootUser'], 'strictstring', 'root');
	$rootPassword = readVariable($_POST['rootPassword'], 'emptystringnull', null); // lehet benne bármilyen karakter

	if ( $action == 'ujTanev' ) {

	    $tanev = readVariable($_POST['ujTanev'], 'numeric unsigned', null);
	    if ( isset($tanev) ) {

		$DATA = array(); $j = 0;
		for ($i = 0; $i < count($_POST['kezdesDt']); $i++) {
		    $kezdesDt = readVariable($_POST['kezdesDt'][$i], 'datetime', null);
		    $zarasDt  = readVariable($_POST['zarasDt' ][$i], 'datetime', null);
		    if (isset($kezdesDt) && isset($zarasDt)) {
			$DATA[$j++] = array(
			    'tanev' => $tanev,
			    'szemeszter' => readVariable($_POST['szemeszter'][$i], 'numeric unsigned'),
			    'kezdesDt' => $kezdesDt,
			    'zarasDt' => $zarasDt,
			    'statusz' => 'tervezett'
			);
		    }
		}
		for ($i = 0; $i < count($DATA); $i++) szemeszterBejegyzes($DATA[$i]);
		$Tanevek = getTanevek($tervezett = true);

	    }

	} elseif ($action == 'intezmenyValasztas') {

    	    if (isset($intezmeny) && $intezmeny !== __INTEZMENY) {
        	if (updateSessionIntezmeny($intezmeny)) {
            	    header('Location: '.location('index.php?page=naplo&sub=admin&f=tanevek'));
        	}
    	    }

	} elseif ($action == 'tanevAktival') {
	    $TA = getTanevAdat($tanev);
	    $dbNev = tanevDbNev(__INTEZMENY, $tanev);
	    if ($TA['statusz'] == 'tervezett') {
		// hozzuk létre az adatbázist és adjunk megfelelő jogokat hozzá!
    		if (
		    createDatabase($dbNev, __TANEV_DB_FILE, $rootUser, $rootPassword, array("%DB%" => intezmenyDbNev(__INTEZMENY)) )
		    !== false)
		{
		    // frissítsük az osztalyNaplo táblát
		    refreshOsztalyNaplo($dbNev,$tanev);
		    activateTanev($tanev);
		}
	    } else {
		grantWriteAccessToDb($dbNev, $rootUser, $rootPassword);
		activateTanev($tanev);
	    }
	} elseif ($action == 'tanevLezar' && $ADAT['tanevAdat']['statusz'] == 'aktív' && is_array($_POST['step'])) {
	    $ADAT['step'] = $_POST['step'];
	    $ADAT['vjlOsztaly'] = $_POST['vjlOsztaly'];
	    $ADAT['vatOsztaly'] = $_POST['vatOsztaly'];
	    if (closeTanev($ADAT) && in_array('tanevLezaras', $ADAT['step']))
		revokeWriteAccessFromDb(tanevDbNev(__INTEZMENY, $tanev), $rootUser, $rootPassword);
	} elseif ($action == 'tanevLezar' && $ADAT['tanevAdat']['statusz'] == 'aktív' && !is_array($_POST['step'])) {
	    $_SESSION['alert'][] = 'message:nothing_to_do:'.$action;
	} elseif ($action == 'tanevValasztas') {
//    	    if ($_POST['tanev'] !== __TANEV) {
//		require_once('include/modules/naplo/share/intezmenyek.php');
//        	if (updateSessionTanev($_POST['tanev'])) {
//        	    header('Location: '.location('index.php?page=naplo&sub=admin&f=tanevek'));
//		}
//    	    }
	} elseif ($action == 'szemeszterTorles') {
	    // Szemeszterek kezdes és zaras dátumainak változtatása
	    $Szemeszterek = getTanevSzemeszterek($tanev);
	    if (
		is_array($_POST['kezdesDt']) && is_array($_POST['zarasDt']) && is_array($Szemeszterek)
		&& count($Szemeszterek) == count($_POST['kezdesDt'])
	    ) {
		$ADAT['modSzemeszter'] = array();
		$elozoDt = ''; $rendezett = true;
		for ($i = 0; $i < count($Szemeszterek); $i++) {
		    $kezdesDt = readVariable($_POST['kezdesDt'][$i],'datetime','');
		    $zarasDt = readVariable($_POST['zarasDt'][$i],'datetime','');
		    if ($elozoDt >= $kezdesDt || $kezdesDt >= $zarasDt) {
			$rendezett = false;
			$_SESSION['alert'][] = 'message:wrong_data:szemeszter dátum módosítás:'.$kezdesDt.'-'.$zarasDt;
			break;
		    } elseif ($Szemeszterek[$i]['kezdesDt'] != $kezdesDt || $Szemeszterek[$i]['zarasDt'] != $zarasDt) {
			$Szemeszterek[$i]['kezdesDt'] = $kezdesDt; $Szemeszterek[$i]['zarasDt'] = $zarasDt;
			$ADAT['modSzemeszter'][] = $Szemeszterek[$i];
		    }
		    $elozoDt = $zarasDt;
		}
		if ($rendezett && count($ADAT['modSzemeszter']) > 0) szemeszterModositas($ADAT['modSzemeszter']);
	    }
	    if (is_array($_POST['szemeszterId'])) szemeszterTorles($_POST['szemeszterId']);
	} // action
	updateNaploSession($sessionID,__INTEZMENY,$tanev);

	if (isset($tanev)) $Szemeszterek = getTanevSzemeszterek($tanev);
	$i = 0; 
	while (($i < count($Szemeszterek)) && ($Szemeszterek[$i]['statusz'] != 'aktív')) $i++;
	$aktivTanev = ($i < count($Szemeszterek));

	$TOOL['intezmenySelect'] = array('tipus' => 'cella', 'action' => 'intezmenyValasztas', 'intezmenyek' => $Intezmenyek, 'post' => array());
	$TOOL['tanevSelect'] = array('tipus' => 'cella', 'tanevek' => $Tanevek, 'action' => 'tanevValasztas', 'tervezett' => true, 'post' => array(), 'paramName'=>'tanev');
	getToolParameters();

    }

?>
