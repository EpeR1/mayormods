<?php

    // share függőség:
    require_once('include/modules/naplo/share/hianyzasModifier.php');
    require_once('include/modules/naplo/share/jegyModifier.php');
    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/tankorBlokk.php');

    /* Általános függvények a tankörbe be és kivételhez */

    function tankorDiakFelvesz($ADAT) {


	$tankorId = $ADAT['tankorId'];
	$diakId = $ADAT['diakId'];
	$tolDt = $ADAT['tolDt'];
	$igDt = $ADAT['igDt'];
	$jovahagyva = ($ADAT['jovahagyva']=='') ? 0:1;

	$TankorIds = getTankorCsoportTankoreiByTankorId($ADAT['tankorId']);
	$TankorAdat = getTankorAdatByIds($TankorIds, array('dt' => $tolDt));

	// Blokkba való tartozás
	$Tanevek = getTanevekByDtInterval($tolDt,$igDt);
	for ($i=0; $i<count($Tanevek); $i++) {
	    $BlokkTankorIds = getTankorBlokkok($Tanevek[$i]);
	    if ( is_array($BlokkTankorIds['idk']) ) {
		foreach ($BlokkTankorIds['idk'] as $bId=>$BTID) {
    		    // Ellenőrzés megadott időintervallumban vizsgálva:
		    // Tagja-e a diák az adott intervallumban a blokk tanköreinek?
		    if ( in_array($tankorId,$BTID) ) {
    			$q = "SELECT tankorId FROM ".__INTEZMENYDBNEV.".tankorDiak
            		    WHERE tankorId IN (".implode(',', array_fill(0, count($BTID), '%u')).")
			    AND diakId=%u
            		    AND beDt <= '%s' AND (kiDt IS NULL OR kiDt >= '%s')";
			$v = mayor_array_join($BTID, array($diakId, $tolDt, $tolDt));
			$UTKOZO_TANKORIDS = db_query($q, array('fv' => 'tankorDiakFelvesz', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));
    			if (count($UTKOZO_TANKORIDS)>0) {
			    $q = "SELECT  tankorId  FROM tankorDiakFelmentes WHERE tankorId IN (".implode(',',$UTKOZO_TANKORIDS).")  AND diakId=%u AND felmentesTipus='óralátogatás alól' AND nap is null AND ora is null AND
				beDt<='%s' AND (kiDt IS NULL or kiDt >='%s')";
			    $values = array($diakId, $tolDt, $tolDt);
			    $FELMENTETTTANKORIDS = db_query($q, array('fv' => 'tankorDiakFelvesz', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $values));
			    for ($j=0; $j<count($FELMENTETTTANKORIDS); $j++) {
				$_tankorId = $FELMENTETTTANKORIDS[$j];
			    }
			    if (count(array_diff(array_values($UTKOZO_TANKORIDS),array_values($FELMENTETTTANKORIDS))) === 0) { 
				// OK
			    } else {
        			$_SESSION['alert'][] = '::Sikertelen. Tankörblokk ütközés!:blokkid('.$bId.')';
        			return false;
			    }
    			} 
		    }
		}
	    }
	}
	//---

	// Ellenőrizzük a tankörlétszámot és maximumot (csak ref dátumra...)
	if (_checkTankorMinMax($tankorId,array('diff'=>1,'refDt'=>$ADAT['tolDt'])) == 'tankor_max_reached')
	{
	    $_SESSION['alert'][] = 'info:tankor_max_reached';
	    return false;
	}
	//--

	// Main()
	{
	    tankorDiakTorol( array('tankorIds'=>$TankorIds, 'diakId'=> $diakId, 'tolDt'=> $tolDt,'igDt'=> $igDt, 'utkozes'=>'nemEllenoriz', 'MIN_CONTROL'=>false) );	

	    $v = array();
	    for ($i = 0; $i < count($TankorIds); $i++) {
		$_tankorId = $TankorIds[$i];		
		//$_kovetelmeny = $TankorAdat[$_tankorId]['kovetelmeny']; // vagy nem ez. diák statusatol is függ...
		//$_jelenlet = $TankorAdat[$_tankorId]['jelenlet']; // MIÉRT EZ???????????????????????????????????????????????????
		//$_jelenlet = "kötelező";
		//array_push($v, $_tankorId, $diakId, $tolDt, $igDt, $_jelenlet, $_kovetelmeny, $jovahagyva);
		//$V[] = "(%u, %u, '%s', NULLIF('%s',''), '%s', '%s', %u)";
		array_push($v, $_tankorId, $diakId, $tolDt, $igDt);
		$V[] = "(%u, %u, '%s', NULLIF('%s',''))";
	    }
	    $q = "INSERT INTO tankorDiak (tankorId,diakId,beDt,kiDt) VALUES ". implode(',',$V);
	    db_query($q, array('fv' => 'tankorDiakFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v) );
    	}

    }

    function diakTankorMagantanulo($diakId, $tolDt, $igDt = NULL, $utkozes = 'ellenorzes', $tanev = __TANEV) {

	die('FATAL ERROR in tankorDiakModifier.php -- diakTankorMagantanulo() -- OOOpps!');

    }

    function tankorDiakModify($ADAT) {
    /*
	$ADAT = array(
	    tankorId - a módosítandó tankor
	    tolDt, igDt - A módosítás hatálya
	    diaktorol - torlendő diákok id-i
	    diakok - Az érintett diákok id-i
	    {OBSOLETE}DJ* - egy adott diák 'jelenlét' attribútuma (* a diakId)
	    {OBSOLETE}DK* - egy adott diák 'követelmény' attribútuma (* a diakId)
	    {OBSOLETE}DOK[diakId] - egy adott diák 'jovahagyva' attribútuma - ha nincs megadva akkor $ADAT['jovahagyva'] érvényes
	    {OBSOLETE}jovahagyva - a diákok 'jovahagyva' attribútumának alapértelmezése
	    utkozes - ha igaz akkor 'torles', ha hamis akkor 'ellenorzes'
	)
    */

	$tankorId = $ADAT['tankorId'];
	$alapIgDt = $ADAT['igDt'];
	$tolDt = $ADAT['tolDt'];
	$jovahagyva = ($ADAT['jovahagyva']=='') ? 0:1;

	if ($alapIgDt!='' && strtotime($tolDt)>strtotime($alapIgDt)) {
	    $_SESSION['alert'][] = 'info::hibasdatum';
	    return false;
	}
	// Kik lettek törlésre jelölve - ez az "erősebb"
	for($i = 0; $i < count($ADAT['diaktorol']); $i++) $TORLENDOK[$ADAT['diaktorol'][$i]] = true;
	// diákonként végezzük a módosításokat
	for($i = 0; $i < count($ADAT['diakok']); $i++) {

	    $diakId = $ADAT['diakok'][$i];
	    //{OBSOLETE}$jelenlet = $ADAT['DJ'.$diakId];
	    //{OBSOLETE}$kovetelmeny = $ADAT['DK'.$diakId]; // ezt már nem használjuk
	    //{OBSOLETE$jovahagyva = ($ADAT['DOK'.$diakId]!='') ? $ADAT['DOK'.$diakId]:$jovahagyva;
	    $igDt = $alapIgDt;

	    $utkozes = ((bool)$ADAT['utkozes']) ? 'torles':'ellenorzes';

	    if ($TORLENDOK[$diakId]) {
		//tankorDiakTorol( array('tankorId' => $tankorId, 'diakId' => $diakId, 'tolDt' => $tolDt, 'igDt' => $igDt, 'jovahagyva'=>$jovahagyva, 'utkozes' => $utkozes ));
		tankorDiakTorol( array('tankorId' => $tankorId, 'diakId' => $diakId, 'tolDt' => $tolDt, 'igDt' => $igDt, 'utkozes' => $utkozes ));
	    } else {
		// A tankörcsoportok miatt módosítani csak az aktuális intervallumon belül - tehát az első szakadásig - lehet!
		$q = "SELECT beDt, kiDt, kiDt + INTERVAL 1 DAY AS kovDt FROM tankorDiak WHERE diakId=%u AND tankorId=%u 
			AND ('%s'<=kiDt OR kiDt IS NULL)";
		$v = array($diakId, $tankorId, $tolDt);
		if ($igDt != '') {
		    $q .= " AND beDt<='%s' ORDER BY beDt,kiDt";
		    array_push($v, $igDt);
		}
		$ret = db_query($q,  array('fv' => 'tankorDiakModify', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
		for ($j = 0; (($j < count($ret)-1) && ($ret[$j]['kovDt'] == $ret[$j+1]['beDt'])); $j++);
		if (
		    $ret[$j]['kiDt'] != ''
		    && ($igDt == '' || strtotime($ret[$j]['kiDt']) < strtotime($igDt))
		) $igDt = $ret[$j]['kiDt'];

		// lekérdezzük, hogy módosul-e a <del>jelenlét/követelmény</del><ins>oralatogatasAlol, erdemjegyet</ins> paraméter
		/* ERRE NINCS SZÜKSÉG!
		$q = "SELECT COUNT(*) FROM tankorDiak WHERE diakId=%u AND tankorId=%u AND (kiDt>='%s' OR kiDt IS NULL) AND (oralatogatasAlol!='%s' OR erdemjegyet!='%s')";
		$v = array($diakId, $tankorId, $tolDt, $oralatogatasAlol, $erdemjegyet);
		if ($igDt != '') {
		    $q .= " AND beDt<='$igDt'";
		    array_push($v, $igDt);
		}
		$db = db_query($q, array('fv' => 'tankorDiakModify', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));

		if ($db > 0) {
		    if (// nem töröljük a tankörcsoport összes tanköréből, csak az adott tankörből!!
			tankorDiakTorol( 
			    array(
				'tankorId' => $tankorId, 'diakId' => $diakId, 'tolDt' => $tolDt, 'igDt' => $igDt,
				'tankorIds' => array($tankorId), 'utkozes' => $utkozes
			    )
			)
		    ) {
		    	$q = "INSERT INTO tankorDiak (diakId,tankorId,beDt,kiDt,erdemjegyet,oralatogatasAlol) 
				VALUES (%u, %u, '%s', NULLIF('%s', ''), '%s', '%s')";
			$v = array($diakId, $tankorId, $tolDt, $igDt, $erdemjegyet, $oralatogatasAlol);

		    	db_query($q, array('fv' => 'tankorDiakModify', 'modul' => 'naplo_intezmeny', 'values' => $v));
		    }
		} // $db>0
		*/
	    }
	}
    }


    function tankorDiakTorol($ADAT, $olr = '') {

	/*
	    [TOL-IG] töröl zárt szigorú
	    Paraméterek:
		diakId
		utkozes - torles, ellenorzes, nemEllenoriz
		tankorId
		tankorIds
		MIN_CONTROL - true/false
		tolDt, igDt
	*/


	// esetleges külső tranzakciókhoz!
	$lr = ($olr != '') ? $olr : db_connect('naplo_intezmeny');
	// A törlendő időszakra beírt hiányzás, vagy jegy okozhat ütközést. Ekkor vagy automatikusan töröljük a hibás bejegyzéseket, 
	// vagy hibajelzést adunk, vagy nem foglalkozunk az esetleges ütközésekkel
	// (ez státuszmódosításkor lehet, mikor a törlés után rögtön vissza is írjuk a tagságot)
	if (!in_array($ADAT['utkozes'], array('torles','ellenorzes','nemEllenoriz'))) $ADAT['utkozes'] = 'ellenorzes';

	// Valódi törlésnél a tankörcsoport összes tagjából törölni kell, de pl. a tagság típusának módosításakor csak az érintett tankörből töröljük
	// illetve, ha már valamiért lekérdeztük az érintett tanköröket, akkor át lehessen adni.
	if (is_array($ADAT['tankorIds']) && count($ADAT['tankorIds']) > 0) $TANKORIDS = $ADAT['tankorIds'];
	elseif (isset($ADAT['tankorId']) && $ADAT['tankorId'] != '') $TANKORIDS = getTankorCsoportTankoreiByTankorId($ADAT['tankorId'], $lr);
        else {                                                                                                                                               
            $_SESSION['alert'][] = 'message:wrong_data:tankorDiakTorol:nincs tankör megadva:'.$ADAT['diakId'];                                                           
            return false;                                                                                                                                    
        }                                                                                                                                                    

	// Ellenőrizzük a minimum és maximum létszámokat, ha kell
	// Figyelem!  pl. Előtárgyjelentkezési időszakban pl tilos vizsgálni...
	if ($ADAT['MIN_CONTROL']===true) {
	//for ($i=0; $i<count($TANKORIDS); $i++) { // elég egyet vizsgálni, mert elvileg egyeznek
	    $_tankorId = $TANKORIDS[0];
	    if (_checkTankorMinMax($_tankorId,array('diff'=>(-1),'refDt'=>$ADAT['tolDt'])) == 'tankor_min_reached') {
		$_SESSION['alert'][] = 'info:tankor_min_reached';
		if ($olr == '') db_close($lr);
		return false;
	    }
	//}
	}
	// --
	$TH = $TJ = array();
	$HSUM = $JSUM = array();
	// A tol-ig dátumok által érintett aktív tanévek lekérdezése
	$aktivTanevek = getTanevekByDtInterval($ADAT['tolDt'], $ADAT['igDt'], array('aktív')); // hiányzó link resource
	if ($ADAT['utkozes'] != 'nemEllenoriz') {
	    // Az érintett tanéveken végigmenve
	    foreach ($aktivTanevek as $key => $tanev) {
    		for ($i = 0; $i < count($TANKORIDS); $i++) {
		    $_SET = array('diakId'=>$ADAT['diakId'], 'tankorIds'=>$TANKORIDS[$i], 'tanev'=>$tanev, 'tolDt'=>$ADAT['tolDt'], 'igDt'=>$ADAT['igDt']);
		    $H = tankorDiakHianyzasIdk2($_SET, $lr);
		    $J = tankorDiakJegyIdk($_SET, $lr);
		    if (count($H)>0) $TH[] = $TANKORIDS[$i];
		    if (count($J)>0) $TJ[] = $TANKORIDS[$i];
		    if (is_array($H)) $HSUM = array_merge($HSUM,$H); // $H lehet false is
		    if (is_array($J)) $JSUM = array_merge($JSUM,$J); // $J lehet false is
		}
		if (count($TH) > 0) $_SESSION['alert'][] = 'info:hibas_hianyzasok:'.count($HSUM).':tanev='.$tanev.':tankorIdk='.implode(',',$TH);
		if (count($TJ) > 0) $_SESSION['alert'][] = 'info:hibas_jegyek:'.count($JSUM).':tanev='.$tanev.':tankorIdk='.implode(',',$TJ);

		// Modosítás:
		if ($ADAT['utkozes']=='torles') {
		    // hiányzások és jegyek törlése...
		    hianyzasTorles($HSUM, $tanev, $lr);
			jegyTorles($JSUM, null, $tanev, $lr);
		    $TH = $TJ = array();
		    $HSUM = $JSUM = array();
		}
	    }
	}

	if (  ((count($TH) == 0 && count($TJ) == 0)) || $ADAT['utkozes'] == 'torles' || $ADAT['utkozes'] == 'nemEllenoriz') {

		logAction(
		    array(
			'szoveg'=>'diakKilep: '.$ADAT['diakId'].', '.$ADAT['tolDt'].', '.$ADAT['igDt'].', '.implode(',',$TANKORIDS),
			'table'=>'tankorDiak'
		    )
		);

		// A tol-ig közé esőket töröljük
		$q = "DELETE FROM tankorDiak WHERE diakId=%u AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDS), '%u')).") AND '%s'<=beDt";
		$v = mayor_array_join(array($ADAT['diakId']), $TANKORIDS, array($ADAT['tolDt']));
		if ($ADAT['igDt']!='') {
		    $q.= " AND kiDt<='%s'";
		    array_push($v, $ADAT['igDt']);
		}
		$delResult = db_query($q, array('fv' => 'tankorDiakTorol', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr );

		if ($ADAT['igDt'] != '') {
		    // a tol-ig intervallumot tartalmazókat ketté kell vágni, ezért a jobb oldali darabot felvesszük
		    $q = "INSERT INTO tankorDiak (tankorId,diakId,beDt,kiDt)
			    SELECT tankorId,diakId,'%s' + INTERVAL 1 DAY AS beDt,kiDt FROM tankorDiak
			    WHERE diakId=%u AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDS), '%u')).")
			    AND beDt<'%s' AND (kiDt IS NULL OR '%s'< kiDt)";
		    $v = mayor_array_join(array($ADAT['igDt'], $ADAT['diakId']), $TANKORIDS, array($ADAT['tolDt'], $ADAT['igDt']));
		    $insResult = db_query($q, array('fv' => 'tankorDiakTorol', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr );
		}

		// A balról benyúló intervallumokat levágjuk (az "átnyúlókat" is! Így kapjuk a baloldali darabot)
		$q = "UPDATE tankorDiak SET kiDt = '%s' - INTERVAL 1 DAY WHERE diakId=%u 
			AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDS), '%u')).") 
			AND beDt<'%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
		$v = mayor_array_join(array($ADAT['tolDt'], $ADAT['diakId']), $TANKORIDS, array($ADAT['tolDt'], $ADAT['tolDt']));
		$upResult1 = db_query($q, array('fv' => 'tankorDiakTorol', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);

		if ($ADAT['igDt']!='') {
		    // A jobbról benyúló intervallumokat levágjuk ("átnyúló" darab már nincs!)
		    $q = "UPDATE tankorDiak SET beDt = '%s' + INTERVAL 1 DAY WHERE diakId=%u 
			    AND tankorId IN (".implode(',', array_fill(0, count($TANKORIDS), '%u')).") 
			    AND beDt<='%s' AND (kiDt IS NULL OR '%s'< kiDt) ";
		    $v = mayor_array_join(array($ADAT['igDt'], $ADAT['diakId']), $TANKORIDS, array($ADAT['igDt'], $ADAT['igDt']));
    		    $upResult2 = db_query($q, array('fv' => 'tankorDiakTorol', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr );
		}

		if ($delResult===false || $upResult1 === false || $upResult2 === false || $insResult===false) {
		    if ($olr == '') db_close($lr);
            	    return false;
		}

	    if ($olr == '') db_close($lr);
	    return true;

	} else {

	    if ($olr == '') db_close($lr);
	    return false;
	}

    }




    function _checkTankorMinMax($tankorId,$ADAT=array('diff'=>1,'refDt'=>'')) {

	$letszam = getTankorLetszam($tankorId, array('refDt'=>$ADAT['refDt']));
	$TankorAdat = getTankorAdatByIds(array($tankorId), array('dt' => $ADAT['refDt']));
	if ($ADAT['diff']>0) {
	    if (
		$TankorAdat[$tankorId]['max']!=0 && /* Ez a feltétel problémás. A tankörjelentkezés UI-n ugyanis nem engedjük meg a 
							0-0-s tankörökbe bejelentkezést. Ezzel elérhető hogy az időközben 0-0ra állított
							limitű tankörbe bejelentkezhessenek. Holott ez unlimited-et jelent?
						    */
		($letszam+$ADAT['diff'])>$TankorAdat[$tankorId]['max']) {
	        $r = 'tankor_max_reached';
	    }
	} elseif ($ADAT['diff']<0) {
	    if ($TankorAdat[$tankorId]['min']!=0 && ($letszam+$ADAT['diff'])<$TankorAdat[$tankorId]['min']) {
	        $r = 'tankor_min_reached';
	    }
	} else {
	    $r = 'notChecked';
	}
	return ($r=='') ? false : $r;
    }


    /* Adott zárt intervallumra felvesz egy bizonyos típusú felmentést */
    function tankorDiakFelmentesFelvesz($ADAT, $olr = '') {

	/* [TOL-IG] töröl zárt szigorú */
	$lr = ($olr != '') ? $olr : db_connect('naplo_intezmeny');
	if ($olr=='') db_start_strans($lr);

	$tankorId = $ADAT['tankorId'];
	$diakId = $ADAT['diakId'];
	$tolDt = $ADAT['tolDt'];
	$igDt = $ADAT['igDt'];
	$nap = $ADAT['nap'];
	$ora = $ADAT['ora'];
	$iktatoszam = $ADAT['iktatoszam'];
	$felmentesTipus= $ADAT['felmentesTipus'];

	if (!in_array($ADAT['utkozes'], array('torles','ellenorzes','nemEllenoriz'))) $ADAT['utkozes'] = 'ellenorzes';
	// --
	$TH = $TJ = array();
	$HSUM = $JSUM = array();
	// A tol-ig dátumok által érintett aktív tanévek lekérdezése
	$aktivTanevek = getTanevekByDtInterval($ADAT['tolDt'], $ADAT['igDt'], array('aktív'));
	if ($ADAT['utkozes'] != 'nemEllenoriz') {
	    // Az érintett tanéveken végigmenve
	    foreach ($aktivTanevek as $key => $tanev) {

		if ($felmentesTipus=='óralátogatás alól') {
		    $H = tankorDiakHianyzasIdk($ADAT['diakId'], $tankorId, $tanev, $ADAT['tolDt'], $ADAT['igDt'], $nap, $ora);
		} elseif ($felmentesTipus=='értékelés alól') {
		    $J = tankorDiakJegyIdk(array('diakId'=>$ADAT['diakId'], 'tankorIds'=>$tankorId, 'tanev'=>$tanev, 'tolDt'=>$ADAT['tolDt'], 'igDt'=>$ADAT['igDt']));		
		}
		
		    if (count($H)>0) $TH[] = $tankorId;
		    if (count($J)>0) $TJ[] = $tankorId;
		    if (is_array($H)) $HSUM = array_merge($HSUM,$H); // $H lehet false is
		    if (is_array($J)) $JSUM = array_merge($JSUM,$J); // $J lehet false is

		if (count($TH) > 0) $_SESSION['alert'][] = 'info:hibas_hianyzasok:'.count($HSUM).':tanev='.$tanev.':tankorIdk='.implode(',',$TH);
		if (count($TJ) > 0) $_SESSION['alert'][] = 'info:hibas_jegyek:'.count($JSUM).':tanev='.$tanev.':tankorIdk='.implode(',',$TJ);

		// Modosítás:
		if ($ADAT['utkozes']=='torles') {
		    // hiányzások és jegyek törlése...
		    hianyzasTorles($HSUM, $tanev, $lr);
			jegyTorles($JSUM, null, $tanev, $lr);
		    $TH = $TJ = array();
		    $HSUM = $JSUM = array();
		}
	    }
	}

	if (  ((count($TH) == 0 && count($TJ) == 0)) || $ADAT['utkozes'] == 'torles' || $ADAT['utkozes'] == 'nemEllenoriz') {

		logAction(
		    array(
			'szoveg'=>'diakFelmentes: '.$ADAT['diakId'].', '.$ADAT['felmentesTipus'].', '.$ADAT['tolDt'].', '.$ADAT['igDt'].', '.$tankorId,
			'tabla'=>'tankorDiakFelmentes'
		    )
		);
		// A tol-ig közé esőket töröljük
		$q = "DELETE FROM tankorDiakFelmentes WHERE diakId=%u AND tankorId=%u AND '%s'<=beDt AND felmentesTipus='%s'";
		$v = array($diakId, $tankorId,$tolDt,$felmentesTipus);
		if ($ADAT['igDt']!='') {
		    $q.= " AND kiDt<='%s'";
		    array_push($v, $ADAT['igDt']);
		}	
		if ($ADAT['ora']!=='') {
		    $q .= " AND ora=%u ";
		    array_push($v, $ADAT['ora']);
		}
		if ($ADAT['nap']!='') {
		    $q .= " AND nap=%u ";
		    array_push($v, $ADAT['nap']);
		}
		$delResult = db_query($q, array('fv' => 'tankorDiakFelmentesFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr );

		if ($ADAT['igDt'] != '') {
		    // a tol-ig intervallumot tartalmazókat ketté kell vágni, ezért a jobb oldali darabot felvesszük
		    $q = "INSERT INTO tankorDiakFelmentes (tankorId,diakId,beDt,kiDt,felmentesTipus,nap,ora,iktatoszam)
			    SELECT tankorId,diakId,'%s' + INTERVAL 1 DAY AS beDt,kiDt,felmentesTipus,nap,ora,iktatoszam FROM tankorDiakFelmentes
			    WHERE diakId=%u AND tankorId=%u
			    AND beDt<'%s' AND (kiDt IS NULL OR '%s'< kiDt) AND felmentesTipus='%s'";
//		    $v = array_merge(array($ADAT['igDt'], $ADAT['diakId']), $TANKORIDS, array($ADAT['tolDt'], $ADAT['igDt']));
		    $v = array($igDt, $diakId, $tankorId, $tolDt, $igDt, $felmentesTipus);
		    $insResult = db_query($q, array('fv' => 'tankorDiakFelmentesFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr );
		}

		// A balról benyúló intervallumokat levágjuk (az "átnyúlókat" is! Így kapjuk a baloldali darabot)
		$q = "UPDATE tankorDiakFelmentes SET kiDt = '%s' - INTERVAL 1 DAY WHERE diakId=%u 
			AND tankorId=%u
			AND beDt<'%s' AND (kiDt IS NULL OR '%s'<=kiDt) AND felmentesTipus='%s'";
		$v = array($tolDt,$diakId,$tankorId,$tolDt,$tolDt, $felmentesTipus);
		if ($ADAT['ora']!=='') {$q .= " AND ora=%u ";array_push($v, $ADAT['ora']);}
		$upResult1 = db_query($q, array('fv' => 'tankorDiakFelmentesFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);

		if ($ADAT['igDt']!='') {
		    // A jobbról benyúló intervallumokat levágjuk ("átnyúló" darab már nincs!)
		    $q = "UPDATE tankorDiakFelmentes SET beDt = '%s' + INTERVAL 1 DAY WHERE diakId=%u 
			    AND tankorId=%u
			    AND beDt<='%s' AND (kiDt IS NULL OR '%s'< kiDt) AND felmentesTipus='%s'";
		    $v = array($igDt,$diakId,$tankorId,$igDt,$igDt, $felmentesTipus);
		    if ($ADAT['ora']!=='') {$q .= " AND ora=%u ";array_push($v, $ADAT['ora']);}
    		    $upResult2 = db_query($q, array('fv' => 'tankorDiakFelmentesFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr );
		}

		if ($nap=='' && ($ora==='' || is_null($ora))) {
		    $q = "INSERT INTO tankorDiakFelmentes (tankorId,diakId,beDt,kiDt,felmentesTipus,iktatoszam) VALUES (%u,%u,'%s','%s','%s','%s')";
		    $v = array($tankorId,$diakId,$tolDt,$igDt,$felmentesTipus,$iktatoszam);
		} elseif ($nap=='' && $ora!=='') {
		    $q = "INSERT INTO tankorDiakFelmentes (tankorId,diakId,beDt,kiDt,felmentesTipus,ora,iktatoszam) VALUES (%u,%u,'%s','%s','%s',%u,'%s')";
		    $v = array($tankorId,$diakId,$tolDt,$igDt,$felmentesTipus,$ora,$iktatoszam);
		} elseif ($nap!='' && ($ora==='' || is_null($ora))) {
		    $q = "INSERT INTO tankorDiakFelmentes (tankorId,diakId,beDt,kiDt,felmentesTipus,nap,iktatoszam) VALUES (%u,%u,'%s','%s','%s',%u,'%s')";
		    $v = array($tankorId,$diakId,$tolDt,$igDt,$felmentesTipus,$nap,$iktatoszam);
		} else {
		    $q = "INSERT INTO tankorDiakFelmentes (tankorId,diakId,beDt,kiDt,felmentesTipus,nap,ora,iktatoszam) VALUES (%u,%u,'%s','%s','%s',%u,%u,'%s')";
		    $v = array($tankorId,$diakId,$tolDt,$igDt,$felmentesTipus,$nap,$ora,$iktatoszam);		
		}
		$result = db_query($q, array('fv' => 'tankorDiakFelmentesFelvesz', 'modul' => 'naplo_intezmeny', 'values' => $v),$lr );

		if ($delResult===false || $upResult1 === false || $upResult2 === false || $insResult===false || $result === false) {
		    if ($olr == '') {
			db_rollback($lr);
			db_close($lr);
		    }
            	    return false;
		}

	    if ($olr == '') {
		db_commit($lr);
		db_close($lr);
	    }
	    return true;

	} else {

	    if ($olr == '') {
		db_rollback($lr);
		db_close($lr);
	    }
	    return false;
	}

    }


    function tankorDiakFelmentesLezar($ADAT, $olr = '') {


	if (!is_numeric($ADAT['tankorDiakFelmentesId'])) return false;

	$lr = ($olr != '') ? $olr : db_connect('naplo_intezmeny');
	if ($olr=='') db_start_strans($lr);

	    $q = "UPDATE tankorDiakFelmentes SET kiDt=('%s' - INTERVAL 1 DAY) WHERE tankorDiakFelmentesId=%u AND (kiDt>='%s' OR kiDt IS NULL)";
	    $v = array($ADAT['kiDt'],$ADAT['tankorDiakFelmentesId'],$ADAT['kiDt']); // ha nem nagyobb, akkor nem bővítjük!
	    $result = db_query($q, array('fv' => 'tankorDiakFelmentesLezar', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);


	if ($result === false) {
		if ($olr == '') {
			db_rollback($lr);
			db_close($lr);
		}
            	return false;

	} else {
	    if ($olr == '') {
		db_commit($lr);
		db_close($lr);
	    }
	    return true;	
	}

    }

?>
