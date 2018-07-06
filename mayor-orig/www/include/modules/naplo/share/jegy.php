<?php
/*
    Module: naplo
*/

    require_once('include/modules/naplo/share/ora.php');

    function getJegyInfo($jegyId, $tanev = __TANEV, $olr = '') {

   
        if (!isset($tanev)) 
	    if (defined('__TANEV')) $tanev = __TANEV;
    	    else return false;
        
	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);

	if ($olr == '') $lr = db_connect('naplo', array('fv' => 'getJegyInfo'));
	else $lr = $olr;

	$q = "SELECT jegyId, jegy.diakId AS diakId, tankorId, dt, jegy, jegyTipus, tipus, jegy.megjegyzes, oraId, dolgozatId,
			TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) AS diakNev, modositasDt
		    FROM `%s`.jegy LEFT JOIN ".__INTEZMENYDBNEV.".diak USING(diakId)
		    WHERE jegyId=%u";
	$jegy = db_query($q, array('fv' => 'getJegyInfo', 'modul' => 'naplo', 'result' => 'record', 'values' => array($tanevDb, $jegyId)), $lr);
	if (!is_array($jegy)) {
	    $_SESSION['alert'][] = 'message:wrong_data:getJegyek:jegyId='.$jegyId;
	    if ($olr == '') db_close($lr);
	    return false;
	}

	$jegy['tanár'] = getTankorTanaraiByInterval($jegy['tankorId'], array('tanev' => $tanev, 'result' => 'nevsor'), $lr);
	$tanarSzam = count($jegy['tanár']); $jegy['tanár']['idk'] = array();
	for ($i = 0; $i < $tanarSzam; $i++) {
	    if (!in_array($jegy['tanár'][$i]['tanarId'], $jegy['tanár']['idk']))
		$jegy['tanár']['idk'][] = $jegy['tanár'][$i]['tanarId'];
	}
	$jegy['tankör'] = getTankorById($jegy['tankorId'], $tanev, $lr);

	if ($jegy['oraId'] != '') $jegy['oraAdat'] = getOraAdatById($jegy['oraId'], $tanev, $lr);

	if ($jegy['tipus'] > 2 && $jegy['dolgozatId'] != '') {
	
	    // A dolgozat adatainak lekérdezése
	    $q = "SELECT bejelentesDt, tervezettDt, dolgozatNev FROM `%s`.dolgozat WHERE dolgozatId=%u";
	    $jegy['dolgozat'] = db_query($q, array('fv' => 'getJegyInfo', 'modul' => 'naplo', 'result' => 'record', 'values' => array($tanevDb, $jegy['dolgozatId'])), $lr);
	    if (!is_array($jegy['dolgozat']) || count($jegy['dolgozat']) == 0) {
		$_SESSION['alert'][] = 'message:wrong_data:jegyId='.$jegyId.':dolgozatId='.$jegy['dolgozatId'];
		if ($olr == '') db_close($lr);
		return false;
	    }
	
	}
	
	if ($olr == '')  db_close($lr);
    	return $jegy;
    }


    /* 
	A függvényt arra használjuk, hogy van-e eltérés a tárgy átlag és az osztalyzat között
    */
    function getDiakJegyAtlagok($DIAKIDS,$ADAT=array('evfolyam'=>''),$lr='') {
	// tanev adatbázis
	//$q = "SELECT targyId,AVG(jegy) as jegyAtlag FROM jegy LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) WHERE diakId=%u GROUP BY targyId";
	//$r = db_query($q, array('modul'=>'naplo','values'=>array($diakId), 'result'=>'assoc','keyfield'=>'targyId'));
	$q = "SELECT diakId,targyId,AVG(jegy) as jegyAtlag FROM jegy LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) WHERE diakId IN 
	    (".implode(',', array_fill(0, count($DIAKIDS), '%u')).") GROUP BY diakId,targyId";
	$r = db_query($q, array('modul'=>'naplo', 'result'=>'indexed', 'values'=>$DIAKIDS));
	$arraymap = array('diakId','targyId');
	return reindex($r, $arraymap);
    }

?>
