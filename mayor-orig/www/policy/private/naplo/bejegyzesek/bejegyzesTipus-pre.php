<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/share/date/names.php');
	require_once('include/modules/naplo/share/file.php');
	require_once('include/modules/naplo/share/bejegyzes.php');

	$dt = $ADAT['dt'] = readVariable($_POST['dt'], 'date', date('Y-m-d'));
	$ADAT['jogosult'] = getSetField('naplo_intezmeny','bejegyzesTipus','jogosult');
	$ADAT['bejegyzesTipusok'] = getBejegyzesTipusok($dt);

	if ($action == 'modositas') {

	    $M['btId'] = readVariable($_POST['bejegyzesTipusId'], 'id', null);
	    $M['btNev'] = readVariable($_POST['bejegyzesTipusNev'], 'string', '');
	    for ($i = 0; $i < count($M['btId']); $i++) {
		$btId = $M['btId'][$i];
		$B[$btId]['bejegyzesTipusNev'] = $M['btNev'][$i];
		
	    }
	    if (is_array($_POST['jogosult'])) foreach ($_POST['jogosult'] as $value) {
		list($id, $jogosult) = explode('-', $value);
		if (in_array($jogosult, $ADAT['jogosult'])) $B[$id]['jogosult'][] = $jogosult;
	    }
	    if (is_array($_POST['hianyzasDb'])) foreach ($_POST['hianyzasDb'] as $value) {
		list($id, $hianyzasDb) = explode('-', $value);
		$B[$id]['hianyzasDb'] = $hianyzasDb;
	    }
	    // Végigmenve a jelenlegi bejegyzesTipus-okon - van-e változás?
	    foreach ($ADAT['bejegyzesTipusok'] as $tipus => $tAdat) {
		foreach ($tAdat as $btAdat) {
		    $mdb = 0; // módosítandó mezők száma
		    $btId = $btAdat['bejegyzesTipusId'];
		    if ($btAdat['bejegyzesTipusNev'] != $B[$btId]['bejegyzesTipusNev']) $mdb++;
		    if ($btAdat['tipus'] == 'fegyelmi' && intval($btAdat['hianyzasDb']) != $B[$btId]['hianyzasDb']) $mdb++;
		    if (explode(',',$btAdat['jogosult']) != $B[$btId]['jogosult']) {
			if ($mdb == 0) {
			    jogosultValtoztatas($btId, $B[$btId]['jogosult']);
			    $mdb = 0;
			}
		    }
		    if ($mdb > 0) bejegyzesTipusModositas($btId, $B[$btId], $dt);
		}
		// Fokozat-törlés - új fokozat
		if (isset($_POST['del-'.ekezettelen($tipus)])) fokozatTorles($tipus, $dt);
		elseif (isset($_POST['new-'.ekezettelen($tipus)])) ujFokozat($tipus, $dt);
	    }
	    $ADAT['bejegyzesTipusok'] = getBejegyzesTipusok($dt);
	}

	$TOOL['datumSelect'] = array('tipus'=>'cella','paramName' => 'dt', 'post'=>array());
	getToolParameters();
    }
?>
