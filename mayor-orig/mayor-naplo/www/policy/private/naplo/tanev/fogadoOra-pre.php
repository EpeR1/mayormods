<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR && (!__DIAK || _POLICY != 'parent')) {
	$_SESSION['alert'][] = 'page:insufficient_acces';
    } else {
	
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/terem.php');
	require_once('include/modules/naplo/share/szulo.php');
	require_once('include/modules/naplo/share/szemeszter.php');

	$tanarId = readVariable(
	    $_POST['tanarId'], 'id', readVariable($_GET['tanarId'], 'id', ((__TANAR && !isset($_POST['tanarId']))?__USERTANARID:null))
	);

	if (__DIAK) {
	    require_once('include/modules/naplo/share/tankor.php');
	    $szuloId = getSzuloIdByUserAccount();
	    $szuloDiakjai = getSzuloDiakjai();
	    $diakIds = array();
	    for ($i = 0; $i < count($szuloDiakjai); $i++) $diakIds[] = $szuloDiakjai[$i]['diakId'];
	    $diakTankorIds = getTankorIdsByDiakIds($diakIds);
	    $diakTanarai = getTankorTanaraiByInterval($diakTankorIds);

	    $idoszak = getIdoszakByTanev(array('tanev' => __TANEV, null, 'tipus' => array('fogadóóra jelentkezés'), 'tolDt' => date('Y-m-d H:i:s'), 'igDt' => date('Y-m-d H:i:s'), 'return' => '', 'arraymap'=>null));
	    define('__FOGADOORA_JELENTKEZES',(is_array($idoszak) && count($idoszak)>0));
	}
	// ----------- action -------------- //
	if (__NAPLOADMIN || __VEZETOSEG) {
	    if ( strtotime($_POST['tolDt']) > 0) $_tolDt = $_POST['tolDt'];

	    if ($action == 'kovetkezoFogado') {
		$tol = readVariable($_tolDt.' '.$_POST['tolTime'].':00', 'datetime');
		$ig  = readVariable($_tolDt.' '.$_POST['igTime'].':00', 'datetime');
		if (isset($tol) && isset($ig)) kovetkezoFogadoOraInit($tol, $ig);
	    } elseif ($action == 'tanarFogado') {
		$tol = readVariable($_tolDt.' '.$_POST['tolTime'].':00', 'datetime');
		$ig  = readVariable($_tolDt.' '.$_POST['igTime'].':00', 'datetime');
		$teremId = readVariable($_POST['teremId'], 'id');
		if (tanarFogadoOra($tanarId, $tol, $ig, $teremId))
		    $_SESSION['alert'][] = 'info:success';
		
	    } elseif ($action == 'listaLekerdezese') {
		$Lista = getFogadoOraLista();
	    }
	}
	if (__DIAK && $action == 'fogadoOraJelentkezes') {
	    if (__FOGADOORA_JELENTKEZES === true) {
		$M = array();
		for ($i = 0; $i < count($diakTanarai); $i++) {
		    if (isset($_POST['jel'.$i])) {
			list($tId, $datetime) = explode('/',$_POST['jel'.$i]);
			$M[] = array('tanarId' => readVariable($tId, 'id'), 'datetime' => readVariable($datetime, 'datetime'));
		    }
		}
		fogadoOraJelentkezes($szuloId, $M);	    
	    } else {
		$_SESSION['alert'][] = 'message:deadline_expired';
	    }
	}
	// ----------- action vége -------------- //
	$FogadoDt = getKovetkezoFogadoDtk();
	$Tanarok = getTanarok(array('result' => 'assoc'));
	$Termek = getTermek();
	$TermekAsszoc = getTermek(array('result' => 'assoc', 'tipus' => array(), 'telephelyId' => null));
	$Szulok = getSzulok();

	if (isset($tanarId)) {
	    $tanarFogado = getTanarFogadoOra($tanarId);
	    $szuloIds = array();
	    foreach ($tanarFogado['jelentkezesek'] as $szId => $szAdat) $szuloIds[] = $szAdat['szuloId'];
	    if (count($szuloIds) > 0) $Szulok['diakjai'] = getSzulokDiakjai($szuloIds);
	} elseif (__NAPLOADMIN || __VEZETOSEG || __DIAK) {
	    if (count($FogadoDt['dates']) > 0) {
		$FogadoOsszes = getFogadoOsszes();
	    }
	    if (is_array($Lista)) {
		$szuloIds = array(2,3,4);
		reset($Lista);
		foreach ($Lista['jelentkezesek'] as $tanarId => $tAdat) {
		    foreach ($tAdat as $index => $A) {
			if (!in_array($A['szuloId'], $szuloIds) && $A['szuloId'] != '') $szuloIds[] = $A['szuloId'];
		    }
		}
		reset($Lista);
		if (count($szuloIds) > 0) $Szulok['diakjai'] = getSzulokDiakjai($szuloIds);
		unset($tanarId);
	    }
	}
	if (__DIAK) {
	    // Minden tanár csak egyszer szerepeljen!!
	    $tanarIds = $dTanarai = $tanarTerme = array();
	    for ($i = 0; $i < count($diakTanarai); $i++)
		if (!in_array($diakTanarai[$i]['tanarId'], $tanarIds)) {
		    $diakTanarai[$i]['foglalt'] = getTanarFogadoOra($diakTanarai[$i]['tanarId']);
		    $tanarIds[] = $diakTanarai[$i]['tanarId'];
		    $dTanarai[] = $diakTanarai[$i];
		    
		}
	    $diakTanarai = $dTanarai;
	    $Alkalmak = array('napok' => array());
	    for ($i = 0; $i < count($FogadoOsszes); $i++) {
		if (in_array($FogadoOsszes[$i]['tanarId'], $tanarIds)) {
		    $Alkalmak[$FogadoOsszes[$i]['tanarId']] = $FogadoOsszes[$i];
		    $Alkalmak['tanarTerme'][$FogadoOsszes[$i]['tanarId']] = $FogadoOsszes[$i]['teremId'];

		    $nap  = substr($FogadoOsszes[$i]['tol'], 0, 10);
		    if (!array_key_exists($nap, $Alkalmak['napok'])) {
			$Alkalmak['napok'][$nap] = array(
			    'tol' => substr($FogadoOsszes[$i]['tol'], 11, 5),
			    'ig' => substr($FogadoOsszes[$i]['ig'], 11, 5),
			);
		    } else {
			if ($Alkalmak['napok'][$nap]['tol'] > ($tol = substr($FogadoOsszes[$i]['tol'], 11, 5)))
			    $Alkalmak['napok'][$nap]['tol'] = $tol;
			if ($Alkalmak['napok'][$nap]['ig'] < ($ig = substr($FogadoOsszes[$i]['ig'], 11, 5)))
			    $Alkalmak['napok'][$nap]['ig'] = $ig;
		    }
		}
	    }
	    ksort($Alkalmak);
	    $szuloJelentkezes = getSzuloJelentkezes($szuloId);
	}

        if (count($FogadoDt['dates']) > 0) {
	    // Szülő fogadóóra naponként jelentkezhet
//	    if (__DIAK)
//    		$TOOL['datumSelect'] = array(
//        	    'tipus'=>'sor', 'post'=>array('tanarId', 'diakId', 'osztalyId', 'tankorId'),
//        	    'paramName' => 'igDt', 'hanyNaponta' => 7,
//        	    'tolDt' => date('Y-m-d', strtotime('Saturday', strtotime($_TANEV['kezdesDt']))),
//        	    'igDt' => $_TANEV['zarasDt'],
//    		);
	    // Aki a tanár fogadóóráit szeretné látni
	    if (__NAPLOADMIN || __VEZETOSEG || __TANAR || __TITKARSAG)
        	$TOOL['tanarSelect'] = array('tipus' => 'cella', 'post' => array('igDt'));
	}
	getToolParameters();
    }

?>
