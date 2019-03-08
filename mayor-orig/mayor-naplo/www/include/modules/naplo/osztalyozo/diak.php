<?php
/*
    Module: naplo

    getDiakJegyek($diakId, $SET, $olr)

	+getTankorByDiakId
	+getTankorDolgozatok
*/

    function getDiakJegyek($diakId, $SET = array('sulyozas'=>'1:1:1:1:1:1'), $olr = '') {

	global $_TANEV, $KOVETELMENY;

	if ($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;

	if (isset($SET['sulyozas']) && $SET['sulyozas']!='') {
	    $suly = explode(':',':'.$SET['sulyozas']);
	} else {
	    if (defined('__DEFAULT_SULYOZAS')) $suly = explode(':',':'.__DEFAULT_SULYOZAS);
	    else $suly = array(1,1,1,1,1,1);
	}
	$q = "SELECT DISTINCT jegyId, tankorId, tankorNev, dt, jegy, jegyTipus, tipus, oraId, dolgozatId, megjegyzes, IF (modositasDt='0000-00-00 00:00:00',dt,modositasDt) AS modositasDt
		FROM jegy LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE tipus <> 0 AND tanev=".__TANEV." AND diakId=%u
		ORDER BY dt, jegyId";
	$v = array($diakId);
	$jegyAdatok = db_query($q, array('fv' => 'getDiakJegyek', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'jegyId', 'values' => $v), $lr);

	$tankorIds = getTankorByDiakId($diakId, __TANEV, array('csakId' => true, 'tolDt' => '', 'igDt' => ''), $lr);

	if (is_array($jegyAdatok))
	    foreach ($jegyAdatok as $jegyId => $jegyAdat) {
		if (!in_array($jegyAdat['tankorId'], $tankorIds)) $tankorIds[] = $jegyAdat['tankorId'];
	    }
	if (is_array($tankorIds) && count($tankorIds) > 0) {
	    $q = "SELECT tankorId, targyId, targyNev FROM ".__INTEZMENYDBNEV.".tankor
		LEFT JOIN ".__INTEZMENYDBNEV.".targy USING (targyId)
		WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		ORDER BY targyNev";
	    $tankorTargyak = db_query($q, array(
		'fv' => 'getDiakJegyek', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
	    ), $lr);
	    if (!is_array($tankorTargyak)) $tankorTargyak = array();
	} else { $tankorTargyak = array(); }
	$targyJegyei = array();
	foreach ($tankorTargyak as $tankorId => $tAdat) {
	    $targyId = $tAdat['targyId']; $targyNev = $tAdat['targyNev'];
	    if (!is_array($targyJegyei[$targyId])) $targyJegyei[$targyId] = array('targyNev' => $targyNev);
	}
	foreach ($jegyAdatok as $jegyId => $jegyAdat) {
	    $tankorId = $jegyAdat['tankorId'];
	    $targyId = $tankorTargyak[$tankorId]['targyId'];
	    //$targyNev = $tankorTargyak[$tankorId]['targyNev'];
	    list($ev,$ho,$nap) = explode('-',$jegyAdat['dt']);
	    $targyJegyei[$targyId][$ev][$ho][] = $jegyId;
	    if (
		in_array($jegyAdat['jegyTipus'],array('jegy','féljegy'))
		|| $KOVETELMENY[ $jegyAdat['jegyTipus'] ]['átlagolható'] === true
	    ) {
		$targyJegyei[$targyId]['osszeg'] += $jegyAdat['jegy']*$suly[$jegyAdat['tipus']];
		$targyJegyei[$targyId]['db'] += $suly[$jegyAdat['tipus']];
	    }
	}
	foreach ($targyJegyei as $targyId => $targyAdat)
	    if ($targyJegyei[$targyId]['db'] != 0)
		$targyJegyei[$targyId]['atlag'] = number_format($targyJegyei[$targyId]['osszeg'] / $targyJegyei[$targyId]['db'],2,',','');

	// Bizonyítvány
	// kikerült innen...

	// Dolgozatok lekérdezése
	$dolgozatAdat = getTankorDolgozatok($tankorIds, ($csakTervezett = false));
	// Nem megírt dolgozatok lekérdezése
	if (is_array($dolgozatAdat['dolgozatIds']) && count($dolgozatAdat['dolgozatIds']) > 0) {
	    $q = "SELECT dolgozat.dolgozatId, bejelentesDt, dolgozatNev FROM dolgozat LEFT JOIN jegy ON dolgozat.dolgozatId=jegy.dolgozatId AND diakId=%u 
		    WHERE dolgozat.dolgozatId IN (".implode(',', array_fill(0, count($dolgozatAdat['dolgozatIds']), '%u')).") 
		    AND diakId IS NULL ORDER BY bejelentesDt";
	    $v = mayor_array_join(array($diakId), $dolgozatAdat['dolgozatIds']);
	    $nemMegirtDolgozat = db_query($q, array('fv' => 'getDiakJegyek/nem megírt dolgozat', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	} else {
	    $nemMegirtDolgozat = array();
	}
	$targyHianyzoDolgozatai = array();
	for ($i = 0; $i < count($nemMegirtDolgozat); $i++) {
	    $dolgozatId = $nemMegirtDolgozat[$i]['dolgozatId'];
	    $tankorId = $dolgozatAdat[$dolgozatId]['tankor'][0]['tankorId'];
	    $targyId = $tankorTargyak[$tankorId]['targyId'];
	    //$targyNev = $tankorTargyak[$tankorId]['targyNev'];
	    list($ev,$ho,$nap) = explode('-',$nemMegirtDolgozat[$i]['bejelentesDt']);
	    $targyHianyzoDolgozatai[$targyId][$ev][$ho][] = $dolgozatId;
	}
	/* -------------- */

	if ($olr == '') db_close($lr);
	$ret = array(
	    'jegyek' => $jegyAdatok, 'targyak' => $targyJegyei,
	    'dolgozat' => $dolgozatAdat, 'hianyzoDolgozatok' => $targyHianyzoDolgozatai
	);

	return $ret;

    }

?>
