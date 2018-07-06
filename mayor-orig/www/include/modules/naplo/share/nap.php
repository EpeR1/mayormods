<?php
/*
    getNapTipusok
    getNapokSzama
	_genNapok
    getTanitasiNapAdat
    getTanitasiNapSzama
    orarendiHetekHozzarendelese
    napokHozzaadasa
    getMunkatervek
    getMunkatervByOsztalyId
    getMunkatervByTanarId
    getTanitasiHetekSzama
*/
    global $UNNEPNAPOK;
    $UNNEPNAPOK = array(
            '08-20' => 'Szent István király ünnepe',
            '10-23' => '56-os forradalom ünnepe',
            '11-01' => 'Mindenszentek',
            '12-25' => 'Karácsony első napja',
            '12-26' => 'Karácsony második napja',
            '01-01' => 'Újév',
            '03-15' => '48-as forradalom és szabadságharc ünnepe',
	    '05-01' => 'Munka Ünnepe'
    );

    function getNapTipusok() {

        return getEnumField('naplo', 'nap', 'tipus');

    }

    function getNapokSzama($SET = array('osztalyId' => null, 'munkatervId' => 1)) {

	if (isset($SET['osztalyId'])) {
	    $vegzosOsztalyJellegIds = getVegzosOsztalyJellegIds();
	    $oAdat = getOsztalyAdat($SET['osztalyId']);
	    if (
		in_array($oAdat['osztalyJellegId'], $vegzosOsztalyJellegIds) 		// érettségiző osztály
		&& $oAdat['vegzoTanev'] == __TANEV					// most végez
	    ) {
    		$q = "SELECT tipus, COUNT(*) AS db FROM nap 
			LEFT JOIN munkatervOsztaly USING (munkatervId) 
			LEFT JOIN munkaterv USING (munkatervId)
			WHERE osztalyId=%u AND dt <= vegzosZarasDt
			GROUP BY tipus";
	    } else {
    		$q = "SELECT tipus, COUNT(*) AS db FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) WHERE osztalyId=%u GROUP BY tipus";
	    }
	    $v = array($SET['osztalyId']);
	} else {
	    if (!isset($SET['munkatervId'])) $SET['munkatervId'] = 1;
    	    $q = "SELECT tipus, COUNT(*) AS db FROM nap WHERE munkatervId=%u GROUP BY tipus";
	    $v = array($SET['munkatervId']);
	}
        return db_query($q, array('fv' => 'getNapokSzama', 'modul' => 'naplo', 'result' => 'keyvaluepair', 'values' => $v));
    }

    function _genNapok($tolDt,$igDt) {
        $q = "SELECT TO_DAYS('%s') - TO_DAYS('%s') AS diff";
        $nod = db_query($q, array('fv' => '_genNapok', 'modul' => 'naplo_base', 'result' => 'value', 'values' => array($igDt, $tolDt)));
        $_stamp = strtotime($tolDt);
        for ($i = 0; $i <= $nod; $i++) {
            $__stamp = mktime(0, 0, 0, date('m', $_stamp), date('d', $_stamp)+$i , date('y', $_stamp));
            $NAPOK[$i] = date('Y-m-d', $__stamp);
        }
        return $NAPOK;
    }

    // Az ora.php függvényeinek része ide tartozik!
    /*
	A függvény minden olyan nap esetén emeli a tanítási napok számát 1-gyel, melyen a megadott munkatervek legalább 
	egyikén tanítási nap van. Értelmes ez így? (amúgy mit jelentene a tanár haladási naplójában a tanítási nap szám?)
    */
    function getTanitasiNapAdat($DT, $SET = array('munkatervIds' => array())) { // refer to nyomtatas/haladasinaplo.php
	if (is_array($DT) && count($DT)>0) {
	    if (!is_array($SET['munkatervIds']) || count($SET['munkatervIds']) == 0) $SET['munkatervIds'] = array(1);
	    $lr = db_connect('naplo');
	    $q = "SET @napszam=(SELECT COUNT(DISTINCT dt) FROM nap 
			WHERE tipus IN ('tanítási nap','speciális tanítási nap') AND dt<'".$DT[0]."'
			AND munkatervId IN (".implode(',', array_fill(0, count($SET['munkatervIds']), '%u'))."))";
	    db_query($q, array('fv' => 'getTanitasiNapAdat', 'modul' => 'naplo', 'values' => $SET['munkatervIds']), $lr);
	    $q = "SELECT dt,@napszam:=@napszam+1 AS napszam FROM nap WHERE tipus IN ('tanítási nap','speciális tanítási nap') 
		    AND dt IN ('".implode("','",$DT)."')
		    AND munkatervId IN (".implode(',', array_fill(0, count($SET['munkatervIds']), '%u')).") GROUP BY dt";
	    $v = array($dt);
	    $v = $SET['munkatervIds'];
    	    $ret=db_query($q, array('fv' => 'share/nap/getTanitasiNapAdat', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield'=>'dt', 'values' => $v),$lr);
	    db_close($lr);
	    return $ret;
	} else {
	    return false;
	}
    }

    function getTanitasiNapSzama($dt, $munkatervId) {
        $q = "SELECT COUNT(*) FROM nap WHERE tipus IN ('tanítási nap','speciális tanítási nap') AND dt<='%s' AND munkatervId=%u";
        return db_query($q, array('fv' => 'getTanitasiNapSzama', 'modul' => 'naplo', 'result' => 'value', 'values' => array($dt, $munkatervId)));
    }

    /*
	Az órarendi hetek hozzárendelése mindig az összes munkatervet érintő változtatás!
    */
    function orarendiHetekHozzarendelese($tolDt = '', $igDt = '', $Hetek = array(1), $olr = '') {

        global $_TANEV;
            
        if ($tolDt == '') $tolDt = $_TANEV['kezdesDt'];
        if ($igDt == '') $igDt = $_TANEV['zarasDt'];

        if ($olr == '') $lr = db_connect('naplo', array('fv' => 'orarendiHetekHozzarendelese'));
        else $lr = $olr;

	// Az összes munkatervre elvégzi a hozzárendelést...
    	$q = "SELECT dt,tipus,munkatervId FROM nap WHERE dt>='%s' AND dt<='%s' AND tipus='tanítási nap' ORDER BY dt";
    	$v = array($tolDt, $igDt, $munkatervId);
        $r = db_query($q, array('fv' => 'orarendiHetekHozzarendelese', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v), $lr);
        if (!$r) { if ($olr == '') db_close($lr); return false; }
    
        $i = array(); $het = $Hetek[0]; $dow = array();
        foreach ($r as $key => $val) {
            $dt = $val['dt'];
            $tipus = $val['tipus'];
            $munkatervId = $val['munkatervId'];
            $elozo_dow[$munkatervId] = $dow[$munkatervId];
            // DOW lehetne munkatervenként is!
            $dow[$munkatervId] = date('w',strtotime($dt)); if ($dow[$munkatervId] == 0) $dow[$munkatervId] = 7; // Vasárnap a hét utolsó napja - nálunk
            // csak a $dow-okat nézzük, ha tehát egy keddi nap után a következő heti csütörtök jön, akkor nem vált hetet!
            if ($elozo_dow[$munkatervId] >= $dow[$munkatervId]) {
                $i[$munkatervId] = ($i[$munkatervId]+1) % count($Hetek);
                $het = $Hetek[$i[$munkatervId]];
            }
            if ($het=='') $het=1; //szkúzi...
            if ($tipus = 'tanítási nap' && is_numeric($het)) {
                $q = "UPDATE nap SET orarendiHet=%u WHERE dt='%s' AND tipus='tanítási nap' AND munkatervId=%u";
                $v = array($het, $dt, $munkatervId);
            } else {
                $q = "UPDATE nap SET orarendiHet=0 WHERE dt='%s' AND tipus='tanítási nap' AND munkatervId=%u";
                $v = array($dt, $munkatervId);
            }
            $r2 = db_query($q, array('fv' => 'orarendiHetekHozzarendelese', 'modul' => 'naplo', 'values' => $v), $lr);
            if (!$r2) { if ($olr == '') db_close($lr); return false; }
        }

        if ($olr == '') db_close($lr);

    }

    /**
     * Törli, majd felveszi a megadott dátumok közötti napokat - minden munkatervhez!
    **/
    function napokHozzaadasa($tanev, $tolDt, $igDt, $tanevAdat, $lr = null) {

	global $UNNEPNAPOK;

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	$r = array();
	$q = "DELETE FROM `$tanevDb`.`nap` WHERE '%s'<=dt AND dt<='%s'";
	$v = array($tolDt, $igDt);
        $r[] = db_query($q, array('fv' => 'napokHozzáadása/delete', 'modul' => 'naplo', 'values' => $v), $lr);
        for ($stamp = strtotime($tolDt.' 08:00:00'); $stamp <= strtotime($igDt.' 08:00:00');$stamp = $stamp + 24*60*60 ) {
                $dt = date('Y-m-d', $stamp);
                $ho_nap = date('m-d', $stamp);
		$ho = date('m', $stamp);
                $dow = date('w', $stamp);
                if ($UNNEPNAPOK[$ho_nap] != '') { // Fix dátumú, állami ünnepnapok
                        $tipus = 'tanítási szünet';
                        $megjegyzes = $UNNEPNAPOK[$ho_nap];
                        $orarendiHet = 0;
                } elseif ($dow == '0' or $dow == '6') { // Hétvégék
                        $tipus = 'tanítási szünet';
                        $megjegyzes = '';
                        $orarendiHet = 0;
		} elseif ($ho == 7) { //  Július hónap - nyári szünet
                        $tipus = 'tanítási szünet';
                        $megjegyzes = 'Nyári szünet';
                        $orarendiHet = 0;
                } elseif (strtotime($tanevAdat['zarasDt'])<strtotime($dt) || strtotime($dt) < strtotime($tanevAdat['kezdesDt'])) {
			$tipus = 'szorgalmi időszakon kívüli munkanap';
                        $megjegyzes = '';
                        $orarendiHet = 0;
		} else {
                        $tipus = 'tanítási nap';
                        $megjegyzes = '';
                        $orarendiHet = 1;
                }

                $q = "INSERT INTO `$tanevDb`.`nap` (dt,tipus,megjegyzes,orarendiHet,munkatervId)
			    SELECT '%s' AS dt, '%s' AS tipus, '%s' AS megjegyzes, %u AS orarendiHet, munkatervId
			    FROM `$tanevDb`.`munkaterv`";
                $v = array($dt, $tipus, $megjegyzes, $orarendiHet);
                $r[] = db_query($q, array('fv' => 'napokHozzáadása/insert', 'modul' => 'naplo', 'values' => $v), $lr);
        
        }
	return !in_array(false, $r);

    }

    function getMunkatervek($SET = array('result' => 'indexed')) {
	if ($SET['result']==='idonly') {
	    $q = "SELECT munkatervId FROM munkaterv";
	} else {
            $q = "SELECT * FROM munkaterv";
	}
    	$R = db_query($q, array('fv' => 'getMunkatervek', 'modul' => 'naplo', 'result' => $SET['result'], 'keyfield' => 'munkatervId'));
	return $R;
    }

    function getMunkatervByOsztalyId($osztalyId, $SET = array('result' => 'value')) {

	if (!is_array($osztalyId)) $osztalyId = array($osztalyId);
	if (count($osztalyId) == 0) return false;
	$q = "SELECT DISTINCT munkatervId FROM munkaterv LEFT JOIN munkatervOsztaly USING (munkatervId) WHERE osztalyId IN (".implode(',',array_fill(0, count($osztalyId), '%u')).")";
	$v = $osztalyId;
	return db_query($q, array('fv' => 'getMunkatervByOsztalyId', 'modul' => 'naplo', 'values'=>$v, 'result' => $SET['result']));

    }
    function getMunkatervByDiakId($diakId, $SET = array('tolDt'=>null, 'igDt'=>null)) {

	if (!is_array($diakId)) $diakId = array($diakId);
	if (count($diakId) == 0) return false;
	$q = "SELECT DISTINCT munkatervId FROM munkaterv LEFT JOIN munkatervOsztaly USING (munkatervId) LEFT JOIN `".__INTEZMENYDBNEV."`.osztalyDiak USING (osztalyId) WHERE diakId IN (".implode(',',array_fill(0, count($diakId), '%u')).")";
	$v = $diakId;
	return db_query($q, array('fv' => 'getMunkatervByOsztalyId', 'modul' => 'naplo', 'values'=>$v, 'result' => 'value'));

    }

    /**
     * A függvény az orarendiOra tábla alapján keresi meg a tanár tanköreit, nem a tankorTanar alapján!!!!
     *
    **/
    function getMunkatervByTanarId($tanarId, $SET = array('result' => 'indexed', 'tanev'=>__TANEV, 'tolDt'=>null, 'igDt'=>null)) {

	$tanev = isset($SET['tanev'])?$SET['tanev']:__TANEV;
	$tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
        initTolIgDt($tanev, $tolDt, $igDt);
	if (!is_array($tanarId)) $tanarId = array($tanarId);
	// lekérdezzük a tankorTanar tábla alapján a tanárhoz tartozó tanköröket, majd ezek osztályaihoz tartozó munkaterveket
	$q = "SELECT DISTINCT osztalyId FROM tankorTanar LEFT JOIN tankorOsztaly USING (tankorId) 
		WHERE tanarId IN (".implode(',', array_fill(0, count($tanarId), '%u')).") AND beDt<='%s' AND (kiDt IS NULL OR '%s'<=kiDt)";
	$v = mayor_array_join($tanarId, array($igDt, $tolDt));
	$osztalyId = db_query($q, array('fv'=>'getMunkatervByTanarId', 'modul'=>'naplo_intezmeny', 'result'=>'idonly', 'values'=>$v));

	return getMunkatervByOsztalyId($osztalyId, $SET);
    }

    /*
	A függvény munkatervId, osztalyId vagy tankorId (az egyik megadása kötelező!) alapján adja meg az összes, vagy a végzős tanítási hetek számát.
	Ha nincs megadva, hogy az osztály vagy tankör végzős-e, akkor lekérdezzük, hogy érettségiző osztály-e az osztályjelleg alapján.
    */
    function getTanitasiHetekSzama($SET = array('munkatervId'=>null, 'osztalyId'=>null, 'vegzos'=>false)) {

	if ($SET['munkatervId'] != '') {
	    if ($SET['vegzos']) {
		$q = "SELECT CEIL(COUNT(*)/%u) AS tanitasiHet FROM nap
			WHERE tipus IN ('tanítási nap','speciális tanítási nap') AND munkatervId=%u
			AND dt<=(SELECT vegzosZarasDt FROM munkaterv WHERE munkatervId=nap.munkatervId)";
	    } else {
		$q = "SELECT CEIL(COUNT(*)/%u) AS tanitasiHet FROM nap 
			WHERE tipus IN ('tanítási nap','speciális tanítási nap') AND munkatervId=%u";
	    }
	    $v = array(__TANITASINAP_HETENTE, $SET['munkatervId']);
	} elseif ($SET['osztalyId'] != '') {
	    if (!isset($SET['vegzos'])) {
		$VO = getVegzosOsztalyok(array('tanev'=>__TANEV,'result'=>'idonly','vizsgazo'=>true));
		$SET['vegzos'] = (is_array($VO)&&in_array($SET['osztalyId'],$VO));
	    }
	    if ($SET['vegzos']) {
		$q = "SELECT CEIL(COUNT(*)/%u) AS tanitasiHet FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) 
			WHERE tipus IN ('tanítási nap','speciális tanítási nap') 
			AND osztalyId=%u
			AND dt<=(SELECT vegzosZarasDt FROM munkaterv WHERE munkatervId=nap.munkatervId)";
	    } else {
		$q = "SELECT CEIL(COUNT(*)/%u) AS tanitasiHet FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) 
			WHERE tipus IN ('tanítási nap','speciális tanítási nap') 
			AND osztalyId=%u";
	    }
	    $v = array(__TANITASINAP_HETENTE, $SET['osztalyId']);
	} elseif ($SET['tankorId'] != '') {
	    if (!isset($SET['vegzos'])) {
		$SET['vegzos'] = tankorVegzosE($tankorId, __TANEV, array('tagokAlapjan' => true, 'tolDt' => null, 'igDt' => null));
	    }
	    if ($SET['vegzos']) {
		$q = "SELECT CEIL(COUNT(DISTINCT dt)/%u) AS tanitasiHet FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) 
			    WHERE tipus IN ('tanítási nap','speciális tanítási nap') 
			    AND osztalyId IN (SELECT osztalyId FROM ".__INTEZMENYDBNEV.".tankorOsztaly WHERE tankorId=%u)
			    AND dt<=(SELECT vegzosZarasDt FROM munkaterv WHERE munkatervId=nap.munkatervId)";
	    } else {
		$q = "SELECT CEIL(COUNT(DISTINCT dt)/%u) AS tanitasiHet FROM nap LEFT JOIN munkatervOsztaly USING (munkatervId) 
			    WHERE tipus IN ('tanítási nap','speciális tanítási nap') 
			    AND osztalyId IN (SELECT osztalyId FROM ".__INTEZMENYDBNEV.".tankorOsztaly WHERE tankorId=%u)";
	    }
	    $v = array(__TANITASINAP_HETENTE, $SET['tankorId']);
	} else {
	    $_SESSION['alert'][] = 'message:empty_field:getTanitasiHetekSzama';
	    return false;
	}

	if (__TANITASI_HETEK_OVERRIDE === true) {
	    if ($SET['vegzos']) {
		if (defined('___VEGZOS_TANITASI_HETEK_SZAMA')) return ___VEGZOS_TANITASI_HETEK_SZAMA;
	    } else {
		if (defined('___TANITASI_HETEK_SZAMA')) return ___TANITASI_HETEK_SZAMA;
	    }
	}
	return db_query($q, array('debug'=>false,'fv'=>'getTanitasiHetekSzama','modul'=>'naplo','result'=>'value','values'=>$v));
    }

    function getOsztalyUtolsoTanitasiNap($osztalyId, $tanev=__TANEV) {

	global $_TANEV;

	if ($tanev == __TANEV) $TA = $_TANEV;
	else $TA = getTanevAdat($tanev);
	$tanevDb = tanevDbNev(__INTEZMENY,$tanev);

	// idén érettségiző vagy szakmai vizsgát tevő osztály-e
	$q = "SELECT COUNT(*) FROM osztaly LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) 
		WHERE vegzesKovetelmenye IN ('érettségi vizsga','szakmai vizsga') AND vegzoTanev=%u AND osztalyId=%u";
	$v = array($tanev, $osztalyId);
	$vizsgazoE = (db_query($q, array('fv'=>'getOsztalyUtolsoTanitasiNap','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v)) == 1);

	if ($vizsgazoE) {
	    $q = "SELECT vegzosZarasDt FROM `".$tanevDb."`.munkaterv LEFT JOIN `".$tanevDb."`.munkatervOsztaly USING (munkatervId) WHERE osztalyId=%u";
	    return db_query($q, array('debug'=>false,'fv'=>'getOsztalyUtolsoTanitasiNap/dt','modul'=>'naplo','result'=>'value','values'=>array($osztalyId)));
	} else {
	    return $TA['zarasDt'];
	}

    }

?>