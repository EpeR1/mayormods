<?php

    if (_RIGHTS_OK !== true) die();


    if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/share/net/upload.php');
        require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/targy.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/osztaly.php');

        require_once('include/modules/naplo/intezmeny/tankor.php');
        require_once('include/modules/naplo/share/tankorModifier.php');
        require_once('include/modules/naplo/share/tankorDiakModifier.php');


	$selectedTargyId = $ADAT['selectedTargyId'] = readVariable($_POST['selectedTargyId'],'id');
	$selectedTanarId = $ADAT['selectedTanarId'] = readVariable($_POST['selectedTanarId'],'id');

	// dump(get_defined_constants(TRUE));

/*
        if ($action == 'upload') {
	    // ez csak képek feltöltésére jó sajnos:
            // mayorFileUpload(array('subdir'=>_DATADIR.'/','filename'=>'ttfimport.tsv'));
        } elseif (isset($_POST['fileName']) && $_POST['fileName'] != '') {

	}
*/

	$q = "SELECT szemeszterId FROM szemeszter WHERE tanev=%u";
	$v = array(__TANEV);
	$ADAT['szemeszterek'] = db_query($q, array('fv' => 'pre', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'idonly'), $lr);

	// DIÁKOK
	$DIAKOK = getDiakok(array('extraAttrs'=>'oId'));
	for ($i=0; $i<count($DIAKOK); $i++) { 
	    if ($DIAKOK[$i]['oId']!='') {
		$OID2ID[$DIAKOK[$i]['oId']] = intval($DIAKOK[$i]['diakId']); 
	    } else {
		$ADAT['bug']['diak'][] = $DIAKOK[$i]['oId'];
	    }
	}
	// TÁRGYAK
	$TARGY = getTargyak();
	for ($i=0; $i<count($TARGY); $i++) {
	    $T = $TARGY[$i];
	    if ($T['kretaTargyNev']!='') {
		$KRETATARGYNEV2TARGYID[$T['kretaTargyNev']] = intval($T['targyId']); 
	    }
	}
	// OSZTÁLYOK
	//$OSZTALY = getOsztalyok();
	//dump($OSZTALY);
	//for ($i=0; $i<count($OSZTALY); $i++) {
	//    $O = $OSZTALY[$i];
	//    $OSZTALYNEV2ID[ $OSZTALY ];
	//}
	
	// TODO valami szótár, nem tudjuk kitalálni
	$ADAT['kulcsertektar']['osztalyjel2id'] = $OSZTALYJEL2ID = array(
	    '07.A' => 124,
	    '07.B' => 125,
	    '08.A' => 117,
	    '08.B' => 118,
	    '09.A' => 113,
	    '09.B' => 114,
	    '09.C' => 126,
	    '09.D' => 127,
	    '09.ny' => 128,
	    '09.E' => 119,
	    '10.A' => 105,
	    '10.B' => 106,
	    '10.C' => 120,
	    '10.D' => 121,
	    '10.E' => 122,
	    '11.A' => 95,
	    '11.B' => 96,
	    '11.C' => 110,
	    '11.D' => 111,
	    '11.E' => 107,
	    '12.A' => 90,
	    '12.B' => 91,
	    '12.C' => 108,
	    '12.D' => 109,
	    '12.E' => 99,
	);


	$fn = fopen(_DATADIR.'/'."ttfimport.tsv","r");
	while(! feof($fn))  {
	    /*
		0 => string '12.C' (length=4)
		1 => string '12.c.m+t' (length=8)
        	2 => string 'dráma' (length=6)
        	3 => string '1,00' (length=4)
        	4 => string 'Széles Zsuzsanna' (length=17)
        	5 => string 'Nem' (length=3)
	    */
	    $line = (fgets($fn));
	    if (ord($line[0]) == 32) $line = "\t".trim($line);
	    else $line = trim($line);
	    $result = explode("\t",$line);
	    $ADAT['ttf'][] = $result;
	}
	fclose($fn);

	# Osztályba Járó Tanulók:

	# Osztály	Név	Oktatási azonosító
	# 07.A	Ambrus Dániel	72644951895
	# 07.A	Apjok Balázs	72719658348
	#	tankorosztaly kitalálás:
	#	DIÁK1 -(import)-> kretaOsztalyNev -(osztalyNaplo)-> osztalyId

	$fn = fopen(_DATADIR.'/'."osztalyba_jaro_tanulok.tsv","r");
	while(! feof($fn))  {
	    $line = (fgets($fn));
	    if (ord($line[0]) == 32) $line = "\t".trim($line);
	    else $line = trim($line);
	    $result = explode("\t",$line);
	    // $ADAT['osztalyDiak'][] = $result;
	    // osztalyNev --> osztalyId ???
	    $ADAT['osztalyDiak'][$OSZTALYJEL2ID[$result[0]]][] = array(
		'diakNev' => $result[1],
		'oId' => $result[2],
		'diakId' => ($OID2ID[$result[2]]>0) ? intval($OID2ID[$result[2]]) : null,
		'osztalyId' => $OSZTALYJEL2ID[$result[0]],
		'osztalyJel' => $result[0]
	    );
	    $OID2OSZTALYJEL[$result[2]] = $OSZTALYJEL2ID[$result[0]];
	    #### Töltsük fel csoportként az egészosztályt is	    
	    $osztalyId = $OSZTALYJEL2ID[$result[0]];
	    $csoportNev = $result[0];
	    $oId = $result[2];
	    if (!in_array($osztalyId, $CSOPORTADAT[$csoportNev]['osztalyok'])) {
		$CSOPORTADAT[$csoportNev]['osztalyok'][] = $osztalyId;
	    }
	    $CSOPORTADAT[$csoportNev]['diakIds'][] = $OID2ID[$oId];
	    $CSOPORTADAT[$csoportNev]['diakOIds'][] = $oId;
	    $CSOPORTADAT[$csoportNev]['diakNevsor'][] = $result[1];
	    if ($OID2ID[$oId] =='') {
		$ADAT['bug']['diak'][] = $oId.$line;
	    }

	}
	fclose($fn);

	########################################################£

	#Csoportba Járó Tanulok:

