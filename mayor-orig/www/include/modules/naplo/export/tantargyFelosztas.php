<?php

    function getTankorOraszamok() {

	$q = "SELECT tankorId, tankorNev, targyId, tankorTipusId, tanev, szemeszter, oraszam
		FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		WHERE tanev=".__TANEV;
	$return = db_query($q, array('fv'=>'getTankorOraszamok','modul'=>'naplo_intezmeny','result'=>'indexed'));
	for ($i=0; $i<count($return); $i++) {
	    $return[$i]['tanarIds'] = getTankorTanaraiByInterval($return[$i]['tankorId'], array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result' => 'idonly', 'datumKenyszeritessel' => false));
	    $return[$i]['osztalyIds'] = getTankorOsztalyai($return[$i]['tankorId']);
	}
	return $return;

    }

    function exportTantargyFelosztas($file, $ADAT) {

	$T = array();

	$T[0] = array('név','képesítés/tantárgy','tényleges óraszám','kötelező óraszám','besorolás');
/*
	$T[0] = array('név','képesítés/tantárgy','óraszám');
	foreach ($ADAT['osztalyok'] as $oAdat) { 
	    $T[0][] = $oAdat['osztalyJel'];  
	}
*/
	foreach ($ADAT['tanarAdat'] as $tanarId => $tAdat) {
	    // tanár neve, képesítései, összes óraszáma, kötelező óraszáma, besorolas
	    $elsoSor = count($T);
	    $sor = array($tAdat['tanarNev'], null, null, $tAdat['hetiKotelezoOraszam'], $tAdat['besorolas']);
	    if (is_array($ADAT['tanarKepesitesIds'])) {
		$_kepesites = array();
		foreach ($ADAT['tanarKepesitesIds'][$tanarId] as $kepesitesId) $_kepesites[] = $ADAT['kepesitesAdat'][$kepesitesId]['kepesitesNev'];
		$sor[1] = implode(', ', $_kepesites);
	    }
	    $T[] = $sor;
	    // tanár tárgyai és óraszámai
	    $sum = 0;
	    $utolsoTargyId = end((array_keys($ADAT['export'][$tanarId])));
	    foreach ($ADAT['export'][$tanarId] as $targyId => $targyAdat) {
		
		    $sor = array('', $ADAT['targyAdat'][$targyId]['targyNev'],0);
		    $resz = 0;
		    foreach ($ADAT['osztalyok'] as $oAdat) { 
/*
			if (($targyAdat[$oAdat['osztalyId']][1]+$targyAdat[$oAdat['osztalyId']][2])/2 != 0)
			    $sor[] = ($targyAdat[$oAdat['osztalyId']][1]+$targyAdat[$oAdat['osztalyId']][2])/2;
			else $sor[] = null;
*/
			$resz += ($targyAdat[$oAdat['osztalyId']][1]+$targyAdat[$oAdat['osztalyId']][2])/2;
		    }
		    $sor[2] = $resz;
		    $sum += $resz;
		    $T[] = $sor;
		
	    }
	    $T[$elsoSor][2] = $sum;
	}
//dump($T);
        if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $T, 'tantárgyFelosztás');
        elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $T, 'tantárgyFelosztás');
        elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $T, 'tantárgyFelosztás');
        else return false;

    }

?>