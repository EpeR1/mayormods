<?php

    function getSzemeszterek($SET = array('statusz' => array('aktív', 'lezárt', 'tervezett'), 'filter' => array())) {
	global $mayorCache;
        if (!isset($SET)) $SET = array('statusz' => array('aktív','lezárt','tervezett'));

	$key = __FUNCTION__.':'.md5(serialize($SET));
	if ($mayorCache->exists($key)) return $mayorCache->get($key);

        $q = "SELECT * FROM szemeszter ";
	if (is_array($SET['statusz']) && count($SET['statusz']) != 0) {
	    $q .="WHERE statusz IN ('".implode("','", array_fill(0, count($SET['statusz']), '%s'))."')";
	    $v = $SET['statusz'];
	    $kapocs = "AND ";
	} else { 
	    $kapocs = "WHERE "; $v = array(); 
	}
	if (is_array($SET['filter']) && count($SET['filter']) != 0) {
	    $q .= $kapocs . implode(' AND ',$SET['filter']);
	}
	$q .= ' ORDER BY tanev,szemeszter';
        $r = db_query($q, array('fv' => 'getSzemeszterek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	$mayorCache->set($key,$r,'szemeszter');
	return $r;
    }

    function getSzemeszterByDt($dt, $tipus = 0) {
	/*
	     0 - a szemeszter tartalmazza a dátumot
	     1 - a dátum napján, vagy utána végződő első szemeszter
	    -1 - a dátum napján vagy előtte kezdődő első szemeszter 
	*/
	if ($tipus < 0) $q = "SELECT * FROM szemeszter WHERE szemeszter.kezdesDt <= '%s' ORDER BY kezdesDt DESC LIMIT 1";
	elseif ($tipus > 0) $q = "SELECT * FROM szemeszter WHERE szemeszter.zarasDt >= '%s' ORDER BY zarasDt LIMIT 1";
        else $q = "SELECT * FROM szemeszter WHERE szemeszter.kezdesDt <= '%s' AND szemeszter.zarasDt >= '%s'";
	$v = array($dt, $dt);
	return db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'getSzemeszterByDt', 'result' => 'record', 'values' => $v, 'debug' => false));
    }

    function getSzemeszterIdByDt($dt) {
        $q = "SELECT szemeszterId FROM szemeszter WHERE szemeszter.kezdesDt<='%s' AND szemeszter.zarasDt>='%s'";
	return db_query($q, array('fv' => 'getSzemeszterIdByDt', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($dt, $dt)));		       
    }

    function getKovetkezoSzemeszterId($tanev,$szemeszter,$aktiv=null) {
	if ($aktiv!='') $W = ' AND statusz="aktív" ';
	$q = "SELECT szemeszterId FROM szemeszter WHERE CONCAT(szemeszter.tanev,szemeszter)>'%s' $W ORDER BY tanev,szemeszter LIMIT 1";
	return db_query($q, array(
	    'fv' => 'getKovetkezoSzemeszterId', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tanev.$szemeszter)
	));
    }

    function getFelevByDt($dt) {
        $q = "SELECT szemeszter AS felev FROM szemeszter WHERE szemeszter.kezdesDt<='%s' AND szemeszter.zarasDt>='%s'";
	$v = array($dt,$dt);
	return db_query($q, array('fv' => 'getFelevByDt', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));
    }

    function getTanevekByDt($dt) {
        $q = "SELECT DISTINCT tanev FROM szemeszter WHERE szemeszter.kezdesDt<='%s' and szemeszter.zarasDt>='%s'";
	$v = array($dt, $dt);
	return db_query($q, array('fv' => 'getTanevByDt', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
    }

    function getTanevekByDtInterval($dt1, $dt2 = '', $statusz = array('aktív','lezárt','tervezett')) {
	/*
	    A dátumot lefedő tanévek közül azok, amik státusza megfelelő
	*/
	$dt2 = readVariable($dt2, 'datetime', $dt1);
	$q = "SELECT DISTINCT tanev FROM szemeszter WHERE statusz in ('".implode("','", array_fill(0, count($statusz), '%s'))."') GROUP BY tanev 
		HAVING '$dt1'<=MAX(szemeszter.zarasDt) AND MIN(szemeszter.kezdesDt)<='$dt2'";
	array_push($statusz, $dt1, $dt2);
	return db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'getTanevByDtInterval', 'result' => 'idonly', 'values' => $statusz));
    }

    function getTanevekByStatusz($statusz = array('aktív','lezárt','tervezett')) {
	$q = "SELECT DISTINCT tanev FROM szemeszter WHERE statusz in ('".implode("','", array_fill(0, count($statusz), '%s'))."') GROUP BY tanev";
	return db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'getTanevByDtInterval', 'result' => 'idonly', 'values'=>$statusz));
    }

    function getTanevSzemeszterek($tanev) {
        $q = "SELECT * FROM szemeszter WHERE tanev=%u";
	return db_query($q, array('fv' => 'getTanevSzemeszterek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanev)));
    }

    function getSzemeszterAdatById($szemeszterId) {
        $q = "SELECT * FROM szemeszter WHERE szemeszterId = %u";
	$ret = db_query($q, array('fv' => 'getSzemeszterAdatById', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($szemeszterId)));
	if (!is_array($ret)) return false;

        $ret['idoszak'] = getIdoszakByTanev(array('tanev' => $ret['tanev'], 'szemeszter' => $ret['szemeszter']));
        $q = "SELECT tanev,MAX(szemeszter) AS maxSzemeszter, MIN(kezdesDt) AS kezdesDt, MAX(zarasDt) AS zarasDt FROM szemeszter
                WHERE tanev=%u GROUP BY tanev";
        $ret['tanevAdat'] = db_query($q, array(
	    'fv' => 'getSzemeszterAdatById', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($ret['tanev'])
	));
        return $ret;
    }

    function getSzemeszterIdBySzemeszter($tanev, $szemeszter) {

	$q = "SELECT szemeszterId FROM szemeszter WHERE tanev=%u AND szemeszter=%u";
	$v = array($tanev, $szemeszter);
	return db_query($q, array('fv' => 'getSzemeszterIdBySzemeszter', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));

    }
/* (Még) nem használt függvény

    function getSzemeszterAdatBySzemeszter($tanev, $szemeszter) {

	$szemeszterId = getSzemeszterIdBySzemeszter($tanev, $szemeszter);
	if ($szemeszterId === false) return false;
	return getSzemeszterAdatById($szemeszterId);

    }

*/
    function getIdoszakByTanev($SET = array('tanev' => __TANEV, 'szemeszter' => '', 'tipus' => array(), 'tolDt' => '', 'igDt' => '', 'return' => '', 'arraymap'=>null)) {

	if ($SET['tanev']=='') $SET['tanev'] = __TANEV;

        if ($SET['szemeszter'] != '') {
	    $q = "SELECT * FROM idoszak WHERE tanev=%u AND szemeszter=%u";
	    $v = array($SET['tanev'], $SET['szemeszter']);
        } else {
	    $q = "SELECT * FROM idoszak WHERE tanev=%u";
	    $v = array($SET['tanev']);
	}
        if (is_array($SET['tipus']) && count($SET['tipus']) > 0) {
	    $q .= " AND tipus IN ('".implode("','", array_fill(0, count($SET['tipus']), '%s'))."')";
	    $v = mayor_array_join($v, $SET['tipus']);
	}
        if ($SET['tolDt'] != '') {
	    $q .= " AND igDt >= '%s'";
	    $v[] = $SET['tolDt'];
	}
        if ($SET['igDt'] != '') {
	    $q .= " AND tolDt <= '%s'";
	    $v[] = $SET['igDt'];
	}

        $q .= " ORDER BY szemeszter,tolDt,igDt";
        $ret = db_query($q, array('fv' => 'getIdoszakByTanev', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

        if ($SET['return'] == 'assoc') {
            $tmp = array();
            for ($i = 0; $i < count($ret); $i++) {
                $tmp[ $ret[$i]['szemeszter'] ][ $ret[$i]['tipus'] ][] = $ret[$i];
            }
            $ret = $tmp;
        }

	if (is_array($SET['arraymap'])) {
	    $ret = reindex($ret,$SET['arraymap']);
	}

        return $ret;

    }


    function getTanevAdatBySzemeszterId( $szemeszterId ) {
	if ($szemeszterId=='') return false;
	$q = "SELECT * FROM szemeszter WHERE szemeszterId=%u";
	$v = array($szemeszterId);
	$R['szemeszter'] = db_query($q, array(
	    'fv' => 'getTanevAdatBySzemeszterId', 'modul'=>'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'szemeszter', 'values' => $v
	));
	// legyen az első az alapértelmezett
	$R['statusz'] = $R['szemeszter'][1]['statusz'];
	$R['tanev'] = $R['szemeszter'][1]['tanev'];
	// a két dárum innen hiányzik!
	return $R;
    }

?>
