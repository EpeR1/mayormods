<?php
/*
    Module: naplo
*/


    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG && !__DIAK) {
	$_SESSION['alert'][] = 'page:illegal_access';
    } else {

	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/nap.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/share/date/names.php');

	$ADAT['napTipusok'] = getNapTipusok();
	$ADAT['munkaterv'] = getMunkatervek(array('result' => 'assoc'));
	$ADAT['fields']['csengetesiRendTipus'] = getEnumField('naplo','nap','csengetesiRendTipus');
	
	if (count($ADAT['munkaterv']) == 1) $munkatervId = 1;
	else $munkatervId = readVariable($_POST['munkatervId'], 'id');
	if (!isset($munkatervId)) $munkatervId = 1;
	$ADAT['munkatervId'] = $munkatervId;
	if (__NAPLOADMIN===true || __VEZETOSEG===true) { // csak admin és a vezetőség módosíthat bármit!

	    $ADAT['osztaly'] = getOsztalyok();
	    for ($i = 0; $i < count($ADAT['osztaly']); $i++) {
		$ADAT['osztaly'][$i]['munkatervId'] = getMunkatervByOsztalyId($ADAT['osztaly'][$i]['osztalyId']);
	    }
	    if ($action == 'napokInit') {
		for ($i = 1; $i <= intval($_POST['hetDb']); $i++) $ADAT['Hetek'][] = $i;
		$ADAT['tanitasiNap'] = readVariable($_POST['tanitasiNap'], 'numeric unsigned');
		$ADAT['tanitasNelkuliMunkanap'] = readVariable($_POST['tanitasNelkuliMunkanap'], 'numeric unsigned');
		$ADAT['vegzosZarasDt'] = readVariable($_POST['vegzosZarasDt'], 'date');
		if ($initResult = initNapok($ADAT)) $ADAT['munkatervId'] = 1;
		$ADAT['munkaterv'] = getMunkatervek(array('result' => 'assoc'));
	    }
	    if (__MUNKATERV_OK || $initResult===true) {
		if ($action == 'hetHozzarendeles') {
		    // később esetleg ezt is lehetne munkatervenként külön... nem?
		    for ($i = 1; $i <= intval($_POST['hetDb']); $i++) $ADAT['Hetek'][] = $i;
		    $tolDt = readVariable($_POST['tolDt'], 'date');
		    $igDt = readVariable($_POST['igDt'], 'date');
		    orarendiHetekHozzarendelese($tolDt, $igDt, $ADAT['Hetek']);
		} elseif ($action == 'munkatervModositas') {
		    $dt = readVariable($_POST['dt'], 'date');
		    $tipus = readVariable($_POST['tipus'], 'enum', 'tanítási nap', $ADAT['napTipusok']);
		    $megj = readVariable($_POST['megjegyzes'],'string','');
		    $orarendiHet = readVariable($_POST['orarendiHet'], 'numeric unsigned');
		    $csengetesiRendTipus = readVariable($_POST['csengetesiRendTipus'], 'string', null, $ADAT['fields']['csengetesiRendTipus']);
		    $ADAT['Hetek'] = getOrarendiHetek(array('tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt']));
		    if (
			$_TANEV['statusz'] == 'aktív'
			&& (__FOLYO_TANEV || __NAPLOADMIN)
		    )
		    munkatervModositas($dt, $tipus, $megj, $orarendiHet, $ADAT['Hetek'], $ADAT['munkatervId'], $csengetesiRendTipus);
		    $ADAT['Napok'] = getTanevNapjai($munkatervId);
		} elseif ($action == 'ujMunkaterv') {
		    $ADAT['munkatervNev'] = readVariable($_POST['munkatervNev'],'string','');
		    $ADAT['tanitasiNap'] = readVariable($_POST['tanitasiNap'], 'numeric unsigned');
		    $ADAT['tanitasNelkuliMunkanap'] = readVariable($_POST['tanitasNelkuliMunkanap'], 'numeric unsigned');
		    $ADAT['vegzosZarasDt'] = readVariable($_POST['vegzosZarasDt'], 'date');
		    $ADAT['munkatervId'] = $munkatervId = ujMunkaterv($ADAT);
		    if ($munkatervId) { $ADAT['munkaterv'] = getMunkatervek(array('result' => 'assoc')); }
		} elseif ($action == 'munkatervOsztaly') {
		    $ADAT['osztalyIds'] = readVariable($_POST['osztalyId'], 'id');
		    $ADAT['ujMunkatervIds'] = readVariable($_POST['ujMunkatervId'], 'id');
		    if (!munkatervOsztaly($ADAT)) $_SESSION['alert'][] = 'message:a hozzárendelés nem sikerült';
		    for ($i = 0; $i < count($ADAT['osztaly']); $i++) {
			$ADAT['osztaly'][$i]['munkatervId'] = getMunkatervByOsztalyId($ADAT['osztaly'][$i]['osztalyId']);
		    }
		} 
		if ($action == 'honapValasztas' && $_POST['ho'] != '') {
		    $ho = $_POST['ho'];
		    $ADAT['Napok'] = getHonapNapjai($ho, $munkatervId);
		    $ADAT['Hetek'] = getOrarendiHetek(array('tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt']));
    		} else {
		    $ADAT['Napok'] = getTanevNapjai($munkatervId);
		}

	    } else {
		$ADAT['Hetek'] = getOrarendiHetek(array('tolDt'=>$_TANEV['kezdesDt'],'igDt'=>$_TANEV['zarasDt']));
	    }
	} else { // nem admin, nem vezető --> csak éves munkatervet lát (ha van)
	    $ADAT['Napok'] = getTanevNapjai($munkatervId);
	}
	$ADAT['NapokSzama'] = getNapokSzama(array('munkatervId' => $munkatervId));


	if (count($ADAT['munkaterv']) > 1) {
    	    $TOOL['munkatervSelect'] = array('tipus' => 'cella','paramName' => 'munkatervId', 'post' => array());
    	    getToolParameters();

	}

    }

?>
