<?php

// osztalyId, tanarId, beDt --> kiDt
function osztalyfonokKileptetes($osztalyId, $tanarId, $beDt, $kiDt, $olr = null) {
	global $mayorCache;
	$mayorCache->delType('osztaly');
	
	if (strtotime($beDt) > strtotime($kiDt)) {
		$_SESSION['alert'][] = 'message:wrong_data:'.$beDt.' - '.$kiDt;
		return false; 
	}
	
	$q = "UPDATE ".__INTEZMENYDBNEV.".osztalyTanar SET kiDt='%s' WHERE osztalyId=%u AND tanarId=%u AND beDt='%s'";
	$v = array($kiDt, $osztalyId, $tanarId, $beDt);
	return db_query($q, array('fv' => 'osztalyfonokKileptetes', 'modul' => 'naplo_intezmeny', 'values' => $v), $olr);

}



// MÁR NINCS HASZNÁLATBAN!!!!!!!!!!!!
function osztalyNevsorModositas($osztalyId, $diakIds, $beDt, $kiDt, $olr='') {

//    1. A kiDt utáni tankörtagságokat törli, ha a tankör nincs a diák másik kiDt-kori, vagy utána lévő osztályához is rendelve (tankoOsztaly)
//    2. A beDt előtti tankörtagságokat érintetlenül hagyjuk (a tankorOsztaly változhat...)
//    3. Az osztalyDiak-ban (osztalyId,diakId) kulcs - csak egy bejegyzés van, ezt bővítjük, szűkítjük...
//    4. Sikertelen tankör kiléptetés esetén visszagörgetünk (pl. ha van jegye, hiányzása egy tankörben)


	$_SESSION['alert'][] = 'info:!!!:osztalyNevsorModositas()';
	
	if (is_array($diakIds) && count($diakIds) > 0) {

	    if ($olr!='')  { 
		$lr=$olr; 
	    } else {
		$lr = db_connect('naplo_intezmeny');
		db_start_trans($lr);
	    }

	    $diakTankorKileptetesOK = array(); // a diakTorol függvény talált-e ütközést vagy hibát, akkor bebillenti ezt a flag-et, false-ra!

	    if ($kiDt != '') {
	      /* Konzisztencia megőrzése: tankorből kiléptetés, ahol: tankorOsztaly --> tankorId-k be benne van */
	      for($i = 0; $i < count($diakIds); $i++) {

		$diakId = $diakIds[$i];

		// A diáknak mely tankörei érintettek (amikben a kiDt-kor, vagy utána benne van, de más (kiDt-kori, vagy későbbi) osztályához nem tartozik:
		$q = "SELECT tankorId FROM tankorDiak WHERE diakId=%u AND (kiDt IS NULL or '%s'<=kiDt) AND tankorId NOT IN 
			(SELECT DISTINCT tankorId FROM osztalyDiak LEFT JOIN tankorOsztaly USING(osztalyId) 
			    WHERE diakId=%u AND (kiDt is NULL or '%s'<=kiDt) AND osztalyId <> %u)";
		$v = array($diakId, $kiDt, $diakId, $kiDt, $osztalyId);
		$tankorIds = db_query($q, array('fv' => 'osztalyNevsorModositas', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $lr);

    		// Tankörökből kivétel - $kiDt utáni naptól
		$diakTankorKileptetesOK[$diakId] = true;
		for ($j = 0; $j < count($tankorIds); $j++) {
            	    $_D = array('tankorId' => $tankorIds[$j], 'diakId' => $diakId, 'utkozes'=>'torles', 'tolDt' => date('Y-m-d', strtotime('+1 days', strtotime($kiDt))));
		    $ret = tankorDiakTorol($_D, $lr);
		    if ($ret === false) {
			$_SESSION['alert'][] = 'message:wrong_data:osztalyNevsorModositas - tankorDiakTorol FAILED - diakId='.$diakId.' - tankorId='.$tankorIds[$j];
            		$diakTankorKileptetesOK[$diakId] = false;
		    }
		}
		// Visszajelzés
		if (count($tankorIds) > 0 && $diakTankorKileptetesOK[$diakId] === true)
		    $_SESSION['alert'][] = 'info:diak_tankorokben_lezarva:'.$diakId.':'.implode(',',$tankorIds).':'.$kiDt;
		// Megjegyzés: a beDt előtti tankörtagságokat nem állítjuk be... mert a tankorOsztaly összerendelés változhat...
	      }
	
	    }

	    if (!in_array(false, array_values($diakTankorKileptetesOK))) {
	    
		// (osztalyId, diakId) kulcs, csak egy bejegyzés van...
		if ($beDt == '') { 
		    $q = "UPDATE osztalyDiak SET kiDt='%s' WHERE osztalyId=%u AND diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")";
		    array_unshift($diakIds, $kiDt, $osztalyId);
		} elseif ($kiDt != '') { 
		    $q = "UPDATE osztalyDiak SET beDt='%s', kiDt='%s' WHERE osztalyId=%u AND diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")";
		    array_unshift($diakIds, $beDt, $kiDt, $osztalyId);
		} else { 
		    $q = "UPDATE osztalyDiak SET beDt='%s', kiDt=NULL WHERE osztalyId=%u AND diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")";
		    array_unshift($diakIds, $beDt, $osztalyId);
		}
		$ret = db_query($q, array('fv' => 'osztalyNevsorModositas', 'modul' => 'naplo_intezmeny', 'values' => $diakIds, 'rollback' => true), $lr);
		db_commit($lr);

	    } else {
		db_rollback($lr);
		$ret = false;
	    }
    	    if ($olr!='') {

	    } else {
		db_close($lr);
	    }

	    return $ret;

	} else { return false; } // nincs diakId

}


