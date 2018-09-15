<?php
    global $page,$sub,$f;

    if (_RIGHTS_OK !== true) die();
    $tanev = __TANEV;
    if ($tanev<1997) return;

        require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/orarend.php');
	require_once('include/share/date/names.php');
        require_once('include/modules/naplo/share/terem.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/dolgozat.php');
	require_once('include/modules/naplo/share/jegyzet.php');
	require_once('include/modules/naplo/share/hianyzas.php');

    // -- TESZT - savePageState()
    define('__PAGE_PRESET', false); // kísérlet vége. no
    $preSetLoad = readVariable($_POST['preSetLoad'],'bool',false);
    $preSetSave = readVariable($_POST['preSetSave'],'bool',false);

    function getPSFvar($name) {
	global $PAGESTATE, $preSetSave, $preSetLoad;
	return (__PAGE_PRESET===TRUE && ($preSetLoad || $preSetSave)) ? readVariable($PAGESTATE[$name], 'id', null) : null;
	//return null;
    }
    // get toolbar state - később általánosul
    
    $TOOLBARVARS = array('targyId','tankorId','osztalyId','tanarId','diakId','teremId','mkId','het');
    $allowPreset=false;
    foreach ($TOOLBARVARS as $k) {if (isset($_POST[$k])||isset($_GET[$k])) $allowPreset = false;}
    if ($allowPreset) {
	$PAGESTATE = array();
	if (isset($_COOKIE[$page.'_'.$sub.'_'.$f])) 
	    $_explode = explode('+',$_COOKIE[$page.'_'.$sub.'_'.$f]);
	if (is_array($_explode)) {
	    foreach($_explode as $kvp) {
		list($key,$val) = explode('-',$kvp);
		if (in_array($key,$TOOLBARVARS) && isset($val) && intval($val)>0) $PAGESTATE[$key]=intval($val);
	    }
	}
    }
    
    // -- TESZT VÉGE
    $targyId = readVariable($_POST['targyId'], 'id', getPSFvar('targyId'));
    $tankorId = readVariable($_POST['tankorId'], 'id', readVariable($_GET['tankorId'],'id',getPSFvar('tankorId')));
    $osztalyId = readVariable($_POST['osztalyId'], 'id', readVariable($_GET['osztalyId'],'id',getPSFvar('osztalyId')));
    $tanarId = $_POST['tanarId'] = readVariable($_POST['tanarId'], 'id', readVariable($_GET['tanarId'],'id',getPSFvar('tanarId')));
    $diakId = $_POST['diakId'] = readVariable($_POST['diakId'], 'id', readVariable($_GET['diakId'],'id',getPSFvar('diakId')));
    $teremId = readVariable($_POST['teremId'], 'id', getPSFvar('teremId'));
    $mkId = readVariable($_POST['mkId'], 'id',getPSFvar('mkId'));
    $het = readVariable($_POST['het'], 'id',getPSFvar('het')); // ???

    $ADAT['telephelyek'] = getTelephelyek();
    $telephelyIds = array();
    foreach ($ADAT['telephelyek'] as $tAdat) $telephelyIds[] = $tAdat['telephelyId'];
    $telephelyId = readVariable($_POST['telephelyId'], 'id', (count($ADAT['telephelyek'])>1?null:1), $telephelyIds);
    /* A telephelyet ki tudnánk találni a lekérdezett órák termeiből is... */

    $tolDt = readVariable($_POST['tolDt'], 'date', getTanitasihetHetfo(array('napszam'=>0)));
    $dt = readVariable($_POST['dt'], 'date'); // mutatni

	if ($mkId=='' && $tanarId=='' && $diakId=='' && $osztalyId=='' && $tankorId=='' && $teremId=='') { // ez itt mind isnotset
	    if (__DIAK && defined('__USERDIAKID')) $diakId=__USERDIAKID;
	    if (__TANAR && defined('__USERTANARID')) $tanarId=__USERTANARID;
	}

    /* ----------------------------------------- */
    if (_POLICY=='private' && $action == 'setPublic' && is_numeric($diakId) && ((__NAGYKORU===true && $diakId==__USERDIAKID) || (__NAGYKORU === false && $diakId==__SZULODIAKID))) {
	require_once('include/modules/naplo/share/diakModifier.php');	
	diakAdatkezelesModositas(array('diakId'=>$diakId,'kulcs'=>'publikusOrarend','ertek'=>'1'));
    }
    /* ----------------------------------------- */

    /* Az órarendihét kiválasztása */
    if (isset($dt)) $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', strtotime($dt) )));
        if (!isset($tolDt))
	    // A következő nap előtti hétfő
	    $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', time())));


