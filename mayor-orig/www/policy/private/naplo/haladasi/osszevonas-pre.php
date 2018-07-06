<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

        global $_TANEV;
        if ($_TANEV['statusz'] != 'aktív') $_SESSION['alert'][] = 'page:nem_aktív_tanev:'.$_TANEV['tanev'];

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/helyettesitesModifier.php');
        require_once('include/share/date/names.php');
	
	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

	$dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
	$ora = readVariable($_POST['ora'], 'numeric unsigned', null);
	$tanarId = readVariable($_POST['tanarId'], 'id', null);
	$tankorId = readVariable($_POST['tankorId'], 'id', null);
	// $teremId = readVariable($_POST['teremId'], 'id', null);

	if (isset($dt)) {
	    checkNaplo($dt); // a fv. maga ellenőrzi, hogy kell-e, lehet-e órákat betölteni
	    if (isset($tankorId)) {
		// A tankör tanárainak lekérdezése (tanarSelect számára)
		$Tanarok = getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => $dt, 'igDt' => $dt, 'result' => 'nevsor'));
		if (isset($tanarId)) {
		    // A tanár a tankör tanára-e?
		    for ($i = 0; ($i < count($Tanarok) && $Tanarok[$i] != $tanarId); $i++);
		    if ($i > count($Tanarok)) {
			$_SESSION['alert'][] = 'message:wrong_data:pluszOra:Nem a tankör tanára:$tankorId/$tanarId';
			unset($tanarId); unset($_POST['tanarId']);
		    }
		}
		// Ha csak egy tanarId van, akkor azt állítsuk be! 
		if (count($Tanarok) == 1) {
		    $tanarId = $Tanarok[0]['tanarId'];
		    if (isset($osztalyId)) unset($osztalyId);
		}
	    }

	    // Felvehető-e az óra
	    $ok = (isset($ora) && isset($tanarId) && isset($tankorId));

	    if (isset($tanarId)) {
		// tanar Napi órái
		$Orak = getTanarNapiOrak($tanarId, $dt);
	    }
	    if (isset($ora)) {
		if (isset($tankorId)) {
		    $DIAKIDK = getTankorDiakjaiByInterval($tankorId, $tanev = __TANEV, $tolDt = $dt, $igDt = $dt);
		    $ADAT['torlendoTankorok'] = getOrakByDiakIdk($DIAKIDK['idk'], array('dt'=>$dt,'ora'=>$ora));
		    for ($i=0; $i<count($ADAT['torlendoTankorok']); $i++) {
			$ADAT['tankorIds'][] = $ADAT['torlendoTankorok'][$i]['tankorId'];
		    }
		    $ADAT['tankorok'] = getTankorAdatByIds($ADAT['tankorIds']);
		}
		$Tanarok = getFoglaltTanarok($dt,$ora);
	    } // ora
	} // dt

	$ADAT['dt'] = $dt;
	$ADAT['ora'] = $ora;
	$ADAT['tanarId'] = $tanarId;
	$ADAT['tankorId'] = $tankorId;

	// Action
	if ($ok && $action == 'oraFelvetele') {
		for ($i=0; $i<count($ADAT['torlendoTankorok']); $i++) {
		    $oraId = $ADAT['torlendoTankorok'][$i]['oraId'];
		    HianyzasEsJegyHozzarendelesTorles($oraId);
		    oraElmarad($oraId);
		}
		$eredet = 'plusz';
		$tipus = 'összevonás';
		if (oraFelvetele($dt, $ora, $tanarId, $tankorId, $teremId, $tipus, $eredet)) {
		    $_SESSION['alert'][] = 'info:change_success';
		    $Orak = getTanarNapiOrak($tanarId, $dt);
		}
	}

	// toolBar
        $TOOL['datumSelect'] = array(
            'tipus' => 'cella', 'post' => array('ora', 'tanarId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
	    'napTipusok' => array('tanítási nap','speciális tanítási nap'),
        );
	if (isset($dt)) {
    	    $TOOL['oraSelect'] = array('tipus' => 'cella', 'post' => array('osztalyId', 'tanarId', 'tankorId', 'dt', 'ora'));
    	    if (isset($ora) && (!isset($osztalyId) || isset($tankorId))) $TOOL['tanarSelect'] = array('tipus'=>'cella', 'tanarok' => $Tanarok, 'post'=>array('dt', 'ora', 'tankorId', 'teremId' ));
    	    if (isset($osztalyId) or isset($tanarId))
        	$TOOL['tankorSelect'] = array('tipus'=>'sor', 'paramName'=>'tankorId', 'post'=>array('osztalyId','tanarId','dt','ora','teremId'));
    	    // if (isset($ora)) $TOOL['teremSelect'] = array('tipus'=>'cella', 'paramName'=>'teremId', 'termek' => $Termek, 'post'=>array('osztalyId','tanarId','dt','ora','tankorId'));
	}
        getToolParameters();

    } // admin vagy igazgató

?>
