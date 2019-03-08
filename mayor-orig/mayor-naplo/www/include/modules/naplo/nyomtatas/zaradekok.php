<?php

    function pdfZaradekok($ADAT) {

	global $Honapok;

        // A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        $tmplFile = 'zaradekok';
        if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl';
        } elseif (file_exists(__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
        } else {
            $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
            return false;
        }

	list($evf, $oszt) = explode('.', $ADAT['osztaly']['osztalyJel']);
	list($zEv, $zHo, $zNap) = explode('-', $ADAT['tanevAdat']['zarasDt']);

        $DATA = array(
            'file' => $ADAT['file'],
            'base' => array('nyomtatasDt' => date('Y.m.d'), 
                'intezmenyNev' => $ADAT['intezmeny']['nev'], 'intezmenyCimIrsz' => $ADAT['intezmeny']['cimIrsz'],
                'intezmenyCimHelyseg' => $ADAT['intezmeny']['cimHelyseg'], 'intezmenyCimKozteruletNev' => $ADAT['intezmeny']['cimKozteruletNev'],
                'intezmenyCimKozteruletJelleg' => $ADAT['intezmeny']['cimKozteruletJelleg'], 'intezmenyCimHazszam' => $ADAT['intezmeny']['cimHazszam'],
                'intezmenyTelefon' => $ADAT['intezmeny']['telefon'], 'intezmenyHonlap' => $ADAT['intezmeny']['honlap'],
                'intezmenyFax' => $ADAT['intezmeny']['fax'], 'intezmenyEmail' => $ADAT['intezmeny']['email'],
                'osztalyJele' => $evf.'/'.nagybetus($oszt), 'tanevJele' => $ADAT['tanev'].'/'.$zEv,
            ),
        );

	for ($i = 0; $i < count($ADAT['diakIds']); $i++) 
	    $DATA['diak'][ $ADAT['diakIds'][$i] ] = array('diakNev' => $ADAT['diak'][ $ADAT['diakIds'][$i] ]['diakNev']);
	foreach ($ADAT['zaradek'] as $diakId => $dZaradek) {
	    for ($i = 0; $i < count($dZaradek); $i++) {
		$DATA['diak'][$diakId]['zaradek'][ $dZaradek[$i]['zaradekId'] ] = $dZaradek[$i]['zaradekId'];
		$DATA['zaradek'][ $dZaradek[$i]['zaradekId'] ] = $dZaradek[$i];
	    }
	}
	foreach ($ADAT['bejegyzes'] as $diakId => $dBejegyzes) {
	    for ($i = 0; $i < count($dBejegyzes); $i++) {
		$DATA['diak'][$diakId]['bejegyzes'][ $dBejegyzes[$i]['bejegyzesId'] ] = $dBejegyzes[$i]['bejegyzesId'];
		$DATA['bejegyzes'][ $dBejegyzes[$i]['bejegyzesId'] ] = $dBejegyzes[$i];
		// A "bejegyzes" név már foglalt ezért át kell nevezni a bejegyzesTipusNev-et...
		$DATA['bejegyzes'][ $dBejegyzes[$i]['bejegyzesId'] ]['szovFokozat'] = $dBejegyzes[$i]['bejegyzesTipusNev'];
	    }
	}
	$DATA['base']['diak'] = array();
	for ($i = 0; $i < count($ADAT['diakIds']); $i++) {
	    if (
		is_array($DATA['diak'][ $ADAT['diakIds'][$i] ]['bejegyzes']) 
		|| is_array($DATA['diak'][ $ADAT['diakIds'][$i] ]['zaradek'])
	    ) $DATA['base']['diak'][] = $ADAT['diakIds'][$i];
	}

        return template2file($templateFile, $DATA);


    }

?>
