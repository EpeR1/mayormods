<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__DIAK && !__TANAR) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorBlokk.php');
	require_once('include/modules/naplo/share/osztaly.php');

	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/tankorDiakModifier.php');
	require_once('include/share/date/names.php');

	if (__DIAK) $diakId = __USERDIAKID;
	else $diakId = readVariable($_POST['diakId'],'id');

	$ADAT['diakId'] = $diakId;

	global $_TANEV;

	if (isset($_POST['szemeszterId']) && $_POST['szemeszterId'] != '') 
	    $szemeszterId = readVariable($_POST['szemeszterId'],'id');
	else {
	    if (__FOLYO_TANEV) $_felev = getFelevByDt(date('Y-m-d')); else $_felev = count($_TANEV['szemeszter']);
	    $szemeszterId = getKovetkezoSzemeszterId($_TANEV['szemeszter'][$_felev]['tanev'],$_TANEV['szemeszter'][$_felev]['szemeszter'], true);
	    if (is_null($szemeszterId)) $szemeszterId = getSzemeszterIdByDt(date('Y-m-d'));
	}
	$ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	$ADAT['tanev'] = $tanev = $ADAT['szemeszterAdat']['tanev'];
	$ADAT['szemeszterId'] = $szemeszterId;

	//igaziból nem kéne blokkba szervezni... var_dump($ADAT['szemeszterAdat']['statusz']=='aktív');
	$refDt = ($ADAT['szemeszterAdat']['kezdesDt']);
	// csak a félévhez tartozó időszakok érnek, a tanév más szemesztereihez nem!
	$IDO = getIdoszakByTanev(array('tanev' => $tanev, 'szemeszter'=> $ADAT['szemeszterAdat']['szemeszter'],'tipus' => array('előzetes tárgyválasztás','tárgyválasztás'), 'tolDt' => '', 'igDt' => '', 'return' => 'assoc'));
	if (__VEZETOSEG===true || __NAPLOADMIN===true) {
	    define('__TARGYVALASZTAS',true); define('__MINCONTROL',false);	    
	} else {
	    if (is_array($IDO) && count($IDO)>0) {
	      foreach ( $IDO as $idoszakId => $IDATA ) {
		if ( strtotime($IDATA['előzetes tárgyválasztás'][0]['tolDt']) <= time() && time()<=strtotime($IDATA['előzetes tárgyválasztás'][0]['igDt'])) {
		    define('__TARGYVALASZTAS',true); define('__MINCONTROL',false); break;
		} elseif ( strtotime($IDATA['tárgyválasztás'][0]['tolDt']) <= time() && time()<=strtotime($IDATA['tárgyválasztás'][0]['igDt'])) {
		    define('__TARGYVALASZTAS',true); define('__MINCONTROL',true); break;	    
		} else {
		    // loop();
		}
	      }
	    }
	}
	if (!defined('__TARGYVALASZTAS'))  { define('__TARGYVALASZTAS',false); define('__MINCONTROL',false); }

	if (__TARGYVALASZTAS===true) {

	} else {
	    $_SESSION['alert'][] = 'info:nem_targyvalasztasi_idoszak:';
	}

	/* Képzésre vonatkozó beállítások */
	$ADAT['diakKepzes'] = getKepzesByDiakId($diakId, array('result'=>'assoc','dt'=>$refDt));
	for ($i=0; $i<count($ADAT['diakKepzes'][$diakId]); $i++) {
		$K = $ADAT['diakKepzes'][$diakId][$i];
                /* Évfolyam meghatározás ha lehet (adott tanév!) */
                $ADAT['diakOsztaly'] = getDiakokOsztalyai(array($diakId), array('tanev' => $tanev));
                for($j=0; $j<count($ADAT['diakOsztaly'][$diakId]); $j++) {
		    $ADAT['diakEvfolyam'][] = getEvfolyam($ADAT['diakOsztaly'][$diakId][$j], $tanev);
                }
		if (count($ADAT['diakEvfolyam'])>1) $_SESSION['alert'][] = ':multi_evfolyam:';
		$ADAT['kepzesOraszam'][$K['kepzesId']] = getOraszamByKepzes($K['kepzesId'],array('evfolyam'=>$ADAT['diakEvfolyam'][0], 'szemeszter'=>$ADAT['szemeszterAdat']['szemeszter']));
	}
	//var_dump($ADAT['kepzesOraszam']['kötelezően választható']['sum']);
	//var_dump($ADAT['kepzesOraszam']['szabadon választható']['sum']);
	/* Képzés vége */

	//define('__MODOSITHATO',(( (__NAPLOADMIN || __VEZETOSEG) && ($ADAT['szemeszterAdat']['statusz']=='aktív' || (__FOLYO_TANEV && $tanev==__TANEV)))));

	if ($action=='do' && __TARGYVALASZTAS===true && (__VEZETOSEG===true || __NAPLOADMIN===true || __DIAK===true)) {
	    foreach($_POST as $pNev => $pErtek) {
		if (substr($pNev,0,strlen("UJTANKORID")) == 'UJtankorId') {
		    $_D = array('tankorId'=>intval(substr($pNev,10)),'diakId'=>$diakId,'tolDt'=> $refDt);
		    tankorDiakFelvesz($_D);
		} elseif (substr($pNev,0,strlen("DELTANKORID")) == 'DELtankorId') {
		    $_D = array('tankorId'=>intval(substr($pNev,11)),'diakId'=>$diakId,'tolDt'=>$refDt,'MIN_CONTROL'=>__MINCONTROL);
		    tankorDiakTorol($_D);
		}
	    }
	} elseif ($action=='do') {
	    $_SESSION['alert'][] = 'info:deadline_expired:';
	}

	if ($diakId!='') {
	    $ADAT['osztalyok'] = $osztalyIdk = getDiakOsztalya($diakId, array('tanev'=>$tanev,'result'=>'csakid'));
	    $ADAT['tankorok']['diake'] = getTankorByDiakId($diakId,$tanev,array('tolDt'=>$refDt,'igDt'=>$refDt));
	}
	$ADAT['tankorBlokkok'] = getTankorBlokkok($tanev);
	if ($diakId==''  OR ($diakId!='' && is_array($ADAT['osztalyok']) && count($ADAT['osztalyok'])!=0))
	    $ADAT['tankorok']['valaszthato'] = getValaszthatoTankorok($tanev,$ADAT['szemeszterAdat']['szemeszter'],	$ADAT['osztalyok']);
	if (__DIAK!==true) $TOOL['diakSelect'] = array('tipus'=>'cella','paramName'=>'diakId', 'post'=>array('tanev','szemeszterId','refDt'));
	$TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName'=>'szemeszterId', 'post'=>array('diakId'),
    				           'tanev'=>$tanev, 'statusz'=>array('aktív'));
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=tankor'),
    	    'titleConst' => array('_TANKOR'), 'post' => array('tankorId','mkId','targyId'));

	getToolParameters();

    }

?>
