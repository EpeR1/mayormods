<?php

    function exportBizonyitvany($file, $ADAT) {

	global $KOVETELMENY;

	$EXPORT = array();

	// fejléc
	// -- diák adatok
	foreach ($ADAT['diakAttrs'] as $attr) $EXPORT[0][] = $attr;
	// -- hiányzások
	if (true) {
		$EXPORT[0][] = "igazolt";
		$EXPORT[0][] = "igazolatlan";
		$EXPORT[0][] = "kesesPercOsszeg";
		$EXPORT[0][] = "gyakorlatIgazolt";
		$EXPORT[0][] = "gyakorlatIgazolatlan";
		$EXPORT[0][] = "gyakorlatKesesPercOsszeg";
		$EXPORT[0][] = "elmeletIgazolt";
		$EXPORT[0][] = "elmeletIgazolatlan";
		$EXPORT[0][] = "elmeletKesesPercOsszeg";
	}
	// -- jegyek
	foreach ($ADAT['targyak'] as $i => $targyAdat) {
	    $EXPORT[0][] = 'targy'.($i+1).'_nev';
	    $EXPORT[0][] = 'targy'.($i+1).'_oraszam';
	    $EXPORT[0][] = 'targy'.($i+1).'_jegy';
	}

	// adatok
	foreach ($ADAT['diakIds'] as $diakId) {
	    $ADAT['evesOraszam'][$diakId] = getTargyOraszamByDiakId($diakId, $ADAT);
	    $SOR = array();
	    // -- diák adatok
	    foreach ($ADAT['diakAttrs'] as $attr) $SOR[] = $ADAT['diakAdat'][$diakId][$attr];
	    if (true) {
		$SOR[] = $ADAT['hianyzas'][$diakId]["igazolt"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["igazolatlan"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["kesesPercOsszeg"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["gyakorlatIgazolt"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["gyakorlatIgazolatlan"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["gyakorlatKesesPercOsszeg"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["elmeletIgazolt"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["elmeletIgazolatlan"];
		$SOR[] = $ADAT['hianyzas'][$diakId]["elmeletKesesPercOsszeg"];
	    }
	    // -- jegyek
	    foreach ($ADAT['targyak'] as $i => $targyAdat) {
		$jegy = $ADAT['jegyek'][$diakId][ $targyAdat['targyId'] ][0];
		$SOR[] = $targyAdat['targyNev'];
		$SOR[] = $ADAT['evesOraszam'][$diakId][ $targyAdat['targyId'] ];
		$SOR[] = $KOVETELMENY[ $jegy['jegyTipus'] ][ $jegy['jegy'] ]['hivatalos'];
	    }

	    $EXPORT[] = $SOR;
	}

	if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $EXPORT, 'bizonyítvány');
	elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $EXPORT, 'bizonyítvány');
	elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $EXPORT, 'bizonyítvány');
	else return false;

    }

    function getTargyOraszamByDiakId($diakId, $ADAT) {

            $q = "SELECT targyId,oraszam FROM tankorDiak LEFT JOIN tankorSzemeszter USING (tankorId) LEFT JOIN tankor USING (tankorId)
                    WHERE diakId=%u AND tanev=%u AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
            $v = array($diakId, $ADAT['szemeszterAdat']['tanev'], $ADAT['szemeszterAdat']['zarasDt'], $ADAT['szemeszterAdat']['zarasDt']);
            $jres = db_query($q, array(
                'fv' => 'getTargyOraszamByDiakId', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'targyId', 'values' => $v
            ));
            $szDb = $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']; // Feltételezzük, hogy a szemeszterek számozása 1-től indul és folyamatos

            foreach ($jres as $targyId => $tAdat) {
                $oraszam = 0;
                for ($i = 0; $i < count($tAdat); $i++) {
                    $oraszam += $tAdat[$i]['oraszam'];
                }
                //$ret['targyOraszam'][$targyId]['hetiOraszam'] = $oraszam / $szDb;
		// itt mindenképp van osztalyId - ha nem adunk meg 'vegzos' paramétert, úgy az osztály alapján veszi...
                $ret[$targyId] = $oraszam / $szDb * getTanitasiHetekSzama(array('osztalyId'=>$ADAT['osztalyId']/* ,'vegzos'=>diakVegzosE($diakId) */));
            }
	return $ret;
    }

?>
