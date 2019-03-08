<?php

    function oraBeirhato($oraId, $olr='') { // kötelező: oraId // modosithatoOra sql wrap
	global $_TANEV;
	$result = false;
        $lr = $olr=='' ? db_connect('naplo', array('fv' => 'oraBeirhato')):$olr;
	$q = "SELECT * FROM ora WHERE oraId = %u";
	$values = array('oraId'=>$oraId);
	$oraAdat = db_query($q, array('fv'=>'oraBeirhato','modul'=>'naplo','result'=>'record','values'=>$values),$lr);
        if ($olr == '') db_close($lr);
        return modosithatoOra($oraAdat);
    }

    function modosithatoOra($haladasiOraAdat) { // lásd még: oraBeirhato($oraId)

        global $_TANEV;
        if (!defined('_HALADASI_HATARIDO')) $_SESSION['alert'][] = 'info::modosithatoOra.not defined._HALADASI_HATARIDO';
	// if (!defined('__USERTANARID')) return false;
        /* feladat típusokra vonatkozó beállítások */
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
        $tanara = ($i < $count) || $haladasiOraAdat['ki']==__USERTANARID; // nem mindig van 'tanar' adat! Az nem része az ora rekordnak

	return ($_TANEV['szemeszter'][1]['statusz'] == 'aktív') // Csak aktív szemeszterbe írhatunk
                && (
                    ((__VEZETOSEG || __NAPLOADMIN) && $Feladat && $haladasiOraAdat['eredet']=='órarend')
                    || $time <= time()
                )       // A jövőbeli órák nem írhatók be, kivéve, ha az előírt tanári feladat (pl versenyfelügyelet)!
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

    function ujOraFelvesz($ADAT,$olr='') { // --TODO: a függvény figyelhetné a tagok óraütközését!
        $lr = $olr=='' ? db_connect('naplo', array('fv' => 'ujOraFelvesz')):$olr;
        $q = "SELECT count(*) FROM ora WHERE dt='%s' AND ora=%u AND ki=%u";
        $values = array($ADAT['dt'],$ADAT['ora'],$ADAT['ki']);
        $c = db_query($q, array('fv'=>'ujOraFelvesz/1','modul'=>'naplo','result'=>'value','values'=>$values),$lr);
        if ($c==0) { // csak ha még nincs adott nap adott órájára rögzítve "feladata"
	    if ($ADAT['feladatTipusId']==0) $ADAT['feladatTipusId']='NULL';
	    if ($ADAT['tankorId']==0) $ADAT['tankorId']='NULL';
            $q = "INSERT INTO `ora` (`dt`,`ora`,`ki`,`tipus`,`eredet`,`feladatTipusId`,`munkaido`,`leiras`,`tankorId`) VALUES ('%s',%u,%u,'%s','%s',%s,'%s','%s',%s)";
            $values = array($ADAT['dt'],$ADAT['ora'],$ADAT['ki'],$ADAT['tipus'],$ADAT['eredet'],$ADAT['feladatTipusId'],$ADAT['munkaido'],$ADAT['leiras'],$ADAT['tankorId']);
            $RESULT = db_query($q, array('fv'=>'ujOraFelvesz','modul'=>'naplo','result'=>'insert','values'=>$values),$lr);
        }
        if ($olr == '') db_close($lr);
        return $RESULT;
    }

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
//          $RESULT = false; // igaziból nincs hiba, hisz nem csináltunk semmit
            $_SESSION['alert'][] = 'message:wrong_data:nem modosithato ora!!!';
        }
        if ($olr == '') db_close($lr);

        return $RESULT;

    }

?>