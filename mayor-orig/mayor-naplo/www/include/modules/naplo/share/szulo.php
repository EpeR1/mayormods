<?php

    function getSzuloAdat($szuloId) {

        $q = "SELECT *, TRIM(CONCAT_WS(' ',nevElotag, csaladiNev, utoNev)) AS szuloNev FROM szulo WHERE szuloId=%u";
        return db_query($q, array('fv'=>'getSzuloAdat','modul'=>'naplo_intezmeny','result'=>'record','values'=>array($szuloId)));

    }

    function getSzulok($SET = array('csakId'=>false,'result'=>'','szuloIds'=>array())) {

	if (is_array($SET['szuloIds']) && count($SET['szuloIds']) > 0 && count($SET['szuloIds']) < 50 ) $W_SZULO = ' WHERE szuloId IN ('.implode(',',$SET['szuloIds']).')';
	if ($SET['csakId'] === true || $SET['result'] == 'csakId') $SET['result'] = 'idonly';
	if ($SET['result'] == 'idonly') {
	    $q = "SELECT szuloId
		FROM ".__INTEZMENYDBNEV.".szulo $W_SZULO
		ORDER BY CONCAT_WS(' ',csaladinev,utonev)";	    
	    $ret = db_query($q, array('fv' => 'getSzulok', 'modul' => 'naplo_intezmeny', 'result' => 'idonly'));
	} elseif ($SET['result']=='standard') {
	    $q = "SELECT *,TRIM(CONCAT_WS(' ',nevElotag,csaladinev,utonev)) AS szuloNev 
		FROM ".__INTEZMENYDBNEV.".szulo $W_SZULO
		ORDER BY CONCAT_WS(' ',csaladinev,utonev)";	    
	    $ret = db_query($q, array('fv' => 'getSzulok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));
	} elseif ($SET['result']=='indexed') {
	    $q = "SELECT szuloId,TRIM(CONCAT_WS(' ',nevElotag,csaladinev,utonev)) AS szuloNev, statusz
		FROM ".__INTEZMENYDBNEV.".szulo $W_SZULO
		ORDER BY CONCAT_WS(' ',csaladinev,utonev)";	    
	    $ret = db_query($q, array('fv' => 'getSzulok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));
	} else {
	    $q = "SELECT *,TRIM(CONCAT_WS(' ',nevElotag,csaladinev,utonev)) AS szuloNev 
		FROM ".__INTEZMENYDBNEV.".szulo $W_SZULO
		ORDER BY CONCAT_WS(' ',csaladinev,utonev)";
	    $ret = db_query($q, array('fv' => 'getSzulok', 'modul' => 'naplo_intezmeny', 'keyfield' => 'szuloId', 'result' => 'assoc'));
	    foreach ($ret as $szuloId => $adat) $ret['szuloIds'][] = $szuloId;
	}
	return $ret;

    }

    function getSzuloDiakjai() {
        $q = "SELECT diak.diakId AS diakId,".
                    "TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) AS diakNev ".
                 "FROM szulo LEFT JOIN diak ON (szuloId IN (anyaId,apaId,gondviseloId,neveloId)) ".
                 "WHERE szulo.userAccount='"._USERACCOUNT."'";
        return db_query($q, array('fv' => 'getSzuloDiakjai', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));
    }

    function getSzulokDiakjai($szuloIds) {
        $q = "SELECT szuloId, diak.diakId AS diakId,".
                    "TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladiNev,viseltUtonev)) AS diakNev ".
                 "FROM szulo LEFT JOIN diak ON (szuloId IN (anyaId,apaId,gondviseloId,neveloId)) ".
                 "WHERE szuloId IN (".implode(',', array_fill(0, count($szuloIds), '%u')).")";
        return db_query(
	    $q, array('fv' => 'getSzulokDiakjai','modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'szuloId', 'values' => $szuloIds)
	);
    }

    function getDiakSzulei($diakId) {
        $q = "SELECT anyaId,apaId,gondviseloId,neveloId ".
                 "FROM diak ".
                 "WHERE diakId=%u";
        return db_query($q,array('fv' => 'getDiakSzulei', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($diakId)));
    }


    function getSzuloNevById($szuloId, $szuleteskori=false) {
	if ($szuleteskori) {
    	    $q = "SELECT IF(szuleteskoriCsaladinev='',TRIM(CONCAT_WS(' ', nevElotag, csaladiNev, utonev)),TRIM(CONCAT_WS(' ', szuleteskoriNevElotag, szuleteskoriCsaladinev, szuleteskoriUtonev))) AS szuloNev FROM szulo WHERE szuloId=%u";
	} else {
    	    $q = "SELECT TRIM(CONCAT_WS(' ', nevElotag, csaladiNev, utonev)) AS szuloNev FROM szulo WHERE szuloId=%u";
	}
        return db_query($q, array('fv' => 'getszuloNevById', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($szuloId)));
    }

    
?>
