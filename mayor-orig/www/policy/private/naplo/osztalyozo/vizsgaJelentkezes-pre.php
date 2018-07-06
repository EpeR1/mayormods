<?php

    if (_RIGHTS_OK !== true) die();

    if (
        !__NAPLOADMIN && !__VEZETOSEG
    ) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/vizsga.php');
	require_once('include/modules/naplo/share/szemeszter.php');

	$ADAT['szemeszterId'] = $szemeszterId = readVariable($_POST['szemeszterId'], 'id', getSzemeszterIdByDt(date('Y-m-d')));

	// Soron következő vizsgaidőszak lekérdezése
	$ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	$idoszakIds = $ADAT['vizsgaIdoszak'] = array();
	foreach ($ADAT['szemeszterAdat']['idoszak'] as $key => $iAdat) {
	    if ($iAdat['tipus'] == 'vizsga') {
		$ADAT['vizsgaIdoszak'][] = $iAdat;
		$idoszakIds[] = $iAdat['idoszakId'];
		if (!isset($ADAT['kovetkezoIdoszakIndex']) && time() < strtotime($iAdat['tolDt'])) $ADAT['kovetkezoIdoszakIndex'] = count($ADAT['vizsgaIdoszak']) - 1;
	    }
	}
	if (count($ADAT['vizsgaIdoszak']) == 0) {
	    $_SESSION['alert'][] = 'message:wrong_data:nincs vizsgaidőszak kijelölve ebben a szemeszterben';
	} else {
	
	} // van kijelölve vizsgaidőszak
	
        $TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'post' => array('sorrendNev', 'osztalyId'));
	getToolParameters();

    }

?>
