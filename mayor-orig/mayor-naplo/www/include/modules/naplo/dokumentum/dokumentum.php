<?php

    function getDokumentumok() {

        $q = "SELECT * FROM dokumentum ORDER BY dokumentumId";
        $v = array();
        $r = db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'dokumentum','values'=>$v, 'result'=>'indexed'));
        return $r;

    }

    function getDokumentumokAssoc() {

	$q = "select dokumentum.*,IFNULL(tanev,YEAR(dokumentumDt)) AS dokumentumTanev from dokumentum left join szemeszter ON (dokumentumDt>kezdesDt && dokumentumDt<=szemeszter.ZarasDt)";
        $v = array();
        $r = db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'dokumentum','values'=>$v, 'result'=>'indexed'));

	return reindex($r,array('dokumentumTipus','dokumentumTanev'));
        return $r;

    }

    function updateDokumentum($ADAT) {


    }

    function addDokumentum($ADAT) {

	if ($ADAT['dokumentumLeiras']!='' && readVariable($ADAT['dokumentumUrl'],'url')!='') {
    	    $q = "INSERT INTO dokumentum (dokumentumLeiras, dokumentumRovidLeiras, dokumentumUrl, dokumentumMegjegyzes, dokumentumSorrend, dokumentumTipus, dokumentumPolicy, dokumentumDt)
	      VALUES ('%s','%s','%s','%s',%u,'%s','%s',NOW())";
    	    $v = array($ADAT['dokumentumLeiras'],$ADAT['dokumentumRovidLeiras'],$ADAT['dokumentumUrl'],$ADAT['dokumentumMegjegyzes'],intval($ADAT['dokumentumSorrend']),$ADAT['dokumentumTipus'],$ADAT['dokumentumPolicy']);
    	    $r = db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'dokumentum','values'=>$v, 'result'=>'insert'));
	}
        return $r;

    }

    function delDokumentum($dokumentumId) {

	if (!is_array($dokumentumId) && intval($dokumentumId)>0) {
    	    $q = "DELETE FROM dokumentum WHERE dokumentumId=%u";
    	    $v = array($dokumentumId);
    	    $r = db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'dokumentum','values'=>$v, 'result'=>''));
	} elseif (is_array($dokumentumId)) {
    	    $q = 'DELETE FROM dokumentum WHERE dokumentumId IN ('.implode(',',$dokumentumId).')';
    	    $v = array();
    	    $r = db_query($q, array('modul'=>'naplo_intezmeny','fv'=>'dokumentum','values'=>$v, 'result'=>''));
	}
        return $r;

    }


?>