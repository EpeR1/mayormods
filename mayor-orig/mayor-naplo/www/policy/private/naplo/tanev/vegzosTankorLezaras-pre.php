<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	$_SESSION['alert'][] = 'page:insufficient_access';

// ide ne jöjjünk!




	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/jegy.php');
	require_once('include/modules/naplo/share/jegyModifier.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');


	//if (isset($_POST['dt']) && $_POST['dt'] != '') $dt = $ADAT['dt'] = $_POST['dt'];
	//elseif (!isset($_POST['dt'])) $dt = $ADAT['dt'] = $_TANEV['zarasDt'];

	global $_TANEV;
	$dt = readVariable($_TANEV['zarasDt'], 'datetime');
	$dt_s = strtotime($dt);
	$dt = date('Y-m-d',mktime(0,0,0,date('m',$dt_s),date('d',$dt_s)+1,date('Y',$dt_s)));


	$ADAT['dt'] = $dt;
	$ADAT['tanev'] = __TANEV;
	// Végzős osztályok lekérdezése
	$ADAT['osztalyok'] = getOsztalyok(__TANEV);
	// Lezárandó osztályok
	if (is_array($_POST['lezarandoOsztaly'])) $ADAT['lezarandoOsztaly'] = readVariable($_POST['lezarandoOsztaly'], 'id');
	else $ADAT['lezarandoOsztaly'] = array();

	// Végzős tanulmány időszak lekérdezése - egyelőre ezt nem csináljuk - adott dátumhoz igazodunk!

	if ($action == 'lezaras') {

	    // Végzős tankörök lekérdezése (csak végzős!!)
	    $ADAT['vegzosTankor'] = getTankorByOsztalyIds($ADAT['lezarandoOsztaly']);
	    // A tanulmányi időszakon túlnyúló órarendi órák tolDt-ének beállítása	    
	    //vegzosOrarendLezaras($ADAT);
	    // Az órarendet nem itt zárjuk le!
	    foreach ($ADAT['lezarandoOsztaly'] as $index => $osztalyId) {
		// osztaly diákjainak lekérdezése
		$Diakok = getDiakok(array('osztalyId' => $osztalyId));
		$diakIds = array();
		for ($i = 0; $i < count($Diakok); $i++) $diakIds[] = $Diakok[$i]['diakId'];
		// osztaly tanköreinek lekérdezése
		$tankorIds = getTankorByOsztalyId($osztalyId, __TANEV, array('csakId' => true));
		if (is_array($tankorIds) && count($tankorIds) > 0) for ($i = 0; $i < count($diakIds); $i++) {
		// A nem csak végzős tankörökben a tankör tagság beállítása
		    $Mod = array(
			'diakId' => $diakIds[$i], 
			'tankorIds' => $tankorIds, 
			'tolDt' => $ADAT['dt'], 
			'utkozes' => 'torles'
		    );
		    tankorDiakTorol($Mod);
		}
	    }
	    $_SESSION['alert'][] = 'info:success';

	}

    }

?>
