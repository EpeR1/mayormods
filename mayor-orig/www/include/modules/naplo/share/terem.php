<?php

    function getTeremAdatById($teremId) {

	$q = "SELECT * FROM terem WHERE teremId=%u";
	return db_query($q, array('fv' => 'getTeremAdatById', 'modul' => 'naplo_intezmeny', 'values' => array($teremId), 'result' => 'record'));

    }

    function getTermek($SET = array('result' => 'indexed', 'tipus' => array(), 'telephelyId' => null), $olr = null) {
	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed', 'idonly', 'assoc'));
	$v = array();

	if (is_array($SET['tipus']) && count($SET['tipus'])>0) {
	    $W[] = " tipus IN ('".implode("','", array_fill(0, count($SET['tipus']), '%s'))."')";
	    $v = $SET['tipus'];
	} else {
	    $W[] = " tipus != 'megszűnt' ";
	}
	if ($SET['telephelyId']!='') {
	    $W[] = " (telephelyId=%u OR telephelyId IS NULL) "; // vagy set vagy null
	    $v[] = $SET['telephelyId'];
	}

	if (count($W) > 0) $WHERE = ' WHERE '.implode(' AND ', $W);

	if ($result == 'idonly') {
	    $q = "SELECT teremId FROM ".__INTEZMENYDBNEV.".terem".$WHERE." ORDER BY teremId";
	} else {
	    $q = "SELECT * FROM ".__INTEZMENYDBNEV.".terem".$WHERE." ORDER BY teremId";
	}
	$R = db_query($q, array('fv' => 'getTerem', 'modul' => 'naplo_intezmeny', 'result' => $result, 'values' => $v, 'keyfield' => 'teremId'), $olr);
	return $R; 
    }    

    function getSzabadTermekByDt($dt, $teremIds = array(), $forras = 'orarendiOra', $olr = '') {

	if (!is_array($teremIds) || count($teremIds) == 0) $teremIds = getTermek(array('result' => 'idonly', 'tipus'=>array('tanterem','labor')), $olr);
	$szabadTermek = $foglaltTermek = array();
	if ($forras == 'ora') 
	    $q = "SELECT DISTINCT ora,teremId FROM ".__TANEVDBNEV.".ora
		WHERE dt='%s' ORDER BY ora,teremId";
	else
	    $q = "SELECT DISTINCT ora,teremId FROM ".__TANEVDBNEV.".nap LEFT JOIN ".__TANEVDBNEV.".orarendiOra
                    ON (((DAYOFWEEK(dt)+5) MOD 7)+1 = orarendiOra.nap)
                    AND orarendiOra.het=nap.orarendiHet
                    AND orarendiOra.tolDt<=dt AND orarendiOra.igDt>=dt 
		WHERE dt='%s' ORDER BY ora,teremId";
	$ret = db_query($q, array('fv' => 'getSzabadTermekByDt', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($dt)), $olr);
	for ($i = 0; $i < count($ret); $i++) $foglaltTermek[$ret[$i]['ora']][] = $ret[$i]['teremId'];
	foreach ($foglaltTermek as $ora => $fTermek) {
	    $szT = array_diff($teremIds, $fTermek);
	    // reindex
	    foreach($szT as $_key => $_value) 
		$szabadTermek[$ora][] = intval($_value);
	}
	return $szabadTermek;

    }

    function getSzabadTermekByDtInterval($tolDt, $igDt, $teremIds, $forras = 'orarendiOra') {

	if (!is_array($teremIds)) $teremIds = getTermek(array('result' => 'idonly', 'tipus'=>array('tanterem','szaktanterem','osztályterem','labor','gépterem','tornaterem','előadó')));

	for ($dt = $tolDt; strtotime($dt) <= strtotime($igDt); $dt = date('Y-m-d', strtotime('+1 days', strtotime($dt)))) {
		$szabadTermek[$dt] = getSzabadTermekByDt($dt, $teremIds, $forras);
	}

	return $szabadTermek;
    }

    function getFoglaltTeremekByOrarendiOra($SET = array('tanev' => __TANEV, 'dt' => null, 'het' => null, 'nap' => null, 'ora' => null)) {


        $dt = readVariable($SET['dt'], 'datetime', null);
        initTolIgDt($SET['tanev'], $dt, $dt);

        // Ha van dátum, de nincs hét, nap óra, akkor azt a dátum alapján kellene beállítani)
        if (isset($SET['het']) && isset($SET['nap']) && isset($SET['ora']) && isset($dt) && isset($SET['tanev'])) {

            $tanevDb = tanevDbNev(__INTEZMENY, $SET['tanev']);
	    $q = "SELECT * FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
                    WHERE tolDt <= '%s' AND '%s' <= igDt 
		    AND het=%u AND nap=%u AND ora=%u
		    AND teremId IS NOT NULL
		    ORDER BY teremId";
	    $v = array($tanevDb, $tanevDb, $dt, $dt, $SET['het'], $SET['nap'], $SET['ora']);
            return db_query($q, array('fv' => 'getFoglaltTermekByOrarendiOra', 'modul' => 'naplo_intezmeny', 'keyfield' => 'teremId', 'result' => 'assoc', 'values' => $v));

        } else {
            $_SESSION['alert'][] = 'message:empty_field:het,nap,ora,tanev,dt';
            return false;
        }


    }

    function getTeremPreferencia($SET = array('telephelyId' => null,'teremPreferenciaId'=>null)) {

	if ($SET['teremPreferenciaId']!='') {
	    $W = 'WHERE teremPreferenciaId=%u';
	    $v = array($SET['teremPreferenciaId']);
	} else {
	    $W='';
	    $v = array();
	}
	$q = "SELECT * FROM teremPreferencia $W ORDER BY teremPreferenciaId";
        return db_query($q, array('fv' => 'getFoglaltTermekByOrarendiOra', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

    }

    // teremModifier

    function teremModositas($ADAT) {

	if (MAYOR_SOCIAL === true) $ADAT['tanev'] = __TANEV;

        $dt = readVariable($ADAT['dt'], 'datetime', null);
        initTolIgDt($ADAT['tanev'], $dt, $dt);
        $tanevDb = tanevDbNev(__INTEZMENY, $ADAT['tanev']);

        $return = false;

        if (is_array($ADAT['foglaltTermek'][ $ADAT['teremId'] ])) {
            $return = $ADAT['foglaltTermek'][ $ADAT['teremId'] ]['tanarId'];
            // A foglalt terem felszabadítása
            $q = "UPDATE `%s`.orarendiOra SET teremId=NULL WHERE tolDt<='%s' AND '%s'<=igDt AND het=%u AND nap=%u AND ora=%u AND teremId=%u";
            $v = array($tanevDb, $dt, $dt, $ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['teremId']);
            db_query($q, array('fv' => 'teremModositas/foglalt terem felszabadítása', 'modul' => 'naplo', 'values' => $v));
        }
        // teremhozzárendelés módosítása
	if ($ADAT['teremId']>0) {
    	    $q = "UPDATE `%s`.orarendiOra SET teremId=%u WHERE tolDt <= '%s' AND '%s' <= igDt AND het=%u AND nap=%u AND ora=%u AND tanarId=%u";
    	    $v = array($tanevDb, $ADAT['teremId'], $dt, $dt, $ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['tanarId']);
	} else {
    	    $q = "UPDATE `%s`.orarendiOra SET teremId=NULL WHERE tolDt <= '%s' AND '%s' <= igDt AND het=%u AND nap=%u AND ora=%u AND tanarId=%u AND teremId IS NOT NULL";
    	    $v = array($tanevDb, $dt, $dt, $ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['tanarId']);
	}
        db_query($q, array('debug'=>true,'fv' => 'teremModositas/foglalt terem felszabadítása', 'modul' => 'naplo', 'values' => $v));

        return $return;

    }

?>
