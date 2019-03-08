<?php
/*
    Jó az, hogy egész évre nézzük a tankorBlokk ellenőrzéseket?
    A tankorTanarFelvesz függvényben csak az érintett tol-ig határok kozott ellenőriztem... [bb]
*/


    function getTankorExportOraszamByTanev($tanev, $tankorIds = array(), $blokkId = '') {

	$tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

	$v = $WHERE = array(); $whereStr = '';
	if (is_array($tankorIds) && count($tankorIds) > 0) {
	    $WHERE[] = 'tankorId IN ('.implode(',', array_fill(0, count($tankorIds), '%u')).')';
	    $v = $tankorIds;
	}
	if (isset($blokkId) && intval($blokkId) > 0) {
	    $WHERE[] = "blokkId != %u";
	    $v[] = intval($blokkId);
	}
	if (count($WHERE) > 0) $whereStr = "WHERE ".implode(' AND ', $WHERE);

	$return = array();
	// tankörök export óraszáma (az aktuális blokk kivételével)
	$q = "SELECT tankorId, SUM(exportOraszam) AS exportOraszam FROM `%s`.tankorBlokk
		    LEFT JOIN `%s`.blokk USING (blokkId) 
		    $whereStr GROUP BY tankorId";
	array_unshift($v, $tanevDbNev, $tanevDbNev);
	return db_query($q, array('fv' => 'getTankorExportOraszamByTanev', 'modul' => 'naplo', 'result' => 'keyvaluepair', 'keyfield' => 'tankorId', 'values' => $v), $lr);

    }

    function ujTankorBlokk($blokkNev, $exportOraszam, $tankorIds, $tanev='') {

	global $_TANEV;

	if (!is_array($tankorIds) || count($tankorIds)==0 || $blokkNev=='') {
	    $_SESSION['alert'][] = '::egy kötelező paraméter hiányzik!';
	    return false;
	}

	if ($tanev=='') {
	    $tanev = __TANEV;
	    $tanevAdat = $_TANEV;
	} else {
	    $tanevAdat = getTanevAdat($tanev);
	}

	$tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

	if (strtotime($tanevAdat['zarasDt']) < time()) {
	    $_SESSION['alert'][] = 'message:Elmúlt tanévre ne hozzunk létre tankörblokkot!';
	    return false;
	}
	if (time() < strtotime($tanevAdat['kezdesDt']))	{
	    $kezdesDt = $tanevAdat['kezdesDt'];
	    $kezdesDtPattern = "'%s'";
	} else {
	    $kezdesDt = 'CURDATE()';
	    $kezdesDtPattern = '%s';
	}
	$zarasDt  = $tanevAdat['zarasDt'];

	/* Vizsgáljuk meg, hogy létrehozható-e a tankorBlokk 
	    kizáró feltétel, ha egy diák beletartozik több csoportba HAVING count>2
	*/

	$lr = db_connect('naplo', array('fv' => 'ujTankorBlokk'));

	// Ellenőrzés - tankör - diákok
	$q = "SELECT diakId,COUNT(DISTINCT tankorId) AS c FROM ".__INTEZMENYDBNEV.".tankorDiak 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= $kezdesDtPattern)
		GROUP BY diakId HAVING c>1
		ORDER BY tankorId,diakId";
//		AND jelenlet='kötelező'
	/* Ez a függvény nem veszi figyelembe a felmentéseket! */

	$_SESSION['alert'][] = 'info:!!!:ujTankorBlokk() felmentések';

	$v = mayor_array_join($tankorIds, array($zarasDt, $kezdesDt));
	$ret=db_query($q, array('fv' => 'ujTankorBlokk', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	if (count( $ret ) > 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:ujTankorBlokk:Sikertelen. '.count($ret).' db ütköző diákot találtam!: diakId='.$ret[0]['diakId'].'...';
	    db_close($lr);
	    return false;
	}

	// Ellenőrzés - tankör - tanárok
	$q = "SELECT tanarId,COUNT(DISTINCT tankorId) AS c FROM ".__INTEZMENYDBNEV.".tankorTanar 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= $kezdesDtPattern)
		GROUP BY tanarId HAVING c>1
		ORDER BY tankorId,tanarId";
	$v = mayor_array_join($tankorIds, array($zarasDt, $kezdesDt));
	$ret = db_query($q, array('fv' => 'ujTankorBlokk', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	if (count( $ret ) > 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:ujTankorBlokk:Sikertelen. '.count($ret).' db ütköző tanárt találtam!:tanarId='.$ret[0]['tanarId'].'...';
	    db_close($lr);
	    return false;
	}

	// Ellenőrzés - óraszám
	// tankörök export óraszáma (az aktuális blokk kivételével)
	$tankorExportOraszam = getTankorExportOraszamByTanev($tanev, $tankorIds);
	// tankörök óraszáma
	$tankorOraszam = getTankorOraszamByTanev($tanev, $tankorIds);
	foreach ($tankorIds as $index => $tankorId) {
	    if ($tankorOraszam[$tankorId] - $tankorExportOraszam[$tankorId] < $exportOraszam)
		$exportOraszam = $tankorOraszam[$tankorId] - $tankorExportOraszam[$tankorId];
	}
	if ($exportOraszam < 0) $exportOraszam = 0;	
	db_start_trans($lr);

	// Új felvétele
	$q = "INSERT INTO `%s`.`blokk` (`blokkNev`, `exportOraszam`) VALUES ('%s', %f)";
	$v = array($tanevDbNev, $blokkNev, $exportOraszam);

	$blokkId = db_query($q, array('fv' => 'ujTankorBlokk', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v, 'rollback' => true), $lr);
	if ($blokkId === false) { db_close($lr); return false; }

	// Tankörök hozzárendelése
	$Val = array(); $v = array($tanevDbNev);
	for ($i = 0; $i < count($tankorIds); $i++) {
	    $Val[] = "(%u, %u)";
	    array_push($v, $blokkId, $tankorIds[$i]);
	}
	$q = "INSERT INTO `%s`.tankorBlokk (blokkId,tankorId) VALUES ".implode(',', $Val);
	$r = db_query($q, array('fv' => 'ujTankorBlokk', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	if (!$r) { db_close($lr); return false; }

	db_commit($lr);
	db_close($lr);
	return true;

    }

    function tankorBlokkModositas($ADAT) {


	if (!is_array($ADAT['tankorIds']) || count($ADAT['tankorIds'])==0 || $ADAT['blokkNev']=='') {
	    $_SESSION['alert'][] = '::egy kötelező paraméter hiányzik!(tbmod)';
	    return false;
	}

	if ($ADAT['tanev']=='') {
	    $tanev = __TANEV;
	    $tanevAdat = $_TANEV;
	} else {
	    $tanev = $ADAT['tanev'];
	    $tanevAdat = getTanevAdat($tanev);
	}
	
	$tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

	if (strtotime($tanevAdat['zarasDt']) < time()) {
	    $_SESSION['alert'][] = 'message:Elmúlt tanévre ne hozzunk létre tankörblokkot!';
	    return false;
	}
	$blokkId = $ADAT['blokkId'];
	$tankorIds = $ADAT['tankorIds'];

	$zarasDt  = $tanevAdat['zarasDt'];
	if (time() < strtotime($tanevAdat['kezdesDt']))	{
	    $kezdesDt = $tanevAdat['kezdesDt'];
	    $kezdesDtPattern = "'%s'";
	} else {
	    $kezdesDt = 'CURDATE()';
	    $kezdesDtPattern = "%s";
	}

	$lr = db_connect('naplo');

	// Ellenőrizzük a tankör tagokat - azonosak-e tankörönként
	$q = "SELECT diakId,COUNT(DISTINCT tankorId) AS c FROM ".__INTEZMENYDBNEV.".tankorDiak 
		WHERE tankorId IN (".implode(',', array_fill(0, count($ADAT['tankorIds']), '%u')).")
		AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= $kezdesDtPattern)
		GROUP BY diakId HAVING c>1
		ORDER BY tankorId,diakId";

	$v = mayor_array_join($ADAT['tankorIds'], array($zarasDt, $kezdesDt));
	$ret = db_query($q, array('fv' => 'tankorBlokkModositas', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $lr);
	if (count( $ret ) > 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:tankorBlokkModositas:Sikertelen. '.count($ret).' db ütköző diákot találtam!:diakId='.$ret[0]['diakId'].'...';
	    db_close($lr);
	    return false;
	}

	// Ellenőrizzük a tankör tanárokat - azonosak-e tankörönként
	$q = "SELECT tanarId,COUNT(DISTINCT tankorId) AS c FROM ".__INTEZMENYDBNEV.".tankorTanar 
		WHERE tankorId IN (".implode(',', array_fill(0, count($ADAT['tankorIds']), '%u')).")
		AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= $kezdesDtPattern)
		GROUP BY tanarId HAVING c>1
		ORDER BY tankorId,tanarId";
	$v = mayor_array_join($ADAT['tankorIds'], array($zarasDt, $kezdesDt));
	$ret = db_query($q, array('fv' => 'tankorBlokkModositas', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $lr);
	if (count( $ret )>0) {
	    $_SESSION['alert'][] = 'message:wrong_data:tankorBlokkModositas:Sikertelen. '.count($ret).' db ütköző tanárt találtam!:blokkId='.$blokkId.':tanarId='.$ret[0]['tanarId'].'...';
	    db_close($lr);
	    return false;
	}

	// tankörök export óraszáma (az aktuális blokk kivételével)
	$tankorExportOraszam = getTankorExportOraszamByTanev($tanev, $ADAT['tankorIds'], $blokkId);
	// tankörök óraszáma
	$tankorOraszam = getTankorOraszamByTanev($tanev, $ADAT['tankorIds']);
	foreach ($ADAT['tankorIds'] as $index => $tankorId) {
	    if ($tankorOraszam[$tankorId] - $tankorExportOraszam[$tankorId] < $ADAT['exportOraszam']) {
		$_SESSION['alert'][] = 'message:wrong_data:tankorId='.$tankorId.', óraszám='.$tankorOraszam[$tankorId].', export óraszám='.
		    intval($tankorExportOraszam[$tankorId]).', blokk óraszám='.$ADAT['exportOraszam'];
		return false;
	    }
	}

	// Csoportnév és export óraszám módosítása
	$q = "UPDATE `%s`.blokk SET blokkNev='%s',exportOraszam=%f WHERE blokkId=%u";
	$v = array($tanevDbNev, $ADAT['blokkNev'], $ADAT['exportOraszam'], $ADAT['blokkId']);
	db_query($q, array('fv' => 'tankorBlokkModositas', 'modul' => 'naplo', 'values' => $v), $lr);

	// SAFE:
	db_start_trans($lr);

	    // Törlés
	    $q = "DELETE FROM `%s`.tankorBlokk WHERE blokkId=%u";
	    $v = array($tanevDbNev, $blokkId);
	    $r = db_query($q, array('fv' => 'tankorBlokkModositas/Delete', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	    if ($r === false) { db_close($lr); return false; }
	    // Itt nem jó a commit, hisz még félben van a dolog - nem? // else db_commit($lr);

	    // Tankörök hozzárendelése
	    $v = array($tanevDbNev); $Val = array();
	    for ($i = 0; $i < count($tankorIds); $i++) {
		$Val[] = "(%u, %u)";
		array_push($v, $blokkId, $tankorIds[$i]);
	    }
	    if (count($Val) > 0) {
		$q = "INSERT INTO `%s`.tankorBlokk (blokkId,tankorId) VALUES ".implode(',', $Val);
		$r = db_query($q, array('fv' => 'tankorBlokkModositas/Insert', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
	    }

	    if ($r===false) { db_close($lr); return false; }

	db_commit($lr);

	db_close($lr);
	return true;

    }

    function tankorBlokkTorles($ADAT) {

	if ($ADAT['blokkId']=='') {
	    $_SESSION['alert'][] = '::egy kötelező paraméter hiányzik!';
	    return false;
	}

	if ($ADAT['tanev']=='') {
	    $tanev = __TANEV;
	    $tanevAdat = $_TANEV;
	} else {
	    $tanev = $ADAT['tanev'];
	    $tanevAdat = getTanevAdat($tanev);
	}
	
	$tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);

	$blokkId=intval($ADAT['blokkId']);

	$q = "DELETE FROM `%s`.blokk WHERE blokkId=%u";
	$v = array($tanevDbNev, $blokkId);
	$r = db_query($q, array('fv' => 'TankorBlokk|Delete', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback'=>true), $lr);

	return true;

    }

?>
