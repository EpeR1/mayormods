<?php

    function getBejegyzesTipusById($btId) {
	$q = "SELECT * FROM bejegyzesTipus WHERE bejegyzesTipusId=%u";
	return db_query($q, array('fv' => 'getBejegyzesTipusById','modul'=>'naplo_intezmeny','values'=>array($btId),'result'=>'record'));
    }

    function getDarabBejegyzes($ADAT) {

	$q = "SELECT COUNT(*) FROM bejegyzes WHERE beirasDt>='%s' AND diakId=%u";
	return db_query($q, array('fv' => 'getDarabBejegyzes', 'modul' => 'naplo', 'result' => 'value', 'values' => array($ADAT['tolDt'], $ADAT['diakId'])));

    }

    function getDiakBejegyzesekByTanev($diakId, $tanev) {

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);

        $q = "SELECT * FROM `%s`.bejegyzes LEFT JOIN `%s`.`bejegyzesTipus` USING (`bejegyzesTipusId`) WHERE diakId=%u ORDER BY beirasDt";
	$v = array($tanevDb, __INTEZMENYDBNEV, $diakId);
        $ret = db_query($q, array(
            'fv' => 'getDiakBejegyzesekByTanev', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v
        ));
        for ($i = 0; $i < count($ret); $i++)
            if ($ret[$i]['tanarId'] != '') $ret[$i]['tanarNev'] = getTanarNevById($ret[$i]['tanarId']);

        return $ret;

    }

    function getBejegyzesTipusokByJogosult($jogosult, $SET = array('tipus' => array('fegyelmi','dicséret','üzenet'), 'hianyzas' => false, 'dt' => null)) {

	if (!is_array($jogosult) || count($jogosult) == 0) return false;
	$dt = isset($SET['dt'])?$SET['dt']:date('Y-m-d');

	$W = array();
	foreach ($jogosult as $j) $W[] = "jogosult LIKE '%%$j%%'";

	if ($SET['hianyzas'] === true) $WH = "AND hianyzasDb > 0 ";

	$q = "SELECT * FROM bejegyzesTipus WHERE (".implode(' OR ', $W).") ".$WH."AND tipus IN ('".implode("','", $SET['tipus'])."') 
		AND tolDt<='%s' AND (igDt IS NULL OR '%s'<=igDt)
		ORDER BY tipus,fokozat";
	$v = array($dt, $dt);
	return db_query($q, array('fv' => 'getBejegyzesTipusokByJogosult', 'modul' => 'naplo_intezmeny', 'result' => 'indexed','values' => $v));

    }

    function getTorzslapBejegyzesByDiakIds($diakIds, $SET = array('tanev'=>__TANEV)) {

	if (!is_array($diakIds)) $diakIds = array($diakIds);
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	if ($tanev == __TANEV) { global $_TANEV; $TA = $_TANEV; }
	else { $TA = getTanevAdat($tanev); }

	$q = "SELECT * FROM `%s`.`bejegyzes` WHERE `referenciaDt`='%s' AND `diakId` IN ('".implode("','", array_fill(0, count($diakIds), '%u'))."') ORDER BY `beirasDt`";
	$v = $diakIds; array_unshift($v, tanevDbNev(__INTEZMENY, $tanev), $TA['zarasDt']);
        $ret = db_query($q, array(
            'fv' => 'getTorzslapBejegyzesByDiakIds', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield'=>'diakId', 'values' => $v
        ));

	return $ret;
    }

?>
