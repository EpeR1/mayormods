<?php
/*
    Module: naplo
*/

    function getDolgozat($dolgozatId, $olr='') {

	global $_TANEV;

        if ($olr == '') $lr = db_connect('naplo', array('fv' => 'getDolgozat'));
	else $lr = $olr;

	$v = array($dolgozatId);
	// A dolgozat alapadatai
	$q = "SELECT * FROM dolgozat WHERE dolgozatId=%u";
	$Dolgozat = db_query($q, array('fv' => 'getDolgozat', 'modul' => 'naplo', 'values' => $v, 'result' => 'record'), $lr);
	$Dolgozat['diakIds'] = array();

	// A dolgozat jegyei
	$q = "SELECT * FROM jegy WHERE dolgozatId=%u AND tipus != 0";
	$ret = db_query($q, array('fv' => 'getDolgozat', 'modul' => 'naplo', 'keyfield' => 'tankorId', 'result' => 'multiassoc', 'values' => $v), $lr);
	$Dolgozat['ertekelt'] = (is_array($ret) && count($ret) > 0);
	if (is_array($ret)) foreach ($ret as $tankorId => $tankorJegyek) {
	    for ($j = 0; $j < count($tankorJegyek); $j++) {
		$diakId = $tankorJegyek[$j]['diakId'];
		$tankorDiakJegyek[$tankorId][$diakId][] = $tankorJegyek[$j];
	    }
	}

	// A dolgozat tankörei
	$q = "SELECT DISTINCT tankorId, targyId, tankorNev
		FROM tankorDolgozat LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
		WHERE tanev=".__TANEV." AND dolgozatId=%u";
	$Dolgozat['tankor'] = db_query($q, array('fv' => 'getDolgozat', 'modul' => 'naplo', 'values' => $v, 'result' => 'indexed'), $lr);
	$Dolgozat['tankorIds'] = array();
        for ($d = 0; $d < count($Dolgozat['tankor']); $d++) $Dolgozat['tankorIds'][] = $Dolgozat['tankor'][$d]['tankorId'];
	$Dolgozat['tanarIds'] = getTankorTanaraiByInterval($Dolgozat['tankorIds'], array('tanev' => __TANEV, 'result' => 'csakId'));
	$Dolgozat['targyId'] = $Dolgozat['tankor'][0]['targyId'];
	for ($i = 0; $i < count($Dolgozat['tankor']); $i++) {
	    $tankorId = $Dolgozat['tankor'][$i]['tankorId'];
	    $Dolgozat['tankor'][$i]['diakok'] = getTankorDiakjaiByInterval($tankorId, __TANEV);
	    foreach ($Dolgozat['tankor'][$i]['diakok']['idk'] as $index => $diakId)
		if (!in_array($diakId, $Dolgozat['diakIds'])) $Dolgozat['diakIds'][] = $diakId;
	    $Dolgozat['tankor'][$i]['jegyek'] = $tankorDiakJegyek[$tankorId];
	}
	//$diakTankorIds = getTankorIdsByDiakIds($Dolgozat['diakIds'],array('kovetelmeny'=>array('jegy'))); // miért csak jegy???
	$diakTankorIds = getTankorIdsByDiakIds($Dolgozat['diakIds']);
	$Dolgozat['utkozoDolgozatok'] = getTankorDolgozatok($diakTankorIds, true, date('Y-m-d'), $_TANEV['zarasDt']);
	if ($olr == '') db_close($lr);

	return $Dolgozat;

    }

    function dolgozatTankorHozzarendeles($dolgozatId, $torlendoTankorIds, $ujTankorIds) {


	$lr = db_connect('naplo');
        if (count($torlendoTankorIds) > 0) {
            // ellenőrizzük, hogy van-e jegy hozzá!
            $q = "SELECT COUNT(jegyId) FROM jegy WHERE dolgozatId=%u
                            AND tankorId IN (".implode(',', array_fill(0, count($torlendoTankorIds), '%u')).")";
	    $v = $torlendoTankorIds; array_unshift($v, $dolgozatId);
	    $num = db_query($q, array('fv' => 'dolgozatTankorHozzarendeles', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);
	    if ($num > 0) {
		$_SESSION['alert'][] = 'message:wrong_data:dolgozatTankorHozzarendeles:Tankör hozzárendelés megszüntetése előtt a jegyeket törölni kell!:jegyek száma '.$num;
		db_close($lr);
		return false;
	    }
	    $q = "DELETE FROM tankorDolgozat WHERE dolgozatId=%u
		    AND tankorId IN (".implode(',', array_fill(0, count($torlendoTankorIds), '%u')).")";
	    db_query($q, array('fv' => 'dolgozatTankorHozzarendeles', 'modul' => 'naplo', 'values' => $v), $lr);
        }
        if (($count = count($ujTankorIds)) > 0) {
            foreach ($ujTankorIds as $key => $tankorId) $Val[] = "(%".($key+1)."\$u, %".($count+1)."\$u)";
	    array_push($ujTankorIds, $dolgozatId);
            $q = "INSERT INTO tankorDolgozat (tankorId, dolgozatId) VALUES ".implode(',',$Val);
	    db_query($q, array('fv' => 'dolgozatTankorHozzarendeles', 'values' => $ujTankorIds, 'modul' => 'naplo'), $lr);
        }
	db_close($lr);
	return true;

    }

    function dolgozatJegyekTorlese($dolgozatId, $tankorId) {

	// Törlendő jegyek lekérdezése - logolás céljából
	$q = "SELECT diakId, jegy, dt FROM jegy WHERE dolgozatId=%u AND tankorId=%u";
	$ret = db_query($q, array('fv' => 'dolgozatJegyekTorlese', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($dolgozatId, $tankorId)));
	if (!$ret) return false;

	for ($i = 0; $i < count($ret); $i++) {
	    if (__NAPLOADMIN || (strtotime(_OSZTALYOZO_HATARIDO) <= strtotime($ret[$i]['dt']))) {
		logAction(
		    array(
			'szoveg'=>'Dolgozat jegy törlése: diakId: '.$ret[$i]['diakId'].', tankorId: '.$ret[$i]['tankorId'].', jegy: '.$ret[$i]['jegy'], 
			'table'=>'jegy'
		    )
		);
	    } else {
		$_SESSION['alert'][] = 'message:deadline_expired:'.$ret[$i]['dt'];
	    }
	}
	$q = "DELETE FROM jegy WHERE dolgozatId=%u AND tankorId=%u";
	return db_query($q, array('fv' => 'dolgozatJegyekTorlese', 'modul' => 'naplo', 'values' => array($dolgozatId, $tankorId)));
    }

    function dolgozatTorles($dolgozatId) {

	$q = "DELETE FROM dolgozat WHERE dolgozatId=%u";
        return db_query($q, array('fv' => 'dolgozatTorles', 'modul' => 'naplo', 'values' => array($dolgozatId)));

    }

    function dolgozatModositas($dolgozatId, $dolgozatNev, $tervezettDt) {

	$q = "UPDATE dolgozat SET dolgozatNev='%s', tervezettDt='%s', modositasDt=now() WHERE dolgozatId=%u";
        return db_query($q, array('fv' => 'dolgozatModositas', 'modul' => 'naplo', 'values' => array($dolgozatNev, $tervezettDt, $dolgozatId)));

    }

?>
