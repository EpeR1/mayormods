<?php

    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/osztaly.php'); // _SQL_EVFOLYAMJEL_SORREND

    function getKepzesek() {

	$q = "SELECT * FROM kepzes";
	return db_query($q, array('fv' => 'getKepzesek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));

    }

    function getKepzesAdatById($kepzesId) {

	$q = "SELECT * FROM kepzes WHERE kepzesId=%u";
	$ret = db_query($q, array('fv' => 'getKepzesAdatById', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($kepzesId)));
	if (!$ret || !is_array($ret) || count($ret) == 0) return false;

	if ($ret['osztalyJellegId']!='') {
	    $EVFOLYAMJELEK=getEvfolyamJelek(array('result'=>'idonly'));
	    $ret['osztalyJelleg']=getOsztalyJellegAdat($ret['osztalyJellegId']);
	    $ret['osztalyEvfolyamJelek'] = explode(',',$ret['osztalyJelleg']['evfolyamJelek']);
	}

	$q = "SELECT osztalyId FROM kepzesOsztaly WHERE kepzesId=%u";
	$ret['osztalyIds'] = db_query($q, array('fv' => 'getKepzesAdatById/Osztályok', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($kepzesId)));
	return $ret;

    }

    function getKepzesByOsztalyJelleg($osztalyJellegId) {

	$q = "select * from kepzes where osztalyJellegId=%u order by kepzesNev";
	return db_query($q, array('fv'=>'getKepzesByOsztalyJelleg','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array($osztalyJellegId)));

    }

    function getKepzesByOsztalyId($osztalyId, $SET = array('result' => 'indexed', 'arraymap' => null)) {

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed','multiassoc'));
	if (!is_array($osztalyId)) $osztalyId = array($osztalyId);

	$q = "SELECT * FROM kepzesOsztaly LEFT JOIN kepzes USING (kepzesId) WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyId), '%u')).")";
	$ret = db_query($q, array(
	    'fv' => 'getKepzesByOsztalyId', 'modul' => 'naplo_intezmeny', 'values' => $osztalyId, 'result' => $result, 'keyfield' => 'osztalyId'
	));

	if (is_array($SET['arraymap'])) return reindex($ret, $SET['arraymap']);
	else return $ret;

    }

    /* NEW */
    function getOsztalyLehetsegesKepzesei($osztalyId, $SET=array()) {
	return getKepzesByOsztaly($osztalyId,$SET);
    }

    /* NEW */
    function getOsztalyKepzesei($osztalyId,$SET=array()) {
	$DIAKIDK = getDiakokByOsztalyIds($osztalyId);
    }

    function getKepzesByDiakId($diakId, $SET = array('result' => 'assoc', 'dt' => null, 'arraymap' => null)) {
    /**
     * Ha nincs dátum megadva, akkor az összes, amúgy az adott dátumkor érvényes képzéseket adja vissza a függvény!
    **/

	if (!is_array($diakId))
	    if ($diakId != '') $diakId = array($diakId);
	    else $diakId = array();
	if (count($diakId) == 0) return false;
	if ($SET['result'] == 'csakid') $SET['result'] = 'idonly'; // az egységesítés nevében

	if (isset($SET['dt']) and $SET['dt'] != '') {
	    $WHERE_DT = " AND tolDt<='%s' AND (igDt IS NULL OR '%s'<=igDt)";
	    $v = mayor_array_join($diakId, array($SET['dt'], $SET['dt']));
	} else {
	    $WHERE_DT = "";
	    $v = $diakId;
	}
	if ($SET['result'] == 'idonly') {
	    $q = "SELECT DISTINCT kepzesId FROM kepzesDiak LEFT JOIN kepzes USING (kepzesId) 
		    WHERE diakId IN (".implode(',', array_fill(0, count($diakId), '%u')).")".$WHERE_DT;
	    $ret = db_query($q, array('fv' => 'getKepzesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));
	} else {
	    $q = "SELECT * FROM kepzesDiak LEFT JOIN kepzes USING (kepzesId) 
		    WHERE diakId IN (".implode(',', array_fill(0, count($diakId), '%u')).")".$WHERE_DT;
	    if ($SET['arraymap'] && count($SET['arraymap'])>0) { // ha arraymap van, nem figyelünk a resultra
		$r = db_query($q, array('fv' => 'getKepzesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
		$ret = reindex($r,$SET['arraymap']);
	    } elseif ($SET['result'] == 'assoc' || $SET['result'] == 'multiassoc') {
		$ret = db_query($q, array(
		    'fv' => 'getKepzesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'diakId', 'values' => $v
		));
	    } else {
		$ret = db_query($q, array('fv' => 'getKepzesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	    }
	}
	if (!$ret || !is_array($ret)) return false; //ez miért jó?
	return $ret;

    }

//    function getKepzesTargyByDiakId() --> targyId,targyNev


    function getTargyAdatFromKepzesOraterv($kepzesId, $SET = array('tipus'=>null, 'targyId'=>null, 'evfolyamJel'=>null, 'szemeszter'=>null, 'arraymap'=>null)) {
	/*
	    Ha van megadva típus és az nem 'mintatantervi', akkor azt (is) figyelembe veszi, DE az adatszerkezetbe nem kerül bele a tipus!
	*/
	if ($kepzesId=='') return false;
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('targyId','evfolyamJel','szemeszter');
	$W = ''; $v = array($kepzesId);
	if (isset($SET['tipus']) && $SET['tipus'] != 'mintatantervi') { $W .= " AND tipus='%s'"; $v[] = $tipus = $SET['tipus']; }
	else if (isset($SET['targyId'])) { $W .= " AND targyId=%u"; $v[] = $targyId = $SET['targyId']; }
	if (isset($SET['evfolyamJel'])) { $W .= " AND evfolyamJel='%s'"; $v[] = $evfolyamJel = $SET['evfolyamJel']; }
	if (isset($SET['szemeszter'])) { $W .= " AND szemeszter=%u"; $v[] = $szemeszter = $SET['szemeszter']; }

        $q = "SELECT * FROM kepzesOraterv WHERE kepzesId=%u".$W." ORDER BY tipus, targyId, "._SQL_EVFOLYAMJEL_SORREND.", szemeszter";
        $r = db_query($q,  array('modul'=>'naplo_intezmeny','fv'=>'getTargyAdatFromKepzesOraterv', 'result'=>'indexed', 'values'=> $v));
	$RE = reindex($r,$SET['arraymap']);
        return $RE;
    }


    function getKepzesOraterv($kepzesId, $SET = array('arraymap'=>null, 'evfolyamJel' => null)) {
	if ($kepzesId=='') return false;
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('tipus','targyId','evfolyamJel','szemeszter');
	
	if (isset($SET['evfolyamJel'])) {
	    $q = "SELECT * FROM kepzesOraterv WHERE kepzesId=%u AND evfolyamJel='%s'";
	    $v = array($kepzesId, $SET['evfolyamJel']);
	} else {
	    $q = "SELECT * FROM kepzesOraterv WHERE kepzesId=%u";
	    $v = array($kepzesId);
	}
        $r = db_query($q,  array('modul'=>'naplo_intezmeny','fv'=>'getKepzesOraterv', 'result'=>'indexed','values'=>$v));

	$RE = reindex($r, $SET['arraymap']);
        return $RE;
    }

//    function getKepzesTargyByDiakId() --> targyId,targyNev

    function getOraszamByKepzes($kepzesId, $SET = array('arraymap'=>null, 'evfolyamJel'=>'', 'szemeszter'=>0)) { // --TODO check evfolyam -> evfolyamJel
	if ($kepzesId=='') return false;
	//if (!is_array($SET['arraymap']) || count($SET['arraymap'])==1) $SET['arraymap'] = array();

        $q = "SELECT tipus,sum(hetiOraszam) AS sum FROM kepzesOraterv WHERE kepzesId=%u AND evfolyamJel='%s' AND szemeszter=%u GROUP BY tipus";
	$v = array($kepzesId,$SET['evfolyamJel'],$SET['szemeszter']);
        $r = db_query($q,  array('modul'=>'naplo_intezmeny','fv'=>'getOraszamByKepzes', 'result'=>'assoc','keyfield'=>'tipus', 'values'=>$v));
        return $r;
    }

    function setOsztalyKepzesei($osztalyId, $kepzesIds) {
	if (is_array($kepzesIds) && count($kepzesIds)>0) {
	    $q = "INSERT INTO kepzesOsztaly VALUES ".implode(',', array_fill(0, count($kepzesIds), '(%u,%u)'));
	    foreach ($kepzesIds as $kepzesId) { 
		$v[] = $kepzesId; 
		$v[] = $osztalyId; 
	    }
    	    $r = db_query($q,  array('modul'=>'naplo_intezmeny','fv'=>'setOsztalyKepzesei', 'result'=>'insert', 'values'=>$v));
	    return $r;
	}
    }

    function getKepzesOratervAdatByBontasId($bontasId) {

	$q = "select * from kepzesOraterv left join ".__TANEVDBNEV.".kepzesTargyBontas using (kepzesOratervId) where bontasId=%u";
	return db_query($q, array('fv'=>'getKepzesOratervAdatByBontasId','modul'=>'naplo_intezmeny','result'=>'record','values'=>array($bontasId)));

    }
?>
