<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG && !__TANAR && !__DIAK) {
	$_SESSION['alert'][] = 'message:insufficient_access';
    } else {

        require_once('include/modules/naplo/share/file.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/szulo.php');

	$Tipusok = array('anya','apa','gondviselo','nevelo');
        $ADAT['statuszTipusok'] = getEnumField('naplo_intezmeny', 'szulo', 'statusz');

	if (__DIAK===true) $ADAT['diakId'] = $diakId = __USERDIAKID;
	else $ADAT['diakId'] = $diakId = readVariable($_POST['diakId'], 'numeric unsigned', readVariable($_GET['diakId'], 'numeric unsigned', null));
	$ADAT['$szuloId'] = $szuloId = readVariable($_POST['szuloId'], 'numeric unsigned', readVariable($_GET['szuloId'], 'numeric unsigned', null));
	$ADAT['tipus'] = $tipus = readVariable($_POST['tipus'], 'enum', readVariable($_GET['tipus'], 'enum', null, $Tipusok), $Tipusok);
	$ADAT['kozteruletJelleg'] = getEnumField('naplo_intezmeny', 'diak', 'lakhelyKozteruletJelleg');

	if (isset($szuloId)) {
	    $ret = getSzulokDiakjai(array($szuloId));
	    $ADAT['szuloDiakjai'] = $ret[$szuloId];
	    $szDiakIds = array();
	    for ($i = 0; $i < count($ADAT['szuloDiakjai']); $i++) $szDiakIds[] = $ADAT['szuloDiakjai'][$i]['diakId'];
	    if (!isset($diakId)) $ADAT['diakId'] = $diakId = $ADAT['szuloDiakjai'][0]['diakId'];
	    elseif (!in_array($diakId, $szDiakIds)) {
		unset($szuloId); unset($ADAT['$szuloId']); unset($_POST['szuloId']); unset($ADAT['szuloDiakjai']);
	    }
	}
	if (isset($diakId)) {
	    $diakAdat = getDiakAdatById($diakId);
	    if (isset($szuloId) && $diakAdat[$tipus.'Id'] != $szuloId) {
		if ($diakAdat['anyaId'] == $szuloId) $tipus='anya';
		elseif ($diakAdat['apaId'] == $szuloId) $tipus='apa';
		elseif ($diakAdat['gondviseloId'] == $szuloId) $tipus='gondviselo';
		elseif ($diakAdat['neveloId'] == $szuloId) $tipus='nevelo';
	    }
	}

	if (!isset($_POST['semmi'])) {
	    if (isset($_POST['anya'])) $tipus = 'anya';
	    elseif (isset($_POST['apa'])) $tipus = 'apa';
	    elseif (isset($_POST['gondviselo'])) $tipus = 'gondviselo';
	    elseif (isset($_POST['nevelo'])) $tipus = 'nevelo';
	}
	$ADAT['$szuloId'] = $szuloId = $_POST['szuloId'] = $diakAdat[$tipus.'Id'];

	define('_MODOSITHAT',(__NAPLOADMIN || __TITKARSAG));
	// ------------- action ------------------ //
	if (_MODOSITHAT && $action=='diakSzuloModositas') {

	    if (
		!isset($_POST['semmi']) &&
		(isset($_POST['anya']) || isset($_POST['apa']) || isset($_POST['gondviselo']) || isset($_POST['nevelo']))
	    ) {
		// echo 'lap váltás';
	    } else {

//		if (!isset($_POST['szuloId']) || $_POST['szuloId'] == '') {
		$FIELDS = getTableFields('szulo');
		foreach ($FIELDS as $attr => $name) {
		    if (!isset($ADAT[$attr])) 
			if ($attr == 'szuletesiEv') $ADAT[$attr] = readVariable($_POST[$attr], 'numeric unsigned', null, array(), '1900<$return && $return<2100');
			else $ADAT[$attr] = readVariable($_POST[$attr], 'sql', null);
		}
		if (!isset($szuloId)) {
		    $ujSzuloId = readVariable($_POST['ujSzuloId'], 'numeric unsigned', null);
		    if (isset($ujSzuloId)) {
			$szuloId = szuloHozzarendeles($diakId, $tipus, $ujSzuloId);
		    } else {
			$szuloId = ujSzulo($ADAT, $FIELDS); // ez rendben van, de ajaxnál???
		    }
		    if ($szuloId) $diakAdat[$tipus.'Id'] = $szuloId;
		} else {
		    if (!isset($_POST['semmi']) && isset($_POST['torles'])) {
			if (szuloHozzarendelesTorlese($diakId, $tipus)) unset($diakAdat[$tipus.'Id']);
		    } else {
			if (__NAPLOADMIN !== true) unset($ADAT['userAccount']);
			szuloAdatModositas($ADAT, $FIELDS);
		    }
		}
	
	    }

	}
	// ------------- action ------------------ //

	$Szulok = getSzulok();

	$TOOL['diakSelect'] = array('tipus'=>'cella', 'post'=>array('szuloId','tipus'));
	$TOOL['szuloSelect'] = array('tipus'=>'cella', 'szulok'=>$Szulok, 'post'=>array(''));
	getToolParameters();

    }

?>
