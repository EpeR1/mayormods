<?php

    /*
	A loadFile() függvény a paraméterül kapott $fileName nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
	és berakja a $OrarendiOra globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

	------------------------------------------

	Az alábbi példa egy speciális, tabulátorokkal tagolt file formátumot dolgoz fel.
	Ebben egy-egy sor (pontosabban két-két sor) egy-egy tanár óráit írja le.
	A tanárokat a nevük alapján azonosítjuk.

    */

    function loadFile($ADAT) {

	// !! Sajnos az összevont cellákkal baj van !! //

	global $OrarendiOra;
	$OrarendiOra = array();

	require_once('include/modules/naplo/share/tanar.php');
	$Tanarok = getTanarok(array('tanev' => __TANEV, 'result' => 'assoc'));
	foreach ($Tanarok as $tanarId => $tanarAdat) $Tanar[ $tanarAdat['tanarNev'] ] = $tanarId;

	$fp = fopen($ADAT['fileName'], 'r');
	if (!$fp) return false;

	// Az első sor a napok neveit tartalmazza
	$Napok = explode('	', chop(fgets($fp, 1024)));
	for ($i = 0; $i < count($Napok); $i++)
	    if ($Napok[$i] == 'Hétfő') $Napok[$i] = 1;
	    elseif ($Napok[$i] == 'Kedd') $Napok[$i] = 2;
	    elseif ($Napok[$i] == 'Szerda') $Napok[$i] = 3;
	    elseif ($Napok[$i] == 'Csütörtök') $Napok[$i] = 4;
	    elseif ($Napok[$i] == 'Péntek') $Napok[$i] = 5;
	    elseif ($Napok[$i] == 'Szombat') $Napok[$i] = 6;
	    elseif ($Napok[$i] == 'Vasárnap') $Napok[$i] = 7;
	// A második sor az adott napon belöli óraszámot adja
	$oraszamok = explode('	', chop(fgets($fp, 1024)));
	$return = true;
	while ($sor = fgets($fp, 1024)) {

	    // Két sor egy tanár órarendjét tartalmazza
	    $S1 = explode('	', chop($sor));
	    $S2 = explode('	', chop(fgets($fp, 1024)));
	    // Az első sor első mezője a tanár neve
	    if ($Tanar[ $S1[0] ] != '') {
		$tanar = $Tanar[ $S1[0] ];
	    } else {
		$_SESSION['alert'][] = 'message:wrong_data:tanarNev='.$S1[0]; // hiányzó vagy hibás tanárnév esetén nem tudjuk betölteni az órarendet
		$return = false;
		$tanar = $S1[0] . ' / ' . $Tanar[ $S1[0] ];
	    }
	    // A második cellátol jönnek az órák - első sor a tárgy, második az osztály
	    for ($i = 1; $i < count($S1); $i++) {
		if ($Napok[$i] != 0) $nap = $Napok[$i];
		if ($S1[$i] != '') {

		    $ora = $oraszamok[$i];
		    $targy = $S1[$i];
		    $osztaly = $S2[$i];

		    $OrarendiOra[] = array(
			$ADAT['orarendiHet'],$nap,$ora,$tanar,$osztaly,$targy,$ADAT['tolDt'],$ADAT['igDt']
		    );

		}

	    }

	}

	fclose($fp);
	return $return;

    }

?>
