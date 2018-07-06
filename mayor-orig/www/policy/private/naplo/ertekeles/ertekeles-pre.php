<?php

    if (_RIGHTS_OK !== true) die();

    $tanev = __TANEV;

    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/kerdoiv.php');

    if (_POLICY == 'parent') {
	$cimzettTipusok = array('szulo','tankorSzulo','osztalySzulo');
	$feladoTipus = 'szulo';
	$feladoId = __USERSZULOID;
	$diakId = __USERDIAKID;
	$cimzett['szulo'] = array(__USERSZULOID);
	$ret = getTankorByDiakId($diakId, $tanev);
	$cimzett['tankorSzulo'] = array(); $tankorAdat = array();
	for ($i = 0; $i < count($ret); $i++) {
	    if (!in_array($ret[$i]['tankorId'], $cimzett['tankorSzulo'])) {
		$cimzett['tankorSzulo'][] = $ret[$i]['tankorId'];
		$tankorAdat[ $ret[$i]['tankorId'] ] = $ret[$i];
	    }
	}
	$cimzett['osztaly'] = getDiakOsztalya($diakId, array('tanev' => $tanev, 'result' => 'csakid'));

    } elseif (__DIAK) {
	$cimzettTipusok = array('diak','tankor','osztaly');
	$feladoTipus = 'diak';
	$feladoId = $diakId = __USERDIAKID;
	$cimzett['diak'] = array(__USERDIAKID);
	$cimzett['tankor'] = array(0);
	$tankorAdat = array();
	$ret = getTankorByDiakId($diakId, $tanev);
	if (is_array($ret)) for ($i = 0; $i < count($ret); $i++) {
	    if (!in_array($ret[$i]['tankorId'], $cimzett['tankor'])) {
		$cimzett['tankor'][] = $ret[$i]['tankorId'];
		$tankorAdat[ $ret[$i]['tankorId'] ] = $ret[$i];
	    }
	}
	$cimzett['osztaly'] = getDiakOsztalya($diakId, array('tanev' => $tanev, 'result' => 'csakid'));
    } elseif (__TANAR) {
	$cimzettTipusok = array('tanar','munkakozosseg');
	$feladoTipus = 'tanar';
	$feladoId = __USERTANARID;
	$cimzett['tanar'] = array(__USERTANARID);
	$cimzett['munkakozosseg'] = getMunkakozossegByTanarId(__USERTANARID);
	// Munkaközösségek lekérdezése, címzett listába felvevése...
    }
    if (__NAPLOADMIN) {
	// Ez egy hack. Az admin kiválasztva egy diákon belebújhat annak a szerepébe...
	$diakId = readVariable($_POST['diakId'], 'id');
	if (isset($diakId)) {
	    $cimzett['diak'] = array($diakId);
	    $cimzett['tankor'] = array(0);
	    $tankorAdat = array();
	    $ret = getTankorByDiakId($diakId, $tanev);
	    if (is_array($ret)) for ($i = 0; $i < count($ret); $i++) {
		if (!in_array($ret[$i]['tankorId'], $cimzett['tankor'])) {
		    $cimzett['tankor'][] = $ret[$i]['tankorId'];
		    $tankorAdat[ $ret[$i]['tankorId'] ] = $ret[$i];
		}
	    }
	    $cimzett['osztaly'] = getDiakOsztalya($diakId, array('tanev' => $tanev, 'result' => 'csakid'));
	}

    }

    // A szóbajövő kérdőívek
    $Kerdoiv = getKerdoiv($cimzett);

	$kerdoivIds = array();
	for ($i = 0; $i < count($Kerdoiv); $i++) $kerdoivIds[] = $Kerdoiv[$i]['kerdoivId'];

	// Kérdőív kiválasztás
	$ADAT['kerdoivId'] = $kerdoivId = readVariable($_POST['kerdoivId'], 'numeric', null, $kerdoivIds);
	if (!isset($kerdoivId) && count($Kerdoiv) == 1) $kerdoivId = $Kerdoiv[0]['kerdoivId'];

	// Ha van kiválasztott kérdőív
	if (isset($kerdoivId)) {
	    $ADAT['kerdoivAdat'] = getKerdoivAdat($kerdoivId);
	    // mely címzettek közösek
	    if (is_array($cimzett)) foreach ($cimzett as $cimzettTipus => $cimzettIds) {
		if (is_array($cimzettIds) && count($cimzettIds) > 0) {
		    if (is_array($ADAT['kerdoivAdat']['cimzett'][$cimzettTipus])) {
			if (in_array(0, $ADAT['kerdoivAdat']['cimzett'][$cimzettTipus])) $cIds = $cimzettIds;
			else $cIds = array_intersect($cimzettIds, $ADAT['kerdoivAdat']['cimzett'][$cimzettTipus]);
			if (count($cIds) > 0) {
			    $kozosCimzett[$cimzettTipus] = array_values($cIds);
			    switch ($cimzettTipus) {
				case 'tankor':
				case 'tankorSzulo':
				    $Tankorok = array();
				    foreach ($cIds as $index => $tankorId) $Tankorok[] = $tankorAdat[ $tankorId ];
				    // ki van-e választva (tankorSelect) az értékelendő tankör
				    $ADAT['cimzettId'] = readVariable($_POST['tankorId'], 'numeric', null, $cIds);
				    if (isset($ADAT['cimzettId'])) {
					$ADAT['cimzettTipus'] = $cimzettTipus;
					$Nevek = array();
					foreach (getTankorTanaraiByInterval( $ADAT['cimzettId'] ) as $idx => $tAdat) $Nevek[] = $tAdat['tanarNev'];
					$ADAT['cimzettLeiras'] = $tankorAdat[ $ADAT['cimzettId'] ]['tankorNev'].' ('.implode(', ', $Nevek).')';
				    }
				    break;
				case 'osztaly':
				case 'osztalySzulo':
				    $Osztalyok = array();
				    foreach ($cIds as $index => $osztalyId) $Osztalyok[] = $osztalyAdat[ $osztalyId ]; // Még nincsenek osztályok lekérdezve!!
				    // ki van-e választva (osztalySelect) az értékelendő osztály
				    $ADAT['cimzettId'] = readVariable($_POST['osztalyId'], 'numeric', null, $cIds);
				    if (isset($ADAT['cimzettId'])) $ADAT['cimzettTipus'] = $cimzettTipus;
				    break;
				case 'munkakozosseg':
				    $Munkakozossegek = array();
				    foreach ($cIds as $index => $mkId) $Munkakozossegek[] = $mkAdat[ $mkId ]; // Még nincsenek munkaközösségek lekérdezve!!
				    // ki van-e választva (munkakozossegSelect) az értékelendő munkaközösség
				    $ADAT['cimzettId'] = readVariable($_POST['mkId'], 'numeric', null, $cIds);
				    if (isset($ADAT['cimzettId'])) $ADAT['cimzettTipus'] = $cimzettTipus;
				    break;
			    }
			}
		    }
		}
	    }
	    // Ha már a kérdőív kitöltése folyik - ismert a cimzettTipus és cimzettId
	    if (!isset($ADAT['cimzettId'])) {
		$ADAT['cimzettTipus'] = readVariable($_POST['cimzettTipus'], 'enum', null, $cimzettTipusok);
		if (isset($ADAT['cimzettTipus'])) 
		    $ADAT['cimzettId'] = readVariable($_POST['cimzettId'], 'numeric', null, $kozosCimzett[$ADAT['cimzettTipus']]);
	    }
	    // Ha egyik előző sem, de egyértelmű a címzett
	    if (!isset($ADAT['cimzettId']) && count($kozosCimzett) == 1) {
		list($cTipus, $cIds) = each($kozosCimzett);
		if (count($cIds) == 1) {
		    $ADAT['cimzettTipus'] = readVariable($cTipus, 'enum', null, $cimzettTipusok);
		    if (isset($ADAT['cimzettTipus']))
			$ADAT['cimzettId'] = readVariable($cIds[0], 'numeric', null, $kozosCimzett[$ADAT['cimzettTipus']]);
		}
	    }

	    unset($tankorId); unset($osztalyId); unset($mkId);
	    if (isset($ADAT['cimzettId'])) {
		switch ($ADAT['cimzettTipus']) {
		    case 'tankor':
		    case 'tankorSzulo': $tankorId = $ADAT['cimzettId']; break;
		    case 'osztaly':
		    case 'osztalySzulo': $osztalyId = $ADAT['cimzettId']; break;
		    case 'munkakozosseg': $mkId = $ADAT['cimzettId'];
		}
		if ($action == 'ertekeles') {
		    // A válaszok rögzítése...
		    $ADAT['megvalaszoltKerdes'] = getMegvalaszoltKerdes($kerdoivId, $feladoId, $feladoTipus, $ADAT['cimzettId'], $ADAT['cimzettTipus']);

		    // -------------------
		    $lr = db_connect('naplo');
		    db_start_trans($lr);

		    for ($i = 0; $i < count($ADAT['kerdoivAdat']['kerdes']); $i++) {
			$kerdesId = $ADAT['kerdoivAdat']['kerdes'][$i]['kerdesId'];
			$valaszId = readVariable($_POST['valasz'.$kerdesId], 'numeric unsigned', null);
			$szabadValasz = readVariable($_POST['szabadValasz'.$kerdesId], 'string', null);
			if (!in_array($kerdesId, $ADAT['megvalaszoltKerdes']) && (isset($valaszId) || $szabadValasz != '')) { // Ha még nem válaszolt a kérdésre, és most van válasz
			    $q = "INSERT INTO kerdoivMegvalaszoltKerdes (feladoId,feladoTipus,kerdesId,cimzettId,cimzettTipus) VALUES (%u, '%s', %u, %u, '%s')";
			    $v = array($feladoId, $feladoTipus, $kerdesId, $ADAT['cimzettId'], $ADAT['cimzettTipus']);
			    db_query($q, array('fv' => 'ertekeles', 'modul' => 'naplo', 'values' => $v), $lr);

			    // Kérdezzük le, hogy van-e kerdoivValaszSzam bejegyzés az adott válaszhoz, címzetthez
			    $q = "SELECT COUNT(*) FROM kerdoivValaszSzam WHERE valaszId = %u AND cimzettId=%u AND cimzettTipus='%s'";
			    $v = array($valaszId, $ADAT['cimzettId'], $ADAT['cimzettTipus']);
			    $db = db_query($q, array('fv' => 'ertekeles/volt-e már ez a válasz', 'result' => 'value', 'modul' => 'naplo', 'values' => $v), $lr);
			    if ($db == 0) {
				$q = "INSERT INTO kerdoivValaszSzam (valaszId, cimzettId, cimzettTipus, szavazat) VALUES (%u,%u,'%s',1)";
				$v = array($valaszId, $ADAT['cimzettId'], $ADAT['cimzettTipus']);
				db_query($q, array('fv' => 'ertekeles', 'modul' => 'naplo', 'values' => $v), $lr);
			    } else {
				$q = "UPDATE kerdoivValaszSzam SET szavazat=szavazat+1 WHERE valaszId=%u AND cimzettId=%u AND cimzettTipus='%s'";
				$v = array($valaszId, $ADAT['cimzettId'], $ADAT['cimzettTipus']);
				db_query($q, array('fv' => 'ertekeles', 'modul' => 'naplo', 'values' => $v), $lr);
			    }
			}
			if ($szabadValasz != '') {
			    $q = "INSERT INTO kerdoivSzabadValasz (kerdesId, szoveg) VALUES (%u, '%s')";
			    $v = array($kerdesId, $szabadValasz);
			    db_query($q, array('fv' => 'ertekeles/szabadValasz', 'modul' => 'naplo', 'values' => $v), $lr);
			}
		    }
		    db_commit($lr);
		    db_close($lr);
		    // -------------------

		}
		$ADAT['megvalaszoltKerdes'] = getMegvalaszoltKerdes($kerdoivId, $feladoId, $feladoTipus, $ADAT['cimzettId'], $ADAT['cimzettTipus']);
	    }
	}
    if (is_array($Kerdoiv))
	$TOOL['kerdoivSelect'] = array('tipus' => 'cella', 'kerdoiv' => $Kerdoiv, 'paramName' => 'kerdoivId', 'post' => array('diakId'));
    if (__NAPLOADMIN)
	$TOOL['diakSelect'] = array('tipus' => 'sor', 'paramName' => 'diakId', 'post' => array('kerdoivId','tankorId'));
    if (is_array($Tankorok)) 
	$TOOL['tankorSelect'] = array('tipus' => 'sor', 'tankorok' => $Tankorok, 'paramName' => 'tankorId', 'post' => array('kerdoivId','diakId'));

    getToolParameters();


?>
