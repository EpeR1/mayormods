<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';


    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tanmenet.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');


    if (__TANAR) $ADAT['vezetettMkIds'] = getVezetettMunkakozossegByTanarId(__USERTANARID);
    if (!is_array($ADAT['vezetettMkIds'])) $ADAT['vezetettMkIds'] = array();

    $mkId = readVariable($_POST['mkId'], 'id');
    if ($mkId == '' && count($ADAT['vezetettMkIds'])>0) $mkId = $ADAT['vezetettMkIds'][0];
    $ADAT['mkId'] = $mkId;

    if ($mkId != 0) {

	
	$ADAT['tanarok'] = getTanarok(array('mkId'=>$mkId, 'tanev'=>__TANEV, 'result'=>'assoc'));
	$ADAT['tanarIds'] = array_keys($ADAT['tanarok']);
	$ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'id', null, $ADAT['tanarIds']);
	$Filter = array();
	if ($tanarId != '') {
	    $tTankorIds = getTankorByTanarId($tanarId, __TANEV, array('csakId'=>true));
	    if (is_array($tTankorIds) && count($tTankorIds) > 0) $Filter[] = 'tankor.tankorId IN ('.implode(',',$tTankorIds).')';
	}
	$ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'], 'id', null);
	if ($osztalyId != '') {
	    $oTankorIds = getTankorByOsztalyId($osztalyId, __TANEV, array('csakId'=>true));
	    if (is_array($oTankorIds) && count($oTankorIds) > 0) $Filter[] = 'tankor.tankorId IN ('.implode(',',$oTankorIds).')';

	}
	define('__JOVAHAGYHAT', __VEZETOSEG || __NAPLOADMIN || in_array($mkId, $ADAT['vezetettMkIds']));
	$ADAT['tankorok'] = getTankorByMkId($mkId, __TANEV, array('csakId'=>false, 'filter'=>$Filter));
	$ADAT['tankorIds'] = array();
	foreach ($ADAT['tankorok'] as $tAdat) {
	    $ADAT['tankorIds'][] = $tAdat['tankorId'];
	}
	if (count($ADAT['tankorIds']) > 0) {
	    $ADAT['tankorTanmenet'] = getTanmenetByTankorIds($ADAT['tankorIds']);
	    $ADAT['tanmenetAdat'] = $tanmenetIds = array();
	    foreach ($ADAT['tankorTanmenet'] as $tankorId => $tanmenetId) {
		if (!in_array($tanmenetId, $tanmenetIds)) $tanmenetIds[] = $tanmenetId;	
	    }
	    foreach ($tanmenetIds as $tanmenetId) $ADAT['tanmenetAdat'][$tanmenetId] = getTanmenetAdat($tanmenetId);
	} // vannak tankorok


	if ($action == 'tanmenetModositas' && __JOVAHAGYHAT) {

	    $tanmenetId = readVariable($_POST['tanmenetId'], 'id');
	    $statusz = readVariable($_POST['statusz'], 'enum', array('új','kész','jóváhagyott','publikus'));
	    if ($tanmenetId != '' && $statusz != '') {
		$D = array(
		    'tanmenetNev' => $ADAT['tanmenetAdat'][$tanmenetId]['tanmenetNev'],
		    'oraszam' => $ADAT['tanmenetAdat'][$tanmenetId]['oraszam'],
		    'evfolyamJel' => $ADAT['tanmenetAdat'][$tanmenetId]['evfolyamJel'],
		    'tanmenetId' => $tanmenetId,
		    'ujStatusz' => $statusz
		);
		if (tanmenetAdatModositas($D)) $ADAT['tanmenetAdat'][$tanmenetId]['statusz'] = $statusz;
	    }
	}

    } // van mkId

    $TOOL['munkakozossegSelect'] = array('tipus'=>'cella', 'paramName'=>'mkId', 'post'=>array('tanarId','osztalyId'));
    if ($mkId != '') {
	$TOOL['tanarSelect'] = array('tipus'=>'cella', 'paramName'=>'tanarId', 'mkId'=>$mkId, 'post'=>array('mkId','osztalyId'));
	$TOOL['osztalySelect'] = array('tipus'=>'cella', 'paramName'=>'osztalyId', 'mkId'=>$mkId, 'post'=>array('mkId','tanarId'));
    }
/*
    $TOOL['targySelect'] = array('tipus'=>'cella', 'paramName'=>'targyId', 'post'=>array());
    if (isset($tanarId) || isset($targyId)) {
        $TOOL['tanmenetSelect'] = array('tipus'=>'cella', 'paramName'=>'tanmenetId', 'post'=>array('tanarId','targyId'));
        $TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=tanmenet&f=tanmenetModositas'),
        'titleConst' => array('_MODOSITAS'), 'post' => array('tanarId','targyId'),
        'paramName'=>'tanmenetId');
    }
*/
    getToolParameters();

?>