<?php

    /* 2010 GPL */
    function getOraIdByPattern($P) {

	$v = array($P['dt'], $P['ora'], $P['ki'], $P['kit'], $P['tankorId'], $P['teremId']);
	$q = "SELECT oraId FROM ora WHERE dt='%s' AND ora=%u and (ki=%u or kit=%u or tankorId=%u or teremId=%u) AND tipus!='elmarad' AND tipus!='elmarad máskor'";
	$r = db_query($q, array( 'fv' => 'getOraIdByPattern', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v, 'debug'=>false ));
	if (count($r)!==1) 
	    return false;
	else
	    return $r[0]['oraId'];
	return false;
    }

    function checkHaladasiSzabadTerem($dt,$ora,$teremId,$lr) {
	$v = array($dt,$ora,$teremId);
	$q = "SELECT count(*) as db FROM `ora` WHERE `dt`='%s' AND `ora`=%u AND `teremId`=%u AND tipus!='elmarad' AND tipus!='elmarad máskor'";
	return (db_query($q, array( 'fv' => 'checkHaladasiSzabadTerem', 'modul' => 'naplo', 'result'=>'value','values' => $v), $lr) === "0");
    }

    function haladasiTeremModositas($oraId,$teremId,$lr) {
	if (!is_numeric($oraId) || !is_numeric($teremId)) return false;
	$v = array($teremId,$oraId);
	$q = "UPDATE ora SET teremId=%u WHERE oraId=%u";
	return db_query($q, array( 'fv' => 'haladasiTeremModositas', 
	'modul' => 'naplo', 'values' => $v), $lr);
    }

?>
