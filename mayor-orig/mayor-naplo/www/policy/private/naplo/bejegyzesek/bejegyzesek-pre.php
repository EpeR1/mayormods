<?php
/*
    module:	naplo
*/

    if (_RIGHTS_OK !== true) die();

    if (!__DIAK && !__TANAR && !__VEZETOSEG && !__NAPLOADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/ertekeles.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/diak.php');
	require_once('include/modules/naplo/share/tanar.php');

	$tanarId = readVariable($_POST['tanarId'], 'id', null);

	$Osztalyok = getOsztalyok(__TANEV);
	$osztalyIds = array(); for ($i = 0; $i < count($Osztalyok); $i++) $osztalyIds[] = $Osztalyok[$i]['osztalyId'];
	if (!$tanarId)
	    $osztalyId = readVariable($_POST['osztalyId'], 'id', null, $osztalyIds);

	// TODO!!!

	$DIAKOK = getDiakok(array('tanev' => __TANEV, 'osztalyId' => $osztalyId, 'result'=>'assoc'));
	$Diakok = getDiakok(array('tanev' => __TANEV, 'osztalyId' => $osztalyId));
	$diakIds = array(); for ($i = 0; $i < count($Diakok); $i++) $diakIds[] = $Diakok[$i]['diakId'];
	if (!$tanarId)
	    $diakId = readVariable($_POST['diakId'], 'numeric unsigned', null, $diakIds);

	if (__DIAK) $diakId = __USERDIAKID;

	if ($action === 'delBejegyzes') {

	    $bejegyzesId = readVariable($_POST['bejegyzesId'], 'numeric unsigned', 0);
	    $bejegyzesAdat = getBejegyzesAdatById($bejegyzesId);
	    if ( // Admin bármikor, tanár a beírás napján törölhet
		__NAPLOADMIN ||
		($bejegyzesAdat['tanarId'] = __USERTANARID && $bejegyzesAdat['beirasDt'] == date('Y-m-d'))
	    ) {
		delBejegyzes($bejegyzesId);
	    }

	}

	if (isset($diakId)) $BEJEGYZESEK = getBejegyzesLista($diakId);
	elseif (isset($osztalyId)) $osztalyBejegyzesek = getBejegyzesekByDiakIds($diakIds);
	elseif (isset($tanarId)) $BEJEGYZESEK = getBejegyzesekByTanarId($tanarId);

	if (__TANAR || __VEZETOSEG || __NAPLOADMIN) {
            $TOOL['tanarSelect'] = array(
		'tipus' => 'cella', 'paramName' => 'tanarId', 'post'=>array('osztalyId','tolDt', 'igDt', 'targySorrend')
	    );

	    $TOOL['osztalySelect'] = array(
		'tipus' => 'cella', 'paramName' => 'osztalyId', 'osztalyok' => $Osztalyok, 'post' => array('tolDt', 'igDt', 'targySorrend')
	    );
            $TOOL['diakSelect'] = array(
		'tipus' => 'cella', 'paramName' => 'diakId', 
//		'diakok' => $Diakok, 
		'post'=>array('osztalyId','tolDt', 'igDt', 'targySorrend'),
		'statusz' => array('vendégtanuló','jogviszonyban van','magántanuló','egyéni munkarend','jogviszonya lezárva','jogviszonya felfüggesztve')		
	    );
	    if (isset($diakId))
		$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=bejegyzesek&f=ujBejegyzes'),
    		    'titleConst' => array('_UJBEJEGYZES'), 'post' => array('osztalyId'), 'paramName'=>'diakId');

	}
	getToolParameters();

    }
?>
