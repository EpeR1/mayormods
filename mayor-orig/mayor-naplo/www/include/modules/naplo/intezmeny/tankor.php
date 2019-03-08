<?php

    function ujTankor($ADAT) {


	$return = false;
	$lr = db_connect('naplo_intezmeny', array('fv' => 'ujTankor'));
	if (!$lr) return false;

	/* pre-check variables */
	//...
	/* pre-check */
	if (isset($ADAT['tankorId']) && $ADAT['tankorId']!='') {
	    $return = $tankorId = $ADAT['tankorId'];
	    $_tankorCn = $ADAT['tankorCn'];
	    $q = "UPDATE tankor SET felveheto=%u, min=%u, max=%u, kovetelmeny='%s', tankorCn='%s' WHERE tankorId=%u";
	    $v = array($ADAT['felveheto'], $ADAT['min'], $ADAT['max'], $ADAT['kovetelmeny'],$_tankorCn,$tankorId);
	    db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => $v));
	    $tanarFelvesz = false;
	} else {
	    $q = "INSERT INTO tankor (targyId,felveheto,min,max,kovetelmeny) VALUES (%u, '%s', %u, %u,'%s')"; 
	    $v = array($ADAT['targyId'], $ADAT['felveheto'], $ADAT['min'], $ADAT['max'], $ADAT['kovetelmeny']);
	    $return = $tankorId = db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
	    $tanarFelvesz = true;
	}
	/* tankorTipus rev 1261++ -- 1294 */
	if (isset($ADAT['tankorTipus']) && !is_null($ADAT['tankorTipus'])) {
		$q = "UPDATE tankor SET tankorTipus='%s' WHERE tankorId=%u";
		$v = array($ADAT['tankorTipus'], $tankorId);
		db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => $v));
	}
	/* tankorTipus rev 1294++ */
	if (isset($ADAT['tankorTipusId']) && !is_null($ADAT['tankorTipusId'])) {
		$q = "UPDATE tankor SET tankorTipusId='%s' WHERE tankorId=%u";
		$v = array($ADAT['tankorTipusId'], $tankorId);
		db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => $v));
		$q = "SELECT jelleg FROM tankorTipus WHERE tankorTipusId=%u";
		$v = array($ADAT['tankorTipusId']);		
		$tankorTipusJelleg = db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'result'=>'value','values' => $v));
	}

	/* TankörCsoport min/max - a tankörcsoport minden tankörében átállítjuk ezeket */
	$q = "UPDATE ".__INTEZMENYDBNEV.".tankor SET min=%u, max=%u WHERE tankorId IN (
		SELECT DISTINCT tankorId FROM tankorCsoport WHERE csoportId IN (
		    SELECT csoportId FROM tankorCsoport WHERE tankorId=%u
		)
	    )";
	$v = array($ADAT['min'], $ADAT['max'], $tankorId);
	db_query($q, array('fv' => 'ujTankor/minMax', 'modul' => 'naplo', 'values' => $v));

	    //--
	    $IOSZTALY = getTankorOsztalyai($tankorId, array('result' => 'id'), $lr); //TAGOK ALAPJÁN???
	    for ($i = 0; $i < count($ADAT['osztalyok']); $i++) {
		$q = "REPLACE INTO tankorOsztaly (tankorId, osztalyId) VALUES (%u, %u)";
		$v = array($tankorId, $ADAT['osztalyok'][$i]);
		db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	    }
	
	    /* */
	    $TOSZTALY = getTankorOsztalyaiByTanev($tankorId, $ADAT['tanev'], array('result' => 'id', 'tagokAlapjan' => true), $lr);

	    /* FIGYELEM! EZ veszélyes művelet! */
	    if (is_array($IOSZTALY) && is_array($ADAT['osztalyok'])) $DEL_OSZTALY = array_diff($IOSZTALY,$ADAT['osztalyok']);
	    if (($_ERR = array_intersect($DEL_OSZTALY,$TOSZTALY))) {
		$_SESSION['alert'][] = 'info:tankorOsztalyNemTorolheto:'.implode('-',$_ERR);
		for ($k=0; $k<count($_ERR); $k++) {
		    $ADAT['osztalyok'][] = $_ERR[$k];
		}
	    }
	    $DEL_OSZTALY = array_diff($DEL_OSZTALY,$TOSZTALY);

	    if (is_array($DEL_OSZTALY) && count($DEL_OSZTALY)>0) {
		$q = "DELETE FROM tankorOsztaly WHERE osztalyId IN (".implode(',', array_fill(0, count($DEL_OSZTALY), '%u')).") AND tankorId=%u";
		$v = mayor_array_join($DEL_OSZTALY, array($tankorId));
		db_query($q, array('fv' => 'ujTankor/del-oszt', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	    }

	/* create name */
	// osztalyId alapján évfolyam, osztály jelek lekérdezése
	// abból összeállítás
	// getOsztalyAdat helyett

	$TARGYADAT = getTargyById($ADAT['targyId'],$lr);
	$kdt = '3000-01-01';
	$vdt = '1970-01-01';
	if (is_array($ADAT['szemeszterek'])) 
	for ($j = 0; $j < count($ADAT['szemeszterek']); $j++) {
	    $nev = '';
	    $szid = $ADAT['szemeszterek'][$j];
	    $_SZ = getSzemeszterek(array('filter' => array("szemeszterId=$szid")));
	    if ($_SZ[0]['kezdesDt'] < $kdt) $kdt = $_SZ[0]['kezdesDt'];
	    if ($_SZ[0]['zarasDt'] > $vdt) $vdt = $_SZ[0]['zarasDt'];
	    $_tanev = $_SZ[0]['tanev'];
	    $_szemeszter = $_SZ[0]['szemeszter'];
	    $_oraszam = $ADAT['SZ'.$szid];
	    if ($tankorTipusJelleg=='osztályfüggetlen') {
		$nev = "Isk.";
	    } else {
	      $OSZTALYOK = getOsztalyok($_tanev);
	      if ($OSZTALYOK !== false && is_array($OSZTALYOK) && is_array($ADAT['osztalyok'])) {	
		$nev = '';
		$TMP = array();
		for($i = 0; $i < count($OSZTALYOK); $i++) {
                    // Ha évenként változik az osztály jele, akkor jobb, ha nem generáljuk, hanem a lekérdezett adatokat használjuk!
                    // $_oj = genOsztalyJel($_tanev, $OSZTALYOK[$i]);
		    $_oj = $OSZTALYOK[$i]['osztalyJel'];
		    if ($_oj!==false && !is_null($_oj)) {
			list($e,$o) = explode('.',$_oj);
			if (in_array($OSZTALYOK[$i]['osztalyId'], $ADAT['osztalyok'])) $TMP[$e][]= $o;
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
		    $_SESSION['alert'][] = '::Minden osztály elballagott';
		}
	      } else {
		$nev = false; // adott szemeszterbe nem jár osztály
		$_SESSION['alert'][] = '::Az adott szemeszterbe nem jár osztály';
	      }
	    }
	    if ($nev !== false) {
		$nev .= ' '.$TARGYADAT['targyNev'];
		$nev .= ' ';

		$q = "SELECT tankorJel FROM tankor LEFT JOIN tankorTipus USING (tankorTipusId) WHERE tankorId=%u";
		$tankorJel = db_query($q, array('fv' => 'genTankorNev', 'modul' => 'naplo_intezmeny', 'result'=>'value', 'values' => array($tankorId), 'debug'=>false), $lr);
		if ($tankorJel!='') $nev .= $tankorJel.' '.$ADAT['tipus'];
		else $nev .= $ADAT['tipus'];
		$q = "REPLACE INTO tankorSzemeszter (tankorId,tanev,szemeszter,oraszam,tankorNev) VALUES (%u, %u, %u, %f, '%s')";
		if ($ADAT['tanev'] < __TANEV || $ADAT['tankorNevMegorzes']===true) { // a neve már ne változzon, és az óraszáma?
		    $q1 = "SELECT tankorNev FROM tankorSzemeszter WHERE tankorId=%u AND tanev=%u AND szemeszter=%u";
		    // OVERWRITE!!!!
		    $orignev = db_query($q1, array('fv' => 'ujTankor', 'result'=>'value', 'modul' => 'naplo_intezmeny', 'values' => array($tankorId,$_tanev,$_szemeszter), 'debug'=>false), $lr);
		    if ($orignev!='' && $orignev!='Array') $nev = $orignev; // csúnya bugfix
		}
		$v = array($tankorId, $_tanev, $_szemeszter, $_oraszam, $nev);
		if ($nev!='') db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => $v, 'debug'=>false), $lr);
		// delete!!!!????
	    }
	    	    
	}
	if ($tanarFelvesz && $ADAT['tanarId']!='') {
	    
		$q = "INSERT INTO tankorTanar (tankorId,tanarId,beDt,kiDt) VALUES (%u, %u, '%s', '%s')"; 
		$v = array($tankorId, $ADAT['tanarId'], $kdt, $vdt);
		$r = db_query($q, array('fv' => 'ujTankor', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	    
	}

	// =======================================================
	
	db_close($lr);

	return $return;
    }

    function tankorTorol($tankorId) {
    
	$q = "DELETE FROM tankor WHERE tankorId=%u";
	return db_query($q, array('fv' => 'tankorTorol', 'modul' => 'naplo_intezmeny', 'values' => array($tankorId)));
	
    }

    function tankorTargyModositas($ADAT) {
    /**
     * Elvárt paraméterek: $ADAT['tankorId'], $ADAT['ujTargyId'], $ADAT['targyId'] // az eredeti
    **/
	$lr = db_connect('naplo_intezmeny');
	if (!$lr) return false;
	db_start_trans($lr);

	// A régi és új tárgynév lekérdezése
	$q = "SELECT targyId, targyNev FROM targy WHERE targyId IN (%u, %u)";
	$v = array($ADAT['ujTargyId'], $ADAT['targyId']);
	$ret = db_query($q, array('fv'=>'tankorTargyModositas/targyNev','values'=>$v, 'result'=>'keyvaluepair'), $lr);
	// a tárgynév cserje a tankorSzemeszter táblában
	$q = "UPDATE tankorSzemeszter SET tankorNev=REPLACE(tankorNev,'%s','%s') WHERE tankorId=%u";
	$v = array($ret[$ADAT['targyId']], $ret[$ADAT['ujTargyId']], $ADAT['tankorId']);
	$r = db_query($q, array('fv'=>'tankorTargyModositas/updateTargyNev','values'=>$v), $lr);
	if (!$r) { db_rollback($lr, 'tankorTargyModositas'); db_close($lr); return false; }
	// A targyId módosítása
	$q = "UPDATE tankor SET targyId=%u WHERE tankorId=%u";
	$v = array($ADAT['ujTargyId'], $ADAT['tankorId']);
	$r = db_query($q, array('fv'=>'tankorTargyModositas/updateTargyId','values'=>$v), $lr);
	if (!$r) { db_rollback($lr, 'tankorTargyModositas'); db_close($lr); return false; }
	$nev = setTankorNev($ADAT['tankorId'], $tankorNevExtra=null, $lr);
	if (!$nev) { db_rollback($lr, 'tankorTargyModositas'); db_close($lr); return false; }

	db_commit($lr);
	db_close($lr);
	return true;

    }

?>
