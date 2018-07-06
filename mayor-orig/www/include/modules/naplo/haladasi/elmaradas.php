<?php

    function getHaladasiElmaradas() {

	$ret = array();
	$elozoTanitasiNapDt = getTanitasiNapVissza(1);

	// Még beírható, de már elmúlt órák száma
	$q = "SELECT ki, COUNT(*) AS db FROM ".__TANEVDBNEV.".ora WHERE tipus NOT LIKE 'elmarad%%'
		AND (leiras = '' OR leiras IS NULL)
		AND dt >= CAST('"._HALADASI_HATARIDO."' AS DATE)
		AND dt <= CAST('%s' AS DATE) GROUP BY ki";
	$ret['beirando'] = db_query($q, array(
	    'fv' => 'getHaladasiElmaradas/#1', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'ki', 'values' => array($elozoTanitasiNapDt)
	));
	// Lezárt hiányzások száma
	$q = "SELECT ki, COUNT(*) AS db FROM ".__TANEVDBNEV.".ora WHERE tipus NOT LIKE 'elmarad%'
		AND (leiras = '' OR leiras IS NULL)
		AND dt < CAST('"._HALADASI_HATARIDO."' AS DATE)
		GROUP BY ki
		ORDER BY db DESC";
	$ret['lezart'] = db_query($q, array('fv' => 'getHaladasiElmaradas/#2', 'modul' => 'naplo', 'result' => 'indexed'));

	return $ret;
    }

?>
