<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/vizsga.php');
	require_once('include/modules/naplo/share/zaradek.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/zaroJegyModifier.php');

	$ADAT['evfolyamJelek'] = getEvfolyamJelek(array('result'=>'idonly'));
	$diakId = $ADAT['diakId'] = readVariable($_POST['diakId'], 'id', readVariable($_GET['diakId'], 'id'));
	$targyId = $ADAT['targyId'] = readVariable($_POST['targyId'], 'id', readVariable($_GET['targyId'], 'id'));
	$evfolyamJel = $ADAT['evfolyamJel'] = readVariable(
	    $_POST['evfolyamJel'], 'enum', readVariable($_GET['evfolyamJel'], 'enum', null, $ADAT['evfolyamJelek']), $ADAT['evfolyamJelek']
	);
	if (isset($evfolyamJel)) $evfolyam = $ADAT['evfolyam'] = evfolyamJel2Evfolyam($evfolyamJel);

	$ADAT['tanev'] = $_TANEV['tanev']; // ezt ellenőrizni
	$felev = readVariable($_POST['felev'], 'numeric unsigned');
	if ($felev=='') foreach($_TANEV['szemeszter'] as $felev => $_SZA);
	$ADAT['felev'] =  $felev; // maxSzemeszter
	$ADAT['vizsgatipusok'] = getEnumField('naplo_intezmeny', 'vizsga', 'tipus');
	$ADAT['zarojegytipusok'] = getEnumField('naplo_intezmeny', 'zaroJegy', 'jegyTipus');
        $ADAT['statusz'] = getEnumField('naplo_intezmeny', 'diak', 'statusz');
	$ADAT['vizsgaTipus'] = $vizsgaTipus = readVariable($_POST['vizsgaTipus'], 'enum', null, $ADAT['vizsgatipusok']);
	if ($action == 'vizsgajelentkezes' && isset($diakId) && isset($targyId) && isset($evfolyamJel)) {
	    $ADAT['jelentkezesDt'] = readVariable($_POST['jelentkezesDt'], 'date');
	    if (isset($ADAT['jelentkezesDt']) && isset($ADAT['vizsgaTipus'])) {
		vizsgajelentkezes($ADAT);
	    }
	} elseif ($action == 'vizsgaKezeles') {
	    $vizsgaDatum = $halasztasDatum = array();

	    // Vizsgaidőpontok kijelölése
	    $vizsgaIds = readVariable($_POST['vizsgaDtVizsgaIds'], 'numeric unsigned');
	    $vizsgaDts = readVariable($_POST['vizsgaDts'], 'date', '');
	    for ($i = 0; $i < count($vizsgaDts); $i++) if ($vizsgaDts[$i] != '') $vizsgaDatum[ $vizsgaIds[$i] ] = $vizsgaDts[$i];
	    vizsgaIdopontRogzites($vizsgaDatum);

	    $erthalVizsgaIds = readVariable($_POST['erthalVizsgaIds'], 'numeric unsigned');
	    // Vizsga halasztása
	    $halasztasDts = readVariable($_POST['halasztasDts'], 'date', '');
	    for ($i = 0; $i < count($halasztasDts); $i++) if ($halasztasDts[$i] != '') $halasztasDatum[ $erthalVizsgaIds[$i] ] = $halasztasDts[$i];
	    vizsgaHalasztas($halasztasDatum);
	    // Vizsga értékelés
	    $jegyAdat = $_POST['jegyAdat'];
	    for ($i = 0; $i < count($jegyAdat); $i++) if ($jegyAdat[$i] != '') {
                    $X = explode('|', $jegyAdat[$i]);
                    for ($j = 0; $j < count($X); $j++) {
                        list($key, $value) = explode('=', $X[$j]);
			$jegyek[ $erthalVizsgaIds[$i] ][$key] = $value;
                    }
	    }
	    vizsgaErtekeles($jegyek);
	} elseif ($action == 'vizsgaTorlese') {
	    $vizsgaId = readVariable($_GET['vizsgaId'], 'id');
	    if (isset($vizsgaId)) vizsgaTorlese($vizsgaId);
	}
	$ADAT['vizsga'] = $ADAT['diakIds'] = $ADAT['targyIds'] = array();
	if (isset($diakId) || isset($targyId)) $ADAT['vizsga'] = getVizsgak($ADAT);

	for ($i = 0; $i < count($ADAT['vizsga']); $i++) {
	    if (!in_array($ADAT['vizsga'][$i]['diakId'], $ADAT['diakIds'])) $ADAT['diakIds'][] = $ADAT['vizsga'][$i]['diakId'];
	    if (!in_array($ADAT['vizsga'][$i]['targyId'], $ADAT['targyIds'])) $ADAT['targyIds'][] = $ADAT['vizsga'][$i]['targyId'];
	}
	if (count($ADAT['targyIds']) > 0) $ADAT['targyak'] = getTargyAdatByIds($ADAT['targyIds']);
	if (count($ADAT['diakIds']) > 0) $ADAT['diakok'] = getDiakokById($ADAT['diakIds']);


	$TOOL['diakSelect'] = array('tipus'=>'cella', 'paramName'=>'diakId', 'post'=>array('targyId','jelentkezesDt', 'vizsgaDt', 'evfolyamJel', 'felev','vizsgaTipus'), 'statusz'=>$ADAT['statusz']);
	$TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'post' => array('diakId', 'evfolyamJel', 'felev','vizsgaTipus'));
	$TOOL['evfolyamJelSelect'] = array(
                'tipus' => 'cella', 'paramName' => 'evfolyamJel', 'paramDesc'=>'evfolyamJel','adatok' => getEvfolyamJelek(),
        	'post' => array('targyId', 'diakId', 'jelentkezesDt', 'vizsgaDt','felev','vizsgaTipus')
        );
	$TOOL['felevSelect'] = array(
                'tipus' => 'cella', 'paramName' => 'felev', 'post' => array('targyId', 'diakId', 'jelentkezesDt', 'vizsgaDt', 'evfolyamJel','vizsgaTipus')
        );
	for ($i=0; $i<count($ADAT['vizsgatipusok']); $i++) $toolData[$i] = array('vizsgaTipus'=>$ADAT['vizsgatipusok'][$i]);
	$TOOL['vizsgatipusSelect'] = array(
	    'tipus' => 'cella', 'paramName' => 'vizsgaTipus', 'paramDesc' => 'vizsgaTipus', 'post' => array('targyId', 'diakId', 'jelentkezesDt', 'vizsgaDt', 'evfolyamJel','felev'), 
	    'adatok' => $toolData, 'title'=>'VIZSGATIPUS'
	);

	getToolParameters();
    }

?>
