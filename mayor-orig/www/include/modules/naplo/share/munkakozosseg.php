<?php

    function getMunkakozossegByTargyId($targyId) {
	$q = "SELECT mkId FROM targy WHERE targyId=%u";
	$mkId = db_query($q, array('fv' => 'getMunkakozossegByTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values'=>array($targyId)));
	return getMunkakozossegById($mkId);    
    }

    function getMunkakozossegById($id) {
	return getMunkakozossegek(array("mkId=$id"),array('result' => 'record'));    
    }

    function getMunkakozossegek($FILTER=array(),$SET=array('result' => 'indexed')) {


	if ($SET['result'] == '') $SET['result'] = 'indexed';
        $RESULT = array();

	/* Általános filterező */
	$QW = '';
	if (is_array($FILTER) && count($FILTER)>0) {
	    $QW = " WHERE ".implode(' AND ',$FILTER);
	}
	if ($SET['idonly']===true || $SET['csakId']===true) $fields = "mkId";
	else $fields="mkId,mkId AS munkakozossegId,leiras,leiras as mkNev,leiras as munkakozossegNev, mkVezId,TRIM((CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev))) AS mkVezNev";
        $q = "SELECT $fields FROM munkakozosseg LEFT JOIN tanar ON mkVezId=tanarId".$QW.' ORDER BY leiras';
	$RESULT = db_query($q, array('modul' => 'naplo_intezmeny', 'fv' => 'getMunkakozossegek', 'result' => $SET['result']));

        return $RESULT;

    }

    function getMunkakozossegByTanarId($tanarId, $SET = array('result' => 'idonly')) {

	if ($SET['csakId']===true) $SET['result'] = 'idonly'; // Az egységesíítés nevében :o)
	if ($SET['result'] == 'idonly') {
	    $q = "SELECT mkId AS munkakozossegId FROM mkTanar WHERE tanarId=%u";
	    return db_query($q, array('fv' => 'getMunkakozossegByTanarId','modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tanarId)));
	} else {
    	    $q = "SELECT mkId,mkId AS munkakozossegId,leiras, leiras AS mkNev, leiras AS munkakozossegNev,mkVezId
		  FROM munkakozosseg LEFT JOIN mkTanar USING (mkId) WHERE tanarId=%u ORDER BY leiras";
	    return db_query($q, array('fv' => 'getMunkakozossegByTanarId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanarId)));
	}
	
    }

    function getVezetettMunkakozossegByTanarId($tanarId, $SET = array('result' => 'idonly'), $olr='') {

	$lr = ($olr!='') ? $olr : db_connect('naplo_intezmeny');
	if ($SET['csakId']===true) $SET['result'] = 'idonly'; // Az egységesíítés nevében :o)
	if ($SET['result'] == 'idonly') {
	    //$q = "SELECT mkId AS munkakozossegId FROM munkakozosseg WHERE mkVezId=%u";
	    $q = "SELECT DISTINCT mkId AS munkakozossegId FROM munkakozosseg LEFT JOIN mkVezeto USING (mkId) WHERE mkVezId=%u OR tanarId=%u";
	    $R = db_query($q, array('fv' => 'getVezetettMunkakozossegByTanarId','modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tanarId, $tanarId)), $lr);
	} else {
    	    //$q = "SELECT mkId,mkId AS munkakozossegId,leiras, leiras AS mkNev, leiras AS munkakozossegNev,mkVezId
	    //	  FROM munkakozosseg WHERE mkVezId=%u ORDER BY leiras";
    	    $q = "SELECT DISTINCT mkId,mkId AS munkakozossegId,leiras, leiras AS mkNev, leiras AS munkakozossegNev,mkVezId
		  FROM munkakozosseg LEFT JOIN mkVezeto USING (mkId) WHERE mkVezId=%u OR tanarId=%u ORDER BY leiras";
	    $R = db_query($q, array('fv' => 'getVezetettMunkakozossegByTanarId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanarId,$tanarId)),$lr);
	}
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getMunkakozossegNevById($munkakozossegId) {

        $q = "SELECT leiras AS munkakozossegNev FROM `munkakozosseg` WHERE mkId=%u";
        return db_query($q, array('fv' => 'getmunkakozossegNevById', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($munkakozossegId)));

    }

    function getMunkakozossegTanaraMatrix() {
	$q = "SELECT * FROM mkTanar";
	$r = db_query($q,array('fv' => '', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));
	for ($i=0; $i<count($r); $i++) {
	    $R['mkTanar'][$r[$i]['mkId']][] = $r[$i]['tanarId'];
	    $R['tanarMk'][$r[$i]['tanarId']][] = $r[$i]['mkId'];
	}
	return $R;
    }


?>