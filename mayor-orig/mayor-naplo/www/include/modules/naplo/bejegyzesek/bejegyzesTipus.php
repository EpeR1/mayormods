<?php

    function getBejegyzesTipusok($dt = null) {

	if (is_null($dt)) $dt = date('Y-m-d');

	$q = "SELECT * FROM bejegyzesTipus WHERE tolDt<='%s' AND (igDt IS NULL OR '%s'<=igDt) ORDER BY tipus, fokozat, bejegyzesTipusNev";
	$v = array($dt, $dt);
	return db_query($q, array('fv'=>'getBejegyzesek','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'multiassoc','keyfield'=>'tipus'));

    }

    function jogosultValtoztatas($btId, $jogosult) {

	$q = "UPDATE bejegyzesTipus SET jogosult='%s' WHERE bejegyzesTipusId=%u";
	$v = array(implode(',',$jogosult), $btId);
	return db_query($q, array('fv' => 'jogosultValtoztatas', 'modul'=>'naplo_intezmeny', 'values'=>$v));

    }


    function bejegyzesTipusModositas($btId, $btAdat, $dt) {
	$B = getBejegyzesTipusById($btId);
	$lr = db_connect('naplo_intezmeny');
	db_start_trans($lr);
	if ($B['tolDt'] != $dt) {
	    // A korábbi bejegyzesTipus lemásolása
	    foreach ($btAdat as $key => $value) { $B[$key] = $value; }
	    $q = "INSERT INTO bejegyzesTipus (tipus, fokozat, bejegyzesTipusNev, hianyzasDb, jogosult, tolDt, igDt) VALUES ";
	    if ($igDt == NULL) {
		$q .= "('%s',%u,'%s',%u,'%s','%s',NULL)";
		$v = array($B['tipus'], $B['fokozat'], $B['bejegyzesTipusNev'], $B['hianyzasDb'], implode(',',$B['jogosult']), $dt);
	    } else {
		$q .= "('%s',%u,'%s',%u,'%s','%s','%s')";
		$v = array($B['tipus'], $B['fokozat'], $B['bejegyzesTipusNev'], $B['hianyzasDb'], implode(',',$B['jogosult']), $dt, $B['igDt']);
	    }
	    $bejegyzesTipusId = db_query($q, array('fv'=>'bejegyzesTipusModositas/insert','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'insert'),$lr);
	    if (!$bejegyzesTipusId) { db_rollback($lr); db_close($lr); return false; }
	    // A korábbi bejegyzesTipus lezárása a megelőző nap dátumával
	    $q = "UPDATE bejegyzesTipus SET igDt = '%s' - INTERVAL 1 DAY WHERE bejegyzesTipusId=%u";
	    $v = array($dt, $btId);
	    $r = db_query($q, array('fv'=>'bejegyzesTipusModositas/lezar','modul'=>'naplo_intezmeny','values'=>$v),$lr);
	    if (!$r) { db_rollback($lr); db_close($lr); return false; }
	} else {
	    // A meglévő bejegyzesTipus módosítása
	    $q = "UPDATE bejegyzesTipus SET bejegyzesTipusNev='%s', hianyzasDb=%u, jogosult='%s' WHERE bejegyzesTipusId=%u";
	    $v = array($btAdat['bejegyzesTipusNev'], $btAdat['hianyzasDb'], implode(',', $btAdat['jogosult']), $btId);
	    $r = db_query($q, array('fv'=>'bejegyzesTipusModositas/lezar','modul'=>'naplo_intezmeny','values'=>$v),$lr);
	    if (!$r) { db_rollback($lr); db_close($lr); return false; }
	}
	db_commit($lr);
	db_close($lr);
	return true;
    }

    function fokozatTorles($tipus, $dt) {
	$q ="SELECT bejegyzesTipusId FROM bejegyzesTipus WHERE tipus='%s' AND tolDt<='%s' AND (igDt IS NULL OR '%s'<=igDt) ORDER BY fokozat DESC LIMIT 1";
	$v = array($tipus, $dt, $dt);
	$id = db_query($q, array('fv'=>'fokozatTorles/id','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'value'));
	
	$q = "UPDATE bejegyzesTipus SET igDt = '%s' - INTERVAL 1 DAY WHERE bejegyzesTipusId=%u";
	$v = array($dt, $id);
	return db_query($q, array('fv'=>'fokozatTorles/update','modul'=>'naplo_intezmeny','values'=>$v));
    }

    function ujFokozat($tipus, $dt) {
	$q ="SELECT max(fokozat) FROM bejegyzesTipus WHERE tipus='%s' AND tolDt<='%s' AND (igDt IS NULL OR '%s'<=igDt)";
	$v = array($tipus, $dt, $dt);
	$fokozat = db_query($q, array('fv'=>'ujFokozat/fokozat','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'value'));
	$fokozat++;

	// van-e ilyen fokozat későbbi dátummal?
	$q = "SELECT tolDt FROM bejegyzesTipus WHERE tipus='%s' AND fokozat=%u AND tolDt>'%s' ORDER BY tolDt LIMIT 1";
	$v = array($tipus, $fokozat, $dt);
	$tolDt = db_query($q, array('fv'=>'ujFokozat/tolDt','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'value'));

	if ($tolDt == NULL) {
	    $q = "INSERT INTO bejegyzesTipus (tipus, fokozat, bejegyzesTipusNev, hianyzasDb, jogosult, tolDt, igDt) VALUES ('%s', %u,'%s',NULL, 'admin','%s',NULL)";
	    $v = array($tipus, $fokozat, $tipus.' fokozat', $dt);
	} else {
	    $q = "INSERT INTO bejegyzesTipus (tipus, fokozat, bejegyzesTipusNev, hianyzasDb, jogosult, tolDt, igDt) VALUES ('%s', %u,'%s',NULL, 'admin','%s', '%s' - INTERVAL 1 DAY)";
	    $v = array($tipus, $fokozat, $tipus.' fokozat', $dt, $tolDt);
	}
	return db_query($q, array('fv'=>'ujFokozat/insert','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'insert'));
    }

?>