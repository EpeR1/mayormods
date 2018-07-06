<?php

    function pdfErtesito($ADAT) {

	global $KOVETELMENY, $Honapok, $bizonyitvanyMegjegyzesek; 

        // A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        $tmplFile = 'ertesito';
        if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl';
        } elseif (file_exists(__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
        } else {
            $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
            return false;
        }

	list($ev,$ho,$nap) = explode('-', $ADAT['szemeszterAdat']['zarasDt']);

        $DATA = array(
            'file' => $ADAT['file'],
            'base' => array('nyomtatasDt' => date('Y.m.d'), 'ev' => $ev, 'honap' => $ho, 'nap' => $nap, 'hoNev' => kisbetus($Honapok[$ho-1]),
		'tanevKezdesEv' => $ADAT['szemeszterAdat']['tanev'], 'tanevZarasEv' => ($ADAT['szemeszterAdat']['tanev']+1), 
		'szemeszter' => $ADAT['szemeszterAdat']['szemeszter'],
                'intezmenyNev' => $ADAT['intezmeny']['nev'], 'intezmenyCimIrsz' => $ADAT['intezmeny']['cimIrsz'],
                'intezmenyCimHelyseg' => $ADAT['intezmeny']['cimHelyseg'], 'intezmenyCimKozteruletNev' => $ADAT['intezmeny']['cimKozteruletNev'],
                'intezmenyCimKozteruletJelleg' => $ADAT['intezmeny']['cimKozteruletJelleg'], 'intezmenyCimHazszam' => $ADAT['intezmeny']['cimHazszam'],
                'intezmenyTelefon' => $ADAT['intezmeny']['telefon'], 'intezmenyHonlap' => $ADAT['intezmeny']['honlap'],
                'intezmenyFax' => $ADAT['intezmeny']['fax'], 'intezmenyEmail' => $ADAT['intezmeny']['email'],
                'diak' => array(), 'osztaly' => $ADAT['osztaly']['osztalyJel'],
		'magatartas' => '', 'szorgalom' => '', 
		'igazolt' => 0, 'igazolatlan' => 0, 'kesesIgazolatlan' => 0, 'kesesPercOsszeg' => 0, 
		'gyakIgazolt' => 0, 'gyakIgazolatlan' => 0, 'gyakKesesIgazolatlan' => 0, 'gyakKesesPercOsszeg' => 0, 
		'elmIgazolt' => 0, 'elmIgazolatlan' => 0, 'elmKesesIgazolatlan' => 0, 'elmKesesPercOsszeg' => 0, 
		'mJel' => '', 'megjegyzes' => '',
            ),
            'diak' => $ADAT['diakok'],
        );
	for ($i = 0; $i < count($ADAT['diakIds']); $i++) if (isset($ADAT['jegyek'][ $ADAT['diakIds'][$i] ])) $DATA['base']['diak'][] = $ADAT['diakIds'][$i];
	for ($i = 0; $i < count ($ADAT['targyak']); $i++) {
	    $DATA['targy'][ $ADAT['targyak'][$i]['targyId'] ] = $ADAT['targyak'][$i];
	}
	if (is_array($ADAT['jegyek']) && count($ADAT['jegyek']) > 0) foreach ($ADAT['jegyek'] as $diakId => $dJegyek) {
	    if (is_array($dJegyek) && count($dJegyek) > 0) { // Ha vanak egyáltalán jegyei...
		foreach ($DATA['targy'] as $targyId => $tAdat) { // A helyes sorrend miatt kell ezen végigmenni
		    if (is_array($dJegyek[$targyId])) { // van az adott tárgyból jegye
			$tJegyek = $dJegyek[$targyId];
			// Az utolsó jegyet írjuk csak ki
			$jegy = $tJegyek[ count($tJegyek)-1 ];
			if (in_array($targyId, $ADAT['szorgalomIds'])) {
			    $DATA['diak'][$diakId]['szorgalom'] = $KOVETELMENY['szorgalom'][ $jegy['jegy'] ]['hivatalos'];
			    $DATA['diak'][$diakId]['szorgMegjJel'] = nagybetus($jegy['megjegyzes'][0]);
			} elseif (in_array($targyId, $ADAT['magatartasIds'])) {
			    $DATA['diak'][$diakId]['magatartas'] = $KOVETELMENY['magatartás'][ $jegy['jegy'] ]['hivatalos'];
			    $DATA['diak'][$diakId]['magMegjJel'] = nagybetus($jegy['megjegyzes'][0]);
			} else {
			    $DATA['diak'][$diakId]['targy'][$targyId] = $jegy;
			    $DATA['diak'][$diakId]['targy'][$targyId]['jTipus'] = $jegy['jegyTipus'];
			    $DATA['diak'][$diakId]['targy'][$targyId]['mJel'] = nagybetus($jegy['megjegyzes'][0]);
			    $DATA['diak'][$diakId]['targy'][$targyId]['hivatalos'] = $KOVETELMENY[ $jegy['jegyTipus'] ][ $jegy['jegy'] ]['hivatalos'];
			    $DATA['diak'][$diakId]['targy'][$targyId]['rovid'] = $KOVETELMENY[ $jegy['jegyTipus'] ][ $jegy['jegy'] ]['rovid'];
			}
		    }
		}
	    }
	}
//	foreach ($ADAT['hianyzas'] as $diakId => $dHianyzas) { - A kimaradó diákok bajt okoznak => az összes diákon végig kell menni!
	foreach ($ADAT['diakIds'] as $key => $diakId) {
		$dHianyzas = $ADAT['hianyzas'][$diakId];

		$DATA['diak'][$diakId]['igazolt'] = $dHianyzas['igazolt'];
		$DATA['diak'][$diakId]['kesesPercOsszeg'] = intval($dHianyzas['kesesPercOsszeg']);
		$DATA['diak'][$diakId]['kesesIgazolatlan'] = floor($dHianyzas['kesesPercOsszeg']/45);
		$DATA['diak'][$diakId]['osszesIgazolatlan'] = $dHianyzas['igazolatlan']; // Tartalmazza az összes hivatalos hiányzást! A késések percösszegéből adódóakat is!!
		$DATA['diak'][$diakId]['igazolatlan'] = $dHianyzas['igazolatlan']-$DATA['diak'][$diakId]['kesesIgazolatlan']; 

		$DATA['diak'][$diakId]['gyakIgazolt'] = intval($dHianyzas['gyakorlatIgazolt']);
		$DATA['diak'][$diakId]['gyakIgazolatlan'] = intval($dHianyzas['gyakorlatIgazolatlan']);
		$DATA['diak'][$diakId]['gyakKesesPercOsszeg'] = intval($dHianyzas['gyakorlatKesesPercOsszeg']);
		$DATA['diak'][$diakId]['gyakKesesIgazolatlan'] = floor($dHianyzas['gyakorlatKesesPercOsszeg']/45);
		$DATA['diak'][$diakId]['gyakOsszesIgazolatlan'] = $DATA['diak'][$diakId]['gyakIgazolatlan']+$DATA['diak'][$diakId]['gyakKesesIgazolatlan'];

		$DATA['diak'][$diakId]['elmIgazolt'] = intval($dHianyzas['elmeletIgazolt']);
		$DATA['diak'][$diakId]['elmIgazolatlan'] = intval($dHianyzas['elmeletIgazolatlan']);
		$DATA['diak'][$diakId]['elmKesesPercOsszeg'] = intval($dHianyzas['elmeletKesesPercOsszeg']);
		$DATA['diak'][$diakId]['elmKesesIgazolatlan'] = floor($dHianyzas['elmeletKesesPercOsszeg']/45);
		$DATA['diak'][$diakId]['elmOsszesIgazolatlan'] = $DATA['diak'][$diakId]['elmIgazolatlan']+$DATA['diak'][$diakId]['elmKesesIgazolatlan'];

	}
//echo '<pre>';
//var_dump($DATA['base']['diak'], $ADAT['jegyek']);
//echo '</pre>';
//die();

        return template2file($templateFile, $DATA);


    }

?>
