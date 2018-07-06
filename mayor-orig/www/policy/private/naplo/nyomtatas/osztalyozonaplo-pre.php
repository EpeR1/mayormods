<?php

if (_RIGHTS_OK !== true) die();

if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) {
    $_SESSION['alert'][] = 'page:insufficient_access';
} else {

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/szulo.php');
    require_once('include/modules/naplo/share/hianyzas.php');
    require_once('include/modules/naplo/share/file.php');
    require_once('include/modules/naplo/share/nap.php');
    require_once('include/modules/naplo/share/osztalyzatok.php');
    require_once('include/modules/naplo/share/kepzes.php');
    require_once('include/modules/naplo/nyomtatas/tex.php');
    require_once('include/share/date/names.php');
    require_once('include/share/str/tex.php');

    $osztalyId = readVariable($_POST['osztalyId'], 'id');

    if (isset($osztalyId)) {

	require_once('include/share/str/hyphen.php');

	// Adatok lekérése

	/* Az évfolyam meghatározása, osztály alapján */
	$evfolyamJel = getEvfolyamJel($osztalyId); //TODO: ellenőrzés (evfolyam-->evfolyamJel)
	// tanítási napok száma az aláíró ív záradékához
	$NSz = getNapokSzama(array('osztalyId' => $osztalyId));
	$ADAT['tanitasiNapokSzama'] = $NSz['tanítási nap']+$NSz['speciális tanítási nap'];

	if ($evfolyamJel=='') die('VÉGZETES HIBA o-pre.php');

	/* Tanárok kigyűjtése */
	$_TANKOROK  = getOsztalyTankorei($osztalyId);
	$_TANAROK = array();
	for ($i=0; $i<count($_TANKOROK); $i++) {
	    $_tankorId = $_TANKOROK[$i]['tankorId'];
	    $_res = getTankorTanaraiByInterval($_tankorId, array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'result' => 'nevsor'));
	    for ($j=0; $j<count($_res); $j++) {
		if (!in_array($_res[$j]['tanarNev'],$_TANAROK)) $_TANAROK[] = $_res[$j]['tanarNev'];
	    }
	}
	reset($_TANAROK);
	sort($_TANAROK);
	$ADAT['tanarok'] = $_TANAROK;
	unset($_TANAROK);
//	$ADAT['diakTankorei'] = array();

	// == Osztály adatai == //
	$ADAT['honapok'] = array();
	for (
	    $dt = date('Y-m-01', strtotime($_TANEV['kezdesDt'])); 
	    strtotime($dt) <= strtotime($_TANEV['zarasDt']); 
	    $dt = date('Y-m-01', strtotime('+1 month', strtotime($dt)))
	) $ADAT['honapok'][] = substr($dt, 5, 2);	    
	if (count($ADAT['honapok']) != 10 && __OSZTALYOZONAPLO_JEGYEK_FELEVENTE !== true) $_SESSION['alert'][] = 'message:wrong_data:hónapok száma '.count($ADAT['honapok']).' != 10';
	$ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId);
	$ADAT['targyak'] = getTanevTargySorByOsztalyId($osztalyId, __TANEV, 'napló');
	// Feltételezzük, hogy csak egyféle magatartás, illetve szorgalom tárgy lehet...
	list($ADAT['targyak']['magatartasId']) = getMagatartas();
	list($ADAT['targyak']['szorgalomId']) = getSzorgalom();
	for ($i = 0; $i < count($ADAT['targyak']); $i++) {
	    if (isset($ADAT['targyak'][$i]['targyNev'])) 
		$ADAT['targyak'][$i]['elvalasztott'] = str_replace(
		    array(' -', '--', '-', '~'),
		    array(' ', '~', '\-', '-'),
		    $huHyphen->hyphen(trim($ADAT['targyak'][$i]['targyNev']))
		);
	}

	$ADAT['diakAdat'] = $ADAT['diakIds'] = array();
	// diákok lekérdezése jogviszony és osztály tagság adatokkal
	$diakByOsztaly = getDiakokByOsztaly($osztalyId, array('orderBy' => 'naploSorszam', 'tanev' => $tanev, 'statusz' => array('jogviszonyban van','magántanuló','vendégtanuló') ));
	$ADAT['diakIds'] = array_values(
	    array_diff(array_keys($diakByOsztaly), array('jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve','jogviszonya lezárva','felvételt nyert'))
	);
	$ret = getDiakAdatById($ADAT['diakIds']);
	for ($i = 0; $i < count($ret); $i++) {
	    $ADAT['diakAdat'][ $ret[$i]['diakId'] ] = array_merge($ret[$i], $diakByOsztaly[ $ret[$i]['diakId'] ]);
	    $ADAT['diakTargy'][ $ret[$i]['diakId'] ] = getTargyakByDiakId($ret[$i]['diakId'],
			array('result'=>'idonly', 'osztatlyId' => $osztalyId, 'csakOratervi'=>true, 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'], 'filter' => 'kovetelmeny')
		    ); //TODO getTargyakByDiakId() itt a visszatérési érték változott, evfolyam, evfolyamJel is van! ellenőrizni kell!
//echo '<pre>'; var_dump($ret2); echo '</pre>';
	    
	    $diakKiDts[ $ret[$i]['diakId'] ] = $diakByOsztaly[ $ret[$i]['diakId'] ]['osztalyDiak'][0]['kiDt']; // mikor lépett ki a diák az osztályból
	}
	unset($ret);
	unset($diakByOsztaly);

	foreach ($_TANEV['szemeszter'] as $szemeszter => $szAdat) {
	    $ADAT['hianyzas'][$szemeszter] = getDiakHianyzasOsszesites($ADAT['diakIds'], $szAdat, $diakKiDts);
	    // Az egész szorgalmi időszak alatt szerzett osztályzatokra szükség van! (szükség van?)
	    $szAdat['zarasDt'] = $_TANEV['zarasDt'];
	    $szAdat['kezdesDt'] = $_TANEV['kezdesDt'];
	    $ADAT['zaroJegy'][$szemeszter] = getDiakZarojegyekByEvfolyamJel($ADAT['diakIds'], $evfolyamJel, $szAdat); //TODO: ellenőrizni, evfolyam-->evfolyamJel!!!
	}
	$ADAT['jegyek'] = getDiakJegyek($ADAT);
	$ADAT['szulok'] = getSzulok();
	// TeX generálás
	$filename = fileNameNormal('Osztalyozo_'.date('Ymd').'_'.$ADAT['osztalyAdat']['osztalyJel']);
	$lapDobasok = 0;
        $content =

	    putTeXOsztalyozoOldalbeallitas().
//            putTeXOldalbeallitasok().
//            putTeXMakrok().

            putTeXDefineFootline($ADAT['osztalyAdat']['osztalyJel'], $ADAT['osztalyAdat']['osztalyfonokNev']);

            // Tanulónként egy-egy lap....
            for ($i = 0; $i < count($ADAT['diakIds']); $i++) {
                $sorsz = $i+1; // Napló sorszám
                $diakId = $ADAT['diakIds'][$i];
                $cn = $ADAT['diakAdat'][$diakId]['diakNev'];
                $birthlocality = $ADAT['diakAdat'][$diakId]['szuletesiHely'];
                $birthtimestamp = $ADAT['diakAdat'][$diakId]['szuletesiIdo'];
                //$content .= putTeXOsztalyozoFejlec($sorsz,$cn,$birthlocality,$birthtimestamp);
                //$content .= putTeXOsztalyozoJegyek($diakId, $ADAT);
                //$content .= putTeXOsztalyozoAdatok($diakId, $ADAT);
                //$content .= putTeXLapdobas();
		//$lapDobasok++;
		$iGlobal = 0; // Ha nem férne ki 20 helyre a tárgyak listája...
		while ($iGlobal < count($ADAT['targyak'])) {
            	    $content .= putTeXOsztalyozoFejlec($sorsz,$cn,$birthlocality,$birthtimestamp);
            	    $content .= putTeXOsztalyozoJegyek($diakId, $ADAT, $iGlobal);
            	    $content .= putTeXOsztalyozoAdatok($diakId, $ADAT);
            	    $content .= putTeXLapdobas();
		    $lapDobasok++;
		}
            }

        $content .= putTeXTanarLista($ADAT, $lapDobasok).putTeXLapdobas();
        $content .= endTeXDocument();

	$filename = str_replace('/','_',$filename);
	if (generatePDF($filename, _DOWNLOADDIR.'/private/nyomtatas/osztalyozo', $content, __NYOMTATAS_FUZETKENT === true)) {
	    if (count($_SESSION['alert']) == 0)
		header('Location: '.location('index.php?page=session&f=download&download=true&dir=nyomtatas/osztalyozo&file='.$filename.'.pdf'));
	    else
		$ADAT['letoltes'] = 'index.php?page=session&f=download&download=true&dir=nyomtatas/osztalyozo&file='.$filename.'.pdf';
	}

    }

    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array());
    getToolParameters();

}		
		

?>
