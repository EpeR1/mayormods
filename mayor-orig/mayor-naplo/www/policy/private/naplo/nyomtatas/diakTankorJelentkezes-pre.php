<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {


	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');

	global $_TANEV, $Honapok;

	$ADAT['leadasiHatarido'] = '2008. június 9.';
	list($ev, $ho, $nap) = explode('-', readVariable($_POST['leadasiHatarido'], 'date'));
	$ADAT['leadasiHatarido'] = "$ev. ".kisbetus($Honapok[$ho-1])." $nap.";
	$ADAT['osztalyIds'] = readVariable($_POST['osztalyId'], 'numeric unsigned');
	if (isset($_POST['szemeszterId']) && $_POST['szemeszterId'] != '') {
	    $szemeszterId = $_POST['szemeszterId'];
	} else {
	    $_felev = getFelevByDt(date('Y-m-d'));
	    $szemeszterId = getKovetkezoSzemeszterId($_TANEV['szemeszter'][$_felev]['tanev'],$_TANEV['szemeszter'][$_felev]['szemeszter']);
	}
	$ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	$ADAT['tanev'] = $tanev = $ADAT['szemeszterAdat']['tanev'];
	$ADAT['szemeszterId'] = $szemeszterId;
	//igaziból nem kéne blokkba szervezni... var_dump($ADAT['szemeszterAdat']['statusz']=='aktív');
	// intézmény adatok
        $ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
//	$refDt = ($ADAT['szemeszterAdat']['kezdesDt']);
	$ADAT['tankorBlokkok'] = getTankorBlokkok($tanev);
	$ADAT['valasztott'] = getValasztottTankorok($tanev, $ADAT['szemeszterAdat']['szemeszter'], $ADAT['osztalyIds']);
	$ADAT['diakIds'] = array();
	if (is_array($ADAT['valasztott']['felvett'])) foreach ($ADAT['valasztott']['felvett'] as $diakId => $tankorIds) {
	    $ADAT['diakIds'][] = $diakId;
	}
	$ADAT['diakAdat'] = getDiakokById($ADAT['diakIds']);
	$ADAT['diakOsztaly'] = getDiakokOsztalyai($ADAT['diakIds'], array('tanev' => $tanev));
	$ADAT['osztalyok'] = getOsztalyok($tanev, array('result'=>'assoc'));

	// A TeX forrás generálása - A5-ös méretben
	if ($action == 'pdfGeneralas') {
	    $TeX = texLevelGeneralas($ADAT);
	    if (pdfLaTeX($TeX, 'faktJelentkezes-A5-'.date('Y-m-d'))) {

		// Az A5-ös lapok A4-es lapra helyezése
    		$TeX = '\documentclass[a4paper,landscape]{article}'."\n";
    		$TeX .= '\usepackage[final]{pdfpages}'."\n";
    		$TeX .= '\begin{document}'."\n";
    		$TeX .= '\includepdf[nup=2x1, pages={-}]{faktJelentkezes-A5-'.date('Y-m-d').'.pdf}'."\n";
    		$TeX .= '\end{document}'."\n";
		$fileName = fileNameNormal('faktJelentkezes-A4-'.date('Y-m-d'));
		if (pdfLaTeX($TeX, $fileName))
		    header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/diakTankorJelentkezes&file='.$fileName.'.pdf'));
	    }
	}

	$TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName'=>'szemeszterId', 'post'=>array('diakId'),
    				           'tanev'=>$tanev);

	getToolParameters();

    }
?>
