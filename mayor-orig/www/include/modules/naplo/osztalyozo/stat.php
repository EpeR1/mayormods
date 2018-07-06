<?php
/*
    module: naplo

     * getTargyakByDiakIds($diakIds, $szemeszterAdat, $osztalyId, $sorrendNev) --> SHARED! (bizonyítvány nyomtatás/értesítő)
     * getDiakZarojegyAtlagok($diakIds, $tanev, $szemeszter)
     * getTargyZarojegyAtlagok($diakIds, $tanev, $szemeszter)
     * getTanarokByDiakIds($diakIds, $szemeszterAdat)			--> SHARED!
     * getTargyakBySzemeszter($szemeszterAdat)				--> SHARED!
     * getTargyAtlagokBySzemeszter($szemeszterAdat)
     * getOsztalyHianyzasOsszesites($szemeszterAdat)
     * getZarojegyStatBySzemeszter($SZA)

*/

    function getDiakZarojegyAtlagok($diakIds, $tanev, $szemeszter) {

	if (count($diakIds)<1) return false;
	$mIdk = getMagatartas();
	$szIdk = getSzorgalom();
        $q = "SELECT diakId,FLOOR(100*AVG(jegy))/100 AS atlag FROM zaroJegy LEFT JOIN targy USING (targyId)
		LEFT JOIN szemeszter ON kezdesDt<=hivatalosDt AND hivatalosDt<=zarasDt
		WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND tanev=%u AND szemeszter=%u 
		AND jegy != 0 AND zaroJegy.jegyTipus ='jegy' AND targy.targyId NOT IN (".implode(',',array_merge($mIdk,$szIdk)).") 
		AND felev = %u
		GROUP BY diakId WITH ROLLUP";
	array_push($diakIds, $tanev, $szemeszter, $szemeszter);
	$r = db_query($q, array('fv' => 'getDiakZarojegyAtlagok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $diakIds));
	$ret = array();
	$sum = $db = 0;
	for ($i = 0; $i < count($r); $i++) {
	    if ($r[$i]['diakId'] == '') {
		$r[$i]['diakId'] = 'osztaly';
		$ret['osztaly'] = floor(100*$r[$i]['atlag'])/100;
	    } else {
		//$ret[ $r[$i]['diakId'] ] = floor(100*$r[$i]['atlag'])/100; - elvileg nem lenne baj, de php hiba: pl. 4.64, 4.14, 4.35 hibásan "kerekedik"...
		$ret[ $r[$i]['diakId'] ] = $r[$i]['atlag']; // a lekérdezésben már csonkoltunk...
		$sum += $ret[ $r[$i]['diakId'] ];
		$db++;
	    }
	}
	$ret['osztaly'] = ($db==0) ? 0 : floor(100*$sum/$db)/100; // felülírjuk, mert a jegyek átlaga a tárgy átlagoknál már megvan
	return $ret;
    }

    function getTargyZarojegyAtlagok($diakIds, $tanev, $szemeszter) {
    /*
     * Az adott szemeszterben szerzett zárójegyek átlaga, de csak szemeszter=felev megfeleltetéssel
     */
	if (count($diakIds)<1) return false;

        $q = "SELECT targyId, floor(100*avg(jegy))/100 AS atlag FROM zaroJegy
		LEFT JOIN szemeszter ON kezdesDt<=hivatalosDt AND hivatalosDt<=zarasDt AND felev=szemeszter	
		WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND tanev=%u AND szemeszter=%u
		AND jegy != 0 AND jegyTipus='jegy' GROUP BY targyId WITH ROLLUP";
	array_push($diakIds, $tanev, $szemeszter);
	$r = db_query($q, array('fv' => 'getTargyZarojegyAtlagok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $diakIds));
	$ret = array();
	for ($i = 0; $i < count($r); $i++) {
	    if ($r[$i]['targyId'] == '') $r[$i]['targyId'] = 'osztaly';
	    //$ret[ $r[$i]['targyId'] ] = floor(100*$r[$i]['atlag'])/100; // sql-ben megbízhatóbb a csonkolás működése - sajnos...
	    $ret[ $r[$i]['targyId'] ] = $r[$i]['atlag'];
	}
	return $ret;
    }

    function getTanarokByDiakIds($diakIds, $szemeszterAdat) {
    //??? 2009. shared lib ?

	if (count($diakIds)<1) return false;

	// A tárgyak lekérdezése a szemeszterben felvett tankörök alapján (miből lehet zárójegyet kapni)
	$q = "SELECT DISTINCT targyId, TRIM(CONCAT_WS(' ',viseltNevElotag,viseltCsaladinev,viseltUtonev)) AS tanarNev
		FROM tankor LEFT JOIN tankorDiak USING (tankorId) LEFT JOIN tankorTanar USING (tankorId) LEFT JOIN tanar USING (tanarId)
		WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") 
		    AND (tankorDiak.kiDt IS NULL OR tankorDiak.kiDt>='%s') AND tankorDiak.beDt<='%s'
		    AND (tankorTanar.kiDt IS NULL OR tankorTanar.kiDt>='%s') AND tankorTanar.beDt<='%s'";
	array_push($diakIds, $szemeszterAdat['kezdesDt'], $szemeszterAdat['zarasDt'], $szemeszterAdat['kezdesDt'], $szemeszterAdat['zarasDt']);
	return db_query($q, array('debug'=>false,'fv' => 'getTanarokByDiakIds', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'targyId', 'values' => $diakIds));

    }

    function getTargyakBySzemeszter($szemeszterAdat) {
    //??? 2009. shared lib ?

	// A tárgyak lekérdezése a beírt jegyek alapján (lehet hozott jegy)
	$q = "SELECT DISTINCT targyId,targyNev 
		FROM targy LEFT JOIN zaroJegy USING (targyId) 
		LEFT JOIN szemeszter ON kezdesDt<=hivatalosDt AND hivatalosDt<=zarasDt
		WHERE tanev=%u AND szemeszter=%u ORDER BY targyNev";
	$v = array($szemeszterAdat['tanev'], $szemeszterAdat['szemeszter']);
	return db_query($q, array('fv' => 'getTargyakBySzemeszter', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
    }


    function getTargyAtlagokBySzemeszter($szemeszterAdat) {
        $q = "SELECT targyId,osztalyId,FLOOR(100*AVG(jegy))/100 AS atlag FROM zaroJegy LEFT JOIN osztalyDiak
		ON (zaroJegy.diakId=osztalyDiak.diakId AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt))
		LEFT JOIN szemeszter ON kezdesDt<=hivatalosDt AND hivatalosDt<=zarasDt 
		    AND felev=szemeszter
                WHERE tanev=%u AND szemeszter=%u AND jegy != 0 
            	    AND jegyTipus in ('jegy','magatartas','szorgalom') 
                GROUP BY targyId,osztalyId WITH ROLLUP";
	$v = array($szemeszterAdat['zarasDt'], $szemeszterAdat['zarasDt'], $szemeszterAdat['tanev'], $szemeszterAdat['szemeszter']);
        $r = db_query($q, array('fv' => 'getTargyAtlagokBySzemeszter', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
        $ret = array();
        for ($i = 0; $i < count($r); $i++)
    	    if ($r[$i]['targyId'] != '') {
        	if ($r[$i]['osztalyId'] == '') $r[$i]['osztalyId'] = 'iskola'; // tárgyanként az összes jegy átlagát írjuk ki
        	$ret[ $r[$i]['targyId'] ][ $r[$i]['osztalyId'] ] = $r[$i]['atlag'];
    	    } else {
    		$ret['iskola'] = $r[$i]['atlag']; // nem használjuk - ez az összes jegyek átlaga
    	    }
        return $ret;

    }

    function getOsztalyHianyzasOsszesites($szemeszterAdat, $SET = array('telephelyId'=>null)) {
        $ret = array();
        if (is_array($szemeszterAdat)) {
           // Egy szemeszter hiányzási adatainak lekérdezése
            if ($szemeszterAdat['statusz'] == 'aktív') {
		// Mindenféle típus kell?
    		$Wnemszamit = defWnemszamit();            
                // Folyó tanév - a tanév adatbázisból kérdezünk le
/*                $q = "SELECT osztalyId,
                            COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) AS igazolt,
                            COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) AS igazolatlan,
                            SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) AS kesesPercOsszeg
                        FROM `%s`.hianyzas
			".$Wnemszamit['join']."
			LEFT JOIN osztalyDiak ON (hianyzas.diakId=osztalyDiak.diakId AND beDt<='%s' 
			    AND (kiDt IS NULL OR '%s'<=kiDt))
			WHERE (tipus = 'hiányzás' OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL))
			    AND dt<='%s'
			    ".$Wnemszamit['nemszamit']."
			GROUP BY osztalyId WITH ROLLUP";
*/
		$tanevDbNev = tanevDbNev(__INTEZMENY, $szemeszterAdat['tanev']);

		$q = "SELECT osztalyId, SUM(igazolt) AS igazolt, SUM(igazolatlan) AS igazolatlan, SUM(kesesPercOsszeg) AS kesesPercOsszeg,
			    SUM(igazolatlanKesesbol) AS igazolatlanKesesbol, SUM(osszesIgazolatlan) AS osszesIgazolatlan FROM
			(SELECT osztalyId,hianyzas.diakId,
			    (COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) + IFNULL((SELECT SUM(dbHianyzas) FROM `".$tanevDbNev."`.hianyzasHozott WHERE hianyzasHozott.diakId=hianyzas.diakId AND statusz='igazolt'),0)) AS igazolt,
			    (COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) + IFNULL((SELECT SUM(dbHianyzas) FROM `".$tanevDbNev."`.hianyzasHozott WHERE hianyzasHozott.diakId=hianyzas.diakId AND statusz='igazolatlan'),0)) AS igazolatlan,
			    IFNULL(SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)),0) AS kesesPercOsszeg,
			    SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) DIV 45 as igazolatlanKesesbol,
			    (
				COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL))+IFNULL((SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) DIV 45),0) 
				+ IFNULL((SELECT SUM(dbHianyzas) FROM `".$tanevDbNev."`.hianyzasHozott WHERE hianyzasHozott.diakId=hianyzas.diakId AND statusz='igazolatlan'),0)
			    )
			    AS osszesIgazolatlan
			 FROM `".$tanevDbNev."`.hianyzas
			 LEFT JOIN tankorTipus USING (tankorTipusId)
			 LEFT JOIN osztalyDiak ON (hianyzas.diakId=osztalyDiak.diakId AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt))
			 WHERE (tipus = 'hiányzás' OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL)) AND dt<='%s' AND hianyzasBeleszamit='igen'
			 GROUP BY osztalyId,hianyzas.diakId
		    ) AS diakHianyzas GROUP BY osztalyId WITH ROLLUP";
