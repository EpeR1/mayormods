<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tanmenet.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/share/date/names.php');

    $ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'id', __TANAR?__USERTANARID:null);
    $ADAT['targyId'] = $targyId = readVariable($_POST['targyId'], 'id');

    $ADAT['tanmenetId'] = $tanmenetId = readVariable($_POST['tanmenetId'], 'id', readVAriable($_GET['tanmenetId'], 'id'));
    if (isset($tanmenetId)) {

	$ADAT['evfolyamJelek'] = getEvfolyamJelek(array('result'=>'idonly'));
	$ADAT['tanmenetAdat'] = getTanmenetAdat($tanmenetId);
	$ADAT['mkAdat'] = getMunkakozossegByTargyId($ADAT['tanmenetAdat']['targyId']);
	$ADAT['tanarId'] = $tanarId = $ADAT['tanmenetAdat']['tanarId'];
	$ADAT['targyId'] = $targyId = $ADAT['tanmenetAdat']['targyId'];
	define('__KESZITO', (__USERTANARID == $ADAT['tanmenetAdat']['tanarId']));
	define('__MKVEZETO', (__USERTANARID == $ADAT['mkAdat']['mkVezId']));
	define('__MODOSITHAT', 
		    __NAPLOADMIN // admin bármikor
			|| (
			    (__VEZETOSEG || __KESZITO || __MKVEZETO) // vezetőségi tag, mk.vez és a létrehozó szaktanár...
			    && ($ADAT['tanmenetAdat']['statusz'] == 'új' || $ADAT['tanmenetAdat']['statusz'] == 'kész') // ... ha még nincs jóváhagyva
			)
	);
	if (__NAPLOADMIN || __VEZETOSEG || __MKVEZETO) $ADAT['statusz'] = array('új','kész','jóváhagyott','publikus','elavult');
	elseif (__KESZITO)  {
	    if (in_array($ADAT['tanmenetAdat']['statusz'], array('új','kész'))) { $ADAT['statusz'] = array('új','kész'); }
	    else { $ADAT['statusz'] = array('jóváhagyott','publikus'); }	
	} else $ADAT['statusz'] = array();

	if ($action != '') {
	  if (__MODOSITHAT && $action == 'tanmenetTemakorModositas') {
		$ADAT['temakor']['oraszam'] = readVariable($_POST['oraszam'], 'numeric unsigned');
		$ADAT['temakor']['temakorMegnevezes'] = readVariable($_POST['temakorMegnevezes'], 'string', '');
		tanmenetTemakorModositas($ADAT);
		$ADAT['tanmenetAdat'] = getTanmenetAdat($tanmenetId);
	  } elseif ($action == 'tanmenetAdatModositas') {
	    if ((__NAPLOADMIN === true || __KESZITO) && readVariable($_POST['tanmenetTorol'],'numeric unsigned') == 1) {
		if (tanmenetTorol($tanmenetId)===false) {
		    $_SESSION['alert'][] = 'info:error:hiba a tanmenet törlésekor';
		} else {
		    $_SESSION['alert'][] = 'info:success';
		    unset ($ADAT);
		    unset($tanmenetId);
		}
	    } else { 
		if (__MODOSITHAT) { // Az óraszám és évfolyam-jel is változtatható
		    $DAT['oraszam'] = readVariable($_POST['oraszam'], 'numeric unsigned');
	    	    $DAT['evfolyamJel'] = readVariable($_POST['evfolyamJel'], 'numeric unsigned');
		} else { // csak a tanmenet neve és státusza változtatható
		    $DAT = $ADAT['tanmenetAdat'];
		}
		$DAT['tanmenetId'] = $tanmenetId;
		$DAT['tanmenetNev'] = readVariable($_POST['tanmenetNev'], 'string');
		$DAT['ujStatusz'] = readVariable($_POST['statusz'], 'enum', $ADAT['tanmenetAdat']['statusz'], $ADAT['statusz']);
		tanmenetAdatModositas($DAT);
		$ADAT['tanmenetAdat'] = getTanmenetAdat($tanmenetId);
	    }
	  }
	}
    }


    $TOOL['tanarSelect'] = array('tipus'=>'cella', 'paramName'=>'tanarId', 'post'=>array());
    $TOOL['targySelect'] = array('tipus'=>'cella', 'paramName'=>'targyId', 'post'=>array());
    if (isset($tanarId) || isset($targyId) || isset($tanmenetId)) {
	$TOOL['tanmenetSelect'] = array('tipus'=>'sor', 'paramName'=>'tanmenetId', 'post'=>array('tanarId','targyId'));
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=tanmenet&f=tanmenetInfo'),
        'titleConst' => array('_MUTAT'), 'post' => array('tanmenetId','tanarId','targyId'),
        'paramName'=>'tanmenetId');
    }
    getToolParameters();


?>
