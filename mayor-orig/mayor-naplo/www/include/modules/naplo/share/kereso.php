<?php

    function getDiakokByPattern($pattern) {
        if ($pattern=='') return false;
        $q = "SELECT DISTINCT diak.diakId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS diakNev, oId, osztalyJel FROM `diak` 
	      LEFT JOIN osztalyDiak ON (osztalyDiak.diakId=diak.diakId AND osztalyDiak.beDt<=NOW() AND (osztalyDiak.kiDt>=NOW() OR osztalyDiak.kiDt IS NULL))
	      LEFT JOIN " . __TANEVDBNEV . ".osztalyNaplo USING (osztalyId) 
	     HAVING (diakNev LIKE '%s' OR oId LIKE '%s') ORDER BY diakNev";
        $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array('%'.$pattern.'%','%'.$pattern.'%')));
        return $r;
    }
    function getTanarokByPattern($pattern) {
        if ($pattern=='') return false;
        $q = "SELECT tanarId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS tanarNev FROM `tanar` WHERE (kiDt IS NULL OR kiDt >=NOW()) HAVING tanarNev LIKE '%s' ORDER BY tanarNev";
        $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array('%'.$pattern.'%')));
        return $r;
    }
    function getTankorokByPattern($pattern) {
        if ($pattern=='') return false;
        $q = "SELECT tankorId, tankorNev AS tankorNev FROM `tankor`HAVING tankorNev LIKE '%s' ORDER BY tankorNev";
        $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array('%'.$pattern.'%')));
        return $r;
    }
    function getSzulokByPattern($pattern, $SET=array('diakokkal'=>false)) {
        if ($pattern=='') return false;
	if ($SET['diakokkal']!==true) {
		$p= "TRIM(CONCAT_WS(' ',nevElotag, csaladinev, utonev)) ";
    		$q = "SELECT szuloId, $p AS szuloNev FROM `szulo` HAVING szuloNev LIKE '%s' ORDER BY szuloNev";
    		$r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array('%'.$pattern.'%')));

	} else {

		if (defined('__UZENO_SZULO_CSAK_ACCOUNTTAL') && __UZENO_SZULO_CSAK_ACCOUNTTAL === true)
		    $W = " AND userAccount!='' ";
		else 
		    $W = '';

		$sr = getDiakokByPattern($pattern);
		for ($i=0; $i<count($sr); $i++) {
		    $DIAKIDS[] = $sr[$i]['diakId'];
		}
		$subquery = 'LEFT JOIN diak ON (szuloId IN (anyaId,apaId,neveloId,gondviseloId))';
		$p = "CONCAT(TRIM(CONCAT_WS(' ',nevElotag, csaladinev, utonev)), ' (',TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)),')' ) ";

    		$q2 = "SELECT szuloId, $p AS szuloNev FROM `szulo` $subquery WHERE TRIM(CONCAT_WS(' ',nevElotag, csaladinev, utonev)) LIKE '%s'  $W ";
    		if (count($DIAKIDS)>0) {
		    $q1 = "SELECT szuloId, $p AS szuloNev FROM `szulo`$subquery WHERE diakId IN (".implode(',',$DIAKIDS).")  $W ";
		    $q = "$q1 UNION ($q2)";
		} else $q = "($q2)";
		$r = db_query("$q ORDER BY szuloNev", array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array('%'.$pattern.'%')));
	}
        return $r;
    }

?>
