<?php
    /*
	Vigyázat!! A létszám adatok csak az épp aktuális státuszokkal dolgoznak. Visstamenőleg, korábbi évekre nézni őket nincs értelme.
     */

    function getDiakLetszamByStatusz() {

	$q = "select statusz, count(*) as letszam from diak group by statusz";
	$ret = db_query($q, array('fv'=>'getDiakLetszamByStatusz','modul'=>'naplo_intezmeny','result'=>'keyvaluepair'));
	$q = "select statusz, count(*) as letszam from diak where nem='fiú' group by statusz";
	$ret['fiú'] = db_query($q, array('fv'=>'getDiakLetszamByStatusz/fiú','modul'=>'naplo_intezmeny','result'=>'keyvaluepair'));
	$q = "select statusz, count(*) as letszam from diak where nem='lány' group by statusz";
	$ret['lány'] = db_query($q, array('fv'=>'getDiakLetszamByStatusz/lány','modul'=>'naplo_intezmeny','result'=>'keyvaluepair'));

	return $ret;

    }

    function getDiakLetszamByOsztalyId($osztalyIds) {
	if (is_array($osztalyIds) && count($osztalyIds)>0) {
	    $q = "select osztalyId, count(*) as letszam from diak left join osztalyDiak using (diakId) 
		where statusz in ('jogviszonyban van','magántanuló','egyéni munkarend') and osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") group by osztalyId";
	    $ret = db_query($q, array('fv'=>'getDiakLetszamByStatusz','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$osztalyIds));
	    $q = "select osztalyId, count(*) as letszam from diak left join osztalyDiak using (diakId) 
		where statusz in ('jogviszonyban van','magántanuló','egyéni munkarend') and osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") and nem='fiú' group by osztalyId";
	    $ret['fiú'] = db_query($q, array('fv'=>'getDiakLetszamByStatusz/fiú','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$osztalyIds));
	    $q = "select osztalyId, count(*) as letszam from diak left join osztalyDiak using (diakId) 
		where statusz in ('jogviszonyban van','magántanuló','egyéni munkarend') and osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") and nem='lány' group by osztalyId";
	    $ret['lány'] = db_query($q, array('fv'=>'getDiakLetszamByStatusz/lány','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$osztalyIds));

	    return $ret;

	} else {
	    return false;
	}
    }

    function getTanarLetszamByBesorolas() {
	$q = "select besorolas, count(*) as letszam from tanar where statusz<>'jogviszonya lezárva' and statusz<>'külső óraadó' group by besorolas";
	return db_query($q, array('fv'=>'getTanarLetszamByBesorolas','modul'=>'naplo_intezmeny','result'=>'keyvaluepair'));
    }

    function getTanarLetszamByStatusz() {
	$q = "select statusz, count(*) as letszam from tanar group by statusz";
	return db_query($q, array('fv'=>'getTanarLetszamByBesorolas','modul'=>'naplo_intezmeny','result'=>'keyvaluepair'));
    }

    function getTankorOraszamOsszesites($tankorTipusIds) {

	$q = "select sum(oraszam/2) from tankorSzemeszter where tanev=".__TANEV;
	$ret['összes'] = db_query($q, array('fv'=>'getTankorOraszamOsszesites','modul'=>'naplo_intezmeny','result'=>'value'));
	$q = "select sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) 
	    where tankorTipusId in (".implode(',', array_fill(0, count($tankorTipusIds['óratervi']), '%u')).") and tanev=".__TANEV;
	$ret['óratervi'] = db_query($q, array('fv'=>'getTankorOraszamOsszesites/óratervi','modul'=>'naplo_intezmeny','result'=>'value','values'=>$tankorTipusIds['óratervi']));
	$q = "select sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) 
	    where tankorTipusId in (".implode(',', array_fill(0, count($tankorTipusIds['tanórán kívüli']), '%u')).") and tanev=".__TANEV;
	$ret['tanórán kívüli'] = db_query($q, array('fv'=>'getTankorOraszamOsszesites/tanórán kívüli','modul'=>'naplo_intezmeny','result'=>'value','values'=>$tankorTipusIds['tanórán kívüli']));

	return $ret;
    }

    function getTargyOraszamok($tankorTipusIds) {

	$q = "select targyId, sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) 
	    where tanev=".__TANEV." group by targyId";
	$ret['összes'] = db_query($q, array('fv'=>'getTargyOraszamok','modul'=>'naplo_intezmeny','result'=>'keyvaluepair'));
	$q = "select targyId, sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) 
	    where tankorTipusId in (".implode(',', array_fill(0, count($tankorTipusIds['óratervi']), '%u')).") and tanev=".__TANEV." group by targyId";
	$ret['óratervi'] = db_query($q, array('fv'=>'getTargyOraszamok/óratervi','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$tankorTipusIds['óratervi']));
	$q = "select targyId, sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) 
	    where tankorTipusId in (".implode(',', array_fill(0, count($tankorTipusIds['tanórán kívüli']), '%u')).") and tanev=".__TANEV." group by targyId";
	$ret['tanórán kívüli'] = db_query($q, array('fv'=>'getTargyOraszamok/tanórán kívüli','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$tankorTipusIds['tanórán kívüli']));

	return $ret;
    }

    function getOsztalyOraszamok($osztalyIds, $tankorTipusIds) {

	$q = "select osztalyId, sum(oraszam/2) from tankorSzemeszter left join tankorOsztaly using (tankorId) 
	    where osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
	    and tanev=".__TANEV." group by osztalyId";
	$ret['összes'] = db_query($q, array('fv'=>'getOsztalyOraszamok','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$osztalyIds));
	$q = "select osztalyId, sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) left join tankorOsztaly using (tankorId)
	    where tankorTipusId in (".implode(',', array_fill(0, count($tankorTipusIds['óratervi']), '%u')).") 
	    and osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
	    and tanev=".__TANEV." group by osztalyId";
	$v = array_merge($tankorTipusIds['óratervi'], $osztalyIds);
	$ret['óratervi'] = db_query($q, array('fv'=>'getOsztalyOraszamok/óratervi','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$v));
	$q = "select osztalyId, sum(oraszam/2) from tankorSzemeszter left join tankor using (tankorId) left join tankorOsztaly using (tankorId)
	    where tankorTipusId in (".implode(',', array_fill(0, count($tankorTipusIds['tanórán kívüli']), '%u')).") 
	    and osztalyId in (".implode(',', array_fill(0, count($osztalyIds), '%u')).") 
	    and tanev=".__TANEV." group by osztalyId";
	$v = array_merge($tankorTipusIds['tanórán kívüli'], $osztalyIds);
	$ret['tanórán kívüli'] = db_query($q, array('fv'=>'getOsztalyOraszamok/tanórán kívüli','modul'=>'naplo_intezmeny','result'=>'keyvaluepair','values'=>$v));

	return $ret;
    }

    function getTankorLetszamStat() {
	global $_TANEV;
	$r = getTankorByTanev(__TANEV);
	foreach ($r as $idx => $tAdat) {
	    $return[ $tAdat['targyId'] ]['tankorIds'][] = $tAdat['tankorId'];
	    $letszam = getTankorLetszam($tAdat['tankorId'], array('refDt'=>$_TANEV['kezdesDt']));
	    $return[ $tAdat['targyId'] ]['sum'] += $letszam;
	    $return[ $tAdat['targyId'] ]['db']++;
	    if ($return[ $tAdat['targyId'] ]['max'] < $letszam) $return[ $tAdat['targyId'] ]['max'] = $letszam;
	    if (!isset($return[ $tAdat['targyId'] ]['min']) || $return[ $tAdat['targyId'] ]['min'] > $letszam) $return[ $tAdat['targyId'] ]['min'] = $letszam;
	}
	return $return;
    }


function getTantargyfelosztasStat() {

    // __TANEV és __INTEZMENY függés!!

    global $ADAT;

    // ---- ---- ---- ----
    $ADAT['evfolyamJelek'] = getEvfolyamJelek(array('result'=>'idonly'));
    $ADAT['tankorTipusok'] = getTankorTipusok();
    foreach ($ADAT['tankorTipusok'] as $tankorTipusId => $tAdat) $ADAT['tankorTipusIds'][$tAdat['oratervi']][] = $tankorTipusId;
    // Ha módosul, akkor javítani kell a fenntarto/naplo/tantargyfelosztas alatt is!!
    $ADAT['finanszírozott pedagógus létszám'] = array(
        'általános iskola'                              => 11.8, // 11.8 tanuló / 1 pedagógus
        'gimnázium'                                     => 12.5, // 12.5 tanuló / 1 pedagógus
        'szakiskola, Híd programok'                     => 12,   // ...
        'szakközépiskola, nem szakkképző évfolyam'      => 12.4,
        'szakközépiskola, szakkképző évfolyam'          => 13.7
    ); // -- TODO szakgimnázium???



    $IA['intezmenyAdat'] = getIntezmenyByRovidnev(__INTEZMENY);
    $IA['osztalyAdat'] = getOsztalyok(__TANEV, array('result' => 'assoc', 'minden'=>false, 'telephelyId' => null));
    foreach ($IA['osztalyAdat'] as $idx => $oAdat) $IA['osztalyIds'][] = $oAdat['osztalyId'];
    $IA['targyAdat'] = getTargyAdatByIds();

    $IA['diakLetszam']['statusz'] = getDiakLetszamByStatusz();
    $IA['diakLetszam']['osztaly'] = getDiakLetszamByOsztalyId($IA['osztalyIds']);
    foreach ($IA['diakLetszam']['osztaly'] as $osztalyId => $letszam)
        if (is_numeric($osztalyId)) $IA['diakLetszam']['evfolyamJel'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ]['összes'] += intval($letszam);
    foreach ($IA['diakLetszam']['osztaly']['fiú'] as $osztalyId => $letszam)
        if (is_numeric($osztalyId)) $IA['diakLetszam']['evfolyamJel'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ]['fiú'] += intval($letszam);
    foreach ($IA['diakLetszam']['osztaly']['lány'] as $osztalyId => $letszam)
        if (is_numeric($osztalyId)) $IA['diakLetszam']['evfolyamJel'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ]['lány'] += intval($letszam);
    $IA['tanarLetszam']['besorolas'] = getTanarLetszamByBesorolas();
    $IA['tanarLetszam']['statusz'] = getTanarLetszamByStatusz();
    $IA['oraszamok'] = getTankorOraszamOsszesites($ADAT['tankorTipusIds']);
    $IA['targyOraszamok'] = getTargyOraszamok($ADAT['tankorTipusIds']);
    $IA['osztalyOraszamok'] = getOsztalyOraszamok($IA['osztalyIds'], $ADAT['tankorTipusIds']);
    foreach ($IA['osztalyOraszamok']['összes'] as $osztalyId => $oraszam) {
        $IA['evfolyamOraszamok']['összes'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ] += intval($oraszam);
        $IA['evfolyamOraszamok']['óratervi'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ] += intval($IA['osztalyOraszamok']['óratervi'][$osztalyId]);
        $IA['evfolyamOraszamok']['tanórán kívüli'][ $IA['osztalyAdat'][$osztalyId]['evfolyamJel'] ] += intval($IA['osztalyOraszamok']['tanórán kívüli'][$osztalyId]);
    }
    $egyhaziE = ($IA['intezmenyAdat']['fenntarto']=='egyházi');
    foreach ($IA['osztalyAdat'] as $osztalyId => $osztalyAdat) {
        $IA['osztalyIdokeret'][$osztalyId] = getOsztalyHetiIdokeret($osztalyId, $osztalyAdat, array('egyhaziE'=>$egyhaziE));
        $IA['osztalyIdokeret']['összesen']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
        $IA['osztalyIdokeret']['összesen']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
        $IA['osztalyIdokeret']['összesen']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
        $IA['osztalyIdokeret']['összesen']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
        $IA['osztalyIdokeret']['összesen']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
        $IA['osztalyIdokeret']['összesen']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        if (in_array($osztalyAdat['osztalyJellegId'], array(21,22)) && in_array($osztalyAdat['evfolyamJel'], array('1','2','3','4'))) {
            $IA['osztalyIdokeret']['alsó']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
            $IA['osztalyIdokeret']['alsó']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
            $IA['osztalyIdokeret']['alsó']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
            $IA['osztalyIdokeret']['alsó']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
            $IA['osztalyIdokeret']['alsó']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
            $IA['osztalyIdokeret']['alsó']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        }
        if (in_array($osztalyAdat['osztalyJellegId'], array(21,23)) && in_array($osztalyAdat['evfolyamJel'], array('5','6','7','8'))) {
            $IA['osztalyIdokeret']['felső']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
            $IA['osztalyIdokeret']['felső']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
            $IA['osztalyIdokeret']['felső']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
            $IA['osztalyIdokeret']['felső']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
            $IA['osztalyIdokeret']['felső']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
            $IA['osztalyIdokeret']['felső']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        }
        if (in_array($osztalyAdat['osztalyJellegId'], array(21,22,23)) && in_array($osztalyAdat['evfolyamJel'], array('1','2','3','4','5','6','7','8'))) {
            $IA['osztalyIdokeret']['általános']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
            $IA['osztalyIdokeret']['általános']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
            $IA['osztalyIdokeret']['általános']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
            $IA['osztalyIdokeret']['általános']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
            $IA['osztalyIdokeret']['általános']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
            $IA['osztalyIdokeret']['általános']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        }
        if (in_array($osztalyAdat['osztalyJellegId'], array(51,52,53,61,62,63)) && in_array($osztalyAdat['evfolyamJel'], array('1','2','3','4','5','6','7','8'))) {
            $IA['osztalyIdokeret']['gimnázium18']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
            $IA['osztalyIdokeret']['gimnázium18']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
            $IA['osztalyIdokeret']['gimnázium18']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
            $IA['osztalyIdokeret']['gimnázium18']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
            $IA['osztalyIdokeret']['gimnázium18']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
            $IA['osztalyIdokeret']['gimnázium18']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        }
        if (in_array($osztalyAdat['osztalyJellegId'], array(31,32,33,34,35,36,51,52,53,61,62,63)) && in_array($osztalyAdat['evfolyamJel'], array('9','10','11','12'))) {
            $IA['osztalyIdokeret']['gimnázium92']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
            $IA['osztalyIdokeret']['gimnázium92']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
            $IA['osztalyIdokeret']['gimnázium92']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
            $IA['osztalyIdokeret']['gimnázium92']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
            $IA['osztalyIdokeret']['gimnázium92']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
            $IA['osztalyIdokeret']['gimnázium92']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        }
        if (in_array($osztalyAdat['osztalyJellegId'], array(31,32,33,34,35,36,51,52,53,61,62,63))) {
            $IA['osztalyIdokeret']['gimnázium']['összes'] += $IA['osztalyIdokeret'][$osztalyId]['összes'];
            $IA['osztalyIdokeret']['gimnázium']['engedélyezett'] += $IA['osztalyIdokeret'][$osztalyId]['engedélyezett'];
            $IA['osztalyIdokeret']['gimnázium']['tehetséggondozás-felzárkóztatás'] += $IA['osztalyIdokeret'][$osztalyId]['tehetséggondozás-felzárkóztatás'];
            $IA['osztalyIdokeret']['gimnázium']['egyházi'] += $IA['osztalyIdokeret'][$osztalyId]['egyházi'];
            $IA['osztalyIdokeret']['gimnázium']['gimnázium'] += $IA['osztalyIdokeret'][$osztalyId]['gimnázium'];
            $IA['osztalyIdokeret']['gimnázium']['nemzetiségi'] += $IA['osztalyIdokeret'][$osztalyId]['nemzetiségi'];
        }
        // Finanszírozott pedagógus létszámhoz diáklészámok osztály-típusonként
        if (in_array($osztalyAdat['osztalyJellegId'], array(21,22,23))) { // általános iskola
            $IA['diakLetszam']['általános iskola'] += $IA['diakLetszam']['osztaly'][$osztalyId];
        } else if (in_array($osztalyAdat['osztalyJellegId'], array(31,32,33,34,35,36,51,52,53,61,62,63,65))) { // gimnázium
            $IA['diakLetszam']['gimnázium'] += $IA['diakLetszam']['osztaly'][$osztalyId];
        } else if (in_array($osztalyAdat['osztalyJellegId'], array(82,83,84,85,91,92,93))) { // szakiskola, Híd programok
            $IA['diakLetszam']['szakiskola, Híd programok'] += $IA['diakLetszam']['osztaly'][$osztalyId];
        } else if (in_array($osztalyAdat['osztalyJellegId'], array(71,72,73,74,75,76,77,78,79))) { // szakközépiskola, nem szakképző évfolyam
            $IA['diakLetszam']['szakközépiskola, nem szakkképző évfolyam'] += $IA['diakLetszam']['osztaly'][$osztalyId];
        } else if (in_array($osztalyAdat['osztalyJellegId'], array())) { // szakközépiskola, szakképző évfolyam
            $IA['diakLetszam']['szakközépiskola, szakképző évfolyam'] += $IA['diakLetszam']['osztaly'][$osztalyId];
        }
    } // osztályok
    $IA['tankorLetszamStat'] = getTankorLetszamStat();
    // ---- ---- ---- ----

    return $IA;

}


?>