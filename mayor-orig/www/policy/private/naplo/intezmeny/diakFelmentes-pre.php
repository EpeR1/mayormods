<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR && !__DIAK) {
        $_SESSION['alert'][] = 'message:insufficient_access';
    } else {

        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/targy.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/tankorDiakModifier.php');
        require_once('include/modules/naplo/share/zaroJegyModifier.php');
        require_once('include/modules/naplo/share/zaradek.php');
        require_once('include/share/date/names.php');

	$ADAT['dt'] = $dt = readVariable($_POST['dt'],'date',date('Y-m-d'));

	if (__DIAK===true) {
	    $ADAT['diakId'] = $diakId = __USERDIAKID;
	} else {
	    $ADAT['osztalyId'] = $osztalyId = readVariable($_POST['osztalyId'],'id');
	    $ADAT['diakId'] = $diakId = readVariable($_POST['diakId'],'id');
	    if ($diakId!='') {
		$ADAT['tankorId'] = $tankorId = readVariable($_POST['tankorId'],'id');
	    } else {
		$ADAT['diakId'] = $_POST['diakId'] = $diakId = readVariable($_GET['diakId'],'id');
		if ($diakId!='') {
		    $_r = getDiakOsztalya($diakId,array('result'=>'idonly'));
		    $ADAT['osztalyId'] = $_POST['osztalyId'] = $osztalyId = $_r[0];
		}
	    }
	}
	if ($diakId!='') {
	    $tolDt = readVariable($_POST['tolDt'],'date',$dt);
	    $targyId = readVariable($_POST['targyId'],'id');
	    $ADAT['diakAdat'] = getDiakAdatById($diakId);
	    $ADAT['diakTargy'] = getTargyakByDiakId($diakId,array('tolDt'=>$dt,'result'=>'indexed'));
	    $ADAT['diakTankor'] = getTankorByDiakId($diakId,__TANEV,array('tolDt'=>$dt,'igDt'=>$_TANEV['zarasDt']));
	    $ADAT['diakTankorFelmentes'] = getTankorDiakFelmentes($diakId, __TANEV, array('csakId' => true, 
		'tolDt' => $_TANEV['kezdesDt'], 'igDt'=>$_TANEV['zarasDt'], 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól','értékelés alól')));
	}

	if (__NAPLOADMIN === true || __VEZETOSEG===true) {

	/* ACTION */
	    /*
	    
		1-es típus a részénél: értékelés alól FM (egész tnév) + záradék
		    b: záradék
	    */
	    
	    if ($action=='tipus1' && $targyId!='') {

		$altipus = readVariable($_POST['t1altipus'],'numeric unsigned',null,array(1,2));
		$igDt = readVariable($_POST['igDt'],'date',$_TANEV['zarasDt']);
		$iktatoszam = readVariable($_POST['iktatoszam'],'string');

		if ($altipus=='1') {
		    // felmentés értékelés alól tolDt-től minden tankörben ahol a tárgy adott
		    $SQL_fail=false;
		    $lr = db_connect('naplo_intezmeny');
		    db_start_trans($lr);
		    /* 1. FM rögzítés */
		    for($i=0;$i<count($ADAT['diakTankor']); $i++) {
			if ($ADAT['diakTankor'][$i]['targyId'] == $_POST['targyId']) {
			    $_tankorId = $ADAT['diakTankor'][$i]['tankorId'];
			    $FM = array('diakId'=>$diakId, 'tankorId'=>$_tankorId, 'tolDt'=>$tolDt, 'igDt'=>$igDt, 'felmentesTipus'=>'értékelés alól','iktatoszam'=>$iktatoszam);
			    $result = tankorDiakFelmentesFelvesz($FM,$lr);
			    if ($result===false) $SQL_fail = true;
			}
		    }
		    /* 2. írjuk be neki az FM bejegyzést */
		    //találjuk ki milyen félévekre kell beírnunk:
		    $q = "SELECT szemeszter,zarasDt FROM szemeszter WHERE zarasDt>='%s' AND tanev=%s";
		    $v = array($tolDt,__TANEV);
		    $SZEMESZTEREK = db_query($q, array('fv'=>'-pre','values'=>$v,'result'=>'indexed','modul'=>'naplo_intezmeny'), $lr);
		    for ($i=0; $i<count($SZEMESZTEREK); $i++) {
			$_szemeszter = $SZEMESZTEREK[$i]['szemeszter'];
			$_hivatalosDt = $SZEMESZTEREK[$i]['zarasDt'];
			$_evfolyamJel = generateDiakEvfolyamJel($diakId,__TANEV,$szemeszter);
			// ellenőrizzük a zárójegy táblát, van-e bejegyzése, DE csak a beírandó szemeszter számít
			{
		    	    $q = "SELECT count(zaroJegyId) AS db FROM zaroJegy WHERE diakId=%u AND targyId=%u AND evfolyamJel='%s' AND felev=%u AND hivatalosDt='%s'";
		    	    $v = array($diakId,$targyId,$_evfolyamJel,$_szemeszter,$_hivatalosDt);
		    	    $count = db_query($q, array('fv'=>'-pre','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v), $lr);
			}
			if ($count==0) {
			    $JEGY = array(array('targyId'=>$targyId,'zaroJegyId'=>null, 'diakId'=> $diakId, 'jegyTipus'=>'nem értékelhető', 'jegy'=>'1.0', 'tanev'=>__TANEV, 'szemeszter'=>$_szemeszter, 'felev'=>$_szemeszter));
			    if (($zaroJegyek=zaroJegyBeiras($JEGY,$lr))===false) {
				$_SESSION['alert'][] = 'info:wrong_data:Hiba a jegy rögzítéskor';
				$SQL_fail=true;
			    } else {
				// rögzített zárójegyek idjei... $zaroJegyek
				// ezt elmenthetjük az esetleges kapcsolatok megőrzésére
			    }
			} else {
			    $_SESSION['alert'][] = 'info:wrong_data:A megadott félévre már van rögzítve zárójegye!:'.$_szemeszter;
			    $SQL_fail=true ;
			}

		    } // szemeszterek
		    /* 3. Most már a megfelelő záradékot is rögzíthetjük akár */
		    $targyAdat = getTargyById($targyId);
		    $ZARADEK = array('iktatoszam'=>$iktatoszam,'diakId'=>$diakId, 'dt'=>date('Y-m-d'), 'zaradekIndex'=>$ZaradekIndex['felmentés']['értékelés alól'],'csere'=>array('%tantárgy%'=>$targyAdat['targyNev']));
		    if (($zaradekId = zaradekRogzites($ZARADEK,$lr))===false) {
			$_SESSION['alert'][] = 'info::Hiba a záradék rögzítésekor!';
			$SQL_fail=true;
		    } else {
			// rögzített záradék idje: $zaradekId
			// ezt elmenthetjük az esetleges kapcsolatok megőrzésére
		    }
		    if ($SQL_fail===true) db_rollback($lr);
		    else {
			$_SESSION['alert'][] = 'info:success';
			db_commit($lr);
		    }
		    db_close($lr);
		    
		} elseif ($altipus=='2') {
		    // csak záradék rögzítése
		    $mi = readVariable($_POST['zaradekTxt2'],'string');
		    $miatt = readVariable($_POST['zaradekTxt1'],'string');
		    $ZARADEK = array('iktatoszam'=>$iktatoszam,'diakId'=>$diakId, 'dt'=>date('Y-m-d'), 'zaradekIndex'=>$ZaradekIndex['felmentés']['értékelés és minősítés alól'],'csere'=>array('%miatt%'=>$miatt,'%mi%'=>$mi));
		    if (($zaradekId = zaradekRogzites($ZARADEK,$lr))===false) {
			$_SESSION['alert'][] = 'info::Hiba a záradék rögzítésekor!';
		    } else {
			$_SESSION['alert'][] = 'info:success';
		    }

		}
	    
	    } elseif ($action=='tipus2' && $tankorId!='') {

		$igDt = readVariable($_POST['igDt'],'date',$_TANEV['zarasDt']);
		$nap = readVariable($_POST['nap'],'numeric unsigned');
		$ora = readVariable($_POST['ora'],'numeric unsigned');
		$iktatoszam = readVariable($_POST['iktatoszam'],'string');

		$ovi = ($_POST['ovi']==='1');
		$forceDel = ($_POST['forceDel']==='1');		
		$skipZaradek = ($_POST['skipZaradek']==='1');		
		if ($_POST['ovi']==='1')
		if (strtotime($tolDt)>strtotime($igDt)) {
		    $_SESSION['alert'][] = 'info:wrong_data:igDt<tolDt';
		    unset($igDt);
		}
		if ($igDt=='') 
		    $_SESSION['alert'][] = 'info:wrong_data:kötelező igDt paraméter';
		else {

		    $SQL_fail=false;
		    $lr = db_connect('naplo_intezmeny');
		    db_start_trans($lr);

			// Teendő: * tankörben FM: óralátogatás alól és értékelés alól, de zárójegyet kap (!) 			
			$FM = array('diakId'=>$diakId, 'tankorId'=>$tankorId, 'tolDt'=>$tolDt, 'igDt'=>$igDt, 'felmentesTipus'=>'óralátogatás alól','nap'=>$nap,'ora'=>$ora,'iktatoszam'=>$iktatoszam);
			if ($forceDel===true) $FM['utkozes'] = 'torles';
			$result = tankorDiakFelmentesFelvesz($FM,$lr);
			if ($result===false) $SQL_fail = true;
			if ($ovi=='1') {
			    $FM = array('diakId'=>$diakId, 'tankorId'=>$tankorId, 'tolDt'=>$tolDt, 'igDt'=>$igDt, 'felmentesTipus'=>'értékelés alól','iktatoszam'=>$iktatoszam);
			    if ($forceDel===true) $FM['utkozes'] = 'torles';
			    $result = tankorDiakFelmentesFelvesz($FM,$lr);
			    if ($result===false) $SQL_fail = true;
			}
			if ($skipZaradek===false) {
			    $tankorNev = getTankorNevById($tankorId);
			    $targyAdat = getTargyById((getTankorTargyId($tankorId)));
			    $targyNev = $targyAdat['targyNev'];
			    $ZaradekIndex['felmentés'] = ($ovi) ? $ZaradekIndex['felmentés']['óra látogatása alól osztályozóvizsgával'] : $ZaradekIndex['felmentés']['óra látogatása alól'];
			    if (isset($nap)) $napOraStr[] = ($aHetNapjai[$nap-1]).'i';
			    if (isset($ora)) $napOraStr[] = $ora.'.';
			    $ZARADEK = array('iktatoszam'=>$iktatoszam,'diakId'=>$diakId, 'dt'=>date('Y-m-d'), 'zaradekIndex'=>$ZaradekIndex['felmentés'],'csere'=>array(
				'%tantárgy%'=> "$targyNev ($tankorNev)",
				'%ezen óráinak%'=> @implode(' ',$napOraStr),
				'%tólDt%'=> $tolDt,
				'%igDt%'=> $igDt
				));
			    if (($zaradekId = zaradekRogzites($ZARADEK,$lr))===false) {
				$_SESSION['alert'][] = 'info::Hiba a záradék rögzítésekor!';
				$SQL_fail=true;
			    } else {
				// rögzített záradék idje: $zaradekId (ez nem kell most, kösz)
			    }
			}

		    if ($SQL_fail===true) db_rollback($lr);
		    else db_commit($lr);
		    db_close($lr);

		}
	
	    
	    
	    } elseif ($action=='tipus3' && $targyId!='') {
		$iktatoszam = readVariable($_POST['iktatoszam'],'string');

		$SQL_fail=false;
		$lr = db_connect('naplo_intezmeny');
		db_start_trans($lr);
		/* 1. léptessük ki a tanköreiből véglegesen */
		for($i=0;$i<count($ADAT['diakTankor']); $i++) {
		    /* Jó lenne azért ellenőrizni, hogy van-e már ilyen felmentése */
		    if ($ADAT['diakTankor'][$i]['targyId'] == $_POST['targyId']) {
			$_SESSION['alert'][] = 'info::Kiléptettük:'.$ADAT['diakTankor'][$i]['tankorNev'];
			$_tankorId = $ADAT['diakTankor'][$i]['tankorId'];
			$DEL = array(
			    'tolDt'=>$tolDt,'igDt'=>null,'diakId'=>$diakId,
			    'utkozes'=>'torles',
			    'tankorId'=>$_tankorId,
			    'MIN_CONTROL'=>false
			);

			    // meg kell szüntetnünk a tankörcsoportot a jövőbeli tanévekben is...
			    //$q = "SELECT DISTINCT tanev FROM szemeszter WHERE kezdesDt>='%s' AND statusz='aktív'";
			    //$v = array($tolDt);
			    //$r = db_query($q, array('fv'=>'tankorDiakTorol/getTanev','modul'=>'naplo_intezmeny','values'=>$v, 'result'=>'idonly'),$lr);
			    $q = "SELECT count(*) AS db FROM ".__TANEVDBNEV.".`tankorCsoport` WHERE tankorId=%u";
			    $erintettTankorCsoport = db_query($q, array('debug'=>false,'fv'=>'-pre/tankorCsoport','modul'=>'naplo','result'=>'value','values'=>array($_tankorId)),$lr);
			    if ($erintettTankorCsoport > 0) $_SESSION['alert'][] = 'info::A tankör ('.$_tankorId.') a tankörcsoportot/okat elhagyta.';

			    $q = "DELETE FROM ".__TANEVDBNEV.".`tankorCsoport` WHERE tankorId=%u";
			    $r = db_query($q, array('debug'=>false,'fv'=>'-pre/tankorCsoport','modul'=>'naplo','values'=>array($_tankorId)),$lr);

			if (tankorDiakTorol($DEL,$lr) === false) {
			    $_SESSION['alert'][] = 'info:wrong_data:Hiba a tankörből való kiléptetéskor';
			    $SQL_fail=true;
			}
			// a tankorDiakFelmentes táblával ebben az esetben mi legyen? a tankorDiakTorol függvénynek
			// azzal is kéne foglalkoznia? - talán igen
		    }
		}
		/* 2. írjuk be neki az FM bejegyzést */
		//találjuk ki milyen félévekre kell beírnunk:
		    $q = "SELECT szemeszter,zarasDt FROM szemeszter WHERE zarasDt>='%s' AND tanev=%s";
		    $v = array($tolDt,__TANEV);
		    $SZEMESZTEREK = db_query($q, array('fv'=>'-pre','values'=>$v,'result'=>'indexed','modul'=>'naplo_intezmeny'), $lr);
		    for ($i=0; $i<count($SZEMESZTEREK); $i++) {
			$_szemeszter = $SZEMESZTEREK[$i]['szemeszter'];
			$_hivatalosDt= $SZEMESZTEREK[$i]['zarasDt'];
			$_evfolyamJel = generateDiakEvfolyamJel($diakId,__TANEV,$szemeszter);
			// ellenőrizzük a zárójegy táblát, van-e bejegyzése
			{
		    	    $q = "SELECT count(zaroJegyId) AS db FROM zaroJegy WHERE diakId=%u AND targyId=%u AND evfolyamJel='%s' AND felev=%u AND hivatalosDt='%s'";
		    	    $v = array($diakId,$targyId,$_evfolyamJel,$_szemeszter,$_hivatalosDt);
		    	    $count = db_query($q, array('fv'=>'-pre','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v), $lr);
			}
			if ($count==0) {
			    $JEGY = array(array('targyId'=>$targyId,'zaroJegyId'=>null, 'diakId'=> $diakId, 'jegyTipus'=>'nem értékelhető', 'jegy'=>'1.0', 'tanev'=>__TANEV, 'szemeszter'=>$_szemeszter, 'felev'=>$_szemeszter));
			    if (($zaroJegyek=zaroJegyBeiras($JEGY,$lr))===false) {
				$_SESSION['alert'][] = 'info:wrong_data:Hiba a jegy rögzítéskor';
				$SQL_fail=true;
			    } else {
				// rögzített zárójegyek idjei... $zaroJegyek
			    }
			} else {
			    $_SESSION['alert'][] = 'info:wrong_data:A megadott félévre már van rögzítve zárójegye!:'.$_szemeszter;
			    $SQL_fail=true ;
			}

		    } // szemeszterek
		/* 3. Most már a megfelelő záradékot is rögzíthetjük akár */
		$targyAdat = getTargyById($targyId);
		$ZARADEK = array('iktatoszam'=>$iktatoszam,'diakId'=>$diakId, 'dt'=>date('Y-m-d'), 'zaradekIndex'=>$ZaradekIndex['felmentés']['tárgy tanulása alól'],'csere'=>array('%tantárgyak neve%'=>$targyAdat['targyNev']));
		if (($zaradekId = zaradekRogzites($ZARADEK,$lr))===false) {
		    $_SESSION['alert'][] = 'info::Hiba a záradék rögzítésekor!';
		    $SQL_fail=true;
		} else {
		    // rögzített záradék idje: $zaradekId
		}

		if ($SQL_fail===true) db_rollback($lr);
		else db_commit($lr);
		db_close($lr);

		/* ReReading Data */
		$ADAT['diakAdat'] = getDiakAdatById($diakId);
		$ADAT['diakTargy'] = getTargyakByDiakId($diakId,array('tolDt'=>$dt,'result'=>'indexed'));
		$ADAT['diakTankor'] = getTankorByDiakId($diakId,__TANEV,array('tolDt'=>$dt));

	    } // action 3
	    elseif ($action=="tankorDiakFelmentesTorol") {
		$SQL_fail=false;
		$lr = db_connect('naplo_intezmeny');
		db_start_trans($lr);

		$DELFMID = readVariable($_POST['DELFM'],'id');
		for ($i=0; $i<count($DELFMID); $i++) {
		    tankorDiakFelmentesLezar(array('tankorDiakFelmentesId'=>$DELFMID[$i], 'kiDt'=>$dt), $lr);
		    $ZARADEK = array('iktatoszam'=>$iktatoszam,'diakId'=>$diakId, 'dt'=>date('Y-m-d'), 'zaradekIndex'=>$ZaradekIndex['felmentés']['törlés'],'csere'=>array('%tankorDiakFelmentesId%'=>$DELFMID[$i],'%dt%'=>$dt));
		    if (($zaradekId = zaradekRogzites($ZARADEK,$lr))===false) {
			$_SESSION['alert'][] = 'info::Hiba a záradék rögzítésekor!';
			$SQL_fail=true;
		    }
		}
		if ($SQL_fail===true) db_rollback($lr);
		else db_commit($lr);
		db_close($lr);	    
	    }
	}
	/* End of ACTION */
	$ADAT['diakZaradek'] = getDiakZaradekok($diakId, array('tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt']));
	$ADAT['tankorDiakFelmentes'] = getTankorDiakFelmentes($diakId, __TANEV,array('csakId' => false, 'tolDt' => $_TANEV['kezdesDt'], 'igDt' => $_TANEV['zarasDt'], 'result'=>'indexed', 'felmentesTipus'=>array('óralátogatás alól','értékelés alól')) );

        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post' => array('osztalyId', 'tanev'),
            'paramName' => 'dt',
	    'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($_TANEV['kezdesDt'])),
            'igDt' => $_TANEV['zarasDt'],
            'post'=>array('osztalyId','diakId')
        );
	$TOOL['osztalySelect'] = array('tipus' => 'cella', 'paramName' => 'osztalyId', 'post' => array('dt'));
        $TOOL['diakSelect'] = array('tipus'=>'cella', 'tanev'=>$tanev, 'osztalyId' => $osztalyId,
//            'statusz' => $ADAT['statusz'],
            'post' => array('osztalyId','dt')
        );
	$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=felmentes'),
	    'titleConst' => array('_FELMENTES'), 'post' => array('diakId','osztalyId'));
        getToolParameters();

    }

?>
