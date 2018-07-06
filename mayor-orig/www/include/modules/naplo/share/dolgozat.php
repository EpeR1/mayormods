<?php
/*
    module:	naplo

    function checkTankorDolgozata($tankorId, $dolgozatId, $olr = '')
	return bool (ha $dolgozatId == 'uj' akkor is true)

    function getTankorDolgozatok($tankorId, $tolDt = '', $igDt = '', $olr = '')
	return array(
	    'dolgozatIds' => array(...),
	    'tervezett' => array(...),
	    $tankorId => array(
		'bejelentés'=> ...,
		'tervezett' => array($dt => array(...)),
		'megjegyzés' => ...,
		'tankör' => array(array('id','leírás'), ...)
	    )
	)

    function ujDolgozat($tanarId, $tankorId, $olr = '')

*/

 // --------------------------------------------------------- //

    function checkTankorDolgozata($tankorId, $dolgozatId, $olr = '') {


	if ($dolgozatId == 'uj') {
	    // Az új dolgozat csak ez után lesz létrehozva (jegybeírás)
	    return true;
        } else {
            // ellenőrizzük, hogy a megadott dolgozatId valóban egy ehhez a tankörhöz tartozó did-e.
	    $q = "SELECT COUNT(dolgozatId) FROM dolgozat LEFT JOIN tankorDolgozat USING (dolgozatId)
                    WHERE dolgozat.dolgozatId = %u AND tankorId = %u";
	    $v = array($dolgozatId, $tankorId);
	    return (1 == db_query($q, array('fv' => 'checkTankorDolgozata', 'modul' => 'naplo', 'values' => $v, 'result' => 'value'), $olr));
        }

    }

 // --------------------------------------------------------- //

    function getTankorDolgozatok($tankorId, $csakTervezett = false, $tolDt = null, $igDt = null, $olr = null) {

	$return = array();

	initTolIgDt(__TANEV, $tolDt, $igDt);
	$tankorIds = array();
	if (!is_array($tankorId) && $tankorId != '') $tankorIds = array($tankorId);
	elseif (is_array($tankorId[0])) for ($i = 0; $i < count($tankorId); $i++) $tankorIds[] = $tankorId[$i]['tankorId'];
	elseif (is_array($tankorId)) $tankorIds = $tankorId;
	else return false;

	if (count($tankorIds) > 0) {
	    $v = $tankorIds;
	    if ($csakTervezett) {
		$q = "SELECT * FROM dolgozat LEFT JOIN tankorDolgozat USING (dolgozatId)
		    WHERE tankorDolgozat.tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    AND '%s' <= tervezettDt AND tervezettDt <= '%s'
		    ORDER BY tervezettDt, bejelentesDt";
		array_push($v, $tolDt, $igDt);
	    } else {
		$q = "SELECT * FROM dolgozat LEFT JOIN tankorDolgozat USING (dolgozatId)
		    WHERE tankorDolgozat.tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    ORDER BY tervezettDt, bejelentesDt";
	    }
	    $return = db_query($q, array('fv' => 'getTankorDolgozatok', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'dolgozatId', 'values' => $v), $olr);
	    $dolgozatIds = $tervezett = array();
	    foreach ($return as $dolgozatId => $dolgozatAdat) {
		$dolgozatIds[] = $dolgozatId;
		if ($dolgozatAdat['tervezettDt'] != '') $tervezett[$dolgozatAdat['tervezettDt']][] = $dolgozatId;
	    }
	    $return['dolgozatIds'] = $dolgozatIds;
	    $return['tervezett'] = $tervezett;
	    if (count($dolgozatIds) > 0) {
		$q = "SELECT DISTINCT dolgozatId, tankorId, tankorNev
		    FROM tankorDolgozat LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		    WHERE tanev=".__TANEV."
		    AND dolgozatId IN (".implode(',', array_fill(0, count($dolgozatIds), '%u')).")";
		$Tankorok = db_query($q, array('fv' => 'getTankorDolgozatok', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'dolgozatId', 'values' => $dolgozatIds), $olr);
		foreach ($Tankorok as $dolgozatId => $dolgozatTankorei) {
		    $return[$dolgozatId]['tankor'] = $dolgozatTankorei;
		}
	    }
	}
	return $return;

    }

 // --------------------------------------------------------- //

    function ujDolgozat($tanarId, $tankorId, $olr = null) {

	$q = "INSERT INTO dolgozat (bejelentesDt, dolgozatNev, modositasDt) VALUES (now(),'%s',now())";
	$v = array( 'Dolgozat '.date('Y-m-d H:i:s') );
	$dolgozatId = db_query($q, array('fv' => 'ujDolgozat/1', 'modul' => 'naplo', 'result' => 'insert', 'values'=>$v), $olr);

	$q = "INSERT INTO tankorDolgozat (dolgozatId, tankorId) VALUES (%u, %u)";
	$v = array($dolgozatId, $tankorId);
	db_query($q, array('fv' => 'ujDolgozat/2', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v), $olr);
	
	return $dolgozatId;

    }

    function getDolgozatAdat($dolgozatId, $olr = null) {

	if ($dolgozatId=='') return false;
	$lr = (!is_resource($olr)) ? db_connect('naplo') : $olr;
	$q = "SELECT * FROM dolgozat WHERE dolgozatId = %u";
	$v = array($dolgozatId);
	$RET = db_query($q, array('fv' => 'getDolgozatAdat', 'modul' => 'naplo', 'result' => 'record', 'values' => $v), $lr);

	$q = "SELECT tankorId FROM tankorDolgozat WHERE dolgozatId = %u";
	$v = array($dolgozatId);
	$r = db_query($q, array('fv' => 'getDolgozatAdat', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v), $lr);
	for ($i=0; $i<count($r); $i++) {
	    $_tankorId = $r[$i];
	    $_TA = getTankorAdat($_tankorId);
	    $RET['tankorok'][] = $_TA[$_tankorId];
	}

	$q = "SELECT avg(jegy) AS atlag, count(jegyId) AS db FROM jegy WHERE dolgozatId = %u";
	$v = array($dolgozatId);
	$RET['jegyStatisztika'] = db_query($q, array('fv' => 'getDolgozatAdat', 'modul' => 'naplo', 'result' => 'record', 'values' => $v), $lr);

	if (!is_resource($olr)) db_close($lr);

	return $RET;
    }

?>