#0 => string '99.9.énekkar-C' (length=15)
#1 => string 'Andrássy Blanka Éva' (length=21)
#2 => string '71614703894' (length=11)
#3 => string '12.C' (length=4)
	
	$fn = fopen(_DATADIR.'/'."csoportba_jaro_tanulok.tsv","r");
	while(! feof($fn))  {
	    $line = (fgets($fn));
	    if (ord($line[0]) == 32) $line = "\t".trim($line);
	    else $line = trim($line);
	    $result = explode("\t",$line);
	    // $ADAT['osztalyDiak'][] = $result;
	    // osztalyNev --> osztalyId ???
	    $osztalyId = $OSZTALYJEL2ID[$result[3]];
	    $csoportNev = $result[0];
	    $oId = $result[2];
	    if (!in_array($osztalyId, $CSOPORTADAT[$csoportNev]['osztalyok'])) {
		$CSOPORTADAT[$csoportNev]['osztalyok'][] = $osztalyId;
	    }
	    $CSOPORTADAT[$csoportNev]['diakIds'][] = $OID2ID[$oId];
	    $CSOPORTADAT[$csoportNev]['diakOIds'][] = $oId;
	    if ($OID2ID[$oId] =='') {
		$ADAT['bug']['diak'][] = $oId.serialize($line);
	    }
	    $CSOPORTADAT[$csoportNev]['diakNevsor'][] = $result[1];
	}
	fclose($fn);

	$ADAT['csoportAdat'] = $CSOPORTADAT;

	########################################################£
	$CSOPORT = array();
	for ($i=0; $i<count($ADAT['ttf']); $i++) {
	    if ($ADAT['ttf'][$i][0]!='') $CSOPORT[] = $ADAT['ttf'][$i][0];
	    if ($ADAT['ttf'][$i][1]!='') $CSOPORT[] = $ADAT['ttf'][$i][1];
	}
	$ADAT['csoportok'] = array_unique($CSOPORT);
	if (count($ADAT['csoportok'])>0) {
	    foreach ($ADAT['csoportok'] AS $index => $csoportNev) {
		if ($csoportNev!='') {
		    $q = "INSERT IGNORE INTO csoport (csoportNev) VALUES ('%s')";
		    $v = array($csoportNev);
		    db_query($q, array('fv' => 'csoportinsert', 'modul' => 'naplo', 'values' => $v), $lr);
		}
	    }
	}
	// csoportid match

	// get csoportok 
	$TANKORCSOPORT = getTankorCsoport(__TANEV);
	for ($i=0; $i<count($TANKORCSOPORT); $i++) {
	    $CSOPORT2ID[$TANKORCSOPORT[$i]['csoportNev']] = $TANKORCSOPORT[$i]['csoportId'];
	}
	$TANKORCSOPORTID2CSOPORTNEV = array_flip($CSOPORT2ID);


	// tanarId kitalálós
	$TANAROK = getTanarok( array('extraAttrs'=>'kretaNev'));
