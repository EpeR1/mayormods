<?php

    function getSzempontRendszer($ADAT) {

	$targyTipusok = getEnumField('naplo_intezmeny', 'szempontRendszer', 'targyTipus');
	$ADAT['targyTipus'] = readVariable($ADAT['targyTipus'], 'enum', null, $targyTipusok);
	if ($ADAT['feltetel'] == 'id') {
	    $q = "SELECT * FROM szempontRendszer WHERE szrId=%u";
	    $v = array($ADAT['szrId']);
	} elseif ($ADAT['feltetel'] == 'eros') {
	    $q = "SELECT * FROM szempontRendszer WHERE tanev=%u AND szemeszter=%u AND evfolyamJel='%s'";
	    $v = array($ADAT['szemeszter']['tanev'], $ADAT['szemeszter']['szemeszter'], $ADAT['evfolyamJel']);
	    $q .= (isset($ADAT['targyId'])) ? " AND targyId=".intval($ADAT['targyId']) : " AND targyId IS NULL";
	    $q .= (isset($ADAT['targyTipus'])) ? " AND targyTipus='".$ADAT['targyTipus']."'" : " AND targyTipus IS NULL";
	    $q .= (isset($ADAT['kepzesId'])) ? " AND kepzesId=".intval($ADAT['kepzesId']) : " AND kepzesId IS NULL";
	} else {
	/*
	    Évfolyam kötelező, többi lehet üres/null
	    A fő szempont a targyId, majd a targyTipus, végül a kepzesId
	    Csőkkenő a rendezés, hogy a NULL értékek a végére kerüljenek.
	    Az adott szemeszter elötti utolsó (vagy vele egyenlő)
	*/
	    $q = "SELECT * FROM szempontRendszer
		WHERE tanev <= %u AND szemeszter <= %u AND evfolyamJel = '%s'
		AND (targyId=%u OR targyId IS NULL)
		AND (targyTipus='%s' OR targyTipus IS NULL)
		AND (kepzesId=%u OR kepzesId IS NULL)
		ORDER BY targyId DESC, targyTipus DESC, kepzesId DESC, tanev DESC, szemeszter DESC
		LIMIT 1";
	    $v = array($ADAT['szemeszter']['tanev'], $ADAT['szemeszter']['szemeszter'], $ADAT['evfolyamJel'], $ADAT['targyId'], $ADAT['targyTipus'], $ADAT['kepzesId']);
	}
	$ret = db_query($q, array('fv' => 'getSzempontRendszer', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v));
	if (!is_array($ret) || count($ret) == 0) return false;

	$szrId = $ret['szrId'];
	// szempontok lekérdezése
	$q = "SELECT * FROM szrSzempont WHERE szrId=%u ORDER BY szempontId";
	$ret['szempont'] = db_query($q, array(
	    'fv' => 'getSzempontRendszer/szempont', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'szempontId', 'values' => array($szrId)
	));
	// minősítések lekérdezése
	$ret['minosites'] = array();
	if (is_array($ret['szempont']) && count($ret['szempont']) > 0) {
	    $ret['szempontIds'] = array_keys($ret['szempont']);
	    $v = array_keys($ret['szempont']);
	    $q = "SELECT * FROM szrMinosites WHERE szempontId IN (".implode(',', array_fill(0, count($v), '%u')).") ORDER BY szempontId,minositesId";
	    $ret['minosites'] = db_query($q, array(
		'fv' => 'getSzempontRendszer/minősítés', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'szempontId', 'values' => $v
	    ));
	    if (is_array($ret['minosites'])) {
		$ret['minositesIds'] = array();
		foreach ($ret['minosites'] as $szId => $szM)
		    for ($i = 0; $i < count($szM); $i++) $ret['minositesIds'][] = $szM[$i]['minositesId'];
	    }
	}
	return $ret;
    }

    function getDiakSzovegesTargyZaroErtekeles($diakId, $szrId, $targyId, $tanev, $szemeszter) {
/*
	$q = "SELECT MAX(dt) FROM szovegesErtekeles WHERE szrId=%u AND diakId=%u AND targyId=%u AND dt<='%s'";
	$v = array($szrId, $diakId, $targyId, $igDt);
	$dt = db_query($q, array('fv' => 'getDiakUtolsoSzovegesTargyErtekeles/maxDt', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
	if (!isset($dt)) return false;
	$ret['dt'] = $dt;
*/
	// szeId lekérdezése
	$q = "SELECT * FROM szovegesErtekeles WHERE szrId=%u AND diakId=%u AND targyId=%u AND tanev=%u AND szemeszter=%u";
	$v = array($szrId, $diakId, $targyId, $tanev, $szemeszter);
	$ret = db_query($q, array(
	    'fv' => 'getDiakSzovegesTargyZaroErtekeles/szeId', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v
	));
	$szeId = $ret['szeId'];
	// minősítések lekérdezése
	$q = "SELECT minositesId FROM szeMinosites WHERE szeId=%u";
	$v = array($szeId);
	$ret['minosites'] = db_query($q, array(
	    'fv' => 'getDiakSzovegesTargyZaroErtekeles/minosites', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v
	));
	// Egyedi minősítések lekérdezése
	$q = "SELECT szempontId, egyediMinosites FROM szeEgyediMinosites WHERE szeId=%u";
	$ret['egyediMinosites'] = db_query($q, array(
	    'fv' => 'getDiakSzovegesTargyZaroErtekeles', 'modul' => 'naplo_intezmeny', 
	    'result' => 'assoc', 'keyfield' => 'szempontId', 'values' => $v
	));

	return $ret;

    }

    function getDiakUtolsoSzovegesTargyErtekeles($diakId, $szrId, $targyId, $igDt) {

	$q = "SELECT MAX(dt) FROM szovegesErtekeles WHERE szrId=%u AND diakId=%u AND targyId=%u AND dt<='%s'";
	$v = array($szrId, $diakId, $targyId, $igDt);
	$dt = db_query($q, array('fv' => 'getDiakUtolsoSzovegesTargyErtekeles/maxDt', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
	if (!isset($dt)) return false;
	$ret['dt'] = $dt;

	// szeId lekérdezése
	$q = "SELECT * FROM szovegesErtekeles WHERE szrId=%u AND diakId=%u AND targyId=%u AND dt='%s'";
	$v = array($szrId, $diakId, $targyId, $dt);
	$ret = db_query($q, array(
	    'fv' => 'getDiakUtolsoSzovegesTargyErtekeles/szeId', 'modul' => 'naplo', 'result' => 'record', 'values' => $v
	));
	$szeId = $ret['szeId'];
	// minősítések lekérdezése
	$q = "SELECT minositesId FROM szeMinosites WHERE szeId=%u";
	$v = array($szeId);
	$ret['minosites'] = db_query($q, array(
	    'fv' => 'getDiakUtolsoSzovegesTargyErtekeles/minosites', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v
	));
	// Egyedi minősítések lekérdezése
	$q = "SELECT szempontId, egyediMinosites FROM szeEgyediMinosites WHERE szeId=%u";
	$ret['egyediMinosites'] = db_query($q, array(
	    'fv' => 'getDiakUtolsoSzovegesTargyErtekeles/egyediMinosites', 'modul' => 'naplo', 
	    'result' => 'assoc', 'keyfield' => 'szempontId', 'values' => $v
	));

	return $ret;
    }

    function getDiakOsszesSzovegesErtekeles($ADAT) {

        $q = "SELECT * FROM szovegesErtekeles WHERE diakId=%u AND dt<='%s' ORDER BY dt";
	$v = array($ADAT['diakId'], $ADAT['dt']);
        $ret = db_query($q, array('fv' => 'getDiakOsszesSzovegesErtekeles', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'targyId', 'values' => $v));

        foreach ($ret as $targyId => $tAdat) {
            $tAdat['feltetel'] = 'id';
            $ret[$targyId]['szempontRendszer'] = getSzempontRendszer($tAdat);
            $ret[$targyId]['szovegesErtekeles'] = getDiakUtolsoSzovegesTargyErtekeles($ADAT['diakId'], $tAdat['szrId'], $targyId, $ADAT['dt']);
            $ret[$targyId]['diakTargyak'] = $ADAT['diakTargyak'];
        }

        return $ret;
    }

    function getDiakOsszesSzovegesZaroErtekeles($ADAT) {

        $q = "SELECT * FROM szovegesErtekeles WHERE diakId=%u AND tanev=%u AND szemeszter=%u ORDER BY dt";
	$v = array($ADAT['diakId'], $ADAT['tanev'], $ADAT['szemeszter']);
        $ret = db_query($q, array('fv' => 'getDiakOsszesSzovegesZaroErtekeles', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'targyId', 'values' => $v));

        foreach ($ret as $targyId => $tAdat) {
            $tAdat['feltetel'] = 'id';
            $ret[$targyId]['szempontRendszer'] = getSzempontRendszer($tAdat);
            $ret[$targyId]['szovegesErtekeles'] = getDiakSzovegesTargyZaroErtekeles($ADAT['diakId'], $tAdat['szrId'], $targyId, $ADAT['tanev'], $ADAT['szemeszter']);
            $ret[$targyId]['diakTargyak'] = $ADAT['diakTargyak'];
        }

        return $ret;
    }

    function getOsztalySzovegesErtekeles($ADAT) {
	// Ha van $ADAT['szemeszterId'] akkor záró értékelést ad vissza, különben évközit
	$A = array(
	    'dt' => $ADAT['dt'], 'diakTargyak' => $ADAT['diakTargyak'], 
	    'tanev' => $ADAT['szemeszter']['tanev'], 'szemeszter' => $ADAT['szemeszter']['szemeszter']
	);
	$ret = array();
	if (isset($ADAT['szemeszterId'])) foreach ($ADAT['diakIds'] as $index => $diakId) {
	    $A['diakId'] = $diakId;
	    $ret[$diakId] = getDiakOsszesSzovegesZaroErtekeles($A);
	} else foreach ($ADAT['diakIds'] as $index => $diakId) {
	    $A['diakId'] = $diakId;
	    $ret[$diakId] = getDiakOsszesSzovegesErtekeles($A);

	}

	return $ret;

    }

    function getEvfolyamJelSzempontRendszerek($ADAT) {
	// Legalább tanev, szemeszter, evfolyamJel kell megadva legyen, lehet még targy, targyTipus, kepzesId
	// Visszaadja az összes épp aktuális, a feltételeknek megfelelő szempontrendszert
	$q = "SELECT szr1.*, targyNev, kepzesNev, kepzes.tanev as kepzesTanev 
		FROM szempontRendszer AS szr1 LEFT JOIN targy USING (targyId) LEFT JOIN kepzes USING (kepzesId) 
		WHERE evfolyamJel='%s' AND CONCAT(szr1.tanev,'/',szr1.szemeszter) = (
		    SELECT CONCAT(szr2.tanev,'/',szr2.szemeszter) FROM szempontRendszer AS szr2
		    WHERE szr2.evfolyamJel='%s' 
		    AND (szr2.targyId=szr1.targyId OR (szr1.targyId IS NULL AND szr2.targyId IS NULL)) 
		    AND (szr2.targyTipus=szr1.targyTipus OR (szr1.targyTipus IS NULL AND szr2.targyTipus IS NULL))
		    AND (szr2.kepzesId=szr1.kepzesId OR (szr1.kepzesId IS NULL AND szr2.kepzesId IS NULL))
		    AND ((szr2.tanev=%u AND szr2.szemeszter<=%u) OR szr2.tanev<%u)
		    ORDER BY szr2.tanev DESC, szr2.szemeszter DESC LIMIT 1
		)";
	$v = array($ADAT['evfolyamJel'], $ADAT['evfolyamJel'], $ADAT['szemeszter']['tanev'], $ADAT['szemeszter']['szemeszter'], $ADAT['szemeszter']['tanev'] );
	if (isset($ADAT['targyId'])) { 
	    $q .= " AND targyId=".intval($ADAT['targyId']);
	    $v[] = $ADAT['targyId'];
	}
	if (isset($ADAT['targyTipus'])) {
	    $q .= " AND targyTipus='".$ADAT['targyTipus']."'";
	    $v[] = $ADAT['targyTipus'];
	}
	if (isset($ADAT['kepzesId'])) {
	    $q .=  " AND kepzesId=".intval($ADAT['kepzesId']);
	    $v[] = $ADAT['kepzesId'];
	}

	$ret = db_query($q, array('fv' => 'getEvfolyamJelSzempontRendszerek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	return $ret;
    }

?>
