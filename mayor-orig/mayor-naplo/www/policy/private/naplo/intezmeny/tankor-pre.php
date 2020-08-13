<?php

    if (_RIGHTS_OK !== true) die();
    
    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tankorModifier.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/helyettesitesModifier.php');
    require_once('include/modules/naplo/share/hianyzasModifier.php');
    require_once('include/modules/naplo/share/file.php');

    $ADAT['mkId'] = $mkId = readVariable($_POST['mkId'],'id',null);
    $ADAT['targyId'] = $targyId = readVariable($_POST['targyId'],'id',null);
    $ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'],'id',readVariable($_GET['tankorId'],'id',null));
    $ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'],'id',readVariable($_GET['tanarId'],'id',null));
    $_POST['tanev'] = $ADAT['tanev'] = $tanev = readVariable($_POST['tanev'],'numeric unsigned',__TANEV);

    if ($tanev!=__TANEV) $_SESSION['alert'][] = 'info:nem az alapértelmezett tanévben vagyunk!';

    $ADAT['tankor.kovetelmeny'] = getEnumField('naplo_intezmeny','tankor','kovetelmeny');
    if (defined('__AKG_TANKORNEV') && __AKG_TANKORNEV === true) {
	$ADAT['tankorNevMegorzes'] = $_POST['tankorNevMegorzes'] = readVariable($_POST['tankorNevMegorzes'],'bool',false);
    }
    /* */
    //$_TA = getTanevAdat($tanev);
    //$_TA['statusz'];

    // A tankörcsoportok felvétele az Elnevezés ($TANKOR_TIPUS) mezőben kiválasztható listába)
	$q = "select distinct csoportNev from csoport";
	$lr = db_connect('naplo');
	$r = db_query($q, array('fv'=>'AddCsoportToTankorTipus','v'=>array(),'result'=>'idonly'), $lr);
	db_close($lr);
	foreach ($r as $cs) {
	    if(strlen($cs) > 30) $cs = substr($cs, 0, 28).'...';
	    $TANKOR_TIPUS[$cs] = "($cs)"; 
	}
    // $TANKOR_TIPUS bővítés

    if (__NAPLOADMIN || __VEZETOSEG) {
	switch ($action) {
	    case 'ujTankor': // VAGY MÓDOSÍTÁS!!!
		if ($tanev != '') {
		    $ADAT['tankorId'] = $tankorId = ujTankor($_POST);
		    if (readVariable($_POST['forceTankorTipusValtas'],'id')==1) {
			$ADAT['tankorTipusId'] = readVariable($_POST['tankorTipusId'],'id');
			$erintettHianyzasDb = hianyzasTankorTipusValtas($ADAT['tankorId'],$ADAT['tankorTipusId'],array('tanev'=>$ADAT['tanev']));
			if ($erintettHianyzasDb>0) $_SESSION['alert'][] = 'info:db_hianyzas_tipus_modositas:'.$erintettHianyzasDb;
		    }
		    if ($tanev == __TANEV) {
			$csoportId = readVariable($_POST['csoportId'],'id');
			if ($csoportId>0 && $tankorId>0) {
			    addTankorToTankorCsoport($tankorId,$csoportId);
			}
		    }
		}
		break;
	    case 'tankorTargyModositas':
		$ADAT['targyIds'] = getTargyakByMkId($mkId, array('result'=>'idonly'));
		$ADAT['ujTargyId'] = readVariable($_POST['ujTargyId'], 'id', null, $ADAT['targyIds']);
		if (isset($ADAT['ujTargyId']) && isset($tankorId) && $targyId != $ADAT['ujTargyId']) {
		    if (tankorTargyModositas($ADAT)) $_SESSION['alert'][] = 'info:success:tankorTargyModositas';
		}
		break;
	    case 'tankorTorol':
		if ($tankorId != '') {
		    $biztosTorol = readVariable($_POST['biztosTorol'],'id',null);
		    if ($biztosTorol=='1') {
			tankorTorol($tankorId);
			unset($tankorId);
		    } else {
			$_SESSION['alert'][] = 'info:not_changed';
		    }
		}
		break;
	    case 'setTankorNev':
		if ($tankorId != '') {
		    $tagokAlapjan = readVariable($_POST['setTankorNevTagokAlapjan'],'bool',false);
		    if ($tagokAlapjan) {
			setTankorNevByDiakok($tankorId);
		    } else {
			setTankorNev($tankorId);
			$_SESSION['alert'][] = 'info:not_changed';
		    }
		}
		break;
	    case 'tankorLezar':
		if ($tankorId != '') {
		    $biztosTorol = readVariable($_POST['biztosLezar'],'id',null);
		    $lezarDt = readVariable($_POST['lezarDt'],'date',null);
		    if ($biztosTorol=='1' && !is_null($lezarDt)) {
			$lr = db_connect('naplo_intezmeny');
			db_start_trans($lr);

			$v = array($lezarDt, $tankorId);

			// tanár kiléptet
			$q = "UPDATE tankorTanar SET kiDt=('%s' - INTERVAL 1 DAY) WHERE tankorId=%u";
			$r[] = db_query($q, array('fv'=>'tankorTanarLezar', 'values'=>$v), $lr);
			// diákok kiléptet
			$q = "UPDATE tankorDiak SET kiDt=('%s' - INTERVAL 1 DAY) WHERE tankorId=%u";
			$r[] = db_query($q, array('fv'=>'tankorDiakLezar', 'values'=>$v), $lr);
			// tankör szemeszter kiléptet
			$q = "DELETE tankorSzemeszter.* FROM tankorSzemeszter LEFT JOIN szemeszter USING (tanev,szemeszter) WHERE kezdesDt>'%s' AND tankorId=%u";
			$r[] = db_query($q, array('fv'=>'tankorDiakLezar', 'values'=>$v), $lr);

//			$r[] = false;

			// órarendióra lezár (minden tanev adatbázisában, ami aktív
			$q = "SELECT distinct tanev FROM szemeszter WHERE statusz='aktív'";
			$_tanevek = db_query($q, array('fv'=>'tankorDiakLezar', 'result'=>'idonly'), $lr);
			for ($i=0; $i<count($_tanevek); $i++) {
			    $_tanev = $_tanevek[$i];
			    $_tanevDb = tanevDbNev(__INTEZMENY, $_tanev);

    			    // A lezárási dátum utáni bejegyzések törlése                                                                                                                                                      
    			    $q = "DELETE FROM $_tanevDb.orarendiOra WHERE tolDt >= '%s' AND (tanarId,osztalyJel,targyJel) IN (
                		SELECT tanarId,osztalyJel,targyJel FROM $_tanevDb.orarendiOraTankor WHERE tankorId = %u
            			)";
			    $v = array($lezarDt,$tankorId);
    			    $r[] = db_query($q, array('fv' => 'vegzosOrarendLezaras', 'values' => $v),$lr);
                                                                                                                                                                                                           
    			    // A lezárás dátuma után végződő bejegyzáések igDt-inek beállítása                                                                                                                                 
    			    $q = "UPDATE $_tanevDb.orarendiOra LEFT JOIN $_tanevDb.orarendiOraTankor USING (tanarId,osztalyJel,targyJel) SET igDt=('%s' - INTERVAL 1 DAY)                                                                          
				WHERE igDt > '%s' AND tankorId = %u";
			    $v = array($lezarDt,$lezarDt,$tankorId);
			    $r[] = db_query($q, array('fv' => 'vegzosOrarendLezarads', 'values' => $v),$lr);

			    // óra elmarad
			    $q = "SELECT oraId FROM $_tanevDb.ora WHERE tankorId=%u AND dt>='%s' AND tipus NOT IN ('elmarad','elmarad máskor')";
			    $v = array($tankorId,$lezarDt);
			    $_oraIdk = db_query($q, array('fv'=>'tankorDiakLezar', 'result'=>'idonly', 'values'=>$v), $lr);
			    for ($j=0; $j<count($_oraIdk); $j++) {
				$r[] = oraElmarad($_oraIdk[$j], $lr, $_tanev); 
			    }

			    // jegyek törlése
			    $q = "DELETE FROM $_tanevDb.jegy WHERE tankorId = %u AND dt>='%s'";
			    $v = array($tankorId,$lezarDt);
			    $r[] = db_query($q, array('fv'=>'tankorDiakLezar', 'values'=>$v), $lr);
			}

			if (in_array(false,$r)) db_rollback($lr);
			else 			db_commit($lr);

			db_close($lr);

			//unset($tankorId);
		    } else {
			$_SESSION['alert'][] = 'info:not_changed';
		    }
		}
		break;
	}
    }

    if (isset($tankorId) && $tankorId != '' && $tankorId !== false) {
	// force all variables to refresh!
	//$TANKORADAT = getTankorById($tankorId);
	$TANKORADAT = getTankorAdat($tankorId,$tanev);
	$TANKORADAT = $TANKORADAT[$tankorId];
	$ADAT['targyId'] = $targyId = $TANKORADAT[0]['targyId'];
	$TSZEMESZTEREK = getTankorSzemeszterei($tankorId);
	$TOSZTALYOK = getTankorOsztalyai($tankorId, array('result' => 'assoc'));
	$TARGYADAT = getTargyById($targyId);
	$ADAT['mkId'] = $mkId=$TARGYADAT['mkId'];	
    } elseif ($targyId != '') {
	$TARGYADAT = getTargyById($targyId);
	if ($mkId == '') $mkId=$TARGYADAT['mkId'];
    }

    if (defined('__TANEV')) $__TANEV = __TANEV; else $__TANEV = '';
    if (isset($targyId) && $targyId!='') {	
        $TANAROK = getTanarok(array('targyId' => $targyId, 'tanev' => $__TANEV));
    } 
    if (isset($mkId) && $mkId != '') {
	// csak konkrét tárgy esetén veszünk fel tankört...
        //$TANAROK = getTanarok(array('mkId' => $mkId, 'tanev' => $__TANEV)); 
	$MKADAT = getMunkakozossegById($mkId);
	$ADAT['targyak'] = getTargyak(array('mkId' => $mkId));
    } else {
	// csak konkrét tárgy esetén veszünk fel tankört...
	//$TANAROK = getTanarok();
    }

    $TOPOST['tanev'] = $tanev;
    $TOPOST['mkId'] = $mkId;
    $TOPOST['targyId'] = $targyId;
    $TOPOST['osztalyok'] = $TOSZTALYOK;
    $TOPOST['szemeszterek'] = $TSZEMESZTEREK;
    $TOPOST['tankorTipusId'] = $_POST['tankorTipusId'];
    $TOPOST['tankorId'] = $tankorId;
    $TOPOST['tankoradat'] = $TANKORADAT[0];
    if ($tankorId!='') {
	    $TOPOST['tankortanar'] = getTankorTanaraiByInterval($tankorId,array('tanev'=>$tanev,'result'=>'nevsor'));
	    $TOPOST['tankorcsoport']['idk'] = getTankorCsoportTankoreiByTankorId($tankorId);
	    $TOPOST['tankorcsoport']['adat'] = getTankorAdatByIds($TOPOST['tankorcsoport']['idk']);
    } elseif ($TOPOST['tankorTipusId']!='') {
	$TOPOST['tankoradat']['tankorTipusId'] = $TOPOST['tankorTipusId']; // hozzáírjuk ezt is
	$TOPOST['tankoradat']['kovetelmeny'] = $_POST['kovetelmeny']; // hozzáírjuk ezt is
	$TOPOST['tankoradat']['tipus'] = $_POST['tipus']; // hozzáírjuk ezt is
	$TOPOST['osztalyok'][0]['osztalyId'] = $_POST['osztalyId']; // hozzáírjuk ezt is
    }
    $OSZTALYOK = getOsztalyok($tanev);
    
    $ADAT['tankorTipusok'] = getTankorTipusok();
    $ADAT['tankorId'] = $tankorId;
    $ADAT['tanev'] = $tanev;
    $ADAT['tankorOsztalyok'] = getTankorOsztalyaiByTanev($tankorId, $tanev, array('tagokAlapjan'=>true,'result'=>'id'));
    $ADAT['tankorCsoportok'] = getTankorCsoport($tanev);
    $ADAT['tankorTankorCsoportjai'] = getTankorCsoportByTankorId($tankorId);

    if ($tanev != '') $SZEMESZTEREK = getSzemeszterek(array('filter' => array("tanev>=$tanev",'tanev<='.($tanev+7))));
    
    
    $TOOL['tanevSelect'] = array('tipus'=>'cella','paramName' => 'tanev', 
	'tervezett' => true,
	'post' => array('mkId','targyId','tankorId'));
    $TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post' => array('tanev'));
    $TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'targyak' => $ADAT['targyak'], 'post' => array('mkId', 'tanev'));
//    $TOOL['diakSelect'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array());
    $TOOL['tanarSelect'] = array('tipus'=>'sor','paramName'=>'tanarId', 'post'=>array('mkId','targyId','tanev'));
    $TOOL['tankorSelect'] = array('tipus' => 'cella','paramName' => 'tankorId', 'post' => array('tanev', 'mkId', 'targyId'));
    $TOOL['oldalFlipper'] = array('tipus' => 'cella', 
	'url' => array('index.php?page=naplo&sub=intezmeny&f=tankorDiak',
			'index.php?page=naplo&sub=intezmeny&f=tankorTanar'),
	'titleConst' => array('_TANKORDIAK'), 'post' => array('tanev'),
	'paramName'=>'tankorId');
    $TOOL['tanevLapozo'] = array('tipus' => 'sor', 'paramName' => 'tanev',
	'post' => array('mkId', 'targyId', 'tankorId'), 'tanev' => $tanev);
    getToolParameters();

?>
