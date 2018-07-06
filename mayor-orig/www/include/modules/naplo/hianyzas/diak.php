<?php

    function napiHianyzasBeiras($dt,$diakId,$SET=array('tipus'=>'hiányzás','statusz'=>'igazolatlan', 'igazolas'=>'')) {
	    global $napiMinOra, $napiMaxOra;

	    $q = "SELECT * FROM hianyzas WHERE diakId=%u AND dt='%s'";
	    $RES = db_query($q, array('fv' => 'napiHianyzasBeiras', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($diakId, $dt)));

	    $T = $diakTANKOROK = getTankorByDiakId($diakId, __TANEV, array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt, 'result'=>'csakid'));

	    $tmpOrak = getOrak($T,array('tolDt'=>$dt,'igDt'=>$dt,'csakId'=>false));
	    $diakORAK = $tmpOrak['orak'];

	    if (!is_array($diakORAK)) {
		$_SESSION['alert'][] = ':nincs_oraja:';
		return false;
	    }
	    foreach ($diakORAK[$dt] as $_ora=>$ORA) {
		$_diakFmTankorIdk = getTankorDiakFelmentes($diakId, __TANEV, array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt, 'nap'=>date('w',strtotime($dt)), 'ora'=>$_ora, 'result'=>'csakid'));
		foreach($ORA as $_tankorId => $_OA) {
		    if ( in_array($_tankorId,$_diakFmTankorIdk) === true ) {
			continue;
		    }
		    $diakORAIDK[] = $_OA['oraId'];
		    $ORAK[$_OA['oraId']] = $_OA;
		}
	    }

	    $MODOSITANDO = $eddigORAIDK = array();
	    $error = false;
	    for ($i=0; $i<count($RES); $i++) {
		$_tipus = $RES[$i]['tipus'];
		$_statusz = $RES[$i]['statusz'];
		$_hid = $RES[$i]['hianyzasId'];
		$_oraId = $RES[$i]['oraId'];
		if ($SET['tipus']=='hiányzás' && in_array($_tipus,array('felszerelés hiány','felmentés','egyenruha hiány'))) {
		    $_SESSION['alert'][] = '::regisztrált F/f/e-betűs bejegyzése van erre a napra! Egyeztetés szükséges!';
		    $error = true;
		}
		// modositani kell, ha eddig nem az volt, ezeket a hianyzasIdket:
		// ha szeretnéd, hogy módosítsa az igazolás tíipusokat is, akkor hasonlítsd össze ezeket:
		    // var_dump($RES[$i]['igazolas']);
		    // var_dump($SET['igazolas']);
		if ($_statusz!=$SET['statusz']) {
		    $MODOSITANDO[] = array('oraId'=>$_oraId,'id'=>$_hid,'statusz'=>$SET['statusz'],'igazolas'=>$SET['igazolas'],'tipus'=>$SET['tipus']);
		}
		$eddigORAIDK[] = $_oraId;
	    }

	    if (!$error) {
		$BEIRANDO = array_diff($diakORAIDK,$eddigORAIDK);
//		for ($i=0; $i<count($BEIRANDO); $i++) {
		foreach ($BEIRANDO as $_index => $_oraId) {
		    if (!in_array($ORAK[$_oraId]['tipus'],array('elmarad','elmarad máskor')))
			hianyzasRegisztralas(
			    array('oraId'=>$_oraId,'dt'=>$ORAK[$_oraId]['dt'],'ora'=>$ORAK[$_oraId]['ora']),
			    array(array('diakId'=>$diakId, 'id'=>'','statusz'=>$SET['statusz'],'igazolas'=>$SET['igazolas'],'tipus'=>$SET['tipus']))
			);
		}

		if (is_array($MODOSITANDO) && count($MODOSITANDO)>0) 
		    hianyzasIgazolas($MODOSITANDO,$diakId);

	    }
    }


    function oraHianyzasBeiras($dt, $ora, $diakId, $SET=array()) {

	    if ($dt=='' || $ora=='') {
		$_SESSION['alert'][] = 'message:empty_fields:kötelező paraméter üres (oraHianyzasBeiras:dt,ora)';
		return false;
	    }
	    $q = "SELECT * FROM hianyzas WHERE diakId=%u AND dt='%s' AND ora=%u";
	    $RES = db_query($q, array('fv' => 'oraHianyzasBeiras', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($diakId, $dt, $ora)));
	    if ($RES!==false && count($RES)>0) {

		$_SESSION['alert'][] = 'message:wrong_data:már van bejegyzése. Eldöntési kérdés.';

	    } else {

		// Aznapi tankörei és felmentései
		$diakTANKOROK = getTankorByDiakId($diakId, __TANEV, array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt, 'result'=>'csakid'));
		$diakFMTANKOROK = getTankorDiakFelmentes($diakId, __TANEV, array('csakId' => true, 'tolDt' => $dt, 'igDt' => $dt, 'nap'=>date('w',strtotime($dt)),'ora'=>$ora,'result'=>'csakid'));

		$T = array_diff($diakTANKOROK,$diakFMTANKOROK);
		reset($T);
		sort($T);

		$q = "SELECT * FROM ora WHERE ora=%u AND dt='%s' and tankorId IN (".implode(',', array_fill(0, count($T), '%u')).")";
		$v = mayor_array_join(array($ora, $dt), $T);
		$oraAdat = db_query($q, array('fv' => 'oraHianyzasBeiras', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

		// EDDIG: csak akkor regisztráltuk, ha egy találatot adott az ora tábla.
		//		if ( ($db=count($oraAdat)) ==1 && !in_array($oraAdat[0]['tipus'],array('elmarad','elmarad máskor')) ) {
		// MOST : azonban lehetséges olyan eset, hogy az adott órára többször is regisztrálandó. Mit tegyünk?
		//	  a napi beírás beírja! Ez a függvény hibaüzen.

		if ( ($db=count($oraAdat)) >=1 ) {
		    for ($i=0; $i<count($oraAdat); $i++) {
			if (!in_array($oraAdat[$i]['tipus'],array('elmarad','elmarad máskor'))) {
			    hianyzasRegisztralas(
				array('oraId'=>$oraAdat[$i]['oraId'],'dt'=>$oraAdat[$i]['dt'],'ora'=>$oraAdat[$i]['ora']),
				array(array('diakId'=>$diakId, 'id'=>'','statusz'=>$SET['statusz'],'igazolas'=>$SET['igazolas'],'tipus'=>$SET['tipus']))
			    );
                   	}
		    }
		} elseif ($db==0) {
		    $_SESSION['alert'][] = '::nincs órája.';
		} elseif (in_array($oraAdat[0]['tipus'],array('elmarad','elmarad máskor'))) {
		    // ekkor nem kell csinálni semmit. Hibaüzenetet sem.
		} else {
		    // ide nem juthatunk MÁR!
		    $_SESSION['alert'][] = ':%0%:%1% órája is van ebben az időpontban:'.$db;
		}
	    }
    }

?>
