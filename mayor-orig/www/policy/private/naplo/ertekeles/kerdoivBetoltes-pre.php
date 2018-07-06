<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/kerdoiv.php');

	$ADAT['feladoTipusok'] = array('tanar','diak','szulo');
	$ADAT['cimzettTipusok'] = array('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly');

	$ADAT['feladoTipus'] = readVariable($_POST['feladoTipus'], 'enum', null, $ADAT['feladoTipusok']);
	$ADAT['cimzettTipus'] = readVariable($_POST['cimzettTipus'], 'enum', null, $ADAT['cimzettTipusok']);
// !!!!
	$kerdoivId = $ADAT['kerdoivId'] = readVariable($_POST['kerdoivId'], 'id');
	if (isset($kerdoivId)) {
	    $ADAT['kerdoivAdat'] = getKerdoivAdat($kerdoivId);
	    if (is_array($ADAT['kerdoivAdat']['cimzett']) && count($ADAT['kerdoivAdat']['cimzett']) > 0) 
		$ADAT['cimzettTipus'] = array_pop(array_keys($ADAT['kerdoivAdat']['cimzett']));
	    $ADAT['cim'] = $ADAT['kerdoivAdat']['cim'];
	}

	if (isset($ADAT['cimzettTipus'])) {
/*
		switch ($ADAT['feladoTipus']) {
		    case 'tanar':
			$T = getTanarok(array('tanev' => __TANEV));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['feladok'][] = array('feladoId' => $T[$i]['tanarId'], 'feladoNev' => $T[$i]['tanarNev']);
			    $ADAT['feladoIds'][] = $T[$i]['tanarId'];
			}
			break;
		    case 'diak':
			$T = getDiakok(array('tanev' => __TANEV));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['feladok'][] = array('feladoId' => $T[$i]['diakId'], 'feladoNev' => $T[$i]['diakNev']);
			    $ADAT['feladoIds'][] = $T[$i]['diakId'];
			}
			break;
		    case 'szulo':
			$T = getSzulok(array('result' => 'standard'));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['feladok'][] = array('feladoId' => $T[$i]['szuloId'], 'feladoNev' => $T[$i]['szuloNev']);
			    $ADAT['feladoIds'][] = $T[$i]['szuloId'];
			}
			break;
		}
*/
		$ADAT['cimzettIds'] = array(0);
		switch ($ADAT['cimzettTipus']) {
		    case 'tanar':
			$T = getTanarok(array('tanev' => __TANEV));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['cimzettek'][] = array('cimzettId' => $T[$i]['tanarId'], 'cimzettNev' => $T[$i]['tanarNev']);
			    $ADAT['cimzettIds'][] = $T[$i]['tanarId'];
			}
			break;
		    case 'diak':
			$T = getDiakok(array('tanev' => __TANEV));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['cimzettek'][] = array('cimzettId' => $T[$i]['diakId'], 'cimzettNev' => $T[$i]['diakNev']);
			    $ADAT['cimzettIds'][] = $T[$i]['diakId'];
			}
			break;
		    case 'szulo':
			$T = getSzulok(array('result' => 'standard'));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['cimzettek'][] = array('cimzettId' => $T[$i]['szuloId'], 'cimzettNev' => $T[$i]['szuloNev']);
			    $ADAT['cimzettIds'][] = $T[$i]['szuloId'];
			}
			break;
		    case 'tankor':
		    case 'tankorSzulo':
		    case 'tankorTanar':
			$T = getTankorok(array('tanev = '.__TANEV));
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['cimzettek'][] = array('cimzettId' => $T[$i]['tankorId'], 'cimzettNev' => $T[$i]['tankorNev']);
			    $ADAT['cimzettIds'][] = $T[$i]['tankorId'];
			}
			break;
		    case 'munkakozosseg':
			$T = getMunkakozossegek();
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['cimzettek'][] = array('cimzettId' => $T[$i]['mkId'], 'cimzettNev' => $T[$i]['mkNev']);
			    $ADAT['cimzettIds'][] = $T[$i]['mkId'];
			}
			break;
		    case 'osztaly':
		    case 'osztalySzulo':
		    case 'osztalyTanar':
			$T = getOsztalyok(__TANEV);
			for ($i = 0; $i < count($T); $i++) {
			    $ADAT['cimzettek'][] = array('cimzettId' => $T[$i]['osztalyId'], 'cimzettNev' => $T[$i]['osztalyJel']);
			    $ADAT['cimzettIds'][] = $T[$i]['osztalyId'];
			}
			break;
		}
	}

	if ($action == 'kerdoivBetoltes1') {
	    $ADAT['cim'] = readVariable($_POST['cim'], 'sql', null);
	    $ADAT['tolDt'] = readVariable($_POST['tolDt'], 'datetime', null);
	    $ADAT['igDt'] = readVariable($_POST['igDt'], 'datetime', null);
	    $ADAT['megjegyzes'] = readVariable($_POST['megjegyzes'], 'string', null);
	    if (isset($ADAT['cim']) && isset($ADAT['tolDt']) && isset($ADAT['igDt']) && isset($ADAT['cimzettTipus'])) {
		$kerdoivId = $ADAT['kerdoivId'] = ujKerdoiv($ADAT);
		if ($kerdoivId) {
		    $ADAT['txt'] = explode("\n", $_POST['txt']);
		    kerdesValaszFelvetel($ADAT);
		}
	    } else {
		$_SESSION['alert'][] = 'message:empty_fields:ujKerdoiv:cim-tolDt-igDt';
	    }
	} elseif ($action == 'kerdoivBetoltes2') {
	    if (is_array($_POST['cimzettId']) && count($_POST['cimzettId']) > 0) {
		//$kerdoivId = readVariable($_POST['kerdoivId'], 'numeric unsigned', null);
		if (in_array(0, $_POST['cimzettId'])) $_POST['cimzettId'] = $ADAT['cimzettIds'];
		for ($i = 0; $i < count($_POST['cimzettId']); $i++) {
		    $cimzettId = readVariable($_POST['cimzettId'][$i], 'numeric unsigned', null, $ADAT['cimzettIds']);
		    kerdoivCimzettFelvetel($kerdoivId, $cimzettId, $ADAT['cimzettTipus']);
		}
	    }
	    if (is_array($_POST['torlendoCimzettId']) && count($_POST['torlendoCimzettId']) > 0) {
		for ($i = 0; $i < count($_POST['torlendoCimzettId']); $i++) {
		    $cimzettId = readVariable($_POST['torlendoCimzettId'][$i], 'numeric unsigned', null, $ADAT['cimzettIds']);
		    kerdoivCimzettTorles($kerdoivId, $cimzettId, $ADAT['cimzettTipus']);
		}
	    }
	    $ADAT['kerdoivAdat'] = getKerdoivAdat($kerdoivId);
	    if (is_array($ADAT['kerdoivAdat']['cimzett']) && count($ADAT['kerdoivAdat']['cimzett'])>0) {
		$ADAT['cimzettTipus'] = array_pop(array_keys($ADAT['kerdoivAdat']['cimzett']));
	    }
	    $ADAT['cim'] = $ADAT['kerdoivAdat']['cim'];
	}
    }

    $TOOL['kerdoivSelect'] = array('tipus' => 'cella', 'paramName' => 'kerdoivId', 'post' => array());
    getToolParameters();

?>
