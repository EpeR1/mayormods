<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {

        $_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/sni.php');
	require_once('include/modules/naplo/share/tankor.php');

	// Paraméterek
	$diakId = readVariable($_POST['diakId'], 'id');
	if (isset($diakId)) $ADAT['diakIds'] = array($diakId);
	$osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'], 'id');
	if (isset($osztalyId)) {
	    // Az osztály tagjai
	    $ADAT['diakIds'] = getDiakok(array('osztalyId' => $osztalyId, 'result' => 'idonly','override' => false));
	}
	if (is_array($ADAT['diakIds']) && count($ADAT['diakIds']) > 0) {
	    // Intézmény adatai
	    $ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
	    // Osztály adatai
	    if (isset($osztalyId)) $ADAT['osztaly'] = getOsztalyAdat($osztalyId, __TANEV);
	    // A tanárok
	    $ADAT['tanarok'] = getTanarok(array('result' => 'assoc'));
	    // Az osztály tanárai
	    // nincs feltétlen osztály // 
/*
	    $Tankorok = getTankorByOsztalyId($osztalyId, __TANEV, array('tanarral' => true));
	    $ADAT['osztalyTanar'] = array(); $paros = false;
	    for ($i = 0; $i < count($Tankorok); $i++) {
		for ($j = 0; $j < count($Tankorok[$i]['tanarok']); $j++) {
		    if (!is_array($ADAT['osztalyTanar'][ $Tankorok[$i]['tanarok'][$j]['tanarId'] ])) {
			$ADAT['osztalyTanar'][ $Tankorok[$i]['tanarok'][$j]['tanarId'] ] = $ADAT['tanarok'][ $Tankorok[$i]['tanarok'][$j]['tanarId'] ];
			$ADAT['osztalyTanar'][ $Tankorok[$i]['tanarok'][$j]['tanarId'] ]['paros'] = $paros;
			$paros = !$paros;

		    }
		}
	    }
*/
	    if (is_array($ADAT['diakIds']) && count($ADAT['diakIds']) > 0) {
		// a diákok alapadatai
		$ADAT['diakAdat'] = getDiakAdatById($ADAT['diakIds'], array('result' => 'assoc', 'keyfield' => 'diakId'));
		// SNI-s diákok kiválogatása...
		$ADAT['sniDiakIds'] = $Diakok = array();
		foreach ($ADAT['diakAdat'] as $_diakId => $dAdat) {
		    if ($dAdat['fogyatekossag'] != '') {
			$ADAT['sniDiakIds'][] = $_diakId;
			$dAdat['aktualisStatusz'] = $dAdat['statusz'];
			$Diakok[] = $dAdat;
		    }
		}
	    }


	    // sni-s diákokon végigmenve:
	    foreach ($ADAT['sniDiakIds'] as $diakId) {
		// Mentor/Ofő lekérdezése, konstans beállítása
		$sniDA = getSniDiakAdat($diakId);
		if (!is_array($sniDA)) $sniDA = array();
		$DA = array_merge($ADAT['diakAdat'][$diakId], $sniDA);
            	if (is_array($DA['felelos'])) foreach ($DA['felelos'] as $key => $tanarId) $felelos[$tanarId] = array();
		else $felelos = array();
            	$DA['felelos'] = $felelos;
		// A hónapokon végigmenve
		$ho = intval(substr($_TANEV['kezdesDt'],5,2));
		$dt = date('Y-m-01', strtotime($_TANEV['kezdesDt']));
		while ($ho != 1+substr($_TANEV['zarasDt'],5,2)) {
            	    $ADAT['diakAdat'][$diakId]['honap'][$ho] = getHaviOsszegzes($diakId, $dt);
		    $ADAT['diakAdat'][$diakId]['honap'][$ho]['hoNev'] = kisbetus($Honapok[$ho-1]);
		    // lépés a következő hónapra
		    $ho++; if ($ho > 12) $ho = 1;
		    $dt = date('Y-m-d', strtotime('next month', strtotime($dt)));
		}
	    }
	    if (isset($osztalyId)) $ADAT['file'] = fileNameNormal('evVegiJegyzokonyv-'.$ADAT['osztaly']['osztalyJel'].'-'.str_replace('-','',$dt));
	    else $ADAT['file'] = fileNameNormal('evVegiJegyzokonyv-'.$diakId.'-'.str_replace('-','',$dt));
	    if (generateJegyzokonyv($ADAT))
		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/sniEvVegiJegyzokonyv&file='.$ADAT['file'].'.pdf'));

	}
	// Tool
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array('dt'));
        $TOOL['diakSelect'] = array(
    //        'diakok' => $Diakok,
            'tipus'=>'cella','paramName' => 'diakId',
            'osztalyId'=> $osztalyId,'post' => array('osztalyId'),
            'statusz' => array('jogviszonyban van','magántanuló','egyéni munkarend','jogviszonya felfüggesztve','jogviszonya lezárva')
        );

	getToolParameters();
    }

?>
