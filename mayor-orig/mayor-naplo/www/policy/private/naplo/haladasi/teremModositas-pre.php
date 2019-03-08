<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {

	$_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	global $_TANEV;
        if ($_TANEV['statusz']!='aktív') $_SESSION['alert'][] = 'page:nem_aktív_tanev:'.$_TANEV['tanev'];

	$ADAT['dt'] = $dt = readVariable($_POST['dt'], 'datetime', date('Y-m-d'));
	$ADAT['ora'] = 	$ora = readVariable($_POST['ora'], 'numeric', null);
	$ADAT['tanarId'] = 	$tanarId = readVariable($_POST['tanarId'], 'numeric unsigned', null);
	if (!isset($tanarId)) $osztalyId = readVariable($_POST['osztalyId'], 'id', null);
	$ADAT['tankorId'] =	$tankorId = readVariable($_POST['tankorId'], 'id', null);
	$ADAT['teremId'] =	$teremId = readVariable($_POST['teremId'], 'id', null);

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/modules/naplo/share/terem.php');
        require_once('include/share/date/names.php');
	
	$ADAT['napiMinOra'] = $napiMinOra = getMinOra();
	$ADAT['napiMaxOra'] = $napiMaxOra = getMaxOra();

	if (isset($dt)) {
	    // órarendiÓrák betöltése - ha szükséges
	    //checkNaplo($dt);
	    /* Az órákat nem töltjük itt be! */

	    if ($action=="teremModosit") {
		$oraId = readVariable($_POST['oraId'],'id',null);
		$ujTeremId = readVariable($_POST['ujTeremId'],'id',null);
		$ADAT['oraAdat'] = getOraAdatById($oraId);
		$lr = db_connect('naplo');
		db_start_trans($lr);
		if (($x = checkHaladasiSzabadTerem($dt,$ora,$ujTeremId,$lr)) === true)
		    haladasiTeremModositas($oraId,$ujTeremId,$lr);
		else
		    if ($ujTeremId != $ADAT['oraAdat']['teremId']) $_SESSION['alert'][] = 'info:nem_szabadTerem:'.$ADAT['oraAdat']['teremId'].' -- '.$ujTeremId;
		db_commit($lr);
		db_close($lr);

	    }

	    if (isset($tankorId)) {
		// A tankör tanárainak lekérdezése (tanarSelect számára)
		$Tanarok = getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => $dt, 'igDt' => $dt, 'result' => 'nevsor'));
		if (isset($tanarId)) {
		    // A tanár a tankör tanára-e?
		    for ($i = 0; ($i < count($Tanarok) && $Tanarok[$i]['tanarId'] != $tanarId); $i++);
		    if ($i >= count($Tanarok)) {
			$_SESSION['alert'][] = 'message:wrong_data:pluszOra:Nem a tankör tanára:'."$tankorId/$tanarId";
			$Tanarok[] = array('tanarId' => $tanarId, 'tanarNev' => getTanarNevById($tanarId));
			$mkId = getTankorMkId($tankorId);
			$tanarMkIds = getTanarMunkakozosseg($tanarId);
			$ADAT['tanarId'] = $Tanarok[0]['tanarId'];
		    }
		}
		// Ha csak egy tanarId van, akkor azt állítsuk be! 
		if (count($Tanarok) == 1 && !isset($tanarId)) {
		    $tanarId = $Tanarok[0]['tanarId'];
		    if (isset($osztalyId)) unset($osztalyId);
		}
	    }

	    $ADAT['ki'] = $tanarId;
	    $ADAT['kit'] = $tanarId;
	    $ADAT['oraId'] = getOraIdByPattern($ADAT);
	    $ADAT['oraAdat'] = getOraAdatById($ADAT['oraId']);	    
	    $ADAT['szabadTerem'] = getSzabadTermek(array('dt' => $dt, 'ora' => $ora));
	    $ADAT['tankorLetszam'] = getTankorLetszam($ADAT['oraAdat']['tankorId'], array('refDt'=>$ADAT['dt']));
	}

	// toolBar
        $TOOL['datumSelect'] = array(
            'tipus' => 'cella', 'post' => array('ora', 'tanarId', 'osztalyId', 'tankorId'),
            'paramName' => 'dt', 'hanyNaponta' => 1,
            'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
	    'napTipusok' => array('tanítási nap','speciális tanítási nap'),
        );
	if (isset($dt)) {
    	    $TOOL['oraSelect'] = array('tipus'=>'cella', 'orak' => $SzabadOrak, 'foglaltOrakkal' => true, 'post'=>array('osztalyId', 'tanarId', 'tankorId', 'dt', 'ora'));
    	    $TOOL['tanarSelect'] = array('tipus'=>'cella', 'tanarok' => $Tanarok, 'post' => array('dt', 'ora', 'tankorId', 'teremId' ));
    	    //if (!isset($osztalyId) || isset($tankorId)) $TOOL['tanarSelect'] = array('tipus'=>'cella', 'tanarok' => $Tanarok, 'post' => array('dt', 'ora', 'tankorId', 'teremId' ));
    	    //if (!isset($tanarId)) $TOOL['osztalySelect'] = array('tipus'=>'sor','paramName' => 'osztalyId', 'post'=>array('dt', 'ora', 'tankor', 'teremId'));
    	    //if (isset($osztalyId) || isset($tanarId) || isset($ora)) {
    	    //	$TOOL['tankorSelect'] = array('tipus'=>'sor', 'paramName'=>'tankorId', 'tankorok' => $szabadTankorok, 'tolDt'=>$dt, 'igDt'=>$dt, 'post'=>array('osztalyId','tanarId','dt','ora','teremId'));
	    //}
    	    //if (isset($ora)) $TOOL['teremSelect'] = array('tipus'=>'cella', 'paramName'=>'teremId', 'termek' => $Termek, 'post'=>array('osztalyId','tanarId','dt','ora','tankorId'));
	}

        getToolParameters();
    } // admin vagy igazgató

?>
