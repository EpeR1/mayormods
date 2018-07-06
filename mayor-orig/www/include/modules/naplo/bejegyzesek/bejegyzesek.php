<?php
/*
    module: naplo
*/

    // ok
    function getBejegyzesekByTanarId($tanarId) {

	$q = "SELECT * FROM bejegyzes LEFT JOIN `".__INTEZMENYDBNEV."`.`bejegyzesTipus` USING (bejegyzesTipusId) WHERE tanarId=%u ORDER BY tipus,beirasDt";

	$BEJEGYZESEK = db_query($q, array('fv' => 'getBejegyzesLista', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($tanarId)));
	for ($i = 0; $i < count($BEJEGYZESEK); $i++) 
	    if ($BEJEGYZESEK[$i]['tanarId'] != '') $BEJEGYZESEK[$i]['tanarNev'] = getTanarNevById($BEJEGYZESEK[$i]['tanarId']);

	return $BEJEGYZESEK;

    }
    // ok
    function getBejegyzesLista($diakId) {

	$q = "SELECT * FROM bejegyzes LEFT JOIN `".__INTEZMENYDBNEV."`.`bejegyzesTipus` USING (bejegyzesTipusId) WHERE diakId=%u ORDER BY tipus,beirasDt";

	$BEJEGYZESEK = db_query($q, array('fv' => 'getBejegyzesLista', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($diakId)));
	for ($i = 0; $i < count($BEJEGYZESEK); $i++) 
	    if ($BEJEGYZESEK[$i]['tanarId'] != '') $BEJEGYZESEK[$i]['tanarNev'] = getTanarNevById($BEJEGYZESEK[$i]['tanarId']);

	return $BEJEGYZESEK;

    }
    // ok
    function getBejegyzesekByDiakIds($diakIds, $orderBy = 'diakId,tipus,beirasDt') {

	$q = "SELECT * FROM bejegyzes LEFT JOIN `".__INTEZMENYDBNEV."`.`bejegyzesTipus` USING (bejegyzesTipusId) 
		WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") ORDER BY $orderBy";

	$BEJEGYZESEK = db_query($q, array(
	    'fv' => 'getBejegyzesekByDiakIds', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'diakId', 'values' => $diakIds
	));
	foreach ($BEJEGYZESEK as $diakId => $BADAT)
	    for ($i = 0; $i < count($BADAT); $i++)
		if ($BADAT[$i]['tanarId'] != '') 
		    $BEJEGYZESEK[$diakId][$i]['tanarNev'] = getTanarNevById($BADAT[$diakId][$i]['tanarId']);

	return $BEJEGYZESEK;

    }
    // ok
    function delBejegyzes($bejegyzesId) {


	$torolheto = false;
	if (__NAPLOADMIN) {
	    $torolheto = true;
	} elseif (__TANAR) {
	    $q = "SELECT tanarId FROM bejegyzes WHERE bejegyzesId=%u";
	    $tanarId = db_query($q, array('fv' => 'delBejegyzes/check tanar', 'modul' => 'naplo', 'result' => 'value', 'values' => array($bejegyzesId)));
    	    if (__USERTANARID == $tanarId) $torolheto = true;
	}
	if ($torolheto) {
	    $q = "DELETE FROM bejegyzes WHERE bejegyzesId=%u";
	    db_query($q, array('fv' => 'delBejegyzes', 'modul' => 'naplo', 'values' => array($bejegyzesId)));
	} else {
	    $_SESSION['alert'][] = 'message:insufficient_access:delBejegyzes';
	}
    }

    function getBejegyzesAdatById($bejegyzesId) {

	$q = "SELECT * FROM bejegyzes LEFT JOIN `".__INTEZMENYDBNEV."`.`bejegyzesTipus` USING (bejegyzesTipusId) WHERE bejegyzesId=%u";
	return db_query($q, array('fv' => 'getBejegyzesAdatById', 'modul' => 'naplo', 'result' => 'record', 'values' => array($bejegyzesId)));

    }

?>
