<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/jegy.php');
	require_once('include/modules/naplo/share/jegyModifier.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');
	require_once('include/modules/naplo/share/szemeszter.php');

	if (isset($_POST['dt']) && $_POST['dt'] != '') $dt = $ADAT['dt'] = $_POST['dt'];
	elseif (!isset($_POST['dt'])) $dt = $ADAT['dt'] = $_TANEV['zarasDt'];
	// Végzős osztályok lekérdezése
	$ADAT['osztalyok'] = getOsztalyok();
	for ($i = 0; $i < count($ADAT['osztalyok']); $i++) $ADAT['osztalyIds'][] = $ADAT['osztalyok'][$i]['osztalyId'];
	// Lezárandó osztályok
	if (is_array($_POST['lezarandoOsztaly'])) $ADAT['lezarandoOsztaly'] = readVariable($_POST['lezarandoOsztaly'], 'id', null, $ADAT['osztalyIds']);
	else $ADAT['lezarandoOsztaly'] = array();

	// Végzős tanulmány időszak lekérdezése - egyelőre ezt nem csináljuk - adott dátumhoz igazodunk!

	if ($action == 'orarendLezaras' && count($ADAT['lezarandoOsztaly']) > 0) {

	    // Végzős tankörök lekérdezése (csak végzős!!)
	    $ADAT['vegzosTankor'] = getTankorByOsztalyIds($ADAT['lezarandoOsztaly']);
	    // A tanulmányi időszakon túlnyúló órarendi órák tolDt-ének beállítása	    
	    vegzosOrarendLezaras($ADAT);
	    vegzosHaladasiNaploLezaras($ADAT);
	    foreach ($ADAT['lezarandoOsztaly'] as $index => $osztalyId) {
		// osztaly diákjainak lekérdezése
		$Diakok = getDiakok(array('osztalyId' => $osztalyId));
		$diakIds = array();
		for ($i = 0; $i < count($Diakok); $i++) $diakIds[] = $Diakok[$i]['diakId'];
		// osztaly tanköreinek lekérdezése
		$tankorIds = array_values(array_diff(getTankorByOsztalyId($osztalyId, __TANEV, array('csakId' => true)), $ADAT['vegzosTankor']));
		
		if (is_array($tankorIds) && count($tankorIds) > 0) for ($i = 0; $i < count($diakIds); $i++) {
		// A nem csak végzős tankörökben a tankör tagság beállítása
		    $Mod = array(
			'diakId' => $diakIds[$i], 
			'tankorIds' => $tankorIds, 
			'tolDt' => $ADAT['dt'], 
			'igDt' => $_TANEV['zarasDt'], 
			'utkozes' => 'torles'
		    );
		    tankorDiakTorol($Mod);
		}
	    }

	}

        $TOOL['datumSelect'] = array(
            'tipus' => 'cella', 'post' => array('lezarandoOsztaly'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'override'=>true, // használathoz még át kell írni pár függvényt!!!
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
        );
	getToolParameters();

    }

?>
