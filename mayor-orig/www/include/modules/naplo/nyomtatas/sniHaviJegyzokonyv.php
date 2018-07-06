<?php

    function generateJegyzokonyv($ADAT) {

	global $Honapok;

        // A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        $tmplFile = 'sniHaviJegyzokonyv';
        if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl';
        } elseif (file_exists(__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
        } else {
            $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
            return false;
        }

	list($ev,$ho,$nap) = explode('-', $ADAT['dt']);
        $DATA = array(
            'file' => $ADAT['file'],
            'base' => array('nyomtatasDt' => date('Y.m.d'),
		'ev' => $ev, 'honap' => $ho, 'nap' => $nap, 'hoNev' => kisbetus($Honapok[$ho-1]),
                'intezmenyNev' => $ADAT['intezmeny']['nev'], 'intezmenyCimIrsz' => $ADAT['intezmeny']['cimIrsz'],
                'intezmenyCimHelyseg' => $ADAT['intezmeny']['cimHelyseg'], 'intezmenyCimKozteruletNev' => $ADAT['intezmeny']['cimKozteruletNev'],
                'intezmenyCimKozteruletJelleg' => $ADAT['intezmeny']['cimKozteruletJelleg'], 'intezmenyCimHazszam' => $ADAT['intezmeny']['cimHazszam'],
                'intezmenyTelefon' => $ADAT['intezmeny']['telefon'], 'intezmenyHonlap' => $ADAT['intezmeny']['honlap'],
                'intezmenyFax' => $ADAT['intezmeny']['fax'], 'intezmenyEmail' => $ADAT['intezmeny']['email'],
                'diak' => $ADAT['sniDiakIds'], 'osztaly' => $ADAT['osztaly']['osztalyJel'],
                'tanarNev' => '', 'osztalyTanar' => array_keys($ADAT['osztalyTanar']),
            ),
            'diak' => $ADAT['diakAdat'],
            'felelos' => $ADAT['tanarok'],
            'osztalyTanar' => $ADAT['osztalyTanar'],
        );

	return template2file($templateFile, $DATA);

    }

?>
