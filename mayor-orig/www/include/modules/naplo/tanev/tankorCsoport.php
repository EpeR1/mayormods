<?php

    function getTankorCsoportByTankorIds($tankorIds) {

            $q = "SELECT csoportId,csoportNev,tankorId FROM csoport LEFT JOIN tankorCsoport USING (csoportId)
                    WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
            return db_query($q, array(
		'fv' => 'tankorCsoport', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'csoportId', 'values' => $tankorIds
	    ));

    }

    function ujTankorCsoport($csoportNev, $tankorIds) {

	global $_TANEV;
	$dt = (time() <= strtotime($_TANEV['kezdesDt'])) ? "'".$_TANEV['kezdesDt']."'" : 'CURDATE()';

	$lr = db_connect('naplo', array('fv' => 'ujTankorCsoport'));

	// Ellenőrizzük, hogy a megadott tankörök még nem foglaltak
	$q = "SELECT tankorId FROM ".__TANEVDBNEV.".tankorCsoport
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") LIMIT 1";
	$ret = db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $tankorIds), $lr);
	if (count($ret) > 0) {
	    $_SESSION['alert'][] = 'message:utkozes:ujTankorCsoport:tankör ütközés';
	    db_close($lr);
	    return false;
	}
	
	// Ellenőrizzük a tankör tagokat - azonosak-e tankörönként
	$q = "SELECT DISTINCT tankorId,diakId FROM ".__INTEZMENYDBNEV.".tankorDiak 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= '%s')
		ORDER BY tankorId,diakId";
	$v = mayor_array_join($tankorIds, array($dt, $dt));
	$ret = db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
	$tDiakok = array();
	foreach ($tankorIds as $tankorId) $tDiakok[$tankorId] = array(); // különben az üres tankör nem jelennemeg az ellenőrzéskor!
	for ($i = 0; $i < count($ret); $i++) {
	    $tDiakok[$ret[$i]['tankorId']][] = $ret[$i]['diakId'];
	}
	foreach ($tDiakok as $tankorId => $diakIds) {
	    if (is_array($elsoDiakIds)) {
		if ($elsoDiakIds != $diakIds) {
		    $_SESSION['alert'][] = 'message:wrong_data:ujTankorCsoport:tankör tagok nem azonosak:(tankorId='.$tankorId.')';
		    db_close($lr);
		    return false;
		}
	    } else {
		$elsoDiakIds = $diakIds;
	    }
	}

	// Új csoport felvétele
	$q = "INSERT INTO ".__TANEVDBNEV.".csoport (csoportNev) VALUES ('%s')";
	$csoportId = db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'result' => 'insert', 'values' => array($csoportNev)), $lr);
	if ($csoportId === false) { db_close($lr); return false; }

	// Tankörök hozzárendelése
	$v = $Val = array();
	for ($i = 0; $i < count($tankorIds); $i++) {
	    $Val[] = "(%u, %u)";
	    array_push($v, $csoportId, $tankorIds[$i]);
	}
	$q = "INSERT INTO ".__TANEVDBNEV.".tankorCsoport (csoportId,tankorId) VALUES ".implode(',', $Val);
	db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'values' => $v), $lr);

	_setMinMax($csoportId,$lr);

	db_close($lr);
	return true;

    }

    function tankorCsoportModositas($csoportId, $csoportNev, $tankorIds) {

	global $_TANEV;
	$dt = (time() <= strtotime($_TANEV['kezdesDt'])) ? "'".$_TANEV['kezdesDt']."'" : 'CURDATE()';

	if (count($tankorIds) == 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:tankorCsoportModositas/#0:nincs tankör';
	    return false;
	}

	$lr = db_connect('naplo');

	// Ellenőrizzük, hogy a megadott tankörök még nem foglaltak
	$q = "SELECT tankorId FROM ".__TANEVDBNEV.".tankorCsoport
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") AND csoportId != %u LIMIT 1";
	$v = mayor_array_join($tankorIds, array($csoportId));
	$ret = db_query($q, array('fv' => 'tankorCsoportModositas/#1', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
	if (count($ret) > 0) {
	    $_SESSION['alert'][] = 'message:utkozes:tankorCsoportModositas/#2:tankör ütközés';
	    db_close($lr);
	    return false;
	}

	// Ellenőrizzük a tankör tagokat - azonosak-e tankörönként
	$q = "SELECT tankorId,diakId FROM ".__INTEZMENYDBNEV.".tankorDiak 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= '%s' )
		ORDER BY tankorId,diakId";
	$v = mayor_array_join($tankorIds, array($dt, $dt));
	$ret = db_query($q, array('fv' => 'tankorCsoportModositas/#3', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $lr);
	$tDiakok = array();
	for ($i = 0; $i < count($ret); $i++) $tDiakok[$ret[$i]['tankorId']][] = $ret[$i]['diakId'];
	foreach ($tankorIds as $index => $tankorId) {
	    $diakIds = $tDiakok[$tankorId];
	    if (is_array($elsoDiakIds)) {
		if ($elsoDiakIds != $diakIds) {
		    $_SESSION['alert'][] = 'message:wrong_data:tankorCsoportModositas/#4:tankör tagok nem azonosak:(tankorId='.$tankorId.')';
		    db_close($lr);
		    return false;
		}
	    } else {
		$elsoDiakIds = $diakIds;
	    }
	}

	// Csoportnév módosítása
	$q = "UPDATE ".__TANEVDBNEV.".csoport SET csoportNev = '%s' WHERE csoportId = %u";
	$v = array($csoportNev, $csoportId);
	db_query($q, array('fv' => 'tankorCsoportModositas', 'modul' => 'naplo', 'values' => $v));
	// Régi csoporthozzárendelések törlése
	$q = "DELETE FROM ".__TANEVDBNEV.".tankorCsoport WHERE csoportId = %u";
	$v = array($csoportId);
	db_query($q, array('fv' => 'tankorCsoportModositas', 'modul' => 'naplo', 'values' => $v));
	// Tankörök hozzárendelése
	$v = $Val = array();
	for ($i = 0; $i < count($tankorIds); $i++) {
	    $Val[] = "(%u, %u)";
	    array_push($v, $csoportId, $tankorIds[$i]);
	}
	$q = "INSERT INTO ".__TANEVDBNEV.".tankorCsoport (csoportId,tankorId) VALUES ".implode(',', $Val);
	db_query($q, array('fv' => 'tankorCsoportModositas/#5', 'modul' => 'naplo', 'values' => $v));

	_setMinMax($csoportId,$lr);

	db_close($lr);
	return true;

    }

    function tankorCsoportTorles($csoportId, $tanev = __TANEV) {
	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	$q = "DELETE FROM `%s`.csoport WHERE csoportId=%u";
	$v = array($tanevDb, $csoportId);
	db_query($q, array('fv' => 'tankorCsoportTorles', 'modul' => 'naplo', 'values' => $v));
    }

    function _setMinMax($csoportId,$lr) {

	// Tankörcsoport minimum, maximum beállítás - legbővebb halmaz
	$v = array($csoportId);
	$q = ("SET @min= (SELECT MIN(min) FROM ".__INTEZMENYDBNEV.".tankor WHERE tankorId IN (SELECT DISTINCT tankorId FROM tankorCsoport WHERE csoportId=%u))");
	db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'values' => $v), $lr);
	$q = ("SET @max= (SELECT MAX(max) FROM ".__INTEZMENYDBNEV.".tankor WHERE tankorId IN (SELECT DISTINCT tankorId FROM tankorCsoport WHERE csoportId=%u))");
	db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'values' => $v), $lr);
	$q = "UPDATE ".__INTEZMENYDBNEV.".tankor SET min=@min, max=@max WHERE tankorId IN (SELECT DISTINCT tankorId FROM tankorCsoport WHERE csoportId=%u)";
	db_query($q, array('fv' => 'ujTankorCsoport', 'modul' => 'naplo', 'values' => $v), $lr);

    }

?>
