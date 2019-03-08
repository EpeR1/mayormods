<?php

    require_once('include/modules/naplo/share/hirnok.php');
    global $_TANEV;

    if (isset($_SESSION['lastLogin']) && strtotime(getTanitasinapvissza(2))>strtotime($_SESSION['lastLogin'])) $rDt = $_SESSION['lastLogin'];
    else $rDt = getTanitasiNapVissza(2);
    $tolDt = readVariable($_POST['tolDt'],'date',
	readVariable($_GET['tolDt'], 'date', 
	    $rDt
	)
    );
    if (strtotime($tolDt)>strtotime(date('Y-m-d H:i:s'))) $tolDt = date('Y-m-d',strtotime('-10 day'));

    $osztalyId = readVariable($_POST['osztalyId'], 'id');
    if (__NAPLOADMIN === true) { // csak adminnak engedjük kiválasztani - lásd még include
	$diakId = readVariable($_POST['diakId'], 'id', readVariable($_GET['diakId'], 'id'));
	$tanarId = readVariable($_POST['tanarId'], 'id', readVariable($_GET['tanarId'], 'id'));
	$feliratkozott = getHirnokFeliratkozasok();
	if ($diakId==0 && count($feliratkozott['diak'])>0) $diakId = $feliratkozott['diak'];
	if ($tanarId==0 && count($feliratkozott['tanar'])>0) $tanarId = $feliratkozott['tanar'];
	if ($tanarId==0 && defined('__USERTANARID')) $tanarId = __USERTANARID;
    } else {
        if (__DIAK===true) { // diák nézet
                $diakId = __USERDIAKID;
        } elseif (__TANAR ===true) { // tanár nézet
                $tanarId = __USERTANARID;
        }
    }

    $ADAT['hirnokFolyam'] = hirnokWrapper(array('tolDt'=>$tolDt,'diakId'=>$diakId,'tanarId'=>$tanarId));
    $ADAT['tolDt'] = $tolDt;
    $ADAT['igDt'] = $igDt = date('Y-m-d');

    if (__NAPLOADMIN===true) {
	$TOOL['vissza'] = array('tipus'=>'vissza','paramName'=>'','icon'=>'bullhorn');
    	$TOOL['tanarSelect'] = array('tipus'=>'cella','paramName'=>'tanarId', 'post'=>array('tolDt', 'igDt'));
    	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post'=>array('tolDt', 'igDt'));
    	$TOOL['diakSelect'] = array('tipus'=>'cella','paramName'=>'diakId', 'post'=>array('osztalyId','tolDt', 'igDt', 'osztalyId'));
    	if ($diakId>0)$TOOL['diakLapozo'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array('osztalyId','tolDt', 'igDt', 'osztalyId'));
    }
    $TOOL['datumTolIgSelect'] = array(
            'tipus' => 'sor', 'title' => '',
            'post'=>array('tolDt','tanarId','osztalyId','tankorId','mkId','diakId','telephely'),
            'tolParamName' => 'tolDt', 'igParamName' => 'igDt', 'hanyNaponta' => 1,
            'override' => true,
            'tolDt' => $_TANEV['kezdesDt'],
            'igDt' => $igDt,
    );

    getToolParameters();

?>