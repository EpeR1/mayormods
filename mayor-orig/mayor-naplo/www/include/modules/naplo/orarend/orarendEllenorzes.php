<?php

    function checkOrarendiOraTankor($ADAT) {

	$tanevDb = tanevDbNev(__INTEZMENY, $ADAT['tanev']);
	$q = "SELECT * FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
		LEFT JOIN `%s`.osztalyNaplo ON orarendiOra.osztalyJel=osztalyId
		WHERE tankorId IS NULL";
	$v = array($tanevDb, $tanevDb, $tanevDb);
	return db_query($q, array('fv' => 'checkOrarendiOraTankor', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

    }

    function checkTankorOraszam($ADAT) {


	$return = array();

	if (!is_array($ADAT['orarendiHetek']) || count($ADAT['orarendiHetek']) == 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:checkTankorOraszam:orarendiHetek';
	    return $return;
	}

	$tanevDb = tanevDbNev(__INTEZMENY, $ADAT['tanev']);
	$hetDb = count($ADAT['orarendiHetek']);
	$q = "SELECT tankorId,COUNT(*)/$hetDb AS hetiOraszam FROM `%s`.orarendiOraTankor 
		LEFT JOIN `%s`.orarendiOra USING (tanarId,osztalyJel,targyJel) 
		WHERE tolDt <= '%s' AND '%s' <= igDt GROUP BY tankorId";
	$v = array($tanevDb, $tanevDb, $ADAT['dt'], $ADAT['dt']);
	$ret = db_query($q, array('fv' => 'checkTankorOraszam', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $v));

	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {

	    $A = $ADAT['tankorok'][$i]; $tankorId = $A['tankorId'];
	    if ($A['hetiOraszam'] != $ret[$tankorId]['hetiOraszam']) $return[] = array(
		'tankorId' => $tankorId,
		'tankorNev' => $A['tankorNev'],
		'tankorHetiOraszam' => number_format(floatval($A['hetiOraszam']), 2, ',', ''),
		'orarendHetiOraszam' => number_format(floatval($ret[$tankorId]['hetiOraszam']), 2, ',', '')
	    );
	}

	return $return;

    }

    function checkHianyzoTermek($ADAT) {

	$tanevDb = tanevDbNev(__INTEZMENY, $ADAT['tanev']);
	$q = "SELECT het,nap,ora,TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladinev,viseltUtonev)) AS tanarNev,osztalyNaplo.osztalyJel,targyJel
		FROM `%s`.orarendiOra LEFT JOIN ".__INTEZMENYDBNEV.".tanar USING (tanarId)
		LEFT JOIN `%s`.osztalyNaplo ON osztalyId=orarendiOra.osztalyJel 
		WHERE teremId IS NULL AND tolDt <= '%s' AND '%s' <= igDt
		ORDER BY tanarNev, osztalyNaplo.osztalyJel";
	$v = array($tanevDb, $tanevDb, $ADAT['dt'], $ADAT['dt']);
	return db_query($q, array('fv' => 'checkHianyzoTermek', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

    }

    function checkTeremUtkozes($ADAT) {

	$q = "SELECT het,nap,ora,teremId,COUNT(*) AS db FROM `%s`.orarendiOra 
		WHERE teremId IS NOT NULL AND tolDt <= '%s' AND '%s' <= igDt
		GROUP BY het,nap,ora,teremId HAVING db>1";
	$v = array(tanevDbNev(__INTEZMENY, $ADAT['tanev']), $ADAT['dt'], $ADAT['dt']);
	return db_query($q, array('fv' => 'checkTeremUtkozes', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

    }

?>
