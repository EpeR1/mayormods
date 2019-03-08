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
	$dt = readVariable($_POST['dt'], 'date');
	if (!isset($dt)) {
            $tolTime = strtotime($_TANEV['kezdesDt']);
            $igTime = min(time(), strtotime($_TANEV['zarasDt']));
	    $dt = $_TANEV['kezdesDt'];
            for ($t = $tolTime; $t <= $igTime; $t = strtotime("next month", $t)) $dt = date('Y-m-d', $t);
	}
	$ADAT['dt'] = $dt;
	$osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'], 'id');
	if (isset($osztalyId)) {
	    // Intézmény adatai
	    $ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
	    // Osztály adatai
	    $ADAT['osztaly'] = getOsztalyAdat($osztalyId, __TANEV);
	    // A tanárok
	    $ADAT['tanarok'] = getTanarok(array('result' => 'assoc'));
	    // Az osztály tanárai
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
	    // Az osztály tagjai
	    $ADAT['diakIds'] = getDiakok(array('osztalyId' => $osztalyId, 'result' => 'idonly','override' => false));
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
                $tmpArray = array_merge($ADAT['diakAdat'][$diakId], $sniDA, getHaviOsszegzes($diakId, $dt));
                if (is_array($tmpArray['felelos'])) foreach ($tmpArray['felelos'] as $key => $tanarId) $felelos[$tanarId] = array();
		else $felelos = array();
                $tmpArray['felelos'] = $felelos;
                $ADAT['diakAdat'][$diakId] = $tmpArray;
	    }
	    $ADAT['file'] = fileNameNormal('haviJegyzokonyv-'.$ADAT['osztaly']['osztalyJel'].'-'.str_replace('-','',$dt));
	    if (generateJegyzokonyv($ADAT))
		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/sniHaviJegyzokonyv&file='.$ADAT['file'].'.pdf'));

	}
	// Tool
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post' => array('dt'));
        $TOOL['datumSelect'] = array(
            'tipus' => 'sor', 'ParamName' => 'dt', 'tanev' => __TANEV, 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
            'hanyNaponta' => 'havonta', 'post' => array('diakId', 'osztalyId')
        );
	getToolParameters();
    }

?>
