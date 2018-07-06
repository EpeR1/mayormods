<?php
/*
    TODO: evfolyamJel szűrés ellenőrzés!!
*/

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/targy.php');

	if (intval($_POST['tanev'])!=0) $tanev = intval($_POST['tanev']);
	else $tanev = __TANEV;

	$tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
	$osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);

	$targyIds = $ADAT['targySzuro'] = $ADAT['evfolyamJelSzuro'] = array();
	$ADAT['evfolyamJelek'] = getEvfolyamJelek(array('result'=>'idonly'));

	if (isset($osztalyId)) {
	    $ADAT['osztalySzuro'] = array($osztalyId);
	} else {
	    // Évfolyam szerinti szűréshez - évfolyamok
	    $ADAT['evfolyamJelSzuro'] = readVariable($_POST['evfolyamJel'], 'enum', null, $ADAT['evfolyamJelek']);
	    $ADAT['osztalySzuro'] = getOsztalyIdByEvfolyamJel($ADAT['evfolyamJelSzuro'], $tanev);
	}
	if (count($ADAT['osztalySzuro']) > 1) $ADAT['osztalySzurtTankorIds'] = getTankorByOsztalyIds($ADAT['osztalySzuro'], $tanev);
	elseif (count($ADAT['osztalySzuro']) > 0) {
	    $osztalyId = $ADAT['osztalySzuro'][0];
	    $ADAT['osztalySzurtTankorIds'] = getTankorByOsztalyId($osztalyId, $tanev, array('csakId' => true));
	} else $ADAT['osztalySzurtTankorIds'] = array();
	// Tárgy szerinti szűréshez - tárgyak
	$ADAT['targyak'] = getTargyak();
	for ($i = 0; $i < count($ADAT['targyak']); $i++) $targyIds[] = $ADAT['targyak'][$i]['targyId'];
	if (is_array($_POST['targyId'])) {
	    for ($i = 0; $i < count($_POST['targyId']); $i++) {
		$targyId = readVariable($_POST['targyId'][$i], 'numeric unsigned', null, $targyIds);
		if (isset($targyId)) {
		    $ADAT['targySzuro'][] = $targyId;
		}
	    }
	}

	if ($action == 'ujTankorBlokk') {

		$_DATA = array();
		$_DATA['blokkNev'] = readVariable($_POST['blokkNev'],'string');
		$_DATA['exportOraszam'] = readVariable(str_replace(',', '.', $_POST['exportOraszam']), 'float unsigned');
		$_DATA['tankorId'] = readVariable($_POST['tankorId'], 'numeric unsigned');
		ujTankorBlokk($_DATA['blokkNev'], $_DATA['exportOraszam'], $_DATA['tankorId'], $tanev);

	} elseif ($action=='modTankorBlokk') {

		$_DATA['tanev'] = $tanev;
		$_DATA['blokkId'] = readVariable($_POST['blokkId'], 'numeric unsigned');
		$_DATA['exportOraszam'] = readVariable(str_replace(',', '.', $_POST['exportOraszam']), 'float unsigned');
		$_DATA['blokkNev'] = readVariable($_POST['blokkNev'], 'string');
		$_DATA['tankorIds'] = readVariable($_POST['tankorIds'], 'numeric unsigned');
//		for ($i = 0; $i < count($_POST['tankorIds']); $i++) {
//		    if (intval($_POST['tankorIds'][$i])) $_DATA['tankorIds'][] = intval($_POST['tankorIds'][$i]);
//		}
		if (isset($_POST['del']) && $_POST['del'] != '') 
		    tankorBlokkTorles($_DATA);
		else
		    tankorBlokkModositas($_DATA);
		
	}

	$tankorok= getTankorok(
	    array("tanev=$tanev"), 
	    "LPAD(substring_index(substring_index(tankorNev,'-',1),'.',1),2,'0'),substring_index(substring_index(tankorNev,' ',2),' ',-1),tankorNev,tanev,szemeszter"
	);
	if (count($ADAT['targySzuro']) > 0)
	    $szurtTankorok = getTankorok( // tárgy szerinti szűrés
		array("tanev=$tanev", 'targyId IN ('.implode(',', $ADAT['targySzuro']).')'), 
		"LPAD(substring_index(substring_index(tankorNev,'-',1),'.',1),2,'0'),substring_index(substring_index(tankorNev,' ',2),' ',-1),tankorNev,tanev,szemeszter"
	    );
	else $szurtTankorok = $tankorok;

	$ADAT['tankorOraszam'] = getTankorOraszamByTanev($tanev);
	$ADAT['tankorExportOraszam'] = getTankorExportOraszamByTanev($tanev);

	$ADAT['tankorBlokkok'] = getTankorBlokkok($tanev);
	for ($i = 0; $i < count($tankorok); $i++) {
	    $ADAT['tankorIdk'][] = $tankorId = $tankorok[$i]['tankorId'];
	    $ADAT['tankorAdat'][$tankorId] = $tankorok[$i];
	}
	$ADAT['tankorTanar'] = getTankorTanaraiByInterval($ADAT['tankorIdk'], array('tanev' => $tanev, 'result' => 'assoc'));
	for ($i = 0; $i < count($szurtTankorok); $i++) {
	    if ( // osztály szerinti szűrés
		count($ADAT['osztalySzurtTankorIds']) == 0 
		|| in_array($szurtTankorok[$i]['tankorId'], $ADAT['osztalySzurtTankorIds'])
	    ) $ADAT['szurtTankorIdk'][] = $szurtTankorok[$i]['tankorId'];
	}
	if (count($ADAT['szurtTankorIdk']) == 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:A megadott tárgyakhoz nem tartozik egy tankör sem - a szűrési feltételt töröljük!';
	    $ADAT['szurtTankorIdk'] = $ADAT['tankorIdk'];
	    $ADAT['targySzuro'] = $ADAT['osztalySzuro'] = $_POST['targyId'] = array();
	    unset($osztalyId);
	}
	$ADAT['tanev'] = $tanev;

	$TOOL['tanevSelect'] = array('tipus'=>'cella', 'paramName'=>'tanev','post'=>array());
        $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('targyId'));
	getToolParameters();
    }

?>
