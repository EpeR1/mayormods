<?php

if (_RIGHTS_OK !== true) die();
if (!__NAPLOADMIN && !__VEZETOSEG) {
    $_SESSION['alert'] = 'page:insufficient_access'; 
} else {
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/file.php');
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!! XROSS-LINK
    require_once('include/modules/naplo/intezmeny/tankorTanarHozzarendeles.php'); // !!! link!

    $ADAT['telephelyId'] = $telephelyId = readVariable($_POST['telephelyId'], 'id');
    if (isset($telephelyId)) $filter = array('telephelyId'=>$telephelyId);

    $ADAT['csakOratervi'] = readVariable($_POST['csakOratervi'], 'bool', false);

    if ($ADAT['csakOratervi']==true) {
	$q = "select tankorTipusId from tankorTipus WHERE oratervi='óratervi'";
	$exportalandoTankorTipusIdk = db_query($q, array('fv'=>'','modul'=>'naplo_intezmeny','result'=>'idonly'));
	// array(1,2,3,11,13,14);
    } else {
	$q = "select tankorTipusId from tankorTipus";
	$exportalandoTankorTipusIdk = db_query($q, array('fv'=>'','modul'=>'naplo_intezmeny','result'=>'idonly'));
    }

    // teszt
    //	$q = "select *,REVERSE(SUBSTRING(SUBSTRING_INDEX(REVERSE(tankorNev),'(',1) FROM 2)) AS tankorId FROM kretaEttfEllenor";
    //	$ADAT['kretaEllenor'] = db_query($q, array('fv'=>'','modul'=>'naplo_intezmeny','result'=>'multiassoc','keyfield'=>'tankorId'));
    //dump($ADAT['kretaEllenor']);

    /* EREDETI KIEMELHETŐ KÓD */
	$ADAT['szuro'] = array(
	    'osztalyok' =>  getOsztalyok(),
	    'munkakozossegek' => getMunkakozossegek(),
	    'tanarok' => getTanarok(array('extraAttrs'=>'szuletesiIdo')),
	    'targyak' => getTargyak(),

	    'osztalyIds'=>array_keys(reindex(getOsztalyok(__TANEV,$filter),array('osztalyId'))), 
//	    'mkIds'=>readVariable($_POST['mkIds'],'id',array()),
//	    'tanarIds'=>array_keys(reindex(getTanarok(array('tanev'=>__TANEV)),array('tanarId'))),
//	    'targyIds'=>array_keys(reindex(getTargyak(array('tanev'=>__TANEV)),array('targyId'))),
	);

	/* MÁSOLAT */
	foreach ($ADAT['szuro']['targyak'] as $idx => $tAdat) $ADAT['targyAdat'][ $tAdat['targyId'] ] = $tAdat;
	foreach ($ADAT['szuro']['osztalyok'] as $idx => $tAdat) $ADAT['osztalyAdat'][ $tAdat['osztalyId'] ] = $tAdat;

	// A szűrőben beállítottnak megefelő tankörök lekérése
	$ADAT['tankorok'] = getTankorokBySzuro($ADAT['szuro']);
	$ADAT['szuro']['tankorTargyIds'] = array();
	foreach ($ADAT['tankorok'] as $ids => $tAdat) 
	    if (!in_array($tAdat['targyId'], $ADAT['szuro']['tankorTargyIds'])) 
		$ADAT['szuro']['tankorTargyIds'][] = $tAdat['targyId'];
	$ADAT['tanarok'] = getTanarokBySzuro($ADAT['szuro']);
	// stat
	$ADAT['keszTankorDb'] = 0;
	foreach ($ADAT['tankorok'] as $tAdat) if (is_array($tAdat['tanarIds']) && count($tAdat['tanarIds'])>0) $ADAT['keszTankorDb']++;
	$ADAT['tankorStat'] = getTankorStat();
    /* EREDETI KOD VEGE */

    if ($action=='kretaTanarExport') {
	$TANAROK = getTanarok(array('extraAttrs'=>'oId,viseltNevElotag,viseltCsaladinev,viseltUtonev,beDt,szuletesiHely,szuletesiIdo,szuleteskoriUtonev,szuleteskoriCsaladinev,szuleteskoriNevElotag'));
/*	$EXPORT[0] = array('Oktazon','Viselt név előtag','Viselt név vezetéknév','Viselt név keresztnév','Viselt név névsorrend',
			    'Anyja neve előtag','Anyja neve vezetéknév','Anyja neve keresztnév',	'Anyja neve névsorrend',
			    'Születési dátum',	'Születési hely', 'Születési ország','1. állampolgárság','2. állampolgárság',
			    'Végzettség szintje','Állandó lakcím', 'irányító szám','Állandó lakcím település','Állandó lakcím közterület név',
			    'Állandó lakcím közterület jelleg',	'Állandó lakcím házszám','Állandó lakcím pontosítás',
			    'Tartózkodási cím irányító szám','Tartózkodási cím település','Tartózkodási cím közterület név',
			    'Tartózkodási cím közterület jelleg','Tartózkodási cím házszám','Tartózkodási cím pontosítás',
			    'Szakmai gyakorlati évek száma','E-mail cím','Közoktatási intézmény neve','Közoktatási intézmény székhelye',
			    'OM azonosító','Kiemelt feladatellátási hely','Vezetői beosztás','Jogviszony létrejötte','Jogviszony megszűnte',
			    'Jogviszony típusa','Besorolási kategória','Fizetési osztály','Pótlék',
			    'Munkakör kategória','Munkakör','Tantárgy','Szakképzettségek',
			    'Tudományos fokozatok','Pedagógus szakvizsgák','Egyéb továbbképzések');
*/
	$j = 1;
	for ($i=0; $i<count($TANAROK); $i++) {
	    $EXPORT[$j][]=$TANAROK[$i]['oId'];
	    $EXPORT[$j][]=$TANAROK[$i]['viseltNevElotag'];
	    $EXPORT[$j][]=$TANAROK[$i]['viseltCsaladinev'];
	    $EXPORT[$j][]=$TANAROK[$i]['viseltUtonev'];
	    $EXPORT[$j][]=''; // névsorrend
	    $EXPORT[$j][]=''; // Anyja Neve
	    $EXPORT[$j][]=''; // Anyja Neve
	    $EXPORT[$j][]=''; // Anyja Neve
	    $EXPORT[$j][]=''; // Anyja Neve
	    $EXPORT[$j][]=strval($TANAROK[$i]['szuletesiIdo']);
	    $EXPORT[$j][]=strval($TANAROK[$i]['szuletesiHely']);
	    //for ($_x=count($EXPORT[$j]); $_x<count($EXPORT[0]); $_x++) $EXPORT[$j][]='';
	    $j++;
	}
	$ADAT['EXPORT'] = $EXPORT;
	//dump($ADAT);
        $ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', null, array('csv','ods','xml'));
        if ($ADAT['formatum'] == 'xml') $ADAT['mime'] = 'application/vnd.ms-excel';
        if (isset($ADAT['formatum'])) {
	    $file = _DOWNLOADDIR.'/private/naplo/export/kreta_mayor_TANAR_'.date('Ymd');
	    if (exportKretaTanarAdat($file, $ADAT)) {
    		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/&file='.$file.'.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
	    }
	}

    } elseif ($action=='tankorTanarExport') {

	/* KRÉTA tanárok */

	// echo 'A KIR rendszerből az alábbi módon letöltött táblázat a megfelelő: A KIR rendszerben az Alkalmazott keresése menüpontra kattintva, a Keresés ablakban a keresés találatait az Összes találat listába gombbal rakja át a Kérelemhez felvett személyek ablakba. Az ott található lista alján kattintson az Adatok exportálása gombra. A felugró ablakban válassza ki mindhárom listát (Személyes adatok, Jogviszony adatok, Képzettségek), majd töltse le a dokumentumot.';
	/* KRÉTA hagyományos táblázat */
	/*
	Az első oszlopba (A oszlop) az Osztály nevét kell beírni, abban az esetben, ha a tanóra a teljes
	osztálynak kerül megtartásra.
	A második oszlopba (B oszlop) a Csoport neveket kell beírni, abban az esetben, ha a tanóra
	csoportbontásban kerül megtartásra.
	A harmadik oszlopba (C oszlop) a Tantárgy nevét kell megadni.
	A negyedik, Csoportbontás nevű oszlopban (D oszlop) lehet jelezni, ha a tanóra
	csoportbontásban kerül megtartásra. Ennek jelzése az előzetes tantárgyfelosztás esetén nem
	kötelező!
	Az ötödik oszlopba (E oszlop) az egyes pedagógusok adott tantárgyra, ill. osztályra/csoportra
	vonatkozó heti óraszámát kell megadni.
	Az utolsó oszlop (F oszlop) a pedagógus nevét tartalmazza! 
	*/

	$q = "select tanarId FROM tanar WHERE CONCAT(viseltNevElotag,viseltCsaladinev,viseltUtonev) = (select CONCAT(viseltNevElotag,viseltCsaladinev,viseltUtonev) AS nev from tanar GROUP BY nev HAVING count(*)>1)";
	$utkozoNevuTanarok = db_query($q, array('fv'=>'','modul'=>'naplo_intezmeny','result'=>'assoc','keyfield'=>'tanarId'));
	$TANARADAT = (reindex($ADAT['szuro']['tanarok'],array('tanarId')));
	// schema
        $EXPORT[0][] = 'Osztály';
	$EXPORT[0][] = 'Csoport';
	$EXPORT[0][] = 'Tantárgy';
	$EXPORT[0][] = 'Óraszám';
	$EXPORT[0][] = 'Tanár';

	$EXPORT[0][] = 'Túlóra';
	$EXPORT[0][] = 'TTF óraszám korrekció';
	$EXPORT[0][] = 'Nemzetiségi óra';
	$EXPORT[0][] = 'Megbízási szerződéssel ellátott óra óraszáma';

	// -------
 	$j = 1;
	for ($i=0; $i<count($ADAT['tankorok']); $i++) {
	    if ($ADAT['tankorok'][$i]['bontasOk']===false) {
		$_SESSION['alert'][] = 'info:tankör bontás hiba:'.$ADAT['tankorok'][$i]['tankorNev'];
		//dump($ADAT['tankorok'][$i]);
		// ha azt szeretnénk, hogy ne legyenek benne a "hibás" (bontasOk===false), akkor ez kell: } else {
	    }
	    if ($ADAT['tankorok'][$i]['hetiOraszam'] == 0) continue; // a heti nullás órákat kihagyjuk
	    if (!in_array($ADAT['tankorok'][$i]['tankorTipusId'],$exportalandoTankorTipusIdk)) continue;
	    for ($t=0; $t<count($ADAT['tankorok'][$i]['tanarIds']); $t++) {
		    $_tanarId = $ADAT['tankorok'][$i]['tanarIds'][$t];
		    $_szulDt = $TANARADAT[intval($_tanarId)][0]['szuletesiIdo'];
		    $_tanarNev = $TANARADAT[intval($_tanarId)][0]['tanarNev'];
		    $_csoportNev = getTankorCsoportByTankorId($ADAT['tankorok'][$i]['tankorId'])[0]['csoportNev'];
		    
		    $_osztalyIds = getTankorOsztalyaiByTanev($ADAT['tankorok'][$i]['tankorId']); 
		    $_osztalyJel = $ADAT['osztalyAdat'][$_osztalyIds[0]]['osztalyJel'];
		    if (count($osztalyIds) > 1 || $_osztalyJel != $_csoportNev) {
			$EXPORT[$j][] = '';
			$EXPORT[$j][] = $_csoportNev; // B oszlop: csoport név
		    } else {
			$EXPORT[$j][] = $_osztalyJel; // A oszlop: egész osztály
			$EXPORT[$j][] = ''; 
		    }
		    $EXPORT[$j][] = $ADAT['targyAdat'][ $ADAT['tankorok'][$i]['targyId'] ]['kretaTargyNev']!=''?
			$ADAT['targyAdat'][ $ADAT['tankorok'][$i]['targyId'] ]['kretaTargyNev']:
			$ADAT['targyAdat'][ $ADAT['tankorok'][$i]['targyId'] ]['targyNev']; // C oszlop: tantárgy neve
		    // $EXPORT[$j][] = ''; // D oszlop: TRUE/FALSE???
		    $EXPORT[$j][] = $ADAT['tankorok'][$i]['hetiOraszam']/count($ADAT['tankorok'][$i]['tanarIds']); // E oszlop: heti óraszám
		    if (is_array($utkozoNevuTanarok[$_tanarId]) && $_szulDt != '0000-00-00' && $_szulDt != '') $_tanarNev .= ' ('.$_szulDt.')'; // E oszlop: pedagógus neve
		    $EXPORT[$j][] = $_tanarNev; // F oszlop
		    $EXPORT[$j][] = '';		// 'Túlóra';
		    $EXPORT[$j][] = 'Nem';	// 'TTF óraszám korrekció';
		    $EXPORT[$j][] = 'Nem';	// 'Nemzetiségi óra';
		    $EXPORT[$j][] = '';		// 'Megbízási szerződéssel ellátott óra óraszáma';
		    $j++;
	    }
	}
	$ADAT['EXPORT'] = $EXPORT;

        $ADAT['formatum'] = readVariable($_POST['formatum'], 'enum', null, array('csv','ods','xml'));
        if ($ADAT['formatum'] == 'xml') $ADAT['mime'] = 'application/vnd.ms-excel';
        if (isset($ADAT['formatum'])) {
	    $file = _DOWNLOADDIR.'/private/naplo/export/kreta_ETTF_telephely'.intval($telephelyId).'_'.date('Ymd');
	    if (exportTankorTanar($file, $ADAT)) {
    		header('Location: '.location('index.php?page=session&f=download&download=true&dir=naplo/export/&file='.$file.'.'.$ADAT['formatum'].'&mimetype='.$ADAT['mime']));
	    }
	}
    } // end of tankorTanarExport

    $TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array());
    getToolParameters();

}


?>
