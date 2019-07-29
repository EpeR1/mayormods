<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {
    
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztaly.php');

	require_once('include/modules/naplo/share/kepzes.php');

	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/modules/naplo/share/ora.php');
	require_once('include/modules/naplo/share/jegy.php');
	require_once('include/modules/naplo/share/jegyModifier.php');
	require_once('include/modules/naplo/share/hianyzasModifier.php');

	require_once('include/share/date/names.php');

	if (isset($_POST['mkId']) && $_POST['mkId'] != '') $mkId = $_POST['mkId'];
	if (isset($_POST['targyId']) && $_POST['targyId'] != '') $targyId = $_POST['targyId'];
	if (isset($_POST['tankorId']) && $_POST['tankorId'] != '') $tankorId = $_POST['tankorId'];
	elseif (isset($_GET['tankorId']) && $_GET['tankorId'] != '') $tankorId = $_GET['tankorId'];
	if (isset($_POST['tanev']) && $_POST['tanev'] != '') $tanev = $_POST['tanev'];

	if (!isset($tanev)) $tanev=__TANEV;
	$dt = date('Y-m-d');
	$ret = getIdoszakByTanev(array('tanev' => $tanev, 'tipus' => array('tankörnévsor módosítás'), 'tolDt' => $dt, 'igDt' => $dt));
	$modositasiIdoszak = (is_array($ret) && count($ret) > 0);

	$ADAT['diakSelected']=$_POST['diaktorol'];

        if ($tanev!=__TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;

	$ADAT['tanev'] = $tanev;

	/* Dátumok */
        if (isset($_POST['refDt']) && $_POST['refDt'] != '') $refDt = $_POST['refDt'];
        elseif (time()<strtotime($TA['kezdesDt'])) $refDt = $TA['kezdesDt'];
	else  $refDt = date('Y-m-d');

	$ADAT['refDt'] = $refDt;

        if (isset($_POST['igDt']) && $_POST['igDt'] != '') $ADAT['igDt'] = $igDt = $_POST['igDt'];
	$_POST['tolDt']=$_POST['refDt'];


	// Le kell kérdeznünk, hogy tanára-e a tankörnek...
	if (isset($tankorId) && checkTankorInTanev($tankorId, $tanev)) {
	    $ADAT['tankor']['tanarai'] = getTankorTanaraiByInterval($tankorId,array('tanev'=>$tanev,'result'=>'nevsor'));
	    $tanarIds = array();
	    for ($i = 0; $i < count($ADAT['tankor']['tanarai']); $i++)
		if ($tankorId == $ADAT['tankor']['tanarai'][$i]['tankorId'])
		    $tanarIds[] = $ADAT['tankor']['tanarai'][$i]['tanarId'];
	    define('__TANARA',(__TANAR && in_array(__USERTANARID, $tanarIds)));
	    if (
		$_TANEV['statusz'] == 'aktív'
		&& (
		    __NAPLOADMIN || __VEZETOSEG
		    || (
			$modositasiIdoszak
			&& ( __TANARA )
		    )
		)
	    ) define('__MODOSITHATO', true);
	    else define('__MODOSITHATO', false);
	} // van tankör
	if (defined('__MODOSITHATO') && __MODOSITHATO) {
	switch ($action) {
	    case 'tankorUjDiak':
		if ($_POST['diakId'] != '')
		    tankorDiakFelvesz($_POST);
		break;
	    case 'tankorUjDiakMulti':
		if ($_POST['diakId'] != '')
		    for ($i=0; $i<count($_POST['diakId']); $i++) {
			$D = $_POST;
			$D['diakId'] = $_POST['diakId'][$i];
			tankorDiakFelvesz($D);
		    }
		break;
	    case 'tankorDiakMod':
		$oldCount = count($_SESSION['alert']);
		tankorDiakModify($_POST); // és törlés
		$ADAT['voltUtkozes'] = ($oldCount < count($_SESSION['alert']));
		break;
	} // switch
	} // __NAPLOADMIN - mégegyszer - hátha később két szinten szét akarjuk bontani

	// force variables to reload
	if (isset($tankorId)) {
	    $TANKORADAT = getTankorAdat($tankorId,$tanev);
	    $TANKORADAT[0] = $TANKORADAT[$tankorId][0];
	    $ADAT['tankorAdat'] = $TANKORADAT[0];
	    $targyId = $TANKORADAT[0]['targyId'];
	}
	if (isset($targyId)) {
	    $TARGYADAT = getTargyById($targyId);
	    $mkId = $TARGYADAT['mkId'];    
	}
							    
	if (isset($tankorId) && checkTankorInTanev($tankorId, $tanev)) {
	    $ADAT['tankorId'] = $tankorId;
	    $ADAT['tanevadat'] = getTanevAdat($tanev);
	    $ADAT['tankor']['diakjai'] = getTankorDiakjaiByInterval($tankorId, $tanev, $ADAT['refDt'], $ADAT['refDt']);
	    $ADAT['tankor']['osztalyai'] = getTankorOsztalyaiByTanev($tankorId, $tanev, array('tagokAlapjan' => false, 'result' => 'id'));
	    $ADAT['tankor']['szemeszterei'] = getTankorSzemeszterei($tankorId);
	    // !!! Ez az aktuális státuszt és osztály tagságot nézi csak...
	    // $ADAT['diakok'] = getDiakokByOsztalyId( $ADAT['tankor']['osztalyai'], array('tanev'=>$tanev,'result'=>'assoc'));
	    // !!! Így a $ADAT['refDt'] szerinti névsort kérdezzük le, megfelelő jogviszony státusszal!
	    $ADAT['diakok'] = array(); $statuszLista = array('jogviszonyban van', 'magántanuló', 'egyéni munkarend');
	    foreach ($ADAT['tankor']['osztalyai'] as $_osztalyId) {
		$tmp = getDiakokByOsztaly($_osztalyId, array('tolDt' => $ADAT['refDt'], 'igDt' => $ADAT['refDt'], 'statusz' => $statuszLista));
		foreach ($statuszLista as $statusz)
		    foreach ($tmp[$statusz] as $_diakId)
			$ADAT['diakok'][$_osztalyId][] = array(
			    'diakId' => $_diakId, 'diakNev' => $tmp[$_diakId]['diakNev'], 'beDt' => $tmp[$_diakId]['beDt'], 
			    'kiDt' => $tmp[$_diakId]['kiDt'], 'statusz' => $statusz
			);
	    }
	    $ADAT['diakok']['vendegTanulok'] = getDiakok(array('tolDt' => $ADAT['refDt'], 'igDt' => $ADAT['refDt'], 'statusz' => array('vendégtanuló')));
	    foreach($ADAT['diakok'] as $_o=>$_D) for ($i=0; $i<count($_D); $i++) $DIAKIDK[] = $_D[$i]['diakId'];
	    $ADAT['diakOsztaly'] = getDiakokOsztalyai($ADAT['tankor']['diakjai']['idk'], array('tanev'=>__TANEV));
	    $ADAT['diakKepzes'] = getKepzesByDiakId($DIAKIDK, array('result'=>'assoc','dt'=>$ADAT['refDt']));
	    $ADAT['diakAdat'] = getDiakAdatById($DIAKIDK,array('result'=>'assoc','keyfield'=>'diakId'));
	    $ADAT['osztaly'] = getOsztalyok($tanev,array('result'=>'assoc'));
	}
	// -------------------------------------------------------------------------
	$TOOL['tanevSelect'] = array('tipus'=>'cella','paramName' => 'tanev',
            'tervezett'=>true,
	    'post'=>array('mkId','targyId','tankorId','dt'));
	$TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post'=>array('tanev','dt'));
	$TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'post'=>array('mkId','tanev','dt'));
	$TOOL['tankorSelect'] = array('tipus'=>'cella','paramName'=>'tankorId', 'post'=>array('tanev','mkId','targyId','dt','refDt'));
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=tankor'),
    	    'titleConst' => array('_TANKOR'), 'post' => array('tankorId','mkId','targyId','dt','tanev'));
        
	$TOOL['tanevLapozo'] = array('tipus'=>'sor','paramName'=>'tanev', 'post'=>array('mkId','targyId','tankorId','dt'),
    				           'tanev'=>$tanev);
	// megj: ha nincs munkaterv, akkor a selectben nem lesz kiválasztva semmi...
        $TOOL['datumSelect'] = array(
            'tipus'=>'sor',
            'paramName' => 'refDt', 
	    'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])),
            'igDt' => $TA['zarasDt'],
//            'napTipusok' => array('tanítási nap', 'speciális tanítási nap'),
	    'post'=>array('mkId','targyId','tankorId','tanev')
        );


	getToolParameters();

    }
?>