<?php

    function getBeiskolazasiTerv($tanev) {

	$q = "select * from tovabbkepzesTanulmanyiEgyseg left join tovabbkepzes using (tovabbkepzesId) left join tovabbkepzoIntezmeny using (tovabbkepzoIntezmenyId)
		left join tovabbkepzesTanar using (tanarId, tovabbkepzesId)
		where tovabbkepzesStatusz in ('terv','jóváhagyott','teljesített') and tanev=%u";
	return db_query($q, array('fv'=>'getBeiskolazasiTerv','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array($tanev)));

    }

    function beiskolazasNyomtatvanyKeszites($ADAT) {

	global $Honapok;

	// A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        $tmplFile = 'beiskolazasiTerv';

        if (file_exists(__TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/'.__INTEZMENY.'/'.$tmplFile.'.tmpl';
        } elseif (file_exists(__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl')) {
            $templateFile = __TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
        } else {
            $_SESSION['alert'][] = 'message:file_not_found:'.__TEMPLATE_DIR.'/default/'.$tmplFile.'.tmpl';
            return false;
        }

	$Intezmeny = getIntezmenyByRovidnev(__INTEZMENY);
	list($ev, $ho, $nap) = explode('-', $ADAT['tanulmanyiEgyseg']['igDt']);
	$DATA = array(
            'file' => $ADAT['file'],
            'base' => array('nyomtatasDt' => date('Y.m.d'), 'nyomtatasDatumStr' => date('Y. m. d.'), 
		'datumEv' => $ev, 'datumHonap' => $ho, 'datumNap' => $nap, 'hoNev' => kisbetus($Honapok[$ho-1]),
                'intezmenyNev' => $Intezmeny['nev'], 'intezmenyOMKod' => $Intezmeny['OMKod'], 'intezmenyCimIrsz' => $Intezmeny['cimIrsz'],
                'intezmenyCimHelyseg' => $Intezmeny['cimHelyseg'], 'intezmenyCimKozteruletNev' => $Intezmeny['cimKozteruletNev'],
                'intezmenyCimKozteruletJelleg' => $Intezmeny['cimKozteruletJelleg'], 'intezmenyCimHazszam' => $Intezmeny['cimHazszam'],
                'intezmenyTelefon' => $Intezmeny['telefon'], 'intezmenyHonlap' => $Intezmeny['honlap'],
                'intezmenyFax' => $Intezmeny['fax'], 'intezmenyEmail' => $Intezmeny['email'],

		'tanevJele'=>$ADAT['tanev'].'/'.($ADAT['tanev']+1), 'tanev'=>$ADAT['tanev'],

		'tanulmanyiEgyseg' => range(0, count($ADAT['tanulmanyiEgyseg'])-1),

            ),
	    'tanulmanyiEgyseg' => $ADAT['tanulmanyiEgyseg']

	);
	for ($i = 0; $i < count($ADAT['tanulmanyiEgyseg']); $i++) {
	    $TE = $ADAT['tanulmanyiEgyseg'][$i];
	    $DATA['tanulmanyiEgyseg'][$i]['tanarNev'] = $ADAT['tanarok'][ $TE['tanarId'] ]['tanarNev'];
	    $DATA['tanulmanyiEgyseg'][$i]['tolDt'] = str_replace('-','. ',$TE['tolDt']).'.';
	    $DATA['tanulmanyiEgyseg'][$i]['igDt'] = str_replace('-','. ',$TE['igDt']).'.';
	    //$DATA['tanulmanyiEgyseg'][$i]['tanarBesorolas'] = $ADAT['tanarok'][ $TE['tanarId'] ]['besorolas'];
	}

//dump($ADAT);
//dump($DATA);
//return false;
//die();
	return template2file($templateFile, $DATA);
    }

?>