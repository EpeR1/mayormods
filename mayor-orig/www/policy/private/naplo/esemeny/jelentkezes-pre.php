<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR && !__DIAK) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/esemeny.php');
	require_once('include/modules/naplo/share/osztaly.php');

	if (__DIAK) $ADAT['diakId'] = __USERDIAKID;
	else $ADAT['diakId'] = readVariable($_POST['diakId'], 'id');

	if (isset($ADAT['diakId'])) {
	    $ADAT['diakOsztaly'] = getDiakokOsztalyai(array($ADAT['diakId']));
	    $ADAT['esemenyek'] = getAktualisEsemenyByOsztaly($ADAT['diakOsztaly'][ $ADAT['diakId'] ]);
	    $ADAT['esemenyIds'] = array();
	    if (is_array($ADAT['esemenyek'])) foreach ($ADAT['esemenyek'] as $eAdat) $ADAT['esemenyIds'][] = $eAdat['esemenyId'];
	    $ADAT['valasztottEsemenyek'] = getValasztottEsemenyek($ADAT['diakId'], array('esemenyIds' => $ADAT['esemenyIds']));
	    $ADAT['jovahagyottEsemenyek'] = getJovahagyottEsemenyek($ADAT['diakId'], array('esemenyIds' => $ADAT['esemenyIds']));

	    if ($action == 'jelentkezes') {

		foreach ($_POST as $key => $val) {
		    if ($val == 'felvesz') {
			$ADAT['esemenyId'] = readVariable(substr($key,7), 'id');
			// TODO: ellenőrizzük, hogy az adott eseményre jelentkezhet-e a diák...
			esemenyJelentkezes($ADAT['diakId'], $ADAT['esemenyId']);
		    } elseif ($val == 'lead') {
			$ADAT['esemenyId'] = readVariable(substr($key,4), 'id');
			esemenyLeadas($ADAT['diakId'], $ADAT['esemenyId']);
		    }
		    // Választott események újraolvasása...
		    $ADAT['esemenyek'] = getAktualisEsemenyByOsztaly($ADAT['diakOsztaly'][ $ADAT['diakId'] ]);
		    $ADAT['valasztottEsemenyek'] = getValasztottEsemenyek($ADAT['diakId'], array('esemenyIds' => $ADAT['esemenyIds']));
		    $ADAT['jovahagyottEsemenyek'] = getJovahagyottEsemenyek($ADAT['diakId'], array('esemenyIds' => $ADAT['esemenyIds']));
		}

	    }

	}
    }

?>