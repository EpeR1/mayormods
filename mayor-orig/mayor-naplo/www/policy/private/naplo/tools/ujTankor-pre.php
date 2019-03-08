<?php
    /*
     * Az oldal különböző bejövő paraméterek alapján létrehoz egy tankört, majd az adatait json formában visszaküldi...
     * Jelenleg csak $bontasIds bemeneti paraméterrel működik.
     */

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';
    else {

	require_once('include/modules/naplo/share/bontas.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorModifier.php');

/*
	$diakId = readVariable($_POST['diakId'], 'id');
	$osztalyId = readVariable($_POST['osztalyId'], 'id');
	$tanarId = readVariable($_POST['tanarId'], 'id');
	$targyId = readVariable($_POST['targyId'], 'id');
	$mkId = readVariable($_POST['mkId'], 'id');
	$tolDt = readVariable($_POST['tolDt'], 'date');
	$igDt = readVariable($_POST['igDt'], 'date');
	$tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
*/
	$bontasIds = readVariable($_POST['bontasIds'],'id'); // 1430, 1441
	// Az első bontásból lekérdezzük a következő adatokat:  
	// - (tárgy) típus
	// - tárgy (targyId)
	$koAdat = getKepzesOratervAdatByBontasId($bontasIds[0]);
	$ADAT['tipus'] = $koAdat['tipus'];
	$ADAT['targyId'] = $koAdat['targyId'];
	// - tankör típus (tankorTipusId) ~ első nyelv, második nyelv, óratervi (gyakorlat?)
	$ADAT['tankorTipusId'] = ($koAdat['tipus']=='első nyelv'?2:($koAdat['tipus']=='második nyelv'?3:1));
	// - felmenő óraszámok (tankorSzemeszter)
	$targyOraterv = getTargyAdatFromKepzesOraterv($koAdat['kepzesId'], $ADAT);
	$ADAT['oraszam'] = array(); $tanev=__TANEV; $ok = false;
	foreach ($targyOraterv as $targyId => $tAdat) {
	    foreach ($tAdat as $evfolyamJel => $eAdat) {
		if ($evfolyamJel == $koAdat['evfolyamJel']) $ok = true;
		if ($ok) {
		    foreach ($eAdat as $szemeszter => $szAdat) {
			$ADAT['oraszam'][$tanev][$szemeszter] = $szAdat[0]['hetiOraszam'];
			$tankorOraszam[] = array('tanev'=>$tanev,'szemeszter'=>$szemeszter,'oraszam'=>$szAdat[0]['hetiOraszam']);
		    }
		    $tanev++;
		}
	    }
	}
	// a bontásokból: 
	// - osztalyIds
	$q = "SELECT DISTINCT osztalyId FROM kepzesTargyBontas WHERE bontasId IN (".implode(',', array_fill(0, count($bontasIds), '%u')).")";
	$ADAT['osztalyIds'] = db_query($q, array('fv'=>'ujTankor-pre','modul'=>'naplo','result'=>'idonly','values'=>$bontasIds));
	// A tárgy adataiból: évközi követelmény
	$targyAdat = getTargyById($ADAT['targyId']);
	$ADAT['kovetelmeny'] = $targyAdat['evkoziKovetelmeny'];
	// kellene még: elnevezés
	$ADAT['tankorJel'] = 'B'.$bontasIds[0];
	// választható - default NULL; min/max létszám: 0; évközi követelmény: a tárgyból/jegy; 
	$ADAT['min'] = $ADAT['max'] = 0;

	// A tankor felvétele
	$lr = db_connect('naplo_intezmeny');
	db_start_trans($lr); $ok = true;
	$q = "INSERT INTO tankor (targyId,tankorTipusId,kovetelmeny,min,max) VALUES (%u,%u,'%s',%u,%u)";
	$v = array($ADAT['targyId'], $ADAT['tankorTipusId'], $ADAT['kovetelmeny'], $ADAT['min'], $ADAT['max']);
	$tankorId = db_query($q, array('fv'=>'ujTankor-pre/1','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v), $lr);
	$ok = $ok && $tankorId;
	if ($ok) {
	    // tankör-osztály hozzárendelés
	    $q = "INSERT INTO tankorOsztaly (tankorId, osztalyId) VALUES (%u, %u)";
	    foreach ($ADAT['osztalyIds'] as $index => $osztalyId) $ok = $ok && db_query($q, array('fv'=>'ujTankor-pre/3','modul'=>'naplo_intezmeny','values'=>array($tankorId, $osztalyId)), $lr);
	}
	if ($ok) {
	    // tankör-szemeszter - még tankörnév nélkül...
	    $tankorNev = $ADAT['tankorJel'];
	    foreach ($ADAT['oraszam'] as $tanev => $tAdat) {
		foreach ($tAdat as $szemeszter => $oraszam) {
		    $q = "INSERT INTO tankorSzemeszter (tankorId, tanev, szemeszter, oraszam, tankorNev) VALUES (%u, %u, %u, %f, '%s')";
		    $ok = $ok && db_query($q, array('fv'=>'ujTankor-pre/4','modul'=>'naplo_intezmeny','values'=>array($tankorId, $tanev, $szemeszter, $oraszam, $tankorNev)), $lr);
		}
	    }
	}
	// tankör nevének beállítása
	if ($ok) $nev = setTankorNev($tankorId, $tankorNevExtra='B'.$bontasIds[0], $lr);
	$ok = $ok && ($nev !== false);

	// tankör bontásokhoz rendelése
	$bontasAdat = getBontasAdat($bontasIds[0]);
	$hetiOraszam = $koAdat['hetiOraszam']-$bontasAdat['hetiOraszam'];
	$ok = $ok && bontasTankorHozzarendeles($bontasIds, $tankorId, $hetiOraszam, $lr);

	// visszaküldendő adatok összegyűjtése
	$tankorAdat = getTankorAdat($tankorId, __TANEV, $lr);
	$_JSON = array(
	    'bontasIds' => $bontasIds,
	    'hetiOraszam' => $hetiOraszam,
	    'tankorId' => $tankorId,
	    'tankorNev' => str_replace($targyAdat['targyNev'].' ','',$tankorAdat[$tankorId][0]['tankorNev']),
	    'tankorNevOrig' => $tankorAdat[$tankorId][0]['tankorNev'],
	    'tankorOraszam' => $tankorOraszam,
	    'ideiTankorOraszam' => array(
		 $tankorAdat[$tankorId][0]['oraszam'],
		 $tankorAdat[$tankorId][1]['oraszam']
	    )
	);
	if ($ok !== false) {
	    db_commit($lr);
	} else { db_rollback($lr); }
	db_close($lr);

    }
?>