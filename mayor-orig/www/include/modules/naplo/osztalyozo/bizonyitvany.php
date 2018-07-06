<?php

    function getDiakBizonyitvany($diakId, $ADAT) {

	$tanev = $ADAT['szemeszterAdat']['tanev'];
	$szemeszter = $ADAT['szemeszterAdat']['szemeszter'];
	$sorrendNev = $ADAT['sorrendNev'];
	$osztalyId = $ADAT['osztalyId'];
	if ($tanev == '') {
	    // Összes zárójegy lekérdezése
	    $q = "SELECT * FROM zaroJegy 
			LEFT JOIN targy USING (targyId)
			LEFT JOIN szemeszter ON kezdesDt=(SELECT MAX(kezdesDt) FROM szemeszter WHERE kezdesDt<=hivatalosDt)
			WHERE diakId=%u ORDER BY tanev,szemeszter,targyNev";
	    $r = db_query($q, array('fv' => 'getDiakBizonyitvany', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($diakId)));
	    $ret = array('tanevek' => array(), 'szemeszterek' => array(), 'tanevSzemeszterei' => array(), 'jegyek' => array());
	    if (is_array($ret)) foreach ($r as $i => $jegy) {
		$ret['jegyek'][$jegy['targyId']][$jegy['tanev']][$jegy['szemeszter']][] = $jegy;
		$ret['jegyekEvfolyamonkent'][$jegy['evfolyam']][$jegy['szemeszter']][] = $jegy;
		if (!in_array($jegy['tanev'], $ret['tanevek'])) {
		    $ret['tanevek'][] = $jegy['tanev'];
		    $ret['tanevSzemeszterei'][$jegy['tanev']] = array();
		}
		if (!in_array($jegy['szemeszter'], $ret['tanevSzemeszterei'][$jegy['tanev']])) {
		    $ret['szemeszterek'][] = array('tanev' => $jegy['tanev'], 'szemeszter' => $jegy['szemeszter']);
		    $ret['tanevSzemeszterei'][$jegy['tanev']][] = $jegy['szemeszter'];
		}
	    }
	} else {
	    // Adott szemeszter tárgyainak
	    $ret['targyak'] = getTargyakByDiakIds(array($diakId), $ADAT['szemeszterAdat'], $osztalyId, $sorrendNev, array('result' => 'assoc', 'keyfield' => 'targyId'));
	    // Adott szemeszter zárójegyeinek lekérdezése
	    if (isset($sorrendNev) && $sorrendNev != '') {
		$q = "SELECT *,zaroJegy.targyId FROM zaroJegy 
			    LEFT JOIN targy USING (targyId) 
			    LEFT JOIN ".__TANEVDBNEV.".targySorszam 
				ON zaroJegy.targyId = targySorszam.targyId AND osztalyId=%u AND sorrendNev='%s'
			    LEFT JOIN szemeszter ON kezdesDt=(SELECT MAX(kezdesDt) FROM szemeszter WHERE kezdesDt<=hivatalosDt)
			WHERE diakId=%u AND tanev=%u AND szemeszter=%u ORDER BY sorszam,targyNev";
		$v = array($osztalyId, $sorrendNev, $diakId, $tanev, $szemeszter);
	    } else {
		$q = "SELECT *,zaroJegy.targyId FROM zaroJegy LEFT JOIN targy USING (targyId)
			WHERE diakId=%u AND tanev=%u AND szemeszter=%u ORDER BY targyNev";
		$v = array($diakId, $tanev, $szemeszter);
	    }
	    $r = db_query($q,  array(
		'fv' => 'getDiakBizonyitvany', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v
	    ));

	    if (is_array($r)) foreach ($r as $i => $jegy) {
		$ret['jegyek'][$jegy['targyId']][] = $jegy;
//		$ret['jegyekEvfolyamonkent'][$jegy['evfolyam']][$jegy['szemeszter']][] = $jegy;
	    }

	    $utolsoTanitasiNap = getOsztalyUtolsoTanitasiNap($osztalyId);
	    // éves óraszámok lekérdezése - tárgyanként
	    $q = "SELECT targyId,oraszam FROM tankorDiak LEFT JOIN tankorSzemeszter USING (tankorId) LEFT JOIN tankor USING (tankorId)
		    WHERE diakId=%u AND tanev=%u AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
	    //$v = array($diakId, $tanev, $ADAT['szemeszterAdat']['zarasDt'], $ADAT['szemeszterAdat']['zarasDt']);
	    $v = array($diakId, $tanev, $utolsoTanitasiNap, $utolsoTanitasiNap);
	    $jres = db_query($q, array(
		'fv' => 'getDiakBizonyitvany/óraszám', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'targyId', 'values' => $v
	    ));

	    $szDb = $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']; // Feltételezzük, hogy a szemeszterek számozása 1-től indul és folyamatos
	    foreach ($jres as $targyId => $tAdat) {
        	$oraszam = 0;
        	for ($i = 0; $i < count($tAdat); $i++) {
            	    $oraszam += $tAdat[$i]['oraszam'];
        	}
        	$ret['targyOraszam'][$targyId]['hetiOraszam'] = $oraszam / $szDb;
		/*
		    A TANITASI_HETEK_SZAMA a diák (egyik) osztályához rendelt munkaterv alapján van meghatározva - így
		    csak az aktuális tanévben (__TANEV) van értelme. Ha több osztálya is van a tanulónak, akkor problémás...
		*/
            	if (defined('TANITASI_HETEK_SZAMA')) $ret['targyOraszam'][$targyId]['evesOraszam'] = $oraszam / $szDb * TANITASI_HETEK_SZAMA;
	    }
	}
	return $ret;
    }

    function getHianyzasOsszesitesByDiakId($diakId, $szemeszterAdat = '') { // DEPRECATED. a függvény helyett a share/hianyzas.php getDiakHianyzasOsszesites() - t használd!

	$ret = array();
	if (is_array($szemeszterAdat)) {
	    // Egy szemeszter hiányzási adatainak lekérdezése
	    if ($szemeszterAdat['statusz'] == 'aktív') {
		// Folyó tanév - a tanév adatbázisból kérdezünk le
    		$Wnemszamit = defWnemszamit();
		$q = "SELECT tankorTipus.jelleg,
			    COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) AS igazolt,
    			    COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) AS igazolatlan,
    			    SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) AS kesesPercOsszeg
			FROM `%s`.hianyzas " .
			$Wnemszamit['join'] .
			"WHERE (
    			    tipus = 'hiányzás' OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL)
			) AND dt<='%s' AND diakId=%u".
			$Wnemszamit['nemszamit']
			." GROUP BY tankorTipus.jelleg"
		     ;
		$v = array(tanevDbNev(__INTEZMENY, $szemeszterAdat['tanev']), $szemeszterAdat['zarasDt'], $diakId);
		$ret = db_query($q, array('fv' => 'getDiakHianyzasOsszesitesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield'=>'jelleg', 'values' => $v));
		if (is_array($ret))
		    foreach ($ret as $_key=>$_val) {		
			$ret['igazolt'] += intval($ret[$_key]['igazolt']);
			$ret['igazolatlan'] += intval($ret[$_key]['igazolatlan']);
		    }

		$hozottHianyzas = getDiakHozottHianyzas($diakId, array('tanev'=> $szemeszterAdat['tanev'], 'igDt'=>$szemeszterAdat['zarasDt'] ));
		$ret['igazolt'] += intval($hozottHianyzas['igazolt']['db']);
		$ret['igazolatlan'] += intval($hozottHianyzas['igazolatlan']['db']);

	    } else {
		// lezárt tanév - az intézmény adatbázisból kérdezünk le
// Tudtommal az összesítésbe már csak a "beszámítandó" hiányzások kerülnek, így nem kell plusz feltétel... [bb - 2010-11-24]
//		$q = "SELECT * FROM hianyzasOsszesites WHERE diakId=%u AND tanev=%u AND szemeszter=%u $Wnemszamit"; 
		$q = "SELECT * FROM hianyzasOsszesites WHERE diakId=%u AND tanev=%u AND szemeszter=%u";
		$v = array($diakId, $szemeszterAdat['tanev'], $szemeszterAdat['szemeszter']);
		$ret = db_query($q, array('fv' => 'getDiakHianyzasOsszesitesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v));
	    }
	    
	} else {
	    // A diák összes hiányzási adata                        ??????????????
	    // ???????????????????????????	    // ???????????????????????????
	    $q = "SELECT * FROM hianyzasOsszesites WHERE diakId=%u ORDER BY tanev,szemeszter";
	    $r = db_query($q, array('fv' => 'getDiakHianyzasOsszesitesByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($diakId)));
	    for ($i = 0; $i < count($r); $i++) $ret[$r[$i]['tanev']][$r[$i]['szemeszter']] = $r[$i];
	}
	return $ret;

    }

?>
