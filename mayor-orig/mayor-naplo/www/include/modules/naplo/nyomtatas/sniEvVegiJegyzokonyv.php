<?php

    function generateJegyzokonyv($ADAT) {

	global $Honapok, $_TANEV;

        // A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        $tmplFile = 'sniEvVegiJegyzokonyv';
        if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl';
        } elseif (file_exists(__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
        } else {
            $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
            return false;
        }

	list($kEv,$kHo,$kNap) = explode('-', $_TANEV['kezdesDt']);
	list($zEv,$zHo,$zNap) = explode('-', $_TANEV['zarasDt']);
	if (!is_array($ADAT['osztalyTanar'])) $ADAT['osztalyTanar'] = array();
        $DATA = array(
            'file' => $ADAT['file'],
            'base' => array('nyomtatasDt' => date('Y.m.d'),
		'tanev' => "$kEv-$zEv",
//		'ev' => $ev, 'honap' => $ho, 'nap' => $nap, 'hoNev' => kisbetus($Honapok[$ho-1]),
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

//echo '<pre>';
//var_dump($ADAT['diakAdat']);
//echo '</pre>';

	return template2file($templateFile, $DATA);

    }

?>
