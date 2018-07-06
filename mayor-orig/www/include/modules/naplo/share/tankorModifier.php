<?php

    require_once('include/modules/naplo/share/tankorBlokk.php');

    function setTankorNevByDiakok($tankorId, $tankorNevExtra = null, $olr = null) { // módosítja a tankorOsztaly hozzárendelést
        if (!$olr) $lr = db_connect('naplo_intezmeny', array('fv' => 'ujTankor'));
        else $lr = $olr;
	$O = getTankorOsztalyaiByTanev($tankorId, __TANEV, array('tagokAlapjan'=>true,'result'=>'id'),$lr);
	if (count($O)>0) {
	    $q = "DELETE FROM tankorOsztaly WHERE tankorId=%u AND osztalyId NOT IN (".implode(',',$O).")";
	    db_query($q, array('fv' => 'setTankorNevByDiakok', 'modul' => 'naplo_intezmeny', 'result'=>'delete','values' => array($tankorId)),$lr);
	}
	setTankorNev($tankorId,$tankorNevExtra,$olr);
	if (!$olr) db_close($lr);

    }
    // 2012.09. Az adott tanévtől! átnevezi a tankört!
    function setTankorNev($tankorId, $tankorNevExtra = null, $olr = null) { // a függvényt a tankor.php / ujTankor() függvényéből örököltük. "Majdnem" ugyanaz

	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/szemeszter.php');

        if (!$olr) $lr = db_connect('naplo_intezmeny', array('fv' => 'ujTankor'));
        else $lr = $olr;

	if (!$lr) return false;

	// adatgyűjtés tankorId alapján
	$q = "SELECT targyId FROM tankor WHERE tankorId=%u";
	$targyId = db_query($q, array('fv' => 'genTankorNev', 'modul' => 'naplo_intezmeny', 'result'=>'value', 'values' => array($tankorId), 'debug'=>false), $lr);
	$TARGYADAT = getTargyById($targyId,$lr);

	$q = "SELECT osztalyId FROM tankorOsztaly WHERE tankorId=%u";
	$ADAT['osztalyok'] = db_query($q, array('fv' => 'genTankorNev', 'modul' => 'naplo_intezmeny', 'result'=>'idonly', 'values' => array($tankorId), 'debug'=>false), $lr);

	if (is_null($tankorNevExtra)) {
	    $q = "SELECT IF(tankorJel IS NOT NULL AND INSTR(tankorNev,tankorJel)!=0,  trim(substring(trim(substring_index(tankorNev,targyNev,-1)),length(tankorJel)+1)), trim(substring_index(tankorNev,targyNev,-1)))  AS tankorNevExtra FROM tankorSzemeszter LEFT JOIN tankor USING (tankorId) LEFT JOIN targy USING (targyId) LEFT JOIN tankorTipus USING (tankorTipusId) WHERE tankorId=%u AND tanev=%u ORDER BY tanev,szemeszter DESC LIMIT 1";
	    $tankorNevExtra = db_query($q, array('fv' => 'genTankorNev', 'modul' => 'naplo_intezmeny', 'result'=>'value', 'values' => array($tankorId,__TANEV), 'debug'=>false), $lr);
	}

	$q = "SELECT tankorJel FROM tankor LEFT JOIN tankorTipus USING (tankorTipusId) WHERE tankorId=%u";
	$tankorJel = db_query($q, array('fv' => 'genTankorNev', 'modul' => 'naplo_intezmeny', 'result'=>'value', 'values' => array($tankorId), 'debug'=>false), $lr);

	$q = "SELECT DISTINCT szemeszterId FROM tankorSzemeszter LEFT JOIN szemeszter USING (tanev,szemeszter)  WHERE tankorId=%u";
	$ADAT['szemeszterek'] = db_query($q, array('fv' => 'genTankorNev', 'modul' => 'naplo_intezmeny', 'result'=>'idonly', 'values' => array($tankorId), 'debug'=>false), $lr);

	$q = "SELECT jelleg FROM tankor LEFT JOIN tankorTipus USING (tankorTipusId) WHERE tankorId=%u";
        $tankorTipusJelleg = db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'result'=>'value','values' => array($tankorId)),$lr);

	// --
	$kdt = '3000-01-01';
	$vdt = '1970-01-01';

	if (is_array($ADAT['szemeszterek'])) 
	for ($j = 0; $j < count($ADAT['szemeszterek']); $j++) {
	    $nev = '';
	    $szemeszterId = $ADAT['szemeszterek'][$j];
	    $_SZ = getSzemeszterek(array('filter' => array("szemeszterId=$szemeszterId")));
	    if ($_SZ[0]['kezdesDt'] < $kdt) $kdt = $_SZ[0]['kezdesDt'];
	    if ($_SZ[0]['zarasDt'] > $vdt) $vdt = $_SZ[0]['zarasDt'];
	    $_tanev = $_SZ[0]['tanev'];
	    $_szemeszter = $_SZ[0]['szemeszter'];
//	    $_oraszam = $ADAT['SZ'.$szemeszterId];
	    if ($tankorTipusJelleg=='osztályfüggetlen') {
                $nev = "Isk.";
	    } else {
	      $OSZTALYOK = getOsztalyok($_tanev,null,$lr);
	      if ($OSZTALYOK !== false && is_array($OSZTALYOK) && is_array($ADAT['osztalyok'])) {	
		$nev = '';
		$TMP = array();
		for($i = 0; $i < count($OSZTALYOK); $i++) {
                    // Ha évenként változik az osztály jele, akkor jobb, ha nem generáljuk, hanem a lekérdezett adatokat használjuk!
                    // $_oj = genOsztalyJel($_tanev, $OSZTALYOK[$i]);
		    $_oj = $OSZTALYOK[$i]['osztalyJel'];
		    if ($_oj!==false && !is_null($_oj)) {
			list($e,$o) = explode('.',$_oj);
			if (in_array($OSZTALYOK[$i]['osztalyId'], $ADAT['osztalyok'])) 
			    $TMP[$e][]= $o;
		    }
		}
		if (count(array_keys($TMP)) == 1) { // évfolyamon belüli osztályok:
		    $nev = implode('||',array_keys($TMP));
		    $nev .= '.'.implode('',$TMP[$nev]);
		} elseif (count((array_keys($TMP)))>1) { // multi évfolyam:
		    $K = (array_keys($TMP));
		    sort($K);
		    $nev = $K[0].'-'.$K[count($K)-1].'.';
		} else { // ekkorra már elballagott minden osztaly...
		    $nev = false;
		    $_SESSION['alert'][] = 'info:nem módosítható ebben a tanévben ez a tankör:'.$tankorId.' '.$_tanev;
		}
	      } else {
		$nev = false; // adott szemeszterbe nem jár osztály
		$_SESSION['alert'][] = 'info::Az adott szemeszterbe nem jár osztály:tankorId('.$tankorId.')';
	      }
	    }
	    if ($nev !== false) {
		$nev .= ' '.$TARGYADAT['targyNev'].' ';
		if ($tankorJel!='') $nev .= $tankorJel.' '.$tankorNevExtra;
		else $nev .= $tankorNevExtra;
		if ($_tanev >= __TANEV) {
		    $q = "UPDATE tankorSzemeszter SET tankorNev = '%s' WHERE tankorId=%u AND tanev=%u AND szemeszter=%u";
		    $v = array($nev,$tankorId,$_tanev,$_szemeszter);
		    if ($nev!='') db_query($q, array('fv' => 'setTankorNev', 'modul' => 'naplo_intezmeny', 'values' => $v, 'debug'=>false), $lr);
		} else { 
		    // a neve már ne változzon, és az óraszáma?
/*		    $q1 = "SELECT tankorNev FROM tankorSzemeszter WHERE tankorId=%u AND tanev=%u AND szemeszter=%u";
		    $orignev = db_query($q1, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => array($tankorId,$_tanev,$_szemeszter), 'debug'=>false), $lr);
		    if ($orignev!='') $nev = $orignev;
*/
		}
	    }
	    	    
	}
	if (!$olr) db_close($lr);
	return $nev;

    }

    function tankorTanarModosit($tankorId, $tanarId, $SET = array('tanev'=>'','tanevAdat'=>'', 'tolDt'=>'', 'igDt'=>'')) {
	global $_TANEV;
	$tanev = ($SET['tanev']!='') ? $SET['tanev'] : _TANEV;
	$tanevAdat = (is_array($SET['tanevAdat'])) ? $SET['tanevAdat'] : (($SET['tanev']=='') ? $_TANEV : getTanevAdat($tanev));
	$tolDt = ($SET['tolDt']!='') ? $SET['tolDt'] : $tanevAdat['kezdesDt'];
	$igDt  = ($SET['igDt']!='')  ? $SET['igDt']  : $tanevAdat['zarasDt'];
	if (strtotime($tolDt)>strtotime($igDt)) $_SESSION['alert'][] = 'error:wrong_data:hibás intervallum ('.$tanev.', '.$tolDt.'-'.$igDt.')';

	if (!is_numeric($tankorId)) return false;
	$tankorIds = array($tankorId); // kompatibilitási okokból
	$tankorTanarIds[$tankorId] = array($tanarId); // kompatibilitási okokból
	$D = array();

	$lr = db_connect('naplo_intezmeny', array('fv' => 'tankorTanarModosit'));
	db_start_trans($lr);

	// Az intervallumban érintett tankör-tanár tagságok lekérdezése...
	$v = array($tankorId, $igDt, $tolDt, $tanarId); // +tanarId, a többi tanár nem érdekes
	$q = "SELECT tanarId, tankorId, min(bedt) AS mbe ,max(kidt) AS mki
		FROM ".__INTEZMENYDBNEV.".tankorTanar WHERE tankorId = %u
	        AND bedt<='%s' AND kidt>='%s' AND tanarId=%u GROUP BY tankorId,tanarId";
	$ret = db_query($q, array('fv' => 'tankorTanarModosit', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v, 'rollback' => true), $lr);
	if ($ret === false) { db_close($lr); return false; }
	for ($i = 0; $i < count($ret); $i++) {
	    if ($tolDt < $ret[$i]['mbe']) $ret[$i]['mbe'] = $tolDt;
	    if ($igDt > $ret[$i]['mki']) $ret[$i]['mki'] = $igDt;
	    $D[ $ret[$i]['tankorId'] ][ $ret[$i]['tanarId'] ] = array('mbe' => $ret[$i]['mbe'], 'mki' => $ret[$i]['mki'], 'torlendo' => true);
	}

	// Az érintett intervallumba eső tankör-tanár tagságok törlése
	//$q = "DELETE FROM ".__INTEZMENYDBNEV.".tankorTanar WHERE tankorId = %u AND bedt<='%s' AND kidt>='%s'";
	$q = "DELETE FROM ".__INTEZMENYDBNEV.".tankorTanar WHERE tankorId = %u AND bedt<='%s' AND kidt>='%s' AND tanarId=%u";
	$r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	if ($r === false) { db_close($lr); return false; }

	// beszúrandó
//	for ($i = 0; $i < count($tankorIds); $i++) {
//	    $tankorId = $tankorIds[$i];
//	    $tanarIds = $tankorTanarIds[$tankorId];
//	    for ($j = 0; $j < count($tanarIds); $j++) {
//		$tanarId = $tanarIds[$j];
		if ($tanarId != '') {
		    $D[$tankorId][$tanarId]['torlendo'] = false;
		    if (($beDt = $D[$tankorId][$tanarId]['mbe']) == '') $beDt = $tolDt;
		    if (($kiDt = $D[$tankorId][$tanarId]['mki']) == '') $kiDt = $igDt;
		    $q = "INSERT INTO ".__INTEZMENYDBNEV.".tankorTanar (tankorId,tanarId,beDt,kiDt) VALUES (%u, %u, '%s', '%s')";
		    $v = array($tankorId, $tanarId, $beDt, $kiDt);
		    $r = db_query($q, array('fv' => 'tankorTanarFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
		    if ($r === false) { db_close($lr); return false; }
		}
//	    }
//	}

	// törlendők felvétele
//	for ($i = 0; $i < count($tankorIds); $i++) {
//	    $tankorId = $tankorIds[$i];
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
//	}

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
		db_rollback($lr, 'Az összes ütközést ellenőriztem, és a megadott '.$tolDt.'-'.$igDt.' intervallumban egy (esetleg másik) tanárnak több órája van egy időben - így visszaállítjuk az eredeti állapotot...');
		db_close($lr);
		return false;
	    }

	} // aktív tanév

	db_commit($lr);
	db_close($lr);
	return true;

    }

    function tankorTanarTorol($tankorId, $tanarId, $SET = array('tanev'=>'','tanevAdat'=>'', 'tolDt'=>'', 'igDt'=>'')) { // csak ebből a tankörből csak ezt a tanárt, csak ebben az intervallumban
	global $_TANEV;
	$tanev = ($SET['tanev']!='') ? $SET['tanev'] : _TANEV;
	$tanevAdat = (is_array($SET['tanevAdat'])) ? $SET['tanevAdat'] : (($SET['tanev']=='') ? $_TANEV : getTanevAdat($tanev));
	$A = $tolDt = ($SET['tolDt']!='') ? $SET['tolDt'] : $tanevAdat['kezdesDt'];
	$B = $igDt  = ($SET['igDt']!='')  ? $SET['igDt']  : $tanevAdat['zarasDt'];
	if (strtotime($tolDt)>strtotime($igDt)) $_SESSION['alert'][] = 'error:wrong_data:hibás intervallum ('.$tanev.', '.$tolDt.'-'.$igDt.')';
	if (!is_numeric($tankorId)) return false;

	// [$A-$B] zárt intervallumban megszűnik a tankör tanárának lenni, de mást nem módosítunk
	$q = "DELETE FROM tankorTanar WHERE tankorId=%u AND tanarId=%u AND beDt>='%s' AND beDt<='%s' AND kiDt<='%s'";
	$v = array($tankorId,$tanarId,$A,$B,$B);
	db_query($q, array('debug'=>false,'fv'=>'tankorTanarTorol/delete','modul'=>'naplo_intezmeny','values'=>$v));

	$q = "UPDATE tankorTanar SET kiDt='%s' - INTERVAL 1 DAY WHERE tankorId=%u AND tanarId=%u AND beDt<='%s' AND kiDt>='%s'";
	$v = array($A,$tankorId,$tanarId,$A,$A);
	db_query($q, array('debug'=>false,'fv'=>'tankorTanarTorol/delete','modul'=>'naplo_intezmeny','values'=>$v));

	$q = "UPDATE tankorTanar SET beDt='%s' + INTERVAL 1 DAY WHERE tankorId=%u AND tanarId=%u AND beDt<='%s' AND kiDt>='%s'";
	$v = array($B,$tankorId,$tanarId,$B,$B);
	db_query($q, array('debug'=>false,'fv'=>'tankorTanarTorol/delete','modul'=>'naplo_intezmeny','values'=>$v));

	return true;
    }

    function tankorOsztalyHozzarendeles($tankorId, $osztalyIds) {
        // Tankör-osztály hozzárendelés módosítása - az aktuális tanév figyelembevételével (diákok, bontások)
	// !! A tankör nevét nem módosítja !!
        $jelenlegiOsztalyIds = getTankorOsztalyai($tankorId);
	$db = count($_SESSION['alert']);
        $diakOsztalyIds = getTankorOsztalyaiByTanev($tankorId, __TANEV, $SET = array('result' => 'id', 'tagokAlapjan' => true, 'tolDt' => '', 'igDt' => ''));
	if ($db < count($_SESSION['alert'])) $diakOsztalyIds = array(); // Ha nincs tagja az osztálynak, akkor - hibaüzenet mellett - visszaadja a függvény az összes hozzárendelt osztályt...
        $bontasOsztalyIds = getTankorOsztalyaiByBontas($tankorId);
        // diak  vs. új
        $tmp = array_diff($diakOsztalyIds, $osztalyIds);
        if (count($tmp) > 0) $_SESSION['alert'] = 'info:wrong_data:diák:A következő osztályokhoz a tankör hozzá van rendelve diákokon keresztül, ezért bővül az osztályok köre:'.implode(',',$tmp);
        // bontas  vs. új
        $tmp = array_diff($bontasOsztalyIds, $osztalyIds);
        if (count($tmp) > 0) $_SESSION['alert'] = 'info:wrong_data:bontás:A következő osztályokhoz a tankör hozzá van rendelve bontásokon keresztül, ezért bővül az osztályok köre:'.implode(',',$tmp);
        // A helyes osztály lista
        $ujOsztalyIds = array_unique(array_merge($osztalyIds, $bontasOsztalyIds, $diakOsztalyIds));
        // Baj lehet még, ha üres az ujOsztalyIds tömb
        if (count($ujOsztalyIds)==0) return false;
        $lr = db_connect('naplo_intezmeny');
        db_start_trans($lr);
            $ok = true;
            // törlendők
            $tmp = array_diff($jelenlegiOsztalyIds, $ujOsztalyIds);
            if (is_array($tmp) && count($tmp)>0) {
                $q = "DELETE FROM tankorOsztaly WHERE osztalyId IN (".implode(',', array_fill(0, count($tmp), '%u')).") AND tankorId=%u";
                $tmp[] = $tankorId;
                $ok = $ok && db_query($q, array('fv'=>'tankorOsztalyHozzarendeles/delete','modul'=>'naplo_intezmeny','values'=>$tmp));
            }
            // felveendők
            $tmp = array_diff($ujOsztalyIds, $jelenlegiOsztalyIds);
            if (is_array($tmp) && count($tmp)>0) foreach($tmp as $index=>$osztalyId) {
                $q = "INSERT INTO tankorOsztaly (tankorId,osztalyId) VALUES (%u,%u)";
                $ok = $ok && db_query($q, array('fv'=>'tankorOsztalyHozzarendeles/insert','modul'=>'naplo_intezmeny','values'=>array($tankorId,$osztalyId)));
            }
        if ($ok) db_commit($lr); else db_rollback($lr);
        db_close($lr);
        return $ok;
    }

    function tankorSzemeszterHozzarendeles($tankorId, $tankorSzemeszter) {
	// !! A tankör nevét nem módosítja !! (új hozzárendelés esete...)

	// A tankör-szemeszter hozzárendelés módosítása.
	$jelenlegiTsz = $delDisTsz = $ujTsz = array();
	foreach ($tankorSzemeszter as $index => $tszAdat) $ujTsz[$tszAdat['tanev'].'/'.$tszAdat['szemeszter']] = $tszAdat;
	// Jelenlegi hozzárendelések
	$tmp = getTankorSzemeszterei($tankorId);
	$lr = db_connect('naplo_intezmeny');
	db_start_trans($lr); $ok = true;
	foreach ($tmp as $index => $tszAdat) {

	    $tsz = $tszAdat['tanev'].'/'.$tszAdat['szemeszter'];
	    $jelenlegiTsz[$tsz] = $tszAdat;

	    if (!is_array($ujTsz[$tsz])) {
	    // Törölni csak tervezett szemeszterből engedünk...
		if ($tszAdat['statusz'] == 'tervezett') {
		    $q = "DELETE FROM tankorSzemeszter WHERE tankorId=%u AND tanev=%u AND szemeszter=%u";
		    $v = array($tankorId, $tszAdat['tanev'], $tszAdat['szemeszter']);
		    $ok = $ok && db_query($q, array('fv'=>'tankorSzemeszterHozzarendeles/del','modul'=>'naplo_intezmeny','values'=>$v), $lr);
		} else {
		    $delDisTsz[] = $tsz;
		}
	    } else if ($ujTsz[$tsz]['oraszam'] != $tszAdat['oraszam']) {
	    // módosítunk, ha eltér az új óraszám a régitől
		if ($tszAdat['tanev'] == __TANEV) { // A jelenlegi tanévben a bontás óraszámot ellenőrizzük.
		    $TO = getTankorTervezettOraszamok(array($tankorId));
		    $sumBontasOraszam = 0;
		    foreach ($TO[$tankorId]['bontasOraszam'][$tszAdat['szemeszter']] as $idx => $oAdat) $sumBontasOraszam += $oAdat['hetiOraszam'];
		    if ($sumBontasOraszam <= $ujTsz[$tsz]['oraszam']) { $ok = true; }
		    else { $ok = false; $_SESSION['alert'][] = 'message:wrong_data:A bontásokban lekötött összóraszámnál kisebb óraszám nem állítható be.'; }
		} else { $ok = true; } // egyéb évkben hagyjuk módosítani
		if ($ok) {
		    $q = "UPDATE tankorSzemeszter SET oraszam = %f WHERE tankorId=%u AND tanev=%u AND szemeszter=%u";
		    $v = array($ujTsz[$tsz]['oraszam'], $tankorId, $tszAdat['tanev'], $tszAdat['szemeszter']);
		    $ok = $ok && db_query($q, array('fv'=>'tankorSzemeszterHozzarendeles/update','modul'=>'naplo_intezmeny','values'=>$v), $lr);
		}
	    }
	}
	// új hozzárendeléseket korlátozás nélkül fel lehet venni... a tankör neve generált név...
	foreach ($ujTsz as $tsz => $tszAdat) {
	    if (!is_array($jelenlegiTsz[$tsz])) {
		$q = "INSERT INTO tankorSzemeszter (tankorId, tanev, szemeszter, oraszam, tankorNev) VALUES (%u, %u, %u, %f, '%s')";
		$v = array($tankorId, $tszAdat['tanev'], $tszAdat['szemeszter'], $tszAdat['oraszam'], 'tankor-'.$tankorId);
		$ok = $ok && db_query($q, array('fv'=>'tankorSzemeszterHozzarendeles/ins','modul'=>'naplo_intezmeny','values'=>$v), $lr);
	    }
	}
	if (count($delDisTsz) > 0) {
	    $_SESSION['alert'][] = 'info:wrong_data:Nem tervezett tanév esetén nem szüntethető meg a tankör hozzárendelés! ('.implode(', ', $delDisTsz).')';
	}
	if ($ok) db_commit($lr);
	else db_rollback($lr);

	db_close($lr);
	return $ok;
    }

?>
