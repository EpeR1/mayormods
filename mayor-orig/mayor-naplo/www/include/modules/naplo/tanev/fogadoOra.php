<?php

    function getKovetkezoFogadoDtk() {
	$q = "SELECT DISTINCT tol, ig FROM ".__TANEVDBNEV.".fogadoOra WHERE ig>NOW() ORDER BY tol";
	$dts = db_query($q, array('fv' => 'getKovetkezoFogadoDt', 'modul' => 'naplo', 'result' => 'indexed'));
	$ret = array('dates' => array(), 'tol' => array(), 'ig' => array());
	if (is_array($dts)) for ($i = 0; $i < count($dts); $i++) {
	    $dt = substr($dts[$i]['tol'],0,10);
	    if (!in_array($dt, $ret['dates'])) $ret['dates'][] = $dt;
	    $ret['tol'][] = $dts[$i]['tol'];
	    $ret['ig'][] = $dts[$i]['ig'];
	}
	return $ret;
    }

    function getTanarFogadoOra($tanarId) {
	$v = array($tanarId);
	$q = "SELECT * FROM ".__TANEVDBNEV.".fogadoOra WHERE tanarId=%u AND ig>=NOW()";
	$ret['adatok'] = db_query($q, array('fv' => 'getTanarFogadoOra', 'modul' => 'naplo', 'result' => 'record', 'values' => $v));
	if (!is_array($ret['adatok']) || count($ret['adatok']) == 0) $ret['adatok'] = array('tanarId' => $tanarId);

	$q = "SELECT * FROM ".__TANEVDBNEV.".fogadoOraJelentkezes WHERE tanarId=%u AND tol>=CURDATE()";
	$ret['jelentkezesek'] = db_query($q, array('fv' => 'getTanarFogadoOra', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tol', 'values' => $v));

	return $ret;
    }

    function getFogadoOsszes() {
	// Hogy ABC szerint legyen inkább...
	$q = "SELECT fogadoOra.* FROM ".__TANEVDBNEV.".fogadoOra LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId) WHERE ig>=NOW() 
	    ORDER BY CONCAT_WS(' ',viseltCsaladinev,viseltUtonev),tol,ig,teremId";
	$ret = db_query($q, array('fv' => 'getFogadoOsszes', 'modul' => 'naplo', 'result' => 'indexed'));
	return $ret;
    }

    function kovetkezoFogadoOraInit($tol, $ig) {
	// Egyszerre mindig csak egy következő fogadó óra lehet - törlés
	$q = "DELETE FROM fogadoOra WHERE tol>=NOW() OR tol>='$tol'";
	$r = db_query($q, array('fv' => 'kovetkezoFogadoFelvetele', 'modul' => 'naplo'));
	// A szülői jelentkezések is törlődnek ekkor
	$q = "DELETE FROM fogadoOraJelentkezes WHERE tol>=NOW() OR tol>='$tol'";
	$r = db_query($q, array('fv' => 'kovetkezoFogadoFelvetele', 'modul' => 'naplo'));
	// minden tanárra beállítjuk a megadott tol-ig értéket
	$q = "INSERT INTO fogadoOra (tanarId,tol,ig)
		SELECT tanarId, '%s','%s' FROM ".__INTEZMENYDBNEV.".tanar
		WHERE beDt<='%s' and (kiDt is null or kiDt >= '%s');";
	$r = db_query($q, array('fv' => 'kovetkezoFogadoFelvetele', 'modul' => 'naplo', 'values' => array($tol, $ig, $ig, $tol)));
    }

    function tanarFogadoOra($tanarId, $tol, $ig, $teremId) {
	if ($teremId == '') $teremId = 'NULL';
	// Ha van bejegyzett fogadóóra, akkor töröljük
	$q = "DELETE FROM fogadoOra WHERE ig>NOW() AND tanarId=%u";
	db_query($q, array('fv' => 'tanarFogadoOra', 'modul' => 'naplo', 'values' => array($tanarId)));
	// Új fogadóóra felvétele
	if (isset($teremId) && $teremId > 0) {
	    $q = "INSERT INTO fogadoOra VALUES (%u, '%s', '%s',  %u)";
	} else {
	    $q = "INSERT INTO fogadoOra VALUES (%u, '%s', '%s',  NULL)";
	}
	$v = array($tanarId, $tol, $ig, $teremId);
	db_query($q, array('fv' => 'tanarFogadoOra', 'modul' => 'naplo', 'values' => $v));

	// A szülői jelentkezések is törlődnek ekkor
	$q = "DELETE FROM fogadoOraJelentkezes WHERE tanarId=%u AND (tol<'%s' OR tol>='%s')";
	$v = array($tanarId, $tol, $ig);
	return db_query($q, array('fv' => 'tanarFogadoOra', 'modul' => 'naplo', 'values' => $v));
	
    }

    function getSzuloJelentkezes($szuloId) {
	$q = "SELECT * FROM ".__TANEVDBNEV.".fogadoOraJelentkezes WHERE szuloId=%u AND tol>=NOW() ORDER BY tol";
	return db_query($q, array('fv' => 'getSzuloJelentkezes', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tanarId', 'values' => array($szuloId)));
    }

    function fogadoOraJelentkezes($szuloId, $M) {

	$lr = db_connect('naplo');

	for ($i = 0; $i < count($M); $i++) {
	    $tanarId = $M[$i]['tanarId'];
	    $datetime = $M[$i]['datetime'];
	    if (isset($datetime)) {
		// Egy már felvett jelentkezésről van-e szó
		$q = "SELECT COUNT(*) FROM ".__TANEVDBNEV.".fogadoOraJelentkezes WHERE tol='%s' AND tanarId=%u AND szuloId=%u";
		$v = array($datetime, $tanarId, $szuloId);
		$db = db_query($q, array('fv' => 'fogadoOraJelentkezes', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);
		if ($db > 0) continue;

		// Van-e már a szülőnek, vagy a tanárnak bejegyzése az adott időpontra
		$q = "SELECT * FROM ".__TANEVDBNEV.".fogadoOraJelentkezes WHERE tol='%s' AND (tanarId=%u OR szuloId=%u) LIMIT 1";
		$v = array($datetime, $tanarId, $szuloId);
		$r = db_query($q, array('fv' => 'fogadoOraJelentkezes', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
		if (!is_array($r) || count($r) == 1) {
		    //$_SESSION['alert'][] = 'message:wrong_data:fogadoOraJelentkezes:foglalt:'."$szuloId/$tanarId/$datetime";
                    $_SESSION['alert'][] = 'message:fogadoora_foglalt:'.str_replace(':','.',$datetime).':foglalt:'."$szuloId/$tanarId/$datetime";
		    continue;
		}
		// Van-e a tanárnak az adtott időpontban fogadóórája
		$q = "SELECT * FROM fogadoOra WHERE tanarId=%u AND tol<='%s' AND '%s'<ig";
		$v = array($tanarId, $datetime, $datetime);
		$r = db_query($q, array('fv' => 'fogadoOraJelentkezes', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
		if (!is_array($r) || count($r) == 0) {
		    //$_SESSION['alert'][] = 'message:wrong_data:fogadoOraJelentkezes:nincs fogadóórája:'."szuloId/$tanarId/$datetime";
                    $_SESSION['alert'][] = 'message:fogadoora_nincs:'.$datetime.":szuloId/$tanarId/$datetime";
		    continue;
		}
	    }

	    db_start_trans($lr);
	    // Töröljük a korrábbi jelentkezést és felvesszük az újat
	    $q = "DELETE FROM fogadoOraJelentkezes WHERE szuloId=%u AND tanarId=%u AND tol>=NOW()";
	    $v = array($szuloId, $tanarId);
	    $r = db_query($q, array('rollback' => true, 'fv' => 'fogadoOraJelentkezes', 'modul' => 'naplo', 'values' => $v), $lr);
	    // Ha csak törlés volt, akkor tovább
	    if (!isset($datetime)) { db_commit($lr); continue; }

	    // Felvesszük az új jelentkezést
	    $q = "INSERT INTO fogadoOraJelentkezes (szuloId, tanarId, tol) VALUES (%u, %u, '%s')";
	    $v = array($szuloId, $tanarId, $datetime);
	    $r = db_query($q, array('rollback' => true, 'fv' => 'fogadoOraJelentkezes', 'modul' => 'naplo', 'values' => $v), $lr);	    
	    db_commit($lr);
	}

	db_close($lr);

    }
	

    function getFogadoOraLista() {

	$q = "SELECT * FROM ".__TANEVDBNEV.".fogadoOra LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId) WHERE ig>=NOW()";
	$ret['adatok'] = db_query($q, array('fv' => 'getFogadoOraLista', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tanarId'));

	$q = "SELECT * FROM ".__TANEVDBNEV.".fogadoOraJelentkezes WHERE tol>=CURDATE() ORDER BY tanarId,tol";
	$ret['jelentkezesek'] = db_query($q, array('fv' => 'getFogadoOraLista', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'tanarId'));

	return $ret;

    }

?>
