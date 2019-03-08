<?php

    function getTargyOraszam($tanev=__TANEV,$targyId='') {

	$q = "SELECT SUM(oraszam)/(SELECT MAX(szemeszter) FROM szemeszter WHERE tanev=%u) AS db, targyNev FROM tankorSzemeszter LEFT JOIN tankor USING (tankorId) 
		LEFT JOIN targy USING (targyId) 
		WHERE tanev=%u GROUP BY targyid ORDER BY targyNev";
	$v = array($tanev, $tanev);
	$R = db_query($q, array('fv' => 'getTargyOraszam', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'targyNev', 'values' => $v));

	foreach($R as $_tankorId => $D) {
	    $R[$_tankorId]['tankorTanar'] = getTankorTanarai($tankorId);
	}

	return $R;

    }

    function getTargyOraszamEvfolyamonkent($tanev=__TANEV) {

	$q = "SELECT SUM(oraszam)/(SELECT MAX(szemeszter) FROM szemeszter WHERE tanev=%u) AS db, targyNev FROM tankorSzemeszter 
		LEFT JOIN tankor USING (tankorId) LEFT JOIN targy USING (targyId) WHERE tanev=%u GROUP BY targyid ORDER BY targyNev";
	$v = array($tanev, $tanev);
	return db_query($q, array('fv' => 'getTargyOraszamEvfolyamonkent', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'targyNev', 'values' => $v));

    }

?>
