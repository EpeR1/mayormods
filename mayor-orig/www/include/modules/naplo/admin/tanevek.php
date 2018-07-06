<?php

    function updateNaploSession($sessionID,$rovidnev='',$tanev=__TANEV) {
        if ($tanev) {
            $q = "UPDATE session SET intezmeny='%s',tanev=%u WHERE sessionID='%s'";
            $v = array($rovidnev,$tanev,$sessionID);
        } else {
            $q = "UPDATE session SET intezmeny='%s' WHERE sessionID='%s'";
            $v = array($rovidnev,$sessionID);
        }
        $r = db_query($q, array('fv' => 'updateNaploSession', 'modul' => 'naplo_base', 'values' => $v));
    }

    function szemeszterBejegyzes($szemeszterObj) {

	global $mayorCache;
	$mayorCache->delType('szemeszter');

	$tanev = $szemeszterObj['tanev'];
	$szemeszter = $szemeszterObj['szemeszter'];	
	$statusz = $szemeszterObj['statusz'];	
	$kDt = $szemeszterObj['kezdesDt'];	
	$zDt = $szemeszterObj['zarasDt'];	

	if ($tanev != '' && $szemeszter != '') {
	    $lr = db_connect('naplo_intezmeny', array('fv' => 'szemeszterBejegyzes'));

	    $q = "SELECT COUNT(*) FROM szemeszter WHERE szemeszter=%u AND tanev=%u";
	    $v = array($szemeszter, $tanev);
	    $num = db_query($q, array('fv' => 'szemeszterBejegyzes', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v), $lr);
	    if ($num == 0) {

		$q = "INSERT INTO szemeszter (tanev,szemeszter, statusz, kezdesDt, zarasDt)
			    VALUES (%u, %u, '%s', '%s', '%s')";
		$v = array($tanev, $szemeszter, $statusz, $kDt, $zDt);
		$r = db_query($q, array('fv' => 'szemeszterBejegyzes', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);

	    } else {
		$_SESSION['alert'][] = 'message:letezo_szemeszter:'."$tanev:$szemeszter";
	    }
	    db_close($lr);
	}
    }

    function szemeszterTorles($szemeszterId) {

	global $mayorCache;
	$mayorCache->delType('szemeszter');
    	
        $lr = db_connect('naplo_intezmeny', array('fv' => 'szemeszterTorles'));
        if (!$lr) return false;

            $q = 'DELETE FROM szemeszter WHERE szemeszterId IN ('.implode(',', array_fill(0, count($szemeszterId), '%u')).')';
	    $r = db_query($q, array('fv' => 'szemeszterTorles', 'modul' => 'naplo_intezmeny', 'values' => $szemeszterId), $lr);

        db_close($lr);
	return $r;

    }


    function activateTanev($tanev) {
	setTanevStatus($tanev,'aktív');
    }

    function closeTanev($ADAT) {

	global $ZaradekIndex;
	global $mayorCache;
	$mayorCache->delType('szemeszter');

	$tanev = $ADAT['tanev'];

	if (strtotime($ADAT['tanevAdat']['zarasDt']) >= time()) {
	    $_SESSION['alert'][] = "message:wrong_data:A tanév még nem ért véget!:$tanev tanév vége ".$ADAT['tanevAdat']['zarasDt'];
	    return false;
	}
	if (strtotime($ADAT['tanevAdat']['zarasDt']) >= strtotime($ADAT['dt'])) {
	    $_SESSION['alert'][] = "message:wrong_data:A tanév csak az utolsó tanítási nap utáni hatállyal zárható le!:$tanev tanév vége ".$ADAT['tanevAdat']['zarasDt'].': zárás dátuma '.$ADAT['dt'];
	    return false;
	}
	$Szemeszter = $ADAT['tanevAdat']['szemeszter'];
	$vDiakok = getVegzoDiakok(array('tanev' => $tanev));
	// A függvénynek nincs statusz paramétere // $vDiakok = getVegzoDiakok(array('tanev' => $tanev, 'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve')));
	//vegzoOsztalyok, vjlOsztalyok, vatOsztalyok
	
	if (in_array('vegzosJogviszonyLezaras', $ADAT['step'])) {

	    // Azoknak a jogviszonyát kell csak lezárni, akik csak végzős osztálynak tagjai - és az osztályaik meg vannak jelölve  (diak tábla)  
	    $oDiakok = getDiakokByOsztalyId($ADAT['vjlOsztaly'], array('tanev' => $tanev, 'result' => '', 'statusz' => array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve')));
	    $vjlDiakIds = array();
	    for ($i = 0; $i < count($oDiakok); $i++) {
		$diakId = $oDiakok[$i]['diakId'];
		if (!in_array($diakId, $vjlDiakIds)) { // Ha még nem választottuk ki (jöhet többször, mert lehet egy diák több osztályban)
		    if (in_array($diakId, $vDiakok)) {
			$vjlDiakIds[] = $diakId;
			// A jogviszony lezárás egyúttal:
			// - tankörökből való kiléptetés
			// - osztályokból való kiléptetés
			$D = array(
			    'diakId' => $diakId, 'jogviszonyValtasDt' => $ADAT['dt'], 'ujStatusz' => 'jogviszonya lezárva', 'tanev' => $tanev, 
			    'lezarasZaradekIndex' => $ZaradekIndex['jogviszony']['lezárás']['tanulmányait befejezte']
			);
			diakJogviszonyValtas($D);
		    } else {
			$_SESSION['alert'][] = "info:wrong_data:Nem végzős:$diakId (jogviszonyát nem zárjuk le)";
		    }
		}
	    }
//	    if (count($vjlDiakIds) > 0) diakJogviszonyLezaras($vjlDiakIds, $ADAT['dt'], $olr = '');

	}

	if (in_array('vegzosOsztalyokLezarasa', $ADAT['step'])) {
	    // A megjelölt végzős osztályokból kiléptetjük a diákokat (osztaly-Diak tábla)
	    $osztalyIds = array();
	    for ($i = 0; $i < count($ADAT['vegzoOsztalyok']); $i++) $osztalyIds[] = $ADAT['vegzoOsztalyok'][$i]['osztalyId'];
	    osztalyLezaras($osztalyIds, $ADAT['dt']);
	}
	if (in_array('vegzosAzonositokTorlese', $ADAT['step'])) {

	    require_once('include/modules/session/search/searchAccount.php');
	    // Végzősök azonosítóinak törlése (mayor_private.accounts - lezárt jogviszonyúak)

	    $q = "SELECT oId FROM diak WHERE statusz='jogviszonya lezárva' AND oId IS NOT NULL";
	    $oIds = db_query($q, array('fv' => 'closeTanev/azonosítók lekérdezése', 'modul' => 'naplo_intezmeny', 'result' => 'idonly'));
	    foreach ($oIds as $index => $oId) {
		$ret = searchAccount('studyId', $oId, array('userAccount'), 'private');
		if ($ret['count'] == 1) deleteAccount($ret[0]['userAccount'][0], 'private');
		else $_SESSION['alert'][] = "info:wrong_data:nincs diák account:oId=$oId"; 
	    }
	}

	if (in_array('vegzosSzuloAzonositokTorlese', $ADAT['step'])) {

	    require_once('include/modules/session/search/searchAccount.php');
	    // Végzősök szülői azonosítóinak törlése (mayor_parent.accounts - pontosabban: lezárt jogviszonyúak userAccount=NULL)
	    $q = "SELECT szulo.userAccount
		    FROM diak LEFT JOIN szulo ON szuloId IN (apaId,anyaId,gondviseloId) 
		    WHERE szulo.userAccount IS NOT NULL GROUP BY szulo.userAccount 
		    HAVING SUM(IF(diak.statusz IN ('jogviszonyban van','magántanuló','vendégtanuló','jogviszonya felfüggesztve'), 1, 0)) = 0 
		    AND SUM(IF(diak.statusz IN ('jogviszonya lezárva','felvételt nyert'),1,0)) > 0";

	    $userAccounts = db_query($q, array('fv' => 'closeTanev/azonosítók lekérdezése', 'modul' => 'naplo_intezmeny', 'result' => 'idonly'));
	    foreach ($userAccounts as $index => $userAccount) {
		$ret = searchAccount('userAccount', $userAccount, array('userAccount'), 'parent');
		if ($ret['count'] == 1) 
		    deleteAccount($ret[0]['userAccount'][0], 'parent');
		else 
		    $_SESSION['alert'][] = "message:wrong_data:nincs szülő account:userAccount=$userAccount";
		
		$q = "UPDATE szulo SET userAccount=NULL WHERE userAccount IN ('".implode("','", array_fill(0, count($userAccounts), '%s'))."')";
		
		db_query($q, array('fv' => 'closeTanev', 'modul' => 'naplo_intezmeny', 'values' => $userAccounts));
	    }

	}

	if (in_array('tanevLezaras', $ADAT['step'])) {

	    // A tanév lezárása
	    setTanevStatus($tanev,'lezárt');

	    $Wnemszamit = defWnemszamit();
	    // A tanévhez tartozó hiányzási adatok lekérdezése és rögzítése
	    $tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	    foreach ($Szemeszter as $i => $szAdat) {
		if ($szAdat['statusz'] == 'aktív') { // tervezett és lezárt szemeszter nem zárható le...
		    // replace - ha megnyitunk és újra lezárunk egy tanévet...
		    $q = "REPLACE INTO ".__INTEZMENYDBNEV.".hianyzasOsszesites
			    SELECT diakId, %u AS tanev, %u AS szemeszter,
				COUNT(IF(tipus='hianyzas' AND statusz='igazolt',1,NULL)) AS igazolt,
				COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan',1,NULL)) AS igazolatlan,
				SUM(IF(tipus='késés' AND statusz='igazolatlan',perc,NULL)) AS kesesPercOsszeg,

				COUNT(IF(tipus='hianyzas' AND statusz='igazolt' AND tankorTipus.jelleg='gyakorlat',1,NULL)) AS gyakorlatIgazolt,
				COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan' AND tankorTipus.jelleg='gyakorlat',1,NULL)) AS gyakorlatIgazolatlan,
				SUM(IF(tipus='késés' AND statusz='igazolatlan' AND tankorTipus.jelleg='gyakorlat',perc,NULL)) AS gyakorlatKesesPercOsszeg,

				COUNT(IF(tipus='hianyzas' AND statusz='igazolt' AND tankorTipus.jelleg='elmélet',1,NULL)) AS elmeletIgazolt,
				COUNT(IF(tipus='hianyzas' AND statusz='igazolatlan' AND tankorTipus.jelleg='elmélet',1,NULL)) AS elmeletIgazolatlan,
				SUM(IF(tipus='késés' AND statusz='igazolatlan' AND tankorTipus.jelleg='elmélet',perc,NULL)) AS elmeletKesesPercOsszeg

			    FROM `%s`.hianyzas ".$Wnemszamit['join']."
			    WHERE (
				tipus = 'hiányzás'
				OR (tipus='késés' AND statusz='igazolatlan' AND perc IS NOT NULL)
			    ) AND dt<='%s'
			    ".$Wnemszamit['nemszamit']."
			    GROUP BY diakId";
		    $v = array($tanev, $szAdat['szemeszter'], $tanevDb, $szAdat['zarasDt']);
		    $r = db_query($q, array('fv' => 'closeTanev/hianyzasOsszesites', 'modul' => 'naplo_intezmeny', 'values' => $v));
		    // A hozott hiányzások hozzáadása
		    $q = "UPDATE ".__INTEZMENYDBNEV.".hianyzasOsszesites SET 
			    igazolt = igazolt + (
				SELECT IFNULL(SUM(dbHianyzas),0) FROM `%s`.hianyzasHozott AS `hh` 
				WHERE hh.diakId = hianyzasOsszesites.diakId AND hh.statusz='igazolt' AND hh.dt<='%s'
			    ),
			    igazolatlan = igazolatlan + (
				SELECT IFNULL(SUM(dbHianyzas),0) FROM `%s`.hianyzasHozott AS `hh` 
				WHERE hh.diakId = hianyzasOsszesites.diakId AND hh.statusz='igazolatlan' AND hh.dt<='%s'
			    )
			WHERE tanev=%u AND szemeszter=%u";
		    $v = array($tanevDb, $szAdat['zarasDt'], $tanevDb, $szAdat['zarasDt'], $tanev, $szAdat['szemeszter']);
		    $r = db_query($q, array('fv' => 'closeTanev/hianyzasOsszesites/hozott', 'modul' => 'naplo_intezmeny', 'values' => $v));
		}
	    }
	}

	return true;

    }

    function setTanevStatus($tanev,$statusz) {
	global $mayorCache;
	$mayorCache->delType('szemeszter');

            $q = "UPDATE szemeszter SET statusz='%s' WHERE tanev=%u";
	    $v = array($statusz, $tanev);
	    return db_query($q, array('fv' => 'setTanevStatus', 'modul' => 'naplo_intezmeny', 'values' => $v));
    }


 function refreshOsztalyNaplo($dbNev, $tanev) {

	global $mayorCache;
	$mayorCache->flushdb();

        $lr = db_connect('naplo_intezmeny', array('priv' => 'Write', 'fv' => 'refreshOsztalyNaplo'));
        if (!$lr) return false;

            $q = "SELECT `osztalyId`,"._osztalyJel($tanev)." AS `osztalyJel`,"._evfolyam($tanev)." AS evfolyam,"._evfolyamJel($tanev)." AS evfolyamJel
                                FROM `osztaly` LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId)
				WHERE kezdoTanev<=%u AND vegzoTanev>=%u
                                ORDER BY evfolyam, evfolyamJel, kezdoTanev, jel";

	    $v = array($tanev, $tanev);
	    $ret = db_query($q, array('fv' => 'refreshOsztalyNaplo', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $lr);
	    if (!is_array($ret)) return false;
	    foreach ($ret as $key => $sor) {
        	$q = "REPLACE INTO `%s`.osztalyNaplo (osztalyId,osztalyJel,evfolyam,evfolyamJel) VALUES (%u,'%s',%u,'%s')";
		$v = array($dbNev, $sor['osztalyId'], $sor['osztalyJel'], $sor['evfolyam'], $sor['evfolyamJel']);
        	db_query($q, array('fv' => 'refreshOsztalyNaplo', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	    }

        db_close($lr);
	return true;
    
    
    }

    function szemeszterModositasOrig($ADAT) {


	for ($i = 0; $i < count($ADAT); $i++) {

	    $kezdesDt = $ADAT[$i]['kezdesDt']; $zarasDt = $ADAT[$i]['zarasDt'];
	    $tanev = $ADAT[$i]['tanev']; $szemeszter = $ADAT[$i]['szemeszter'];

	    $q = "SELECT zarasDt FROM szemeszter  WHERE tanev=%u AND szemeszter=%u";
	    $v = array($tanev, $szemeszter);
	    $zDt = db_query($q, array('fv' => 'szemeszterModositas', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'value'));

	    $q = "SELECT count(*) FROM zaroJegy WHERE hivatalosDt='%s'";
	    $v = array($zDt);
	    $db = db_query($q, array('fv' => 'szemeszterModositas', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'value'));

	    if ($db==0) {

		$q = "UPDATE szemeszter SET kezdesDt='%s',zarasDt='%s' WHERE tanev=%u AND szemeszter=%u";
		$v = array($kezdesDt, $zarasDt, $tanev, $szemeszter);
		db_query($q, array('fv' => 'szemeszterModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));

	    } else {
		$_SESSION['alert'][] = 'error:wrong_data:van már a '.$zDt.'-hez rögzített zárójegy!';
		return false;
	    }
	}

    }

    /*
     * A szemeszter dátumhatárainak módosítása több dolgot is érint.
     * 1. A zárójegyek hivatalos dátuma a szemszter záró dátuma - kivéve a vizsgajegyket.
     *    - megoldás: módosítsuk a zárójegy dátumát
     * 2. A tanév nap táblája a tanév kezdetétől a végéig tartalmaz napokat.
     *    - vegyük fel, illetve töröljük a hiányzó napokat (??)
     * 3. Elképzelhető, hogy a már beírt órákat, és ezen keresztül hiányzásokat és jegyeket is érinti a módosítás (ora tábla)
     *    - Ha órák törlésével járna, akkor egyszerűbb nem megengedni a módosítást. Ha kell, akkor előre törölje az órákat külön!
     */
    function szemeszterModositas($ADAT) {

	global $mayorCache;
	$mayorCache->flushdb();

	$success = true;
	for ($i = 0; $i < count($ADAT); $i++) {

	    unset($tolDt); unset($igDt);
	    $kezdesDt = $ADAT[$i]['kezdesDt']; $zarasDt = $ADAT[$i]['zarasDt'];
	    $tanev = $ADAT[$i]['tanev']; $szemeszter = $ADAT[$i]['szemeszter'];
	    $tanevDb = tanevDbNev(__INTEZMENY, $tanev);

	    $lr = db_connect('naplo_intezmeny', array('fv' => 'szemeszterModositas'));
	    db_start_trans($lr);

		// a korábbi szemeszter zárás dátumának és státuszának lekérdezése
		$q = "SELECT statusz, kezdesDt, zarasDt FROM szemeszter WHERE tanev=%u AND szemeszter=%u";
		$v = array($tanev, $szemeszter);
		$ret = db_query($q, array('fv' => 'szemeszterModositas/select', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'record'));
		$kDt = $ret['kezdesDt']; $zDt = $ret['zarasDt'];
		if ($ret['statusz'] == 'lezárt') {
		    // lezárt szemeszter adatait ne változtassuk
		    $_SESSION['alert'][] = 'message:wrong_data:lezárt szemeszter! (szemeszter='.$tanev.'/'.$szemeszter.')';
		    db_rollback($lr); db_close($lr); $success = false; continue;
		}
		if (($szemeszter == 1 && $kezdesDt != $kDt) || ($szemeszter == 2 && $zarasDt != $zDt)) {
		    // tanév kezdő vagy záró dátumának módosítása
		    if ($ret['statusz'] == 'aktív') {
			// A tanév adatbázisát is érintik a változások
			if ($szemeszter == 1 && $kezdesDt > $kDt) {
			    // Ha az év elejéből el kellene venni napokat, akkor ellenőrizzük, hogy vannak-e órák ezekre a napokra már beírva
			    $q = "SELECT COUNT(*) FROM `$tanevDb`.`ora` WHERE `dt` < '%s'";
			    $v = array($kezdesDt);
			    $db = db_query($q, array('fv' => 'szemeszterModositas/ora - kezdés', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'), $lr);
			    if ($db === "0") {
				// Ha nincs betöltött óra, akkor módosíthatók a nap tábla megfelelő rekordjai; munkanapok --> szorgalmi időszakon kívüli munkanap
				$q = "UPDATE `$tanevDb`.`nap` SET tipus='szorgalmi időszakon kívüli munkanap', orarendiHet=0 
					WHERE `dt` < '%s' AND tipus IN ('tanítási nap','speciális tanítási nap','tanítás nélküli munkanap')";
				$v = array($kezdesDt);
				$db = db_query($q, array('fv' => 'szemeszterModositas/delete nap', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'), $lr);
			    } else {
				// Ha van, akkor hibát üzenünk és nem hajtjuk végre a módosítást
				$_SESSION['alert'][] = 'message:insufficient_access:szemeszterMododitas/tanév később kezdés:A dátumváltoztatás már betöltött órákat érintene!';
				db_rollback($lr, 'szemeszterModositas/van betöltött óra!'); db_close($lr); $success = false; continue;
			    }
			} elseif ($szemeszter == 2 && $zarasDt < $zDt) {
			    // Ha az év végéből kell elvenni napokat, akkor ellenőrizzük, hogy vannak-e _lekötött_ órák ezekre a napokra már beírva
			    $q = "SELECT COUNT(*) FROM `$tanevDb`.`ora` WHERE `dt` > '%s' AND munkaido='lekötött'";
			    $v = array($zarasDt);
			    $db = db_query($q, array('fv' => 'szemeszterModositas/ora - zárás', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'), $lr);
			    if ($db === "0") {
				// Ha nincs betöltött óra, akkor módosíthatók a nap tábla megfelelő rekordjai; munkanapok --> szorgalmi időszakon kívüli munkanap
				$q = "UPDATE `$tanevDb`.`nap` SET tipus='szorgalmi időszakon kívüli munkanap', orarendiHet=0
					WHERE `dt` > '%s' AND tipus IN ('tanítási nap','speciális tanítási nap','tanítás nélküli munkanap')";
				$v = array($zarasDt);
				$db = db_query($q, array('fv' => 'szemeszterModositas/delete nap', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'), $lr);
			    } else {
				// Ha van, akkor hibát üzenünk és nem hajtjuk végre a módosítást
				$_SESSION['alert'][] = 'message:insufficient_access:szemeszterMododitas/tanév rövidítés:A dátumváltoztatás már betöltött órákat érintene!';
				db_rollback($lr, 'szemeszterModositas/van betöltött óra!'); db_close($lr); $success = false; continue;
			    }
			} elseif ($szemeszter == 1 && $kezdesDt < $kDt) {
			    // éves munkaterv (nap tábla) bővítése
			    $tolDt = $kezdesDt; $igDt = date('Y-m-d', strtotime('-1 day', strtotime($kDt)));
			} elseif ($szemeszter == 2 && $zarasDt > $zDt) {
			    // éves munkaterv (nap tábla) bővítése
			    $tolDt = date('Y-m-d', strtotime('+1 day', strtotime($zDt))); $igDt = $zarasDt;
			}
			if (isset($tolDt) && isset($igDt)) {
			    $Hetek = array(1);
			    $r = napokHozzaadasa($tanev, $tolDt, $igDt, $ADAT[$i], $lr);
			    unset($tolDt); unset($igDt);
			    if (!$r) { db_rollback($lr, 'szemeszterModositas/nap felvétel'); db_close($lr); $success = false; continue; }
    			    /*orarendiHetekHozzarendelese($tolDt, $igDt, $Hetek, $lr);*/
			}
		    } // aktív tanév
		} // tanév hossza változik
		// Az érintett, vizsgához nem kapcsolódó zárójegyek hivatalos dátumának módosítása
		$q = "UPDATE zaroJegy LEFT JOIN vizsga USING (zaroJegyId) SET hivatalosDt='%s' WHERE hivatalosDt='%s' AND vizsgaId IS NULL";
		$v = array($zarasDt, $zDt);
		$r = db_query($q, array('fv' => 'szemeszterModositas', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'value'));
		if ($r === false) { db_rollback($lr, 'szemeszterModositas/zarójegyek'); db_close($lr); $success = false; continue; }
		// A szemeszter kezdés és zárás dátumának módosítása
		$q = "UPDATE szemeszter SET kezdesDt='%s',zarasDt='%s' WHERE tanev=%u AND szemeszter=%u";
		$v = array($kezdesDt, $zarasDt, $tanev, $szemeszter);
		$r = db_query($q, array('fv' => 'szemeszterModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));
		if ($r === false) { db_rollback($lr, 'szemeszterModositas/dátum módosítás'); db_close($lr); $success = false; continue; }

	    db_commit($lr);
	    db_close($lr);

	    $_SESSION['alert'][] = 'info:success:tanev='.$tanev.', szemeszter='.$szemeszter;

	}
	return $success;

    }

?>