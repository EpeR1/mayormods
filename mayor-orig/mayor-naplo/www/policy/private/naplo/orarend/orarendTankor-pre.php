<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';
    
    $tanev = __TANEV;

    require_once('include/share/date/names.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tankorBlokk.php');
    require_once('include/modules/naplo/share/osztaly.php');

    require_once('include/modules/naplo/share/orarend.php');

    if ($action == 'orarendiOraTankorAssoc') {
		orarendiOraTankorAssoc();
    }

    $targyId = readVariable($_POST['targyId'],'id');
    $tankorId = readVariable($_POST['tankorId'],'id');
    $osztalyId = readVariable($_POST['osztalyId'],'id');
    $tanarId = readVariable($_POST['tanarId'],'id');
    $tolDt = readVariable($_POST['tolDt'],'date');
	$igDt = date('Y-m-d', mktime(0,0,0,date('m',strtotime($tolDt)), date('d',strtotime($tolDt))+6, date('Y',strtotime($tolDt))));	       

    if($tanarId!='') { 
	$ORAREND = getOrarendByTanarId($tanarId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'orarendiOraTankor'=>true));
        $ORAREND['napiMinOra'] = getMinOra();
        $ORAREND['napiMaxOra'] = $ORAREND['maxOra'] = getMaxOra();
        $ORAREND['hetiMaxNap'] = getMaxNap();
	$TANKOROK = getTankorByTanarId($tanarId,__TANEV,array('csakId'=>false,'result'=>'multiassoc','tolDt'=>$_TANEV['kezdesDt'],'igDt'=>$_TANEV['zarasDt']));
	foreach($TANKOROK as $_tankorId => $_TA) {
	    $tankorBlokk = getTankorBlokkByTankorId($_tankorId, __TANEV, array('blokkNevekkel'=> true));
	    if (is_array($tankorBlokk ))
		$TANKOROK[$_tankorId][0]['blokkAdat'] = $tankorBlokk;
	}
	dump($TANKOROK);
    } else { $ORAREND = array(); }

        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('tanarId','osztalyId','tankorId'),
            'paramName' => 'tolDt', 'hanyNaponta' => 7,
            'override'=>true, // használathoz még át kell írni pár függvényt!!!
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
	    'igDt' => $_TANEV['zarasDt'],
	);
	$TOOL['tanarSelect'] = array('tipus'=>'cella','paramName'=>'tanarId', 'post'=>array('het','tolDt'));
	getToolParameters();
	
	
	
?>