//	dump($TANAROK);
	for ($i=0; $i<count($TANAROK); $i++) {
	    if ($TANAROK[$i]['tanarNev']!='')$TANAR2ID[$TANAROK[$i]['tanarNev']] = $TANAROK[$i]['tanarId'];
	    if ($TANAROK[$i]['kretaNev']!='') $TANAR2ID[$TANAROK[$i]['kretaNev']] = $TANAROK[$i]['tanarId'];
	}

//	dump($TANAR2ID);



####################################################################################


	if ($action == 'do') {
	    for ($i=0; $i<count($_POST['ujTankor']); $i++) {
		list($csoportId,$tanarId,$osztalyIds,$targyId,$szemeszter_oraszam,$csoportNev) = explode(':####:',$_POST['ujTankor'][$i]);
		$_osztalyIds = explode(',',$osztalyIds);
		// amugy: $csoportNev = $TANKORCSOPORTID2CSOPORTNEV[$csoportId];

		if ($csoportId>0 && $tanarId>0 && count($_osztalyIds)>0 && $targyId>0) {
		    // TODO létre kell hozni a tankört majd beléptetni a csoportId - be és a tankortanárba (lásd később)
		    // 1. új tankör
			$UJTANKOR = array(
			    'tanev'=>__TANEV,
			    'targyId'=>$targyId,
			    'felveheto'=>0,
			    'min'=>0,
			    'max'=>0,
			    'kovetelmeny'=>'féljegy',
			    'tankorTipusId'=>1,
			    'osztalyok'=>$_osztalyIds,
			    'szemeszterek' => $ADAT['szemeszterek'],
			);
			$SZO = explode('&',$szemeszter_oraszam);
			for ($j=0; $j<count($SZO); $j++) {
			    list($k,$v) = explode('=',$SZO[$j]);
			    $UJTANKOR[$k] = $v;
			}

			$tankorId = ujTankor($UJTANKOR);
			setTankorNev($tankorId, '('.$csoportNev.')', null);
		    // 2. tankorcsoport
			$q = "insert ignore into tankorCsoport (tankorId,csoportId) VALUES (%u,%u)";
			$v = array($tankorId,$csoportId);
			db_query($q, array('fv' => 'csoportinsert', 'modul' => 'naplo', 'values' => $v));

		    // 3. tanár
			tankorTanarModosit($tankorId, $tanarId, array('tanev'=>__TANEV,'tanevAdat'=>$_TANEV, 'tolDt'=>$_TANEV['kezdesDt'], 'igDt'=>$_TANEV['zarasDt']));
		    
		    // 4. tagok
			// setTankorNevByDiakok($tankorId, $tankorNevExtra = null, $olr = null);  // ha a nevsorok szinkronban vannak
			// setTankorNev($tankorId, null, null);

			for ($j=0; $j<count($CSOPORTADAT[$csoportNev]['diakIds']); $j++) {
			    $_diakId = $CSOPORTADAT[$csoportNev]['diakIds'][$j];
			    if ($_diakId>0) {
				$UJTANKORDIAK = array(
					'tankorId'=>intval($tankorId),
					'tolDt'=>$_TANEV['kezdesDt'],
					'igDt'=>$_TANEV['zarasDt'],
					'jovahagyva'=>1,
					'diakId' => intval($_diakId)
				);
				tankorDiakFelvesz($UJTANKORDIAK);
			    }
			}
			setTankorNev($tankorId, '('.$csoportNev.')', null);
		}
	    }
	    for ($i=0; $i<count($_POST['tankor2csoport']); $i++) {

		    list($csoportId,$tanarId,$tankorId,$oraszam) = explode(':####:',$_POST['tankor2csoport'][$i]);
		    if ($csoportId>0 && $tanarId>0 && $tankorId>0 && $oraszam>0) {
			$q = "insert ignore into tankorCsoport (tankorId,csoportId) VALUES (%u,%u)";
			$v = array($tankorId,$csoportId);
			db_query($q, array('fv' => 'csoportinsert', 'modul' => 'naplo', 'values' => $v));

			// $q = "insert into tankorTanar (tankorId,tanarId,beDt,kiDt) VALUES (%u,%u,'%s','%s')";
			// $v = array($tankorId,$tanarId,$_TANEV['kezdesDt'],$_TANEV['zarasDt']);
			// db_query($q, array('fv' => 'tankorTanarInsert', 'modul' => 'naplo_intezmeny', 'values' => $v));
			tankorTanarModosit($tankorId, $tanarId, array('tanev'=>__TANEV,'tanevAdat'=>$_TANEV, 'tolDt'=>$_TANEV['kezdesDt'], 'igDt'=>$_TANEV['zarasDt']));

			// Óraszám update
			$q = "update tankorSzemeszter set oraszam=%f where tankorId=%u AND tanev=%u";
			$v = array(floatval($oraszam),intval($tankorId),__TANEV);
			db_query($q, array('fv' => 'csoportinsert', 'modul' => 'naplo_intezmeny', 'values' => $v));
			
			// A csoporttagság frissítése
			$csoportNev = $TANKORCSOPORTID2CSOPORTNEV[$csoportId];
			for ($j=0; $j<count($CSOPORTADAT[$csoportNev]['diakIds']); $j++) {
			    $_diakId = $CSOPORTADAT[$csoportNev]['diakIds'][$j];
			    if ($_diakId>0) {
				$UJTANKORDIAK = array(
					'tankorId'=>intval($tankorId),
					'tolDt'=>$_TANEV['kezdesDt'],
					'igDt'=>$_TANEV['zarasDt'],
					'jovahagyva'=>1,
					'diakId' => intval($_diakId)
				);
				tankorDiakFelvesz($UJTANKORDIAK);
			    }
			}
			setTankorNev($tankorId, '('.$csoportNev.')', null);

		    }

	    }
	

	}