function osztalyLezaras($osztalyIds, $dt) {

	global $mayorCache;
	$mayorCache->delType('osztaly');

	$dt = readVariable($dt, 'datetime', null);
	if (!isset($dt)) return false;

	if (!is_array($osztalyIds) && $osztalyIds != '') $osztalyIds = array($osztalyIds);
	if (count($osztalyIds) == 0) return false;

	// lekérdezzük az érintett osztályok végző tanévének legnagyobb zárás dátumát.
	$q = "SELECT MAX(zarasDt) FROM osztaly LEFT JOIN szemeszter ON vegzoTanev = tanev 
		WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).")";
	$zarasDt = db_query($q, array('fv' => 'osztalyLezaras/ellenőrzés', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $osztalyIds));

	// Csak zarasDt utáni dátummal zárunk le osztályt!
	if (strtotime($dt) < strtotime($zarasDt)) return false;

	// Tagok kiléptetése
	$q = "UPDATE osztalyDiak SET kiDt='%s' WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
		AND (kiDt IS NULL OR '%s' < kiDt)";
	$v = mayor_array_join(array($dt), $osztalyIds, array($dt));
	db_query($q, array('fv' => 'osztalyLezaras/tagok kiléptetése', 'modul' => 'naplo_intezmeny', 'values' => $v));

	// Osztályfőnöki megbizatás lezárása
	// - Az érintett tanárok lekérdezése
	$q = "SELECT DISTINCT tanarId FROM osztalyTanar WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
		AND (kiDt IS NULL OR '%s' < kiDt)";	
	$v = mayor_array_join($osztalyIds, array($dt));
	$tanarIds = db_query($q, array('fv' => 'osztalyLezaras/tanárok kiléptetése', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));
	if (is_array($tanarIds) && count($tanarIds) > 0) {
	    // - Az osztályfőnöki megbizatás lezárása
	    $q = "UPDATE osztalyTanar SET kiDt='%s' WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
		    AND (kiDt IS NULL OR '%s' < kiDt)";
	    $v = mayor_array_join(array($dt), $osztalyIds, array($dt));	
	    db_query($q, array('fv' => 'osztalyLezaras/tanárok kiléptetése', 'modul' => 'naplo_intezmeny', 'values' => $v));

	    // Az osztályfőnöki munkaközösség frissítése
	    $q = "SELECT mkId FROM munkakozosseg WHERE leiras='osztályfőnöki'";
	    $mkId = db_query($q, array('fv' => 'osztalyLezaras/tanárok kiléptetése', 'modul' => 'naplo_intezmeny', 'result' => 'value'));
	    if ($mkId) { // Van egyáltalán osztályfőnöki munkaközösség
		$q = "SELECT mkTanar.tanarId 
			FROM mkTanar LEFT JOIN osztalyTanar ON mkTanar.tanarId=osztalyTanar.tanarId 
			AND osztalyId NOT IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
			WHERE mkId=%u AND mkTanar.tanarId IN (".implode(',', array_fill(0, count($tanarIds), '%u')).") 
			GROUP BY mkTanar.tanarId HAVING COUNT(osztalyId)=0";
		$v = mayor_array_join($osztalyIds, array($mkId), $tanarIds);
		$ofoTanarIds = db_query($q, array(
		    'fv' => 'osztalyLezaras/tanárok kiléptetése', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v
		));
		if (is_array($ofoTanarIds) && count($ofoTanarIds) > 0) {
		    $q = "DELETE FROM mkTanar WHERE mkId=%u AND tanarId IN (".implode(',', array_fill(0, count($ofoTanarIds), '%u')).")";
		    $v = mayor_array_join(array($mkId), $ofoTanarIds);
		    db_query($q, array(
			'fv' => 'osztalyLezaras/tanárok kiléptetése az osztályfőnöki munkaközösségből', 'modul' => 'naplo_intezmeny', 'values' => $v
		    ));
		}
	    }
	}
	return true;

}

