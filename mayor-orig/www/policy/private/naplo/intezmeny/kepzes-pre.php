<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/osztaly.php');

	$tanev = __TANEV;

	$kepzesId = readVariable($_POST['kepzesId'], 'numeric unsigned', null);
	if (isset($kepzesId)) {
	    $ADAT['kepzesAdat'] = getKepzesAdatById($kepzesId);
	}
	$ADAT['osztalyok'] = getOsztalyok($tanev, array('result' => 'assoc', 'minden'=>true));
	$ADAT['osztalyJelleg'] = getOsztalyJellegek();

	if ($action == 'ujKepzes') {
	    $kepzesNev = readVariable($_POST['kepzesNev'], 'string', null);
	    $tolTanev = readVariable($_POST['tolTanev'], 'numeric unsigned', null);
	    $osztalyJellegId = readVariable($_POST['osztalyJellegId'], 'id', null);
	    if (isset($kepzesNev) && isset($tolTanev) && isset($osztalyJellegId)) $kepzesId = ujKepzes($kepzesNev, $tolTanev, $osztalyJellegId);
	    else $_SESSION['alert'][] = 'message:wrong_data';
	    if ($kepzesId) {
		$ADAT['kepzesAdat'] = getKepzesAdatById($kepzesId);
	    }
	} elseif ($action == 'kepzesModositas') {
	    $kepzesNev = readVariable($_POST['kepzesNev'], 'string', null);
	    $tolTanev = readVariable($_POST['tolTanev'], 'numeric unsigned', null);
	    $osztalyJellegId = readVariable($_POST['osztalyJellegId'], 'id', null);
	    $osztalyIds = readVariable($_POST['osztalyId'], 'id');
	    $delOsztalyIds = readVariable($_POST['delOsztalyId'], 'id');
	    if (isset($kepzesNev) && isset($tolTanev)) 
		$MODOSIT = array('kepzesId'=>$kepzesId, 
				 'kepzesNev'=>$kepzesNev,
				 'tolTanev'=>$tolTanev, 
				 'osztalyIds'=>$osztalyIds,
				 'delOsztalyIds'=>$delOsztalyIds,
				 'osztalyJellegId'=>$osztalyJellegId
		);
		if (isset($kepzesNev) && isset($tolTanev)) kepzesModositas($MODOSIT);
	    else $_SESSION['alert'][] = 'message:wrong_data';
	    $ADAT['kepzesAdat'] = getKepzesAdatById($kepzesId);
	} elseif ($action == 'kepzesEles') {
	    $kepzesEles = readVariable($_POST['kepzesEles'],'bool',null); // bool
	    kepzesEles($kepzesId,(($kepzesEles-1)*(-1)));
	    $ADAT['kepzesAdat'] = getKepzesAdatById($kepzesId);
	}

	$TOOL['kepzesSelect'] = array('tipus'=>'cella', 'post' => array());
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=osztaly','index.php?page=naplo&sub=intezmeny&f=kepzesOraterv'),
            				'titleConst' => array('_OSZTALYHOZ','_KEPZESORATERVHEZ'), 'post' => array('kepzesId'),
                        		'paramName'=>'kepzesId'); // paramName ?  
	getToolParameters();

    }

?>
