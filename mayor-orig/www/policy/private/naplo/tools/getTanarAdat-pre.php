<?php

    if (_RIGHTS_OK !== true) die();
//    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/orarend.php');

    if (!defined('__MIN_ORA')) define('__MIN_ORA',getMinOra());
    if (!defined('__MAX_ORA')) define('__MAX_ORA',getMaxOra());

    $tanarId = readVariable($_POST['tanarId'], 'id');

    if (!is_numeric($tanarId)) {
	$_JSON = array();
	exit;
    }

    /* PRIVÁT ADATOK */
    if (__NAPLOADMIN === true || __VEZETOSEG === true || __TITKARSAG === true || __TANAR === true) {
	$tmp = getTanarAdatById($tanarId);
	$_JSON = $tmp[0];
	$_JSON['tanarTankor'] = getTankorByTanarId($tanarId);
	$_JSON['layerPolicy'] = 1;
    } else {
	$_JSON['tanarNev'] = getTanarNevById($tanarId);
	$_JSON['layerPolicy'] = 0;
    }

    // Ha szülő nézi, jó lenne az elérhetőséget ide kiírni

    /* PUBLIKUS ADATOK */
    $_JSON['tanarId'] = $tanarId;
    $_JSON['tanev'] = __TANEV;

    $_JSON['oraTerheles'] = getOraTerhelesStatByTanarId(array('tanarId'=>$tanarId));

    // TEMPLATE
    $ORAK = getTanarNapiOrak($tanarId);
    $s = '';
    if (is_array($ORAK) && count($ORAK)>0) {
	for ($ora=__MIN_ORA; $ora<=__MAX_ORA; $ora++) {
	    $OA = $ORAK[$ora];
	    $s .= '<div class="ora">'.$ora.'. '.$OA[$i]['tankorNev'];
	    for ($i=0; $i<count($OA); $i++) {
		$s .= $OA[$i]['tankorNev'];
	    }
	    $s .='</div>';
	}
    }
    $_JSON['maiOrak']['html'] = $s;
    $_JSON['debug'] = __MIN_ORA;
?>