function osztalyDiakTorol($SET = array('osztalyId' => null, 'diakId' => null, 'tolDt' => null, 'igDt' => null, 'zaradekkal' => true), $olr='') {
/*
    1. Ha az intervallum belelóg valamelyik lezárt tanévbe, akkor afüggvény nem végzi el az osztályból va kiléptetést.
    2. A $tolDt-$igDt közötti tankörtagságokat törli, ha a tankör nincs a diák másik tolDt-kori, vagy utána lévő osztályához is rendelve (tankoOsztaly)
    3. Ha a tankörökből nem sikerül törölni, akkor visszagörgetés történik
    4. A $tolDt-$igDt intervallumban törli a diák osztálytagságát ($igDt hiányában végig)
    5. Záradékolás még nincs.
*/

	$diakId = readVariable($SET['diakId'], 'id');
	$osztalyId = readVariable($SET['osztalyId'], 'id');
	$tolDt = readVariable($SET['tolDt'], 'date');
	$igDt = readVariable($SET['igDt'], 'date');
	$zaradekkal = readVariable($SET['zaradekkal'], 'bool', false);

        // Csatlakozás az adatbázishoz
	if ($olr=='') {
    	    $lr = db_connect('naplo_intezmeny', array('fv' => 'ujTag'));
    	    if (!$lr) return false;
    	    db_start_trans($lr);
	} else {
    	    $lr = $olr;
	}
	
	// Ellenőrizzük, hogy $tolDt-$igDt nem érint-e lezárt szorgalmi időszakot
	if (isset($igDt)) {
	    $q = "SELECT DISTINCT tanev FROM szemeszter WHERE statusz IN ('lezárt','archivált') AND '%s'<=zarasDt AND kezdesDt<='%s'";
	    $v = array($tolDt, $igDt);
	} else {
	    $q = "SELECT DISTINCT tanev FROM szemeszter WHERE statusz IN ('lezárt','archivált') AND '%s'<=zarasDt";
	    $v = array($tolDt);
	}
	$ret = db_query($q, array('fv' => 'osztalyDiakTorol', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $lr);
	if (is_array($ret) && count($ret) > 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:Lezárt tanév - '.implode(', ', $ret);
	    if ($olr=='') {
		db_rollback($lr);
		db_close($lr);
	    }
	    return false;
	}

	// ----- Tankörök ------
	// A diáknak mely tankörei érintettek (amikben a kiDt-kor, vagy utána benne van, de más (kiDt-kori, vagy későbbi) osztályához nem tartozik:
	$q = "SELECT tankorId FROM tankorDiak WHERE diakId=%u AND (kiDt IS NULL or '%s'<=kiDt) AND tankorId NOT IN 
			(SELECT DISTINCT tankorId FROM osztalyDiak LEFT JOIN tankorOsztaly USING(osztalyId) 
			    WHERE diakId=%u AND (kiDt is NULL or '%s'<=kiDt) AND osztalyId <> %u)";
	$v = array($diakId, $tolDt, $diakId, $tolDt, $osztalyId);
	$tankorIds = db_query($q, array('fv' => 'osztalyDiakTorol', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $lr);

    	// Tankörökből kivétel - $tolDt-től
	$diakTankorKileptetesOK = true;
	for ($j = 0; $j < count($tankorIds); $j++) {
        	$_D = array('tankorId' => $tankorIds[$j], 'diakId' => $diakId, 'utkozes'=>'torles', 'tolDt' => $tolDt, 'igDt' => $igDt);
		$ret = tankorDiakTorol($_D, $lr);
		if ($ret === false) {
		    $_SESSION['alert'][] = 'message:wrong_data:osztalyDiakTorol - tankorDiakTorol FAILED - diakId='.$diakId.' - tankorId='.$tankorIds[$j];
            	    $diakTankorKileptetesOK = false;
		    $R[] = false;
		}
	}
	// Visszajelzés
	if (count($tankorIds) > 0 && $diakTankorKileptetesOK[$diakId] === true) {
		$_SESSION['alert'][] = 'info:diak_tankorokben_lezarva:'.$diakId.':'.implode(',',$tankorIds).':'.$tolDt.' - '.$igDt;
		// Megjegyzés: a beDt előtti tankörtagságokat nem állítjuk be... mert a tankorOsztaly összerendelés változhat...
	}

	// ------- Tankörök vége------------

        // Van-e már $tolDt-t tartalmazó osztálytagsága
        $q = "SELECT beDt,kiDt FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND beDt<'%s' AND (kiDt IS NULL OR kiDt >= '%s')";
        $ret = db_query($q, array('fv' => 'osztalyDiakTorol/bal', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($osztalyId, $diakId, $tolDt, $tolDt)), $lr);
        if (!is_null($ret)) {
	    // Ha van, akkor azt levégjuk $tolDt előtt 1 nappal
	    $q = "UPDATE osztalyDiak SET kiDt='%s' - INTERVAL 1 DAY WHERE osztalyId=%u AND diakId=%u AND beDt='%s'";
    	    $R[] = db_query($q, array('fv' => 'osztalyDiakTorol/bal levágás', 'modul' => 'naplo_intezmeny', 'values' => array($tolDt, $osztalyId, $diakId, $ret['beDt'])), $lr);
	    // Ha átnyuló tagság, akkor felvesszük a jobboldali szakaszt igDt után 1 nappal
	    if (!is_null($igDt)) {
	      if (is_null($ret['kiDt'])) { // kiDt NULL 
		$q = "INSERT INTO osztalyDiak (osztalyId, diakId, beDt, kiDt) VALUES (%u, %u, '%s' + INTERVAL 1 DAY, NULL)";
		$R[] = db_query($q, array('fv' => 'osztalyDiakTorol/jobb beszúrás', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId, $diakId, $igDt)), $lr);
	      } elseif (strtotime($igDt) < strtotime($ret['kiDt'])) { // kiDt rögzített és túllóg igDt-n
		$q = "INSERT INTO osztalyDiak (osztalyId, diakId, beDt, kiDt) VALUES (%u, %u, '%s' + INTERVAL 1 DAY, '%s')";
		$R[] = db_query($q, array('fv' => 'osztalyDiakTorol/jobb beszúrás', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId, $diakId, $igDt, $ret['kiDt'])), $lr);
	      }
	    }
	}
	// Van-e $igDt-t tartalmazó osztály tagsága
        if ($igDt != '') {
    	    $q = "SELECT beDt FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND beDt<'%s' AND (kiDt IS NULL OR kiDt >= '%s')";
    	    $ret = db_query($q, array('fv' => 'osztalyDiakTorol/jobb', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($osztalyId, $diakId, $igDt, $igDt)), $lr);
    	    if (!is_null($ret)) {
		// Ha van, akkor azt levégjuk $igDt után 1 nappal
		$q = "UPDATE osztalyDiak SET beDt='%s' + INTERVAL 1 DAY WHERE osztalyId=%u AND diakId=%u AND beDt='%s'";
    		$R[] = db_query($q, array('fv' => 'osztalyDiakTorol/jobb levágás', 'modul' => 'naplo_intezmeny', 'values' => array($igDt, $osztalyId, $diakId, $ret['beDt'])), $lr);
	    }
    	    // A köztes tagságokat töröljük
    	    $q = "DELETE FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND '%s'<=beDt AND kiDt <= '%s'";
    	    $R[] = db_query($q, array('fv' => 'osztalyDiakTorol', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($osztalyId, $diakId, $tolDt, $igDt)), $lr);
	} else {
    	    // A $tolDt utáni tagságokat töröljük
    	    $q = "DELETE FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND '%s'<=beDt";
    	    $R[] = db_query($q, array('fv' => 'osztalyDiakTorol', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($osztalyId, $diakId, $tolDt)), $lr);
	}

        if ($olr=='') {
	    if (in_array(false,$R)) {
		db_rollback($lr);
	    } else {
		db_commit($lr);
	    }
    	    db_close($lr);
	}
	if (in_array(false,$R)) return false; else return true;
}

