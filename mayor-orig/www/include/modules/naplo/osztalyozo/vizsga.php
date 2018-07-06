<?php

    function vizsgajelentkezes($ADAT) {

	$q = "INSERT INTO vizsga (diakId, targyId, evfolyam, evfolyamJel, felev, tipus, jelentkezesDt) VALUES (%u, %u, %u, '%s', %u, '%s', '%s')";
	$v = array($ADAT['diakId'], $ADAT['targyId'], $ADAT['evfolyam'], $ADAT['evfolyamJel'], $ADAT['felev'], $ADAT['tipus'], $ADAT['jelentkezesDt']);
	return db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgajelentkezes', 'result' => 'insert', 'values' => $v));

    }

    function vizsgaIdopontRogzites($VD) {


	foreach ($VD as $vizsgaId => $vizsgaDt) {
	    $vizsgaAdat = getVizsgaAdatById($vizsgaId);
	    if ($vizsgaAdat['vizsgaDt'] == '') {
		$q = "UPDATE vizsga SET vizsgaDt='%s' WHERE vizsgaId=%u";
		$v = array($vizsgaDt, $vizsgaId);
		db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaIdopontRogzites', 'values' => $v));
	    } else {
		$_SESSION['alert'][] = 'message:wrong_data:már van vizsgaDt:vizsgaId='.$vizsgaId;
	    }
	}

    }

    function vizsgaHalasztas($HD) {

	global $ZaradekIndex;

	foreach ($HD as $vizsgaId => $halasztasDt) {
	    $vizsgaAdat = getVizsgaAdatById($vizsgaId);
	    if ($vizsgaAdat['vizsgaDt'] != '' && strtotime($vizsgaAdat['vizsgaDt']) < strtotime($halasztasDt) && !isset($vizsgaAdat['zaradekId'])) {
		// vizsgahalasztás záradékai
		$zaradekIndex = $ZaradekIndex['vizsga halasztás'][ $vizsgaAdat['tipus'] ];
		$Z = array(
		    'zaradekIndex' => $zaradekIndex,
		    'diakId' => $vizsgaAdat['diakId'],
		    'dt' => date('Y-m-d'),
		    'csere' => array('%igDt%' => $halasztasDt)
		);
		$zaradekId = zaradekRogzites($Z);
		// eredeti vizsga záradékolása
		$q = "UPDATE vizsga SET zaradekId=%u WHERE vizsgaId=%u";
		$v = array($zaradekId, $vizsgaId);
		db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaHalasztas/záradékolás', 'values' => $v));
		// új vizsga felvétele
//		$q = "INSERT INTO vizsga (diakId, targyId, evfolyam, felev, tipus, jelentkezesDt, vizsgaDt) VALUES (%u, %u, %u, %u, '%s', '%s', '%s')";
//		$v = array($vizsgaAdat['diakId'], $vizsgaAdat['targyId'], $vizsgaAdat['evfolyam'], $vizsgaAdat['felev'], $vizsgaAdat['tipus'], $vizsgaAdat['jelentkezesDt'], $halasztasDt);
// A halasztáskor megadott dátum nem a vizsga dátuma, hanem egy határidő, amíg le kell tenni a vizsgát.
		$q = "INSERT INTO vizsga (diakId, targyId, evfolyam, evfolyamJel, felev, tipus, jelentkezesDt) VALUES (%u, %u, %u, '%s', %u, '%s', '%s')";
		$v = array($vizsgaAdat['diakId'], $vizsgaAdat['targyId'], $vizsgaAdat['evfolyam'], $ADAT['evfolyamJel'], $vizsgaAdat['felev'], $vizsgaAdat['tipus'], $vizsgaAdat['jelentkezesDt']);
		db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgajelentkezes', 'result' => 'insert', 'values' => $v));

	    } else {
		$_SESSION['alert'][] = 'message:wrong_data:még nincs vizsgaDt, vagy korábbi, mint a halasztás dátuma:vizsgaId='.$vizsgaId;
	    }
	}

    }

    function vizsgaErtekeles($jegyek) {

	global $KOVETELMENY, $ZaradekIndex;

	if (is_array($jegyek)) foreach ($jegyek as $vizsgaId => $jegyAdat) {
	    $vizsgaAdat = getVizsgaAdatById($vizsgaId);
// dump($vizsgaAdat);
	    // A beírandó jegy adatai szinkronban kell legyenek a vizsga adataival
	    $jegyAdat['felev'] = $vizsgaAdat['felev'];
	    $jegyAdat['diakId'] = $vizsgaAdat['diakId'];
	    $jegyAdat['targyId'] = $vizsgaAdat['targyId'];
	    $jegyAdat['evfolyamJel'] = $vizsgaAdat['evfolyamJel'];
	    $jegyAdat['evfolyam'] = $vizsgaAdat['evfolyam'];

	    $targyAdat = getTargyById($vizsgaAdat['targyId']);
	    $bukas = (in_array($jegyAdat['jegy'], $KOVETELMENY[ $jegyAdat['jegyTipus'] ]['sikertelen']));
	    if ($vizsgaAdat['vizsgaDt'] != '' && !isset($vizsgaAdat['zaroJegyId']) && !isset($vizsgaAdat['zaradekId'])) {
		// vizsga értékelés záradékai
		if ($bukas) {
		    if (
			$jegyAdat['jegyTipus'] != 'jegy' 
		        && $jegyAdat['jegyTipus'] != 'féljegy'
			&& $vizsgaAdat['tipus'] == 'javítóvizsga'
		    ) $zaradekIndex = $ZaradekIndex['vizsga'][$vizsgaAdat['tipus'].' nem teljesített'];
		    else $zaradekIndex = $ZaradekIndex['vizsga'][$vizsgaAdat['tipus'].' bukás'];
		} else { $zaradekIndex = $ZaradekIndex['vizsga'][ $vizsgaAdat['tipus'] ]; }
		$Z = array(
		    'zaradekIndex' => $zaradekIndex,
		    'diakId' => $vizsgaAdat['diakId'],
		    'dt' => $vizsgaAdat['vizsgaDt'], // date('Y-m-d'),
		    'csere' => array(
			'%tantárgy%' => $targyAdat['targyNev'], 
			'%dátum%' => str_replace('-','.',$vizsgaAdat['vizsgaDt']),
			'%osztályzat%' => $KOVETELMENY[ $jegyAdat['jegyTipus'] ][ $jegyAdat['jegy'] ]['rovid'].' ('.$KOVETELMENY[ $jegyAdat['jegyTipus'] ][ $jegyAdat['jegy'] ]['hivatalos'].')',
			'%évfolyam%' => $vizsgaAdat['evfolyamJel'] . (($jegyAdat['felev'] == 2) ? '.':'. ('.$jegyAdat['felev'].'. félév)') // +1 - vajon miért volt?
		    )
		);
		// Ez hibás így!
		// - lehet, hogy több tárgyból is bukott! Akkor egy sikeres javítóvizsga nem jelenti azt, hogy tovább léphet
		if ($vizsgaAdat['tipus'] == 'javítóvizsga' && !$bukas) $Z['csere']['%évfolyam%'] = getKovetkezoEvfolyamJel($vizsgaAdat['evfolyamJel']).'.'; // következő évfolyamba léphet
		$zaradekId = zaradekRogzites($Z);
		// vizsga zárójegyének beírása
		$jegyAdat['dt'] = $vizsgaAdat['vizsgaDt']; // -- ez elavult
		$jegyAdat['hivatalosDt'] = $vizsgaAdat['vizsgaDt'];
		if ($jegyAdat['evfolyamJel'] != '') $jegyAdat['evfolyam'] = evfolyamJel2evfolyam($jegyAdat['evfolyamJel']);
		$zaroJegyId = zaroJegyBeiras(array($jegyAdat));
		// eredeti vizsga záradékolása és zárójegyhez kötése
		$q = "UPDATE vizsga SET zaradekId=%u, zaroJegyId=%u WHERE vizsgaId=%u";
		$v = array($zaradekId, $zaroJegyId, $vizsgaId);
		db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaHalasztas/záradékolás', 'values' => $v));
	    }
	}

    }

    function vizsgaTorlese($vizsgaId) {

	$v = array($vizsgaId);
	
	// Elároljuk a vizsgához tartozó zárójegyId-t
	$q = "SELECT zaroJegyId FROM vizsga WHERE vizsgaId=%u";
	$zaroJegyId = db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaTorlese/zárójegy törlése', 'result' => 'value', 'values' => $v));
	// Töröljük a vizsgához tartozó zárasékot - és ezzel a vizsgát is
	$q = "DELETE FROM zaradek WHERE zaradekId=(SELECT zaradekId FROM vizsga WHERE vizsgaId=%u)";
	db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaTorlese/zárójegy törlése', 'values' => $v));
	// Töröljük az eltárolt vizsgajegyet
	$q = "DELETE FROM zaroJegy WHERE zaroJegyId=%u";
	db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaTorlese/zárójegy törlése', 'values' => array($zaroJegyId)));
	// Ha netán még nem törlődött volna a függőségek miatt, akkor most töröljük a vizsgát
	$q = "DELETE FROM vizsga WHERE vizsgaId=%u";
	return db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'vizsgaTorlese', 'values' => $v));
    
    }

?>
