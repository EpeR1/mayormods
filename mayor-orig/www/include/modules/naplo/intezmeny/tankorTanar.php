<?php

    function tankorTanarTorol($tankorId,$tanarId,$beDt,$kiDt) { // CORE function

	$q = "SELECT COUNT(*) AS db FROM szemeszter WHERE statusz='lezárt' AND 
		(('%s' BETWEEN  szemeszter.kezdesDt AND szemeszter.zarasDt)
		OR
		('%s' BETWEEN  szemeszter.kezdesDt AND szemeszter.zarasDt))
	     LIMIT 1";
	$v = array($beDt, $kiDt);
	$r = db_query($q, array('fv' => 'tankorTanarTorol', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));

	if ($r == 0) {
	    $q = "DELETE FROM ".__INTEZMENYDBNEV.".tankorTanar WHERE tankorId=%u AND bedt='%s' AND tanarId=%u";
	    $v = array($tankorId, $beDt, $tanarId);
	    db_query($q, array('fv' => 'tankorTanarTorol', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true));
	} else {
	    $_SESSION['alert'][] = 'info:lezart_tanev';
	}
    }

    function tankorTanarJavit($tankorId,$tanarId,$beDt,$kiDt) {

	$q = "SELECT COUNT(*) AS db FROM szemeszter WHERE statusz='lezárt' AND 
		(('%s' BETWEEN  szemeszter.kezdesDt AND szemeszter.zarasDt)
		OR
		('%s' BETWEEN  szemeszter.kezdesDt AND szemeszter.zarasDt))
	     LIMIT 1";
	$v = array($beDt, $kiDt);
	$r = db_query($q, array('fv' => 'tankorTanarTorol', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));

	if ($r == 0) {
	    $q = "UPDATE ".__INTEZMENYDBNEV.".tankorTanar SET kiDt='%s' WHERE tankorId=%u AND bedt='%s' AND tanarId=%u";
	    $v = array($kiDt,$tankorId, $beDt, $tanarId);
	    db_query($q, array('fv' => 'tankorTanarTorol', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true));
	} else {
	    $_SESSION['alert'][] = 'info:lezart_tanev';
	}
    }

    //---


    function tankorTanarFelvesz($tankorIds, $tankorTanarIds, $tanevAdat, $tolDt, $igDt) {


	if (!is_array($tankorIds) || count($tankorIds) == 0) return false;
	$D = array();

	$lr = db_connect('naplo_intezmeny', array('fv' => 'tankorTanarFelvesz'));
	db_start_trans($lr);

	// Az intervallumban érintett tankör-tanár tagságok lekérdezése...
	$v = mayor_array_join($tankorIds, array($igDt, $tolDt));
	$q = "SELECT tanarId, tankorId, min(bedt) AS mbe ,max(kidt) AS mki 
		FROM ".__INTEZMENYDBNEV.".tankorTanar WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
	        AND bedt<='%s' AND kidt>='%s' GROUP BY tankorid,tanarid";
	$ret = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v, 'rollback' => true), $lr);
	if ($ret === false) { db_close($lr); return false; }
	for ($i = 0; $i < count($ret); $i++) {
	    if ($tolDt < $ret[$i]['mbe']) $ret[$i]['mbe'] = $tolDt;
	    if ($igDt > $ret[$i]['mki']) $ret[$i]['mki'] = $igDt;
	    $D[ $ret[$i]['tankorId'] ][ $ret[$i]['tanarId'] ] = array('mbe' => $ret[$i]['mbe'], 'mki' => $ret[$i]['mki'], 'torlendo' => true);
	}

	// Az érintett intervallumba eső tankör-tanár tagságok törlése
	$q = "DELETE FROM ".__INTEZMENYDBNEV.".tankorTanar WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") 
		AND bedt<='%s' AND kidt>='%s'";
	$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	if ($r === false) { db_close($lr); return false; }

	// beszúrandó
	for ($i = 0; $i < count($tankorIds); $i++) {
	    $tankorId = $tankorIds[$i];
	    $tanarIds = $tankorTanarIds[$tankorId];
	    for ($j = 0; $j < count($tanarIds); $j++) {
		$tanarId = $tanarIds[$j];
		if ($tanarId != '') {
		    $D[$tankorId][$tanarId]['torlendo'] = false;
		    if (($beDt = $D[$tankorId][$tanarId]['mbe']) == '') $beDt = $tolDt;
		    if (($kiDt = $D[$tankorId][$tanarId]['mki']) == '') $kiDt = $igDt;
		    $q = "INSERT INTO ".__INTEZMENYDBNEV.".tankorTanar (tankorId,tanarId,beDt,kiDt) VALUES (%u, %u, '%s', '%s')";
		    $v = array($tankorId, $tanarId, $beDt, $kiDt);
		    $r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
		    if ($r === false) { db_close($lr); return false; }
		}
	    }
	}

	// törlendők felvétele
	for ($i = 0; $i < count($tankorIds); $i++) {
	    $tankorId = $tankorIds[$i];
	    if (is_array($D[$tankorId]))
	    foreach($D[$tankorId] as $tanarId => $T) {
		if ($T['torlendo']) {
		    if ($T['mbe'] < $tolDt) {
			$beDt = $T['mbe'];
			$kiDt = date('Y-m-d', strtotime('-1 days',strtotime($tolDt)));
			$q = "INSERT INTO ".__INTEZMENYDBNEV.".tankorTanar (tankorId,tanarId,beDt,kiDt) VALUES (%u, %u, '%s', '%s')";
			$v = array($tankorId, $tanarId, $beDt, $kiDt);
			$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
			if ($r === false) { db_close($lr); return false; }
		    }
		    if ($T['mki'] > $igDt) {
			$kiDt = $T['mki'];
			$beDt = date('Y-m-d', strtotime('+1 days',strtotime($igDt)));
			$q = "INSERT INTO ".__INTEZMENYDBNEV.".tankorTanar (tankorId,tanarId,beDt,kiDt) VALUES (%u, %u, '%s','%s')";
			$v = array($tankorId, $tanarId, $beDt, $kiDt);
			$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
			if ($r === false) { db_close($lr); return false; }
		    }
		}
	    }
	}

	// tankörblokkok ellenőrzése - csak nem tervezett tanévben
	// Érintett blokkok lekérdezése
	if ($tanevAdat['statusz'] != 'tervezett') {
	    $blokkIds = getTankorBlokkByTankorId($tankorIds, $tanevAdat['tanev']);
	    if (is_array($blokkIds)) foreach ($blokkIds as $index => $blokkId) {
		// A blokk tankörei
		$bTankorIds = getTankorokByBlokkId($blokkId, $tanevAdat['tanev']);

		// Ellenőrizzük a tankör tanárokat - azonosak-e tankörönként
    		$q = "SELECT tanarId,COUNT(DISTINCT tankorId) AS c FROM ".__INTEZMENYDBNEV.".tankorTanar
            	    WHERE tankorId IN (".implode(',', array_fill(0, count($bTankorIds), '%u')).")
            	    AND beDt <= '%s' AND (kiDt IS NULL OR '%s' <= kiDt)
            	    GROUP BY tanarId HAVING c>1
            	    ORDER BY tankorId,tanarId";
		$v = mayor_array_join($bTankorIds, array($igDt, $tolDt));
		$r = db_query($q, array('fv' => 'tankorTanarFelvesz/BlokkEllenőrzés', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $lr);
    		if (is_array($r) && count($r) > 0) {
		    db_rollback($lr, 'Ütköző tanárt találtam egy blokkban ('.$blokkId.')! Visszaállítjuk az eredeti állapotot!');
        	    db_close($lr);
        	    return false;
    		}
	    }
	}
	/* ========================================================
	    Órarend módosítása
	======================================================== */


	if ($tanevAdat['statusz'] == 'aktív') {

	    $tanevDbNev = tanevDbNev(__INTEZMENY, $tanevAdat['tanev']);

	    // ÓrarendiOraTankor bejegyzés ellenőrzés/készítés
	    $V = $v2 = array();
	    foreach ($tankorIds as $i => $tankorId) {
		if ($tankorTanarIds[$tankorId][0] != '') { // Ha akarunk egyáltalán tanárt hozzárendelni
		    // van-e már az igényeinknek megfelelő bejegyzés
		    $q = "SELECT * FROM `%s`.orarendiOraTankor WHERE tankorId=%u 
			    AND tanarId IN (".implode(',', array_fill(0, count($tankorTanarIds[$tankorId]), '%u')).") LIMIT 1";
		    $v = mayor_array_join(array($tanevDbNev, $tankorId), $tankorTanarIds[$tankorId]);
		    $ret = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v, 'rollback' => true), $lr);
		    if ($ret === false) { db_close($lr); return false; }

		    if (count($ret) != 0) { // ha van, akkor az elsőt használjuk
			$OOT[$tankorId] = $ret[0];
		    } else { // ha nincs, akkor generálunk egy jót
			$OOT[$tankorId] = array(
			    'tanarId' => $tankorTanarIds[$tankorId][0], 
			    'osztalyJel' => 'NaN', 
			    'targyJel' => $tankorId.'-'.$tankorTanarIds[$tankorId][0],
			    'tankorId' => $tankorId
			);
			$V[] = "(%u, 'NaN', '%s', %u)";
			array_push($v2, $tankorTanarIds[$tankorId][0], $tankorId.'-'.$tankorTanarIds[$tankorId][0], $tankorId);
		    }
		}
	    }
	    if (count($V) > 0) { // Az új bejegyzéseket felvesszük
		$q = "INSERT INTO `%s`.orarendiOraTankor (tanarId,osztalyJel,targyJel,tankorId) VALUES ".implode(',', $V);
		array_unshift($v2, $tanevDbNev);
		$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'values' => $v2, 'rollback' => true), $lr);
		if ($r === false) { db_close($lr); return false; }

	    }

	    // Az érintett órarendi bejegyzések lekérdezése beDt szerint rendezve
	    $q = "SELECT tolDt,igDt,het,nap,ora,tankorId,tanarId,osztalyJel,targyJel,teremId
		    FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
		    WHERE tolDt <= '%s' AND (igDt >= '%s' OR igDt IS NULL)
		    AND tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    ORDER BY tankorId,tolDt";
	    $v = mayor_array_join(array($tanevDbNev, $tanevDbNev, $igDt, $tolDt), $tankorIds);
	    $ret = db_query($q, array(
		'fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $v, 'rollback' => true
	    ), $lr);
	    if ($ret === false) { db_close($lr); return false; }

	    foreach ($ret as $tankorId => $tankorOrarendiBejegyzesek) {

		// Ha van orarendiOra bejegyzés és nem akarunk tanárt hozzárendelni - az hiba!!
		if (!is_array($OOT[$tankorId])) {
		    db_rollback($lr, 'Létező órarendi óra esetén a tanár nem törölhető:'.$tankorId); db_close($lr); return false; 
		};

		$tanarId = $OOT[$tankorId]['tanarId'];
		$osztalyJel = $OOT[$tankorId]['osztalyJel'];
		$targyJel = $OOT[$tankorId]['targyJel'];

		foreach ($tankorOrarendiBejegyzesek as $i => $TOB) {
		    if ($TOB['teremId'] == '') {
			$TOB['teremId'] = 'NULL';
			$valueStr = "(%u, %u, %u, %u, '%s', '%s', %s, '%s', '%s')";
		    } else {
			$valueStr = "(%u, %u, %u, %u, '%s', '%s', %u, '%s', '%s')";
		    }
		    if ($TOB['tolDt'] < $tolDt) { // balról túlnyúlik - kettévágjuk
			$q = "UPDATE `%s`.orarendiOra SET igDt='%s' - INTERVAL 1 DAY 
				WHERE tolDt='%s' AND tanarId=%u 
				AND het=%u AND nap=%u AND ora=%u"; 
			$v = array($tanevDbNev, $tolDt, $TOB['tolDt'], $TOB['tanarId'], $TOB['het'], $TOB['nap'], $TOB['ora']);
			$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
			if ($r === false) { db_close($lr); return false; }
			$q = "INSERT INTO `%s`.orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt) VALUES $valueStr";
			$v = array(
			    $tanevDbNev, $TOB['het'], $TOB['nap'], $TOB['ora'], $TOB['tanarId'], $TOB['osztalyJel'],
			    $TOB['targyJel'], $TOB['teremId'], $tolDt, $TOB['igDt']
			);
			$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
			if ($r === false) { db_close($lr); return false; }
			$TOB['tolDt'] = $tolDt; // Az intervallumot lefedő bejegyzések miatt - három fele fogjuk vágni
		    }
		    if ($igDt < $TOB['igDt']) { // jobbról túlnyúlik - kettévágjuk !! igDt nem lehet NULL !!
			$q = "UPDATE `%s`.orarendiOra SET tolDt='%s' + INTERVAL 1 DAY 
				WHERE igDt='%s' AND tanarId=%u AND het=%u AND nap=%u AND ora=%u"; 
			$v = array($tanevDbNev, $igDt, $TOB['igDt'], $TOB['tanarId'], $TOB['het'], $TOB['nap'], $TOB['ora']);
			$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
			if ($r === false) { db_close($lr); return false; }
			$q = "INSERT INTO `%s`.orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt) VALUES $valueStr";
			$v = array(
			    $tanevDbNev, $TOB['het'], $TOB['nap'], $TOB['ora'], $TOB['tanarId'], $TOB['osztalyJel'], 
			    $TOB['targyJel'], $TOB['teremId'], $TOB['tolDt'], $igDt
			);
			$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
			if ($r === false) { db_close($lr); return false; }

		    }
		    // A közbensőkben tanárt váltunk
            	    if ($tanarId != $TOB['tanarId']) {
                	$q = "UPDATE `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
                                SET tanarId=%u, osztalyJel='%s', targyJel='%s'
                                WHERE '%s'<=tolDt AND igDt<='%s' AND tanarId=%u AND het=%u AND nap=%u AND ora=%u AND tankorId=%u";
			$v = array($tanevDbNev, $tanevDbNev, $tanarId, $osztalyJel, $targyJel, $tolDt, $igDt, $TOB['tanarId'], $TOB['het'], $TOB['nap'], $TOB['ora'], $tankorId);
                	$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
                	if ($r === false) { db_close($lr); return false; }
            	    }

		}

	    } // foreach

	    // A módosított órarend ütközésellenőrzése  -  [k] munkatervenként külön. Itt most megengedjük, hogy ha több munkaterv is van, de egyszerre van órája a tanárnak... :/
