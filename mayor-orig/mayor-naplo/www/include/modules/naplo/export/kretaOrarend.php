<?php

    function getOrarendAdat($ADAT) {

	global $aHetNapjai, $kretaHETIREND;

	$lr = db_connect('naplo');
	// orarendiOra - tankor
	// Így több hetes órarend esetén mindent külön felvesz az egyes hetekre, nem használja a "Minden hétre" lehetőséget...
	$q = "SELECT * from orarendiOra 
		LEFT JOIN orarendiOraTankor USING (tanarId, targyJel, osztalyJel)
		LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
		WHERE orarendiOra.tolDt <= '%s' AND '%s' <= orarendiOra.igDt
		ORDER BY tanarId, het, nap, ora";
	// A hetek összegét kérdezzük le, ez egy hetes órarend esetén nem változtat semmit
	// Kéthetes órarendnél 1 -> A hét, 2 -> B hét, 1+2=3 -> Minden hét
	// Más esetekben már nem lesz jó...
	$q = "select sum(het) as het,nap,ora,tanarId,osztalyJel,targyJel,teremId,leiras,tankorId from orarendiOra
		LEFT JOIN orarendiOraTankor USING (tanarId, targyJel, osztalyJel)
		LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
		WHERE orarendiOra.tolDt <= '%s' AND '%s' <= orarendiOra.igDt
		group by nap,ora,tanarId,osztalyJel,targyJel,teremId,leiras,tankorId
		ORDER BY tanarId, het, nap, ora";
	$v = array($ADAT['dt'], $ADAT['dt']);
	$ADAT['orak'] = db_query($q, array('fv' => 'getOrarendAdat', 'result'=>'indexed','values'=>$v), $lr);
	$q = "SELECT tankorId, tankorNev, targyNev, kretaTargyNev, csoportNev FROM ".__INTEZMENYDBNEV.".tankor 
		LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId) 
		LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (targyId)
		LEFT JOIN tankorCsoport USING (tankorId)
		LEFT JOIN csoport USING (csoportId)
		WHERE tanev=".__TANEV." AND szemeszter=1";

	$v = array();
	$ADAT['tankor'] = db_query($q, array('fv' => 'getOrarendAdat/tankor', 'result'=>'assoc','keyfield'=>'tankorId','values'=>$v), $lr);
	foreach ($ADAT['tankor'] as $_tankorId => $T) {
	    $M = explode(' ', $T['csoportNev']);


	    // Ha van a csoportnévben vessző vagy aláhúzás, akkor több osztályhoz tartozik, 
	    // pl: "9.a, 9.b tnf", "11. inf_A", "12.inf_E"
	    if (strpos($T['csoportNev'],',') !== false || strpos($T['csoportNev'],'_') !== false) $ADAT['t2osztaly'][$_tankorId] = '';
	    else $ADAT['t2osztaly'][$_tankorId] = $M[0];
	    // Ha nincs benne szóköz és aláhúzás sem, akkor egy osztály jele valójában a MaYoR csoportnév 
	    // csoportot jelöl pl:	"12.inf_E", "9.a csop1", "7.a, 7.b tnl"
	    // osztályt jelöl pl: 	"8.b"
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
		$aHetNapjai[ $O['nap']-1 ],				// Nap
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