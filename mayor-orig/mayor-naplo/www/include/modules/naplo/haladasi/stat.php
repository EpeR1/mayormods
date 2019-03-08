<?php

/*
    function munkatervTankor($tankorIds) {

	$q = "SELECT DISTINCT munkatervId, tankorId FROM munkatervOsztaly LEFT JOIN ".__INTEZMENYDBNEV.".tankorOsztaly USING (osztalyId) 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
	return db_query($q, array('fv' => 'munkatervTankor', 'modul'=>'naplo', 'result'=>'keyvalues','values'=>$tankorIds));
    }

    function tankorMunkaterv($tankorIds) {

	$q = "SELECT DISTINCT tankorId, munkatervId FROM munkatervOsztaly LEFT JOIN ".__INTEZMENYDBNEV.".tankorOsztaly USING (osztalyId) 
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") ORDER BY tankorId, munkatervId";
	return db_query($q, array('fv' => 'tankorMunkaterv', 'modul'=>'naplo', 'result'=>'keyvalues','values'=>$tankorIds));
    }
*/
    function getTankorStat($tankorIds, $dt = '') {

	global $_TANEV;

	if ($dt == '') $dt = date('Y-m-d');
	$szDb = count($_TANEV['szemeszter']);
	$ret = array();

	// Van-e nem végzős tanuló az adott tankörökben --> a tankör végzős-e
	$ret['vegzos'] = tankorokVegzosekE($tankorIds, __TANEV, array('tagokAlapjan' => true, 'tolDt' => null, 'igDt' => null));

	// tervezett óraszámok lekérdezése
	$q = "SELECT szemeszter, tankorId, tankorNev, oraszam FROM ".__INTEZMENYDBNEV.".tankorSzemeszter
		WHERE tanev=".__TANEV." AND tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		ORDER BY tankorId, szemeszter";
	$ret['tervezett'] = db_query($q, array(
	    'fv' => 'getTankorStat', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
	));
	foreach ($ret['tervezett'] as $tankorId => $tankorAdat) {
	    $ret['tanitasiHetekSzama'][$tankorId] = getTanitasiHetekSzama(array('tankorId'=>$tankorId,'vegzos'=>$ret['vegzos'][$tankorId]));
	    $oraszam = 0;
	    for ($i = 0; $i < count($tankorAdat); $i++) {
		$oraszam += $tankorAdat[$i]['oraszam'];
	    }
	    $ret['tervezett'][$tankorId]['hetiOraszam'] = $oraszam / $szDb;
	    $ret['tervezett'][$tankorId]['evesOraszam'] = $oraszam / $szDb * $ret['tanitasiHetekSzama'][$tankorId];
	}

	// megtartott órák száma
        if (defined('__ORASZAMOT_NOVELO_TIPUSOK')) {
            $oraszamNoveloTipus = explode(',', __ORASZAMOT_NOVELO_TIPUSOK);
        } else {
            $_SESSION['alert'][] = 'info:missing_constant:__ORASZAMOT_NOVELO_TIPUSOK';
            $oraszamNoveloTipus = array('normál', 'normál máskor', 'helyettesítés', 'összevonás');
        }
        $q = "SELECT tankorId, COUNT(oraId) AS oraSzam FROM ora
                    WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
                    AND tipus IN ('".implode("','", array_fill(0, count($oraszamNoveloTipus), '%s'))."')
                    AND dt <= '%s' GROUP BY tankorId";
	$v = mayor_array_join($tankorIds, $oraszamNoveloTipus, array($dt));
	$ret['megtartott'] = db_query($q, array(
	    'fv' => 'getTankorStat', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $v
	));

	if (is_array($tankorIds) && count($tankorIds)>0) {

	    $q = "SELECT tankorId,COUNT(DISTINCT dt, ora) AS oraSzam 
		    FROM (nap LEFT JOIN munkatervOsztaly USING (munkatervId))
		    LEFT JOIN  (
			orarendiOra
			LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorOsztaly USING (tankorId)
		    )
		    ON (((DAYOFWEEK(dt)+5) MOD 7)+1 = orarendiOra.nap)
			AND orarendiOra.het=nap.orarendiHet 
			AND orarendiOra.tolDt<=dt AND orarendiOra.igDt>=dt 
			AND munkatervOsztaly.osztalyId = tankorOsztaly.osztalyId
		    WHERE tanarId IS NOT NULL
		    AND tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    AND dt > '%s'
		    GROUP BY tankorId";
	    $v = mayor_array_join($tankorIds, array($dt));
	    $ret['becsult'] = db_query($q, array(
		    'fv' => 'getTankorStat/becsült', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $v
	    ));

	    // beírt érdemjegyek száma
	    $q = "SELECT tankorId, COUNT(jegy) AS jegyDb FROM jegy WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    AND dt <= '%s' GROUP BY tankorId";
	    $v = $tankorIds; array_push($v, $dt);
	    $ret['jegyekSzama'] = db_query($q, array(
		'fv' => 'getTankorStat/jegyekSzama', 'modul' => 'naplo', 'result' => 'keyvaluepair', 'values' => $v
	    ));
	    // tankörlétszámok...
	    array_push($v, $dt);
	    $q = "SELECT tankorId, COUNT(*) AS db FROM tankorDiak WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    AND beDt <= '%s' AND (kiDt IS NULL OR '%s' <= kiDt) GROUP BY tankorId";
	    $ret['letszam'] = db_query($q, array(
		'fv' => 'getTankorStat/letszam', 'modul' => 'naplo_intezmeny', 'result' => 'keyvaluepair', 'values' => $v
	    ));
	}

	return $ret;
    }

?>