//	    $q = "SELECT tanarId, dt, ora, COUNT(*) AS db
//            		FROM `%s`.nap LEFT JOIN `%s`.orarendiOra
//                	    ON (((DAYOFWEEK(dt)+5) MOD 7)+1 = orarendiOra.nap)
//                	    AND orarendiOra.het=nap.orarendiHet
//                	    AND orarendiOra.tolDt<=dt AND orarendiOra.igDt>=dt
//			WHERE '%s' <= dt AND dt <= '%s'
//			GROUP BY munkatervId,tanarId, dt, ora
//			HAVING db > 1";
	    // [bb] szerintem ez a jó: a hét-nap-óra-tanár-tolDt kulcs az orarendiOra táblában, így ha két sorban ezek megegyeznek, akkor nem kell külön számolni...
	    $q = "SELECT tanarId, dt, ora, COUNT(DISTINCT het, nap, ora, tanarId, tolDt) AS db
            		FROM `%s`.nap LEFT JOIN `%s`.orarendiOra
                	    ON (((DAYOFWEEK(dt)+5) MOD 7)+1 = orarendiOra.nap)
                	    AND orarendiOra.het=nap.orarendiHet
                	    AND orarendiOra.tolDt<=dt AND orarendiOra.igDt>=dt
			WHERE '%s' <= dt AND dt <= '%s'
			GROUP BY tanarId, dt, ora
			HAVING db > 1";
	    $v = array($tanevDbNev, $tanevDbNev, $tolDt, $igDt);
	    $ret = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v, 'rollback' => true), $lr);
	    if ($ret === false) { db_close($lr); return false; }

	    if (count($ret)) { // Van ütközés!
		db_rollback($lr, 'Az összes ütközést ellenőriztem, és a megadott '.$tolDt.'-'.$igDt.' intervallumban egy (esetleg másik) tanárnak több órája van egy időben (tanarId='.$ret[0]['tanarId'].', dt='.$ret[0]['dt'].', ora='.$ret[0]['ora'].') - így visszaállítjuk az eredeti állapotot...');
		db_close($lr);
		return false;
	    }

	} // aktív tanév

	db_commit($lr);
	db_close($lr);
	return true;

    }

?>
