<?php

    function oraFelvetele($dt, $ora, $tanarId, $tankorId, $teremId = 'NULL', $tipus = 'normál', $eredet = 'plusz', $kit = 'NULL') {
    
	if (!isset($teremId) || $teremId == '') { $teremId = 'NULL'; $tStr = '%s'; } 
	else { $tStr = '%u'; }
	if (!isset($kit) || $kit == '') { $kit = 'NULL'; $kStr = '%s'; }
	else { $kStr = '%u'; }

	// ------------------------------------
	// ITT NEM ellenőrizzük a tanár terhelését!
	// ------------------------------------
	$q = "INSERT INTO ora (dt,ora,ki,kit,tankorId,teremId,tipus,eredet,modositasDt)
		VALUES ('%s', %u, %u, $kStr, %u, $tStr, '%s', '%s', NOW())";
	$v = array($dt, $ora, $tanarId, $kit, $tankorId, $teremId, $tipus, $eredet);
	return db_query($q, array('fv' => 'oraFelvetele', 'modul' => 'naplo', 'values' => $v), $lr);
	
    }
    
    function getSzabadTankorok($dt, $ora) {

	// Összes tankör
	$ret = $osszesTankorIds = getTankorByTanev($tanev = __TANEV, array('result' => 'idonly'));
	// Az adott időpontban foglalt tankörök
	$q = "SELECT tankorId FROM ora WHERE dt='%s' AND ora=%u AND tipus IN ('normál','normál máskor','helyettesítés','felügyelet','összevonás')";
	$v = array($dt, $ora);
	$tankorIds = db_query($q, array('fv' => 'getSzabadTankorok', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v));
	if (is_array($tankorIds) && count($tankorIds) > 0) {
	    // A tankörök tagjai
	    $q = "SELECT DISTINCT diakId FROM ".__INTEZMENYDBNEV.".tankorDiak 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") 
		AND (kiDt>='%s' OR kiDt is null) AND beDt<='%s' ORDER BY diakId";
	    $v = mayor_array_join($tankorIds, array($dt, $dt));
	    $diakIds = db_query($q, array('fv' => 'getSzabadTankorok', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v));
	    // A foglalt diákok tankörei
	    $foglaltTankorIds = getTankorIdsByDiakIds($diakIds, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt, 'felmentettekkel'=>false));
	    if (!is_array($foglaltTankorIds)) $foglaltTankorIds = $tankorIds;
	    $ret = array_diff($osszesTankorIds, $foglaltTankorIds);
	}

	/* és vegyük hozzá a szabadon felvehető tanköröket, hm? */
	$pluszNemKotelezoTankorok = getTankorByTanev($tanev, array('result'=>'idonly','jelenlet'=>'nem kötelező' ));
	$ret = mayor_array_join($ret,$pluszNemKotelezoTankorok);
	if (is_array($ret) && count($ret)>0) {
	    $q = "SELECT DISTINCT tankor.tankorId,tankorNev
                     FROM ".__INTEZMENYDBNEV.".tankor LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		    WHERE tanev=".__TANEV." AND tankorId IN (".implode(',', array_fill(0, count($ret), '%u')).") ORDER BY tankorNev";
	    return db_query($q, array('fv' => 'getSzabadTankorok', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $ret));
	} else {
	    return $ret;
	}

    }

?>
