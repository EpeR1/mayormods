<?php
/*
    Module: naplo
    
    function getHianyzok($osztaly)
*/

    function getHianyzok($ADAT,$SET = array()) {

	$lr = db_connect('naplo', array('fv' => 'getHianyzok'));
	$H = array();
	if ($SET['dt']!='') {
	    $Diakok = getDiakokByOsztaly($ADAT['osztalyId'],array('tolDt'=>$SET['dt'],'igDt'=>$SET['dt']));
	} else {
	    $Diakok = getDiakokByOsztaly($ADAT['osztalyId']);
	}
	$munkatervIds = getMunkatervByOsztalyId($ADAT['osztalyId'], array('result'=>'idonly'));
	$H['névsor'] = array();
	foreach (array('jogviszonyban van','magántanuló','egyéni munkarend','vendégtanuló') as $statusz) {
	    foreach ($Diakok[$statusz] as $diakId) {
		if (!is_array($H['névsdor'][$diakId])) {
		    $H['névsor'][$diakId] = $Diakok[$diakId];
		    // Az aktuális státusz megállapítása
		    $i = 0;
		    // A státuszbejegyzések sora időben visszafele rendezett!!
		    while ($i < count($Diakok[$diakId]['statusz']) && strtotime($Diakok[$diakId]['statusz'][$i]['dt']) > time()) $i++; 
		    $H['névsor'][$diakId]['aktualisStatusz'] = $Diakok[$diakId]['statusz'][$i]['statusz'];
		}
	    }
	}


	foreach ($H['névsor'] as $diakId => $dAdat) {
	    $H['diakIds'][] = $diakId;
	    $H[$diakId] = array();
	}

	if (count($H['névsor']) == 0) return $H;

	// A legmagasabb fegyelmi fokozat lekérdezése tanulónként
	$q = "SELECT `diakId`, MAX(`referenciaDt`) AS `referenciaDt`, MAX(`fokozat`) AS `fokozat`, MAX(`bejegyzes`.`hianyzasDb`) AS `hianyzasDb` 
		FROM `".__TANEVDBNEV."`.`bejegyzes` LEFT JOIN `".__INTEZMENYDBNEV."`.`bejegyzesTipus` USING (`bejegyzesTipusId`)
		WHERE `diakId` IN (".implode(',', array_fill(0, count($H['diakIds']), '%u')).")
		AND `tipus` = 'fegyelmi' AND `bejegyzes`.`hianyzasDb` > 0
		GROUP BY `diakId`";
	$ret = db_query($q, array('fv' => 'getHianyzok/fegyelmi', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $H['diakIds']), $lr);
	if (!is_array($ret)) { if ($olr == '') db_close($lr); return false; }
	foreach ($ret as $key => $val) {
	    $H[ $val['diakId'] ]['fegyelmi'] = array(
		'fokozat' => $val['fokozat'],
		'referenciaDt' => $val['referenciaDt'],
		'hianyzasDb' => $val['hianyzasDb']
	    );
	}
	$q = "SELECT `diakId`, MAX(`referenciaDt`) AS `referenciaDt`, MAX(`fokozat`) AS `fokozat`, MAX(`bejegyzes`.`hianyzasDb`) AS `hianyzasDb` 
		FROM `".__TANEVDBNEV."`.`bejegyzes` LEFT JOIN `".__INTEZMENYDBNEV."`.`bejegyzesTipus` USING (`bejegyzesTipusId`)
		WHERE `diakId` IN (".implode(',', array_fill(0, count($H['diakIds']), '%u')).")
		AND `tipus` = 'fegyelmi'
		GROUP BY `diakId`";
	$ret = db_query($q, array('fv' => 'getHianyzok/fegyelmi', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $H['diakIds']), $lr);
	if (!is_array($ret)) { if ($olr == '') db_close($lr); return false; }
	foreach ($ret as $key => $val) {
	    $H[ $val['diakId'] ]['fegyelmi']['maxFokozat'] = $val['fokozat'];
	}

	$f_where = $where = $v_fw = $v_w = array();
	$v_w = array($ADAT['tolDt'], $ADAT['igDt']);
	// A _LEGKORABBI_IGAZOLHATO_HIANYZAS és a legutóbbi osztályfőnöki óra függvénye
	$dt = legkorabbiIgazolhatoHianyzasVeg($ADAT['osztalyId'], $lr);
	foreach ($H['névsor'] as $diakId => $dAdat) {
	    // Az utolsó lezártnak tekinthető dátum - a beírt hiányzások függvénye!
	    $H[$diakId]['igDt'] = $tDt = getNemIgazolhatoDt($diakId, $munkatervIds, $dt, $lr);
	    // Ha megadott a felhasználó új viszonyítási pontott, akkor úgy vesszük, hogy addig a dátumig már le vannak zárva a hiányzások - legalábbis a fegyelmi szempontjából
	    if ($ADAT['referenciaDt'] != '' && strtotime($ADAT['referenciaDt']) > strtotime($tDt)) $tDt = $ADAT['referenciaDt'];
	    $where[] = "(diakId=%u AND dt<='%s')"; array_push($v_w, $diakId, $tDt);
	    if ($H[$diakId]['fegyelmi']['referenciaDt'] != '') {
		$f_where[] = "(diakId=%u AND '%s'<dt AND dt<='%s')";
		array_push($v_fw, $diakId, $H[$diakId]['fegyelmi']['referenciaDt'], $tDt);
	    } else {
		$f_where[] = "(diakId=%u AND dt<='%s')";
		array_push($v_fw, $diakId, $tDt);
	    }
	}
	    
	$Wnemszamit = defWnemszamit();

	if (count($where) > 0) $where = "AND '%s' <= dt AND dt <= '%s' AND (".implode(' OR ', $where).")";
	if ($f_where != '') $f_where = 'AND ('.implode(' OR ',$f_where).')';
	// Összes hiányzás lekérdezése
        $q = "SELECT diakId,tipus,statusz,count(*) AS db,SUM(perc) AS ido 
		FROM ".__TANEVDBNEV.".hianyzas".$Wnemszamit['join']." 
                    WHERE statusz != 'törölt' 
		    AND diakId IN (".implode(',', array_fill(0, count($H['diakIds']), '%u')).") 
		        AND '%s' <= dt AND dt <= '%s' 
		    ".$Wnemszamit['nemszamit']." 
                    GROUP BY diakId, tipus, statusz";
	$v = $H['diakIds']; $v[] = $ADAT['tolDt']; $v[] = $ADAT['igDt'];
	$ret = db_query($q, array('fv' => 'getHianyzok/összes', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
	if (!is_array($ret)) { if ($olr == '') db_close($lr); }
	foreach ($ret as  $key => $val) {
	    if ($val['tipus'] == 'késés') 
		$H[ $val['diakId'] ]['összes'][ $val['tipus'] ][ $val['statusz'] ] = array('db' => $val['db'], 'ido' => $val['ido']);
            else 
		$H[ $val['diakId'] ]['összes'][ $val['tipus'] ][ $val['statusz'] ] = $val['db'];
	}

	// Lezárt hiányzások lekérdezése
        $q = "SELECT diakId,tipus,statusz,COUNT(*) AS db,SUM(perc) AS ido
                    FROM hianyzas
		    ".$Wnemszamit['join']."
                    WHERE statusz != 'törölt'
		    $where
		    ".$Wnemszamit['nemszamit']."
                    GROUP BY diakId, tipus, statusz";
	$ret = db_query($q, array('fv' => 'getHianyzok/lezárt', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v_w), $lr);
	if (!is_array($ret)) { if ($olr == '') db_close($lr); }
	foreach ($ret as  $key => $val) {
	    if ($val['tipus'] == 'késés') 
		$H[ $val['diakId'] ]['lezárt'][ $val['tipus'] ][ $val['statusz'] ] = array('db' => $val['db'], 'ido' => $val['ido']);
            else 
		$H[ $val['diakId'] ]['lezárt'][ $val['tipus'] ][ $val['statusz'] ] = $val['db'];
	}

	// Lezárt, még nem szankcionált hiányzások lekérdezése
        $q = "SELECT diakId, tipus, statusz, COUNT(*) AS db, SUM(perc) AS ido
                    FROM hianyzas
		    ".$Wnemszamit['join']."
                    WHERE statusz != 'törölt'
		    $f_where
		    ".$Wnemszamit['nemszamit']."
                    GROUP BY diakId, tipus, statusz";

        $ret = db_query($q, array('fv' => 'getHianyzok/lezárt, nem szankcionált', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v_fw), $lr);
	if (!is_array($ret)) { if ($olr == '') db_close($lr); }
	foreach ($ret as  $key => $val) {
	    if ($val['tipus'] == 'késés') 
		$H[ $val['diakId'] ]['fegyelmi'][ $val['tipus'] ][ $val['statusz'] ] = array('db' => $val['db'], 'ido' => $val['ido']);
            else
        	$H[ $val['diakId'] ]['fegyelmi'][ $val['tipus'] ][ $val['statusz'] ] = $val['db'];
	}

	// Tanulónként
	foreach ($H['névsor'] as $diakId => $dAdat) {

	    $_HOZOTT = getDiakHozottHianyzas($diakId);  //hozott hiányzások lekérdezése az alapértelmezett tanévre
	    //$H[$diakId]['összes']['hozott'] = $_HOZOTT['igazolatlan']['db'] + $_HOZOTT['igazolt']['db'];

	    $H[$diakId]['hozott'] = $_HOZOTT; //???

//	    $H[$diakId]['összes']['hiányzás']['igazolt'] += $_HOZOTT['igazolt']['db'];
//	    $H[$diakId]['összes']['hiányzás']['igazolatlan'] += $_HOZOTT['igazolatlan']['db'];
	    
	    /* Egy diák */
	    $H[$diakId]['összes igazolatlan'] = $H[$diakId]['összes']['hiányzás']['igazolatlan'];

	    // 20/2012 EMMI 51. § (10) - igazolt késések is átváltandók
 	    $H[$diakId]['összes igazolt'] +=
		floor($H[$diakId]['összes']['késés']['igazolt']['ido'] / 45);
	    // Az igazolatlanok esetén azt a számítási módot használjuk, amelyik szigorúbb
	    // Előbb a hivatalos, 45 percenkénti átváltás szerint
	    $igazolatlanKesesbol =  floor($H[$diakId]['összes']['késés']['igazolatlan']['ido'] / 45);
	    // majd a késések, felszereléshiányok darabszáma szerinti
	    if (intval(_HANY_KESES_IGAZOLATLAN) != 0)
		$igazolatlanFegyelmi = floor($H[$diakId]['összes']['késés']['igazolatlan']['db'] / intval(_HANY_KESES_IGAZOLATLAN));
	    else
		$igazolatlanFegyelmi = 0;
	    if (intval(_HANY_FSZ_IGAZOLATLAN) != 0)
		$igazolatlanFegyelmi += floor($H[$diakId]['összes']['felszerelés hiány']['igazolatlan'] / intval(_HANY_FSZ_IGAZOLATLAN));
	    if (intval(_HANY_EH_IGAZOLATLAN) != 0) // egyenruha hiány
		$igazolatlanFegyelmi += floor($H[$diakId]['összes']['egyenruha hiány']['igazolatlan'] / intval(_HANY_EH_IGAZOLATLAN));
	    // végül a kettő közül a nagyobbikkal növeljük az összes igazolatlanok számát ??? ezt miért?
	    if ($igazolatlanKesesbol > $igazolatlanFegyelmi) 
		$H[$diakId]['összes igazolatlan'] += $igazolatlanKesesbol;
	    else
		$H[$diakId]['összes igazolatlan'] += $igazolatlanFegyelmi;
/*
	    //if (_KESESI_IDOK_OSSZEADODNAK === true) { // 20/2012 EMMI - mindenképp összeadódnak a késési idők
 		$H[$diakId]['összes igazolatlan'] +=
		    floor($H[$diakId]['összes']['késés']['igazolatlan']['ido'] / 45);
	    //} else {
		if (intval(_HANY_KESES_IGAZOLATLAN) != 0)
		    $H[$diakId]['összes igazolatlan'] +=
			floor($H[$diakId]['összes']['késés']['igazolatlan']['db'] / intval(_HANY_KESES_IGAZOLATLAN));
		if (intval(_HANY_FSZ_IGAZOLATLAN) != 0)
		    $H[$diakId]['összes igazolatlan'] +=
			floor($H[$diakId]['összes']['felszerelés hiány']['igazolatlan'] / intval(_HANY_FSZ_IGAZOLATLAN));
	    //}
*/	    
# Itt ne adjuk ezt hozzá, mert alább a $H[$diakId]['összes igazolatlan']-okat összegezzük - abban meg már benne lesz a hozott! (Issu 59)
#	    $H['összes']['összes igazolatlan'] += floor($_HOZOTT['igazolatlan']['db']);
#	    $H['összes']['összes igazolt'] += floor($_HOZOTT['igazolt']['db']);
	    $H[$diakId]['összes igazolatlan'] += floor($_HOZOTT['igazolatlan']['db']);
	    $H[$diakId]['összes igazolt'] += floor($_HOZOTT['igazolt']['db']);
	    
	    
	    if (_KESESI_IDOK_OSSZEADODNAK === true) {
		$H[$diakId]['összes fegyelmi igazolatlan'] +=
		    floor(((($H[$diakId]['összes']['késés']['igazolatlan']['ido']
			- $H[$diakId]['fegyelmi']['késés']['igazolatlan']['ido']) % 45 )
		    + $H[$diakId]['fegyelmi']['késés']['igazolatlan']['ido']) / 45);
	    }


	    /* Összes Diákra, összesítés */
	    $H['összes']['hiányzás']['igazolt'] += $H[$diakId]['összes']['hiányzás']['igazolt'];
	    $H['összes']['hiányzás']['igazolatlan'] += $H[$diakId]['összes']['hiányzás']['igazolatlan'];

	    $H['összes']['hiányzás']['igazolatlan'] += floor($_HOZOTT['igazolatlan']['db']);
	    $H['összes']['hiányzás']['igazolt'] += floor($_HOZOTT['igazolt']['db']);

	    $H['összes']['késés']['igazolt'] += $H[$diakId]['összes']['késés']['igazolt']['db'];
	    $H['összes']['késés']['igazolatlan'] += $H[$diakId]['összes']['késés']['igazolatlan']['db'];
	    $H['összes']['felszerelés hiány']['igazolatlan'] += $H[$diakId]['összes']['felszerelés hiány']['igazolatlan'];
	    $H['összes']['felmentés']['igazolatlan'] += $H[$diakId]['összes']['felmentés']['igazolatlan'];
	    $H['összes']['egyenruha hiány']['igazolatlan'] += $H[$diakId]['összes']['egyenruha hiány']['igazolatlan'];

	    $H['összes']['összes igazolatlan'] += $H[$diakId]['összes igazolatlan'];

	    $H['összes']['fegyelmi']['hiányzás']['igazolatlan'] += $H[$diakId]['fegyelmi']['hiányzás']['igazolatlan'];
	    $H['összes']['fegyelmi']['késés']['igazolatlan'] += $H[$diakId]['fegyelmi']['késés']['igazolatlan']['db'];
	    $H['összes']['fegyelmi']['felszerelés hiány']['igazolatlan'] += $H[$diakId]['fegyelmi']['felszerelés hiány']['igazolatlan'];
	    $H['összes']['fegyelmi']['egyenruha hiány']['igazolatlan'] += $H[$diakId]['fegyelmi']['egyenruha hiány']['igazolatlan'];

	}

	db_close($lr);

	return $H;
    
    }

?>
