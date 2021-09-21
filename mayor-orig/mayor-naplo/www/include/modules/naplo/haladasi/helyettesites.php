<?php

    // Valójában nem feltétlen hiányzók, hanem azok akiknek módosult az órarendje...
    function getHianyzok($dt = '', $olr = null) {

	$dt = readVariable($dt, 'datetime', date('Y-m-d'));
	$hianyzok = array();

	    $q = "SELECT kit, ki, eredet FROM ora
			WHERE dt='%s' AND ((kit IS NOT NULL AND kit != '') OR (eredet='plusz' AND feladatTipusId IS NULL))
			ORDER BY kit, ora";
	    $result = db_query($q, array('fv' => 'getHianyzok', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($dt)), $olr);

	    foreach ($result as $key => $sor) {
		// Ha a kit nem üres, akkor az az eredeti tanár, különben a ki (plusz óránál lehet)
		if ($sor['kit'] != '') {
		    if (!in_array($sor['kit'], $hianyzok)) $hianyzok[] = $sor['kit'];
		} elseif ($sor['ki'] != '' && !in_array($sor['ki'], $hianyzok)) {
		    $hianyzok[] = $sor['ki'];
		}
	    }

	return $hianyzok;

    }


    function getHianyzoOrak($dt = '', $olr = '') {


	if($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;

	    // Kik érintettek az aktuális helyettesítésekben
	    $ORAK['helyettesites']['tanarIds'] = getHianyzok($dt, $lr);

	    // Az érintettek óráinak adatai, kivéve a kötött munkaidő plusz óráit, hiszen azok nem számítanak. Nem számítanak?
	    if (count($ORAK['helyettesites']['tanarIds']) > 0) {
		$q = "SELECT DISTINCT
			    oraId, dt, ora, ki, kit, tankorId, teremId, leiras, tipus, eredet, tankorNev, targyId,feladatTipusId,munkaido
			FROM ora
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
			LEFT JOIN ".__INTEZMENYDBNEV.".feladatTipus USING (feladatTipusId)
			WHERE (
			    ki IN (".implode(',', array_fill(0, count($ORAK['helyettesites']['tanarIds']),'%u')).") OR
			    kit IN (".implode(',', array_fill(0, count($ORAK['helyettesites']['tanarIds']), '%u')).")
			)
			AND dt='%s'
			AND (tanev=".__TANEV." OR feladatTipusId IS NOT NULL)
			ORDER BY ora";
		$v = mayor_array_join($ORAK['helyettesites']['tanarIds'], $ORAK['helyettesites']['tanarIds'], array($dt));
		$result = db_query($q, array('fv' => 'getHianyzoOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
		if (!$result) {
		    if($olr == '') $lr = db_close($lr);
		    return false;
		}
		foreach ($result as $key => $sor) {
		    if ($sor['kit'] != '') {
			$ORAK['helyettesites'][$sor['kit']]['orak'][] = $sor;
		    }
		    if (in_array($sor['ki'], $ORAK['helyettesites']['tanarIds'])) {
			$ORAK['helyettesites'][$sor['ki']]['orak'][] = $sor;
		    }
		}
	    }

	    // Tanárnevek lekérése
	    $TANAR_NEVSOR = getTanarok(array('tanev' => __TANEV,'beDt'=>$dt,'kiDt'=>$dt), $lr);
	    for ($i = 0; $i < count($TANAR_NEVSOR); $i++) {
		$ORAK['tanarok']['tanarIds'][] = $TANAR_NEVSOR[$i]['tanarId'];
		$ORAK['tanarok'][$TANAR_NEVSOR[$i]['tanarId']] = array('tanarNev' => $TANAR_NEVSOR[$i]['tanarNev']);
	    }
	    // Az adott napon ki melyik órákban tanít, első, utolsó, db
	    $q = "SELECT ki, ora FROM ora
		    WHERE dt = '%s' AND ki != ''
		    ORDER BY ki, ora";
	    $r = db_query($q, array('fv' => 'getHianyzoOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($dt)), $lr);
	    foreach ($r as $key => $val) {
		$ki = $val['ki']; $ora = $val['ora'];
		if (!is_array($ORAK['tanarok']['foglaltak'][$ora])	// gyűjtjük, hogy kik tanítanak az adott órában
		    || !in_array($ki, $ORAK['tanarok']['foglaltak'][$ora])
		) $ORAK['tanarok']['foglaltak'][$ora][] = $ki;
		$ORAK['tanarok'][$ki]['orak'][$ora] = true; 
		$ORAK['tanarok'][$ki]['db']++;
		if (
		    !isset($ORAK['tanarok'][$ki]['elso ora']) or 
		    $ORAK['tanarok'][$ki]['elso ora'] > $ora
		) $ORAK['tanarok'][$ki]['elso ora'] = $ora;
		if ($ORAK['tanarok'][$ki]['utolso ora'] < $ora) $ORAK['tanarok'][$ki]['utolso ora'] = $ora;
	    }

	    // Milyen tárgyat és mely osztályokban tanít az adott tanévben! (TANAR_NEVSOR tömbből válogassuk hozzá a tanáridket)
	    $q = "SELECT tanarId,targyId FROM ".__INTEZMENYDBNEV.".mkTanar
		    LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (mkId) 
		    WHERE mkTanar.tanarId IN (".implode(',', array_fill(0, count($ORAK['tanarok']['tanarIds']), '%u')).")";
	    $r = db_query($q, array('fv' => 'getHianyzoOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $ORAK['tanarok']['tanarIds']), $lr);
	    if (count($r)>0) foreach ($r as $key => $val) {
		$tanarId = $val['tanarId']; $targyId = $val['targyId'];
		$ORAK['tanarok'][$tanarId]['targyak'][$targyId] = true;
	    }
	    // Egészítsük ki a képesítése szerint is! (2011, 2015)
	    $q = "SELECT tanarId,targyId FROM ".__INTEZMENYDBNEV.".tanarKepesites LEFT JOIN ".__INTEZMENYDBNEV.".kepesitesTargy USING (kepesitesId) ".
		 "WHERE tanarId IN (".implode(',', array_fill(0, count($ORAK['tanarok']['tanarIds']), '%u')).")";
	    $r = db_query($q, array('fv' => 'getHianyzoOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $ORAK['tanarok']['tanarIds']), $lr);
	    if (count($r)>0) foreach ($r as $key => $val) {
		$tanarId = $val['tanarId']; 
		$targyId = $val['targyId'];
		$ORAK['tanarok'][$tanarId]['targyak'][$targyId] = true;
	    }

	    // Az összevonó/foglalt tanárok
	    for ($i = 0; $i < count($ORAK['helyettesites']['tanarIds']);$i++) {

		$tanarId = $ORAK['helyettesites']['tanarIds'][$i];
		$tanarOrak = $ORAK['helyettesites'][$tanarId]['orak'];

		for ($j = 0; $j < count($tanarOrak); $j++) {

		    $ora = $tanarOrak[$j]['ora'];
		    $Foglaltak = $ORAK['tanarok']['foglaltak'][$ora];
		    // Ha a tanár szakos, akkor összevonhat.
		    // Itt most egyelőre annyit kérdezünk le, hogy ugyanabban az időben ki tanít
		    for ($f = 0; $f < count($Foglaltak); $f++) {
			if ($Foglaltak[$f] != $tanarId) {
			    if ($ORAK['tanarok'][$Foglaltak[$f]]['targyak'][$tanarOrak[$j]['targyId']])
				$ORAK['helyettesites'][$tanarId]['orak'][$j]['osszevono'][] = $Foglaltak[$f];
			    else
				$ORAK['helyettesites'][$tanarId]['orak'][$j]['foglalt'][] = $Foglaltak[$f];
			}
		    } // Adott óra öszzevonói/foglaltjai
		} // Adott tanár órái
	    } // A helyettesítésben érintett tanárok
	    $q = "SELECT ki, ora FROM ora
		    WHERE dt = '%s' AND ki != '' AND feladatTipusId IS NOT NULL
		    ORDER BY ki, ora";
	    $r = db_query($q, array('fv' => 'getHianyzoOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($dt)), $lr);
	    for ($i=0; $i<count($r); $i++) {
		$ORAK['egyeb'][$r[$i]['ora']][]=$r[$i]['ki'];
	    }
	    $ORAK['termek'] = getTermek(array('result'=>'assoc'));
	    
	if($olr=='') $lr = db_close($lr);
	return $ORAK;
    }

    function ujHianyzokFelvetele($ujHianyzok, $dt, $olr = '') {
    

	if (count($ujHianyzok) > 0) {

	    if ($olr == '') $lr = db_connect('naplo');
	    else $lr = $olr;
	
	    $where = "ki IN (".implode(',', array_fill(0, count($ujHianyzok), '%u')).")";
	    $v = $ujHianyzok;

	    // Ha visszamenőleg állítunk elmaradtra egy órát, akkor kezelni kell a hozzá tartozó bejegyzéseket
	    if (strtotime($dt) < time()) {
		// Az elmaradó órák id-inek lekérdezése
		$q = "SELECT oraId FROM ora
		    WHERE dt='%s'
		    AND ki IN (".implode(',', array_fill(0, count($ujHianyzok), '%u')).")
		    AND (
			tipus='normál' OR
			tipus='helyettesítés' OR
			tipus='felügyelet' OR
			tipus='összevonás'
		    )";
		$v = mayor_array_join(array($dt), $ujHianyzok);
		$oraIds = db_query($q, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);

		if (is_array($oraIds) && count($oraIds) > 0) { // Ha van elmaradt óra
		    $where_id = "oraId IN (".implode(',', array_fill(0, count($oraIds), '%u')).")";
	
		    // Az elmaradt órákhoz tartozó hiányztások, késések, felszerelés hiányok, egyenruha hiányok törlése!
		    $q = "SELECT hianyzasId FROM hianyzas WHERE oraId IN (".implode(',', array_fill(0, count($oraIds), '%u')).")";
		    $hIds = db_query($q, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $oraIds), $lr);
		    if (count($hIds) > 0) {
			logAction(
			    array(
				'tabla' => 'hianyzas',
				'szoveg'=> "hiányzó tanár - óraelmaradás: $where_id, hianyzasId IN (".implode(',', array_fill(0, count($hIds), '%u')).")",
				'values' => mayor_array_join($oraIds, $hIds)
			    ), 
			    $lr
			);
			$q = "DELETE FROM hianyzas WHERE oraId IN (".implode(',', array_fill(0, count($oraIds), '%u')).")";
			$r = db_query($q, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'values' => $oraIds), $lr);
		    }
		    // Az elmaradt órákhoz rendelt jegyek hozzárendelésének törlése
		    $q = "UPDATE jegy SET oraId=NULL WHERE oraId IN (".implode(',', array_fill(0, count($oraIds), '%u')).")";
		    $r = db_query($q, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'values' => $oraIds), $lr);
		}
	    }

	    $v = mayor_array_join(array($dt), $ujHianyzok);
	    // Normál órái elmaradnak
	    $q = "UPDATE ora
		SET kit=ki, ki=NULL, tipus='elmarad', modositasDt=NOW()
		WHERE dt='%s'
		AND tipus='normál'
		AND ki IN (".implode(',', array_fill(0, count($ujHianyzok), '%u')).")";
	    $r = db_query($q, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'values' => $v), $lr);

	    // Helyettesített, felügyelt, összevont órái elmaradnak
	    $q = "UPDATE ora SET ki=NULL,tipus='elmarad', modositasDt=NOW()
                WHERE dt='%s'
		AND (
		    tipus='helyettesítés' OR
		    tipus='felügyelet' OR
		    tipus='összevonás'
		)
		AND ki IN (".implode(',', array_fill(0, count($ujHianyzok), '%u')).")";
	    $r = db_query($q, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'values' => $v), $lr);

	    // Elmaradnak-e a rögzített feladatai? ??????????????

	    if ($olr == '') db_close($lr);
	}
    }

    function toroltHianyzokVisszaallitasa($toroltHianyzok, $dt, $olr='') {

    
	if (count($toroltHianyzok) > 0) {

	    if ($olr == '') $lr = db_connect('naplo');
	    else $lr = $olr;

	    // Cserék visszaállítása
	    $q_cs = "SELECT oraId FROM ora WHERE tipus like '%%máskor'
			AND (
			    ki IN (".implode(',', array_fill(0, count($toroltHianyzok), '%u')).")
			    OR kit IN (".implode(',', array_fill(0, count($toroltHianyzok), '%u')).")
			) AND dt = '%s'";
	    $v_cs = mayor_array_join($toroltHianyzok, $toroltHianyzok, array($dt));
	    $r_cs = db_query($q_cs, array('fv' => 'ujHianyzokFelvetele', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v_cs), $lr);
	    foreach ($r_cs as $key => $vissza_id) {
		csereVisszaallitas($vissza_id, $lr);
	    }

	    // A nem hiáynzók helyettesített, összevont, elmaradt, felügyelt óráinak visszaállítása
	    // 2013. Itt vissza kell állítanunk munkaido-t 'lekötött'-re. Mi történik ugyanakkor, ha
	    // a visszaállított óra már nem fér bele... Ugye... Sajnos
	    $q_v = "UPDATE ora SET ki=kit, kit=NULL, tipus='normál', munkaido='lekötött', modositasDt=NOW()
    			WHERE tipus IN ('helyettesítés','felügyelet','összevonás','elmarad')
			AND dt='%s' AND kit IN (".implode(',', array_fill(0, count($toroltHianyzok), '%u')).")";
	    $v_v = mayor_array_join(array($dt), $toroltHianyzok);
	    $r_v = db_query($q_v, array('fv' => 'toroltHianyzokVisszaallitasa', 'modul' => 'naplo', 'values' => $v_v), $lr);

	    if ($olr == '') db_close($lr);
	}
    }

    function csereVisszaallitas($oraId, $olr = '') {


	if ($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;

	// A csere csereId-jének lekérdezése
	$q = "SELECT DISTINCT csereId
		    FROM csereAlapOra LEFT JOIN cserePluszOra USING (csereId)
            	    WHERE csereAlapOra.oraId=%u OR cserePluszOra.oraId=%u";
	$v = array($oraId, $oraId);
	$r = db_query($q, array('fv' => 'csereVisszaallitas', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);
	if (!$r) {
	    if ($olr == '') db_close($lr);
	    return false;
	}
	if (($num = count($r)) != 1) {
	    $_SESSION['alert'][] = 'message:wrong_data:csereVisszaallitas:Nincs csere?:'.$num;
	    if ($olr == '') db_close($lr);
	    return false; // Lehet ilyen?
	} 
	$csereId = $r[0];

	// A cserében résztvevő órák id-jének lekérdezése
	$q = "SELECT csereAlapOra.oraId AS alap, cserePluszOra.oraId AS plusz
		    FROM cserePluszOra LEFT JOIN csereAlapOra USING (csereId)
            	    WHERE csereId=%u";
	$r = db_query($q, array('fv' => 'csereVisszaallitas', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($csereId)), $lr);
	if (!$r) {
	    if ($olr == '') db_close($lr);
	    return false;
	}	
	$Alap = $Plusz = array();
	for ($i = 0; $i < count($r); $i++) {
	    $alap = $r[$i]['alap']; $plusz = $r[$i]['plusz'];
	    if (!in_array($alap, $Alap)) $Alap[] = $alap;
	    if (!in_array($plusz, $Plusz)) $Plusz[] = $plusz;
	}

    	// az eredeti órák 'elmarad máskor'-ról 'elmarad'-ra állítása
    	$q = "UPDATE ora SET tipus='elmarad',modositasDt=NOW() WHERE oraId IN (".implode(',', array_fill(0, count($Alap), '%u')).")";
        $r = db_query($q, array('fv' => 'csereVisszaallitas', 'modul' => 'naplo', 'values' => $Alap), $lr);
	if (!$r) {
	    if ($olr == '') db_close($lr);
	    return false;
	}

    	// a csere-bejegyzés törlése
    	$q = "DELETE FROM csere WHERE csereId=%u";
    	$r = db_query($q, array('fv' => 'csereVisszaallitas', 'modul' => 'naplo', 'values' => array($csereId)), $lr);
	if (!$r) {
	    if ($olr == '') db_close($lr);
	    return false;
	}

	// Az órákhoz rendelt hiányzások és jegyhozzárendelések törlése (csak plusz lehet érintett!)
	hianyzasEsJegyHozzarendelesTorles($Plusz, $lr);

    	// a plusz órák törlése
    	$q = "DELETE FROM ora WHERE oraId IN (".implode(',', array_fill(0, count($Plusz), '%u')).")";
    	$r = db_query($q, array('fv' => 'csereVisszaallitas', 'modul' => 'naplo', 'values' => $Plusz), $lr);
	if (!$r) {
	    if ($olr == '') db_close($lr);
	    return false;
	}

	if ($olr == '') db_close($lr);
	return true;

    }


    function cmp($a,$b) {
	if ($a['súly'] == $b['súly']) return 0;
	return ($a['súly'] > $b['súly']) ? -1 : 1;
    }


    function ujOra($ORA, $olr = null) {
	// alapértelmezésben munkaido='lekötött'
	if ($ORA['ki'] == '')  $ORA['ki'] = 'NULL';
	if ($ORA['kit'] == '') $ORA['kit'] = 'NULL';
	if ($ORA['teremId'] == '') $ORA['teremId'] = 'NULL';
	$q = "INSERT INTO ora (ki,kit,dt,ora,tankorId,teremId,leiras,tipus,eredet,modositasDt) VALUES (%s, %s, '%s', %u, %u, %s, '%s', '%s', '%s',NOW())";
	$v = array($ORA['ki'], $ORA['kit'], $ORA['dt'], $ORA['ora'], $ORA['tankorId'], $ORA['teremId'], $ORA['leiras'], $ORA['tipus'], $ORA['eredet']);
	return db_query($q, array('fv' => 'ujOra', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v), $olr);
    }

    function oraMozgatas($oraId, $dt, $ora, $olr = '') {


	if ($olr == '') $lr = db_connect('naplo', array('fv' => 'oraMozgatas'));
	else $lr = $olr;

            $oraAdat = getOraAdatById($oraId, __TANEV, $lr);

	    if ($oraAdat['tipus'] == 'elmarad máskor') {
		$_SESSION['alert'][] = 'message:wrong_data:oraMozgatas:már mozgatott óra:'.$oraId;
		if ($olr == '') db_close($lr);
		return false;
	    }

            if (isset($oraAdat['kit']) && $oraAdat['kit'] != '') $tanarId = $oraAdat['kit'];
            else $tanarId = $oraAdat['ki'];
            $tankorId = $oraAdat['tankorId'];
            $teremId = $oraAdat['teremId'];

	    checkNaplo($dt);

            // A tanár nem foglalt-e az adott időpontban
            if (!tanarLukasOrajaE($tanarId, $dt, $ora, $lr)) {
                $_SESSION['alert'][] = 'message:haladasi_utkozes:'."mozgat/tanár ütközés/$dt:$ora";
		if ($olr == '') db_close($lr);
    		return false;
            }

            // diák ütközés
            if (!tankorTagokLukasOrajaE($tankorId, $dt, $ora)) {
                $_SESSION['alert'][] = 'message:haladasi_utkozes:'."mozgat/diák ütközés/$dt:$ora";
		if ($olr == '') db_close($lr);
                return false;
            }

            // terem ellenőrzés
            $Termek = getSzabadTermek(array('dt' => $dt, 'ora' => $ora), $lr);
            for ($i = 0;($i < count($Termek) && $Termek[$i]['teremId'] != $teremId); $i++);
            if ($i >= count($Termek)) {
                $_SESSION['alert'][] = 'message:haladasi_utkozes:Foglalt terem:'.$teremId;
                $teremUtkozes = true;
            }

	    // érintett hiányzások, késések, felszerelés hiányok, egyenruha hiányok, jegyHozzárendelések törlése!
	    if (strtotime($dt) < time()) hianyzasEsJegyHozzarendelesTorles($oraId, $lr);
		
	    if ($oraAdat['eredet'] == 'órarend') {

		// ha órarendi óra, akkor elmarad máskor - plusz óra felvétel
		if (isset($oraAdat['kit']) && $oraAdat['kit'] != '') {
		    // kit nem üres, ki törölhető
		    $q = "UPDATE ora SET tipus='elmarad máskor',ki=NULL,modositasDt=NOW() WHERE oraId=%u";
    		    $oraAdat['ki'] = $tanarId;
    		    $oraAdat['kit'] = '';
		} else {
		    // normál óra, akkor a ki --> kit...
		    $q = "UPDATE ora SET tipus='elmarad máskor',kit=ki,ki=NULL,modositasDt=NOW() WHERE oraId=%u";
		}
	        $r = db_query($q, array('fv' => 'oraMozgatas', 'modul' => 'naplo', 'values' => array($oraAdat['oraId'])), $lr);
		if (!$r) {
		    if ($olr == '') db_close($lr);
		    return false;
		}

		// A felveendő plusz óra
		$oraAdat['tipus']='normál máskor';
		$oraAdat['eredet']='plusz';
		$oraAdat['dt'] = $dt;
	        $oraAdat['ora'] = $ora;
		if ($teremUtkozes) $oraAdat['teremId'] = 'NULL';

		if ($pluszId = ujOra($oraAdat, $lr)) {

	    	    // órarendi óra mozgatásánál a csere táblába is fel kell venni...
		    $error = '';
		    db_start_trans($lr);

		    $q = "INSERT INTO csere VALUES (NULL)";
	    	    $csereId = db_query($q, array('fv' => 'oraMozgatas', 'modul' => 'naplo', 'result' => 'insert', 'rollback' => true), $lr);
		    if (!$csereId) { if ($olr == '') db_close($lr); return false; }

		    $q = "INSERT INTO csereAlapOra (csereId, oraId) VALUES (%u, %u)";
	    	    $r = db_query($q, array('fv' => 'oraMozgatas', 'modul' => 'naplo', 'values' => array($csereId, $oraId), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    $q = "INSERT INTO cserePluszOra (csereId, oraId) VALUES (%u, %u)";
	    	    $r = db_query($q, array('fv' => 'oraMozgatas', 'modul' => 'naplo', 'values' => array($csereId, $pluszId), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    db_commit($lr);

		}
	    } else {

		// plusz óra egyszerűen módosítandó...
	        if ($teremUtkozes) $q = "UPDATE ora SET tipus='normál máskor',ki=%u, kit=NULL, dt='%s', ora='%u', teremId=NULL, modositasDt=NOW() WHERE oraId=%u";
		else $q = "UPDATE ora SET tipus='normál máskor',ki=%u, kit=NULL, dt='%s', ora='%u', modositasDt=NOW() WHERE oraId=%u";
		$v = array($tanarId, $dt, $ora, $oraId);
		$r = db_query($q, array('fv' => 'oraMozgatas', 'modul' => 'naplo', 'values' => $v), $lr);
		if (!$r) { if ($olr == '') db_close($lr); return false; }
		
	    } // órarendi vagy plusz óra

	if ($olr == '') db_close($lr);
	return true;

    }

    function getCsereOraiByOraId($oraId) {


	$lr = db_connect('naplo', array('fv' => 'getCsereOraiByOraId'));

	$q = "SELECT DISTINCT csereId FROM csereAlapOra LEFT JOIN cserePluszOra USING (csereId)
		WHERE csereAlapOra.oraId=%u OR cserePluszOra.oraId=%u";
	$v = array($oraId, $oraId);
	$arrayCsereId = db_query($q , array('fv' => 'getCsereOraiByOraId', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);
	if (!is_array($arrayCsereId) || ($num = count($arrayCsereId)) != 1) {
	    $_SESSION['alert'][] = 'message:wrong_data:getCsereOraiByOraId:Nincs csere?:'.$num.'/'.$oraId;
	    db_close($lr); return false;
	}
	$csereId = $arrayCsereId[0];

	$q = "SELECT DISTINCT oraId, dt, ora, ki, kit, tankorId, tipus, eredet, tankorNev
                FROM csereAlapOra LEFT JOIN ora USING (oraId)
                    LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
                WHERE csereId=%u AND tanev=".__TANEV." ORDER BY dt, ora";
        $ret['alap'] =  db_query($q, array(
	    'fv' => 'getCsereOraiByOraId', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => array($csereId)
	), $lr);

	$q = "SELECT DISTINCT oraId, dt, ora, ki, kit, tankorId, tipus, eredet, tankorNev
                FROM cserePluszOra LEFT JOIN ora USING (oraId)
                    LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
                WHERE csereId=%u AND tanev=".__TANEV." ORDER BY dt, ora";
        $ret['plusz'] =  db_query($q, array(
	    'fv' => 'getCsereOraiByOraId', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => array($csereId)
	), $lr);

	db_close($lr);
	return $ret;

    }

    function oraCsere($oraId1, $oraId2, $olr = '') {


	if ($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;

            $csereOraAdat1 = $oraAdat1 = getOraAdatById($oraId1, __TANEV, $lr);
            $csereOraAdat2 = $oraAdat2 = getOraAdatById($oraId2, __TANEV, $lr);

	    if ($oraAdat1['tipus'] == 'elmarad máskor' || $oraAdat2['tipus'] == 'elmarad máskor') {
		$_SESSION['alert'][] = 'message:wrong_data:oraCsere:már mozgatott óra:'.$oraId1.'/'.$oraId2;
		if ($olr == '') db_close($lr);
		return false;
	    }

            if (isset($oraAdat1['kit']) && $oraAdat1['kit'] != '') $tanarId1 = $oraAdat1['kit'];
            else $tanarId1 = $oraAdat1['ki'];

            if (isset($oraAdat2['kit']) && $oraAdat2['kit'] != '') $tanarId2 = $oraAdat2['kit'];
            else $tanarId2 = $oraAdat2['ki'];

	    if ($tanarId1 != $tanarId2) {
        	// Ha nem saját magával cserél, akkor a tanár nem foglalt-e az adott időpontban
        	if (!tanarLukasOrajaE($tanarId1, $oraAdat2['dt'], $oraAdat2['ora'], $lr)) {
            	    $_SESSION['alert'][] = 'message:haladasi_utkozes:'."oraCsere/tanár ütközés #1 (".$oraId1.'):'.$oraAdat2['dt'].':'.$oraAdat2['ora'];
		    if ($olr == '') db_close($lr);
    		    return false;
        	}
        	if (!tanarLukasOrajaE($tanarId2, $oraAdat1['dt'], $oraAdat1['ora'], $lr)) {
            	    $_SESSION['alert'][] = 'message:haladasi_utkozes:'."oraCsere/tanár ütközés #2 (".$oraId2.'):'.$oraAdat1['dt'].':'.$oraAdat1['ora'];
		    if ($olr == '') db_close($lr);
    		    return false;
        	}
	    }

            // diák ütközés
            if (!tankorTagokLukasOrajaE($oraAdat1['tankorId'], $oraAdat2['dt'], $oraAdat2['ora'], $oraAdat2['tankorId'])) {
		if ($olr == '') db_close($lr);
                return false;
            }
            if (!tankorTagokLukasOrajaE($oraAdat2['tankorId'], $oraAdat1['dt'], $oraAdat1['ora'], $oraAdat1['tankorId'])) {
		if ($olr == '') db_close($lr);
                return false;
            }

            // terem ellenőrzés nincs: Termeket nem cserélünk!!

	    // érintett hiányzások, késések, felszerelés hiányok, egyenruha hiányok, jegyHozzárendelések törlése!
	    if (strtotime($oraAdat1['dt']) < time()) hianyzasEsJegyHozzarendelesTorles($oraId1, $lr);
	    if (strtotime($oraAdat2['dt']) < time()) hianyzasEsJegyHozzarendelesTorles($oraId2, $lr);

	    // 1. óra mozgatása
	    if ($oraAdat1['eredet'] == 'órarend') {

		$q = "UPDATE ora SET tipus='elmarad máskor', kit=%u, ki=NULL, modositasDt=NOW() WHERE oraId=%u";
		if (!db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($tanarId1, $oraAdat1['oraId'])), $lr)) {
		    if ($olr == '') db_close($lr);
		    return false;
		}

		// A felveendő plusz óra
    		$csereOraAdat1['ki'] = $tanarId1;
    		$csereOraAdat1['kit'] = 'NULL';
		$csereOraAdat1['tipus']='normál máskor';
		$csereOraAdat1['eredet']='plusz';
		$csereOraAdat1['dt'] = $oraAdat2['dt'];
	        $csereOraAdat1['ora'] = $oraAdat2['ora'];
		$csereOraAdat1['teremId'] = $oraAdat2['teremId'];

		$pluszId1 = ujOra($csereOraAdat1, $lr);

		// A csere táblába bejegyezzük a mozgatást
		if ($pluszId1) {

		    db_start_trans($lr);

		    $q = "INSERT INTO csere VALUES (NULL)";
		    $csereId1 = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'result' => 'insert', 'rollback' => true), $lr);
		    if (!$csereId1) { if ($olr == '') db_close($lr); return false; }

		    $q = "INSERT INTO csereAlapOra (csereId, oraId) VALUES (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId1, $oraId1), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    $q = "INSERT INTO cserePluszOra (csereId, oraId) VALUES (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId1, $pluszId1), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    db_commit($lr);
		}

	    } else {

		// plusz óra egyszerűen módosítandó...
	        $q = "UPDATE ora SET tipus='normál máskor', ki=%u, kit=NULL, dt='%s', ora=%u, teremId=%u, modositasDt=NOW() WHERE oraId=%u";
		$r = db_query($q, array(
		    'fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($tanarId1, $oraAdat2['dt'], $oraAdat2['ora'], $oraAdat2['teremId'], $oraId1)
		), $lr);
		if (!$r) { if ($olr == '') db_close($lr); return false; }

		// A plusz óra beletartozik-e valamelyik cserébe (elvileg csak egybe tartozhat)
		$q = "SELECT csereId FROM cserePluszOra WHERE oraId=%u";
		$csereId1 = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'result' => 'value', 'values' => array($oraId1)), $lr);
		
	    } // 1. óra: órarendi vagy plusz óra

	    // 2. óra mozgatsa
	    if ($oraAdat2['eredet'] == 'órarend') {

		// ha órarendi óra, akkor elmarad máskor - plusz óra felvétel
		$q = "UPDATE ora SET tipus='elmarad máskor', kit=%u, ki=NULL, modositasDt=NOW() WHERE oraId=%u";
		$r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($tanarId2, $oraAdat2['oraId'])), $lr);
		if (!r) { if ($olr == '') db_close($lr); return false; }

		// A felveendő plusz óra
    		$csereOraAdat2['ki'] = $tanarId2;
    		$csereOraAdat2['kit'] = 'NULL';
		$csereOraAdat2['tipus']='normál máskor';
		$csereOraAdat2['eredet']='plusz';
		$csereOraAdat2['dt'] = $oraAdat1['dt'];
	        $csereOraAdat2['ora'] = $oraAdat1['ora'];
		$csereOraAdat2['teremId'] = $oraAdat1['teremId'];

		$pluszId2 = ujOra($csereOraAdat2, $lr);
		// A csere táblába bejegyezzük a mozgatást
		if ($pluszId2) {

		    db_start_trans($lr);

		    $q = "INSERT INTO csere VALUES (NULL)";
		    $csereId2 = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'result' => 'insert', 'rollback' => true), $lr);
		    if (!$csereId2) { if ($olr == '') db_close($lr); return false; }

		    $q = "INSERT INTO csereAlapOra (csereId, oraId) VALUES (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId2, $oraId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    $q = "INSERT INTO cserePluszOra (csereId, oraId) VALUES (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId2, $pluszId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    db_commit($lr);
		}

	    } else {

		// plusz óra egyszerűen módosítandó...
	        $q = "UPDATE ora SET tipus='normál máskor', ki=%u , kit=NULL, dt='%s', ora=%u, teremId=%u, modositasDt=NOW() WHERE oraId=%u";
		$r = db_query($q, array(
		    'fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($tanarId2, $oraAdat1['dt'], $oraAdat1['ora'], $oraAdat1['teremId'], $oraId2)
		), $lr);
		if (!$r) { if ($olr == '') db_close($lr); return false; }

		// A plusz óra beletartozik-e valamelyik cserébe
		$q = "SELECT csereId FROM cserePluszOra WHERE oraId=%u";
		$csereId2 = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'result' => 'value', 'values' => array($oraId2)), $lr);
		
	    } // 2. óra: órarendi vagy plusz óra

	    // Egy cserévé tesszük...

		if (isset($csereId1) && isset($csereId2) && $csereId1 != $csereId2) {

		    db_start_trans($lr);

		    // A cserélt órák két különböző cserébe tartoznak --> egyesítjük a két cserét
		    $q = "UPDATE csereAlapOra SET csereId=%u WHERE csereId=%u";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId1, $csereId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    $q = "UPDATE cserePluszOra SET csereId=%u WHERE csereId=%u";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId1, $csereId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    $q = "DELETE FROM csere WHERE csereId=%u";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		    db_commit($lr);

		} elseif (isset($csereId1) && !isset($csereId2)) {

		    // Csak az első óra van cserében --> a másodikat is (ami plusz óra) bele kell rakni
		    $q = "INSERT INTO cserePluszOra (csereId, oraId) VALUES (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId1, $oraId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		} elseif (!isset($csereId1) && isset($csereId2)) {
		    // Csak a második óra van cserében --> az elsőt is (ami plusz óra) bele kell rakni
		    $q = "INSERT INTO cserePluszOra (csereId, oraId) VALUES (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($csereId2, $oraId1), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		} elseif (!isset($csereId1) && !isset($csereId2)) {
		    // Egyik sincs cserében --> Azaz két plusz óra --> nem vesszük fel őket cserének
		    // Ezért ez nem is csere --> a típusok nem normál máskor, hanem normál
		    $q = "UPDATE ora SET tipus='normál', modositasDt=NOW() WHERE oraId IN (%u, %u)";
		    $r = db_query($q, array('fv' => 'oraCsere', 'modul' => 'naplo', 'values' => array($oraId1, $oraId2), 'rollback' => true), $lr);
		    if (!$r) { if ($olr == '') db_close($lr); return false; }

		}

	if ($olr == '') db_close($lr);
	return true;

    }

    function tanarTankortTanithatE($tanarId, $tankorId, $olr = '') {


	if ($olr != '') $lr = $olr;
	else $lr = db_connect('naplo');

    	    // Beletartozik-e a megadott tanár a tankör munkaközösségébe?
            $q = "SELECT COUNT(tanarId)
                    FROM ".__INTEZMENYDBNEV.".tankor
                        LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (targyId)
                        LEFT JOIN ".__INTEZMENYDBNEV.".mkTanar USING (mkId)
                    WHERE tankorId=%u AND tanarId=%u";
	    $num = db_query($q, array(
		'fv' => 'tanarTankortTanithatE', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tankorId, $tanarId)
	    ));
	    if ($num != 1) $_SESSION['alert'][] = 'message:wrong_data:keziBeallitas/tanarTankortTanithatE:Nem szakos:(tanarId/tankorId) - '.$tanarId.'/'.$tankorId;

	if ($olr == '') db_close($lr);
	return ($num == 1);

    }


    function keziBeallitas($oraId, $ki, $tipus, $teremId) {

	global $dt;

        $lr = db_connect('naplo', array('fv' => 'keziBeallitas'));

            $oraAdat = getOraAdatById($oraId, __TANEV, $lr);
            $dt = $oraAdat['dt'];
            if ($ki != $oraAdat['ki'] || $tipus != $oraAdat['tipus'] || $teremId != $oraAdat['teremId']) {
                $ok = true;
                // új tanár, vagy típus - ütközés ellenőrzése
                if ($ki != $oraAdat['ki'] || $tipus != $oraAdat['tipus']) {
                    $q = "SELECT COUNT(*) FROM ora
                    	    WHERE dt='%s' AND ora=%u AND oraId!=%u AND ki=%u AND tipus NOT LIKE 'elmarad%%'";
		    $_db = db_query($q, array(
			'fv' => 'keziBeallitas', 'modul' => 'naplo', 'result' => 'value', 'values' => array($oraAdat['dt'], $oraAdat['ora'], $oraId, $ki)
		    ), $lr);
                    if ($_db == 0) {
                        if ($tipus == 'összevonás') {
                            $_SESSION['alert'][] = 'message:wrong_data:keziBeallitas:Egy órát nem lehet összevonni';
                            $ok = false;
                        } elseif ($tipus == 'helyettesítés') {
                            // Beletartozik-e a megadott tanár a tankör munkaközösségébe?
                            $ok = tanarTankortTanithatE($ki, $oraAdat['tankorId'], $lr);
                        }
                    } else {
                        if ($tipus != 'összevonás') {
                            $_SESSION['alert'][] = "message:haladasi_utkozes:keziBeallitas:Már van órája!:$ki";
                            $ok = false;
                        } else {
                            // Beletartozik-e a megadott tanár a tankör munkaközösségébe?
                            $ok = tanarTankortTanithatE($ki, $oraAdat['tankorId'], $lr);
                        }
                    }
                }
		// Terem változtatás - szabad-e a terem? vagy nincs megadva
                if ($teremId != $oraAdat['teremId'] && $teremId!='NULL') {
                    // Szabad-e a terem? - a saját maga által használt terem nem foglalt! (összevonáshoz így kell)
            	    $Termek = getSzabadTermek(array('dt' => $OraAdat['dt'], 'ora' => $oraAdat['ora'], 'ki' => $ki), $lr);
                    for ($i = 0;($i < count($Termek) && $Termek[$i]['teremId'] != $teremId); $i++);
                    if ($i >= count($Termek)) {
                    	$_SESSION['alert'][] = 'message:haladasi_utkozes:keziBeallitas:Foglalt terem:'.$teremId;
                    	$ok = false;
                    }
                }
		// Módosítás
                if ($ok && $tipus != 'elmarad') {
                    if (substr($oraAdat['tipus'], 0, 7) == 'normál') {
                        if ($tipus == $oraAdat['tipus'] || $ki == $oraAdat['ki']) {
                            // Vagy mindkettő változik, vagy egyik se
                            $q = "UPDATE ora SET teremId=%u, modositasDt=NOW() WHERE oraId=%u";
			    $v = array($teremId, $oraId);
                        } else { // ki --> kit (csak helyettesítés, felügyelet, vagy összevonás lehet az új típus!!
                            $q = "UPDATE ora SET kit=ki, ki=%u, tipus='%s', teremId=%u, modositasDt=NOW() WHERE oraId=%u";
			    $v = array($ki, $tipus, $teremId, $oraId);
			}
                    } else { // kit nem változik
                        $q = "UPDATE ora SET ki=%u, tipus='%s', teremId=%u, modositasDt=NOW() WHERE oraId=%u";
			$v = array($ki, $tipus, $teremId, $oraId);
		    }
                    $r = db_query($q, array('fv' => 'keziBeallitas', 'modul' => 'naplo', 'values' => $v), $lr);
                    if ($r) $_SESSION['alert'][] = 'info:change_success:keziBeallitas';
                } else {
		    $_SESSION['alert'][] = 'info:do_nothing:keziBeallitas:Nem történt módosítás';
		}
            }

        db_close($lr);

    }

    function ujHelyettes2($oraAdat, $ki, $tipus, $olr = null) {
	$oraId = $oraAdat['oraId'];
	$dt = $oraAdat['dt'];
	if (is_null($ki) || $ki!=0) {

	    // a helyettesített óra milyen munkaidőbe számolódik vajon?
	    // 1. összevonás = ??? (ezt nem vesszük figyelembe, ezért tökmindegy)
	    // 2. felügyelet = ez bizony egyértelműen a fennmaradó
	    // 3. helyettesítés = lekötött HA (26-on belül van az elmúlt 5 napos lekötött VAGY még a 28-on belül van, de érvényesek a feltételek 2-6-30)
	    //			  fennmaradó EGYÉBKÉNT.

	    // ez már le van kérdezve, de a tranzakció miatt sajnos újra kell:
	    $TERHELES = getOraTerhelesStatByTanarId(array('tanarId'=>array($ki),'dt'=>$dt), $olr);

	    if ($tipus=='összevonás') $_munkaido = 'lekötött';
	    elseif ($tipus=='felügyelet') $_munkaido = 'fennmaradó';
	    elseif ($tipus=='helyettesítés') {
		if ($TERHELES[$ki]['munkaido']['lekotott']>$TERHELES[$ki]['lekotott']['heti']) {
		    $_lekotheto = true;
		} elseif ($TERHELES[$ki]['over']['napi']<2 && $TERHELES[$ki]['over']['heti']<6) {
		    $_lekotheto =false; 
		} else {
		    $_lekotheto=false;
		    $_SESSION['alert'][] = 'info:OVERTIME'; // időközben túllépte valahogy
		}
		if ($_lekotheto===true) $_munkaido = 'lekötött'; else $_munkaido='fennmaradó';
	    } else {
		$_munkaido='lekötött'; //tipus???
	    }

	    if ($_munkaido=='')
		$q = "UPDATE ora SET ki=%u,tipus='%s', modositasDt=NOW() WHERE oraId=%u";
	    else
		$q = "UPDATE ora SET ki=%u,tipus='%s',munkaido='".$_munkaido."', modositasDt=NOW() WHERE oraId=%u";

	    return db_query($q, array('fv' => 'ujHelyettes', 'modul' => 'naplo', 'values' => array($ki, $tipus, $oraId)), $olr);
	} else {
	    $_SESSION['alert'][] = '::Nem sikerült beállítani az új helyettest!:oraId-'.$oraId.':ki-'.$ki.':tipus-'.$tipus;
	    return false;
	}

    }

    function eredetiOraVissza($oraId, $eredet, $olr = null) {


	if ($olr == '') $lr = db_connect('naplo', array('fv' => 'eredetiOraVissza'));
	else $lr = $olr;

	if ($eredet == 'plusz') {
	    $q_csere = "SELECT COUNT(csereId) FROM cserePluszOra WHERE oraId=%u";
	    $num = db_query($q_csere, array('fv' => 'eredetiOraVissza', 'modul' => 'naplo', 'result' => 'value', 'values' => array($oraId)), $lr);
	}
	if (($eredet == 'órarend') || ($num == 0))
	    $q = "UPDATE ora SET ki=kit,kit=NULL,tipus='normál',munkaido='lekötött',modositasDt=NOW() WHERE oraId=%u";
    	else
	    $q = "UPDATE ora SET ki=kit,kit=NULL,tipus='normál máskor',munkaido='lekötött',modositasDt=NOW() WHERE oraId=%u";
	$r = db_query($q, array('fv' => 'eredetiOraVissza', 'modul' => 'naplo', 'values' => array($oraId)), $lr);

	if ($olr == '') db_close($lr);
    }

    function helyettesitesRogzites($T) {


        $lr = db_connect('naplo');

            for ($i = 0; $i < count($T); $i++) {

                if ($T[$i] == '') continue; // ha eredeti maradt, ne módosítson!
		$teremUtkozes = false;
                list($ki, $oraId, $tipus) = explode('/',$T[$i]);
                $oraAdat = getOraAdatById($oraId);
		if ($tipus == 'normál') {
		    if ($oraAdat['kit'] != '') $ki = $oraAdat['kit'];
		    else $ki = $oraAdat['ki'];
		}
                $regi_tipus = $oraAdat['tipus'];

                    // A csere miatt elmaradt óra változásakor, illetve
                    // a csere miatt felvett óra visszaállításakor a cserét meg kell szüntetni
                    if (($regi_tipus == 'normál máskor' && $tipus == '') || ($regi_tipus == 'elmarad máskor')) {
                        csereVisszaallitas($oraId, $lr);
			if (
			    $tipus == '' // csere/mozgatás visszaállítás
			    || ($regi_tipus == 'elmarad máskor' && $tipus == 'elmarad') // mozgatott óra elmarad
			) continue;
                    }

                    // Elmaradó óra esetén a hiányzások, késések, felszerelés hiányok, egyenruha hiányok, jegy hozzárendelések törlendők!
		    // Ezek bekerült az oraElmarad függvénybe
		    if ($tipus=='töröl' && $oraAdat['tipus']=='egyéb') { // az egyéb típusú óra gond nélkül törölhető, nincs hozzá semmi
			oraElmarad($oraId, $lr); // használjuk ugyanazt a függvényt
		    } elseif (substr($tipus,0,7) == 'elmarad') {
			oraElmarad($oraId, $lr);
                    } else {
		    // Ha egy órát "mégis" megtartunk, akkor ellenőrizni kell, hogy nem ütközik-e valamivel!! (esetleg felvett plusz órával, mozgatott órával)
			// Tanár ellenőrzése
			if (tanarLukasOrajaE($ki, $oraAdat['dt'], $oraAdat['ora'], $lr)) {
			    if ($tipus == 'összevonás') {
				$_SESSION['alert'][] = 'message:wrong_data:helyettesitesRogzites:nincs mivel összevonni:'.$oraId;
				continue;				
			    }
			} else {
			    if ($tipus != 'összevonás') {
				$_SESSION['alert'][] = 'message:haladasi_utkozes:helyettesitesRogzites:'.$oraId;
				continue;
			    }
			}
                        if (substr($regi_tipus,0,7) == 'elmarad') {
			    // Tankör tagok ellenőrzése
			    if (getTankorJelenletKotelezoE($oraAdat['tankorId']) && !tankorTagokLukasOrajaE($oraAdat['tankorId'], $oraAdat['dt'], $oraAdat['ora'])) {
				$_SESSION['alert'][] = 'message:wrong_data:A mégis megtartott óra ütközne!:oraId='.$oraId.', dt='.$oraAdat['dt'].', ora='.$oraAdat['ora'];
				continue;
			    }
			}

			// A helyettesítés rögzítése
                	if (mb_substr($tipus,0,6,'UTF-8') == 'normál') {
                    	    if (mb_substr($regi_tipus,0,6,'UTF-8') != 'normál') eredetiOraVissza($oraId, $oraAdat['eredet'], $lr);
                	} else {
                    	    if (mb_substr($regi_tipus,0,6,'UTF-8') == 'normál') {
				// óraelmaradás itt már nem lehet!
				masTartja($oraId, $ki, $tipus, $lr);
                    	    } else {
				// Ebbe NEM értjük bele a helyettesítés --> elmarad váltást
				// de beleértjük az elmarad --> helyettesítés váltást
                        	//ujHelyettes($oraId, $ki, $tipus, $lr);
                        	ujHelyettes2($oraAdat, $ki, $tipus, $lr);
                    	    }
                	}

		    } // elmarad / nem marad el


            	    // Ha összevonásról nem összevonásra állítunk, akkor kialakulhatott terem ütközés!
                    if (
			$oraAdat['teremId'] != ''
			&& substr($tipus,0,7) != 'elmarad'
			&& ($regi_tipus == 'összevonás' || substr($regi_tipus,0,7) == 'elmarad') 
		    ) {
                        $Termek = getSzabadTermek(array('dt' => $oraAdat['dt'], 'ora' => $oraAdat['ora'], 'ki' => $ki), $lr);
                        for ($j = 0; ($j < count($Termek) && $Termek[$j]['teremId'] != $oraAdat['teremId']); $j++);
                        if ($j >= count($Termek)) {
                            $_SESSION['alert'][] = 'message:haladasi_utkozes:hianyzasRogzites:a terem foglalt:'.$oraAdat['teremId'];
			    $q = "UPDATE ".__TANEVDBNEV.".ora SET teremId=NULL WHERE oraId=%u";
                            db_query($q, array('fv' => 'hianyzasRogzites', 'modul' => 'naplo', 'values' => array($oraId)), $lr);
                        }
            	    }
            } // for

        db_close($lr);

    }


?>
