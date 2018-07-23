<?php

    if (file_exists("lang/$lang/module-naplo/share/tanar.php")) {
        require_once("lang/$lang/module-naplo/share/tanar.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/module-naplo/share/tanar.php')) {
        require_once('lang/'._DEFAULT_LANG.'/module-naplo/share/tanar.php');
    }

    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/osztaly.php');

    $tanarAttrs = array(
	'oId' => _TANAR_OID,
	'viseltNevElotag' => _TANAR_VNE,
	'viseltCsaladiNev' => _TANAR_VCSN,
	'viseltUtonev' => _TANAR_VUN,
	'szuletesiHely' => _TANAR_SZH,
	'szuletesiIdo' => _TANAR_SZI,
    );

    function getTanarok($SET = array('targyId'=> null,'mkId' => null, 'tanev' => __TANEV, 'beDt' => null, 'kiDt' => null, 'összes'=> false, 'override' => false, 'telephelyId'=>null, 'result' => 'indexed', 'extraAttrs' => null), $olr = '') {

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed', 'assoc'));
	$beDt = $SET['beDt']; $kiDt = $SET['kiDt'];
	initTolIgDt($SET['tanev'], $beDt, $kiDt, $SET['override']);

	$where = $v = array();
	if ($SET['összes']!==true && in_date_interval(date(),$beDt,$kiDt)==true) { // ha nincs benne a mai nap a vizsgált intervallumban, akkor értelmetlen (lehet) ez a feltétel
	    $where[] = "statusz IN ('határozatlan idejű','határozott idejű','külső óraadó')";
	}
	if ($beDt != '') {
	    $where[] = "((kiDt IS NULL) OR '%s' <= kiDt)";
	    array_push($v, $beDt);
	}
	if ($kiDt != '') { 
	    $where[] = "'%s' >= beDt";
	    array_push($v, $kiDt);
	}
	if ($SET['extraAttrs'] != '') $extraAttrs = ', '.$SET['extraAttrs'];

/*
	if ($SET['telephelyId'] != '') { 
	    $where[] = " (telephelyId = %u OR telephelyId IS NULL) ";
	    array_push($v, $SET['telephelyId']);
	}
	// 2015-08-06 - aktualisStatusz kiiktatása - statusz mező megjelenése miatt...
	IF( beDt <= CURDATE() AND (kiDt IS NULL OR CURDATE()<=kiDt),'jogviszonyban van','nincs jogviszonyban') as aktualisStatusz, 
*/
	if ($SET['targyId'] != '') {
	    if (count($where) > 0) $W = 'AND ' . implode(' AND ',$where);
	    
	    $q1 = "SELECT tanar.tanarId AS tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev, statusz, 
		    hetiKotelezoOraszam,hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam,hetiMunkaora
			$extraAttrs
		FROM ".__INTEZMENYDBNEV.".targy LEFT JOIN ".__INTEZMENYDBNEV.".mkTanar USING (mkId) LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId)
		WHERE targyId=%u AND tanarId IS NOT NULL ".$W;
	    $q2 = "SELECT tanar.tanarId AS tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev, statusz,
		    hetiKotelezoOraszam,hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam,hetiMunkaora
			$extraAttrs
		FROM ".__INTEZMENYDBNEV.".kepesitesTargy LEFT JOIN ".__INTEZMENYDBNEV.".tanarKepesites USING (kepesitesId) LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId)
		WHERE targyId=%u AND tanarId IS NOT NULL ".$W;
	    array_unshift($v, $SET['targyId']);
	    $tmp=$v; foreach ($tmp as $tmpV) $v[]=$tmpV;
	    $q = "(".$q1.") UNION DISTINCT (".$q2.") ORDER BY tanarNev,tanarId";
	} elseif ($SET['mkId'] == '') {
	    if (count($where) > 0) $W = 'WHERE ' . implode(' AND ',$where);
	    $q = "SELECT tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev, statusz,
		    hetiKotelezoOraszam,hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam,hetiMunkaora
			$extraAttrs
		FROM ".__INTEZMENYDBNEV.".tanar $W ORDER BY CONCAT_WS(' ', ViseltCsaladiNev, viseltUtoNev)";
	} else {
	    if (count($where) > 0) $W = 'AND ' . implode(' AND ',$where);
	    $q = "SELECT tanar.tanarId AS tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev, statusz,
		    hetiKotelezoOraszam,hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam,hetiMunkaora
			$extraAttrs
		FROM ".__INTEZMENYDBNEV.".tanar LEFT JOIN ".__INTEZMENYDBNEV.".mkTanar USING (tanarId)
		WHERE mkId=%u $W ORDER BY CONCAT_WS(' ', ViseltCsaladiNev, viseltUtoNev)";
	    array_unshift($v, $SET['mkId']);
	}
	return db_query($q, array('fv' => 'getTanarok', 'modul' => 'naplo_intezmeny', 'result' => $result, 'keyfield' => 'tanarId', 'values' => $v));

    }

    function getTanarNevById($tanarId, $olr = null) {

	$q = "SELECT TRIM(CONCAT_WS(' ', viseltNevElotag, viseltCsaladiNev, viseltUtonev)) AS tanarNev
		FROM ".__INTEZMENYDBNEV.".tanar WHERE tanarId=%u";
	return db_query($q, array('fv' => 'getTanarNevById', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tanarId)), $olr);

    }

    function getTanarMunkakozosseg($tanarId) {
	$q = "SELECT mkId FROM mkTanar WHERE tanarId=%u";
	return db_query($q, array('fv' => 'getTanarMunkakozosseg', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tanarId)));
    }

    function getSzabadTanarok($dt, $ora, $olr = '') {

	$q = "SELECT tanar.tanarId AS tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev
		FROM ".__INTEZMENYDBNEV.".tanar LEFT JOIN ora ON tanarId=ki AND dt='%1\$s' and ora=%2\$u
		WHERE ki IS NULL AND beDt<='%1\$s' and (kiDt IS NULL OR kiDt>='%1\$s')
		ORDER BY CONCAT_WS(' ', ViseltCsaladiNev, viseltUtoNev)";
	$v = array($dt, $ora);
	return db_query($q, array('fv' => 'getSzabadTanarok', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $olr);
    }

    function getFoglaltTanarok($dt, $ora, $olr = '') {

	$q = "SELECT DISTINCT tanar.tanarId AS tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev
		FROM ".__INTEZMENYDBNEV.".tanar LEFT JOIN ora ON tanarId=ki AND dt='%1\$s' and ora=%2\$u
		WHERE ki IS NOT NULL AND beDt<='%1\$s' and (kiDt IS NULL OR kiDt>='%1\$s')
		ORDER BY CONCAT_WS(' ', ViseltCsaladiNev, viseltUtoNev)";
	$v = array($dt, $ora);
	return db_query($q, array('fv' => 'getFoglaltTanarok', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $olr);
    }

    function getTanarAdatById($tanarIds, $olr = '') {

        if (!is_array($tanarIds)) $tanarIds = array($tanarIds);
        $q = "SELECT *,TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev FROM ".__INTEZMENYDBNEV.".tanar WHERE tanarId IN (".implode(',', array_fill(0, count($tanarIds), '%u')).")";
	$R = db_query($q, array('fv' => 'getTanarAdatById', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $tanarIds));
//	for ($i=0; $i<count($R); $i++) {
//	    $q2 = "SELECT osztalyId
//	    $R[$i]['osztalya'] = 
//	}
	return $R;
    }

    function getTanarOsztaly($tanarId, $SET = array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result'=>null), $olr='') {

        global $_TANEV;
	$tanev = ($SET['tanev'] =='') ? __TANEV: $SET['tanev'];
        $tolDt = $SET['tolDt']; 
	$igDt=$SET['igDt']; 
        initTolIgDt($tanev, $tolDt, $igDt);
	$lr = ($olr!='') ? $olr : db_connect('naplo_intezmeny');

        $RESULT = array();

	    // tankorTanar (be ki) --> tankorDiak (be ki) --> osztaly-Diak (be ki)
	    $TANKORIDK = getTankorByTanarId($tanarId, $tanev, array('csakId' => true),$lr);
	    $DIAK = getTankorDiakjaiByInterval($TANKORIDK, $tanev, $tolDt, $igDt, $lr);
	    $SET2=$SET;
	    $SET2['result'] = 'csakId';
	    $OSZTALYIDK = getDiakokOsztalyai($DIAK['idk'], $SET2, $lr);

	if ($SET['result']==='csakId' || $SET['result']==='idonly') {
	    $RESULT = $OSZTALYIDK;
	} else {
	    require_once('include/modules/naplo/share/kepzes.php');
	    for ($i=0; $i<count($OSZTALYIDK); $i++) {
		$RESULT[$i] = getOsztalyAdat($OSZTALYIDK[$i], null, $lr); // null=tanev
	    }
	}
	if ($olr=='') db_close($lr);
        return $RESULT;
    }

    function getTanarOraszam($tanarId,$tanev='') {
	if ($tanev=='') $tanev=__TANEV;
	$q = "SELECT sum(d) FROM (SELECT tankorId, avg(oraszam) AS d FROM tankorSzemeszter
	LEFT JOIN tankorTanar USING (tankorId)
	WHERE tanarId=%u AND tanev=%u GROUP BY tankorId) AS a";
	$v = array($tanarId, $tanev);
	return db_query($q, array('fv' => 'getTanarOraszam', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v), $olr);
    }
/*
    NOT IMPLEMENTED

    function getTanarTelephely($tanarId) {

	$q = "SELECT * FROM tanarTelephely WHERE tanarId=%u";
	$v = array($tanarId);
	$r = db_query($q, array('fv' => 'getTanarOraszam', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $olr);
	for ($i=0; $i<count($r); $i++) {
	    $RET[$r[$i]['tanarId']][] = $r[$i]['telephelyId']; 
	}
	return $RET;
    }

    function getTelephelyTanar($telephelyId) {

	$q = "SELECT * FROM tanarTelephely WHERE telephelyId=%u";
	$v = array($telephelyId);
	$r = db_query($q, array('fv' => 'getTanarOraszam', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $olr);
	for ($i=0; $i<count($r); $i++) {
	    $RET[$r[$i]['telephelyId']][] = $r[$i]['tanarId']; 
	}
	return $RET;
    }
*/

?>
