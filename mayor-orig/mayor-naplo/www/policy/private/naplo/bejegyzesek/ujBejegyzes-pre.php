<?php
/*
    module:	naplo
*/

    if (_RIGHTS_OK !== true) die();

    if (!__TANAR && !__VEZETOSEG && !__NAPLOADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/bejegyzesModifier.php');
	require_once('include/modules/naplo/share/bejegyzes.php');

	$diakId = readVariable($_POST['diakId'], 'id');
	$osztalyId = readVariable($_POST['osztalyId'], 'id');


	if (isset($diakId)) {
    	    $DIAKOSZTALYAI = getDiakokOsztalyai(array($diakId));
    	    define(__OFO, ( is_array($_OSZTALYA) && count(array_intersect($DIAKOSZTALYAI[$diakId],$_OSZTALYA)) > 0 ));

	    $jogosult = array();
	    if (__TANAR) $jogosult[] = 'szaktanár';
	    if (__OFO) $jogosult[] = 'osztályfőnök';
	    if (__VEZETOSEG) $jogosult[] = 'vezetőség';
	    if (__NAPLOADMIN) $jogosult[] = 'admin';
	    $FOKOZATOK = getBejegyzesTipusokByJogosult($jogosult);

	    if ($action == 'ujBejegyzes') {

		$bejegyzesTipusId = readVariable($_POST['bejegyzesTipusId'], 'id');
		$szoveg = readVariable($_POST['szoveg'], 'string', '');
		$evvegi = readVariable($_POST['evvegi'], 'bool', false);
		if ($evvegi) {
		    // A tanév záró napjára dátumozott bejegyzések kerülnek a törzslapra
		    $referenciaDt = $_TANEV['zarasDt'];
		} else {
		    $referenciaDt = readVariable($_POST['referenciaDt'],'datetime','');
		}

		if (isset($bejegyzesTipusId) && $szoveg != '') {
		    if (ujBejegyzes($bejegyzesTipusId, $szoveg, $referenciaDt, $diakId)) $_SESSION['alert'][] = 'info:success:ujBejegyzes';
		} else { $_SESSION['alert'][] = 'message:wrong_data:ujBejegyzes:szöveg='.$szoveg.', bejegyzesTipusId='.$bejegyzesTipusId; }
	    }
	} // isset($diakId)

        $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post'=>array('tolDt', 'igDt', 'targySorrend'));
    	$TOOL['diakSelect'] = array('tipus'=>'cella','paramName'=>'diakId', 'post'=>array('osztalyId','tolDt', 'igDt', 'targySorrend'),
	                	    'statusz' => array('vendégtanuló','jogviszonyban van','magántanuló','egyéni munkarend')
	);
        if (isset($diakId))
            $TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'),
                'titleConst' => array('_BEJEGYZESEK'), 'post' => array('osztalyId'), 'paramName'=>'diakId');

        getToolParameters();

    }
?>
