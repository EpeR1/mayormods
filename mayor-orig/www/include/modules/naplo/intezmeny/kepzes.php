<?php

    function ujKepzes($kepzesNev, $tanev, $osztalyJellegId) {

	$q = "INSERT INTO kepzes (kepzesNev,tanev, osztalyJellegId) VALUES ('%s', %u, %u)";
	$v = array($kepzesNev, $tanev, $osztalyJellegId);
	return db_query($q, array('fv' => 'ujKepzes', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v));

    }

    function kepzesEles($kepzesId, $kepzesEles) {

	$q = "UPDATE kepzes SET kepzesEles=%u WHERE kepzesId =%u";
	$v = array($kepzesEles,$kepzesId);
	return db_query($q, array('fv' => 'kepzesEles', 'modul' => 'naplo_intezmeny', 'result' => 'update', 'values' => $v));

    }

    function kepzesModositas($ADAT) {

	//$ADAT eredeti paraméterezése: $kepzesId, $kepzesNev, $tanev, $osztalyJellegId, $osztalyIds, $delOsztalyIds
	extract($ADAT);

	// Van-e ilyen képzés
	$q = "SELECT COUNT(*) AS db FROM kepzes WHERE kepzesId = %u";
	$v = array($kepzesId);
	$db = db_query($q, array('modul'=> 'naplo_intezmeny','fv'=>'kepzesModositas','values'=>$v, 'result'=>'value'));

	if ($db != 1) {
	    $_SESSION['alert'][] = 'message:wrong_data:hibás képzés azonosító:'.$kepzesId;
	    return false;
	}

	if (isset($osztalyJellegId) && $osztalyJellegId>0) {
	    $q = "UPDATE kepzes SET kepzesNev='%s',tanev=%u,osztalyJellegId=%u WHERE kepzesId=%u";
	    $v = array($kepzesNev,$tolTanev,$osztalyJellegId,$kepzesId);
	}
	db_query($q, array('modul'=> 'naplo_intezmeny','fv'=>'kepzesModositas','values'=>$v));

	// TOROLNI NEM LEHET csak, ha egyetlen osztály-tag sincs hozzárendelve az adott képzéshez AZ ADOTT TANÉVBEN...
	if (count($delOsztalyIds) > 0) {
	    foreach ($delOsztalyIds as $osztalyId) {
		$q = "SELECT COUNT(*) FROM kepzesDiak LEFT JOIN osztalyDiak USING (diakId) WHERE kepzesId=%u AND osztalyId=%u 
			AND tolDt<='".$_TANEV['zarasDt']."' AND (igDt IS NULL OR igDt>='".$_TANEV['kezdesDt']."')
			AND beDt<='".$_TANEV['zarasDt']."' AND (kiDt IS NULL OR kiDt>='".$_TANEV['kezdesDt']."')";
		$db = db_query($q, array('fv'=>'kepzesModositas/del-osztaly#1','modul'=>'naplo_intezmeny','result'=>'value','values'=>array($kepzesId, $osztalyId)));
		if ($db == 0) {
		    $q = "DELETE FROM kepzesOsztaly WHERE kepzesId=%u AND osztalyId=%u";
		    db_query($q, array('fv' => 'kepzesModositas/osztályhozzárendelés törlése', 'modul' => 'naplo_intezmeny', 'values' => array($kepzesId,$osztalyId)));
		} else {
		    $_SESSION['alert'][] = 'message:wrong_data:Az osztály hozzárendelés nem törölhető! '.$db.' db tanuló az osztályból hozzá van rendelve ehhez a képzéshez.';
		}
	    }	
	}
	if (count($osztalyIds)>0) {
		$q = "REPLACE INTO kepzesOsztaly (kepzesId,osztalyId) VALUES (".implode("),(", array_fill(0, count($osztalyIds), '%u, %u')).")";
		$v = array();
		for ($i = 0; $i < count($osztalyIds); $i++) array_push($v, $kepzesId, $osztalyIds[$i]);
		db_query($q, array('fv' => 'kepzesModositas/osztályhozzárendelés', 'modul' => 'naplo_intezmeny', 'values' => $v));
	}

    }

?>
