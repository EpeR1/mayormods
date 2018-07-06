<?php

    function getElszamolas($tolDt, $igDt, $tanarId = '') {

	if (isset($tanarId) && intval($tanarId)!='') { 
	    $w = " AND ki=%u";
	    $v2 = $tanarId;
	} else $w='';

	$A = array();
	// Megtartott órák száma tanáronként, típusonként
        $q = "SELECT ki,tipus,eredet,munkaido,COUNT(*) AS db FROM                                                                                                                                
                ( SELECT DISTINCT ki,munkaido,tipus,dt,ora,eredet FROM                                                                                                                           
                    ora WHERE leiras != '' AND tipus NOT LIKE 'elmarad%%' AND '%s'<=dt AND dt<='%s' $w ) AS x                                                                       
                GROUP BY ki, munkaido, tipus,eredet";         
	$v = mayor_array_join(array($tolDt, $igDt),$v2);
	$ret = db_query($q, array('fv' => 'getElszamolas', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	for ($i = 0; $i < count($ret); $i++) {
	    $A[ $ret[$i]['ki'] ][ $ret[$i]['tipus'] ] += intval($ret[$i]['db']);
	    $A['detailed'][ $ret[$i]['ki'] ][ $ret[$i]['tipus'] ][ $ret[$i]['munkaido'] ] = intval($ret[$i]['db']);
	    $A['detailed_ki_tipus_munkaido_eredet'][ $ret[$i]['ki'] ][ $ret[$i]['tipus'] ][ $ret[$i]['munkaido'] ][ $ret[$i]['eredet'] ] = intval($ret[$i]['db']);
	}
	// Tanárok heti óraszáma
	$q = "SELECT tanarId,
		    COUNT(*)/(
			SELECT COUNT(DISTINCT het) AS db FROM orarendiOra WHERE tolDt<=CURDATE() AND CURDATE()<=igDt
		    ) AS db
		FROM orarendiOra WHERE tolDt <= CURDATE() AND CURDATE() <= igDt
		GROUP BY tanarId";
	$ret = db_query($q, array('fv' => 'getElszamolas', 'modul' => 'naplo', 'result'=> 'indexed'));
	for ($i = 0; $i < count($ret); $i++) $A[ $ret[$i]['tanarId'] ]['oraszam'] = $ret[$i]['db'];

	// Napok száma az adott időszak alatt - típusonként
	$q = "SELECT munkatervId,tipus,COUNT(*) AS db FROM nap WHERE '%s'<=dt AND dt<='%s' GROUP BY munkatervId,tipus";
	$ret = db_query($q, array(
	    'fv' => 'getElszamolas', 'modul' => 'naplo', 'result' => 'indexed', 'keyfield' => 'tipus', 'values' => mayor_array_join(array($tolDt, $igDt),$v)
	));
	foreach ($ret as $tmp) { $A['napok'][ $tmp['munkatervId'] ][ $tmp['tipus'] ] = $tmp['db']; }
	$A['munkaterv'] = getMunkatervek(array('result'=>'assoc', 'keyfield'=>'munkatervId'));

	return $A;
    }

?>
