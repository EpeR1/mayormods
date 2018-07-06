<?php

    function torzslapNyomtatvanyKeszites($ADAT) {

	global $Honapok, $KOVETELMENY, $ZaradekIndex, $bizonyitvanyJegyzetek;

	// A sablonfile meghatározása
	define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
	$tmplFile = 'torzslap';
	
	if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl')) {
	    $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl';
	} elseif (file_exists(__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl')) {
	    $templateFile = __TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
	} else {
	    $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
	    return false;
	}

	$Intezmeny = getIntezmenyByRovidnev(__INTEZMENY);
	$Ertekeles = getOsztalySzovegesErtekeles($ADAT);
	$_Targyak = getTargyak(array('targySorrendNev' => $ADAT['targySorrendNev'],'osztalyId' => $ADAT['osztalyId'])); $Targyak = array();
	for ($i = 0; $i < count($_Targyak); $i++) {
	    $Targyak[ $_Targyak[$i]['targyId'] ] = $_Targyak[$i];
	    $Targyak[ $_Targyak[$i]['targyId'] ]['targyNev'] = nagybetus(mb_substr($_Targyak[$i]['targyNev'],0,1)).mb_substr($_Targyak[$i]['targyNev'],1);
	}
	unset($_Targyak);

	list($ev,$ho,$nap) = explode('-', $ADAT['dt']);
	list($evf,$oszt) = explode('.', $ADAT['osztalyAdat']['osztalyJel']);
	
	$DATA = array(
	    'file' => $ADAT['file'],
	    'base' => array('nyomtatasDt' => date('Y.m.d'), 'datumEv' => $ev, 'datumHonap' => $ho, 'datumNap' => $nap, 'hoNev' => kisbetus($Honapok[$ho-1]),
		'intezmenyNev' => $Intezmeny['nev'], 'intezmenyOMKod' => $Intezmeny['OMKod'], 'intezmenyCimIrsz' => $Intezmeny['cimIrsz'], 
		'intezmenyCimHelyseg' => $Intezmeny['cimHelyseg'], 'intezmenyCimKozteruletNev' => $Intezmeny['cimKozteruletNev'],
		'intezmenyCimKozteruletJelleg' => $Intezmeny['cimKozteruletJelleg'], 'intezmenyCimHazszam' => $Intezmeny['cimHazszam'],
		'intezmenyTelefon' => $Intezmeny['telefon'], 'intezmenyHonlap' => $Intezmeny['honlap'],
		'intezmenyFax' => $Intezmeny['fax'], 'intezmenyEmail' => $Intezmeny['email'],
		'tanevJele' => substr($ADAT['szemeszter']['tanevAdat']['kezdesDt'],0,4).'/'.substr($ADAT['szemeszter']['tanevAdat']['zarasDt'],2,2),
		'felevi' => ($ADAT['szemeszter']['szemeszter'] == 1),
		'diak' => $ADAT['diakIds'], 'osztaly' => "$evf. ".nagybetus($oszt),
		'szovegesErtekeles' => false,
	    ),
	    'diak' => $ADAT['diakAdat'],
	    'targy' => $Targyak,
	    'szempont' => array(),
	    'minosites' => array(),
	);

	unset($Intezmeny);
	// Az osztályzatokhoz tartozó tárgyak (lesznek szöveges értékeléshez tartozók is....)
	for ($i = 0; $i < count ($ADAT['targyak']); $i++) {
            $DATA['osztalyzatTargy'][ $ADAT['targyak'][$i]['targyId'] ] = $ADAT['targyak'][$i];
        }
        if (is_array($ADAT['jegyek']) && count($ADAT['jegyek']) > 0) foreach ($ADAT['jegyek'] as $diakId => $dJegyek) {
            if (is_array($dJegyek) && count($dJegyek) > 0) { // Ha vanak egyáltalán jegyei...
                foreach ($DATA['osztalyzatTargy'] as $targyId => $tAdat) { // A helyes sorrend miatt kell ezen végigmenni
                    if (is_array($dJegyek[$targyId])) { // van az adott tárgyból jegye
			$targyNev = kisbetus($Targyak[$targyId]['targyNev']);
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
                            $DATA['diak'][$diakId]['osztalyzatTargy'][$targyId] = $jegy;
                            $DATA['diak'][$diakId]['osztalyzatTargy'][$targyId]['jTipus'] = $jegy['jegyTipus'];
                            $DATA['diak'][$diakId]['osztalyzatTargy'][$targyId]['mJel'] = nagybetus($jegy['megjegyzes'][0]);
                            $DATA['diak'][$diakId]['osztalyzatTargy'][$targyId]['hivatalos'] = $KOVETELMENY[ $jegy['jegyTipus'] ][ $jegy['jegy'] ]['hivatalos'];
			    if ($jegy['megjegyzes'] == 'dicséret') { // csak dicséreteket rakunk be - év végén nincs más
				$DATA['diak'][$diakId]['bejegyzesek'] .= str_replace('%1%', $targyNev, $bizonyitvanyJegyzetek['dicséret'])."\n\n";
			    }
                            $DATA['diak'][$diakId]['osztalyzatTargy'][$targyId]['evesOraszam'] = $ADAT['targyOraszam'][$diakId][$targyId]['evesOraszam'];
                        }
                    }
                }
            }
        }

	$DATA['diak'][$diakId]['feljegyzesek'] = $DATA['diak'][$diakId]['zaroZaradek'] = $DATA['diak'][$diakId]['zaradekok'] = '';
	if (is_array($ADAT['diakZaradekok'])) {
	    foreach ($ADAT['diakZaradekok'] as $diakId => $dZaradekok) {
		if (count($dZaradekok) > 0) {
		    foreach ($dZaradekok as $idx => $zAdat) {
			if (in_array($zAdat['zaradekIndex'], array_values($ZaradekIndex['törzslap feljegyzés']))) {
			    $DATA['diak'][$diakId]['feljegyzesek'] .= '['.$zAdat['sorszam'].'] '.$zAdat['szoveg'].' ('.dateToString($zAdat['dt']).')';
			} elseif (
			    in_array($zAdat['zaradekIndex'], array_values($ZaradekIndex['konferencia']))
			    || in_array($zAdat['zaradekIndex'], array_values($ZaradekIndex['konferencia bukás']))
			) {
			    $DATA['diak'][$diakId]['zaroZaradek'] .= '['.$zAdat['sorszam'].'] '.$zAdat['szoveg'].' ('.dateToString($zAdat['dt']).')'."\n\n";
			} else {
			    $DATA['diak'][$diakId]['zaradekok'] .= '['.$zAdat['sorszam'].'] '.$zAdat['szoveg'].' ('.dateToString($zAdat['dt']).')'."\n\n";
			}
		    }
		}
	    }
	}
	if (is_array($ADAT['diakBejegyzesek'])) {
	    foreach ($ADAT['diakBejegyzesek'] as $diakId => $dBejegyzesek) {
		if (is_null($DATA['diak'][$diakId]['bejegyzesek'])) $DATA['diak'][$diakId]['bejegyzesek'] = '';
		if (count($dBejegyzesek)>0) {
		    foreach ($dBejegyzesek as $idx => $bAdat) {
			$DATA['diak'][$diakId]['bejegyzesek'] .= $bAdat['szoveg']."\n\n";
		    }
		}
	    }
	}
	if (is_array($ADAT['diakAdat'])) {
	    foreach ($ADAT['diakAdat'] as $diakId => $dAdat) { // alapértelmezések
		if ($DATA['diak'][$diakId]['feljegyzesek'] == '') $DATA['diak'][$diakId]['feljegyzesek'] = '\ ';
		foreach (array('igazolt','igazolatlan','kesesPercOsszeg','kesesIgazolatlan','osszesIgazolatlan',
			'gyakIgazolt','gyakIgazolatlan','gyakKesesPercOsszeg','gyakKesesIgazolatlan','gyakOsszesIgazolatlan',
			'elmIgazolt','elmIgazolatlan','elmKesesPercOsszeg','elmKesesIgazolatlan','elmOsszesIgazolatlan') as $_k) {
		    $DATA['diak'][$diakId][$_k] = 0;
		}
	    }
	}
	if (is_array($ADAT['hianyzas'])) {
    	    foreach ($ADAT['hianyzas'] as $diakId => $dHianyzas) {
                $DATA['diak'][$diakId]['igazolt'] = intval($dHianyzas['igazolt']);
                $DATA['diak'][$diakId]['igazolatlan'] = intval($dHianyzas['igazolatlan']);
                $DATA['diak'][$diakId]['kesesPercOsszeg'] = intval($dHianyzas['kesesPercOsszeg']);
                $DATA['diak'][$diakId]['kesesIgazolatlan'] = floor($dHianyzas['kesesPercOsszeg']/45);
                $DATA['diak'][$diakId]['osszesIgazolatlan'] = intval($DATA['diak'][$diakId]['igazolatlan']+$DATA['diak'][$diakId]['kesesIgazolatlan']);

                $DATA['diak'][$diakId]['gyakIgazolt'] = intval($dHianyzas['gyakorlatIgazolt']);
                $DATA['diak'][$diakId]['gyakIgazolatlan'] = intval($dHianyzas['gyakorlatIgazolatlan']);
                $DATA['diak'][$diakId]['gyakKesesPercOsszeg'] = intval($dHianyzas['gyakorlatKesesPercOsszeg']);
                $DATA['diak'][$diakId]['gyakKesesIgazolatlan'] = floor($dHianyzas['gyakorlatKesesPercOsszeg']/45);
                $DATA['diak'][$diakId]['gyakOsszesIgazolatlan'] = intval($DATA['diak'][$diakId]['gyakIgazolatlan']+$DATA['diak'][$diakId]['gyakKesesIgazolatlan']);

                $DATA['diak'][$diakId]['elmIgazolt'] = intval($dHianyzas['elmeletIgazolt']);
                $DATA['diak'][$diakId]['elmIgazolatlan'] = intval($dHianyzas['elmeletIgazolatlan']);
                $DATA['diak'][$diakId]['elmKesesPercOsszeg'] = intval($dHianyzas['elmeletKesesPercOsszeg']);
                $DATA['diak'][$diakId]['elmKesesIgazolatlan'] = floor($dHianyzas['elmeletKesesPercOsszeg']/45);
                $DATA['diak'][$diakId]['elmOsszesIgazolatlan'] = intval($DATA['diak'][$diakId]['elmIgazolatlan']+$DATA['diak'][$diakId]['elmKesesIgazolatlan']);
    	    }
	}
	// van egyáltalán tanév adat?
	if (strtotime($ADAT['dt']) < strtotime($ADAT['tanevAdat']['szemeszter'][1]['zarasDt'])) $DATA['base']['negyedev'] = 'az első';
	else $DATA['base']['negyedev'] = 'a harmadik';
	foreach ($Ertekeles as $diakId => $E) {
	    $DATA['diak'][$diakId]['targy'] = array();

	    // !!! Tesztelendő !!! //
#	    foreach ($E as $targyId => $eAdat) {
	    foreach ($Targyak as $targyId => $tAdat) {
		if (is_array($E[$targyId])) {
		    $eAdat = $E[$targyId];
		    $DATA['base']['szovegesErtekeles'] = true;
		} else {
		    continue;
		}
	    // !!! Tesztelendő !!! //
		if (
		    is_array($eAdat['szovegesErtekeles']['minosites']) 
		    && count($eAdat['szovegesErtekeles']['minosites']) > 0
		    && $ADAT['tolDt'] <= $eAdat['szovegesErtekeles']['dt']
		) {

		    $DATA['diak'][$diakId]['targy'][$targyId] = $eAdat; // Ezt lehetne finomítani

		    $DATA['diak'][$diakId]['targy'][$targyId]['szempont'] = $eAdat['szempontRendszer']['szempont'];
                    $targyMinositesId = $eAdat['szovegesErtekeles']['minosites'][0]; // Ha egy tárgy egy minősítés kell (az első)
		    foreach ($eAdat['szempontRendszer']['szempont'] as $szempontId => $szAdat) {
			$DATA['diak'][$diakId]['targy'][$targyId]['szempont'][$szempontId]['egyediMinosites'] = 
				$eAdat['szovegesErtekeles']['egyediMinosites'][$szempontId]['egyediMinosites'];
			$M = $eAdat['szempontRendszer']['minosites'][$szempontId];
			$elsoValasztottKovetkezik = true;
                	for ($i = 0; $i < count($M); $i++) {
			    $DATA['diak'][$diakId]['targy'][$targyId]['szempont'][$szempontId]['minosites'][ $M[$i]['minositesId'] ] 
				= array('minosites' => $M[$i]['minosites']);
			    if (in_array($M[$i]['minositesId'], $eAdat['szovegesErtekeles']['minosites'])) {
				$DATA['diak'][$diakId]['targy'][$targyId]['szempont'][$szempontId]['minosites'][$M[$i]['minositesId']]['valasztott'] = true;
				$DATA['diak'][$diakId]['targy'][$targyId]['szempont'][$szempontId]['minosites'][$M[$i]['minositesId']]['elsoValasztott'] = $elsoValasztottKovetkezik;
				$DATA['diak'][$diakId]['targy'][$targyId]['szempont'][$szempontId]['minosites'][$M[$i]['minositesId']]['tobbedikValasztott'] = (!$elsoValasztottKovetkezik);
				$elsoValasztottKovetkezik = false;
                    		if ($M[$i]['minositesId'] == $targyMinositesId) {
                        	    $DATA['diak'][$diakId]['targy'][$targyId]['targyMinosites'] = $M[$i]['minosites']; // Ha tárgyanként egy minősítés kell (az első)
                        	}
                    	    }
                	}
		    }
		}
	    }
	}

	return template2file($templateFile, $DATA);

    }


?>
