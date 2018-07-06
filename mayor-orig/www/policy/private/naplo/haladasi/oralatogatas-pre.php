<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';


    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/share/date/names.php');
    require_once('include/modules/naplo/share/ora.php');

//    $tankorId = readVariable($_POST['tankorId'], 'id');
    $ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
    $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id');
    if (!isset($osztalyId)) {
	$ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'numeric unsigned', $tanarId = readVariable($_GET['tanarId'],'numeric unsigned'));
	if (!isset($tanarId) && __USERTANARID!==false && __TANAR) $ADAT['tanarId'] = $tanarId = __USERTANARID;
    }
    $ADAT['tanarok'] = getTanarok(array('tanev' => __TANEV));

    // Adott napi órák lekérdezése
    if (isset($osztalyId)) {
	$ADAT['orak'] = getOsztalyNapiOrak($osztalyId, $dt);
    } elseif ($tanarId) {
	$ADAT['orak'] = getTanarNapiOrak($tanarId, $dt);
    }

    // Az órákhoz tartozó látogatások és látogatók lekérdezése
    $ADAT['oralatogatas'] = $ADAT['oraIds'] = array();
    if (is_array($ADAT['orak']) && count($ADAT['orak']) > 0) {
	foreach ($ADAT['orak'] as $ora => $oAdat) {
	    foreach ($oAdat as $key => $oraAdat) {
		$ADAT['oraIds'][] = $oraAdat['oraId'];
	    }
	}
    }

    // Jogosultság ellenőrzés, tanév aktív-e...
    if (
	$_TANEV['szemeszter'][1]['statusz'] == 'aktív'		// Csak aktív tanévben lehet módosítani
	&& (
	    __NAPLOADMIN					// adminnak vagy
	    || (__VEZETOSEG && __FOLYO_TANEV)			// A tanév közben a vezetőségi tagoknak
	)
    ) {
        if ($action == 'oralatogatasBeiras') {
	    $_D['oraId'] = readVariable($_POST['oraId'], 'id');
	    $_D['megjegyzes'] = readVariable($_POST['megjegyzes'], 'string');
	    $_D['tanarIds'] = readVariable($_POST['tanarIds'], 'id');
	    if (isset($_D['oraId'])) {
		oralatogatasBeiras($_D);
	    }
	    foreach($_POST as $_key => $_val) {
		if (substr($_key,0,6)=='delete') {
			list($_rest,$_oraId) = explode('_',$_key);
			oralatogatasTorles($_oraId);
		}
	    }
        }
    }


    $ADAT['oralatogatas'] = getOralatogatasByOraIds($ADAT['oraIds']);


    /* ------------------------------------------------- */

    // tankörök lekérdzése
    if (isset($osztalyId)) {
	$Tankorok = getTankorByOsztalyId($osztalyId, __TANEV);
    } elseif (isset($tanarId)) {
	$Tankorok = getTankorByTanarId(
		$tanarId,
		__TANEV,
		array('csakId' => false)
	);
    }

//    $TankorokMutat = $Tankorok;
//
//    if (isset($tankorId)) {
//	$Tankorok = getTankorById($tankorId, __TANEV); // felül kell írnunk
//    }

    /* ------------------------------------------------- */

        // toolBar
    $TOOL['datumSelect'] = array(
	    'tipus'=>'sor', 'post'=>array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
	    'paramName' => 'dt', 'hanyNaponta' => 1,
	    'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
	    'igDt' => date('Y-m-d', strtotime($_TANEV['zarasDt'])),
	    'override' => true
    );
    $TOOL['tanarSelect'] = array('tipus' => 'cella', 'tanarok' => $ADAT['tanarok'], 'post' => array('igDt'));
    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('igDt'));
//    if (isset($osztalyId) or isset($tanarId) or isset($diakId))
//	$TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $TankorokMutat, 'paramName' => 'tankorId', 'post' => array('osztalyId','tanarId','diakId','igDt'));
    getToolParameters();

?>
