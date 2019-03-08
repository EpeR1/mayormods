<?php

    function getKerdoiv($cimzett = array()) {

        $cimzettFeltetel = array();
	$v = array();
        if (is_array($cimzett)) foreach ($cimzett as $cimzettTipus => $cimzettIds) {
            if (is_array($cimzettIds) && count($cimzettIds) > 0) {
                $cimzettFeltetel[] = "(cimzettTipus='%s' AND cimzettId IN (0,".implode(',', array_fill(0, count($cimzettIds), '%u'))."))";
		$v = mayor_array_join($v, array($cimzettTipus), $cimzettIds);
	    }
        }

        $q = "SELECT DISTINCT kerdoivId,cim,tolDt,igDt FROM kerdoiv LEFT JOIN kerdoivCimzett USING (kerdoivId)
                WHERE tolDt<=NOW() AND NOW()<=igDt";
        if (count($cimzettFeltetel) > 0) $q .= " AND (".implode(" OR ", $cimzettFeltetel).")";
        return db_query($q, array('fv' => 'getKerdoiv','modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
    }

    function getOsszesKerdoiv($tanev = __TANEV) {

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
        $q = "SELECT DISTINCT kerdoivId,cim,tolDt,igDt FROM %s.kerdoiv LEFT JOIN `%s`.kerdoivCimzett USING (kerdoivId)";
        return db_query($q, array('fv' => 'getKerdoiv','modul' => 'naplo', 'result' => 'indexed', 'values' => array($tanevDb,$tanevDb)));

    }

    function getKerdoivAdat($kerdoivId) {

	$v = array($kerdoivId);
        $q = "SELECT * FROM kerdoiv WHERE kerdoivId=%u";
        $ret = db_query($q, array('fv' => 'getKerdoivAdat', 'modul' => 'naplo', 'result' => 'record', 'values' => $v));

        $q = "SELECT * FROM kerdoivKerdes WHERE kerdoivId=%u ORDER BY kerdesId";
        $ret['kerdes'] = db_query($q, array('fv' => 'getKerdoivAdat/kerdes', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));

        for ($i = 0; $i < count($ret['kerdes']); $i++) {
            $q = "SELECT * FROM kerdoivValasz WHERE kerdesId=%u ORDER BY pont, valaszId";
            $ret['kerdes'][$i]['valasz'] = db_query($q, array('fv' => 'getKerdoivAdat/valasz', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($ret['kerdes'][$i]['kerdesId'])));
        }

        $q = "SELECT * FROM kerdoivCimzett WHERE kerdoivId=%u";
        $tmp = db_query($q, array('fv' => 'getKerdoivAdat/cimzett', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'cimzettTipus', 'values' => $v));
        if (is_array($tmp))
            foreach ($tmp as $cimzettTipus => $ctAdat) {
                $ret['cimzettTipusok'][] = $cimzettTipus;
                for ($i = 0; $i < count($ctAdat); $i++)
                    $ret['cimzett'][$cimzettTipus][] = $ctAdat[$i]['cimzettId'];
            }
        /* endif */

        return $ret;

    }

    function getMegvalaszoltKerdes($kerdoivId, $feladoId, $feladoTipus, $cimzettId, $cimzettTipus) {

        $q = "SELECT kerdesId FROM kerdoivMegvalaszoltKerdes
                WHERE feladoId=%u AND feladoTipus='%s' AND cimzettId=%u AND cimzettTipus='%s'
                ORDER BY kerdesId";
	$v = array($feladoId, $feladoTipus, $cimzettId, $cimzettTipus);
        return db_query($q, array('fv' => 'getMegvalaszoltKerdes', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v));

    }

    function getKerdoivStat($kerdoivId, $tanev = __TANEV) {

        global $_TANEV;

	if ($tanev == __TANEV) $TA = $_TANEV;
	else $TA = getTanevAdat($tanev);

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);

        // Kérdőív címe, határidői
        $q = "SELECT * FROM `%s`.kerdoiv WHERE kerdoivId=%u";
        $ret = db_query($q, array('fv' => 'getKerdoivStat', 'modul' => 'naplo', 'result' => 'record', 'values' => array($tanevDb, $kerdoivId)));
        // A kérdőív kérdései
        $q = "SELECT kerdesId,trim(trailing '\c' from kerdes) AS kerdes FROM `%s`.kerdoivKerdes WHERE kerdoivId=%u ORDER BY kerdesId";
        $ret['kerdes'] = db_query($q, array('fv' => 'getKerdoivStat/kerdes', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'kerdesId', 'values' => array($tanevDb, $kerdoivId)));

        // A kérdőív válaszai
        $ret['valaszIds'] = array();
        foreach ($ret['kerdes'] as $kerdesId => $kAdat) {
            $q = "SELECT valaszId,valasz FROM `%s`.kerdoivValasz WHERE kerdesId=%u ORDER BY valaszId";
            $ret['kerdes'][$kerdesId]['valasz'] = db_query($q, array('fv' => 'getKerdoivStat/valasz', 'modul' => 'naplo', 'result' => 'keyvaluepair', 'values' => array($tanevDb, $kerdesId)));
            foreach ($ret['kerdes'][$kerdesId]['valasz'] as $valaszId => $valasz) $ret['valaszIds'][] = $valaszId;
        }

        // A kérdőív címzettjei
        $q = "SELECT cimzettTipus,cimzettId FROM `%s`.kerdoivCimzett WHERE kerdoivId=%u";
        $ret['cimzett']  = db_query($q, array('fv' => 'getKerdoivStat/cimzett', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'cimzettTipus', 'values' => array($tanevDb, $kerdoivId)));
        // A tankör típusú címzettek tanára(i)
        $ret['tanarNev'] = array();
	foreach (array('tankor','tankorSzulo') as $key => $cimzettTipus) {
    	    if (is_array($ret['cimzett'][$cimzettTipus]) && count($ret['cimzett'][$cimzettTipus]) > 0) {
        	for ($i = 0; $i < count($ret['cimzett'][$cimzettTipus]); $i++) {
            	    $tankorId = $ret['cimzett'][$cimzettTipus][$i]['cimzettId'];
            	    $tanarIds = getTankorTanaraiByInterval(
                	$tankorId, array('tanev' => $tanev, 'tolDt' => $ret['kerdes']['tolDt'], 'igDt' => $ret['kerdes']['igDt'], 'result' => 'csakId')
            	    );
            	    for ($j = 0; $j < count($tanarIds); $j++) {
			if (!is_array($ret['tanarTankorei'][ $tanarIds[$j] ]) || !in_array($tankorId, $ret['tanarTankorei'][ $tanarIds[$j] ])) 
			    $ret['tanarTankorei'][ $tanarIds[$j] ][] = $tankorId;
                	if (!isset($ret['tanarNev'][ $tanarIds[$j] ])) $ret['tanarNev'][ $tanarIds[$j] ] = getTanarNevById($tanarIds[$j]);
            	    }
		}
            }
        }

        $q = "SELECT * FROM `%s`.kerdoivValaszSzam WHERE valaszId IN (".implode(',', $ret['valaszIds']).") ORDER BY cimzettTipus,cimzettId,valaszId";
        $tmp = db_query($q, array('fv' => 'getKerdoivStat/szavazat', 'modul' => 'naplo', 'result' => 'indexed', 'values' => array($tanevDb)));
        for ($i = 0; $i < count($tmp); $i++)
            $ret['szavazat'][ $tmp[$i]['cimzettTipus'] ][ $tmp[$i]['cimzettId'] ][ $tmp[$i]['valaszId'] ] = $tmp[$i]['szavazat'];

        $tmp = getTankorok(array("tanev=".$tanev));
        for ($i = 0; $i < count($tmp); $i++) {
            $ret['tankorAdat'][ $tmp[$i]['tankorId'] ] = $tmp[$i];
            $ret['tankorAdat'][ $tmp[$i]['tankorId'] ]['letszam'] = getTankorLetszam($tmp[$i]['tankorId'], array('refDt' => $TA['zarasDt']));
        }
        return $ret;

    }



?>