#########################################################

    $TANKORIDS = getTankorByTanev(__TANEV, array('result'=>'idonly'));

    for ($i=0; $i<count($TANKORIDS); $i++) {
	$_tankorId = $TANKORIDS[$i];
	$tmp = getTankorDiakjai($_tankorId);
	$ADAT['tankorDiak'][$_tankorId] = $tmp['idk'];
    }
    




####################################################################################################################################£3

	// DIÁK1 -(import)-> kretaCsoportNev -(csoport)-> csoportId
//	for ($i=0; $i<count($ADAT['osztalyDiak']); $i++) {
//	    $D = $ADAT['osztalyDiak'][$i];
//	    if ($OID2ID[$D['oId']]>0) $ADAT['osztalyDiak']['diakId'] = $OID2ID[$D['oId']];
//	    else echo 'nincs ilyen diakId'. $D['oId'];
//	}

//	dump($ADAT['osztalyDiak']);

## az összegyűjtott adatok alapján az végeredmény
/*
array (size=11)
  0 => string '' (length=0)
  1 => string '12.6.m.va3' (length=10)
  2 => string 'matematika' (length=10)
  3 => string '3,00' (length=4)
  4 => string 'Volf Annamária' (length=15)
  5 => string 'Nem' (length=3)
  'oraszam' => float 3
  'targyId' => int 6
  'tankorCsoport' => 
    array (size=1)
      0 => string '4368' (length=4)
  'tanarId' => string '131' (length=3)
  'tankorCn' => string ':4368&6&131&3' (length=13)
*/

	for ($i=0; $i<count($ADAT['ttf']); $i++) {
	    $ADAT['ttf'][$i]['oraszam'] = floatval(str_replace(',','.',$ADAT['ttf'][$i][3]));
	    if ($KRETATARGYNEV2TARGYID[$ADAT['ttf'][$i][2]]<=0) {
		$_SESSION['alert'][] = 'info:nincs_megfelelo_kreta_targynev:'.$ADAT['ttf'][$i][2];
		$ADAT['bug']['targy'][] = $ADAT['ttf'][$i][2];
	    }
	    $ADAT['ttf'][$i]['targyId'] = $KRETATARGYNEV2TARGYID[$ADAT['ttf'][$i][2]];
	    $_tmpCsoportId = null;
	    if ($CSOPORT2ID[$ADAT['ttf'][$i][0]]>0) {
		$_tmpCsoportId = $ADAT['ttf'][$i]['tankorCsoport'][] = $CSOPORT2ID[$ADAT['ttf'][$i][0]];
		$_tmpCsoportNev = $ADAT['ttf'][$i][0];
	    }
	    if ($CSOPORT2ID[$ADAT['ttf'][$i][1]]>0) {
		$_tmpCsoportId = $ADAT['ttf'][$i]['tankorCsoport'][] = $CSOPORT2ID[$ADAT['ttf'][$i][1]];
		$_tmpCsoportNev = $ADAT['ttf'][$i][1];
	    }
	    $ADAT['ttf'][$i]['tanarId'] = $TANAR2ID[$ADAT['ttf'][$i][4]]; // itt tárgyid szerint még lehet jobban szűrni (pl PLspa PLmat esete)
	    $ADAT['ttf'][$i]['csoportId'] = $_tmpCsoportId; // ez legyen a default, a második erősebb
	    $ADAT['ttf'][$i]['csoportNev'] = $_tmpCsoportNev; // ez legyen a default, a második erősebb

	    # tankorCn : csoportId targyId tanarId oraszam
	    # ha megváltozik az óraszám, új tankör jönne létre

	    $ADAT['ttf'][$i]['tankorCn'] = ':'.implode('&',array('csoportId'=>$_tmpCsoportId,'targyId'=>$KRETATARGYNEV2TARGYID[$ADAT['ttf'][$i][2]],'tanarId'=>$TANAR2ID[$ADAT['ttf'][$i][4]],'oraszam'=>$ADAT['ttf'][$i]['oraszam']));

	    // ha van ilyen tankorCn, akkor skip és OK
	    // $q = "SELECT tankorId FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)";

	    // ALTERNATÍV ELLENŐRZÉS
	    # ha van olyan tankör, aminek a tárgya, tanára és óraszáma és csoportja egyezik, akkor nem csinálunk semmit (már felvettük)
	    #

	    // ha nincs ilyen tankorCn

	    ## LIMITÁCIÓ (első félév!)

	    // nem lefedett eset: ha már fel van véve a tankör nagyon jól, de nem ezzel a scripttel, akkor nem fogjuk megtalálni
	    // BPné kézi esete

	    if ($ADAT['ttf'][$i]['tanarId'] == $selectedTanarId || $ADAT['ttf'][$i]['targyId'] == $selectedTargyId) {

		$_D = $ADAT['ttf'][$i];
		$q = "select *,tankorSzemeszter.tankorId AS tankorId from tankorSzemeszter 
LEFT JOIN tankor USING (tankorId)
LEFT JOIN tankorTanar ON (tankorTanar.tankorId=tankor.tankorId AND beDt<=NOW() AND (kiDt is null or kiDt>=NOW())) 
LEFT JOIN ".__TANEVDBNEV.".tankorCsoport ON (tankor.tankorId = tankorCsoport.tankorId)
LEFT JOIN ".__TANEVDBNEV.".csoport USING (csoportId)
WHERE tanev=%u AND szemeszter=%u AND targyId=%u AND oraszam=%f AND tanarId=%u
AND csoportNev = '%s'	
GROUP BY tankor.tankorId";
		$v = array(__TANEV,1,$_D['targyId'],$_D['oraszam'],$_D['tanarId'],$_D['csoportNev']);
		$r = db_query($q,array('modul'=>'naplo_intezmeny','values'=>$v,'result'=>'indexed'));
		if (count($r) == 1) {
		    // echo 'OK, a talált tankör';
		    $_D['tankorId'] = $r[0]['tankorId'];
		    $_D['action'] = 'done';
		} elseif (count($r)>1) {
		    // ha van legalább egy találat, akkor kihagyjuk ezt a sort, esetleg megjegyezhetjuk, hogy neki már ezek a párjai lehetnek/vannak
		    // echo 'több találat van';
		    // specifikálni kell tovább (pl. van csoporthozzárendelés?) ugyanolyan?
		    $_D['action'] = 'tankorHozzarendel';
		    // ha nincs
		    for ($j=0; $j<count($r); $j++) {
			$_D['displayTankor'][] = $r[$j];
		    }
		} else {
		    // Ha nincs találat, ezek a tankörök felelhetnek még meg:
		    //  nincs még tanár hozzárendelve:
		    // select * from tankorSzemeszter LEFT JOIN tankor USING (tankorId) LEFT JOIN tankorTanar ON (tankorTanar.tankorId=tankor.tankorId AND beDt<=NOW() AND (kiDt is null or kiDt>=NOW())) WHERE tanev=2019 AND szemeszter=1 AND targyId=6 AND oraszam=5 AND tanarId IS NULL GROUP BY tankor.tankorId ;
		    $_M = array();
		    if (is_array($ADAT['csoportAdat'][$_D[0]]['osztalyok']) && is_array($ADAT['csoportAdat'][$_D[1]]['osztalyok'])) {
			$_M = array_merge(
			    $ADAT['csoportAdat'][$_D[1]]['osztalyok'],
			    $ADAT['csoportAdat'][$_D[1]]['osztalyok']
			);
		    } elseif (is_array($ADAT['csoportAdat'][$_D[1]]['osztalyok'])) {
			$_M = $ADAT['csoportAdat'][$_D[1]]['osztalyok'];
		    } elseif (is_array($ADAT['csoportAdat'][$_D[0]]['osztalyok'])) {
			$_M = $ADAT['csoportAdat'][$_D[0]]['osztalyok'];
		    }

		    $_M = $ADAT['csoportAdat'][$_D['csoportNev']]['osztalyok'];

		    if (!is_array($_M) || count($_M)==0 || is_null($_M)) {
			$_M = array(0);
			$_SESSION['alert'][] = 'info:nincsenek osztályok:'.serialize($_D);
		    }
		    $q = "select *,tankorSzemeszter.tankorId AS tankorId from tankorSzemeszter 
LEFT JOIN tankor USING (tankorId) 
LEFT JOIN tankorTanar ON (tankorTanar.tankorId=tankor.tankorId AND beDt<=NOW() AND (kiDt is null or kiDt>=NOW()))
LEFT JOIN tankorOsztaly ON (tankor.tankorId = tankorOsztaly.tankorId)
WHERE tanev=%u AND szemeszter=%u AND targyId=%u 
AND oraszam=%f 
AND tanarId IS NULL 
AND osztalyId IN (".implode(',',$_M).")
GROUP BY tankor.tankorId ORDER BY tankorNev";

		    $v = array(__TANEV,1,$_D['targyId'],$_D['oraszam']);
		    $r2 = db_query($q,array('modul'=>'naplo_intezmeny','values'=>$v,'result'=>'indexed'));
		    if (count($r2) >= 1) {
			// mit tegyünk? kézzel fvesszük fel? tagokat ellenőrzünk?
			$_D['action'] = 'tankorHozzarendel2';
			for ($j=0; $j<count($r2); $j++) {
			    $_D['displayTankor'][] = $r2[$j];
			}
		    } else {
			    $q = "select *,tankorSzemeszter.tankorId AS tankorId from tankorSzemeszter 
LEFT JOIN tankor USING (tankorId) 
LEFT JOIN tankorTanar ON (tankorTanar.tankorId=tankor.tankorId AND beDt<=NOW() AND (kiDt is null or kiDt>=NOW()))
WHERE tanev=%u AND szemeszter=%u AND targyId=%u
AND oraszam>=%f
AND tanarId IS NULL 
GROUP BY tankor.tankorId ORDER BY tankorNev";

			$v = array(__TANEV,1,$_D['targyId'] ,$_D['oraszam']-10 ); // óraszám igazán gyenge feltételként
			$r3 = db_query($q,array('modul'=>'naplo_intezmeny','values'=>$v,'result'=>'indexed'));

			if (count($r3) >= 1) {
			    $_D['action'] = 'tankorHozzarendel3';
			    for ($j=0; $j<count($r3); $j++) {
			    $_D['displayTankor'][] = $r3[$j];
			    }
			} else {
			    $_D['action'] = 'createTankor';
			}
		    }
		}
		$ADAT['records'][] = $_D;
		// dump($_D);
	    }

	}

	// dump($ADAT);
	// érdemes lenne írni egy csoportszinkronizáló scriptet, 
	// ami a csoportban levő legbővebb halmazúvá teszi a névsorokat
    }

