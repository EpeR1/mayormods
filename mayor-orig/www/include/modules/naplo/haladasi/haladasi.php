<?php

    require_once ( 'include/modules/naplo/share/oraModifier.php' );

/*
    function updateHaladasiNaploOra($oraId, $leiras, $csoportAdat = '', $ki = '', $olr = '') {

	$RESULT = true;

        $lr = $olr=='' ? db_connect('naplo', array('fv' => 'updateHaladasiNaploOra')):$olr;
	// A módosítás előtti állapot lekérdezése
        $oraAdat = getOraAdatById($oraId, __TANEV, $lr);
	$dt = $oraAdat['dt'];
	// Melyik tankör lesz a módosítás után
	if ($csoportAdat != '') list($csoportId, $tankorId) = explode(':', $csoportAdat);
	else $tankorId = $oraAdat['tankorId'];

	// force to be numeric (CHECK)
	$csoportId = intval($csoportId);
	$tankorId = intval($tankorId);

//	$oraAdat['tanar'] =  getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'), $lr);
	$oraAdat['tanar'] =  getTankorTanaraiByInterval($tankorId, array('tanev' => __TANEV, 'tolDt' => $dt, 'igDt' => $dt, 'result' => 'nevsor'), $lr);
	// Melyik ki id lesz módosítás után
	if ($ki != '') $tanarId = $ki; else $tanarId = $oraAdat['ki'];
        if (modosithatoOra($oraAdat)) {

	    // Tananyag beírása
            $q = "UPDATE ora SET leiras='%s'";
	    $v = array($leiras);
            if ($ki != '') { // Ha több tanára van a tankörnek, akkor az átváltható
		$i = 0;
		while ($i < ($db = count($oraAdat['tanar'])) && $ki != $oraAdat['tanar'][$i]['tanarId']) $i++;
        	if ($i < $db) {
		    $q .= ",ki=%u";
		    $v[] = $ki;
		}
	    }
            //!!! A csoportok tankörei válthatóak - ha ugyanaz a tanár tartja
	    if ($csoportAdat != '' && $oraAdat['tankorId'] != $tankorId) {
		$q2 = "SELECT COUNT(tankorId) FROM tankorCsoport LEFT JOIN ".__INTEZMENYDBNEV.".tankorTanar USING (tankorId)
		    WHERE csoportId = %u AND tanarId = %u
		    AND tankorId IN (%u,%u)
		    AND (kiDt IS NULL OR kiDt>='%s') AND beDt<='%s'";
		$v2 = array($csoportId, $tanarId, $tankorId, $oraAdat['tankorId'], $dt, $dt);
		$num = db_query($q2, array('fv' => 'updateHaladasiNaploOra', 'modul' => 'naplo', 'result' => 'value', 'values' => $v2), $lr);
		if (!$num) {
		    $_SESSION['alert'][] = 'message:wrong_data:updateHaladasiNaploOra:'.$num.':'.$csoportId; 
		    $RESULT = false;
		} elseif ($num == 2) {
		    $q .= ",tankorId=%u";
		    $v[] = $tankorId;
		} else { 
		    $_SESSION['alert'][] = 'message:wrong_data:updateHaladasiNaploOra:'.$num.':'.$csoportId; 
		    $RESULT = false;
		}
	    }
	    if ($RESULT!==false) {
        	$q .= " WHERE oraId=%u";
		$v[] = $oraId;
		$RESULT = db_query($q, array('fv' => 'updateHaladasiNaploOra', 'modul' => 'naplo', 'values' => $v), $lr);
		//$_SESSION['alert'][] = $q;
	    }
        } else {
//	    $RESULT = false; // igaziból nincs hiba, hisz nem csináltunk semmit
	    $_SESSION['alert'][] = 'message:wrong_data:nem modosithato ora!!!'; 
	}
        if ($olr == '') db_close($lr);

	return $RESULT;

    }
*/
/*  elköltözött a share/oraModifier.php - be
    function modosithatoOra($haladasiOraAdat) {

	global $_TANEV;
	if (!defined('_HALADASI_HATARIDO')) $_SESSION['alert'][] = 'info::modosithatoOra.not defined._HALADASI_HATARIDO';
	// feladat típusokra vonatkozó beállítások 
	$Feladat = is_numeric($haladasiOraAdat['feladatTipusId']) && $haladasiOraAdat['tipus']=='egyéb'; // 22-26 óra feletti kötött munkaidőbe tartó feladat
	$tanarFeladat = $Feladat && defined('__USERTANARID') && __USERTANARID==$haladasiOraAdat['ki']; // ... amit az épp bejelentkezett tanár tart
	$sajatTanarFeladat  = $tanarFeladat && $haladasiOraAdat['eredet']=='plusz'; // ... és ő is vett fel
	$eloirtTanarFeladat = $tanarFeladat && $haladasiOraAdat['eredet']=='órarend'; // ... illetve, amit számára a vezetőség előírt (nem törölhető)
        $time = strtotime($haladasiOraAdat['dt']);
        $ki = $haladasiOraAdat['ki'];
	$normalOra = (in_array($haladasiOraAdat['tipus'],array('normál','normál máskor')));
	for ($i = 0;
	    (
		($i < ($count = count($haladasiOraAdat['tanar'])))
		&& ($haladasiOraAdat['tanar'][$i]['tanarId'] != __USERTANARID)
	    );
	    $i++
	); 
        $tanara = ($i < $count);

        return ($_TANEV['szemeszter'][1]['statusz'] == 'aktív')	// Csak aktív szemeszterbe írhatunk
		&& (
		    ((__VEZETOSEG || __NAPLOADMIN) && $Feladat && $haladasiOraAdat['eredet']=='órarend')
		    || $time <= time()
		)	// A jövőbeli órák nem írhatók be, kivéve, ha az előírt tanári feladat (pl versenyfelügyelet)!
		&& (
        	    // Az admin bármikor módosíthat - de csak vezetői utasításra teszi!
        	    __NAPLOADMIN
        	    // Az igazgató naplózárásig pótolhat, javíthat - utána elvileg nyomtatható a napló!
        	    || (__VEZETOSEG and strtotime(_ZARAS_HATARIDO) <= $time)
		    || (
            		__TANAR
            		&& (
			    // a számára felvett óra nem módosítható
			    !$eloirtTanarFeladat
                	    && (
                		// tanár a saját tanköreinek óráit a _HALADASI_HATARIDO-ig módosíthatja
				($normalOra && $tanara && (strtotime(_HALADASI_HATARIDO) <= $time))
                		// tanár az általa helyettesített/felügyelt/összevont órát _visszamenőleg_ a _HELYETTESITES_HATARIDO-ig módosíthatja
                		|| (!$normalOra && (__USERTANARID == $ki) && (strtotime(_HELYETTESITES_HATARIDO) <= $time) && $Feladat===false)
				// a kötött munkaidőben végzett feladatok _HALADASI_HATARIDŐIG módosíthatók
				|| ($tanarFeladat && (strtotime(_HALADASI_HATARIDO) <= $time))
			    )
            		)
        	    )
    		);
    }
*/

    function getOraszamByOraId($oraId, $olr='') {


        $lr = ($olr=='') ? db_connect('naplo', array('fv' => 'getOraszamByOraId')) : $olr;

        $q = "SELECT tankorId, dt, ora FROM ora WHERE oraId=%u";
	$r = db_query($q, array('fv' => 'getOraszamByOraId', 'modul' => 'naplo', 'result' => 'record', 'values' => array($oraId)), $lr);

        $tankorId = $r['tankorId'];
	$dt = $r['dt'];
	$ora = $r['ora'];
	
	if (defined('__ORASZAMOT_NOVELO_TIPUSOK')) {
	    $oraszamNoveloTipus = explode(',', __ORASZAMOT_NOVELO_TIPUSOK);
	} else {
	    if (!in_array('info:missing_constant:__ORASZAMOT_NOVELO_TIPUSOK',$_SESSION['alert'])) $_SESSION['alert'][] = 'info:missing_constant:__ORASZAMOT_NOVELO_TIPUSOK';
	    $oraszamNoveloTipus = array('normál', 'normál máskor', 'helyettesítés', 'összevonás');
	}
        $q = "SELECT count(oraId) FROM ora
                    WHERE tankorId=%u
                    AND tipus IN ('".implode("','", array_fill(0, count($oraszamNoveloTipus), '%s'))."')
                    AND (dt<'%s' OR (dt='%s' AND ora<=%u))";
	$v = mayor_array_join(array($tankorId), $oraszamNoveloTipus, array($dt, $dt, $ora));
	$oraszam = db_query($q, array('fv' => 'getOraszamByOraId', 'modul' => 'naplo', 'result' => 'value', 'values' => $v), $lr);

        if ($olr == '') db_close($lr);
        return $oraszam;
    }

    function getHaladasi($Tankorok, $munkatervIds, $orderBy, $tanarId = '', $csakUres=false, $teremId=false) {

	$ret = array();

	// Munkatervidk
	if (!is_array($munkatervIds) || count($munkatervIds)==0) $munkatervIds = array(1); // a default 

	// Az érintett tankörök id-inek listája
	$tankorIds = $tankorAdat = array();
	if (is_array($Tankorok) && ($count = count($Tankorok)) > 0) {
	    $tankorFeltetel = 'tankorId IN (' . $Tankorok[0]['tankorId'];
	    $tankorIds[] = $Tankorok[0]['tankorId'];
	    $Tankorok[0]['tanar'] = getTankorTanaraiByInterval($Tankorok[0]['tankorId'], array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'));
	    $tankorAdat[$Tankorok[0]['tankorId']] = $Tankorok[0];
	    for ($i = 1; $i < $count; $i++) {
		$tankorFeltetel .= ', '.$Tankorok[$i]['tankorId'];
		$tankorIds[] = $Tankorok[$i]['tankorId'];
		$Tankorok[$i]['tanar'] = getTankorTanaraiByInterval($Tankorok[$i]['tankorId'], array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'));
		$tankorAdat[$Tankorok[$i]['tankorId']] = $Tankorok[$i];
	    }
	    $tankorFeltetel .= ')';
	} 
	elseif ($tanarId=='') return false;
	// else return false; // Ha egy kollégának nincs rendszeres órája, tanköre, de helyettesít, akkor meg kell jelenjenek ezek az órái... (Bug #53)

	if ($teremId!==false && is_numeric($teremId)) {
	    $teremFeltetel = ' and teremId = '.$teremId;
	} else
	    $teremFeltetel = '';

	// Ha tanarId is van, akkor az általa helyettesített órák is kellenek
	if ($tanarId != '') {
	    if (isset($tankorFeltetel)) $kiFeltetel = 'OR ki = '.$tanarId;
	    else $kiFeltetel = 'ki = '.$tanarId;
	}
        if (isset($tankorFeltetel) || isset($kiFeltetel)) $feltetel = "AND ($tankorFeltetel $kiFeltetel)";
	if (isset($csakUres) && $csakUres==true) $feltetel .= " AND (leiras='' OR leiras IS NULL) ";

	// Csatlakozás az adatbázishoz
	$lr = db_connect('naplo', array('fv' => 'getHaladasi'));
        $q = "SELECT oraId, dt, ora, ki, kit, tankorId, teremId, ora.leiras, tipus, eredet, csoportId, feladatTipusId
                        FROM ora 
			LEFT JOIN tankorCsoport USING (tankorId) 
			LEFT JOIN ".__INTEZMENYDBNEV.".feladatTipus USING (feladatTipusId)
                            WHERE dt>='%s' AND dt<='%s' AND tipus NOT LIKE 'elmarad%%'
                            $feltetel $teremFeltetel
                            ORDER BY ".implode(',',$orderBy);
	$v = array(_SHOW_DAYS_FROM, _SHOW_DAYS_TO);
	$r = db_query($q, array('fv' => 'getHaladasi', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
	if ($r===false) {
	    db_close($lr);
	    return false;
	}
	foreach ($r as $i => $sor) {
	    // ha nincs a tankorok kozott a tankorId, akkor le kell kérdezni az adatait
	    if (!in_array($sor['tankorId'],$tankorIds)) {
		$T = getTankorById($sor['tankorId'], __TANEV);
		$tankorIds[] = $sor['tankorId'];
		$tankorAdat[$sor['tankorId']] = $T[0];
		$Tankorok[$i]['tanar'] = getTankorTanaraiByInterval($sor['tankorId'], array('tanev' => __TANEV, 'tolDt' => _SHOW_DAYS_FROM, 'igDt' => _SHOW_DAYS_TO, 'result' => 'nevsor'));
	    }
	    $sor['kiCn'] = getTanarNevById($sor['ki'], $lr);
	    $sor['tankorNev'] = $tankorAdat[$sor['tankorId']]['tankorNev'];
	    $sor['tankorTipusId'] = $tankorAdat[$sor['tankorId']]['tankorTipusId'];
	    $sor['oraszam'] = getOraszamByOraId($sor['oraId'], $lr);
	    $sor['tanar'] = $tankorAdat[$sor['tankorId']]['tanar'];
	    // Az óracsoportokat is!!!
	    if (isset($sor['csoportId']) && $tanarId != '') { // Csak tanár nézet esetén lehet váltani!!!
		if (!is_array($tankorAdat[$sor['tankorId']]['csoport'])) {
		    // Csoport adatok lekérdezése
		    // Ha minden oldalon le akarjuk kérdezi a csoportokat, akkor valahogy így...
		    // if ($sor['kit'] == '') $tanarId = $sor['ki'];
		    // else $tanarId = $sor['kit'];
		    $q = "SELECT DISTINCT tankorCsoport.tankorId AS tankorId, tankorNev
			FROM tankorCsoport
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
			LEFT JOIN ".__INTEZMENYDBNEV.".tankorTanar USING (tankorId)
			WHERE tanarId=%u AND csoportId=%u
			AND beDt<='"._SHOW_DAYS_TO."' AND (kiDt IS NULL OR '"._SHOW_DAYS_FROM."'<=kiDt)
			AND tanev=" . __TANEV;
		    $v = array($tanarId, $sor['csoportId']);
		    $r_cs = db_query($q, array('fv' => 'getHaladasi/csoport', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
		    if ($r_cs===false) { //!!!! nem jó simán a tagadás!
			db_close($lr);
			return false;
		    }
		    foreach ($r_cs as $key => $val) {
			$tankorAdat[$sor['tankorId']]['csoport'][] = $val;
		    }
		}
		$sor['csoport'] = $tankorAdat[$sor['tankorId']]['csoport'];
	    }
	    $ret[$sor['dt']][] = $sor;
	}
        // Nap információk lekérdezése
        $q = "SELECT dt,tipus,megjegyzes,orarendiHet FROM nap
                WHERE dt>='%s' AND dt<='%s' AND munkatervId IN (".implode(',', $munkatervIds).")";
	$v = array(_SHOW_DAYS_FROM, _SHOW_DAYS_TO);
	$ret['napok'] = db_query($q, array('fv' => 'getHaladasi', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'dt', 'values' => $v), $lr);
	if (!$ret['napok']) {
	    db_close($lr);
	    return false;
	}

        // dolgozatok lekérdezése;
        $ret['dolgozatok'] = getTankorDolgozatok($tankorIds,true,_SHOW_DAYS_FROM,_SHOW_DAYS_TO, $lr);
        db_close($lr);
        return $ret;
    }

    function haladasiTeremModositas($oraId,$teremId,$lr) {
        if (!is_numeric($oraId) || !is_numeric($teremId)) return false;
        $lr = $olr=='' ? db_connect('naplo', array('fv' => 'haladasiTeremModositas')):$olr;
        $q = "UPDATE ora SET teremId=%u WHERE oraId=%u";
        $v = array($teremId,$oraId);
        $RESULT = db_query($q, array( 'fv' => 'haladasiTeremModositas','modul' => 'naplo', 'values' => $v), $lr);
        if ($olr == '') db_close($lr);
	return $RESULT;
    }

?>
