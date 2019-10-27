<?php

    require_once('include/modules/naplo/share/diak.php');

//    $__tankorOrder = "LPAD(  REPLACE(substring_index(substring_index(tankorNev,'-',1),'.',1),'Ny','')  ,2,'0'), tankorNev";
    $__tankorOrder = __createTankorOrder();

    function __createTankorOrder() {
	    $tmp = "substring_index(substring_index(tankorNev,'-',1),'.',1)";
	    foreach(array('AJTP','AJKP','Kny','Ny','N') as $elotag) {
		$tmp = "REPLACE($tmp,'$elotag','')";
	    }
	    return "LPAD($tmp,2,'0'), tankorNev";
    }

    function _isempty($val) {
	return ($val!=='');
    }

    function checkTankorInTanev($tankorId, $tanev, $olr = '') {
	$q = "SELECT tanev,szemeszter FROM tankorSzemeszter WHERE tanev=%u and tankorId=%u";
	$r = db_query(
	    $q, array('fv' => 'checkTankorInTanev', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanev, $tankorId)), $olr
	);
	return (count($r) != 0);
    }

    function getTankorAdat($tankorId, $tanev = __TANEV, $olr = null) {
	$q = "SELECT * FROM tankor 
			LEFT JOIN tankorSzemeszter USING (tankorId)
			LEFT JOIN tankorTipus USING (tankorTipusId)
		WHERE tankor.tankorId=%u AND tanev=%u";
	return db_query($q, 
	    array('fv' => 'getTankorAdat', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => array($tankorId, $tanev)), $olr
	);
    }

    function getTankorAdatByIds($tankorIds, $SET = array('tanev' => __TANEV, 'dt' => '')) {

	if (!is_array($tankorIds)) return false;
	if (in_array('',$tankorIds)==true) {
	    // ez előállhat akkor is, ha valamiért az órarendben NULL tankorId van (speckó óra!)
	    // $_SESSION['alert'][] = 'message:invalid_array_value_exception:(getTankorAdatByIds:tankorIds:contains empty string)';
	    // clean array
	    $tankorIds = array_filter(array_unique($tankorIds),'_isempty');
	}
	$tanev = $SET['tanev'];
	// Ha valid a tanev... ??? Aktív???
	if ($SET['dt']!='') {
	    $dt = $SET['dt'];
	    $SZ = getSzemeszterByDt($dt);
	    if (is_array($SZ) && $SZ['tanev'] != '') {
		$tanev = $SZ['tanev'];
		$felev = $SZ['szemeszter'];
	    } else { // a referencia dátum kívül esik a tanéven, legyen a default
		$tanev = __TANEV;
		$felev = 1;
	    }
	    $q = "SELECT * FROM tankor 
			LEFT JOIN tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
		WHERE tankor.tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") AND tanev=%u AND szemeszter=%u";

	    array_push($tankorIds, $tanev, $felev);
	    return db_query($q, array(
		'fv' => 'getTankorAdatByIds', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
	    ));
	} elseif ($tanev!='') {
	    $q = "SELECT * FROM tankor 
			LEFT JOIN tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
		WHERE tankor.tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") AND tanev=%u";
	    array_push($tankorIds, $tanev);
	    return db_query($q, array(
		'fv' => 'getTankorAdatByIds', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
	    ));
	}
	return false;
    }

    function getTankorTargyId($tankorId) {
	$q = "SELECT targyId FROM tankor WHERE tankor.tankorId=%u";
	return db_query($q, array('fv' => 'getTankorTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tankorId)));
    }

    function getTankorMkId($tankorId) {
	$q = "SELECT mkId FROM tankor LEFT JOIN targy USING (targyId) WHERE tankor.tankorId=%u";
	return db_query($q, array('fv' => 'getTankorMkId', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tankorId)));		
    }

    function getTankorByMkId($mkId, $tanev, $SET = array('csakId' => false,'filter' => array()) ) {

	global $_TANEV, $__tankorOrder;

	if ($tanev == __TANEV) $TA = $_TANEV; else $TA = getTanevAdat($tanev);
	$szsz = count($TA['szemeszter']);

	if (count($SET['filter']) > 0) $W = ' AND '.implode(' AND ',$SET['filter']); else $W = '';

	if ($SET['csakId'] !== true) {
    	    $q = "SELECT tankor.tankorId AS tankorId, tankorNev, tankorTipusId, targy.targyId AS targyId, kovetelmeny, jelenlet, felveheto,
			 tanev, szemeszter, SUM(oraszam)/%u AS oraszam
		    FROM tankor LEFT JOIN targy USING (targyId)
		    		LEFT JOIN tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
				LEFT JOIN tankorTipus USING (tankorTipusId)
		    WHERE tanev=%u AND mkId=%u $W
		    GROUP BY tankorId
		    ORDER BY $__tankorOrder";
	    $v = array($szsz, $tanev, $mkId);
	    if ($SET['result'] == 'multiassoc') {
		$RESULT = db_query($q, array(
		    'fv' => 'getTankorByMkId', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $v
		));
	    } else {
		$RESULT = db_query($q, array(
		    'fv' => 'getTankorByMkId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v
		));
		$tolDt = isset($SET['tolDt']) ? $SET['tolDt'] : $TA['kezdesDt'];
		$igDt = isset($SET['igDt']) ? $SET['igDt'] : $TA['zarasDt'];
		for($i = 0; $i < count($RESULT); $i++) {
		    $RESULT[$i]['tanarok'] = getTankorTanaraiByInterval(
			$RESULT[$i]['tankorId'], 
			array('tanev' => $tanev, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result' => 'nevsor',
			    'datumKenyszeritessel' => $SET['datumKenyszeritessel']
			)
		    );
		}
	    }
	} else {
    	    $q = "SELECT tankor.tankorId AS tankorId
		     FROM tankor LEFT JOIN targy USING (targyId)
		    		 LEFT JOIN tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
				 WHERE tanev=%u AND mkId=%u $W
				 GROUP BY tankorId
		     ORDER BY $__tankorOrder";
	    $v = array($tanev, $mkId);
	    $RESULT = db_query($q, array('fv' => 'getTankorByMkId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));	
	}
	return $RESULT;
    }

    /*
        A megadott osztályokhoz - és csak azokhoz - rendelt tankörök listája
    */
    function getTankorByOsztalyIds($osztalyIds, $tanev = __TANEV) { // csak Id-kkel tér vissza

            $q = "SELECT DISTINCT tankorId FROM tankorOsztaly LEFT JOIN tankorSzemeszter USING (tankorId)
                    WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") AND tanev=%u
                    AND tankorId NOT IN (
                        SELECT DISTINCT tankorId FROM tankorOsztaly LEFT JOIN tankorSzemeszter USING (tankorId)
                        WHERE osztalyId NOT IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") AND tanev=%u
                    )";
	    $v = mayor_array_join($osztalyIds, array($tanev), $osztalyIds, array($tanev));
            return db_query($q, array('fv' => 'getTankorByOsztalyIds', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));
    }

    function getTankorByTeremId($teremId, $tanev = __TANEV) { // KIVÉTEL speciális függvény, teremben levő órák tankörei - tesztelés alatt

            $q = "SELECT tankor.* FROM ora LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) WHERE teremId = %u GROUP BY tankorId";
	    $v = array($teremId);
            return db_query($q, array('fv' => 'getTankorByTeremId', 'debug'=>false,'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
    }


    function getTankorByOsztalyId($osztalyId, $tanev = __TANEV, $SET = array('csakId' => false, 'tanarral' => false, 'result' => '')) {

	global $__tankorOrder;

	$v = array($osztalyId, $tanev);
	if ($SET['csakId'] == true || $SET['result'] == 'idonly') {

// Felesleges a tankor tábla a lekérdezésben - nem?
//	    $q = "SELECT DISTINCT tankor.tankorId
//		FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
//		LEFT JOIN tankorOsztaly USING (tankorId)
//		WHERE osztalyId=%u AND tanev=%u
//		ORDER BY LPAD(substring_index(substring_index(tankorNev,'-',1),'.',1),2,'0'),tankorNev,tanev,szemeszter";
	    $q = "SELECT DISTINCT tankorId
		FROM tankorOsztaly LEFT JOIN tankorSzemeszter USING (tankorId)
		WHERE osztalyId=%u AND tanev=%u
		ORDER BY ".$__tankorOrder.",tanev,szemeszter";
	    $RESULT = db_query($q, array('fv' => 'getTankorByOsztalyId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));	    

	} else {

	    $q = "SELECT DISTINCT tankor.tankorId,tankorNev,tankorTipusId,targyId,kovetelmeny,jelenlet,felveheto
		FROM tankor 
		    LEFT JOIN tankorTipus USING (tankorTipusId)
		    LEFT JOIN tankorSzemeszter USING (tankorId)
		    LEFT JOIN tankorOsztaly USING (tankorId)
		WHERE osztalyId=%u AND tanev=%u
		ORDER BY ".$__tankorOrder.",tanev,szemeszter";
	    if (!isset($SET['result']) || $SET['result']=='') {
		$RESULT = db_query($q, array('fv' => 'getTankorByOsztalyId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
		if ($SET['tanarral'] === true) {
		    global $_TANEV;
		    if ($tanev != __TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;
		    for($i = 0; $i < count($RESULT); $i++)
			$RESULT[$i]['tanarok'] = getTankorTanaraiByInterval($RESULT[$i]['tankorId'], array('tanev' => $tanev, 'tolDt' => $TA['kezdesDt'], 'igDt' => $TA['zarasDt'], 'result' => 'nevsor'));
		}
	    } else {
		$RESULT = db_query($q, array(
		    'fv' => 'getTankorByOsztalyId', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $v
		));
	    }
	}
	return $RESULT;
    }

    function getTankorByTargyId($targyId, $tanev, $SET = array('idonly' => true, 'lista' => false), $olr = '') {

	global $_TANEV,$__tankorOrder;

	if ($tanev != __TANEV) $TA = getTanevAdat($tanev);
	else $TA = $_TANEV;
	$szsz = count($TA['szemeszter']);
	if ($SET['idonly']) {
    	    $q = "SELECT tankor.tankorId AS tankorId
		    FROM ".__INTEZMENYDBNEV.".tankor
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
				 WHERE tanev=%u AND targyId=%u
				 GROUP BY tankorId";
	    $RESULT = db_query($q,array('fv' => 'getTankorByTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tanev, $targyId)));
	} elseif ($SET['lista']) {
    	    $q = "SELECT DISTINCT tankor.tankorId AS tankorId,tankorNev,tankorTipusId,kovetelmeny,jelenlet,felveheto
			FROM ".__INTEZMENYDBNEV.".tankor 
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
			WHERE tanev=%u AND targyId=%u
		        ORDER BY ".$__tankorOrder;	
	    $RESULT = db_query($q, array('fv' => 'getTankorByTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanev, $targyId)));
	} else {
    	    $q = "SELECT tankor.tankorId AS tankorId,tankorNev,tankorTipusId,kovetelmeny,jelenlet,felveheto,tanev,szemeszter,sum(oraszam)/%u AS oraszam
			FROM ".__INTEZMENYDBNEV.".tankor 
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
				 WHERE tanev=%u AND targyId=%u
				 GROUP BY tankorId
		     ORDER BY ".$__tankorOrder;
	    $RESULT = db_query($q, array('fv' => 'getTankorByTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($szsz, $tanev, $targyId)));
	}
	return $RESULT;
    }

    function getTankorByTanarId($tanarId, $tanev = __TANEV, $SET = array('csakId' => false, 'tolDt' => '', 'igDt' => '', 'result' => '', 'tanarral' => false), $olr = '') {

	global $__tankorOrder;

	if ($tanev=='') $tanev=__TANEV;

	if ($tanarId=='') {
	    $_SESSION['alert'][] = '::getTankorByTanarId fv hívás a kötelező $tanarId paraméter nélkül!';
	    return false;
	}

	$tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt($tanev, $tolDt, $igDt);
/* DÁTUMOKKK!!!!! */
	$v = array($tanev, $tanarId, $tolDt, $igDt);
	if ($SET['csakId']===true) {
    	    $q = "SELECT tankor.tankorId AS tankorId
		     FROM tankor LEFT JOIN tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
		    		 LEFT JOIN tankorTanar ON (tankor.tankorId=tankorTanar.tankorId)
		    WHERE tanev=%u AND tanarId=%u
		    AND ('%s' <= kiDt OR kiDt IS NULL)
		    AND '%s' >= beDt
		    GROUP BY tankorId";
	    $RESULT = db_query($q, array('fv' => 'getTankorByTanarId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $olr);
	} else {
    	    $q = "SELECT tankor.tankorId AS tankorId,tankorNev,tankorTipusId,kovetelmeny,jelenlet,felveheto,tanev,szemeszter
		     FROM ".__INTEZMENYDBNEV.".tankor
				LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId)
		    		LEFT JOIN ".__INTEZMENYDBNEV.".tankorTanar ON (tankor.tankorId=tankorTanar.tankorId)
				LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
		    WHERE tanev=%u AND tanarId=%u 
		    	AND ('%s' <= kiDt OR kiDt IS NULL)
			AND '%s' >= beDt
		    GROUP BY tankorId
		    ORDER BY ".$__tankorOrder.",tanev,szemeszter";	    
		// a kompatibilitás jegyében...
		if ($SET['result']=='multiassoc') {
		    $RESULT = db_query($q, array(
			'result' => 'multiassoc', 'values' => $v, 'keyfield' => 'tankorId', 'modul' => 'naplo_intezmeny', 'fv' => 'getTankorByTanarId'
		    ));
		} else {
		    $RESULT = db_query($q, array(
			'result' => 'indexed', 'values' => $v, 'modul' => 'naplo_intezmeny', 'fv' => 'getTankorByTanarId'
		    ), $olr);
		    if ($SET['tanarral'] === true) {
			global $_TANEV;
			if ($tanev != __TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;
			for($i = 0; $i < count($RESULT); $i++)
			    $RESULT[$i]['tanarok'] = getTankorTanaraiByInterval($RESULT[$i]['tankorId'], array('tanev' => $tanev, 'tolDt' => $TA['kezdesDt'], 'igDt' => $TA['zarasDt'], 'result' => 'nevsor'));
		    }
		    
		}
	}
	return $RESULT;
    }

    function getTankorDiakFelmentes($diakId, $tanev = __TANEV, $SET = array('csakId' => false, 'tolDt' => '', 'igDt' => '', 'override' => false, 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól'), 'nap'=>null, 'ora'=>null) , $olr = '') {
	$tolDt = $SET['tolDt'];	$igDt = $SET['igDt'];
	initTolIgDt($tanev, $tolDt, $igDt);

	if ($SET['csakId'] === true || $SET['result'] == 'csakId') $SET['result'] = 'idonly';
	elseif ($SET['result'] == '') $SET['result'] = 'indexed';
	if (isset($SET['felmentesTipus'])) {
    	    if (!is_array($SET['felmentesTipus'])) {
    		$W = ' AND felmentesTipus = "'.$SET['felmentesTipus'].'" ';
	    } else {
		$W = ' AND felmentesTipus IN ("'.implode('","',$SET['felmentesTipus']).'") ';
	    }
	} else { // alapértelmezés
    	    $W = ' AND felmentesTipus = "óralátogatás alól" ';
	}

	if (is_numeric($SET['nap'])) $W .= " AND (nap=".$SET['nap']." OR nap is null) ";
	if (is_numeric($SET['ora'])) $W .= " AND (ora=".$SET['ora']." OR ora is null) ";

	if ($SET['result'] == 'idonly') {
	    $q = "SELECT DISTINCT tankorId
		FROM ".__INTEZMENYDBNEV.".tankorDiakFelmentes LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE diakId=%u AND tanev=%u
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt".$W
	    ;
	    $v = array($diakId, $tanev, $tolDt, $igDt,$tolDt, $igDt);

	} else {
	    $q = "SELECT *
		FROM ".__INTEZMENYDBNEV.".tankorDiakFelmentes
		WHERE diakId=%u 
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt".$W.""
	    ;
	    $v = array($diakId, $tolDt, $igDt,$tolDt, $igDt);

	}
	$RESULT = db_query($q, array('keyfield' => 'tankorId', 'modul' => 'naplo_intezmeny', 'fv' => 'getTankorDiakFelmentes', 'values' => $v, 'result' => $SET['result']));
	return $RESULT;
    }

    function getFelmentes($SET = array('tanev'=>__TANEV, 'osztalyId'=>false,'csakId' => false, 'tolDt' => '', 'igDt' => '', 'override' => false, 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól'), 'nap'=>null, 'ora'=>null) , $olr = '') {
	$tanev = ($SET['tanev']!='')?$SET['tanev']:__TANEV;
	$tolDt = $SET['tolDt'];	$igDt = $SET['igDt'];
	initTolIgDt($tanev, $tolDt, $igDt);

	if ($SET['csakId'] === true || $SET['result'] == 'csakId') $SET['result'] = 'idonly';
	elseif ($SET['result'] == '') $SET['result'] = 'indexed';
	if (isset($SET['felmentesTipus'])) {
    	    if (!is_array($SET['felmentesTipus'])) {
    		$W = ' AND felmentesTipus = "'.$SET['felmentesTipus'].'" ';
	    } else {
		$W = ' AND felmentesTipus IN ("'.implode('","',$SET['felmentesTipus']).'") ';
	    }
	} else { // alapértelmezés
    	    $W = ' AND felmentesTipus = "óralátogatás alól" ';
	}

	if (is_numeric($SET['nap'])) $W .= " AND (nap=".$SET['nap']." OR nap is null) ";
	if (is_numeric($SET['ora'])) $W .= " AND (ora=".$SET['ora']." OR ora is null) ";
	if (is_numeric($SET['osztalyId'])) $W .= " AND osztalyId=".$SET['osztalyId']." ";

	if ($SET['result'] == 'idonly') {
	    $q = "SELECT DISTINCT tankorId
		FROM ".__INTEZMENYDBNEV.".tankorDiakFelmentes LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE tanev=%u
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt".$W
	    ;
	    $v = array($tanev, $tolDt, $igDt,$tolDt, $igDt);

	} else {
	    $q = "SELECT a.*
		FROM ".__INTEZMENYDBNEV.".tankorDiakFelmentes AS a
		LEFT JOIN ".__INTEZMENYDBNEV.".osztalyDiak ON (osztalyDiak.diakId=a.diakId)
		WHERE ('%s' <= a.kiDt OR a.kiDt IS NULL)
		AND '%s' >= a.beDt".$W." ORDER BY a.diakId,a.beDt"
	    ;
	    $v = array($tolDt, $igDt, $tolDt, $igDt);

	}
	$RESULT = db_query($q, array('debug'=>false,'keyfield' => 'tankorId', 'modul' => 'naplo_intezmeny', 'fv' => 'getTankorDiakFelmentes', 'values' => $v, 'result' => $SET['result']));
	return $RESULT;
    }

    function getTankorByDiakId($diakId, $tanev = __TANEV, $SET = array('csakId' => false, 'tolDt' => '', 'igDt' => '', 'override' => false, 'result'=>'indexed') , $olr = '') {

	global $__tankorOrder;

	if (isset($SET['jelenlet']) && $SET['jelenlet']!='') {
	    //$W = " AND tankorDiak.jelenlet='%s'";
	    $_SESSION['alert'][] = 'info:ERR400:getTankorByDiakId() nem hívható "jelenlet" paraméterrel!:'.$SET['jelenlet'];
	} else {
	    //$W = '';
	}

	$tolDt = $SET['tolDt'];	$igDt = $SET['igDt'];
	initTolIgDt($tanev, $tolDt, $igDt);

	if ($SET['csakId'] === true || $SET['result'] == 'csakId') $SET['result'] = 'idonly';
	elseif ($SET['result'] == '') $SET['result'] = 'indexed';

	if ($SET['result'] == 'idonly') {
//	    $q = "SELECT DISTINCT tankor.tankorId,tankorNev,targyId,tankor.kovetelmeny,tankor.jelenlet,felveheto
//		FROM ".__INTEZMENYDBNEV.".tankor LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
//		LEFT JOIN ".__INTEZMENYDBNEV.".tankorDiak USING (tankorId)
//		WHERE diakId=$diakId AND tanev=$tanev
//		AND ('$tolDt' <= kiDt OR kiDt IS NULL)
//		AND '$igDt' >= beDt
//		ORDER BY LPAD(substring_index(substring_index(tankorNev,'-',1),'.',1),2,'0'),tankorNev,tanev,szemeszter";
	    $q = "SELECT DISTINCT tankorId
		FROM ".__INTEZMENYDBNEV.".tankorDiak LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE diakId=%u AND tanev=%u
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt";
//	    $RESULT = _m_y_id_query($q, array('db' => 'naplo_intezmeny', 'fv' => 'getTankorByDiakId', 'result' => 'idonly'), $olr);
	} else {
	    $q = "SELECT DISTINCT tankor.tankorId,tankorNev,tankorTipusId,targyId,tankor.kovetelmeny,tankorTipus.jelenlet AS jelenlet, felveheto
		FROM ".__INTEZMENYDBNEV.".tankor 
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		    LEFT JOIN ".__INTEZMENYDBNEV.".tankorDiak USING (tankorId)
		WHERE diakId=%u AND tanev=%u
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt $W
		ORDER BY ".$__tankorOrder.",tanev,szemeszter";
//		if ($SET['result']=='multiassoc')
//		    $RESULT = _m_y_multiassoc_query($q,'tankorId', array('keyfield'=>'tankorId','db'=>'naplo_intezmeny','fv'=>'getTankorByDiakId'));
//		else
//		    $RESULT = _m_y_query($q, array('db'=>'naplo_intezmeny', 'fv'=>'getTankorByDiakId'), $olr);
	}
	$v = array($diakId, $tanev, $tolDt, $igDt, $SET['jelenlet']);
	$RESULT = db_query($q, array('keyfield' => 'tankorId', 'modul' => 'naplo_intezmeny', 'fv' => 'getTankorByDiakId', 'values' => $v, 'result' => $SET['result']));
	return $RESULT;
    }

    function getTankorIdsByDiakIds($diakIds, $SET = array(
		'tanev' => __TANEV, 
		'tolDt'=>'',
		'igDt'=>'',
		'felmentettekkel'=>true
//		'jelenlet'=>array('kötelező','nem kötelező')
//		'kovetelmeny'=>array('aláírás','vizsga','jegy')
	    )
    ) {

	global $__tankorOrder;

        if (isset($SET['jelenlet'])) {
	    $_SESSION['alert'][] = 'info:!!!:getTankorIdsByDiakIds(), jelenlét parameter is obsolete';
        }

	if (!is_array($diakIds)) return false;
	elseif (count($diakIds) == 0) return array();
	$tanev = readVariable($SET['tanev'],'numeric unsigned',__TANEV);
	$tolDt = $SET['tolDt'];
	$igDt  = $SET['igDt'];
	initTolIgDt($tanev, $tolDt, $igDt);

//	if (!is_array($SET['jelenlet'])) $SET['jelenlet'] = array('kötelező','nem kötelező');
//	if (!is_array($SET['kovetelmeny'])) $SET['kovetelmeny'] = array('aláírás','vizsga','jegy');

	// ----------------------
	$q = "SELECT DISTINCT tankorDiak.tankorId
		FROM ".__INTEZMENYDBNEV.".tankorDiak
		LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND tanev=%u
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt
		ORDER BY ".$__tankorOrder.",tanev,szemeszter";
//		AND kovetelmeny IN ('".implode("','", array_fill(0, count($SET['kovetelmeny']), '%s'))."')
//		AND jelenlet IN ('".implode("','", array_fill(0, count($SET['jelenlet']), '%s'))."')

//	$v = array_merge($diakIds, array($tanev, $tolDt, $igDt), $SET['jelenlet'], $SET['kovetelmeny']);
	$v = mayor_array_join($diakIds, array($tanev, $tolDt, $igDt));
	$TANKOROK = db_query($q, array('fv' => 'getTankorIdsByDiakIds', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $olr);

	if (!is_array($SET['felmentettekkel'])) $SET['felmentettekkel'] = true;
	/* Ha felmentett, akkor kivegyük-e az adott tanköridket?! */
	if ($SET['felmentettekkel'] === false) {
	    $q = "SELECT DISTINCT tankorDiak.tankorId
		    FROM ".__INTEZMENYDBNEV.".tankorDiakFelmentes
		    WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND tanev=%u
		    AND ('%s' <= kiDt OR kiDt IS NULL)
		    AND '%s' >= beDt";
	    $v = mayor_array_join($diakIds, array($tanev, $tolDt, $igDt));
            $FM = db_query($q, array('fv' => 'getTankorIdsByDiakIdsFM', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $olr);
	}
	if (is_array($TANKOROK)) {
	    if (is_array($FM) && count($FM)>0) {
		$RESULT = array_diff($TANKOROK,$FM);
    		reset($RESULT);                                                                                                                                                       
	        sort($RESULT);             
	    } else {
		$RESULT = $TANKOROK;
	    }
	}
	return $RESULT;
    }

    function getTankorById($tankorId, $tanev = __TANEV, $olr = '') {
	if ($tankorId=='') return false;
	if ($tanev == '') return getTankorok(array("tankor.tankorId=$tankorId"), '', $olr);
	else return getTankorok(array("tankor.tankorId=$tankorId", "tanev=$tanev"), '', $olr);
    }

    function getTankorByTanev($tanev = __TANEV, $SET = array('result' => 'indexed', 'jelenlet'=>''), $olr = '') {
	global $__tankorOrder;

	if ($SET['jelenlet']!='') {
	    $W['join'] = ' LEFT JOIN '.__INTEZMENYDBNEV.'.tankorTipus USING (tankorTipusId) ';
	    $W['where']= ' AND tankorTipus.jelenlet IN ("'.$SET['jelenlet'].'")';
	}
	if ($SET['result'] == 'idonly') {
	    $q = "SELECT DISTINCT tankorId 
		FROM ".__INTEZMENYDBNEV.".tankor 
		LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId) 
		".$W['join']."
		WHERE tanev=%u ".$W['where']." ORDER BY $__tankorOrder";
	    $ret = db_query($q, array('fv' => 'getTankorByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tanev)), $olr);
	} else {
	    $q = "SELECT DISTINCT tankorId,tankorNev,targyId
		FROM ".__INTEZMENYDBNEV.".tankor 
		LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId) 
		".$W['join']."
		WHERE tanev=%u ".$W['where']." ORDER BY $__tankorOrder";
	    $ret = db_query($q, array('fv' => 'getTankorByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanev)), $olr);
	}
	return $ret;
    }

    function getTankorByBontasIds($bontasIds) {
    /*
     * Az aktuális tanévben kérdezi le a bontások által megadott osztályhoz és tárgyhoz rendelhető tankörök tankorId-it...
     */
	if (!is_array($bontasIds) || count($bontasIds)==0) return array();
	$q = "select distinct tankorId from ".__TANEVDBNEV.".kepzesTargyBontas 
		left join (tankor left join tankorOsztaly using (tankorId)) using (targyId,osztalyId) 
		left join tankorSzemeszter using(tankorId) 
		where tanev=".__TANEV." and bontasId in (".implode(',', array_fill(0, count($bontasIds), '%u')).")";
	return db_query($q, array('fv'=>'getTankorByBontasIds','modul'=>'naplo_intezmeny','result'=>'idonly','values'=>$bontasIds));
    }

    // FIGYELEM! NEM MINDIG OPTIMÁLIS EREDMÉNY
    function getTankorok(
	$FILTER = array(), 
	$ORDER = '',
	$olr = ''
    ) {
	global $__tankorOrder;
	/* Általános filterező */
	$QW = '';
	if (is_array($FILTER) && count($FILTER)>0) {
	    $QW = " WHERE ".implode(' AND ',$FILTER);
	}
	if ($ORDER == '') $ORDER = $__tankorOrder.",tanev,szemeszter";

	// --TODO: továbbgondolásra szorul
	// jelenlet = tankorJelenlet
        $q = "SELECT DISTINCT tankor.tankorId,tankorTipusId,tankorNev,targyId,kovetelmeny,jelenlet,felveheto,tanev,zaroKovetelmeny, tankorCn
		     FROM ".__INTEZMENYDBNEV.".tankor 
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId) 
			LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (targyId)
		    $QW 
		    ORDER BY $ORDER";
        return db_query($q, array('fv' => 'getTankorok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'), $olr);

    }

    function getTankorSzemeszterei($tankorId,$SET=array(),$olr='') {
	if ($tankorId=='') return false;

	$q = "SELECT * FROM tankorSzemeszter LEFT JOIN szemeszter using (tanev,szemeszter) WHERE tankorId=%u order by tanev,szemeszter";
	$ret = db_query($q, array('fv' => 'getTankorSzemeszterei', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tankorId)), $olr);

	if (is_array($SET['arraymap'])) return reindex($ret, $SET['arraymap']);
	else return $ret;
    }

    function getTankorOsztalyai($tankorId, $SET = array('result' => 'id'), $olr='') { // lásd még getTankorOsztalyaiByTanev
	if ($tankorId=='') return false;

	    $q = "SELECT DISTINCT osztalyId FROM tankorOsztaly WHERE tankorId=%u";
	    if ($SET['result'] == 'id') {
		return db_query($q, array('fv' => 'getTankorOsztalyai', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tankorId)), $olr);
	    } else {
		return db_query($q, array('fv' => 'getTankorOsztalyai', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tankorId)), $olr);
	    }
    }

    function getTankorOsztalyaiByTanev($tankorId, $tanev = __TANEV, $SET = array('result' => 'id', 'tagokAlapjan' => false, 'tolDt' => '', 'igDt' => ''), $olr = null) {

	if ($tankorId=='' || $tanev=='') return false;

	if ($SET['tagokAlapjan']) { // Ha a tényleges tagok alapján keressük a tankör osztályait...
	    $tolDt = readVariable($SET['tolDt'], 'datetime', null);
	    $igDt = readVariable($SET['igDt'], 'datetime', null);
	    initTolIgDt($tanev, $tolDt, $igDt);
	    /*
		- a diák tagja a tankörnek az adott idő intervellumban
		- a diák tagja az osztálynak az adott idő intervellumban
		- az osztály hozzá van rendelve a tankörhöz (lehet egy diák több osztálynak is tagja - ez bezavarhat)
	    */
	    $q = "SELECT osztalyId FROM tankorDiak LEFT JOIN osztalyDiak USING (diakId) 
		    WHERE tankorId=%u
		    AND tankorDiak.beDt <= '%s' AND (tankorDiak.kiDt IS NULL OR tankorDiak.kiDt >= '%s')
		    AND osztalyDiak.beDt <= '%s' AND (osztalyDiak.kiDt IS NULL OR osztalyDiak.kiDt >= '%s')
		    AND osztalyId IN (SELECT DISTINCT osztalyId FROM tankorOsztaly WHERE tankorId=%u)
		    GROUP BY osztalyId ORDER BY COUNT(osztalyId) DESC"; // A legtöbb tagú osztály kerüljön előre
	    $v = array($tankorId, $igDt, $tolDt, $igDt, $tolDt, $tankorId);
	    if ($SET['result'] == 'id') 
		$return = db_query($q, array('fv' => 'getTankorOsztalyaiByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v),$olr);
	    else 
		$return = db_query($q, array('fv' => 'getTankorOsztalyaiByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $olr);
	    if (is_array($return) && count($return) > 0) return $return;
	    else $_SESSION['alert'][] = 'info:fallback:ennek a tankörnek egy tagja sincs, tagok alapján nem tudom lekérdezni az érintett osztályokat:'.$tankorId;
	}
	// Ha nincs a tankörnek egy tagja sem, vagy nem tagok alapján keresünk...
	$q = "SELECT tankorOsztaly.osztalyId AS osztalyId FROM tankorOsztaly 
		LEFT JOIN osztaly USING (osztalyId) 
		WHERE tankorId=%u AND kezdoTanev<=%u AND vegzoTanev>=%u";
	$v = array($tankorId, $tanev, $tanev);
	if ($SET['result'] == 'id') 
	    return db_query($q, array('fv' => 'getTankorOsztalyaiByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $olr);
	else 
	    return db_query($q, array('fv' => 'getTankorOsztalyaiByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $olr);
    }

    function getTankorOsztalyaiByBontas($tankorId) {

	$q = "select distinct osztalyId from bontasTankor left join kepzesTargyBontas using (bontasId)  where tankorId=%u";
	return db_query($q, array('fv'=>'getTankorOsztalyaiByBontas','modul'=>'naplo','result'=>'idonly','values'=>array($tankorId)));

    }

    function getTankorTanarai($tankorId, $olr = '') {
	if ($tankorId == '') return false;
	$q = "SELECT tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, viseltCsaladiNev, viseltUtonev)) as tanarNev, min(tankorTanar.beDt) AS minBeDt, max(tankorTanar.kiDt) AS maxKiDt
		FROM ".__INTEZMENYDBNEV.".tankorTanar LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId)
		WHERE tankorId=%u GROUP BY tanarId ORDER BY tankorTanar.beDt,tankorTanar.kiDt";
	return db_query($q, array('fv' => 'getTankorTanarai', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tankorId)), $olr);
    }

    function getTankorTanarBejegyzesek($tankorId, $olr = '') {
	if ($tankorId == '') return false;
	$q = "SELECT tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, viseltCsaladiNev, viseltUtonev)) as tanarNev, tankorTanar.beDt, tankorTanar.kiDt
		FROM ".__INTEZMENYDBNEV.".tankorTanar LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId)
		WHERE tankorId=%u ORDER BY tankorTanar.beDt,tankorTanar.kiDt";
	return db_query($q, array('fv' => 'getTankorTanarBejegyzesek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tankorId)), $olr);
    }

    function getTankorTanaraiByInterval($tankorId, $Param = array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result' => 'nevsor', 'datumKenyszeritessel' => false), $olr = '') {

	$tolDt = $Param['tolDt'];
	$igDt = $Param['igDt'];
	if (!$Param['datumKenyszeritessel']) initTolIgDt($Param['tanev'], $tolDt, $igDt);

	if (!is_array($tankorId)) $tankorId = array($tankorId);
	if (implode(',', $tankorId) == '') {
	    return false;
	}

	if ($Param['result'] == 'csakId' or $Param['result'] == 'idonly') {
	    $q = "SELECT DISTINCT tanarId FROM ".__INTEZMENYDBNEV.".tankorTanar
		    WHERE tankorId IN (".implode(',', array_fill(0, count($tankorId), '%u')).")
		    AND (tankorTanar.kiDt IS NULL OR tankorTanar.kiDt>='%s')
		    AND tankorTanar.beDt<='%s'";
	    array_push($tankorId, $tolDt, $igDt);
	    return db_query($q, array(
		'fv' => 'getTankorTanaraiByInterval', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $tankorId
	    ), $olr);
	} else {
	    $q = "SELECT DISTINCT tankorId, tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, viseltCsaladiNev, viseltUtonev)) as tanarNev
		FROM ".__INTEZMENYDBNEV.".tankorTanar LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId)
		WHERE tankorId IN (".implode(',',$tankorId).")
		AND (tankorTanar.kiDt IS NULL OR tankorTanar.kiDt>='$tolDt')
		AND tankorTanar.beDt<='$igDt' ORDER BY tankorTanar.beDt, tankorTanar.kiDt, tanarNev";
	    array_push($tankorId, $tolDt, $igDt);
	    if ($Param['result'] == 'assoc') 
		return db_query($q, array(
		    'fv' => 'getTankorTanaraiByInterval', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
		), $olr);
	    else return db_query($q, array(
		    'fv' => 'getTankorTanaraiByInterval', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $tankorIds
		), $olr);
	}
    }

    function getTankorDiakjai($tankorId, $olr = '') {
	if ($tankorId=='' || (is_array($tankorId) && count($tankorId) == 0)) return false;
	if (is_array($tankorId)) {
	    $tankorIds = array_filter(array_unique($tankorId),'_isempty');
	    $W = "tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
	    $v = $tankorIds;
	} else {
	    $W = "tankorId=%u";
	    $v = array($tankorId);
	}
	$q = "SELECT DISTINCT diakId FROM ".__INTEZMENYDBNEV.".tankorDiak WHERE ".$W;
	$RETURN['idk'] = db_query($q, array(
	    'fv' => 'getTankorDiakjai/1', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v
	), $olr);
	// jelenlet = diakJelenlet -- TODO
	$q = "SELECT diakId,DATE_FORMAT(kiDt,'%%Y-%%m-%%d') AS kiDt, DATE_FORMAT(beDt,'%%Y-%%m-%%d') AS beDt,_jelenlet,_jelenlet as diakJelenlet,_kovetelmeny,jovahagyva
		FROM ".__INTEZMENYDBNEV.".tankorDiak WHERE tankorId=%u ORDER BY bedt";
	$RETURN['adatok'] = db_query($q, array(
	    'fv' => 'getTankorDiakjai/2', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'diakId', 'values' => array($tankorId)
	), $olr);
	$RETURN['nevek'] = getDiakokById($RETURN['idk'],$olr);
	return $RETURN;
    }

    function getTankorDiakjaiByInterval($tankorId, $tanev = __TANEV, $tolDt = '', $igDt = '', $olr = '') {

	initTolIgDt($tanev, $tolDt, $igDt);
	if ($tankorId == '' || (is_array($tankorId) && count($tankorId) == 0)) return false;
	if (is_array($tankorId)) {
	    $tankorIds = array_filter(array_unique($tankorId),'_isempty');
	    $W = "tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
	    $v = $tankorIds;
	    array_push($v, $tolDt,$igDt);
	} else {
	    $W = "tankorId=%u";
	    $v = array($tankorId,$tolDt,$igDt);
	}
	$q = "SELECT DISTINCT diakId FROM ".__INTEZMENYDBNEV.".tankorDiak 
		WHERE $W AND (kiDt>='%s' OR kiDt is null) AND beDt<='%s' ORDER BY ".__TANEVDBNEV.".getNev(diakId,'diak'),diakId";
	$RETURN['idk'] = db_query($q, array('fv' => 'getTankorDiakjaiByInterval', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $olr);
	/* jelenlet, követelmeny, jóváhagyva mezők MÁR nincsenek */
	$q = "SELECT diakId,DATE_FORMAT(kiDt,'%%Y-%%m-%%d') AS kiDt, DATE_FORMAT(beDt,'%%Y-%%m-%%d') AS beDt 
		FROM ".__INTEZMENYDBNEV.".tankorDiak WHERE $W AND (kiDt>='%s' OR kiDt is null) AND beDt<='%s' ORDER BY bedt";
	$RETURN['adatok'] = db_query($q, array(
	    'fv' => 'getTankorDiakjaiByInterval', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'diakId', 'values' => $v
	), $olr);
	$RETURN['nevek'] = getDiakokById($RETURN['idk'], $olr);
	return $RETURN;

    }


    function tankorTagjaE($diakId, $tankorId, $tanev = __TANEV, $tolDt = '', $igDt = '') {


	initTolIgDt($tanev, $tolDt, $igDt);

	$q = "SELECT COUNT(tankorId) FROM tankorDiak
		WHERE tankorId=%u AND diakId=%u
		AND ('%s' <= kiDt OR kiDt IS NULL)
		AND '%s' >= beDt";
	$v = array($tankorId, $diakId, $tolDt, $igDt);
	$num = db_query($q, array('fv' => 'tankorTagjaE', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));
	return ($num > 0);

    }

    function tankorVegzosE($tankorId, $tanev = __TANEV, $SET = array('tagokAlapjan' => true, 'tolDt' => null, 'igDt' => null)) {
	/*
	    Egy tankört akkor tekintünk végzősnek, ha csak végzős diákok a tagjai. De lehet, hogy egy diák több osztálynak is tagja.
	    Ha az egyik esetleg nem végzős, akkor a diákok osztályai közt lesz nem végzős.

	    Tehát a tankörhöz rendelt osztályokon belül nézzük a tagok osztályait, mert elvileg ez a halmaz csak bővebb lehet a tankör 
	    tagok osztályainak halmazánál.
	*/

	if ($tankorId == '' || $tanev == '') return false;

	// Először lekérdezzük a tankörhöz rendelt nem végzős osztályok számát
	$q = "SELECT COUNT(osztalyId) FROM tankorOsztaly LEFT JOIN osztaly USING (osztalyId)
		    WHERE tankorId=%u AND vegzoTanev != %u";
	$return = (db_query($q, array('fv' => 'tankorVegzosE', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tankorId, $tanev))) == 0);
	// Ha a tankörhöz csak végzős osztályok vannak rendelve, vagy a tényleges tagok vizsgálatára nincs szükség, akkor visszatérünk
	if ($return || !$SET['tagokAlapjan']) return $return;

	// Lekérdezzük a tankör tagok osztályait (ez figyel a tankörhöz rendelt osztályokra való szűkítésre)
	$vegzosOsztalyok = getVegzosOsztalyok(array('tanev' => $tanev, 'result' => 'id'));
	$tankorOsztalyai = getTankorOsztalyaiByTanev(
	    $tankorId, $tanev = $tanev, 
	    array('result' => 'id', 'tagokAlapjan' => true, 'tolDt' => $SET['tolDt'], 'igDt' => $SET['igDt'])
	);
	// vizsgáljuk, hogy van-e benne nem végzős
	for ($i = 0; $i < count($tankorOsztalyai); $i++) {
	    if (!in_array($tankorOsztalyai[$i], $vegzosOsztalyok)) return false;
	}
	return true;

    }

    function tankorokVegzosekE($tankorIds, $tanev = __TANEV, $SET = array('tagokAlapjan' => true, 'tolDt' => null, 'igDt' => null)) {

	if (!is_array($tankorIds) || count($tankorIds) == 0 || $tanev == '') return false;

	// Először lekérdezzük a tankörökhöz rendelt nem végzős osztályok számát
	$q = "SELECT tankorId, COUNT(osztaly.osztalyId) AS db FROM tankorOsztaly 
		    LEFT JOIN osztaly ON tankorOsztaly.osztalyId = osztaly.osztalyId AND vegzoTanev != %u
		    WHERE tankorId IN (".implode(',', $tankorIds).")
		    GROUP BY tankorId";
	array_unshift($tankorIds, $tanev);
	$ret = db_query($q, array(
	    'fv' => 'tankorokVegzosekE', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
	));
	// Ha a tényleges tagok vizsgálatára is szükség van
	if ($SET['tagokAlapjan']) {
	    // Lekérdezzük a tankör tagok osztályait (ez figyel a tankörhöz rendelt osztályokra való szűkítésre)
	    $vegzosOsztalyok = getVegzosOsztalyok(array('tanev' => $tanev, 'result' => 'id'));
	    foreach ($ret as $tankorId => $tAdat) {
		$db = $tAdat['db'];
		$return[$tankorId] = true;
		if ($db != 0) { // Ha csak végzős osztálya van, akkor nem kell tovább nézni, különben...
		    $tankorOsztalyai = getTankorOsztalyaiByTanev(
			$tankorId, $tanev = $tanev, 
			array('result' => 'id', 'tagokAlapjan' => true, 'tolDt' => $SET['tolDt'], 'igDt' => $SET['igDt'])
		    );
		    // vizsgáljuk, hogy van-e benne nem végzős
		    for ($i = 0; $i < count($tankorOsztalyai); $i++) {
			if (!in_array($tankorOsztalyai[$i], $vegzosOsztalyok)) $return[$tankorId] = false;
			break;
		    }
		}
	    }
	} else {
	    foreach ($ret as $tankorId => $tAdat) {
		$return[$tankorId] = ($tAdat['db'] == 0);
	    }
	}
	return $return;

    }


    function tankorDiakKonzisztensE($diakId,$tankorId,$tanev,$tolDt,$igDt) 
    {
	/* 
	    Jelenlét analízis:
        	tankorDiak.jelenlet = diakJelenlet
	*/

	if ($diakId=='' || $tankorId=='') return false;
	initTolIgDt($tanev, $tolDt, $igDt);
	$tanevDb = tanevDbNev(__INTEZMENY,$tanev);

	// 1. adott intervallumban tagsága
	    $DTW1 = $DTW2 = array();
	    $q = "SELECT beDt, kiDt, kovetelmeny, jelenlet as diakJelenlet FROM tankorDiak WHERE diakId=%u AND tankorId=%u 
		    AND (jelenlet='nem kötelező' OR kovetelmeny!='jegy')AND (kiDt>'%s' OR kiDt IS NULL) AND beDt<'%s' ";
	    $v = array($diakId, $tankorId, $tolDt, $igDt);
	    $r = db_query($q, array('fv' => 'tankorDiakKozisztensE', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	    for ($i=0; $i<count($r); $i++) {
		if ($r[$i]['diakJelenlet']=='nem kötelező') 
		    if (is_null($r[$i]['kiDt'])) $DTW1[] =  "(%1\$s.hianyzas.dt>='".$r[$i]['beDt']."')";
		    else $DTW1[] =  "(%1\$s.hianyzas.dt BETWEEN '".$r[$i]['beDt']."' AND '".$r[$i]['kiDt']."')";
		if ($r[$i]['kovetelmeny']!='jegy') 
		    if (is_null($r[$i]['kiDt'])) $DTW2[] =  "(%1\$s.jegy.dt>='".$r[$i]['beDt']."')";
		    else $DTW2[] =  "(%1\$s.jegy.dt BETWEEN '".$r[$i]['beDt']."' AND '".$r[$i]['kiDt']."')";
	    }

	    if (count($DTW1)>0) {
		// 2. hiányzásai
		$q = "SELECT count(hianyzasId) FROM %1\$s.hianyzas LEFT JOIN ora USING (oraId) WHERE diakId=%2\$u 
			AND ora.tankorId=%3\$u AND (".implode(' OR ',$DTW1).")";
		$v = array($tanevDb, $diakId, $tankorId);
		$dbHianyzas = db_query($q,array('fv' => 'tankorDiakKonzisztensE', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
		if ($dbHianyzas>0) $_SESSION['alert'][] = 'info:hibas_hianyzasok:'.$dbHianyzas;

	    }
	    if (count($DTW2)>0) {
		// 3. jegyei
		$q = "SELECT count(jegyId) FROM %1\$s.jegy WHERE diakId=%2\$u AND tankorId=%3\$u AND (".implode(' OR ',$DTW2).")";
		$v = array($tanevDb, $diakId, $tankorId);
		$dbJegy = db_query($q,array('fv' => 'tankorDiakKonzisztensE', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
		if ($dbJegy>0) $_SESSION['alert'][] = 'info:hibas_jegyek:'.$dbJegy;
	    }

	    return ($dbHianyzas==0 && $dbJegy==0);

    }


    // itt a nap/ora-t is figyelni kellene, ha az pl a felmentésénél adott
    function tankorDiakHianyzasIdk($diakId, $tankorIds, $tanev, $tolDt, $igDt, $nap=null, $ora=null) 
    {
	$SET = array('diakId'=>$diakId, 'tankorIds'=>$tankorIds, 'tanev'=>$tanev, 'tolDt'=>$tolDt, 'igDt'=>$igDt, 'nap'=>$nap, 'ora'=>$ora);
	return tankorDiakHianyzasIdk2($SET);
    }

    function tankorDiakHianyzasIdk2($SET = array('diakId', 'tankorIds', 'tanev', 'tolDt', 'igDt', 'nap'=>null, 'ora'=>null), $olr='') 
    {

	$diakId = $SET['diakId'];
	$tanev = $SET['tanev'];
	$tolDt = $SET['tolDt'];
	$igDt = $SET['igDt'];
	$nap = $SET['nap'];
	$ora = $SET['ora'];
	$tankorIds = $SET['tankorIds'];

	if (!is_array($tankorIds)) if ($tankorIds == '') return false;
	else $tankorIds = array($tankorIds);
	if ($diakId=='' || count($tankorIds) == 0) return false;

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) $lr = db_connect('naplo_intezmeny'); else $lr = $olr;

	initTolIgDt($tanev, $tolDt, $igDt);
	$tanevDb = tanevDbNev(__INTEZMENY,$tanev);

	$v = mayor_array_join(array($tanevDb, $tanevDb, $diakId), $tankorIds, array($tanevDb, $tolDt, $igDt));

	if (isset($nap) && is_numeric($nap)) {
	    $W1 = ' AND (DAYOFWEEK(`%s`.hianyzas.dt) -1) = %u ';
	    $v = mayor_array_join($v,array($tanevDb,$nap));
	}
	if (isset($ora) && is_numeric($ora)) {
	    $W2 = ' AND `%s`.hianyzas.ora = %u ';
	    $v = mayor_array_join($v,array($tanevDb,$ora));
	}

	// 2. hiányzásai
	    $q = "SELECT hianyzasId FROM `%s`.hianyzas LEFT JOIN `%s`.ora USING (oraId) 
			WHERE diakId=%u AND ora.tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") 
			AND `%s`.hianyzas.dt BETWEEN '%s' AND '%s'".$W1.$W2;
	    $H = db_query($q, array('debug'=>false,'fv' => 'tankorDiakHianyzasIdk', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);
	    // if (count($H) > 0) $_SESSION['alert'][] = 'info:hibas_hianyzasok:db='.count($H);

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);
	return $H;
    }

    //function tankorDiakJegyIdk($diakId, $tankorIds, $tanev, $tolDt, $igDt) 
    function tankorDiakJegyIdk($SET = array('diakId', 'tankorIds', 'tanev', 'tolDt', 'igDt'), $olr = '')
    {

	$diakId = $SET['diakId'];
	$tanev = $SET['tanev'];
	$tolDt = $SET['tolDt'];
	$igDt = $SET['igDt'];
	$nap = $SET['nap'];
	$ora = $SET['ora'];
	$tankorIds = $SET['tankorIds'];

	if (!is_array($tankorIds)) if ($tankorIds == '') return false;
	else $tankorIds = array($tankorIds);

	if ($diakId=='' || count($tankorIds) == 0) return false;

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) $lr = db_connect('naplo_intezmeny'); else $lr = $olr;
	initTolIgDt($tanev, $tolDt, $igDt);
	$tanevDb = tanevDbNev(__INTEZMENY,$tanev);

	    // 3. jegyei
	    $q = "SELECT jegyId FROM `%s`.jegy 
			WHERE diakId=%u AND tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") AND jegy.dt BETWEEN '%s' AND '%s'";
	    $v = mayor_array_join(array($tanevDb, $diakId), $tankorIds, array($tolDt, $igDt));
	    $J = db_query($q, array('fv' => 'tankorDiakJegyIdk', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v));
	    //if (count($J) > 0) $_SESSION['alert'][] = 'info:hibas_jegyek:db='.count($J);

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);
	return $J;

    }

    function getTankorCsoport($tanev, $olr='') {

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) $lr = db_connect('naplo_intezmeny'); else $lr = $olr;

        // A tankör csoportjának lekérdezése
        $q = "SELECT * FROM ".__TANEVDBNEV.".csoport ORDER BY csoportNev,csoportId ";
        $ret = db_query($q, array(
	    'fv' => 'getTankorCsoport', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'
	), $lr);
	if (is_array($ret) && count($ret) == 0) $ret = array($tankorId);

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);

	return $ret;

    }    
    
    function getTankorCsoportTankoreiByTankorId($tankorId, $olr='') {

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) $lr = db_connect('naplo_intezmeny'); else $lr = $olr;

        // A tankör csoportjának lekérdezése
        $q = "SELECT tankorId FROM ".__TANEVDBNEV.".tankorCsoport
		WHERE csoportId=(SELECT csoportId FROM ".__TANEVDBNEV.".tankorCsoport
            			    WHERE tankorId=%u)";
        $ret = db_query($q, array(
	    'fv' => 'getTankorCsoportTankoreiByTankorId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tankorId)
	), $lr);
	if (is_array($ret) && count($ret) == 0) $ret = array($tankorId);

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);

	return $ret;

    }    

    function getTankorLetszam($tankorId,$ADAT=array('refDt'=>'', 'tolDt'=>'', 'igDt'=>''),$olr='') {

	if ($olr!='') $lr = $olr; else $lr = db_connect('naplo_intezmeny');

	if ($ADAT['refDt']!='') $tolDt=$igDt = $ADAT['refDt'];
	else {
    	    $tolDt = $ADAT['tolDt'];
    	    $igDt  = $ADAT['igDt'];
	}
        $q = "SELECT count(*) AS v FROM tankorDiak WHERE tankorId=%u AND beDt<='%s' AND (kiDt is null OR kiDt>='%s')";
	$v = array($tankorId, $igDt, $tolDt);
        $r = db_query($q, array('fv' => 'getTankorLetszam(share)', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v),$lr);

	if ($olr=='') db_close($lr);
	return $r;

    }

    function getTankorLetszamOsztalyonkent($tankorId,$ADAT=array('tanev'=>'','refDt'=>'', 'tolDt'=>'', 'igDt'=>''),$olr='') {

	if ($olr!='') $lr = $olr; else $lr = db_connect('naplo_intezmeny');

	if ($ADAT['refDt']!='') $tolDt=$igDt = $ADAT['refDt'];
	else {
    	    $tolDt = $ADAT['tolDt'];
    	    $igDt  = $ADAT['igDt'];
	}
	$tanev = ($ADAT['tanev']=='') ? __TANEV : $ADAT['tanev'];
        $q = "SELECT diakId FROM tankorDiak WHERE tankorId=%u AND tankorDiak.beDt<='%s' AND (tankorDiak.kiDt is null OR tankorDiak.kiDt>='%s') ORDER BY diakId";
	$v = array($tankorId, $igDt, $tolDt);
	$r = db_query($q, array('fv' => 'getTankorLetszamOsztalyonkent(share)', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v),$lr);

	for ($i=0; $i<count($r); $i++) {	
	    $diakId = $r[$i]['diakId'];
	    // ez a lassú
	    $o = getDiakOsztalya($diakId,array('tanev'=>$tanev,'tolDt'=>$tolDt,'igDt'=>$igDt),$lr);
	    $RE[$o[0]['osztalyJel']] ++;
	}
	if (is_array($RE)) ksort($RE);
	if ($olr=='') db_close($lr);
        return $RE ;

    }

    function getTankorOraszamByTanev($tanev, $tankorIds = array()) {

	global $_TANEV;

	if ($tanev == __TANEV) $TA = $_TANEV;
	else $TA = getTanevAdat($tanev);

	$szemeszterSzam = count($TA['szemeszter']);
	if ($szemeszterSzam == 0) {
	    return false;
	}

	if (is_array($tankorIds) && count($tankorIds) > 0) {
	    $TANKOR_WHERE = ' tankorId IN ('.implode(',', array_fill(0, count($tankorIds), '%u')).') AND ';
	    $v = $tankorIds;
	} else {
	    $TANKOR_WHERE = '';
	    $v = array();
	}
	$return = array();
	$q = "SELECT tankorId, SUM(oraszam)/%u AS oraszam FROM tankorSzemeszter WHERE $TANKOR_WHERE tanev=%u GROUP BY tankorId";
	array_unshift($v, $szemeszterSzam); $v[] = $tanev;
	$ret = db_query($q, array(
	    'fv' => 'getTankorOraszamByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $v
	));
	if (is_array($ret)) foreach ($ret as $tankorId => $tAdat) $return[$tankorId] = $tAdat['oraszam'];

	return $return;

    }

    function tankorTanarRendbenE($tanev, $dt) {


	$q = "SELECT DISTINCT tankorSzemeszter.tankorId AS tankorId, tankorNev 
		FROM tankorSzemeszter LEFT JOIN tankorTanar 
		ON tankorSzemeszter.tankorId=tankorTanar.tankorId AND kiDt>='%s' AND beDt<='%s' 
		WHERE tanev=%u AND beDt IS NULL";
	$v = array($dt, $dt, $tanev);
	$ret = db_query($q, array('fv' => 'tankorTanarRendbenE', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	if (count($ret) > 0) {
	    $T = array();
	    for ($i = 0; $i < count($ret); $i++) {
		$T[] = $ret[$i]['tankorNev'].' ('.$ret[$i]['tankorId'].')';
	    }
	    $_SESSION['alert'][] = 'message:wrong_data:Hiányzó tanár hozzárendelés:'.implode(', ', $T);
	    return false;
	} else { return true; }

    }

    function getTankorTipusok($SET = array('óratervi'=>null, 'tanórán kívüli'=>null)) {

	$q = "SELECT * FROM tankorTipus";
	if ($SET['óratervi'] == true) $q .= " WHERE oratervi='óratervi'";
	elseif ($SET['tanórán kívüli'] == true) $q .= " WHERE oratervi='tanórán kívüli'";
	$r = db_query($q, array('fv' => 'getTankorTipusok', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield'=>'tankorTipusId'));
        return $r;
	//return getEnumField('naplo_intezmeny', 'tankor', 'tankorTipus');

    }

    function getTankorNevById($tankorId, $SET = array('tanev'=>__TANEV)) {

        $q = "SELECT DISTINCT tankorNev FROM tankor 
		LEFT JOIN tankorSzemeszter ON (tankor.tankorId=tankorSzemeszter.tankorId AND tankorSzemeszter.tanev=%u) 
		WHERE tankor.tankorId=%u";

        return db_query($q, array('fv' => 'gettankorNevById', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($SET['tanev'],$tankorId)));
    }

    function getTankorTervezettOraszamok($tankorIds) {
	// A tankör adott tanévre tervezett óraszámai
	$q = "select tankorId, szemeszter, oraszam from tankorSzemeszter 
		where tanev=".__TANEV." and tankorId in (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		order by tankorId, szemeszter";
	$ret = db_query($q, array('fv'=>'getTankorTervezettOraszamok/tsz','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$tankorIds));
	$return = array();
	foreach ($ret as $adat) {
	    $return[ $adat['tankorId'] ]['bontasOraszam'][0] = $return[ $adat['tankorId'] ]['bontasOraszam'][1] = array();
	    if ($adat['szemeszter'] == 2 && !isset($return[ $adat['tankorId'] ]['oraszam'][0])) $return[ $adat['tankorId'] ]['oraszam'][0] = 0;
	    $return[ $adat['tankorId'] ]['oraszam'][ $adat['szemeszter']-1 ] = $adat['oraszam'];
	}
	// a bontásokkal tárgytípusonként lekötött óraszám - elvileg típusonként azonos hetiOraszam szerepelhet csak, ezét a max() ezek egyikét adja vissza
	$q = "select tankorId, tipus, szemeszter, max(bontasTankor.hetiOraszam) as hetiOraszam 
		from bontasTankor left join kepzesTargyBontas using (bontasId) 
		left join ".__INTEZMENYDBNEV.".kepzesOraterv using (kepzesOratervId) 
		where tankorId in (".implode(',', array_fill(0, count($tankorIds), '%u')).") 
		group by tankorId, tipus, szemeszter;";
	$ret = db_query($q, array('fv'=>'getTankorTervezettOraszamok/bt','modul'=>'naplo','result'=>'indexed','values'=>$tankorIds));
	foreach ($ret as $adat) {
	    $return[ $adat['tankorId'] ]['bontasOraszam'][ $adat['szemeszter']-1 ][] = array('tipus'=>$adat['tipus'],'hetiOraszam'=>$adat['hetiOraszam']);
	}
	return $return;
    }

    function getOratervenKivuliTankorIds() {

        global $_TANEV;

	$tankorTipusok = getTankorTipusok(array('óratervi'=>true));
	$oraterviTipusIds = array_keys($tankorTipusok);
	// óratervi tankörök lekérdezése
	$qOratervi = "SELECT tankorId FROM ".__INTEZMENYDBNEV.".tankor WHERE tankorTipusId IN (".implode(',', array_fill(0, count($oraterviTipusIds), '%u')).")";
	$v = $oraterviTipusIds;	
	// a bontásokkal tárgytípusonként lekötött óraszám - elvileg típusonként azonos hetiOraszam szerepelhet csak, ezét a max() ezek egyikét adja vissza
	$subQ = "select tankorId, tipus, szemeszter, max(bontasTankor.hetiOraszam) as hetiOraszam 
		from bontasTankor left join kepzesTargyBontas using (bontasId) 
		left join ".__INTEZMENYDBNEV.".kepzesOraterv using (kepzesOratervId) 
		group by tankorId, tipus, szemeszter";
	// bontés óraszámok összesítése
	$tblQ = "select tankorId, szemeszter, sum(hetiOraszam) as bontasOraszam from (".$subQ.") as subQuery group by tankorId, szemeszter";
	// bontás óraszámok és tankör óraszámok összevetése
	$q = "select tankorId, tankorNev, sum(oraszam-bontasOraszam) as diff from ".__INTEZMENYDBNEV.".tankorSzemeszter 
		left join (".$tblQ.") as tankorBontasOraszam using (tankorId, szemeszter) 
		where tanev=".__TANEV." and (tankorBontasOraszam.tankorId is null or bontasOraszam<>oraszam) 
		and tankorId in (".$qOratervi.")
		group by tankorId";
		// , tankorNev --  sql_mode=only_full_group_by miatt került be a tankorNev...
        $return = db_query($q, array('debug'=>false,'fv'=>'getOratervenKivuliTankorIds','modul'=>'naplo','result'=>'indexed','values'=>$v));

	return $return;

    }

    function getTankorJelenletKotelezoE($tankorId) {
	$q = "SELECT jelenlet FROM tankor LEFT JOIN tankorTipus USING (tankorTipusId) WHERE tankorId=%u";
	$v = array($tankorId);
	$r = db_query($q, array('fv' => 'getTankorJelenletKotelezoE', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));
	return ($r == 'kötelező');
    }

?>