/*
Csoportba Járó Tanulók:

Csoport neve	Név	Oktatási azonosító	Osztály
99.9.énekkar-C	Allardyce Lilla Rose	71624405564	12.C
99.9.énekkar-C	Andrássy Blanka Éva	71614703894	12.C
99.9.énekkar-C	Árva Janka	72463346174	09.C

Osztályba Járó Tanulók:

Osztály	Név	Oktatási azonosító
07.A	Ambrus Dániel	72644951895
07.A	Apjok Balázs	72719658348
07.A	Bärnkopf Janka Katalin	72660367200

Tantárgyfelosztás:

Osztály	Csoport	Tantárgy	Óraszám	Tanár	Összevont óra
12.C	12.c.m+t	dráma	1,00	Széles Zsuzsanna	Nem
10.E		fizika	2,00	Antal Erzsébet	Nem
10.2.n.djpr	német nyelv	4,00	Dobrosi-Jelinek Piroska Rita	Nem
12.2.n.djpr	német nyelv	3,00	Dobrosi-Jelinek Piroska Rita	Nem
11.D		dráma	1,00	Dobrosi-Jelinek Piroska Rita	Nem

*/


//   $TOOL['tanevSelect'] = array('tipus'=>'cella','paramName' => 'tanev',
//        'tervezett' => true,
//        'post' => array('mkId','targyId','tankorId'));
//    $TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post' => array('tanev'));
    $TOOL['targySelect'] = array('tipus'=>'cella', 'paramName' => 'selectedTargyId');
//    $TOOL['diakSelect'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array());
    $TOOL['tanarSelect'] = array('tipus'=>'cella','paramName'=>'selectedTanarId');
//    $TOOL['tankorSelect'] = array('tipus' => 'cella','paramName' => 'tankorId', 'post' => array('tanev', 'mkId', 'targyId'));
//    $TOOL['tanevLapozo'] = array('tipus' => 'sor', 'paramName' => 'tanev', 'post' => array('mkId', 'targyId', 'tankorId'), 'tanev' => __TANEV);
    getToolParameters();

?>