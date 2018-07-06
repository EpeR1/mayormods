<?php

    function getTanulmanyiEgyseg($tovabbkepzesId, $tanarId, $tanev) {

	$q = "select * from tovabbkepzesTanulmanyiEgyseg left join tovabbkepzes using (tovabbkepzesId) left join tovabbkepzoIntezmeny using (tovabbkepzoIntezmenyId)
		left join tovabbkepzesTanar using (tanarId, tovabbkepzesId)
		where tovabbkepzesId=%u and tanarId=%u and tanev=%u";
	return db_query($q, array('debug'=>false,'fv'=>'getTanulmanyiEgyseg','modul'=>'naplo_intezmeny','result'=>'record','values'=>array($tovabbkepzesId, $tanarId, $tanev)));

    }

    function tovabbkepzesNyomtatvanyKeszites($ADAT) {

	global $Honapok;

	// A sablonfile meghatározása
        define('__TEMPLATE_DIR', _MAYOR_DIR.'/print/module-naplo/templates');
        $tmplFile = 'tovabbkepzesHatarozat';

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

		'tanev'=>$ADAT['tanulmanyiEgyseg']['tanev'], 'reszosszeg'=>$ADAT['tanulmanyiEgyseg']['reszosszeg'], 'tamogatas'=>$ADAT['tanulmanyiEgyseg']['tamogatas'], 
		'tovabbkepzesStatusz'=>$ADAT['tanulmanyiEgyseg']['tovabbkepzesStatusz'], 'megjegyzes'=>$ADAT['tanulmanyiEgyseg']['megjegyzes'], 'tovabbkepzesNev'=>$ADAT['tanulmanyiEgyseg']['tovabbkepzesNev'], 
		'oraszam'=>$ADAT['tanulmanyiEgyseg']['oraszam'], 'akkreditalt'=>$ADAT['tanulmanyiEgyseg']['akkreditalt'], 'tovIntRovidNev'=>$ADAT['tanulmanyiEgyseg']['intezmenyRovidNev'],
		'tovIntNev'=>$ADAT['tanulmanyiEgyseg']['intezmenyNev'], 'tovIntCim'=>$ADAT['tanulmanyiEgyseg']['intezmenyCim'],
		'tolDt'=>str_replace('-','. ',$ADAT['tanulmanyiEgyseg']['tolDt']).'.', 'igDt'=>str_replace('-','. ',$ADAT['tanulmanyiEgyseg']['igDt']).'.',

		'tanarNev' => $ADAT['tanarAdat']['tanarNev'], 'szuletesiHely' => $ADAT['tanarAdat']['szuletesiHely'], 'szuletesiIdo' => $ADAT['tanarAdat']['szuletesiIdo'],
		'hetiMunkaora' => $ADAT['tanarAdat']['hetiMunkaora'], 'statusz' => $ADAT['tanarAdat']['statusz'], 'hetiKotelezoOraszam' => $ADAT['tanarAdat']['hetiKotelezoOraszam'], 
		'besorolas' => $ADAT['tanarAdat']['besorolas'],
            ),

	);

//dump($ADAT);
//dump($DATA);
//die();
	return template2file($templateFile, $DATA);
    }

?>