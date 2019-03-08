<?php

    /*
	A loadFile() függvény a paraméterül kapott $fileName nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
	és berakja a $OrarendiOra globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

	----------------------------------------

	Az aSc Timetables Excel exportjának feldolgozása - XML mentés után!

    */

    function loadFileMsXML($ADAT) {

	global $OrarendiOra;

	$OrarendiOra = $TEREM = $TANAR = array();
	$return = true;

	// A tanarNev --> tanarId konverzióhoz lekérdezzük a tanárok adatait.
	require_once('include/modules/naplo/share/tanar.php');
	$Tanarok = getTanarok(array('tanev' => __TANEV, 'result' => 'assoc'));
	foreach ($Tanarok as $tanarId => $tanarAdat) $Tanar[ $tanarAdat['tanarNev'] ] = $tanarId;
	// A terem --> teremId konverzióhoz lekérdezzük a terem adatokat
	require_once('include/modules/naplo/share/terem.php');
	$Termek = getTermek();
	for ($i = 0; $i < count($Termek); $i++) {
	    $TeremByLeiras[ $Termek[$i]['leiras'] ] = $Termek[$i]['teremId'];
	    $TeremById[ $Termek[$i]['teremId'] ] = $Termek[$i]['teremId'];
	}

	$dom = DOMDocument::load( $ADAT['fileName'] );
	if (!$dom) {
	    $_SESSION['alert'][] = 'message:file:'.$ADAT['fileName'];
	    return false;
	}

	$worksheets = $dom->getElementsByTagName( 'Worksheet' );
	foreach ($worksheets as $worksheet) {
	    $TMP = array();
	    $name = $worksheet->getAttribute( 'ss:Name' );
	    $rows = $worksheet->getElementsByTagName( 'Row' );
	    $r = 0;
	    foreach ($rows as $row) {
		    $s = 0;
		    $cells = $row->getElementsByTagName( 'Cell' );
		    foreach( $cells as $cell ) { 
			// Összevont cellák esetén a cellatartalmat ismételjük
			$span = $cell->getAttribute( 'ss:MergeAcross' );
			for ($i = 0; $i <= $span; $i++) {
			    $TMP[$r][$s] = $cell->nodeValue;
    			    $s++;
			}
		    }
		    $r++;
	    }
	    if (strstr($name, 'sszesített terem')) $TEREM = $TMP;
	    elseif (strstr($name, 'anárok összesített')) $TANAR = $TMP;
	}
//	var_dump($TEREM);


	// Az első sor a napok neveit tartalmazza
	$Napok = $TANAR[0];
	for ($i = 0; $i < count($Napok); $i++)
	    if ($Napok[$i] == 'Hétfő') $Napok[$i] = 1;
	    elseif ($Napok[$i] == 'Kedd') $Napok[$i] = 2;
	    elseif ($Napok[$i] == 'Szerda') $Napok[$i] = 3;
	    elseif ($Napok[$i] == 'Csütörtök') $Napok[$i] = 4;
	    elseif ($Napok[$i] == 'Péntek') $Napok[$i] = 5;
	    elseif ($Napok[$i] == 'Szombat') $Napok[$i] = 6;
	    elseif ($Napok[$i] == 'Vasárnap') $Napok[$i] = 7;
	// A második sor az adott napon belüli óraszámot adja
	$oraszamok = $TANAR[1];

	// A termeket tartalmazó munkalap feldolgozása
	$s = 2;
	while ($s < count($TEREM)) {

	    // Két sor egy terem órarendjét tartalmazza
	    $S1 = $TEREM[$s];
	    $S2 = $TEREM[$s+1];
	    $s += 2;
	    // Az első sor első mezője a terem neve
	    $terem = $S1[0];
	    // A második cellátol jönnek az órák - első sor a tárgy, második az osztály
	    for ($i = 1; $i < count($S1); $i++) {
		if ($Napok[$i] != 0) $nap = $Napok[$i];
		if ($S1[$i] != '') {

		    $ora = $oraszamok[$i];
		    $targy = $S1[$i];
		    $osztaly = $S2[$i];
		    if ($TeremByLeiras[ $terem ] != '') {
			$TeremRend[$nap][$ora][$osztaly][$targy] = $TeremByLeiras[ $terem ];
		    } elseif ($TeremById[ $terem ] != '') {
			$TeremRend[$nap][$ora][$osztaly][$targy] = $TeremById[ $terem ];
		    } else {
			$TeremRend[$nap][$ora][$osztaly][$targy] = $terem;
			$_SESSION['alert'][] = 'message:wrong_data:terem='.$terem;
			$return = false;
		    }

		}

	    }
	}

	// A tanár-órarendet tartalmazó munkalap feldolgozása
	$s = 2;
	while ($s < count($TANAR)) {

	    // Két sor egy tanár órarendjét tartalmazza
	    $S1 = $TANAR[$s];
	    $S2 = $TANAR[$s+1];
	    $s += 2;
	    // Az első sor első mezője a tanár neve
	    if ($S1[0] != '') {
		if ($Tanar[ $S1[0] ] != '') {	
		    $tanar = $Tanar[ $S1[0] ];
		} else {
		    $tanar = $S1[0];
		    $_SESSION['alert'][] = 'message:wrong_data:tanarNev='.$tanar;
		    $return = false;
		}
		// A második cellátol jönnek az órák - első sor a tárgy, második az osztály
		for ($i = 1; $i < count($S1); $i++) {
		    if ($Napok[$i] != 0) $nap = $Napok[$i];
		    if ($S1[$i] != '') {

			$ora = $oraszamok[$i];
			$targy = $S1[$i];
			$osztaly = $S2[$i];

			$OrarendiOra[] = array(
			    $ADAT['orarendiHet'],$nap,$ora,$tanar,$osztaly,$targy,$TeremRend[$nap][$ora][$osztaly][$targy],$ADAT['tolDt'],$ADAT['igDt']
			);
		    }
		}
	    }
	}

	return $return;

    }

?>
