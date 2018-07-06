<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG) {

	$_SESSION['alert'][] = 'page:illegal_access';

    } elseif (!_TANKOROK_OK) {
    
	$_SESSION['alert'][] = 'page:hianyzo_tankorok:'._HIANYZO_TANKOROK_SZAMA;

    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/ertekeles.php');
	require_once('include/modules/naplo/share/bejegyzesModifier.php');
	require_once('include/modules/naplo/share/bejegyzes.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/nap.php');
	require_once('include//share/date/names.php');

	global $_TANEV;


        $ADAT['nevsor'] = $nevsor = readVariable($_POST['nevsor'],'emptystringnull','aktualis',array('aktualis','teljes'));
	$osztalyId = readVariable($_POST['osztalyId'],'numeric', readVariable($_GET['osztalyId'],'numeric'));
	if (!isset($osztalyId) && __TANAR && __OSZTALYFONOK) $osztalyId = $_OSZTALYA[0];
	$ADAT['tolDt'] = $tolDt = readVariable($_POST['tolDt'], 'date', $_TANEV['kezdesDt']);
	$ADAT['igDt'] = $igDt = readVariable($_POST['igDt'], 'date', date('Y-m-d'));

	//if (isset($_POST['referenciaDt']) && strtotime($_POST['referenciaDt'])>0) 
	$ADAT['referenciaDt'] = $referenciaDt = readVariable($_POST['referenciaDt'],'datetime',null);


// --------------------------------

    if (isset($osztalyId)) {
	$ADAT['osztalyId'] = $osztalyId;
        define(__OFO, ( is_array($_OSZTALYA) && in_array($osztalyId, $_OSZTALYA)));

        $jogosult = array();
        if (__TANAR) $jogosult[] = 'szaktanár';
        if (__OFO) $jogosult[] = 'osztályfőnök';
        if (__VEZETOSEG) $jogosult[] = 'vezetőség';
        if (__NAPLOADMIN) $jogosult[] = 'admin';

	$ADAT['jogosult fokozatok'] = getBejegyzesTipusokByJogosult($jogosult, array('tipus' => array('fegyelmi'), 'hianyzas' => true));
	$ADAT['összes fokozat'] = getBejegyzesTipusokByJogosult(array('szaktanár','osztályfőnök','vezetőség','admin'), array('tipus' => array('fegyelmi'), 'hianyzas' => true));
	$ADAT['fokozat2bejegyzesTipus'] = $ADAT['bejegyzesTipusok'] = $ADAT['bejegyzesTipusIds'] = array();
	foreach ($ADAT['összes fokozat'] as $key => $fAdat) {
	    $ADAT['bejegyzesTipusok'][ $fAdat['bejegyzesTipusId'] ] = $fAdat;
	}
	foreach ($ADAT['jogosult fokozatok'] as $key => $fAdat) {
	    $bejegyzesTipusIds[] = $fAdat['bejegyzesTipusId'];
	}
	$_TMP = getBejegyzesTipusokByJogosult(array('szaktanár','osztályfőnök','vezetőség','admin'), array('tipus' => array('fegyelmi'), 'hianyzas' => false));
	foreach ($_TMP as $key => $fAdat) {
	    $ADAT['fokozat2bejegyzesTipus'][ $fAdat['fokozat'] ] = $fAdat;
	}

	if (
	    $_TANEV['statusz']=='aktív'
	    && (__NAPLOADMIN || __VEZETOSEG || (__TANAR && __OSZTALYFONOK && in_array($osztalyId, $_OSZTALYA)))
	) {
	    if ($action == 'fegyelmiRogzitese') {

		$diakId = readVariable($_POST['diakId'], 'id');
		$bejegyzesTipusId = readVariable($_POST['bejegyzesTipusId'], 'id', null, $bejegyzesTipusIds);
		$referenciaDt = readVariable($_POST['ujReferenciaDt'], 'date');
		$hianyzasDb = readVariable($_POST['hianyzasDb'], 'numeric unsigned');

		$szoveg = 'Tisztelt Szülő! Értesítem, hogy gyermeke - igazolatlan hiányzásainak száma ('.$hianyzasDb.') alapján - elérte a(z) "'
		    .$ADAT['bejegyzesTipusok'][$bejegyzesTipusId]['bejegyzesTipusNev'].'" fegyelmi fokozatot.';

		if (isset($bejegyzesTipusId) && isset($diakId)) ujBejegyzes($bejegyzesTipusId, $szoveg, $referenciaDt, $diakId, $hianyzasDb);
		else $_SESSION['alert'][] = 'message:insufficient_access:a fegyelmi nem rögzíthető (bejegyzesTipusId='.$bejegyzesTipusId.'; hianyzasDb='.$hianyzasDb.')';

	    }
	}

	// --------------------------------

	    //$osztalyAdat = getOsztalyAdat($osztalyId);

	    if ($tolDt != $_TANEV['kezdesDt'] || $igDt != date('Y-m-d')) {
		$OPT = array('hozott','lezárt','igazolható','összes');
		$View = readVariable($_POST['View'], 'enum', array('összes'), $OPT);
	    } else {
		$OPT = array('hozott','lezárt','igazolható','összes','fegyelmi utáni','fegyelmi fokozatok');
		$View = readVariable($_POST['View'], 'enum', ($skin != 'pda')?array('összes','fegyelmi fokozatok'):array('összes'), $OPT);
	    }

//	    elseif ($skin != 'pda') $View = array('összes','fegyelmi fokozatok');
//	    else $View = array('összes');

	    if ($nevsor=='aktualis') {
		$ADAT['stat'] = getHianyzok($ADAT,array('dt'=>date("Y-m-d")));
	    } else {
		$ADAT['stat'] = getHianyzok($ADAT);
	    }
//	    $ADAT['hianyzasmentesNapokSzama'] = getHianyzasmentesNapokSzama($osztalyId);	    

	    foreach($ADAT['stat']['névsor'] as $_diakId => $_D) {
		$ADAT['hozottHianyzas'][$_diakId] = getDiakHozottHianyzas($_diakId);
	    }

    } // isset(osztalyId)

//	$TOOL['vissza'] = array('tipus'=>'vissza','paramName'=>'','icon'=>'','post'=>array('page'=>'naplo','sub'=>'haladasi','f'=>'haladasi'));
	$TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('tolDt','igDt','View'));
	getToolParameters();
	$TOOL['datumTolIgSelect'] = array('tipus' => 'sor', 'tolParamName' => 'tolDt', 'igParamName' => 'igDt',
    	    'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
    	    'hanyNaponta' => 1, 'post' => array('osztalyId', 'View')
	);
    }

?>
