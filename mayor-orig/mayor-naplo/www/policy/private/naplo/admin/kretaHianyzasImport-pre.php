<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN!==true || __PORTAL_CODE!=='vmg') {

        $_SESSION['alert'][] = 'page:insufficient_access';

    }

    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/file.php');
    require_once('include/modules/naplo/share/ora.php');

    ini_set('max_execution_time', 120);

    $KRETA2MAYOR['ora']['tipus'] = array(
	    'Szakszerű helyettesítés' => 'helyettesítés',
	    'Szakszerű felügyelet' => 'helyettesítés',
	    'Óraösszevonás' => 'összevonás',
	    'Nem szakszerű helyettesítés (felügyelet)' => 'felügyelet'
	);

//$KRETA2MAYOR['tanar'] = reindex(getTanarok(array('összes'=>true,'extraAttrs'=>'kretaNev')),array('kretaNev'));
//dump($KRETA2MAYOR['tanar']);

    $lr_intezmeny = db_connect('naplo_intezmeny');
    $lr_naplo = db_connect('naplo');

    $q = "select getNev(diakId,'diak') COLLATE utf8_hungarian_ci AS diakNev,diakId,statusz,oId from ".__INTEZMENYDBNEV.".diak WHERE statusz!='jogviszonya lezárva' ORDER BY diakNev";
    $v = array();
    $DIAKNEV2diakId = db_query($q, array('debug'=>false,'modul'=>'naplo','values'=>$v,'result'=>'multiassoc','keyfield'=>'diakNev'),$lr_naplo);
    $file = fopen("/tmp/hianyzasKreta.tsv","r");
    if ($file!==false) {
	$q = "TRUNCATE TABLE hianyzasKreta";
	db_query($q, array('fv'=>'kretaHianyzasImport'),$lr_naplo) or die();
    }
    while(! feof($file)) {
	$line = chop(trim(fgets($file)));
	$record = explode("\t",$line);
//	dump($record);
/*
kretaHianyzasImportarray (size=10)
  0 => string 'Bacsó Dániel József' (length=22)
  1 => string '2018-12-17' (length=10)
  2 => string '2' (length=1)
  3 => string '08.A' (length=4)
  4 => string 'ének-zene' (length=10)
  5 => string 'Hiányzás' (length=10)
  6 => string '' (length=0)
  7 => string 'Igen' (length=4)
  8 => string 'Orvosi igazolás' (length=16)

  ha van: 9 => string '72437828706' (length=11)

*/
	$D = array();
	$D['diakNev'] = $record[0];
	if (count($DIAKNEV2diakId[$D['diakNev']])!=1) {
	    $_SESSION['alert'][] = 'info:dup'.serialize($D['diakNev']).':'.serialize($DIAKNEV2diakId[$D['diakNev']]);
	    $ADAT['hiba'][] = $D;
	    continue;;
	} else {
	    $D['diakId'] = $DIAKNEV2diakId[$D['diakNev']][0]['diakId'];
	    $D['oId'] = $DIAKNEV2diakId[$D['diakNev']][0]['oId'];
	}
	// $_tmp = explode('/',$record[1]);
	// $D['dt'] = $_tmp[2] .'-'. $_tmp[0] .'-'. $_tmp[1];
	$D['dt'] = $record[1]; 
	$D['ora'] = intval($record[2]);
	$D['kretaTankorNev'] = $record[3];
	$D['kretaTantargyNev'] = $record[4];
	$D['tipus'] = kisbetus($record[5]);
	$D['perc'] = ($record[6]<=0) ? '':intval($record[6]);
	$D['kretaStatusz'] = $record[7]=='Igen' ? 'igen':'nem';
	$D['status'] = $record[7]=='Igen' ? 'igazolt':'igazolatlan';
	$D['kretaIgazolas'] = $record[8];
// Itt kitalálható lenne pár dolog, egyelőre hagyjuk
	$D['oraAdat'] = getDiakOra($D['diakId'],$D['dt'],$D['ora'],$lr_intezmeny,$lr_naplo);
	if ($D['oraAdat']['oraId']!='') {
	    // $ADAT['betoltendo'][] = ($D);
//	    $ADAT['KRETA2MAYOR']['targyNev'][$D['kretaTankorNev'].':'.$D['kretaTantargyNev']][] = $D['oraAdat']['tankorNev'];
	    $D['tankorId'] = $D['oraAdat']['tankorId'];
	} else $ADAT['hibaCounter']++;
	
	$q = "INSERT INTO hianyzasKreta (diakId,tankorId,kretaDiakNev,oId,dt,ora,kretaTankorNev,kretaTantargyNev,tipus,perc,kretaStatusz,statusz,kretaIgazolas)
					VALUES (%u, IF(%u=0,NULL,%u),'%s','%s','%s',%u,'%s','%s','%s',%u,'%s','%s','%s')";
	$v = array($D['diakId'],
		    $D['tankorId'],
		    $D['tankorId'],
		    $D['kretaDiakNev'],
		    $D['oId'],
		    $D['dt'],
		    $D['ora'],
		    $D['kretaTankorNev'],
		    $D['kretaTargyNev'],
		    $D['tipus'],
		    $D['perc'],
		    $D['kretaStatusz'],
		    $D['statusz'],
		    $D['kretaIgazolas']);
	db_query($q, array('fv'=>'kretaHianyzasImport','values'=>$v),$lr_naplo);
    }

    fclose($file);
    db_close($lr);

?>