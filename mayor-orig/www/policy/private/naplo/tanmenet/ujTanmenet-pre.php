<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tanmenet.php');

    $ADAT['evfolyamJelek'] = getEvfolyamJelek();
    $ADAT['tanarId'] = __USERTANARID;
    $ADAT['munkakozosseg'] = getMunkakozossegByTanarId($ADAT['tanarId'], array('result' => 'indexed'));
    $ADAT['mkIds'] = $ADAT['targyIds'] = $ADAT['targy'] = array();
    foreach ($ADAT['munkakozosseg'] as $key => $mkAdat) {
	$tmp = getTargyakByMkId($mkAdat['munkakozossegId'], array('result' => 'indexed'));
	$ADAT['mkIds'][] = $mkAdat['munkakozossegId'];
	$ADAT['targy'] = array_merge($ADAT['targy'], $tmp);
    }
    for ($i = 0; $i < count($ADAT['targy']); $i++) $ADAT['targyIds'][] = $ADAT['targy'][$i]['targyId'];

    // tanév - alapértelmezetten a __TANEV
    $ADAT['tanev'] = readVariable($_POST['tanev'], 'numeric unsigned', is_numeric(__TANEV)?__TANEV:null);

    // Egy tankörhöz rendelendő új tanmenethez
    $ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'],'id', readVariable($_GET['tankorId'], 'id'));

    // tankör --> tárgy --> munkaközösség
    if (isset($tankorId)) {
	$TA = getTankorAdat($tankorId);
	$ADAT['tankorAdat'] = $TA[$tankorId][0]; 
	$ADAT['tankorAdat']['osztalyIds'] = getTankorOsztalyaiByTanev($tankorId, $ADAT['tanev']);
	$evf = array();
	foreach ($ADAT['tankorAdat']['osztalyIds'] as $osztalyId) $evf[] = getEvfolyamJel($osztalyId, $ADAT['tanev']);
	$ADAT['tankorAdat']['evfolyamJel'] = $evf[0];
    }
 
    // Munkaközösség és tankör
    $mkId = readVariable($_POST['mkId'], 'id', null, $ADAT['mkIds']);
    $ADAT['targyId'] = $targyId = readVariable($_POST['targyId'], 'id', $ADAT['tankorAdat']['targyId'], $ADAT['targyIds']);
    if (isset($tankorId)  && $targyId != $ADAT['tankorAdat']['targyId']) unset($tankorId);

    $ADAT['evfolyamJel'] = $evfolyamJel = readVariable($_POST['evfolyamJel'], 'enum', $ADAT['tankorAdat']['evfolyamJel']);
    if (isset($targyId) && isset($evfolyamJel)) {
	$ADAT['targyAdat'] = getTargyById($targyId);
	// Itt lekérdezhetnénk az eddigi ilyen tanmenetek listáját - megjelenítés céljából
	$ADAT['tanmenetek'] = getTanmenetByTargyId($targyId);
//echo '<pre>'; var_dump($ADAT['tanmenetek']); echo '</pre>';
    }

    if ($action == 'ujTanmenet') {
	$ADAT['tanmenetNev'] = readVariable($_POST['tanmenetNev'], 'string', $evfolyamJel.'. '.$ADAT['targyAdat']['targyNev'].' ('.$ADAT['targyAdat']['targyJelleg'].')');
	$ADAT['oraszam'] = readVariable($_POST['oraszam'], 'numeric unsigned');
	$ADAT['tanmenetId'] = $tanmenetId = ujTanmenet($ADAT);
	if (isset($tankorId)) {
	    // itt kellene hozzárendelni a tankorhoz?
	    tankorTanmenetHozzarendeles($ADAT);
	}
	if ($tanmenetId) header('Location: '.location('index.php?page=naplo&sub=tanmenet&f=tanmenetModositas&tanmenetId='.$tanmenetId));
    }

    // $TOOL['tanevSelect'] = array('tipus' => 'cella', 'paramName' => 'tanev', 'post' => array('tankorId','targyId','evfolyamJel'));
    $TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'munkakozossegek' => $ADAT['munkakozosseg'], 'post'=>array('tankorId','evfolyamJel'));
    if (isset($mkId)) $TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'post'=>array('mkId', 'tankorId','evfolyamJel'));
    else $TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'targyak' => $ADAT['targy'], 'post'=>array('mkId', 'tankorId','evfolyamJel'));
    $TOOL['evfolyamJelSelect'] = array('tipus'=>'cella', 'paramName' => 'evfolyamJel', 'paramDesc'=>'evfolyamJel','adatok' => $ADAT['evfolyamJelek'],'post'=>array('targyId', 'tankorId'));
    getToolParameters();


?>
