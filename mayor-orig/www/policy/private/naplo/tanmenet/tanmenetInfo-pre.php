<?php

    if (_RIGHTS_OK !== true) die();
    if (!__DIAK && !__TITKARSAG && !__TANAR && !__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tanmenet.php');
    require_once('include/share/date/names.php');

    $ADAT['tanmenetId'] = $tanmenetId = readVariable($_POST['tanmenetId'], 'id', readVariable($_GET['tanmenetId'], 'id'));
    if (isset($tanmenetId)) {

	$ADAT['tanmenetAdat'] = getTanmenetAdat($tanmenetId);
	$ADAT['tanarId'] = $tanarId = $ADAT['tanmenetAdat']['tanarId'];
	$ADAT['targyId'] = $targyId = $ADAT['tanmenetAdat']['targyId'];

    } else {
	$ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'id', (!isset($_POST['targyId'])&&__TANAR)?__USERTANARID:null);
	$ADAT['targyId'] = $targyId = readVariable($_POST['targyId'], 'id');
    }

    $TOOL['tanarSelect'] = array('tipus'=>'cella', 'paramName'=>'tanarId', 'post'=>array('targyId'));
    $TOOL['targySelect'] = array('tipus'=>'cella', 'paramName'=>'targyId', 'post'=>array('tanarId'));
    if (isset($tanarId) || isset($targyId)) {
	$TOOL['tanmenetSelect'] = array('tipus'=>'sor', 'paramName'=>'tanmenetId', 'post'=>array('tanarId','targyId'));
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=tanmenet&f=tanmenetModositas'),
        'titleConst' => array('_MODOSITAS'), 'post' => array('tanarId','targyId'),
        'paramName'=>'tanmenetId');
    }
    getToolParameters();


?>
