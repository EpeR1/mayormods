<?php

    function checkTargyBontas() {
	// $q = "SELECT count(*) as db FROM bontasTankor"; ???
	$q = "SELECT count(*) as db FROM kepzesTargyBontas";
	$darab = db_query($q, array('fv'=>'checkTargyBontas','modul'=>'naplo','result'=>'value'));
	return ($darab>0?TRUE:FALSE);
    }

    function getKepzesTargyBontasByOsztalyIds($osztalyIds) {

	if (!is_array($osztalyIds) || count($osztalyIds)==0) return false;

	// tankör- és óraszám adatok
	$q = "SELECT bontasId, tankorId, hetiOraszam 
		FROM kepzesTargyBontas LEFT JOIN bontasTankor USING (bontasId)
		WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") AND tankorId IS NOT NULL
		ORDER BY bontasId";
	$r = db_query($q, array('fv'=>'getKepzesTargyBontasByOsztalyIds/2','modul'=>'naplo','result'=>'indexed','values'=>$osztalyIds));
	foreach ($r as $a) $TO[$a['bontasId']][] = array('tankorId'=>$a['tankorId'], 'hetiOraszam'=>$a['hetiOraszam']);

	// kepzesTargyBontas adatok
	$q = "SELECT kepzesTargyBontas.*,sum(hetiOraszam) as hetiOraszam 
		FROM kepzesTargyBontas LEFT JOIN bontasTankor USING (bontasId)
		WHERE osztalyId IN (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
		GROUP BY bontasId ORDER BY bontasId";
	$r = db_query($q, array('fv'=>'getKepzesTargyBontasByOsztalyIds','modul'=>'naplo','result'=>'indexed','values'=>$osztalyIds));
	if (!is_array($r)) return $r;
	$return = array();
	foreach ($r as $a) $return[$a['osztalyId']][$a['kepzesOratervId']][] = array(
	    'bontasId' => $a['bontasId'], 
	    'targyId' => $a['targyId'], 
	    'hetiOraszam' => $a['hetiOraszam'], 
	    'tankor-oraszam'=>$TO[ $a['bontasId'] ]
	);

	return $return;

    }

    function kepzesOratervSorrend($evfolyamJel, $osztalyIds, $kepzesIds) {
		// ez volt  // group_concat(kepzesTargyBontas.targyId order by kepzesTargyBontas.targyId separator '-') as btStr 
	
	$q = "select 
		kepzesOratervId, tipus, targyNev, kepzesOraterv.targyId as targyId, kepzesOraterv.hetiOraszam as hetiOraszam, osztalyId, kepzesId, szemeszter,
		group_concat(concat_ws('-',kepzesTargyBontas.targyId,tankorId,bontasTankor.hetiOraszam) order by kepzesTargyBontas.targyId,tankorId separator '_') as btStr
		from kepzesOraterv
		left join targy using (targyId) 
		left join kepzesOsztaly using (kepzesId) 
		left join ".__TANEVDBNEV.".kepzesTargyBontas using (kepzesOratervId, osztalyId)
		left join ".__TANEVDBNEV.".bontasTankor using (bontasId) 
		where evfolyamJel='%s' and osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
		and kepzesId in (".implode(',', array_fill(0, count($kepzesIds), '%u')).") 
		group by kepzesOratervId, tipus, kepzesOraterv.targyId, kepzesOraterv.hetiOraszam, osztalyId, kepzesId, szemeszter
		order by tipus, targyNev, kepzesOraterv.targyId, kepzesOraterv.hetiOraszam, btStr, osztalyId, szemeszter";
	$v = mayor_array_join(array($evfolyamJel), $osztalyIds, $kepzesIds);
	$ret = db_query($q, array('fv'=>'kepzesOratervSorrend','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));
	return reindex($ret, array('tipus','targyId','hetiOraszam','btStr'));
    }

    function addBontas($osztalyId, $kepzesOratervId, $targyId=null, $olr=null) {

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) $lr = db_connect('naplo_intezmeny', array('fv'=>'addBontas'));
	else $lr = $olr;

	if ($targyId == '') {
	    $q = "SELECT targyId FROM kepzesOraterv WHERE kepzesOratervId=%u";
	    $targyId = db_query($q, array('fv'=>'addBontas','modul'=>'naplo_intezmeny','result'=>'value','values'=>array($kepzesOratervId)), $lr);
	}
	if ($targyId == '') return false;

	$q = "INSERT INTO ".__TANEVDBNEV.".kepzesTargyBontas (osztalyId, kepzesOratervId, targyId) VALUES (%u, %u, %u)";
	$bontasId = db_query($q, array('fv'=>'addBontas/insert','modul'=>'naplo','result'=>'insert','values'=>array($osztalyId,$kepzesOratervId,$targyId)), $lr);

	// is_resource mysqli esetán nem jó (object)
	if (!$olr) db_close();

	return array(
	    'targyId' => $targyId,
	    'osztalyId' => $osztalyId,
	    'kepzesOratervId' => $kepzesOratervId,
	    'bontasId' => $bontasId
	);

    }

    function delBontas($bontasIds) {
	if (!is_array($bontasIds) || count($bontasIds) == 0) return false;
	$q = "DELETE FROM kepzesTargyBontas WHERE bontasId IN (".implode(',', array_fill(0, count($bontasIds), '%u')).")";
	$r = db_query($q, array('fv'=>'delBontas','modul'=>'naplo','values'=>$bontasIds));
	if ($r) return $bontasIds;
	else return $r;
    }

    function initFromLastYear() {
	// Csak akkor lehet init, ha még nincs bent egyetlen bontás sem az adott kepzes-osztály párokhoz
	$q = "select count(*) from kepzesTargyBontas";
	$db = db_query($q, array('fv'=>'kepzesTargyBontasInit/0','modul'=>'naplo','result'=>'value','values'=>$v));
	if ($db > 0) return true;

	$lr = db_connect('naplo_intezmeny', array('fv'=>'initFromLastYear'));
	// is_resource mysqli esetén nem jó (object)
	if (!$lr) return false;

	$elozoTanevDb = tanevDbNev(__INTEZMENY,__TANEV-1);
	// ha nincs előző tanév, akkor kész az init
	$q = "SELECT COUNT(SCHEMA_NAME) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '%s'";
	$v = array($elozoTanevDb);
	$db = db_query($q, array('fv'=>'kepzesTargyBontasInit/0.5','modul'=>'naplo','result'=>'value','values'=>$v));
	if ($db == 0) return true;

	set_time_limit(300);

	// Az előző év bontásai alapján
	/*
	    Lekérdezzük az előző év bontásainak adatait, melyek az adott kepzes-osztály párhoz tartoztak.
	    bontasId szerint rendezünk, hogy egy bontást csak egyszer vegyünk fel.
	*/
	$q = "select kepzesOratervId,osztalyId,kepzesOraterv.targyId as koTargyId,kepzesTargyBontas.targyId as bontasTargyId,
		    tankorId,bontasTankor.hetiOraszam as bontasOraszam, bontasId,
		    kepzesId,evfolyamJel,szemeszter,kepzesOraterv.hetiOraszam as koOraszam,tipus 
		from ".$elozoTanevDb.".kepzesTargyBontas 
		left join ".$elozoTanevDb.".bontasTankor using (bontasId) 
		left join kepzesOraterv using (kepzesOratervId)
		order by bontasId";
	$r1 = db_query($q, array('fv'=>'kepzesTargyBontasInit/1','modul'=>'naplo_intezmeny','result'=>'indexed'), $lr);

	$elozoTavalyiBontasId = '';
	foreach ($r1 as $r1Adat) {
	    // Ha van a tavalyinak megfelelő képzés-óraterv bejegyzés, akkor hozzunk létre neki bontást
	    if ($r1Adat['tipus'] == 'mintatantervi') {
		    $q = "select kepzesOraterv.*,oraszam from kepzesOraterv 
			left join tankorSzemeszter on tankorSzemeszter.szemeszter=kepzesOraterv.szemeszter and tanev=".__TANEV." and tankorId=%u 
			where kepzesId=%u and kepzesOraterv.szemeszter=%u
			and tipus='%s' and kepzesOraterv.targyId=%u 
			and kepzesOraterv.evfolyamJel='".getKovetkezoEvfolyamJel($r1Adat['evfolyamJel'])."' 
			";
		    $v = array($r1Adat['tankorId'], $r1Adat['kepzesId'], $r1Adat['szemeszter'], $r1Adat['tipus'], $r1Adat['koTargyId']);
	    } else {
		    $q = "select * from kepzesOraterv 
			left join tankorSzemeszter on tankorSzemeszter.szemeszter=kepzesOraterv.szemeszter and tanev=".__TANEV." and tankorId=%u 
			where kepzesId=%u and kepzesOraterv.szemeszter=%u
			and tipus='%s' and kepzesOraterv.targyId is null
			and kepzesOraterv.evfolyamJel='".getKovetkezoEvfolyamJel($r1Adat['evfolyamJel'])."' 
			";
		    $v = array($r1Adat['tankorId'], $r1Adat['kepzesId'], $r1Adat['szemeszter'], $r1Adat['tipus'], );
	    }
	    $r2 = db_query($q, array('fv'=>'kepzesTargyBontasInit/2','modul'=>'naplo_intezmeny','result'=>'record','values'=>$v), $lr);
	    /*
		- Lehet az eredmény üres, ha nincs a képzés óratervben idén folytatása a tárgynak/típusnak
	    */
	    if (!is_array($r2)) continue; 
	    /*
		- Amúgy csak egy rekord lehet... - ekkor a bontást létrehozhatjuk, ha az előző rekord nem ugyanehhez a bontáshoz tartozott...
		    (a hozzárendelt tankört csak akkor vesszük figyelembe, ha egy van belőle...)
	    */
	    if ($elozoTavalyiBontasId != $r1Adat['bontasId']) {
		$r3 = addBontas($r1Adat['osztalyId'], $r2['kepzesOratervId'], $r1Adat['bontasTargyId'], $lr);
		$bontasId = $r3['bontasId'];
		$elozoTavalyiBontasId = $r1Adat['bontasId'];
	    }

	    if (($r1Adat['tankorId'] != '') && ($r1Adat['bontasOraszam'] == $r1Adat['koOraszam']) && ($r2['hetiOraszam'] == $r2['oraszam'])) {
		/*
		    Ha a tavalyi tankör idei évhez is hozzá van rendelve...
			és tavaly megegyezett a tankör óraszáma a bontás óraszámával...
			a tankör idei óraszáma is megegyezik a bontás/képzés-oraterv óraszámával, 
		    akkor a tankör is hozzárendelhető		    
		*/
		$r4 = bontasTankor(array($bontasId), $r1Adat['tankorId'], $r2['hetiOraszam'], $lr);
		if (!$r4) {} // hibakezelés??
	    }
	}
	db_close($lr);
	return true;
    }

    function kepzesTargyBontasInit($osztalyIds, $kepzesIds) {

	// A megadott osztaly megadott képzéseinek aktuális évfolyamának tantárgyhoz tartozó kepzesOraterv bejegyzéseihez felveszünk egy-egy bontást - ha még nincs
	$q = "insert into ".__TANEVDBNEV.".kepzesTargyBontas (osztalyId, kepzesOratervId, targyId) 
		select osztalyId, kepzesOratervId, kepzesOraterv.targyId as targyId 
		from kepzesOraterv left join kepzesOsztaly using(kepzesId) 
		left join ".__TANEVDBNEV.".osztalyNaplo using (osztalyId)
		left join ".__TANEVDBNEV.".kepzesTargyBontas using (osztalyId, kepzesOratervId) 
		where osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
		and kepzesId in (".implode(',', array_fill(0, count($kepzesIds), '%u')).") 
		and kepzesOraterv.evfolyamJel=osztalyNaplo.evfolyamJel
		and bontasId is null and kepzesOraterv.targyId is not null";
	$v = mayor_array_join($osztalyIds, $kepzesIds);
	return db_query($q, array('fv'=>'kepzesTargyBontasInit','modul'=>'naplo_intezmeny','result'=>'affected rows','values'=>$v));

    }

    function bontasTankor($bontasIds, $tankorId, $hetiOraszam, $olr = null) {

	if (!is_array($bontasIds) || count($bontasIds) == 0 || $tankorId == '' || $hetiOraszam <= 0) {
	    $_SESSION['alert'][] = 'message:empty_field:bontasTankor';
	    return false;
	}
	// is_resource mysqli esetén nem jó (object)
	if (!$olr) $lr = db_connect('naplo_intezmeny', array('fv'=>'bontasTankor'));
	else $lr = $olr;

    	db_start_trans($lr);
	$ok=true;
	/* 
		Óraszám ellenőrzés - kellene itt is? 
		    - tankör óraszáma: tankorBontás óraszám <= tankorSzemeszer óraszám
		    - bontás óraszáma: bontás-óraszám <= kepzesOraterv óraszám
		    - tipus szerint a tankörnek csak egyféle óraszáma lehet
	*/

	// Tankor-osztály hozzárendelés
	$q = "insert into ".__INTEZMENYDBNEV.".tankorOsztaly (tankorId, osztalyId)
		select distinct %u as tankorId, kepzesTargyBontas.osztalyId as osztalyId from ".__TANEVDBNEV.".kepzesTargyBontas 
		left join ".__INTEZMENYDBNEV.".tankorOsztaly on kepzesTargyBontas.osztalyId=tankorOsztaly.osztalyId and tankorId=%u 
		where bontasId in (".implode(',', array_fill(0, count($bontasIds), '%u')).") and tankorId is null";
	$v = $bontasIds; array_unshift($v, $tankorId, $tankorId);
	$r = db_query($q, array('fv'=>'bontasTankor/1','modul'=>'naplo','result'=>'affected rows','values'=>$v), $lr);
	if ($r === false) { db_rollback($lr, 'tankör-osztály hozzárendelés'); if ($olr) db_close($lr); return false; } // is_resource mysqli esetén nem jó (object)
	// bontasTankor rögzítése
	foreach ($bontasIds as $bontasId) {
	    $q = "insert into ".__TANEVDBNEV.".bontasTankor (bontasId, tankorId, hetiOraszam) values (%u, %u, %f)";
	    $v = array($bontasId, $tankorId, $hetiOraszam);
	    $r = db_query($q, array('fv'=>'bontasTankor/bt','modul'=>'naplo','values'=>$v), $lr);
	    if ($r === false) { db_rollback($lr, 'tankörnév hozzárendelés'); if (!$olr) db_close($lr); return false; } // is_resource mysqli esetén nem jó (object)
	}
	db_commit($lr);

	$r1 = setTankorNev($tankorId, $tankorNevExtra=null, $lr);
	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);

	return $r1; 
    }


?>