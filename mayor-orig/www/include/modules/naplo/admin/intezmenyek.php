<?php

    function updateNaploSession($sessionID,$rovidnev) {
	if (defined('__TANEV')) {
	    $q = "UPDATE session SET intezmeny='%s',tanev=%u WHERE sessionID='%s'";
	    $v = array($rovidnev,__TANEV,$sessionID);
	} else {
	    $q = "UPDATE session SET intezmeny='%s' WHERE sessionID='%s'";
	    $v = array($rovidnev,$sessionID);
	}
	$r = db_query($q, array('fv' => 'updateNaploSession', 'modul' => 'naplo_base', 'values' => $v));
    }

    function intezmenyBejegyzese($OMKod, $nev, $rovidnev) {

	$lr = db_connect('naplo_base', array('fv' => 'intezmenyBejegyzese'));

	$q = "SELECT COUNT(*) FROM intezmeny WHERE alapertelmezett=1";
	$num = db_query($q, array('fv' => 'intezmenyBejegyzese', 'modul' => 'naplo_base', 'result' => 'value'), $lr);

	if ($num > 0) $alapertelmezett = 0;
	else $alapertelmezett = 1;

	$q = "INSERT INTO intezmeny (OMKod, nev, rovidnev, alapertelmezett)
		VALUES ('%s', '%s', '%s', %u)";
	$v = array($OMKod, $nev, $rovidnev, $alapertelmezett);
	$r = db_query($q, array('fv' => 'intezmenyBejegyzese', 'modul' => 'naplo_base', 'values' => $v), $lr);

	db_close($lr);
    }

    function intezmenyModositas($ADAT) {

	$q = "UPDATE intezmeny SET nev='%s', OMKod='%s', alapertelmezett=%u, fenntarto='%s' WHERE rovidNev='".__INTEZMENY."' ";
	$v = array($ADAT['nev'], $ADAT['OMKod'], $ADAT['alapertelmezett'], $ADAT['fenntarto']);
	return db_query($q, array('fv' => 'intezmenyModositas', 'modul' => 'naplo_base', 'values' => $v));

    }

    function intezmenyTorles($intezmeny) {

	$q = "DELETE FROM intezmeny WHERE rovidNev='%s'";
	return db_query($q, array('fv' => 'intezmenyTorles', 'modul' => 'naplo_base', 'values' => array($intezmeny)));

    }

    function getIntezmeny($intezmeny) {

        $q = "SELECT * FROM `intezmeny` WHERE `rovidNev`='%s'";
	$ret = db_query($q, array('fv' => 'getIntezmeny', 'modul' => 'naplo_base', 'result' => 'record', 'values' => array($intezmeny)));

	$q = "SELECT * FROM `%s`.`telephely`";
	$ret['telephely'] = db_query($q, array('fv' => 'getIntezmeny', 'modul' => 'naplo_base', 'result' => 'indexed', 'values' => array(intezmenyDbNev($intezmeny))));

	return $ret;

    }

    function telephelyModositas($ADAT) {

	$v = array(
	    __INTEZMENYDBNEV,
	// Telephely adatai
	    readVariable($ADAT['telephelyNev'], 'sql', null),
	    readVariable($ADAT['telephelyRovidNev'], 'sql', null),
	    readVariable($ADAT['alapertelmezett'], 'numeric unsigned', 0, array(0,1)),
	    readVariable($ADAT['cimHelyseg'], 'sql', null),
	    readVariable($ADAT['cimIrsz'], 'numeric', 'NULL'),
	    readVariable($ADAT['cimKozteruletNev'], 'sql', null),
	    readVariable($ADAT['cimKozteruletJelleg'], 'sql', null),
	    readVariable($ADAT['cimHazszam'], 'sql', null),
	    readVariable($_POST['telefon'], 'string'),
	    readVariable($_POST['fax'], 'string'),
	    readVariable($_POST['email'], 'string'),
	    readVariable($_POST['honlap'], 'string'),
	    readVariable($_POST['telephelyId'], 'id')
	);

	$q = "UPDATE `%s`.`telephely` 
		    SET `telephelyNev`='%s', `telephelyRovidNev`='%s', `alapertelmezett`=%u,
			`cimHelyseg`='%s', `cimIrsz`=%u, `cimKozteruletNev`='%s',`cimKozteruletJelleg`='%s', `cimHazszam`='%s',
			`telefon`='%s',`fax`='%s',`email`='%s',`honlap`='%s'
		    WHERE `telephelyId`='%s' ";

	return db_query($q, array('fv' => 'telephelyModositas', 'modul' => 'naplo_base', 'values' => $v));

    }

    function ujTelephely($ADAT) {

	$v = array(
	    __INTEZMENYDBNEV,
	// Telephely adatai
	    readVariable($ADAT['telephelyNev'], 'sql', null),
	    readVariable($ADAT['telephelyRovidNev'], 'sql', null),
	    readVariable($ADAT['alapertelmezett'], 'numeric unsigned', 0, array(0,1)),
	    readVariable($ADAT['cimHelyseg'], 'sql', null),
	    readVariable($ADAT['cimIrsz'], 'numeric', 'NULL'),
	    readVariable($ADAT['cimKozteruletNev'], 'sql', null),
	    readVariable($ADAT['cimKozteruletJelleg'], 'sql', null),
	    readVariable($ADAT['cimHazszam'], 'sql', null),
	    readVariable($_POST['telefon'], 'string'),
	    readVariable($_POST['fax'], 'string'),
	    readVariable($_POST['email'], 'string'),
	    readVariable($_POST['honlap'], 'string'),
	);

	$q = "INSERT INTO `%s`.`telephely`
		    (`telephelyNev`,`telephelyRovidNev`,`alapertelmezett`,`cimHelyseg`,`cimIrsz`,`cimKozteruletNev`,`cimKozteruletJelleg`,`cimHazszam`,
		    `telefon`,`fax`,`email`,`honlap`)
		VALUES ('%s', '%s', %u,'%s', %u, '%s','%s', '%s','%s','%s','%s','%s')";

	return db_query($q, array('fv' => 'ujTelephely', 'modul' => 'naplo_base', 'values' => $v));

    }

?>
