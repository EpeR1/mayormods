<?php

    function nyomtatvanyKeszites($ADAT) {

	global $Honapok;

	// A sablonfile meghatározása
	define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
	if (isset($ADAT['szemeszterId'])) $tmplFile = 'szovegesZaroErtekeles';
	else $tmplFile = 'szovegesErtekeles';
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
	    'base' => array('nyomtatasDt' => date('Y.m.d'), 'ev' => $ev, 'honap' => $ho, 'nap' => $nap, 'hoNev' => kisbetus($Honapok[$ho-1]),
		'intezmenyNev' => $Intezmeny['nev'], 'intezmenyOMKod' => $Intezmeny['OMKod'], 'intezmenyCimIrsz' => $Intezmeny['cimIrsz'], 
		'intezmenyCimHelyseg' => $Intezmeny['cimHelyseg'], 'intezmenyCimKozteruletNev' => $Intezmeny['cimKozteruletNev'],
		'intezmenyCimKozteruletJelleg' => $Intezmeny['cimKozteruletJelleg'], 'intezmenyCimHazszam' => $Intezmeny['cimHazszam'],
		'intezmenyTelefon' => $Intezmeny['telefon'], 'intezmenyHonlap' => $Intezmeny['honlap'],
		'intezmenyFax' => $Intezmeny['fax'], 'intezmenyEmail' => $Intezmeny['email'],
		'tanevJele' => substr($ADAT['tanevAdat']['kezdesDt'],0,4).'/'.substr($ADAT['tanevAdat']['zarasDt'],0,4),
		'felevi' => ($ADAT['szemeszter']['szemeszter'] == 1),
		'diak' => $ADAT['diakIds'], 'osztaly' => "$evf. ".nagybetus($oszt),
	    ),
	    'diak' => $ADAT['diakAdat'],
	    'targy' => $Targyak,
	    'szempont' => array(),
	    'minosites' => array(),
	);
	unset($Intezmeny);

        foreach ($ADAT['hianyzas'] as $diakId => $dHianyzas) {
                $DATA['diak'][$diakId]['igazolt'] = $dHianyzas['igazolt'];
                $DATA['diak'][$diakId]['igazolatlan'] = $dHianyzas['igazolatlan'];
                $DATA['diak'][$diakId]['kesesPercOsszeg'] = intval($dHianyzas['kesesPercOsszeg']);
                $DATA['diak'][$diakId]['kesesIgazolatlan'] = floor($dHianyzas['kesesPercOsszeg']/45);
                $DATA['diak'][$diakId]['osszesIgazolatlan'] = $DATA['diak'][$diakId]['igazolatlan']+$DATA['diak'][$diakId]['kesesIgazolatlan'];
                
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



	if (strtotime($ADAT['dt']) < strtotime($ADAT['tanevAdat']['szemeszter'][1]['zarasDt'])) $DATA['base']['negyedev'] = 'az első';
	else $DATA['base']['negyedev'] = 'a harmadik';
	foreach ($Ertekeles as $diakId => $E) {
	    $DATA['diak'][$diakId]['targy'] = array();

	    // !!! Tesztelendő !!! //
#	    foreach ($E as $targyId => $eAdat) {
	    foreach ($Targyak as $targyId => $tAdat) {
		if (is_array($E[$targyId])) {
		    $eAdat = $E[$targyId];
		} else {
		    continue;
		}
	    // !!! Tesztelendő !!! //
		if (
		    (
			   (is_array($eAdat['szovegesErtekeles']['minosites'])       && count($eAdat['szovegesErtekeles']['minosites']) > 0)
			|| (is_array($eAdat['szovegesErtekeles']['egyediMinosites']) && count($eAdat['szovegesErtekeles']['egyediMinosites']) > 0)
		    )
		    && $ADAT['tolDt'] <= $eAdat['szovegesErtekeles']['dt']
		) {

		    $DATA['diak'][$diakId]['targy'][$targyId] = $eAdat; // Ezt lehetne finomítani
//if ($targyId == 2) {
//echo '<pre>';var_dump($ADAT['tolDt']);
//var_dump($eAdat['szovegesErtekeles']['dt']);
//}

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