function osztalyTorzslapszamGeneralas($osztalyId) {

    $q = "SELECT kezdoTanev, vegzoTanev FROM osztaly WHERE osztalyId=%u";
    $oAdat = db_query($q, array('fv'=>'osztalyTorzslapszamGeneralas','modul'=>'naplo_intezmeny','result'=>'record','values'=>array($osztalyId)));
    $range = range(intval($oAdat['kezdoTanev']), intval($oAdat['vegzoTanev']));

    // A replace nem törli ki a hibás/felesleges bejegyzéseket...
    $q = "DELETE FROM diakTorzslapszam WHERE osztalyId=%u";
    db_query($q, array('fv'=>'osztalyTorzslapszamGeneralas/del','modul'=>'naplo_intezmeny','values'=>array($osztalyId)));

    // Egyszerű insert-tel valamiért nem megy... ??
    $q = "REPLACE INTO diakTorzslapszam
	    SELECT %1\$u as osztalyId, diakId, @rank := @rank+1 AS torzslapszam FROM (
		SELECT DISTINCT diakId";
		foreach ($range as $ev) {
		    $q .= ",ifnull(diakNaploSorszam(diakId, $ev, %1\$u),99) as ns".$ev;
		}
		$q .= " FROM osztalyDiak WHERE osztalyId=%1\$u ORDER BY ns".implode(', ns', $range)."
	    ) t1, (SELECT @rank := 0) t2
	    WHERE ns".implode('<>99 OR ns', $range)."<>99";

    $return = db_query($q, array('debug'=>false,'fv'=>'osztalyTorzslapszamGeneralas/replace','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array($osztalyId)));

    return $return;
}

?>