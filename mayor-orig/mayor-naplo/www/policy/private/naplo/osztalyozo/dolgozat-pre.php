<?php
    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__DIAK && !__TITKARSAG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/dolgozat.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');

	if (__DIAK) {
	    $diakId = __USERDIAKID; // Csak a saját dolgozatait nézheti meg
	} else {
	    $diakId = readVariable($_POST['diakId'],'numeric unsigned',
		readVariable($_GET['diakId'],'numeric unsigned',null)
	    );
	    $osztalyId = readVariable($_POST['osztalyId'],'numeric unsigned',
		readVariable($_GET['osztalyId'],'numeric unsigned',null)
	    );
	    $tanarId = readVariable($_POST['tanarId'], 'numeric unsigned',
		readVariable($_GET['tanarId'], 'numeric unsigned', __TANAR ? __USERTANARID : null)
	    );
	}

	// képkezeléshez
	if (__SHOW_FACES_TF) $ADAT['kepMutat'] = true; // ez később finomítható.
	else $ADAT['kepMutat'] = false;

        // tankörök lekérdzése
        if (isset($diakId)) $Tankorok = getTankorByDiakId($diakId, __TANEV);
        elseif (isset($osztalyId)) $Tankorok = getTankorByOsztalyId($osztalyId, __TANEV);
        elseif (isset($tanarId)) $Tankorok = getTankorByTanarId($tanarId, __TANEV);
	$tankorIds = array();
	for ($i = 0; $i < count($Tankorok); $i++) $tankorIds[] = $Tankorok[$i]['tankorId'];

	// Kiválasztott tankör azonosítója
	$tankorId = readVariable($_POST['tankorId'], 'numeric unsigned',
	    readVariable($_GET['tankorId'], 'numeric unsigned', null, $tankorIds), $tankorIds
	);

	// A tankörökhöz tartozó dolgozatok lekérdezése
	if (is_array($Tankorok)) {
	    $Dolgozat = getTankorDolgozatok($Tankorok);
	    $dolgozatIds = array_keys($Dolgozat);
	    // A kiválasztott dolgozat azonosítója
	    $dolgozatId = readVariable($_POST['dolgozatId'], 'id',
		readVariable($_GET['dolgozatId'], 'id', null, $dolgozatIds), $dolgozatIds
	    );
	}
else {
	    $dolgozatId = readVariable($_POST['dolgozatId'], 'id',
		readVariable($_GET['dolgozatId'], 'id', null)
	    );
}
	// Többi paraméter
	if (isset($dolgozatId)) {
	    $Dolgozat = getDolgozat($dolgozatId);
	    $valaszthatoTankorok = getTankorByTargyId($Dolgozat['targyId'],__TANEV,array('idonly' => false, 'lista' => true));
	} elseif (isset($tankorId)) $Dolgozat = getTankorDolgozatok($tankorId);
//	elseif (isset($diakId) || isset($osztalyId) || isset($tanarId)) $Dolgozat = getTankorDolgozatok($Tankorok);

	// ------  action  ----------------------------- //

	if ($action == 'dolgozatBejelentes') {
	    if (defined('__USERTANARID') && __USERTANARID == $tanarId && isset($tankorId)) {
		$dolgozatId = ujDolgozat(__USERTANARID, $tankorId);
		$Dolgozat = getDolgozat($dolgozatId);
	        $valaszthatoTankorok = getTankorByTargyId($Dolgozat['targyId'],__TANEV,array('idonly' => false, 'lista' => true));
	    }
	} elseif ($action == 'dolgozatTorles') {

	}

	define(__MODOSITHAT,
	    isset($dolgozatId) 
	    && (
		(__NAPLOADMIN && $_TANEV['statusz'] == 'aktív')
		|| (
		    __FOLYO_TANEV && __TANAR
		    && is_array($Dolgozat['tanarIds'])
		    && in_array(__USERTANARID, $Dolgozat['tanarIds'])
		)
	    )
	);

	if (__MODOSITHAT) if ($action == 'dolgozatModositas') {

	    if (isset($_POST['dolgozatTorles'])) {
		if (!$Dolgozat['ertekelt']) {
		    $action = 'dolgozatTorles';
		    if (dolgozatTorles($dolgozatId)) {
			logAction(
			    array(
				'szoveg'=>'Dolgozat törlés: '.$dolgozatId, 
				'table'=>'dolgozat'
			    )
			);
			unset($Dolgozat); unset($dolgozatId);
			$_SESSION['alert'][] = 'info:success:dolgozat törlés';
		    }
		}
	    } else {
		$dolgozatNev = $_POST['dolgozatNev'];
		$tervezettDt = readVariable($_POST['tervezett-dt'],'datetime',null);
		if (dolgozatModositas($dolgozatId, $dolgozatNev, $tervezettDt)) {
		    logAction(
			array(
			    'szoveg'=>'Dolgozat módosítás (név, tervezett dátum): '.$dolgozatId, 
			    'table'=>'dolgozat'
			)
		    );
		    $Dolgozat['dolgozatNev'] = $dolgozatNev;
		    $Dolgozat['tervezettDt'] = $tervezettDt;
		    $_SESSION['alert'][] = 'info:done:';
		}
	    }

	} elseif ($action == 'dolgozatTankorHozzarendeles') {

	    if (is_array($_POST['tankorIds']) && count($_POST['tankorIds']) > 0) {

		$tankorIds = $_POST['tankorIds'];
		$torlendoTankorIds = array_diff($Dolgozat['tankorIds'], $tankorIds);
		$ujTankorIds = array_values(array_diff($tankorIds, $Dolgozat['tankorIds']));
		dolgozatTankorHozzarendeles($dolgozatId, $torlendoTankorIds, $ujTankorIds);
		$Dolgozat = getDolgozat($dolgozatId);

	    } else {
		$_SESSION['alert'][] = 'message:empty_field:tankorId:dolgozatTankorHozzarendeles';
	    }

	} elseif ($action == 'dolgozatJegyekTorlese') {

		foreach ($_POST as $name => $value) {
		    if  (substr($name, 0, 8) == 'tankorId') $tankorId = substr($name, 8);
		}
		dolgozatJegyekTorlese($dolgozatId, $tankorId);
		$Dolgozat = getDolgozat($dolgozatId);

	}

	define(__TOROLHET,
	    __MODOSITHAT
	    && !$Dolgozat['ertekelt']
	);


	// ------  action vége ------------------------- //

	// TOOL
        if (!__DIAK) {
	    $TOOL['tanarSelect'] = array('tipus' => 'cella', 'post' => array());
    	    $TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array());
	}
        if (isset($osztalyId))
            $TOOL['diakSelect'] = array('tipus' => 'cella', 'paramName' => 'diakId', 'post' => array('osztalyId'));
        if (isset($osztalyId) or isset($tanarId) or isset($diakId))
            $TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $Tankorok, 'paramName' => 'tankorId', 'post' => array('osztalyId', 'tanarId', 'diakId'));
	getToolParameters();

    }

?>