/*
	if (strtotime($tolDt) > strtotime($_TANEV['zarasDt'])) $_tolDt = $_TANEV['zarasDt'];
	elseif (strtotime($tolDt) < strtotime($_TANEV['kezdesDt'])) $_tolDt = $_TANEV['kezdesDt'];
	// és akkor korrigáljunk még egyszer
        if (isset($_tolDt)) // A következő nap előtti hétfő
	    $tolDt = date('Y-m-d', strtotime('last Monday', strtotime('+1 days', time())));
*/
	if ($tolDt != '') $het = getOrarendiHetByDt($tolDt);		
	if ($het == '') $het = getLastOrarend();
	$igDt = date('Y-m-d', mktime(0,0,0,date('m',strtotime($tolDt)), date('d',strtotime($tolDt))+6, date('Y',strtotime($tolDt))));

    // SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL
    if (MAYOR_SOCIAL === true && $action== 'orarendiOraTeremModosit') {
	$_MODIFY;
	if(is_array($_POST)) {
	    $TMP_TERMEK = getTermek(array('result' => 'assoc'));
	    $TMP_TEREMIDS = array_keys($TMP_TERMEK);
	    foreach($_POST as $_pk => $_pv) {
		if (($_pv>0 || $_pv=="teremTorol") && substr($_pk,0,3) == 'OOM') {
		    list($placeholder, $M['het'], $M['nap'], $M['ora'], $M['tanarId'],$M['tolDt']) = explode('+',$_pk);
		    if ($_pv=='teremTorol') $_pv=0; // hackit
		    $M['teremId'] = readVariable($_pv,'id',0,$TMP_TEREMIDS);
		    $M['tanev'] = __TANEV;
                    $teremModositasResult = teremModositas($M);
		}
	    }
	}	
    }
    // SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL SOCIAL

    $ADAT['termek'] = getTermek(array('result'=>'assoc','telephelyId'=>$telephelyId));
    $ADAT['tanarok'] = getTanarok(array('result'=>'assoc','telephelyId'=>$telephelyId));                                 //--TODO telephely
