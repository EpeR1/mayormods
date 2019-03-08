<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = "page:insufficient_access";
    } else {

/*
    !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    BUG - zárás és írás időszak kezdődjön együtt!!!!!!!!!
    !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

*/

	require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/file.php');

	define('_TIME', time());
	$szemeszterId = readVariable($_POST['szemeszterId'], 'id', readVariable($_GET['szemeszterId'], 'id'));
        // melyik szemeszter adatait nézzük
//        if (isset($_POST['szemeszterId']) && $_POST['szemeszterId'] != '') {
//            $szemeszterId = $_POST['szemeszterId'];
//        } elseif (!isset($_POST['szemeszterId'])) {
	if (!isset($szemeszterId)) {
            for ($i = 1; $i <= count($_TANEV['szemeszter']); $i++) {
                if (
                    strtotime($_TANEV['szemeszter'][$i]['kezdesDt']) <= _TIME
                    && strtotime($_TANEV['szemeszter'][$i]['zarasDt']) >= _TIME
                ) {
                    $szemeszterId = $_POST['szemeszterId'] = $_TANEV['szemeszter'][$i]['szemeszterId'];
                    $tanev = $_TANEV['szemeszter'][$i]['tanev'];
                    $szemeszter = $_TANEV['szemeszter'][$i]['szemeszter'];
                    break;
                }
            }
	} else {
	    // szándékosan nincs szemeszter beállítva
	}
	if (isset($szemeszterId)) $ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	$ADAT['idoszakTipusok'] = getIdoszakTipusok();

	// -------- action --------- //
	if ($action != '') {
	    if ($action == 'idoszakModositas') {
		for ($i = 0; $i < count($_POST['idoszakId']); $i++) 
		    $Mod[ $_POST['idoszakId'][$i] ] = array('tolDt' => $_POST['tolDt'][$i], 'igDt' => $_POST['igDt'][$i]);
		for ($i = 0; $i < count($_POST['torlendo']); $i++) $Mod[ $_POST['torlendo'][$i] ]['torlendo'] = true;
		foreach ($ADAT['szemeszterAdat']['idoszak'] as $i => $iAdat) {
		    $iId = $iAdat['idoszakId'];
		    if ($Mod[$iId]['torlendo'] == true) {
//		    	echo 'Torol: '.$iId.'<hr>';
			idoszakTorles($iId);
		    } elseif (
			$iAdat['tolDt'] != $Mod[$iId]['tolDt']
			|| $iAdat['igDt'] != $Mod[$iId]['igDt']
		    ) {
//		    	echo '<br>'.$iId.' : '.$iAdat['tolDt'].' -- '.$_POST['tolDt'][$i].'<hr>';
			idoszakModositas($iId, $Mod[$iId]['tolDt'], $Mod[$iId]['igDt']);
		    }

		}
	    } elseif ($action == 'ujIdoszak') {
		ujIdoszak(
		    $_POST['tolDt'], $_POST['igDt'], $_POST['tipus'], 
		    $ADAT['szemeszterAdat']['tanev'], $ADAT['szemeszterAdat']['szemeszter'], $ADAT['idoszakTipusok']
		);
	    }
	    $ADAT['szemeszterAdat']['idoszak'] = getIdoszakByTanev(array('tanev' => $ADAT['szemeszterAdat']['tanev'], 'szemeszter' => $ADAT['szemeszterAdat']['szemeszter']));
	}

//        $TOOL['tanevSelect'] = array('tipus' => 'cella', 'action' => 'tanevValasztas', 'tervezett' => true, 'post' => array(), 'paramName'=>'tanev');
        $TOOL['szemeszterSelect'] = array('tipus' => 'cella', 'action' => 'szemeszterValasztas', 'post' => array(), 'paramName'=>'szemeszterId');
        getToolParameters();


    }


?>
