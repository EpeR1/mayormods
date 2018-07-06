<?php

// EZ A FILE ELAVULT LEHET!

if (_RIGHTS_OK !== true) die();

if (!__NAPLOADMIN && !__VEZETOSEG) {
        $_SESSION['alert'][] = 'page:insufficient_access';
} else {

    $tanev = __TANEV;
    
        require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztaly.php');
			    
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/share/date/names.php');
	     
    $osztalyId = readVariable($_POST['osztalyId'], 'id');
    $tolDt = readVariable($_POST['tolDt'],'datetime');

        if (!isset($tolDt))
            // A következő nap előtti hétfő
            $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', time())));
        if (strtotime($tolDt) > strtotime($_TANEV['zarasDt'])) $tolDt = $_TANEV['zarasDt'];
        elseif (strtotime($tolDt) < strtotime($_TANEV['kezdesDt'])) $tolDt = $_TANEV['kezdesDt'];
        if ($tolDt != '') $het = getOrarendiHetByDt($tolDt);
        if ($het == '') $het = getLastOrarend();
        $igDt = date('Y-m-d', mktime(0,0,0,date('m',strtotime($tolDt)), date('d',strtotime($tolDt))+6, date('Y',strtotime($tolDt))));
			    
    $ADAT['tanarok'] = getTanarok(array('result'=>'assoc'));

// =====================
    if ($tankorId!='') {
        $ADAT['orarend'] = getOrarendByTankorId($tankorId, array('tolDt'=>$tolDt,'igDt'=>$igDt));
        //$ADAT['toPrint'] = getTankorNev($tankorId); // vagy getTankornev külön?
    } elseif($tanarId!='') {
        $ADAT['orarend'] = getOrarendByTanarId($tanarId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
        $ADAT['toPrint'] = $ADAT['tanarok'][$tanarId]['tanarNev'];
    } elseif($diakId!='') {
        $ADAT['orarend'] = getOrarendByDiakId($diakId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
    } elseif ($osztalyId!='') {

	$ADAT['diakok'] = getDiakokByOsztaly($osztalyId, array('tanev' => $tanev, 'statuszonkent' => 0));

	    foreach ($ADAT['diakok'] as $_diakId => $dAdat) {
		$D = array();
    		$D['orarend']  = getOrarendByDiakId($_diakId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
    		$D['tankorok'] = getTankorByDiakId($_diakId,__TANEV,array('csakId'=>false,'result'=>'multiassoc','tolDt'=>$tolDt,'igDt'=>$igDt));
		$ADAT['diakOrarend'][$_diakId] = $D;
	    }

    } elseif ($mkId!='') {
        $ADAT['orarend'] = getOrarendByMkId($mkId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
    } elseif ($teremId!='') {
        $ADAT['orarend'] = getOrarendByTeremId($teremId,'',array('tolDt'=>$tolDt,'igDt'=>$igDt));
    }
        else $ADAT = array();
								    
	/* TOOL ME :) */
									
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('tanarId','osztalyId','tankorId'),
	    'paramName' => 'tolDt', 'hanyNaponta' => 7,
	    'tolDt' => date('Y-m-d', strtotime('Monday', strtotime($_TANEV['kezdesDt']))),
	    'igDt' => $_TANEV['zarasDt'],
	);
	$TOOL['osztalySelect']= array('tipus'=>'cella','paramName'=>'osztalyId', 'post'=>array('tolDt'));
	getToolParameters();

}

?>
