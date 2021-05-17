<?php

    require_once('include/modules/naplo/share/tanar.php');

    function tanmenetTorol($tanmenetId, $SET = array('force' => false)) {
	if (!is_numeric($tanmenetId)) return false;
	$v = array($tanmenetId);
	if ($force !== true) {
	    // Ellenőrizzük, hogy nincs-e tankörhöz rendelve az adott tanmenet
	    $q = "SELECT tankorId FROM tanmenetTankor WHERE tanmenetId=%u";
	    $tankorIds = db_query($q, array('fv' => 'tanmenetTorol/ellenőrzés', 'modul' => 'naplo_intezmeny', 'result'=>'idonly', 'values' => $v));
	    if (is_array($tankorIds) && count($tankorIds) > 0) {
		$_SESSION['alert'][] = 'message:wrong_data:tankörhöz rendelt tanmenet nem törölhető:tankorIds='.implode(', ', $tankorIds);
		return false;
	    }
	}
	$q = "DELETE FROM tanmenet WHERE tanmenetId=%u";
	return db_query($q, array('fv' => 'tanmenetTorol/törlés', 'modul' => 'naplo_intezmeny', 'values' => $v));
    }

    function ujTanmenet($ADAT) {

	$q = "INSERT INTO tanmenet (targyId, evfolyamJel, tanmenetNev, oraszam, tanarId, dt) VALUES (%u, '%s', '%s', %u, %u, CURDATE())";
	$v = array($ADAT['targyId'], $ADAT['evfolyamJel'], $ADAT['tanmenetNev'], $ADAT['oraszam'], $ADAT['tanarId']);
	return db_query($q, array('fv' => 'ujTanmenet', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v));

    }

    function tanmenetTemakorModositas($ADAT) {

	$lr = db_connect('naplo_intezmeny', array('fv' => 'tanmenetTemakorModositas/connect'));
	db_start_trans($lr);

	// Eddigi témakörök törlése
	$q = "DELETE FROM tanmenetTemakor WHERE tanmenetId=%u";
	$v = array($ADAT['tanmenetId']);
	$r = db_query($q, array('fv' => 'tanmenetTemakorModositas/delete', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	if ($r) {

	    // Új témakörök felvétele
	    $v = $VALUE = array();
	    for ($i = 0; $i < count($ADAT['temakor']['oraszam']); $i++) {
		if ($ADAT['temakor']['temakorMegnevezes'][$i] != '' && $ADAT['temakor']['oraszam'][$i] != 0) {
		    $v[] = $ADAT['tanmenetId']; $v[] = $i; $v[] = $ADAT['temakor']['temakorMegnevezes'][$i];
		    $v[] = $ADAT['temakor']['oraszam'][$i];
		    $VALUE[] = "(%u, %u, '%s', %u)";
		}
	    }
	    if (count($VALUE) > 0) { // Ha van egyáltalán beírandó adat
		$q = "INSERT INTO tanmenetTemakor (tanmenetId, sorszam, temakorMegnevezes, oraszam) VALUES ".implode(',', $VALUE);
		$r = db_query($q, array('fv' => 'tanmenetTemakorModositas/insert', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	    }
	    if ($r) db_commit($lr);
	}

	db_close($lr);
	return $r;

    }

    function tankorTanmenetHozzarendeles($ADAT) {
	$lr = db_connect('naplo_intezmeny', array('fv' => 'tankorTanemenetHozzárendeles/connect'));
	db_start_trans($lr);

	$q = "DELETE FROM tanmenetTankor WHERE tankorId=%u AND tanev=%u";
	$v = array($ADAT['tankorId'], $ADAT['tanev']);
	$r = db_query($q, array('fv' => 'tankorTanmenetHozzarendeles/delete', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	if ($r) {
	    if ($ADAT['tanmenetId']!=0) {
		$q = "INSERT INTO tanmenetTankor (tankorId, tanev, tanmenetId) VALUES (%u, %u, %u)";
		$v = array($ADAT['tankorId'], $ADAT['tanev'], $ADAT['tanmenetId']);
		$r = db_query($q, array('fv' => 'tankorTanmenetHozzarendeles/insert', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
		if ($r) db_commit($lr);
	    } else {
		if ($r) db_commit($lr);
	    }
	}	
	db_close($lr);
	return $r;

    }

    function tanmenetDuplikalas($eTanmenetId, $tanarId) {

	$q = "INSERT INTO tanmenet (targyId, evfolyamJel, tanmenetNev, oraszam, tanarId, dt, statusz)
		SELECT targyId, evfolyamJel, tanmenetNev, oraszam, %u AS tanarId, CURDATE() AS dt, 'új' AS statusz FROM tanmenet WHERE tanmenetId=%u";
	$v = array($tanarId, $eTanmenetId);
	$tanmenetId = db_query($q, array('fv' => 'tanmenetDuplikalas/tanmenet', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v));
	$q = "INSERT INTO tanmenetTemakor (tanmenetId, sorszam, oraszam, temakorMegnevezes)
		SELECT %u AS tanmenetId, sorszam, oraszam, temakorMegnevezes FROM tanmenetTemakor WHERE tanmenetId=%u";
	$v = array($tanmenetId, $eTanmenetId);
	db_query($q, array('fv' => 'tanmenetDuplikalas/tanmenetTemakor', 'modul' => 'naplo_intezmeny', 'values' => $v));

	return $tanmenetId;

    }

    function tanmenetAdatModositas($ADAT) {


	// Ha státusz != 'új' || 'elavult', akkor az óraszámnak legalább stimmelnie kellene!
	if ($ADAT['ujStatusz'] != 'új' && $ADAT['ujStatusz'] != 'elavult') {
	    $q = "select tanmenet.oraszam as tervezett, sum(tanmenetTemakor.oraszam) as osszes 
		    from tanmenet left join tanmenetTemakor using(tanmenetId) where tanmenetId=%u GROUP BY tanmenetId";
	    $ret = db_query($q, array('fv' => 'tanmenetAdatModositas', 'modul'=> 'naplo_intezmeny', 'values' => array($ADAT['tanmenetId']), 'result' => 'record'));
	    if ($ADAT['oraszam'] != $ret['osszes']) {
		// A tanmenet státuszát 'új'-ra állítjuk
		$q = "UPDATE tanmenet SET statusz='új' WHERE tanmenetId=%u";
		db_query($q, array('fv' => 'tanmenetAdatModositas', 'modul' => 'naplo_intezmeny', 'values' => array($ADAT['tanmenetId'])));
		
		$_SESSION['alert'][] = 'message:wrong_data:A tervezett óraszám ('.$ADAT['oraszam'].') nem egyenlő az összóraszámmal ('.$ret['osszes'].')';
		return false;
	    }
	}
	$q = "UPDATE tanmenet SET tanmenetNev='%s', oraszam=%u, evfolyamJel='%s', statusz='%s' WHERE tanmenetId=%u";
	$v = array($ADAT['tanmenetNev'], $ADAT['oraszam'], $ADAT['evfolyamJel'], $ADAT['ujStatusz'], $ADAT['tanmenetId']);
	return db_query($q, array('fv' => 'tanmenetAdatModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));

    }

    function getTanmenetek($SET = array('result' => 'assoc')) {

	$result = readVariable($SET['result'], 'enum', 'assoc', array('indexed','assoc'));

	$q = "SELECT * FROM tanmenet ORDER BY targyId, dt DESC";
	return db_query($q, array('fv' => 'getTanmenetek', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield' => 'tanmenetId'));

    }

    function getTanmenetAdat($tanmenetId, $SET = array('tanev' => __TANEV)) {

	if ($SET['tanev']=='') $SET['tanev'] = __TANEV;
	$v = array($tanmenetId,$SET['tanev']);


	$q = "SELECT * FROM tanmenet WHERE tanmenetId=%u";
	$ret = db_query($q, array('fv' => 'getTanmenetAdat', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v));

	$q = "SELECT * FROM tanmenetTemakor WHERE tanmenetId=%u ORDER BY sorszam";
	$ret['temakor'] = db_query($q, array('fv' => 'getTanmenetAdat/témakör', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

	$q = "SELECT * FROM tanmenetTankor WHERE tanmenetId=%u AND tanev=%u";
	$ret['tankor'] = db_query($q, array('fv' => 'getTanmenetAdat/tankör', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	if (is_array($ret['tankor'])) for ($i = 0; $i < count($ret['tankor']); $i++) {
	    $ret['tankor'][$i]['tankorNev'] = getTankorNevById($ret['tankor'][$i]['tankorId'], array('tanev' => $SET['tanev']));
	}

	$q = "SELECT * FROM tanmenetTankor WHERE tanmenetId=%u AND tanev!=%u";
	$ret['tankorNemAktualis'] = db_query($q, array('fv' => 'getTanmenetAdat/tankör/2', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	if (is_array($ret['tankorNemAktualis'])) for ($i = 0; $i < count($ret['tankorNemAktualis']); $i++) {
	    $ret['tankorNemAktualis'][$i]['tankorNev'] = $ret['tankorNemAktualis'][$i]['tanev'].'-'.getTankorNevById($ret['tankorNemAktualis'][$i]['tankorId'], array('tanev' => $ret['tankorNemAktualis'][$i]['tanev']));
	}
	
	$targyAdat = getTargyById($ret['targyId']);
	$ret['targyNev'] = $targyAdat['targyNev'];
	$ret['tanarNev'] = getTanarNevById($ret['tanarId']);

	return $ret;
    }

    function getTanmenetByTankorIds($tankorIds, $SET = array('tanev' => __TANEV, 'jovahagyva' => false)) {

	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);

	$W = array();
	if ($SET['jovahagyva']===true) $W[] = " AND statusz='publikus'";

	$q = "SELECT tankorId, tanmenetId FROM tanmenetTankor LEFT JOIN tanmenet USING (tanmenetId) WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") AND tanev=%u".implode(' ',$W);
	$tankorIds[] = $tanev;
	return db_query($q, array('fv' => 'getTanmenetByTankorIds', 'modul' => 'naplo_intezmeny', 'result' => 'keyvaluepair', 'values' => $tankorIds));

    }

    function getTanmenetByTargyId($targyId, $SET = array('result' => 'indexed')) {

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed', 'assoc'));
	$targyAdat = getTargyById($targyId);

	$q = "SELECT * FROM tanmenet WHERE targyId=%u ORDER BY evfolyamJel,dt DESC";
	$v = array($targyId);
	$ret = db_query($q, array('fv' => 'getTanmenetByTargyId', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield' => 'tanmenetId', 'values' => $v));

	if (is_array($ret)) foreach ($ret as $key => $tAdat) {
	    $ret[$key]['tanarNev'] = getTanarNevById($tAdat['tanarId']);
	    $ret[$key]['targyNev'] = $targyAdat['targyNev'];
	}

	return $ret;
    }

    function getTanmenetByTanarId($tanarId, $SET = array('result' => 'indexed')) {

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed', 'assoc'));

	$q = "SELECT * FROM tanmenet WHERE tanarId=%u ORDER BY targyId,evfolyamJel,dt DESC";
	$v = array($tanarId);
	$ret = db_query($q, array('fv' => 'getTanmenetByTanarId', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield' => 'tanmenetId', 'values' => $v));

	if (is_array($ret)) foreach ($ret as $key => $tAdat) {
	    $targyAdat = getTargyById($tAdat['targyId']);
	    $ret[$key]['targyNev'] = $targyAdat['targyNev'];
	    $ret[$key]['tanarNev'] = getTanarNevById($tAdat['tanarId']);
	}

	return $ret;
    }

?>
