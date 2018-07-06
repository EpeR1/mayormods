<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/kepesites.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/tanar.php');

	$ADAT['targyak'] = getTargyak();
	foreach ($ADAT['targyak'] as $idx => $tAdat) $ADAT['targyIds'][] = $tAdat['targyId'];
	$ADAT['tanarok'] = getTanarok();
	foreach ($ADAT['tanarok'] as $idx => $tAdat) $ADAT['tanarIds'][] = $tAdat['tanarId'];
        $ADAT['vegzettsegek'] = getEnumField('naplo_intezmeny', 'kepesites', 'vegzettseg');
        $ADAT['fokozatok'] = getEnumField('naplo_intezmeny', 'kepesites', 'fokozat');
        $ADAT['specializaciok'] = getEnumField('naplo_intezmeny', 'kepesites', 'specializacio');
	foreach ($ADAT['kepesitesek'] as $idx => $kAdat) $ADAT['kepesitesIds'][] = $kAdat['kepesitesId'];

	$ADAT['kepesitesId'] = $kepesitesId = readVariable($_POST['kepesitesId'], 'id', null, $ADAT['kepesitesIds']);
	if (isset($kepesitesId)) {

	    if ($action == 'kepesitesModositas') {
		// Alap adatok módosítása
		$kepesitesNev = readVariable($_POST['kepesitesNev'], 'string');
		$vegzettseg = readVariable($_POST['vegzettseg'], 'enum', null, $ADAT['vegzettsegek']);
		$fokozat = readVariable($_POST['fokozat'], 'enum', null, $ADAT['fokozatok']);
		$specializacio = readVariable($_POST['specializacio'], 'enum', null, $ADAT['specializaciok']);
		if (isset($kepesitesNev) && isset($vegzettseg) && isset($fokozat) && isset($specializacio)) {
		    $ok = kepesitesModositas($kepesitesId, $vegzettseg, $fokozat, $specializacio, $kepesitesNev);
		    if ($ok) $_SESSION['alert'][] = 'info:success';
		}
		// Tárgy hozzárendelés
		$targyId = readVariable($_POST['targyId'], 'id', null, $ADAT['targyIds']);
		if (isset($targyId)) {
		    $ok = kepesitesTargyHozzarendeles($kepesitesId, $targyId);
		    if ($ok) $_SESSION['alert'][] = 'info:success';
		}
		// Tanár hozzárendelés
		$tanarId = readVariable($_POST['tanarId'], 'id', null, $ADAT['tanarIds']);
		if (isset($tanarId)) {
		    $ok = tanarKepesitesHozzarendeles($tanarId, $kepesitesId);
		    if ($ok) $_SESSION['alert'][] = 'info:success';
		}
	    } elseif ($action == 'delTargy') {

		$targyId = readVariable($_POST['targyId'], 'id', null, $ADAT['targyIds']);
		$ok = kepesitesTargyTorles($kepesitesId, $targyId);		
		$_JSON = array(
		    'result' => ($ok?'success':'fail'),
		    'targyId' => $targyId,
		    'kepesitesId' => $kepesitesId
		);

	    } elseif ($action == 'delTanar') {

		$tanarId = readVariable($_POST['tanarId'], 'id', null, $ADAT['tanarIds']);
		$ok = tanarKepesitesTorles($tanarId, $kepesitesId);		
		$_JSON = array(
		    'result' => ($ok?'success':'fail'),
		    'tanarId' => $tanarId,
		    'kepesitesId' => $kepesitesId
		);
		
	    }

	} elseif ($action == 'ujKepesites') {
		$kepesitesNev = readVariable($_POST['kepesitesNev'], 'string');
		$vegzettseg = readVariable($_POST['vegzettseg'], 'enum', null, $ADAT['vegzettsegek']);
		$fokozat = readVariable($_POST['fokozat'], 'enum', null, $ADAT['fokozatok']);
		$specializacio = readVariable($_POST['specializacio'], 'enum', null, $ADAT['specializaciok']);
		if (isset($kepesitesNev) && isset($vegzettseg) && isset($fokozat) && isset($specializacio)) {
		    $ADAT['kepesitesId'] = $kepesitesId = ujKepesites($vegzettseg, $fokozat, $specializacio, $kepesitesNev);
		    if ($kepesitesId) $_SESSION['alert'][] = 'info:success';
		}
	}


	$ADAT['kepesitesek'] = getKepesitesek();
	if (isset($kepesitesId)) {
	    $i=0;
	    while ($i < count($ADAT['kepesitesek']) && $ADAT['kepesitesek'][$i]['kepesitesId'] != $kepesitesId) $i++;
	    $ADAT['kepesitesAdat'] = $ADAT['kepesitesek'][$i];
	    $ADAT['kepesitesAdat']['targyak'] = getKepesitesTargy($kepesitesId);
	    $ADAT['kepesitesAdat']['tanarok'] = getKepesitesTanar($kepesitesId);
	}

	$TOOL['kepesitesSelect'] = array('tipus'=>'cella', 'paramName'=>'kepesitesId','paramDesc'=>'kepesitesNev','title'=>'KEPESITES','adatok'=>$ADAT['kepesitesek'],'post' => array());

    }
?>