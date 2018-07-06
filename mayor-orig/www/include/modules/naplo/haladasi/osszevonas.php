<?php

    function getOrakByDiakIdk($DIAKIDK, $SET = array('dt' => null, 'ora' => null)) {

	if (!is_array($DIAKIDK) || count($DIAKIDK) == 0) return false;

	$dt = readVariable($SET['dt'], 'datetime', date('Y-m-d'));
	$ora = readVariable($SET['ora'], 'numeric unsigned', 1);

	$q = "SELECT DISTINCT a.tankorId, a.oraId FROM ora AS a LEFT JOIN ".__INTEZMENYDBNEV.".tankorDiak AS b 
	    ON (a.tankorId = b.tankorId AND b.beDt<='%s' AND ('%s'<=b.kiDt OR b.kiDt IS NULL))
	    WHERE b.diakId IN (".implode(',', array_fill(0, count($DIAKIDK), '%u')).") AND a.dt='%s' AND a.ora=%u 
	    GROUP BY b.diakId HAVING COUNT(a.oraId)>0";
	$v = mayor_array_join(array($dt, $dt), $DIAKIDK, array($dt, $ora));
	return db_query($q, array('fv' => 'getOrakByDiakIdk', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

    }

    function oraFelvetele($dt, $ora, $tanarId, $tankorId, $teremId = 'NULL', $tipus = 'normál', $eredet = 'plusz') {

        // ------------------------------------
        // ITT NEM ellenőrizzük a tanár terhelését!
        // ------------------------------------
    
	if (!isset($teremId) || $teremId == '' || intval($teremId) == 0) {
	    $q = "INSERT INTO ora (dt,ora,ki,tankorId,teremId,tipus,eredet)
		VALUES ('%s', %u, %u, %u, NULL, '%s', '%s')";
	    $v = array($dt, $ora, $tanarId, $tankorId, $tipus, $eredet);
	} else {
	    $q = "INSERT INTO ora (dt,ora,ki,tankorId,teremId,tipus,eredet)
		VALUES ('%s', %u, %u, %u, %u, '%s', '%s')";
	    $v = array($dt, $ora, $tanarId, $tankorId, $teremId, $tipus, $eredet);
	}
	return db_query($q, array('fv' => 'oraFelvetele', 'modul' => 'naplo', 'values' => $v));
	
    }
    
?>
