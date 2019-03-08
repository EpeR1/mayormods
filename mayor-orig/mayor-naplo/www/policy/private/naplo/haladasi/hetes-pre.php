<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__DIAK && !__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/orarend.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/dolgozat.php');
    require_once('include/modules/naplo/share/kepzes.php');
    require_once('include/modules/naplo/share/nap.php');
    require_once('include/modules/naplo/share/terem.php');
    require_once('include/modules/naplo/share/tanmenet.php');
    require_once('include/modules/naplo/share/hetes.php');
    require_once('include/modules/naplo/share/helyettesitesModifier.php');
    require_once('include/share/date/names.php');


    $ADAT['csakUres'] = $csakUres = readVariable($_POST['csakUres'],'bool',false,null);
    $ADAT['teremId' ] = $teremId = readVariable($_POST['teremId'],'id');

    // Egy tankör haladási naplójához
    $tankorId = readVariable($_POST['tankorId'],'id');

    // Ha diák nézi, akkor csak a saját tanköreit láthatja
    if (__DIAK) $diakId = __USERDIAKID;
    else $diakId = readVariable($_POST['diakId'],'id');

    $osztalyId = readVariable($_POST['osztalyId'],'id');
    
    if (isset($diakId)) {
	    // A diák már nem határozza meg egyértelműen az osztályt! --> osztalyId-t nem állítunk - hagyjuk
            if (isset($tankorId) and !tankorTagjaE($diakId, $tankorId)) {
                unset($tankorId);
            }
	    $ADAT['title'] = getDiakNevById($diakId);
    } else {
	    if (isset($teremId)) {
	    } elseif (!isset($osztalyId)) {
                if (!isset($tanarId)) $tanarId = readVariable($_POST['tanarId'],'id');
                if (!isset($tanarId)) $tanarId = readVariable($_GET['tanarId'],'id');
                if (!isset($tanarId) && __USERTANARID!==false && __TANAR) $tanarId = __USERTANARID;
    		define(__PLUSZBEIRHAT,
		    (__USERTANARID == $tanarId || __NAPLOADMIN===true || __VEZETOSEG===true)
		);
	    }
    }
    if (!defined('__PLUSZBEIRHAT')) define('__PLUSZBEIRHAT',false);
    /* ------------------------------------------------- */

        // lapozás, tól-ig beállítás
        if (!isset($tankorId)) {

            // egy tanár ($tanarId) vagy osztály ($osztaly) összes órája --> lapozni kell
	    // reading sensitive data
	    $igDt = readVariable($_POST['igDt'], 'datetime', date('Y-m-d'));
	    $tolDt = readVariable($_POST['tolDt'], 'datetime');
	    $lapoz = readVariable($_POST['lapoz'], 'enum', null, array('<<','>>','nextWeek','prevWeek'));
	    $lapoz1 = readVariable($_POST['lapoz1'], 'enum', null, array('<<','>>','nextWeek','prevWeek'));

	    // set defaults ++
//	    if (strtotime($igDt) > strtotime($_TANEV['zarasDt'])) $igDt = $_TANEV['zarasDt'];
	    if (strtotime($igDt) > strtotime($_TANEV['zarasDt'])) $igDt = date('Y-m-d', strtotime('next Saturday', strtotime($_TANEV['zarasDt'])));
	    elseif (strtotime($igDt) < strtotime($_TANEV['kezdesDt'])) $igDt = $_TANEV['kezdesDt'];
	    if (date('w', strtotime($igDt)) == 0) $igDt = date('Y-m-d',strtotime('-1 days',strtotime($igDt))); // Hogy vasárnap még a múltheti látszódjon
	    $eVas = date('Y-m-d',strtotime('Saturday',strtotime($igDt)));

            if ($igDt == '') $igDt = $eVas;
            if ($tolDt == '') $tolDt = $eHet = date('Y-m-d',strtotime('last Monday 02:00',strtotime($eVas)));

            if (in_array($lapoz,array('<<','prevWeek')) or in_array($lapoz1,array('<<','prevWeek'))) {
                $tolDt = date('Y-m-d',strtotime('last Monday 02:00',strtotime($eHet)));
//                $igDt = date('Y-m-d',strtotime('Saturday',strtotime($tolDt)));
// Vasárnap
                $igDt = date('Y-m-d',strtotime('Sunday',strtotime($tolDt)));
            } elseif (in_array($lapoz,array('>>','nextWeek')) or in_array($lapoz1,array('>>','nextWeek'))) {
                $tolDt = date('Y-m-d',strtotime('Monday 02:00',strtotime($eVas)));
//                $igDt = date('Y-m-d',strtotime('Saturday',strtotime($tolDt)));
// Vasárnap
                $igDt = date('Y-m-d',strtotime('Sunday',strtotime($tolDt)));
            }
            if ($csakUres || strtotime($tolDt) < strtotime($_TANEV['kezdesDt'])) $tolDt = date('Y-m-d',strtotime($_TANEV['kezdesDt']));
	    // Ha ezt kiveszem, akkor mindig kirakja a teljes hetet, de a jövőbeli órákat nem lehet beírni!
	    // Itt a post értékét nem használjuk fel, csak vizsgáljuk.
	    if ((!isset($_POST['igDt']) || $_POST['igDt'] == '')&& strtotime($igDt) > time()) $igDt = date('Y-m-d');
            define('_SHOW_DAYS_FROM',$tolDt);
            define('_SHOW_DAYS_TO',$igDt);

        } else {

            // egy tanulócsoport órái (nem kell lapozni)
            define('_SHOW_DAYS_FROM',date('Y-m-d',strtotime($_TANEV['kezdesDt'])));
            define('_SHOW_DAYS_TO',date('Y-m-d'));

        }
    /* ------------------------------------------------- */
    // Jogosultság ellenőrzés, tanév aktív-e...

	// A megjelenítéshez
	if (isset($osztalyId) && !isset($diakId)) {
		$osztalyAdat = getOsztalyAdat($osztalyId);
		$ADAT['title'] = $osztalyAdat['osztalyJel'].' ('.$osztalyAdat['osztalyfonok']['tanarNev'].')';
		// hetesek miatt
		$ADAT['osztalyId'] = $osztalyId;
		$ADAT['diakok'] = getDiakok(array('osztalyId' => $osztalyId));
		for ($i = 0; $i < count($ADAT['diakok']); $i++) $ADAT['diakNevek'][ $ADAT['diakok'][$i]['diakId'] ] = $ADAT['diakok'][$i]['diakNev'];
	} elseif (isset($tanarId)) {
		$ADAT['title'] = getTanarNevById($tanarId);
		//DEPRECATED $ADAT['oraTerheles'] = getOraTerhelesByTanarId(array('tanarId'=>$tanarId,'tolDt'=>$tolDt,'igDt'=>$igDt));
		$ADAT['oraTerheles'] = getOraTerhelesStatByTanarId(array('tanarId'=>$tanarId,'dt'=>_SHOW_DAYS_TO));
	} elseif (isset($teremId)) {
		$ADAT['title'] = $teremId;
	}

    if (
	$_TANEV['szemeszter'][1]['statusz'] == 'aktív'		// Csak aktív tanévben lehet módosítani
	&& (
	    __NAPLOADMIN					// adminnak vagy
	    || __VEZETOSEG					// vezetőség - tanév végi pótlásokhoz kell!
	    || ((__TANAR || __VEZETOSEG ))			// A tanároknak, vezetőségi tagoknak
//	    || ((__TANAR || __VEZETOSEG ) && __FOLYO_TANEV)	// A tanév közben a tanároknak, vezetőségi tagoknak - az év végi napok problémásak így!
	)
    ) {
	// action
        if ($action == 'haladasiNaploBeiras' && (
	    is_array($_POST['oraId'])
	    ||is_array($_POST['UJORA'])
	    ||is_array($_POST['ORATOROL'])
	)) {
        } elseif ($action == 'hetesFelvetel' && isset($osztalyId) && ((__OSZTALYFONOK===true && in_array($osztalyId, $_OSZTALYA)) || __NAPLOADMIN===true )) {
	    $ADAT['dt'] = readVariable($_POST['dt'], 'date');
	    $ADAT['hetes'][1] = readVariable($_POST['hetes1'], 'numeric unsigned');
	    $ADAT['hetes'][2] = readVariable($_POST['hetes2'], 'numeric unsigned');
	    hetesFelvetel($ADAT);
	}
    }

    if ((date('Y-m-d',strtotime($tolDt))==date('Y-m-d',strtotime('next Monday')) 
	 || date('Y-m-d',strtotime($tolDt))==date('Y-m-d',strtotime('last Monday')) 
	) && (__NAPLOADMIN || __VEZETOSEG)) {
	$_NAPOK = _genNapok($tolDt,$igDt);
	for ($i=0; $i<count($_NAPOK); $i++) {
		checkNaplo($_NAPOK[$i]);
	}
    }

    /* ------------------------------------------------- */

	//if (isset($osztalyId) && !isset($diakId)) 
	$ADAT['diakok'] = getDiakok(array('osztalyId' => $osztalyId,'result'=>'assoc','keyfield'=>'diakId'));
	$ADAT['hetesek'] = getHetesek($osztalyId, _SHOW_DAYS_FROM);
	$ADAT['osztalyok'] = getOsztalyok(__TANEV,array('result'=>'assoc'));

	if (isset($tankorId)) $orderBy = array('dt DESC','ora DESC'); // A lekérdezéshez
	else $orderBy = array('dt DESC','ora ASC');

        // tankörök lekérdzése
	    if (isset($diakId)) {
		$ADAT['haladasiTipus']='diakHaladasi';
		$Tankorok = getTankorByDiakId($diakId, __TANEV, array('tolDt'=>_SHOW_DAYS_FROM, 'igDt'=>_SHOW_DAYS_TO));
		$Osztalyok = getDiakOsztalya($diakId, array('tanev'=>__TANEV,'tolDt'=>$tolDt,'igDt'=>$igDt, 'result'=>'idonly'));
    	    } elseif (isset($osztalyId)) {
		$ADAT['haladasiTipus']='osztalyHaladasi';
		$Tankorok = getTankorByOsztalyId($osztalyId, __TANEV);
		$Osztalyok = array($osztalyId);
    	    } elseif (isset($teremId)) { // --new
		$ADAT['haladasiTipus']='teremHaladasi';
		$Tankorok = getTankorByTeremId($teremId, __TANEV);
		$Osztalyok = array($osztalyId);
    	    } elseif (isset($tanarId)) {
		$Tankorok = getTankorByTanarId(
		    $tanarId,
		    __TANEV,
		    array('csakId' => false, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO)
		);
		$Osztalyok = getTanarOsztaly($tanarId, array('tanev' => __TANEV, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result'=>'idonly'));
    	    }
	    $TankorokMutat = $Tankorok;
	    // Kell a munkaterv!! Ahhoz kell(enek) az osztály(ok)!
	    $ADAT['munkaterv'] = getMunkatervByOsztalyId($Osztalyok, array('result' => 'idonly'));

	    // Tanmenet
	    if (is_array($Tankorok)) foreach ($Tankorok as $key => $tAdat) $ADAT['tankorIds'][] = $tAdat['tankorId'];
	    if (is_array($ADAT['tankorIds'])) {
		$ADAT['tankorTanmenet'] = getTanmenetByTankorIds($ADAT['tankorIds'], array('tanev' => __TANEV, 'jovahagyva'=>!__TANAR));
	    }
	    if (isset($tankorId)) {
		if (__DIAK===true) {
		    $allowed=false;
		    for($i=0; $i<count($Tankorok); $i++) {
			if ($tankorId == $Tankorok[$i]['tankorId']) { $allowed=true; break; }
		    }
		} else $allowed = true;
		if ($allowed) {
        	    $Tankorok = getTankorById($tankorId, __TANEV); // felül kell írnunk
    		    //$ADAT['haladasi'] = getHaladasi($Tankorok, $ADAT['munkaterv'], $orderBy, $csakUres);
		}
	    } elseif ($teremId!='') {
    		//$ADAT['haladasi'] = getHaladasi($Tankorok, '', $orderBy, '', $csakUres,$teremId);
	    } else {
    		//$ADAT['haladasi'] = getHaladasi($Tankorok, $ADAT['munkaterv'], $orderBy, $tanarId, $csakUres);
	    }
	    $ADAT['ORAIDK'] = array();
	    if (is_array($ADAT['haladasi'])) {
		reset($ADAT['haladasi']);
		foreach($ADAT['haladasi'] as $_k => $_v) {
		    for ($i=0; $i<count($_v); $i++) {
			if (!is_null($ADAT['haladasi'][$_k][$i]['oraId'])) $ADAT['ORAIDK'][] = $ADAT['haladasi'][$_k][$i]['oraId'];
		    }
		}
	    }
	    $ADAT['oraLatogatasok'] = getOralatogatasByOraIds($ADAT['ORAIDK']);
	    foreach ($ADAT['oraLatogatasok'] as $olId => $olAdat)
		foreach ($olAdat['tanarIds'] as $_tanarId) $ADAT['oraLatogatasok'][$olId]['tanarNevek'][] = getTanarNevById($_tanarId);
	    // Kell a munkaterv!! Ahhoz kell(enek) az osztály(ok)!
	    if (isset($diakId)) $O = getDiakOsztalya($diakId, array('tanev'=>__TANEV,'tolDt'=>$tolDt,'igDt'=>$igDt, 'result'=>'idonly'));
	    elseif (isset($osztalyId)) $O = array($osztalyId);
	    elseif (isset($tanarId)) $O = getTanarOsztaly($tanarId, array('tanev' => __TANEV, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result'=>'idonly'));
	    $ADAT['munkaterv'] = getMunkatervByOsztalyId($O, array('result' => 'idonly'));

	    $ADAT['tanitasiNap'] = getTanitasiNapAdat(_genNapok($tolDt,$igDt), array('munkatervIds' => $ADAT['munkaterv']));
	    $ADAT['diakId'] = $diakId;
	    $ADAT['osztalyId'] = $osztalyId;
	    $ADAT['tanarId'] = $tanarId;
	    $ADAT['tankorId'] = $tankorId;
	    $ADAT['terem'] = getTermek(array('result'=>'assoc'));
	    $ADAT['feladatTipus'] = getFeladatTipus();
	    $ADAT['maxOra'] = 16; // Ha reggel 8-kor kezdődik a tanítás, akkor 24 óráig rendben vagyunk így...
//	    if ($tanarId>0) //$ADAT['oraTerheles'] = getOraTerhelesByTanarId(array('tanarId'=>$tanarId,'tolDt'=>$tolDt,'igDt'=>$igDt));
//		$ADAT['oraTerheles'] = getOraTerhelesStatByTanarId(array('tanarId'=>$tanarId,'tolDt'=>$tolDt,'igDt'=>$igDt));
	
	    $ADAT['szabadTermek'] = getSzabadTermekByDtInterval($tolDt,$igDt, null,'ora');
	    $ADAT['tankorTipusok'] = getTankorTipusok();
    /* ------------------------------------------------- */

        // toolBar
	$TOOL['datumSelect'] = array(
	    'tipus'=>'sor', 'post'=>array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
	    'paramName' => 'igDt', 'hanyNaponta' => 7,
	    'tolDt' => date('Y-m-d', strtotime('Saturday', strtotime($_TANEV['kezdesDt']))),
	    'igDt' => date('Y-m-d', strtotime('next Saturday', strtotime($_TANEV['zarasDt']))),
	    'override' => true
	);
        if (__NAPLOADMIN or __VEZETOSEG or __TANAR or __TITKARSAG) {
//	    $TOOL['tanarSelect'] = array('tipus' => 'cella', 'post' => array('igDt'));
//	    $TOOL['teremSelect'] = array('tipus' => 'cella', 'post' => array('igDt'));
	    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('igDt'));
            if (isset($osztalyId))
		$TOOL['diakSelect'] = array('tipus' => 'cella', 'paramName' => 'diakId', 'diakok' => $ADAT['diakok'], 'post' => array('osztalyId','igDt'));
//            if (isset($osztalyId) or isset($tanarId) or isset($diakId))
//		$TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $TankorokMutat, 'paramName' => 'tankorId', 'post' => array('osztalyId','tanarId','diakId','igDt'));
        } elseif (__DIAK===true) {
//	    $TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $TankorokMutat, 'paramName' => 'tankorId', 'post' => array('osztalyId','tanarId','diakId','igDt'));
	}
        getToolParameters();

?>