// =====================
    if ($tankorId!='') {
	$ADAT['orarend'] = getOrarendByTankorId($tankorId, array('tolDt'=>$tolDt,'igDt'=>$igDt));
	//$ADAT['toPrint'] = getTankorNev($tankorId); // vagy getTankornev külön?
	$TANKOROK['haladasi'] = array($tankorId);
	$ADAT['kivalasztott'] = array('tankor',$tankorId); 
    } elseif($tanarId!='') {
//	if (_POLICY == 'public') $_SESSION['alert'][] = 'info:adatkezeles:letilva';
//	else {
    	    $ADAT['orarend'] = getOrarendByTanarId($tanarId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephelyId'=>$telephelyId,'orarendiOraTankor'=>true));
	    $ADAT['toPrint'] = $ADAT['tanarok'][$tanarId]['tanarNev'];
//	}
	$ADAT['kivalasztott'] = array('tanar',$tanarId); 
    } elseif($diakId!='') {
	$ADAT['orarendTipus'] = 'diakOrarend';
	/* ide kerülhet, hogy a diák (__NAGYKORU)/szülő engedélyezte-e a saját/gyermeke órarendjének mutatását */
	$ADAT['adatKezeles'] = getDiakAdatkezeles($diakId,array('publikusOrarend'=>1));
	$ADAT['publikusOrarend'] = ($ADAT['adatKezeles']['publikusOrarend']['ertek'] == 1) ? true : false;

	// if (MAYOR_SOCIAL === true) $ADAT['publikusOrarend'] = true;

	/* Ha belül vagyunk, akkor állíthassa be egy gombnyomással, hogy ő bizony engedélyezi */
	define(__ALLOWSET, ((__NAGYKORU===true && $diakId==__USERDIAKID) || (__NAGYKORU === false && $diakId==__SZULODIAKID))); 
	if (_POLICY == 'public' && $ADAT['publikusOrarend'] === false) { 
	    $_SESSION['alert'][] = 'info:adatkezeles:'.'A keresési feltétel a felhasználó által belépés után a napló modulban engedélyezhető!';
	} else {
	    $ADAT['orarend'] = getOrarendByDiakId($diakId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'osztalyId'=>$osztalyId)); // itt az osztalyId-t nem lenne kötelező megadni, de a felületen úgysem lehet máshogy idejutni
	    $TANKOROK['haladasi'] = getTankorByDiakId($diakId, __TANEV, array('csakId' => true, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result'=>'idonly'));
	    $ADAT['diakFelmentes'] = getTankorDiakFelmentes($diakId, __TANEV, array('tolDt'=>$tolDt,'igDt'=>$igDt));
	}
	$ADAT['diakEvfolyamAdat'] = getEvfolyamAdatByDiakId($diakId,$tolDt,__TANEV);
	$ADAT['kepzes'] = getKepzesByDiakId($diakId, array('result' => '', 'dt' => $tolDt, 'arraymap' => null));
	if (count($ADAT['kepzes'])==1) {
	    $ADAT['kepzesOraterv'] = getOraszamByKepzes($ADAT['kepzes'][0]['kepzesId'], array('szemeszter'=>1,'evfolyam'=>$ADAT['diakEvfolyamAdat']['evfolyam']));
	} else {//nincs képzése, vagy több van
	}
	
	$ADAT['kivalasztott'] = array('diak',$diakId); 
    } elseif ($osztalyId!='') {
	$ADAT['orarendTipus'] = 'osztalyOrarend';
	$ADAT['orarend'] = getOrarendByOsztalyId($osztalyId,array('tolDt'=>$tolDt,'igDt'=>$igDt));
	$OADAT = getOsztalyAdat($osztalyId);
	$ADAT['toPrint'] = $OADAT['osztalyJel'];
	$TANKOROK['haladasi'] = getTankorByOsztalyId($osztalyId, __TANEV, array('csakId' => true, 'tanarral' => false, 'result' => 'idonly'));
	$ADAT['kivalasztott'] = array('osztaly',$osztalyId); 
    } elseif ($mkId!='') {
	$ADAT['orarend'] = getOrarendByMkId($mkId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephelyId'=>$telephelyId));
	$TANKOROK['haladasi'] = getTankorByMkId($mkId, __TANEV, array('csakId' => true,'filter' => array()) );
	$ADAT['kivalasztott'] = array('munkakozosseg',$mkId); 
    } elseif ($teremId!='') {
	$teremAdat = getTeremAdatById($teremId);
	$ADAT['orarend'] = getOrarendByTeremId($teremId,'',array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephelyId'=>$telephelyId));
	$ADAT['toPrint'] = $teremAdat['leiras'];
	$ADAT['kivalasztott'] = array('terem',$teremId); 
    }
        else $ADAT = array();
// -----------
	$TANKOROK['erintett'] = $ADAT['orarend']['tankorok']; // --FIXME (2013.05.03)
	$TANKOROK['mindenByDt'] = $ADAT['orarend']['mindenTankorByDt']; // --FIXME (2013.05.03)

    	$ADAT['NAPOK'] = $_NAPOK = _genNapok($tolDt,$igDt);

	if (isset($tanarId)) {
	    $ADAT['haladasi'] = getTanarOrak($tanarId,array('tolDt'=>$tolDt,'igDt'=>$igDt,'result'=>'likeOrarend'));
	} elseif ((is_array($TANKOROK['mindenByDt']) && count($TANKOROK['mindenByDt']>0)) || $teremId!='') {
	    // akkor egészítsük ki a haladási naplós órákkal
	    /* FS#100 */
	    $ADAT['haladasi'] = array('tankorok'=>array());
    	    // dátumfüggő FS#100
    	    for ($i=0; $i<count($_NAPOK); $i++) {
        	$_dt = $_NAPOK[$i];
		$_TK = $TANKOROK['mindenByDt'][$_dt];
		if ($teremId!='') 
		    $_D = getOrakByTeremId($teremId,array('tolDt'=>$_dt,'igDt'=>$_dt,'result'=>'likeOrarend'));
		else 
		    $_D = getOrak($TANKOROK['haladasi'],array('tolDt'=>$_dt,'igDt'=>$_dt,'result'=>'likeOrarend'));
		$ADAT['haladasi']['orak'][$_dt] = $_D['orak'][$_dt];
		if (is_array($_D['tankorok'])) $ADAT['haladasi']['tankorok'] = array_map('intval',array_unique(array_merge($_D['tankorok'],$ADAT['haladasi']['tankorok'])));
	    }
	}

	/* FIX */
	// a haladási naplóban olyan tankörök is szerepelnek, amik eddig nem...
	// először gyűjtsük ki a szükséges tanköröket, és utána kérdezzük le az adataikat egyben
	// ...

	if (!is_array($TANKOROK['haladasi'])) $TANKOROK['haladasi'] = array();
	if (is_array($ADAT['orarend']['tankorok']) && is_array($ADAT['haladasi']['tankorok'])) {
	    $TANKOROK = array_unique(array_map('intval',array_merge($ADAT['orarend']['tankorok'],$ADAT['haladasi']['tankorok'],$TANKOROK['haladasi'])));
	} elseif (is_array($ADAT['haladasi']['tankorok'])) {
	    $TANKOROK = $ADAT['haladasi']['tankorok'];
	} else {
	    $TANKOROK = $ADAT['orarend']['tankorok'];
	}	    
	if (count($TANKOROK)>0) $ADAT['tankorok'] = getTankorAdatByIds($TANKOROK);

	/* tankörlétszámok */
	if (is_array($ADAT['tankorok'])) foreach ($ADAT['tankorok'] as $_tankorId =>$_T) {
	    $ADAT['tankorLetszamok'][$_tankorId] = getTankorLetszam($_tankorId,array('refDt'=>$tolDt));
	}
	if (is_array($TANKOROK)) for ($i=0; $i<count($TANKOROK); $i++) {
	    $_tankorId = $TANKOROK[$i];
	    $ADAT['tankorLetszamok'][$_tankorId] = getTankorLetszam($_tankorId,array('refDt'=>$tolDt));
	}
	//--//
	$ADAT['dt'] = $dt; // show this... ha a skin ajax

	$ADAT['csengetesiRend'] = getCsengetesiRend();
	$ADAT['telephelyId'] = $telephelyId;
	$ADAT['napiMinOra'] = getMinOra();
	$ADAT['napiMaxOra'] = getMaxOra();
	$ADAT['hetiMaxNap'] = getMaxNap(array('haladasi'=>true,'tolDt'=>$tolDt,'igDt'=>$igDt));
	if ($ADAT['hetiMaxNap']<5) $ADAT['hetiMaxNap'] = 5;
	$ADAT['tankorTipus'] = getTankorTipusok();
	$ADAT['orakMost'] = getOrakMost();

	$_napok = getNapok(array('tolDt'=>$tolDt,'igDt'=>$igDt));
	for ($i=0; $i<count($_napok); $i++) {
	    $ADAT['napok'][($i+1)] = getNapAdat($_napok[$i]);
	}

        if (_POLICY!='public' && is_array($TANKOROK)) {
	    if (__JEGYZETSZEREPTIPUS == 'diak') {
    		$JA['tankorok'] = getTankorByDiakId(__JEGYZETSZEREPID);
    		$JA['osztalyok'] = getDiakOsztalya(__JEGYZETSZEREPID,array('tanev'=>$tanev,'tolDt'=>$dt,'igDt'=>$dt));
	    } elseif (__JEGYZETSZEREPTIPUS == 'tanar') {
    		$JA['tankorok'] = getTankorByTanarId(__JEGYZETSZEREPID);
		if (is_array($_OSZTALYA) && count($_OSZTALYA)>0) $JA['osztalyok'] = getOsztalyok(null,array('osztalyIds'=>$_OSZTALYA));
		$JA['munkakozossegek'] = getMunkakozossegByTanarId(__JEGYZETSZEREPID, array('idonly'=>false));
	    }
	    for ($i=0; $i<count($JA['tankorok']); $i++) {$JA['tankorIdk'][] = $JA['tankorok'][$i]['tankorId'];}
	    for ($i=0; $i<count($JA['osztalyok']); $i++) {$JA['osztalyIdk'][] = $JA['osztalyok'][$i]['osztalyId'];}
	    for ($i=0; $i<count($JA['munkakozossegek']); $i++) {$JA['mkIdk'][] = $JA['munkakozossegek'][$i]['mkId'];}

            $ADAT['dolgozat'] = getTankorDolgozatok($TANKOROK, TRUE, $_napok[0], $_napok[count($_napok)-1]);
	    $ADAT['jegyzet'] = getJegyzet(array('tolDt'=>$tolDt,'igDt'=>$igDt,'tankorIdk'=>$JA['tankorIdk'],'osztalyIdk'=>$JA['osztalyIdk']));

	    if (in_array(_POLICY,array('private','parent')) && (isset($diakId) && ((__DIAK===true && $diakId==_USERDIAKID) || __TANAR===true || __NAPLOADMIN===true ))) {
		$ADAT['hianyzas'] = getHianyzasByDiakIds(array($diakId), array('tolDt' => $tolDt, 'igDt' => $igDt, 'result' => 'multiassoc', 'keyfield'=>'oraId'));
	    }
        }

	// $ADAT['dt'] = $tolDt; // BUG - ez vajon miért volt???
	$ADAT['tanarId'] = $tanarId;
	$ADAT['osztalyId'] = $osztalyId;
	$ADAT['diakId'] = $diakId;
	$ADAT['tankorId'] = $tankorId;
	$ADAT['teremId'] = $teremId;
	if ($skin=='ajax' && $_REQUEST['httpResponse']=='json') $_JSON['orarend']=$ADAT;

//=====================================

        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('tanarId','osztalyId','tankorId','mkId','diakId','telephelyId'),
	    'paramName' => 'tolDt', 'hanyNaponta' => 7,
	    'override'=>true, // használathoz még át kell írni pár függvényt!!!
//	    'tolDt' => date('Y-m-d', strtotime('Monday', strtotime($_TANEV['kezdesDt']))),
	    'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
	    'igDt' => $_TANEV['zarasDt'],
	    'lapozo' => 0
	);
	$TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array('tolDt','mkId','tanarId'));
	//$TOOL['orarendiHetSelect'] = array('tipus'=>'cella' , 'paramName' => 'het', 'post'=>array('targyId','tankorId','osztalyId','tanarId'), 'disabled'=>true);
	if (($osztalyId!='' || $diakId!='') //&& _POLICY=='private'
	) {
	    $TOOL['diakSelect'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array('tolDt','osztalyId','telephelyId'));
	} else {
	    $TOOL['munkakozossegSelect'] = array('tipus'=>'sor', 'paramName'=>'mkId', 'post'=>array('tolDt','telephelyId'));
	}
	$TOOL['tanarSelect'] = array('tipus'=>'cella', 'paramName'=>'tanarId', 'post'=>array('tolDt','telephelyId'));
	$TOOL['osztalySelect']= array('tipus'=>'cella', 'paramName'=>'osztalyId', 'post'=>array('tolDt','telephelyId'));
	$TOOL['teremSelect'] = array('tipus'=>'cella', 'paramName'=>'teremId', 'telephelyId'=>$telephelyId, 'post'=>array('tolDt','telephelyId'));
        if ($osztalyId!='' || $tanarId!='' || $diakId!='' || $mkId!='' || $tankorId!='') 
	    $TOOL['tankorSelect'] = array('tipus'=>'sor','paramName'=>'tankorId', 'tolDt'=>$tolDt, 'igDt'=>$igDt, 
		'post'=>array('tolDt','osztalyId','targyId','tanarId','diakId','telephelyId'));

	$TOOL['general']['post'] = $TOOLBARVARS;
	getToolParameters();


//**************************************
    /* TESZT - savePageState() */
    if ($preSetSave || $preSetLoad) {
	$SAVESTATE = array();
	foreach($TOOLBARVARS as $key) {
	    if (isset($$key) && intval($$key)>0) {
		$SAVESTATE[] = $key.'-'.intval($$key);
		$stateCounter++;
	    }
	}
	if ($stateCounter>0) setcookie($page.'_'.$sub.'_'.$f, implode('+',$SAVESTATE), 0, '', '', TRUE, TRUE);
    }

?>