<?php

    require_once('include/modules/naplo/share/zaradek.php');

    function diakJogviszonyValtas($ADAT) {


	global $ZaradekIndex;

	/*
	    $ADAT
		diakId
		jogviszonyValtasDt
		ujStatusz
		tanev
		zaradek - felvétel | felvétel osztályba | magántanuló | felfüggesztés
		lezarasZaradekIndex - 40..45
		--
		hatarozat
		felfuggesztesOk
		felfuggesztesIgDt
		lezarasZaradekIndex
		lezarasIgazolatlanOrakSzama, lezarasIskola
	*/
	$ADAT['jogviszonyValtasDt'] = (readVariable($ADAT['jogviszonyValtasDt'],'regexp',null,array('^[0-9]{4}-[0-9]{2}-[0-9]{2}$')));

	if (!isset($ADAT['jogviszonyValtasDt'])) { // nincs meg a változtatás dátuma
	    $_SESSION['alert'][] = 'message:empty_field:diakJogviszonyValtas:jogviszonyValtasDt';
	    return false;
	}
// Mégis egengedett --> lásd: base/rights: checkDiakStatusz()
//	if (strtotime($ADAT['jogviszonyValtasDt']) > time()) { // jövőbeni statusz nem állítható be!
//	    $_SESSION['alert'][] = 'message:wrong_data:diakJogviszonyValtas:jogviszonyValtasDt='.$ADAT['jogviszonyValtasDt'].':Jövőbeli állapotváltást nem rögzítünk!';
//	    return false;
//	}

	// A megelőző és következő jogviszony státusz lekérdezése
	$DJ = getDiakJogviszonyByDt($ADAT['diakId'], $ADAT['jogviszonyValtasDt']);
	if (!isset($DJ['elotte'])) { // Kell legyen megelőző - amúgy ujDiak kellene
	    $_SESSION['alert'][] = 'message:wrong_data:diakJogviszonyValtas:Nincs kezdeti státusz - hiba történt a diák adatainak rögzítésekor?';
	    return false;
	}

	if ($DJ['elotte'] == 'felvételt nyert' && $ADAT['ujStatusz'] != 'jogviszonyban van') { // felvett először iratkozzon be
	    $_SESSION['alert'][] = 'message:wrong_data:'.$ADAT['ujStatusz'].':diakJogviszonyValtas:beiratkozáskor csak "jogviszonyban van" státuszba kerülhet a diák!';
	    return false;
	}

	if ($ADAT['ujStatusz'] == $DJ['elotte']) return true; // nincs mit tenni

	if (isset($DJ['utana'])) { // a következő jogviszonyváltás már rögzítve van... nem piszkálhatunk az intervallum közbe!
	    $_SESSION['alert'][] = 'message:wrong_data:diakJogviszonyValtas:már van rögzítve státuszváltás a megadott dátum után!:'.$DJ['utana'];
	    return false;
	}

        $lr = db_connect('naplo_intezmeny');
        db_start_trans($lr);

	// A diák osztályai a változás napján
	$osztalyIds = getDiakOsztalya($ADAT['diakId'], array('tanev' => $ADAT['tanev'], 'tolDt' => $ADAT['jogviszonyValtasDt'], 'igDt' => $ADAT['jogviszonyValtasDt'], 'result' => 'idonly'));
	// A változás idején (vagy utána közvetlenül) érvényes szemeszter adatok
	$szAdat = getSzemeszterByDt($ADAT['jogviszonyValtasDt'], 1);
	switch ($ADAT['ujStatusz']) {
	    case 'jogviszonyban van':
		if ($DJ['elotte'] == 'felvételt nyert') { // beiratkozás

		    // diak tábla módosítása - jogviszonyKezdete
		    $q = "UPDATE diak SET statusz='jogviszonyban van',jogviszonyKezdete='%s',kezdoTanev=IFNULL(kezdoTanev,%u),kezdoSzemeszter=IFNULL(kezdoSzemeszter,%u) WHERE diakId=%u";
		    $v = array($ADAT['jogviszonyValtasDt'], $szAdat['tanev'], $szAdat['szemeszter'], $ADAT['diakId']);
		    $r = db_query($q, array('fv' => 'diakJogviszonyValtas', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
		    if ($r===false) { db_rollback($lr, 'diak.jogviszonyKezdete - fail');db_close($lr);return false; }
		    
		    // Záradékolás - felvétel/beiratkozás
		    $In = getIntezmenyByRovidnev(__INTEZMENY);
		    $osztalyStr = array();
		    if ($zaradekOsztallyal = (is_array($osztalyIds) && count($osztalyIds) > 0)) {
			foreach ($osztalyIds as $key => $osztalyId) {
			    $osztalyAdat[$osztalyId] = getOsztalyAdat($osztalyId, $ADAT['tanev']);
			    $osztalyStr[] = $osztalyAdat[$osztalyId]['kezdoTanev'].'-'.($osztalyAdat[$osztalyId]['vegzoTanev']+1).'/'.$osztalyAdat[$osztalyId]['jel'];
			}
		    }
		    $Z = array(
			'csere' => array(
			    '%iskola címe%' => $In['nev'].' ('.$In['cimIrsz'].' '.$In['cimHelyseg'].', '.$In['cimKozteruletNev'].' '.$In['cimKozteruletJelleg'].' '.$In['cimHazszam'].'.)',
			    '%osztály%' => implode(', ', $osztalyStr),
			    '%határozat száma%' => $ADAT['hatarozat'],
			),
			'zaradekIndex' => ($zaradekOsztallyal?$ZaradekIndex['jogviszony megnyitás']['felvétel osztályba'] : $ZaradekIndex['jogviszony megnyitás']['felvétel'])
		    );
		}
		break;
	    case 'vendégtanuló':
		break;
	    case 'magántanuló':
		    $Z = array('zaradekIndex' => $ZaradekIndex['jogviszony változás']['magántanuló']);  
		break;
	    case 'jogviszonya felfüggesztve':
		    $Z = array(
//20110610		'zaradekIndex' => $ADAT['zaradek']['felfüggesztés'],
			'zaradekIndex' => $ZaradekIndex['jogviszony változás']['felfüggesztés'],
			'csere' => array('%ok%' => $ADAT['felfuggesztesOk'], '%igDt%' => $ADAT['felfuggesztesIgDt'])
		    );
		break;
	    case 'jogviszonya lezárva':
/* Ezt előrébb vizsgáljuk 
		    if (isset($DJ['utana'])) { // lezárás után nem lehet jogviszonybejegzés
			$_SESSION['alert'][] = 'message:wrong_data:diakJogviszonyValtas:bejegyzett jogviszony változás előtt a jogviszony nem zárható le:'.$DJ['utana'];
			db_rollback($lr);
			db_close($lr);
			return false;
		    }
*/

		    // diak tábla módosítása - jogviszonyVege
		    $q = "UPDATE diak SET jogviszonyVege='%s' WHERE diakId=%u";
		    $v = array($ADAT['jogviszonyValtasDt'], $ADAT['diakId']);
		    $r = db_query($q, array('fv' => 'diakJogviszonyValtas', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);

		    if ($r===false) { db_rollback($lr, 'diak.jogviszonyVege - fail');db_close($lr);return false; }

		    // Osztályokból kivétel (Ez a tankörökből is kiveszi, ellenőrzi a jegyeket, hiányzásokat is a tanévekben)
		    foreach ($osztalyIds as $key => $osztalyId) {
			$r = osztalyDiakTorol(array('osztalyId' => $osztalyId, 'diakId' => $ADAT['diakId'], 'tolDt' => $ADAT['jogviszonyValtasDt']), $lr);
			if ($r===false) { db_rollback($lr, 'osztalyDiakTorol - fail');db_close($lr);return false; }
		    }
		    // Ha netán van olyan tankörtagsága, ami nem osztályhoz kötődik, akkor azt itt törölni kellene!!!! Például: vendégtanuló...
		    // akkor lejjebb töröljük

		    // záradékolás
		    $Z = array(
			'zaradekIndex' => $ADAT['lezarasZaradekIndex'],
			'csere' => array('%igazolatlan órák száma%' => $ADAT['lezarasIgazolatlanOrakSzama'], '%iskola%' => $ADAT['lezarasIskola'])
		    );
		break;

	    default:
		$_SESSION['alert'][] = 'message:wrong_data:új statusz='.$ADAT['ujStatusz'].':diakJogviszonyValtas';
		db_rollback($lr, 'új statusz - wrong');
		db_close($lr);
		return false;
		break;
	}

	// diakJogviszony tábla - bejegyzés
	$q = "INSERT INTO diakJogviszony (diakId, statusz, dt) VALUES (%u, '%s', '%s')";
	$v = array($ADAT['diakId'], $ADAT['ujStatusz'], $ADAT['jogviszonyValtasDt']);
	$r = db_query($q, array('fv' => 'diakJogviszonyValtas', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	if ($r===false) { db_rollback($lr, 'diakJogviszony - fail');db_close($lr);return false; }

	// Tankörökből való kiléptetés (lezárás esetén már megtörtént)
	if ($ADAT['ujStatusz'] == 'jogviszonya felfüggesztve' || $ADAT['ujStatusz'] == 'jogviszonya lezárva') {
	    $tankorIds = getTankorByDiakId($ADAT['diakId'], $ADAT['tanev'], array('tolDt' => $ADAT['jogviszonyValtasDt'], 'override' => 'true', 'result' => 'idonly'), $lr);
	    if (is_array($tankorIds) && count($tankorIds) > 0)
	    $r = tankorDiakTorol(array(
	      'diakId' => $ADAT['diakId'], 'utkozes' => 'torles', 'tankorIds' => $tankorIds, 'MIN_CONTROL' => false, 'tolDt' => $ADAT['jogviszonyValtasDt']
	    ), $lr);
	    if ($r===false) { db_rollback($lr, 'tankorDiakTorol - fail');db_close($lr);return false; }
	} elseif ($ADAT['ujStatusz'] == 'magántanuló') {
	    $tolDt = $ADAT['jogviszonyValtasDt'];
	    //törlés
    		$TH = $TJ = array();                                                                                                                                                                               
    		$HSUM = $JSUM = array();                                                                                                                                                                           
    		// A tol-ig dátumok által érintett aktív tanévek lekérdezése                                                                                                                                       
    		$aktivTanevek = getTanevekByDtInterval($ADAT['jogviszonyValtasDt'], date('Y-m-d'), array('aktív')); 
        	    // Az érintett tanéveken végigmenve                                                                                                                                                            
        	foreach ($aktivTanevek as $key => $tanev) {
		    $TANKORIDS = getTankorByDiakId($ADAT['diakId'], $ADAT['tanev'], array('tolDt' => $tolDt, 'result' => 'idonly'), $lr);
		    if (is_array($TANKORIDS) && count($TANKORIDS) > 0) {
            		for ($i = 0; $i < count($TANKORIDS); $i++) {                                                                                                                                               
                	    $H = tankorDiakHianyzasIdk($ADAT['diakId'], $TANKORIDS[$i], $tanev, $tolDt, null); // tol-ig-et a függvény initTolIgDt hívással kapja
                	    $J = tankorDiakJegyIdk(array('diakId'=>$ADAT['diakId'], 'tankorIds'=>$TANKORIDS[$i], 'tanev'=>$tanev, 'tolDt'=>$tolDt, 'igDt'=>null));
                	    if (is_array($H)) $HSUM = array_merge($HSUM,$H);
                	    if (is_array($J)) $JSUM = array_merge($JSUM,$J);
            		}
                	// hiányzások és jegyek törlése...                                                                                                                                                     
                	$r = hianyzasTorles($HSUM, $tanev, $lr);
			if ($r!==false) $r = jegyTorles($JSUM, null, $tanev, $lr); // különben úgyis rollback van
		    }
            	}
	    if ($r===false) { db_rollback($lr, 'hianyzasTorles/jegyTorles - fail');db_close($lr);return false; }
	}
	// Záradék rögzítés - ha van
	if (isset($Z['zaradekIndex'])) {
	    $Z['diakId'] = $ADAT['diakId'];
	    $Z['iktatoszam'] = $ADAT['iktatoszam'];
	    $Z['dt'] = $ADAT['jogviszonyValtasDt'];
	    $r = zaradekRogzites($Z,$lr);
	    if ($r===false) { db_rollback($lr, 'zaradekRogzites - fail');db_close($lr);return false; }
	}
	// Ha ez az utolsó jogviszony állapot, akkor a diak tábla is módosítandó!

	db_commit($lr);
	db_close($lr);

	checkDiakStatusz();

	return true;

    }


    function ujDiak($ADAT) {

	global $ZaradekIndex;

	$NOTNULL = array('viseltNevElotag','viseltCsaladinev','viseltUtonev','szuleteskoriNevElotag','szuleteskoriCsaladinev','szuleteskoriUtonev');

/*
    $ADAT mezői:
	jogviszonyKezdete - záradékolás és diakJogviszony miatt
	osztalyId - Osztálybalépéshez (csak ha jogviszonyKezdete a dátum
	felvetelTipus - beiratkozásra vár(statusz:felvételt nyert)|vendégtanuló(statusz:vendégtanuló)|más(statusz:jogviszonyban van)
	zaradek - típusonként
	osztaly - kezdoTanev/zaroTanev/jel

	hatarozat - iskolaváltás esetén
	tabelFields - A diak tábla mezői - ez alapján engedélyezett a módosítás
	intezmeny - Az intézmény adatai
*/
	if (is_array($ADAT['tableFields']))$FIELDS = $ADAT['tableFields'];
	else $FIELDS = getTableFields('diak');
	if ($ADAT['felvetelTipus'] == 'beiratkozásra vár') {
	    $statusz = 'felvételt nyert'; 
	    unset($ADAT['jogviszonyKezdete']);
	} elseif ($ADAT['felvetelTipus'] == 'vendégtanuló') { 
	    $statusz='vendégtanuló';
	    unset($ADAT['osztalyId']);
	} else 
	    $statusz = 'jogviszonyban van';

	foreach($ADAT as $attr => $value) {
	    if (array_key_exists($attr,$FIELDS) && !in_array($attr, array('action','diakId'))) {
		$A[] = "$attr";
		if ($value=='' && !in_array($attr, $NOTNULL)) {
		    $P[]='null';
		} else {
		    $V[] = $value;
		    $P[] = "'%s'";
		}
	    }
	}
	$q = "INSERT INTO diak (statusz,".implode(',', $A).") VALUES ('".$statusz."',".implode(',',$P).')';
	$diakId = db_query($q, array('fv' => 'ujDiak', 'modul'=>'naplo_intezmeny', 'result' => 'insert', 'values' => $V));
	if ($diakId) {
	    if ($statusz == 'jogviszonyban van' || $statusz == 'vendégtanuló') {
		// diakJogviszony tábla
		$q = "INSERT INTO diakJogviszony (diakId, statusz, dt) VALUES (%u, '%s', '%s')";
		db_query($q, array('fv' => 'ujDiak/diakJogviszony', 'modul' => 'naplo_intezmeny', 'values' => array($diakId, $statusz, $ADAT['jogviszonyKezdete'])));
	    } // Ha csak felvételt nyert, akkor nincs jogviszony információ
	    // osztályba rakás
	    if (isset($ADAT['osztalyId'])) {
		$q = "INSERT INTO osztalyDiak (osztalyId, diakId, beDt) VALUES (%u, %u, '%s')";
		db_query($q, array('fv' => 'ujDiak/osztalyDiak', 'modul' => 'naplo_intezmeny', 'values' => array($ADAT['osztalyId'], $diakId, $ADAT['jogviszonyKezdete'])));
	    }
	    // záradékolás
	    if (isset($ADAT['zaradek'][ $ADAT['felvetelTipus'] ])) { // A felvételt nyert típus nem záradékolandó
		if (is_array($ADAT['intezmeny'])) $In = $ADAT['intezmeny'];
		else $In = getIntezmenyByRovidnev(__INTEZMENY);
		$Z = array(
		    'diakId' => $diakId,
		    'dt' => $ADAT['jogviszonyKezdete'],
		    'csere' => array(
			'%iskola címe%' => $In['nev'].' ('.$In['cimIrsz'].' '.$In['cimHelyseg'].', '.$In['cimKozteruletNev'].' '.$In['cimKozteruletJelleg'].' '.$In['cimHazszam'].'.)',
			'%osztály%' => $ADAT['osztaly']['kezdoTanev'].'-'.($ADAT['osztaly']['vegzoTanev']+1).'/'.$ADAT['osztaly']['jel'],
			'%határozat száma%' => $ADAT['hatarozat'],
		    ),
		    'zaradekIndex' => $ADAT['zaradek'][ $ADAT['felvetelTipus'] ]
		);
		zaradekRogzites($Z);
	    }
	}
	return $diakId;

    }

    function diakHozottHianyzas($ADAT) {

	if ($ADAT['diakId']!='') {
		if ($ADAT['hozottHianyzasIgazolt']!=0) {
		    $q = "INSERT INTO `hianyzasHozott` (`diakId`,`statusz`,`dbHianyzas`,`dt`) VALUES (%u,'%s',%u,NOW())";
		    $v = array($ADAT['diakId'],'igazolt',$ADAT['hozottHianyzasIgazolt']);
		    $result = db_query($q, array('fv' => 'diakAdatModositas', 'modul' => 'naplo', 'values' => $v));
		}
		if ($ADAT['hozottHianyzasIgazolatlan']!=0) {
		    $q = "INSERT INTO `hianyzasHozott` (`diakId`,`statusz`,`dbHianyzas`,`dt`) VALUES (%u,'%s',%u,NOW())";
		    $v = array($ADAT['diakId'],'igazolatlan',$ADAT['hozottHianyzasIgazolatlan']);
		    $result = db_query($q, array('fv' => 'diakAdatModositas', 'modul' => 'naplo', 'values' => $v));
		}
	}
	return $result;
    }
    
    function diakAdatModositas($ADAT) {

	$FIELDS = getTableFields('diak');
	$NOTNULL = array('viseltNevElotag','viseltCsaladinev','viseltUtonev','szuleteskoriNevElotag','szuleteskoriCsaladinev','szuleteskoriUtonev');

	$v = array();
	foreach($ADAT as $attr => $value) {
	    if (array_key_exists($attr,$FIELDS) && !in_array($attr, array('action','diakId'))) {
		if ($value=='' && !in_array($attr, $NOTNULL)) {
		    $value='null';
		} else {
		    array_push($v, $value);
		    $value = "'%s'";
		}
		$T[] = "$attr=$value";
	    }
	}
	array_push($v, $ADAT['diakId']);
	$q = "UPDATE diak SET ".implode(',', $T)." WHERE diakId=%u";
	return db_query($q, array('fv' => 'diakAdatModositas', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

    }

    function diakJogviszonyBejegyzesTorles($ADAT) {

	$q = "DELETE FROM `diakJogviszony` WHERE `diakId`=%u AND `statusz`='%s' AND `dt`='%s'";
	$v = array($ADAT['diakId'], $ADAT['statusz'], $ADAT['dt']);
	db_query($q, array('fv' => 'diakJogviszonyBejegyzesTorles/diakJogviszony', 'modul' => 'naplo_intezmeny', 'values' => $v));

	$q = "DELETE FROM `zaradek` WHERE `diakId` = %u AND `zaradekId` = %u";
	$v = array($ADAT['diakId'], $ADAT['zaradekId']);
	db_query($q, array('fv' => 'diakJogviszonyBejegyzesTorles/zaradek', 'modul' => 'naplo_intezmeny', 'values' => $v));

    }

    function diakAdatkezelesModositas($ADAT) {

	$q = "INSERT IGNORE INTO diakAdatkezeles (diakId,kulcs,ertek) VALUES (%u,'%s','%s')";
	$v = array($ADAT['diakId'], $ADAT['kulcs'], $ADAT['ertek']);
	return db_query($q, array('fv' => 'diakAdatkezelesModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));
	
    }

    function diakTorol($ADAT) {

	$q = "DELETE FROM diak WHERE statusz='felvételt nyert' AND diakId=%u";
	$v = array($ADAT['diakId']);
	return db_query($q, array('fv' => 'diakTorol', 'modul' => 'naplo_intezmeny', 'values' => $v));

    }

?>