//		$v = array(tanevDbNev(__INTEZMENY, $szemeszterAdat['tanev']), $szemeszterAdat['zarasDt'], $szemeszterAdat['kezdesDt'], $szemeszterAdat['zarasDt']);
// Csak az záráskori tagokat vegyük figyelembe
		$v = array($szemeszterAdat['zarasDt'], $szemeszterAdat['zarasDt'], $szemeszterAdat['zarasDt']);
            } else {
                // lezárt tanév - az intézmény adatbázisból kérdezünk le - nincs $Wnemszamit !!!
                $q = "SELECT osztalyId, SUM(igazolt) AS igazolt, SUM(igazolatlan) AS igazolatlan, SUM(kesesPercOsszeg) AS kesesPercOsszeg, 
			    SUM(kesesPercOsszeg DIV 45) AS igazolatlanKesebol, SUM(igazolatlan + (kesesPercOsszeg DIV 45)) AS osszesIgazolatlan
			FROM hianyzasOsszesites 
			LEFT JOIN osztalyDiak ON (hianyzasOsszesites.diakId=osztalyDiak.diakId AND beDt<='%s' 
			    AND (kiDt IS NULL OR '%s'<=kiDt))
			WHERE tanev=%u AND szemeszter=%u
			GROUP BY osztalyId WITH ROLLUP";
//		$v = array($szemeszterAdat['zarasDt'], $szemeszterAdat['kezdesDt'], $szemeszterAdat['tanev'], $szemeszterAdat['szemeszter']);
// Csak az záráskori tagokat vegyük figyelembe
		$v = array($szemeszterAdat['zarasDt'], $szemeszterAdat['zarasDt'], $szemeszterAdat['tanev'], $szemeszterAdat['szemeszter']);
            }
            $ret = db_query($q, array('fv' => 'getOsztalyHianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'osztalyId', 'values' => $v));
        }
        return $ret;
    }

    function getZarojegyStatBySzemeszter($SZA, $SET = array('telephelyId'=>null)) {

	// Le kell kérdezni minden zárójegyet osztályonként
	$tanev = $SZA['tanev'];
	$telephelyId = readVariable($SET['telephelyId'],'id');

	$OSZTALYOK = getOsztalyok($tanev,array('result'=>'indexed','telephelyId'=>$telephelyId));
	for ($i=0; $i<count($OSZTALYOK); $i++) {
	    $osztalyIdk[] = $OSZTALYOK[$i]['osztalyId'];
	}
	$DIAKIDS = getDiakokByOsztalyId($osztalyIdk, array(
	    'result' => 'multiassoc', 'tanev' => $tanev, 'tolDt' => $SZA['zarasDt'], 'igDt' => $SZA['zarasDt'], 
	    'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva'))
	);
	$ret['intezmeny'] = array('kituno' => 0, 'jeles' => 0, 'bukott' => 0, 'bukas' => 0, 'atlag' => 0, 'osztalyDb' => 0);
	foreach($DIAKIDS as $osztalyId => $DIAKOK) {
	    $diakIds = array();
	    $dbDiaknakVanJegye = 0;
	    for ($i=0; $i<count($DIAKOK); $i++) $diakIds[] = $DIAKOK[$i]['diakId'];
	    $D = getDiakZarojegyek($diakIds, $SZA['tanev'], $SZA['szemeszter'], array('arraymap'=>array('diakId')));
	    $O = array('letszam' => count($diakIds), 'kituno' => 0, 'jeles' => 0, 'bukott' => 0, 'bukas' => 0, 'atlag' => 0);
	    foreach ($D as $diakId => $dJegyek) {
		$lehetJeles = true; $bukas = 0; $sum = 0; $db = 0;
		for ($i = 0; $i < count($dJegyek); $i++) {
		    if (in_array($dJegyek[$i]['jegyTipus'], array('jegy','magatartás','szorgalom'))) { // A statisztika csak a jegy típusra értelmes!
			if ($dJegyek[$i]['jegyTipus'] == 'jegy') {
			    $sum += $dJegyek[$i]['jegy'];
			    $db++;
			    if ($dJegyek[$i]['jegy'] < _JELES_LEGGYENGEBB_JEGY) $lehetJeles = false;
			}
			if ($dJegyek[$i]['jegy'] == 1) {
			    $bukas++;
			    $ret['intezmeny']['targy'][ $dJegyek[$i]['targyId'] ]++;
			}
		    }
		}
		if ($db != 0) { // volt legalább 1 jegy típusú jegye
		    $dbDiaknakVanJegye++;
		    $atlag = @floor(100 * $sum / $db) / 100;
		    $O['atlag'] += $atlag;
		    if ($atlag >= _KITUNO_ATLAG) $O['kituno']++;
		    elseif ($atlag >= _JELES_ATLAG && $lehetJeles) $O['jeles']++;
		    if ($bukas > 0) {
			$O['bukas'] += $bukas;
			$O['bukott']++;
		    }
		}
	    }
	    //if (count($diakIds) > 0)  $O['atlag'] = $O['atlag'] / count($diakIds);
	    if($dbDiaknakVanJegye>0)	$O['atlag'] = floor(100 * $O['atlag'] / $dbDiaknakVanJegye) / 100;
	    $ret[$osztalyId] = $O;
	    $ret['intezmeny']['kituno'] += $O['kituno'];
	    $ret['intezmeny']['jeles'] += $O['jeles'];
	    $ret['intezmeny']['bukott'] += $O['bukott'];
	    $ret['intezmeny']['bukas'] += $O['bukas'];
	    $ret['intezmeny']['atlag'] += $O['atlag'];
	    if ($O['atlag'] > 0) $ret['intezmeny']['osztalyDb']++;
	}
	if ($ret['intezmeny']['osztalyDb'] > 0) $ret['intezmeny']['atlag'] = $ret['intezmeny']['atlag'] / $ret['intezmeny']['osztalyDb'];
	return $ret;

    }

    function getDiakKonferenciaZaradekok($diakIds, $utolsoTanitasiNap) {

	return getZaradekokByDiakIds($diakIds, array('tipus' => 'konferencia, konferencia bukás', 'tolDt' => $utolsoTanitasiNap, 'igDt' => $utolsoTanitasiNap));

    }

?>