<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/szovegesErtekeles.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/szemeszter.php');
	require_once('include/modules/naplo/share/osztaly.php'); // getEvfolyamJelek

	$ADAT['szemeszterId'] = $szemeszterId = readVariable($_POST['szemeszterId'], 'numeric unsigned', getSzemeszterIdByDt(date('Y-m-d')));
	$ADAT['kepzesId'] = $kepzesId = readVariable($_POST['kepzesId'], 'numeric unsigned', null);
	$ADAT['targyId'] = $targyId = readVariable($_POST['targyId'], 'numeric unsigned', null);
	$ADAT['evfolyamJel'] = $evfolyamJel = readVariable($_POST['evfolyamJel'], 'string', null);
	$ADAT['szemeszter'] = getSzemeszterAdatById($szemeszterId);

	if (isset($evfolyamJel)) {
	    $ADAT['feltetel'] = 'eros';
	    $ADAT['szempontRendszer'] = getSzempontRendszer($ADAT);
	    $ADAT['szrId'] = $szrId = $ADAT['szempontRendszer']['szrId'];
	    if (isset($szrId) && $action == 'szempontRendszerTorles') {
		szempontRendszerTorles($ADAT);
		unset($ADAT['szempontRendszer']);
	    } elseif (!is_array($ADAT['szempontRendszer']) && $action == 'ujSzempontRendszer') {
		$ADAT['txt'] = explode("\n", $_POST['txt']);
		if (ujSzempontRendszer($ADAT)) {
		    $_SESSION['alert'][] = 'info:success';
		    $ADAT['szempontRendszer'] = getSzempontRendszer($ADAT);
		}
	    }
	}

	$ADAT['feltetel'] = 'laza';
	$ADAT['szempontRendszerek'] = getEvfolyamJelSzempontRendszerek($ADAT);

	$TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt','tervezett') , 'post' => array('kepzesId','evfolyamJel','targyId'));
        $TOOL['evfolyamJelSelect'] = array(
                'tipus' => 'cella', 'paramName' => 'evfolyamJel', 'paramDesc'=>'evfolyamJel','adatok' => getEvfolyamJelek(), 'post' => array('kepzesId','targyId')
        );

	$TOOL['targySelect'] = array('tipus'=>'cella', 'post' => array('kepzesId','evfolyamJel'));
	$TOOL['kepzesSelect'] = array('tipus'=>'cella', 'post' => array('evfolyamJel','targyId'));
	getToolParameters();

    }

?>
