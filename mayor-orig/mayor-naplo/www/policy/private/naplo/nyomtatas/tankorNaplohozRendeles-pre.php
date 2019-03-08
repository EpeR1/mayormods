<?php

    if (_RIGHTS_OK !== true) die();
    
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');

	$osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned');

	// Az egy osztályhoz rendelt tankörök betöltése a naplóba - de csak azoknál az osztályoknál, ahol nincs hozzárendelés még
	tankorNaploInit();

	// Az adott szemeszter tanköreit lekérdezzük
	$ADAT['tankorok'] = getTankorByTanev(__TANEV);
	$ADAT['tankorIds'] = $ADAT['tankorNeve'] = array();
	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
	    $ADAT['tankorIds'][] = $ADAT['tankorok'][$i]['tankorId'];
	    $ADAT['tankorNeve'][ $ADAT['tankorok'][$i]['tankorId'] ] = $ADAT['tankorok'][$i]['tankorNev'];
	}
	// A tankorok osztályainak lekérdezése
	$ret = getOsztalyIdByTankorIds($ADAT['tankorIds']);
	$ADAT['tankorOsztalyai'] = $ADAT['osztalyTankorei'] = array();
	for ($i = 0; $i < count($ret); $i++) {
	    $ADAT['tankorOsztalyai'][ $ret[$i]['tankorId'] ][] = $ret[$i]['osztalyId'];
	    $ADAT['osztalyTankorei'][ $ret[$i]['osztalyId'] ][] = $ret[$i]['tankorId'];
	}
	if ($action == 'hozzarendelesekTorlese') {
	    tankorNaploInit($torlessel = true);
	} elseif ($action == 'tankorNaplohozRendeles') {

	    if (is_array($_POST['T']) && count($_POST['T']) > 0) {
		tankorNaplohozRendeles($osztalyId, $_POST['T']);
	    }

	} // action


	// A naplókhoz rendelt tanköröket lekérdezzük
	$ADAT['tankorNaploja'] = getTankorokNaploja();
	$ADAT['naploTankorei'] = array();
	foreach ($ADAT['tankorNaploja'] as $_tankorId => $_osztalyId) {
	    $ADAT['naploTankorei'][$_osztalyId][] = $_tankorId;
	}
	// osztályok lekérdezése
	$ADAT['osztalyok'] = getOsztalyok();
	$ADAT['osztalyJele'] = array();
	for ($i =0; $i < count($ADAT['osztalyok']); $i++) $ADAT['osztalyJele'][ $ADAT['osztalyok'][$i]['osztalyId'] ] = $ADAT['osztalyok'][$i]['osztalyJel'];

        $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'osztalyok' => $ADAT['osztalyok'], 'post' => array());
        getToolParameters();

    }

?>
