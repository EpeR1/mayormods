<?php

    if (_RIGHTS_OK !== true) die();
    
    define('_TIME',strtotime(date('Y-m-d')));

    if (
	!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG
    ) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	require_once('include/share/date/names.php');
	require_once('include/share/print/pdf.php');
	require_once('include/modules/naplo/osztalyozo/stat.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/targy.php');

	$ADAT['magatartasIdk'] = getMagatartas();
        $ADAT['szorgalomIdk']= getSzorgalom();

	// melyik szemeszter adatait nézzük
	if (isset($_POST['szemeszterId']) && $_POST['szemeszterId'] != '') {
	    $szemeszterId = $_POST['szemeszterId'];
	} elseif (!isset($_POST['szemeszterId'])) {
	    for ($i = 1; $i <= count($_TANEV['szemeszter']); $i++) {
		if (
		    strtotime($_TANEV['szemeszter'][$i]['kezdesDt']) <= _TIME
		    && strtotime($_TANEV['szemeszter'][$i]['zarasDt']) >= _TIME
		) {
		    $_POST['szemeszterId'] = $szemeszterId = $_TANEV['szemeszter'][$i]['szemeszterId'];
		    break;
		}
	    }
	}
        if (isset($_POST['sorrendNev']) && $_POST['sorrendNev'] != '')  $ADAT['sorrendNev'] = $sorrendNev = $_POST['sorrendNev'];

	if (isset($_POST['osztalyId']) && $_POST['osztalyId'] != '') { $osztalyId = $_POST['osztalyId']; }
	elseif (__OSZTALYFONOK && !isset($_POST['osztalyId'])) { $osztalyId = $_OSZTALYA[0]; $_POST['osztalyId'] = $osztalyId; }

	if (isset($szemeszterId)) {

	    $ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	    define('__ZARO_SZEMESZTER', $ADAT['szemeszterAdat']['szemeszter'] == $ADAT['szemeszterAdat']['tanevAdat']['maxSzemeszter']);

	    $Osztalyok = getOsztalyok($ADAT['szemeszterAdat']['tanev']);
	    if (isset($osztalyId)) {

		define('__OSZTALYFONOKE', (__OSZTALYFONOK === true && in_array($osztalyId, $_OSZTALYA)));

		// intézmény adatok
		$ADAT['intezmeny'] = getIntezmenyByRovidnev(__INTEZMENY);
		// osztály statisztikák
		$ADAT['osztaly'] = getOsztalyAdat($osztalyId, $ADAT['szemeszterAdat']['tanev']);
		// Az adott szemeszterben létezik-e az osztály
		for ($i = 0; ($i < count($Osztalyok) && $Osztalyok[$i]['osztalyId'] != $osztalyId); $i++);
		if ($i < count($Osztalyok)) {
		    $ADAT['diakok'] = getDiakok(array('osztalyId' => $osztalyId, 'tanev' => $ADAT['szemeszterAdat']['tanev']));
		    for ($i = 0; $i < count($ADAT['diakok']); $i++) $ADAT['diakIds'][] = $ADAT['diakok'][$i]['diakId'];
		    $ADAT['targyak'] = getTargyakByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat'], $osztalyId, $sorrendNev);
		    //for ($i = 0; $i < count($ADAT['targyak']); $i++) $ADAT['targyAdat'][ $ADAT['targyak'][$i]['targyNev'] ] = $ADAT['targyak'][$i]['targyId'];
		    $ADAT['tanarok'] = getTanarokByDiakIds($ADAT['diakIds'], $ADAT['szemeszterAdat']);
		    $ADAT['jegyek'] = getDiakZarojegyek($ADAT['diakIds'], $ADAT['szemeszterAdat']['tanev'], $ADAT['szemeszterAdat']['szemeszter']);
		    $ADAT['hianyzas'] = getDiakHianyzasOsszesites($ADAT['diakIds'], $ADAT['szemeszterAdat']);

		    /* A pdfBizonyítvány ezeket használja: szemeszterAdat|intezmeny|diakok|jegyek|hianyzas|osztaly|targyAdat|targyak*/
		    $file = fileNameNormal('bizonyitvany-'.$ADAT['szemeszterAdat']['tanev'].'-'.$ADAT['szemeszterAdat']['szemeszter'].'-'.str_replace('.','',$ADAT['osztaly']['osztalyJel']));
		    if (pdfBizonyitvany($file, $ADAT))
			header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/nyomtatas/bizonyitvanyNyomtatas&file='.$file.'.pdf'));
		} else {
		    unset($osztalyId);
		}

	    }
	}

        $TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') ,'post' => array('sorrendNev', 'osztalyId'));
	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'tanev' => $ADAT['szemeszterAdat']['tanev'], 'post' => array('szemeszterId', 'sorrendNev'));
	getToolParameters();
    
    }
?>
