<?php

    function getOsztalyNevsorEsOid($osztalyId) {

	$q = "SELECT diakId, TRIM(CONCAT_WS(' ',viseltNevElotag, ViseltCsaladiNev, viseltUtoNev)) AS diakNev, viseltCsaladinev, viseltUtonev, oId 
		FROM diak LEFT JOIN osztalyDiak USING (diakId)
		WHERE osztalyId=%u 
		AND beDt<=CURDATE() AND (kiDt >= CURDATE() OR kiDt IS NULL) 
		AND (statusz != 'jogviszonya lezárva' OR jogviszonyVege < CURDATE()) ORDER BY diakNev, oId";
	return db_query($q, array('fv' => 'getOsztalyAzonositok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($osztalyId)), $lr);

    }

?>
