<?php 

    // SUB tankor.php

    function getTankorokByBlokkId($blokkIds, $tanev = __TANEV) {

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	if (!is_array($blokkIds)) $blokkIds = array($blokkIds);

	$q = "SELECT DISTINCT tankorId FROM `%s`.tankorBlokk WHERE blokkId IN (".implode(',', array_fill(0, count($blokkIds), '%u')).")";
	array_unshift($blokkIds, $tanevDb);
	return db_query($q, array('fv' => 'getTankorBlokkByTankorId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $blokkIds));

    }

    function getTankorBlokkByTankorId($tankorIds, $tanev = __TANEV, $SET= array('blokkNevekkel'=>FALSE)) {

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	if (!is_array($tankorIds)) $tankorIds = array($tankorIds);

	if ($SET['blokkNevekkel']===true) {
	    $q = "SELECT blokkId,blokkNev FROM `%s`.tankorBlokk LEFT JOIN `%s`.blokk USING (blokkId) WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).") GROUP BY blokkId";
	    array_unshift($tankorIds, $tanevDb, $tanevDb);
	    return db_query($q, array('fv' => 'getTankorBlokkByTankorId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $tankorIds));
	} else {
	    $q = "SELECT DISTINCT blokkId FROM `%s`.tankorBlokk WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
	    array_unshift($tankorIds, $tanevDb);
	    return db_query($q, array('fv' => 'getTankorBlokkByTankorId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $tankorIds));
	}
    }


     function getTankorBlokkok($tanev = __TANEV) {

	$RET = array();

	$v = array(tanevDbNev(__INTEZMENY, $tanev)); /* Lehet hogy még nincs megnyitva a tanév, nincs is ilyen adatbázis... */
	/* Létezik az adatbázis? */
	$q = "SELECT count(*) AS db FROM Information_schema.tables WHERE table_schema = '".$v[0]."'";
	$r = db_query($q, array('modul'=>'naplo', 'result'=>'value'), $lr);
	if ($r==0) return false;
	/* --- */

	$TA = getTanevAdat($tanev);
	// if ($TA['statusz']!='aktív') return false; // Ez miért kellene? Lekérdezni lehessen lezárt tanév blokkjait is...

	$lr = db_connect('naplo_intezmeny');

	$q = "SELECT * FROM `%s`.blokk ORDER BY blokkNev";
	$r = db_query($q, array('fv' => 'getTankorBlokkok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v), $lr);

	$BID2ORASZAM = $BID2NEV = array();
	for ($i = 0; $i < count($r); $i++) {
	    $_bId = $r[$i]['blokkId'];
	    $BID2NEV[$_bId] = $r[$i]['blokkNev'];
	    $BID2ORASZAM[$_bId] = $r[$i]['exportOraszam'];
	}
	$q = "SELECT * FROM `%s`.tankorBlokk";
	$r = db_query($q, array('fv' => 'getTankorBlokkok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v ), $lr);
	if ($r !== false)  {
	    for ($i = 0; $i < count($r); $i++) {
		$RET['idk'][$r[$i]['blokkId']][] = $r[$i]['tankorId'];
	    }
	    $RET['blokkNevek'] = $BID2NEV;
	    $RET['exportOraszam'] = $BID2ORASZAM;
	}
	if (is_array($RET['blokkNevek']))
	foreach ($RET['blokkNevek'] as $bId => $bNev) {
	    if (is_array($RET['idk'][$bId])) { // Ha netán olyan blokk, aminek nincs tanköre...
		$q = "SELECT MIN(oraszam) FROM tankorSzemeszter WHERE tanev = %u AND tankorId IN (".implode(',', array_fill(0, count($RET['idk'][$bId]), '%u')).")";
		$v = mayor_array_join(array($tanev), $RET['idk'][$bId]);
		$RET['maxOraszam'][$bId] = db_query($q, array(
		    'fv' => 'getTankorBlokkok', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v
		), $lr);
	    }
	}

	db_close($lr);
	return $RET;

    }

?>
