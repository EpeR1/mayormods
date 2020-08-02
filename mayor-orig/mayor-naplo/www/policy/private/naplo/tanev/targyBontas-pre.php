<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorModifier.php');
	require_once('include/modules/naplo/share/bontas.php'); // bontás kész-e

	// Adatok (bontások, tankör-hozzárendelések) átvétele az előző évből - csak üres bontás tábla esetén!
	$ADAT['targyBontasStatus'] = checkTargyBontas();

	if ($ADAT['targyBontasStatus']===false && $action=='targyBontasInit') {
	    $ADAT['targyBontasStatus'] = initFromLastYear();
	}
dump($ADAT);
	// telephely - csak szűréshez kell
	$ADAT['telephelyId'] = $telephelyId = readVariable($_POST['telephelyId'], 'id');
	// kötelező szűrési opció
	$ADAT['evfolyamJelek'] = getEvfolyamJelek();
	$osszesEvfolyamJel = array();
	if (is_array($ADAT['evfolyamJelek'])) foreach ($ADAT['evfolyamJelek'] as $eAdat) $osszesEvfolyamJel[] = $eAdat['evfolyamJel'];
	$ADAT['evfolyamJel'] = $evfolyamJel = readVariable($_POST['evfolyamJel'], 'enum', null, $osszesEvfolyamJel);
	// a tárgyadatok az ajax hívásokhoz is kellenek
	$ADAT['targyAdat'] = getTargyAdatByIds();
	// osztályok - szűréshez, és tovább...
	$ADAT['filter']['osztalyAdat'] = getOsztalyok(__TANEV, array('result' => 'indexed', 'minden'=>false, 'telephelyId' => $telephelyId));
	foreach ($ADAT['filter']['osztalyAdat'] as $idx => $oAdat) {
	    $ADAT['filter']['osztalyAdat'][$idx]['bontasOk'] = osztalyBontasKeszE($oAdat['osztalyId']);
	    $ADAT['filter']['osztalyIds'][] = $oAdat['osztalyId'];
	}
	$ADAT['filter']['kepzesAdat'] = getKepzesByOsztalyId($ADAT['filter']['osztalyIds'], array('result'=>'indexed','arraymap'=>array('kepzesId')));

	if (isset($ADAT['evfolyamJel'])) { // csak egy évfolyamhoz tartozó osztályok jelölhetők ki egyszerre

	    // szűrés osztályra - osztalyId az egyik kulcs mező
	    $ADAT['osztalyAdat'] = array();
	    $osszesOsztalyId = array();
	    if (is_array($ADAT['filter']['osztalyAdat'])) foreach ($ADAT['filter']['osztalyAdat'] as $oAdat) {
		if ($oAdat['evfolyamJel'] == $evfolyamJel) {
		    $ADAT['osztalyAdat'][$oAdat['osztalyId']] = $oAdat;
		    $osszesOsztalyId[] = $oAdat['osztalyId'];
		}
	    }
	    if (count($osszesOsztalyId) == 0) $ADAT['osztalyIds'] = array(); // üres tömb esetén nem szűr a readVariable...
	    else $ADAT['osztalyIds'] = readVariable($_POST['osztalyIds'], 'id', null, $osszesOsztalyId);
	    $_POST['osztalyIds'] = $osztalyIds = $ADAT['osztalyIds'];

	    // Az osztályokhoz rendelt tankörök lekérdezése - a bontásokban ezek lehetnek - kell a névhez...
	    $ADAT['tankorAdat'] = array();
	    foreach ($osztalyIds as $osztalyId) {
		$TA = getTankorByOsztalyId($osztalyId);
		foreach ($TA as $tAdat) if (!is_array($ADAT['tankorAdat'][ $tAdat['tankorId'] ])) {
		    $ADAT['tankorAdat'][ $tAdat['tankorId'] ] = $tAdat;
		    $ADAT['tankorAdat'][ $tAdat['tankorId'] ]['tankorNevTargyNelkul'] = str_replace($ADAT['targyAdat'][ $tAdat['targyId'] ]['targyNev'].' ','',$tAdat['tankorNev']);
		}
	    }

	    // képzések lekérdezése - a képzés óratervhez
	    if (is_array($osztalyIds) && count($osztalyIds) > 0) {
		// itt volt a kepzesTargyBontas - de az init utánra tettem...
		$ADAT['kepzesek'] = getKepzesByOsztalyId($osztalyIds, array('result' => 'indexed', 'arraymap' => array('kepzesId','osztalyId')));
		$ADAT['kepzesAdat'] = array();
		if (is_array($ADAT['kepzesek'])) foreach ($ADAT['kepzesek'] as $kepzesId => $kAdat) {
		    $ADAT['kepzesAdat'][$kepzesId] = current(current($kAdat));
		    unset($ADAT['kepzesAdat'][$kepzesId]['osztalyId']);
		    $ADAT['kepzesAdat'][$kepzesId]['osztalyIds'] = array_keys($kAdat);
		}
		$osszesKepzesId = array_keys($ADAT['kepzesek']);
		unset($ADAT['kepzesek']);
	    }
	    if (is_array($_POST['kepzesIds'])) $ADAT['kepzesIds'] = readVariable($_POST['kepzesIds'], 'id', null, $osszesKepzesId);
	    else $ADAT['kepzesIds'] = $osszesKepzesId;

	    // képzés adott évfolyamának óraterve...
	    if (is_array($ADAT['kepzesIds']) && count($ADAT['kepzesIds'])) {
		// Itt kellene inicializálni - minden tárgyat tartalmazó kepzesOratervId:osztalyId párhoz legyen legalább egy bontás
		kepzesTargyBontasInit($ADAT['osztalyIds'], $ADAT['kepzesIds']);
		$ADAT['targyBontasStatus'] = checkTargyBontas();
		$ADAT['oraterv'] = kepzesOratervSorrend($evfolyamJel, $ADAT['osztalyIds'], $ADAT['kepzesIds']);
		// itt volt a targyAdat... felkerült az elejére
		$ADAT['osztalyTargyBontas'] = getKepzesTargyBontasByOsztalyIds($osztalyIds);
//dump($ADAT['osztalyTargyBontas']);
	    }

	    $ADAT['oratervenKivuliTankorok'] = getOratervenKivuliTankorIds();

	} // van evfolyamJel



	// Ajax action
	if ($action == 'addBontas') {
	    $keys = explode(',', $_POST['keys']);
	    foreach ($keys as $key) {
		list($osztalyId, $kepzesOratervId) = explode('-',$key);
		$bontas[] = addBontas($osztalyId, $kepzesOratervId);

	    }
	    $_JSON = array(
		'keys' => $_POST['keys'],
		'bontas' => $bontas,
		'targyNev' => $ADAT['targyAdat'][ $bontas[0]['targyId'] ]['targyNev'],
		'result' => 'success'
	    );
	} else if ($action == 'addBontasTargy') {
	    $targyIds = array_keys($ADAT['targyAdat']);
	    $targyId = readVariable($_POST['targyId'], 'id', null, $targyIds);// tárgyIds-re szűkítés!!
	    if ($targyId != '') {
		$keys = explode(',', $_POST['keys']);
		foreach ($keys as $key) {
		    list($osztalyId, $kepzesOratervId) = explode('-',$key);
		    $bontas[] = addBontas($osztalyId, $kepzesOratervId, $targyId);
		}
		$_JSON = array(
		    'keys' => $_POST['keys'],
		    'bontas' => $bontas,
		    'targyNev' => $ADAT['targyAdat'][$targyId]['targyNev'],
		    'result' => 'success'
		);
	    } else {
		$_JSON = array(
		    'keys' => $_POST['keys'],
		    'result' => 'fail'
		);
	    }
	} else if ($action == 'bontasTankor') {
	    $keys = explode(',', $_POST['keys']);
	    $bontasIds = readVariable($keys, 'id');
	    $tankorId = readVariable($_POST['tankorId'], 'id');
	    $hetiOraszam = readVariable($_POST['hetiOraszam'], 'float unsigned');

	    $targyId = getTankorTargyId($tankorId);
	    $targyNev = $ADAT['targyAdat'][$targyId]['targyNev'];
	    $ret = bontasTankor($bontasIds, $tankorId, $hetiOraszam);
	    $tankorNev = getTankorNevById($tankorId); // A tankörnév változhat az osztály hozzárendelés miatt...
	    $tankorNevTargyNelkul = str_replace($targyNev.' ','',$tankorNev);
		$_JSON = array(
		    'keys' => $_POST['keys'],
		    'bontasIds' => $bontasIds,
		    'hetiOraszam' => $hetiOraszam,
		    'tankorId' => $tankorId,
		    'tankorNev' => $tankorNevTargyNelkul,
		    'ret' => $ret,
		    'TANEV' => __TANEV,
		    'result' => ($ret?'success':'fail')
		);

	} else if ($action == 'delBontas') {
	    $keys = explode(',', $_POST['keys']);
	    $bontasIds = readVariable($keys, 'id');
	    $return = delBontas($bontasIds);
	    $_JSON = array(
		'keys' => $_POST['keys'],
		'bontasIds' => (is_array($return)?$return:array()),
		'result' => (is_array($return)?'success':'fail')
	    );
	} else { // Ez csak tesztelés, hibakeresés...
	    $_JSON = array(
		'post' => $_POST,
	    );
	}
	// Ajax action vége

        $TOOL['telephelySelect'] = array('tipus' => 'cella','paramName' => 'telephelyId', 'post' => array('osztalyIds','kepzesIds','evfolyamJel'));
	$TOOL['evfolyamJelSelect'] = array('tipus' => 'cella','paramName' => 'evfolyamJel','paramDesc'=>'evfolyamJel','adatok' => $ADAT['evfolyamJelek'],'post' => array('osztalyIds','kepzesIds','telephelyId'));
        getToolParameters();

    }

?>