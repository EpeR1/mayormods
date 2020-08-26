<?php

    function getOrarendAdat($ADAT) {

	global $aHetNapjai, $kretaHETIREND;

	$lr = db_connect('naplo');
	// orarendiOra - tankor
	$q = "SELECT * from orarendiOra 
		LEFT JOIN orarendiOraTankor USING (tanarId, targyJel, osztalyJel)
		LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
		ORDER BY tanarId, het, nap, ora";
	$v = array();
	$ADAT['orak'] = db_query($q, array('fv' => 'getOrarendAdat', 'result'=>'indexed','value'=>$v), $lr);
	$q = "SELECT tankorId, tankorNev, targyNev, kretaTargyNev, csoportNev FROM ".__INTEZMENYDBNEV.".tankor 
		LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId) 
		LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (targyId)
		LEFT JOIN tankorCsoport USING (tankorId)
		LEFT JOIN csoport USING (csoportId)
		WHERE tanev=".__TANEV." AND szemeszter=1";

	$v = array();
	$ADAT['tankor'] = db_query($q, array('fv' => 'getOrarendAdat/tankor', 'result'=>'assoc','keyfield'=>'tankorId','value'=>$v), $lr);
	foreach ($ADAT['tankor'] as $_tankorId => $T) {
	    $M = explode(' ', $T['csoportNev']);


	    if (strpos($T['csoportNev'],',') !== false) $ADAT['t2osztaly'][$_tankorId] = '';
	    else $ADAT['t2osztaly'][$_tankorId] = $M[0];

	    if (strpos($T['csoportNev'],' ') === false && strpos($T['csoportNev'],'_') === false) $ADAT['t2csoport'][$_tankorId] = '';
	    else $ADAT['t2csoport'][$_tankorId] = $T['csoportNev'];
	}


	db_close($lr);
	$ADAT['export'][] = array(
	    'Hetirend','Nap','Óra (adott napon belül)','Osztály','Csoport','Tantárgy','Tanár','Helyiség'
	);
	foreach ($ADAT['orak'] as $index => $O) {
	    list($helyseg,$_nev) = explode(" - ",$O['leiras']);
	    $ADAT['export'][] = array(
		$kretaHETIREND[ $O['het'] ],				// Hetirend
		$aHetNapjai[ $O['nap'] ],				// Nap
		$O['ora'],						// Óra
		$ADAT['t2osztaly'][ $O['tankorId'] ],			// Osztály
		$ADAT['t2csoport'][ $O['tankorId'] ],			// Csoport
		$ADAT['tankor'][ $O['tankorId'] ]['kretaTargyNev'], 	// Tantárgy
		$ADAT['tanar'][ $O['tanarId'] ]['tanarNev'],		// Tanár
		$helyseg 						// Helyiség
	    );
	}

	return $ADAT['export'];

    }

    function exportKretaOrarend($file, $ADAT) {

        $T = $ADAT['export'];

        if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $T, 'kreta_ETTF_simple');
        elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $T, '');
        elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $T, 'kreta_ETTF_simple');
        else return false;

    }


?>