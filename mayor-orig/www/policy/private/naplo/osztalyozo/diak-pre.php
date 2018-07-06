<?php
/*
    Module: naplo
*/
    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__TITKARSAG && !__DIAK) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

//	if ($_POST['tankor'] != '') {
//	    header('Location: '.location('index.php?page=naplo&sub=osztalyozo&f=tankor&tankor='.$_POST['tankor']));
//	}

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/osztalyzatok.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/dolgozat.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/targy.php');

/*
	require_once('include/modules/naplo/share/tankorok.php');
	require_once('include/modules/naplo/share/nevsor.php');
*/
	if(__DIAK) $diakId = __USERDIAKID;
	elseif (isset($_POST['diakId'])) $diakId = readVariable($_POST['diakId'],'id');
	elseif (isset($_GET['diakId']) ) $diakId = readVariable($_GET['diakId'],'id');

	$osztalyId = readVariable($_POST['osztalyId'],'numeric');

	if (isset($diakId)) {
	    $_POST['diakId'] = $diakId;
	    $ADAT['sulyozas'] = $sulyozas = readVariable(
		$_POST['sulyozas'],'regexp',
		readVariable($_SESSION['sulyozas'],'regexp',__DEFAULT_SULYOZAS,array('^[0-9]:[0-9]:[0-9]:[0-9]:[0-9]$')),
		array('^[0-9]:[0-9]:[0-9]:[0-9]:[0-9]$'));
	    $_SESSION['sulyozas'] = $sulyozas;
	    // nem használjuk fel (fejlesztéshez)
	    $targySorrend = readVariable($_POST['targySorrend'], 'emptystringnull', 'napló', getTargySorrendNevek(__TANEV));
	    $diakNev = getDiakNevById($diakId);
	    $Jegyek = getDiakJegyek($diakId, array('sulyozas' => $sulyozas));
	    $ADAT['diakTargy'] = getTargyakByDiakId($diakId, array('tanev' => __TANEV, 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'], 'result' => 'indexed', 'filter' => 'kovetelmeny', 'targySorrendNev' => $targySorrend)); // OK
            // Az aktuális szemeszter kiválasztása
            if (__FOLYO_TANEV) {
		$time = time();
		foreach ($_TANEV['szemeszter'] as $szemeszter => $szemeszterAdat) {
		    if ($time > strtotime($szemeszterAdat['kezdesDt']) && $time < strtotime($szemeszterAdat['zarasDt']))
			break;
		}
                if ($szemeszter !== false) {
                    define('__FOLYO_SZEMESZTER', $szemeszter);
                    $szemeszterKezdesDt = $szemeszterAdat['kezdesDt'];
                    $szemeszterZarasDt = $szemeszterAdat['zarasDt'];
                }
            }
            if (!defined('__FOLYO_SZEMESZTER')) define('__FOLYO_SZEMESZTER',false);

            if (isset($_POST['tolDt']) && $_POST['tolDt'] != '') $tolDt = readVariable($_POST['tolDt'], 'date');
//            elseif (isset($szemeszterKezdesDt)) $tolDt = $szemeszterKezdesDt;
            else $tolDt = $_TANEV['kezdesDt'];

            if (isset($_POST['igDt']) && $_POST['igDt'] != '') $igDt = readVariable($_POST['igDt'],'date');
            elseif (isset($szemeszterZarasDt)) $igDt = $szemeszterZarasDt;
            else $igDt = $_TANEV['zarasDt'];
	    $ADAT['zaroJegyek'] = getDiakZarojegyek($diakId,$_TANEV['tanev'],null,array('arraymap'=>array('diakId','targyId','tanev','szemeszter')));
	    $tmp = getTankorByDiakId($diakId);
	    $ADAT['targyTankor'] = reindex ( $tmp, array('targyId') );
	}
	if ($skin=='pda') {

	} else {
    	    if (!__DIAK) {
        	$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post'=>array('tolDt', 'igDt', 'targySorrend'));
            	$TOOL['diakSelect'] = array('tipus'=>'cella','paramName'=>'diakId', 'post'=>array('osztalyId','tolDt', 'igDt', 'targySorrend'));
            	$TOOL['targySorrendSelect'] = array('tipus'=>'cella','paramName'=>'targySorrend', 'post'=>array('diakId','osztalyId','tolDt', 'igDt', 'targySorrend'));
            	if (__NAPLOADMIN) $TOOL['diakSelect']['statusz'] = array('jogviszonyban van', 'magántanuló', 'jogviszonya felfüggesztve', 'jogviszonya lezárva', 'vendégtanuló');
            	$TOOL['diakLapozo'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array('osztalyId','tolDt', 'igDt', 'targySorrend'));
	    }
/*	    if (isset($diakId)) {
        	$TOOL['datumTolIgSelect'] = array('tipus' => 'sor', 'tolParamName' => 'tolDt', 'igParamName' => 'igDt',
		    'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'],
		    'hanyNaponta' => 'havonta', 'post'=>array('osztalyId', 'diakId', 'targySorrend'));
	    }
*/
	    if (isset($osztalyId)) {
        	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=osztalyozo&f=tankor'),
    		'titleConst' => array(''), 'post' => array('osztalyId'),
    		'paramName'=>'diakId');
	    }
	    getToolParameters();
	}

// tankorSelect 
// targySorrendSelecct
// diakSelect --> diakLapozo
	
    }

?>
