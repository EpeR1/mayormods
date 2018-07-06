<?php

    if (_RIGHTS_OK !== true) die();
    // Az oldalt minden jogosultsági szinten látjuk

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/hianyzas.php');
	require_once('include/modules/naplo/share/nap.php');

	$tanarId = readVariable($_POST['tanarId'],'numeric unsigned');
	$osztalyId = readVariable($_POST['osztalyId'],'numeric unsigned');
	if (__DIAK) $diakId = __USERDIAKID;
	else $diakId = readVariable($_POST['diakId'], 'numeric unsigned', readVariable($_GET['diakId'], 'numeric unsigned'));

	$tankorId = readVariable($_POST['tankorId'],'numeric unsigned');

	if (!isset($tanarId) && !isset($diakId) && !isset($osztalyId) && __TANAR) $tanarId = __USERTANARID;
        // tankörök lekérdzése
        if (isset($diakId)) $Tankorok = getTankorByDiakId($diakId, __TANEV, array('tolDt' => $_TANEV['kezdesDt']));
        elseif (isset($osztalyId)) $Tankorok = getTankorByOsztalyId($osztalyId, __TANEV);
        elseif (isset($tanarId)) $Tankorok = getTankorByTanarId($tanarId, __TANEV);

	$tankorIds = array();
	for ($i = 0; $i < count($Tankorok); $i++) $tankorIds[] = $Tankorok[$i]['tankorId'];

	if (count($tankorIds) > 0) $tankorStat = getTankorStat($tankorIds);
	if (isset($diakId)) {
	    $tankorStat['hianyzasStat'] = getDiakHianyzasStat($diakId, array('tankorIds'=>$tankorIds, 'tanev'=> __TANEV));
	    if (!isset($osztalyId)) {
		$OI = getDiakOsztalya($diakId);
                $osztalyId = $OI[0]['osztalyId'];
	    }
	}
        if (__NAPLOADMIN or __VEZETOSEG or __TANAR or __TITKARSAG) {
            $TOOL['tanarSelect'] = array('tipus'=>'cella', 'post'=>array('igDt'));
            $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post'=>array('igDt'));
            if (isset($osztalyId)) {
                $TOOL['diakSelect'] = array('tipus'=>'cella','paramName'=>'diakId', 'post'=>array('osztalyId','igDt'));
                if (isset($diakId)) $TOOL['diakLapozo'] = array('tipus'=>'sor',  'paramName'=>'diakId', 'post'=>array('osztalyId','igDt'));
            }
        }
	if (isset($diakId)) {
            $TOOL['oldalFlipper'] = array('tipus' => 'cella',
                'url' => array('index.php?page=naplo&sub=hianyzas&f=diak&diakId='.$diakId,'index.php?page=naplo&sub=hianyzas&f=diakLista&diakId='.$diakId),
                'titleConst' => array('_DIAKHIANYZASNAPLO','_DIAKHIANYZASLISTA'),
                'post' => array('tanev','tolDt','igDt','ho','osztaly'),
                'paramName'=>'diakId');
	}
        getToolParameters();

?>
