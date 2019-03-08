<?php

    function ujEsemeny($ADAT) {
	$q = "INSERT INTO esemeny (esemenyKategoria, esemenyRovidnev, esemenyNev, esemenyLeiras, jelentkezesTolDt, jelentkezesIgDt, min, max) 
		VALUES ('%s','%s','%s','%s','%s','%s',%u,%u)";
	$v = array(
	    $ADAT['esemenyKategoria'], $ADAT['esemenyRovidnev'], $ADAT['esemenyNev'], $ADAT['esemenyLeiras'], 
	    $ADAT['jelentkezesTolDt'], $ADAT['jelentkezesIgDt'], $ADAT['min'], $ADAT['max']
	);
	$esemenyId = db_query($q, array('fv'=>'ujEsemeny','modul'=>'naplo','values'=>$v,'result'=>'insert'));
	// Ha tanár veszi fel, akkor őt rögtön rendeljük hozzá!
	if (is_numeric($esemenyId) && __TANAR) {
	    $q = "INSERT INTO esemenyTanar (esemenyId, tanarId) VALUES (%u,%u)";
	    $v = array($esemenyId, __USERTANARID);
	    db_query($q, array('fv'=>'ujEsemenyModositas/insTanar','modul'=>'naplo','values'=>$v));
	}
	return $esemenyId;
    }

    function getEsemenyAdat($esemenyId) {
	$v = array($esemenyId);
	$q = "SELECT * FROM esemeny WHERE esemenyId=%u";
	$ret = db_query($q, array('fv'=>'getEsemenyAdat','modul'=>'naplo','values'=>$v,'result'=>'record'));
	$q = "SELECT osztalyId FROM esemenyOsztaly WHERE esemenyId=%u";
	$ret['osztalyIds'] = db_query($q, array('fv'=>'getEsemenyAdat/osztaly','modul'=>'naplo','values'=>$v,'result'=>'idonly'));
	$q = "SELECT tanarId FROM esemenyTanar WHERE esemenyId=%u";
	$ret['tanarIds'] = db_query($q, array('fv'=>'getEsemenyAdat/tanar','modul'=>'naplo','values'=>$v,'result'=>'idonly'));
	$q = "SELECT esemenyDiak.*, TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS diakNev 
		FROM esemenyDiak LEFT JOIN ".__INTEZMENYDBNEV.".diak USING (diakId) WHERE esemenyId=%u ORDER BY diakNev";
	$ret['diakok'] = db_query($q, array('fv'=>'getEsemenyAdat/diak','modul'=>'naplo','values'=>$v,'result'=>'indexed'));
	$ret['diakIds'] = array();
	if (is_array($ret['diakok'])) foreach ($ret['diakok'] as $dAdat) $ret['diakIds'][] = $dAdat['diakId'];

	return $ret;
    }

    function esemenyModositas($ADAT) {

	$lr = db_connect('naplo');
	db_start_trans($lr);

	// osztály-hozzárendelések törlése
	$q = "DELETE FROM esemenyOsztaly WHERE esemenyId=%u";
	$v = array($ADAT['esemenyId']);
	$ok = db_query($q, array('fv'=>'esemenyModositas/delOsztaly','modul'=>'naplo','values'=>$v), $lr);
	// TODO: diák-hozzárendelés alapján kiegészítendő az osztalyid-k listája!
	if (is_array($ADAT['esemenyOsztaly']) && count($ADAT['esemenyOsztaly'])>0) {
	    // osztály-hozzárendelések felvétele
	    $q = "INSERT INTO esemenyOsztaly (esemenyId, osztalyId) VALUES (".implode('),(', array_fill(0, count($ADAT['esemenyOsztaly']), '%u,%u')).")";
	    $v = array();
	    foreach ($ADAT['esemenyOsztaly'] as $osztalyId) { $v[] = $ADAT['esemenyId']; $v[] = $osztalyId; }
	    $ok = $ok && db_query($q, array('fv'=>'esemenyModositas/insOsztaly','modul'=>'naplo','values'=>$v), $lr);
	}

	// tanár-hozzárendelések törlése
	$q = "DELETE FROM esemenyTanar WHERE esemenyId=%u";
	$v = array($ADAT['esemenyId']);
	$ok = $ok && db_query($q, array('fv'=>'esemenyModositas/delTanar','modul'=>'naplo','values'=>$v), $lr);
	if (is_array($ADAT['esemenyTanar']) && count($ADAT['esemenyTanar'])>0) {
	    // tanár-hozzárendelések felvétele
	    $q = "INSERT INTO esemenyTanar (esemenyId, tanarId) VALUES (".implode('),(', array_fill(0, count($ADAT['esemenyTanar']), '%u,%u')).")";
	    $v = array();
	    foreach ($ADAT['esemenyTanar'] as $tanarId) { $v[] = $ADAT['esemenyId']; $v[] = $tanarId; }
	    $ok = $ok && db_query($q, array('fv'=>'esemenyModositas/insTanar','modul'=>'naplo','values'=>$v), $lr);
	}

	// esemeny alapadatainak módosítása
	$q = "UPDATE esemeny SET esemenyRovidnev='%s', esemenyNev='%s', esemenyKategoria='%s', esemenyLeiras='%s',
		jelentkezesTolDt='%s', jelentkezesIgDt='%s', max=%u, min=%u
		WHERE esemenyId=%u";
	$v = array(
	    $ADAT['esemenyRovidnev'], $ADAT['esemenyNev'], $ADAT['esemenyKategoria'], $ADAT['esemenyLeiras'], 
	    $ADAT['jelentkezesTolDt'], $ADAT['jelentkezesIgDt'], $ADAT['max'], $ADAT['min'],
	    $ADAT['esemenyId'],
	);
	$ok = $ok && db_query($q, array('fv'=>'esemenyModositas/mod','modul'=>'naplo','values'=>$v), $lr);

	if ($ok) db_commit($lr);
	else db_rollback($lr);

	db_close($lr);

	return $ok;


    }

    function getEsemenyLista() {

	$q = "SELECT * FROM esemeny ORDER BY esemenyRovidnev";
	return db_query($q, array('fv'=>'getEsemenyLista','modul'=>'naplo','values'=>array(),'result'=>'indexed'));

    }

    function esemenyTorles($esemenyId) {

	$lr = db_connect('naplo');
	db_start_trans($lr);

	// tanár-hozzárendelések törlése
	$q = "DELETE FROM esemenyTanar WHERE esemenyId=%u";
	$v = array($esemenyId);
	$ok = db_query($q, array('fv'=>'esemenyTorles/delTanar','modul'=>'naplo','values'=>$v), $lr);

	// diák-hozzárendelések törlése
	$q = "DELETE FROM esemenyDiak WHERE esemenyId=%u";
	$v = array($esemenyId);
	$ok = $ok && db_query($q, array('fv'=>'esemenyTorles/delDiak','modul'=>'naplo','values'=>$v), $lr);

	// osztály-hozzárendelések törlése
	$q = "DELETE FROM esemenyOsztaly WHERE esemenyId=%u";
	$v = array($esemenyId);
	$ok = $ok && db_query($q, array('fv'=>'esemenyTorles/delOsztaly','modul'=>'naplo','values'=>$v), $lr);

	// az esemeny törlése
	$q = "DELETE FROM esemeny WHERE esemenyId=%u";
	$v = array($esemenyId);
	$ok = $ok && db_query($q, array('fv'=>'esemenyTorles/delEsemeny','modul'=>'naplo','values'=>$v), $lr);

	if ($ok) db_commit($lr);
	else db_rollback($lr);

	db_close($lr);

	return $ok;

    }

    function getAktualisEsemenyByOsztaly($osztalyIds) {

	$q = "SELECT * FROM esemeny LEFT JOIN esemenyOsztaly USING (esemenyId) WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).")";
	$ret = db_query($q, array('fv'=>'getAktualisEsemenyByOsztaly','modul'=>'naplo','values'=>$osztalyIds, 'result'=>'indexed'));

	for ($i=0; $i<count($ret); $i++) {
	    $q = "SELECT COUNT(*) FROM esemenyDiak WHERE esemenyId=%u";
	    $ret[$i]['letszam'] = db_query($q, array('fv'=>'getAktualisEsemenyByOsztaly/letszam','modul'=>'naplo','values'=>array($ret[$i]['esemenyId']), 'result'=>'value'));
	}

	return $ret;
    }

    function getValasztottEsemenyek($diakId, $SET = array('esemenyIds' => null)) {

	$q = "SELECT esemenyId FROM esemeny LEFT JOIN esemenyDiak USING (esemenyId) WHERE diakId=%u";
	$v = array($diakId);
	if (is_array($SET['esemenyIds'])) {
	    $q .= " AND esemenyId IN (".implode(',', array_fill(0, count($SET['esemenyIds']), '%u')).")";
	    foreach ($SET['esemenyIds'] as $eId) $v[] = $eId;
	}
	return db_query($q, array('fv'=>'getValasztottEsemenyek','modul'=>'naplo','values'=>$v, 'result'=>'idonly'));
    }

    function getJovahagyottEsemenyek($diakId, $SET = array('esemenyIds' => null)) {

	$q = "SELECT esemenyId FROM esemeny LEFT JOIN esemenyDiak USING (esemenyId) WHERE diakId=%u AND jovahagyasDt!='0000-00-00 00:00:00'";
	$v = array($diakId);
	if (is_array($SET['esemenyIds'])) {
	    $q .= " AND esemenyId IN (".implode(',', array_fill(0, count($SET['esemenyIds']), '%u')).")";
	    foreach ($SET['esemenyIds'] as $eId) $v[] = $eId;
	}
	return db_query($q, array('fv'=>'getJovahagyottEsemenyek','modul'=>'naplo','values'=>$v, 'result'=>'idonly'));
    }

    function esemenyJelentkezes($diakId, $esemenyId) {

	$lr = db_connect('naplo');
	db_start_trans($lr);

	// A max lekérdezése
	$q = "SELECT max, COUNT(diakId) AS count FROM esemeny LEFT JOIN esemenyDiak USING (esemenyId) WHERE esemenyId=%u GROUP BY max";
	$v = array($esemenyId);
	$ret = db_query($q, array('fv'=>'esemenyJelentkezes/max,count','modul'=>'naplo','values'=>$v,'result'=>'record'), $lr);

	if ($ret['count'] < $ret['max']) {
	    $q = "INSERT INTO esemenyDiak (diakId, esemenyId, jelentkezesDt, jovahagyasDt) VALUES (%u, %u, NOW(), '0000-00-00 00:00:00')";
	    $v = array($diakId, $esemenyId);
	    $ok = db_query($q, array('fv'=>'esemenyJelentkezes','modul'=>'naplo','values'=>$v), $lr);
	} else {
	     $ok = false;
	    $_SESSION['alert'][] = 'message:wrong_data:Maximális létszám = '.$ret['max'];
	}

	if ($ok) db_commit($lr);
	else db_rollback($lr);

	db_close($lr);

	return $ok;
    }

    function esemenyLeadas($diakId, $esemenyId) {

	$q = "DELETE FROM esemenyDiak WHERE diakId=%u AND esemenyId=%u";
	$v = array($diakId, $esemenyId);
	return db_query($q, array('fv'=>'esemenyLeadas','modul'=>'naplo','values'=>$v));


    }

    function jelentkezesJovahagyas($diakId, $esemenyId) {

	$q = "UPDATE esemenyDiak SET jovahagyasDt=NOW() WHERE diakId=%u AND esemenyId=%u";
	$v = array($diakId, $esemenyId);
	return db_query($q, array('fv'=>'jelentkezesJovahagyas','modul'=>'naplo','values'=>$v));

    }

    function jelentkezesElutasitas($diakId, $esemenyId) {

	$q = "UPDATE esemenyDiak SET jovahagyasDt='0000-00-00 00:00:00' WHERE diakId=%u AND esemenyId=%u";
	$v = array($diakId, $esemenyId);
	return db_query($q, array('fv'=>'jelentkezesElutasitas','modul'=>'naplo','values'=>$v));

    }

?>