<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR && !__DIAK) {
        $_SESSION['alert'][] = 'message:insufficient_access';
    } else {

        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/targy.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/tankorDiakModifier.php');
        require_once('include/modules/naplo/share/zaroJegyModifier.php');
        require_once('include/modules/naplo/share/zaradek.php');
        require_once('include/share/date/names.php');

	$ADAT['dt'] = $dt = readVariable($_POST['dt'],'date',date('Y-m-d'));

	if (__DIAK===true) {
	    $ADAT['diakId'] = $diakId = __USERDIAKID;
	} else {
	    $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'],'id');
	    $ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'],'id');
	    $ADAT['diakId'] = $diakId = readVariable($_POST['diakId'],'id');
	}
	$tolDt = readVariable($_POST['tolDt'],'date',$dt);

	$ADAT['diakAdat'] = getDiakok(array('result'=>'assoc'));

	if ($osztalyId!='') {
	    //$targyId = readVariable($_POST['targyId'],'id');
	    //$ADAT['diakAdat'] = getDiakAdatById($diakId);
	    //$ADAT['diakTargy'] = getTargyakByDiakId($diakId,array('tolDt'=>$dt,'result'=>'indexed'));
	    //$ADAT['diakTankor'] = getTankorByDiakId($diakId,__TANEV,array('tolDt'=>$dt,'igDt'=>$_TANEV['zarasDt']));
	    $ADAT['felmentes'] = getFelmentes(array('csakId' => false,
		'osztalyId'=>$osztalyId,
		'tolDt' => $_TANEV['kezdesDt'], 'igDt'=>$_TANEV['zarasDt'], 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól','értékelés alól')));
	} else {
	    $ADAT['felmentes'] = getFelmentes(array('csakId' => false, 
		'tolDt' => $_TANEV['kezdesDt'], 'igDt'=>$_TANEV['zarasDt'], 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól','értékelés alól')));
	}
	if (__NAPLOADMIN === true || __VEZETOSEG===true) {
	}
/* ------------- */
	$ADAT['diakZaradek'] = getDiakZaradekok($diakId, array('tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt']));
	$ADAT['tankorDiakFelmentes'] = getTankorDiakFelmentes($diakId, __TANEV,array('csakId' => false, 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'], 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól','értékelés alól')) );

/*
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post' => array('osztalyId', 'tanev'),
            'paramName' => 'dt',
	    'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
            'igDt' => $_TANEV['zarasDt'],
            'post'=>array('osztalyId','diakId')
        );
*/
	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('dt'));
        $TOOL['diakSelect'] = array('tipus'=>'cella', 'tanev'=>$tanev, 'osztalyId' => $osztalyId,
//            'statusz' => $ADAT['statusz'],
            'post' => array('osztalyId','dt')
        );
        getToolParameters();

    }

?>
