<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
#	require_once('include/modules/naplo/share/targy.php');
#	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztaly.php');

	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');
	require_once('include/modules/naplo/share/jegyModifier.php');
	require_once('include/modules/naplo/share/jegy.php');

	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/share/date/names.php');

	$refDt = readVariable($_POST['refDt'],'datetime',$_TANEV['kezdesDt']);
	$osztalyId = readVariable($_POST['osztalyId'],'id',null);
	$ADAT['osztalyId'] = $osztalyId;
	$forceDel = (readVariable($_POST['forceDel'],'numeric unsigned',null)=='1') ? true:false;

	$diakId = readVariable($_POST['diakId'], 'id', readVariable($_GET['diakId'],'id'));
	$ADAT['diakId'] = $diakId;
	if (isset($_POST['tanev']) && $_POST['tanev'] != '') $tanev = $_POST['tanev'];
	if (!isset($tanev)) $tanev=__TANEV;
	$ADAT['tanev'] = $tanev;

	//$igDt = date('Y-m-d', strtotime('-1day',strtotime($refDt)));

	if ($tanev!=__TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;

if (isset($refDt)) {

	$ADAT['refDt'] = $refDt;

	define('__MODOSITHATO',(( (__NAPLOADMIN || __VEZETOSEG) && ($TA['statusz']=='aktív' || (__FOLYO_TANEV && $tanev==__TANEV)))));

	if ($action=='do' && __MODOSITHATO) {
	    for($i=0; $i<count($_POST['UJtankorId']); $i++) {
		$_D = array('tankorId'=>$_POST['UJtankorId'][$i],'diakId'=>$diakId,'tolDt'=>$refDt,'jelenlet'=>'kötelező','kovetelmeny'=>'jegy');
		tankorDiakFelvesz($_D);
	    }
	    for($i=0; $i<count($_POST['DELtankorId']); $i++) {
		$_D = array('tankorId'=>$_POST['DELtankorId'][$i],'diakId'=>$diakId,'tolDt'=>$refDt);
		if ($forceDel===true) {
		    $_D['utkozes'] = 'torles';
		    define('__VEZETOSEG_TOROLHET_HIANYZAST',true); // Hiányzást - adminon kívül - alap helyzetben csak a rögzítő tanár törölhet!
		}
		$_result = tankorDiakTorol($_D);
		if ($_result===true) $_SESSION['alert'][] = 'info:done:'.$_POST['DELtankorId'][$i];
		else $_SESSION['alert'][] = 'alert:rollback:tankorId='.$_POST['DELtankorId'][$i];
	    }
	    for ($i=0; $i<count($result); $i++) {
	    
	    }
	    
	} elseif ($action=='do') {
	    $_SESSION['alert'][] = 'info:deadline_expired:';
	}


	if ($diakId!='') {
	    $ADAT['osztalyok'] = $osztalyIdk = getDiakOsztalya($diakId, array('tanev'=>$tanev,'tolDt'=>$refDt));
	    $ADAT['tankorok']['diake'] = getTankorByDiakId($diakId,$tanev,array('tolDt'=>$refDt,'igDt'=>$refDt));
	    /* Óraszámok megállapítása */
	    $sum = 0;
	    if (is_array($ADAT['tankorok']['diake']))
	    foreach ($ADAT['tankorok']['diake'] as $_ti => $TA) {
		$_tankorId = $TA['tankorId'];
		$a = getTankorOraszamByTanev($tanev,$_tankorId);
		$ADAT['diakTankorOraszam'][$TA['tankorTipus']] += $a[$_tankorId];
		$sum += $a[$_tankorId];
		$ADAT['tankorok']['diake'][$_ti]['hetiOraszam'] = $a[$_tankorId];
	    }
	    /* --- */
	    $ADAT['diakOsszOraszam'] = ($sum);
	}
	if (is_array($osztalyIdk)) {
	    for ($i=0; $i<count($osztalyIdk); $i++) {
		$ADAT['tankorok']['osztalye'][ $osztalyIdk[$i]['osztalyId'] ] = getTankorByOsztalyId($osztalyIdk[$i]['osztalyId'],$tanev,array('tanarral'=>true));
	    }
	}	
	
}

	// -------------------------------------------------------------------------

	$TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('diakId','tanev','osztalyId'),
            'paramName' => 'refDt', 'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])),
            'igDt' => $TA['zarasDt'],
//            'napTipusok' => array('tanítási nap', 'speciális tanítási nap')
        );

	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'tanev' => $tanev, 'post' => array('tanev','refDt'));
	$TOOL['diakSelect'] = array('tipus'=>'cella','paramName'=>'diakId', 'post'=>array('tanev','refDt','osztalyId'));
    //    $TOOL['tanarSelect'] = array('tipus'=>'sor','paramName'=>'tanarId', 'post'=>array());
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=tankor'),
    	    'titleConst' => array('_TANKOR'), 'post' => array('tankorId','mkId','targyId'));
        
	$TOOL['tanevLapozo'] = array('tipus'=>'sor','paramName'=>'tanev', 'post'=>array('diakId'),
    				           'tanev'=>$tanev);

	getToolParameters();

    }
?>
