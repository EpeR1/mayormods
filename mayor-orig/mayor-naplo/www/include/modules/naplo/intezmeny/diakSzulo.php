<?php

    function szuloAdatModositas($ADAT, $FIELDS) {

	$v = array();
	foreach($ADAT as $attr => $value) {
	    if (array_key_exists(($attr), $FIELDS) && !in_array($attr, array('szuloId'))) {
		if ($value == '') {
		    $T[] = "`%s`=NULL";
		    array_push($v, $attr);
		} else {
		    array_push($v, $attr, $value);
		    $T[] = "`%s`='%s'";
		}
	    }
	}
	$q = "UPDATE szulo SET ".implode(',',$T)." WHERE szuloId=%u";
	array_push($v, $ADAT['szuloId']);

	return db_query($q, array('fv' => 'szuloAdatModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));

    }

    function szuloHozzarendeles($diakId, $tipus, $ujSzuloId) {


	if ($tipus == 'anya' || $tipus == 'apa') {
	    // Nem ellenőrzés
	    if ($tipus == 'anya') $tiltott = 'fiú';
	    else $tiltott = 'lány';
	    $q = "SELECT nem FROM szulo WHERE szuloId=%u";
	    $nem = db_query($q, array('fv' => 'szuloHozzarendeles', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($ujSzuloId)));
	    if ($nem == $tiltott) {
		$_SESSION['alert'][] = 'message:tiltott_nem_'.$tiltott;
		return false;
	    }
	}

	$q = "UPDATE diak SET `%sId` = %u WHERE diakId = %u";
	$v = array($tipus, $ujSzuloId, $diakId);
	$ret = db_query($q, array('fv' => 'szuloHozzarendeles', 'modul' => 'naplo_intezmeny', 'values' => $v));
	if ($ret) return $ujSzuloId;
	else return false;

    }

    function szuloHozzarendelesTorlese($diakId, $tipus) {

	$q = "UPDATE diak SET `%sId` = NULL WHERE diakId = %u";
	$v = array($tipus, $diakId);
	return db_query($q, array('fv' => 'szuloHozzarendelesTorles', 'modul' => 'naplo_intezmeny', 'values' => $v));

    }

    function ujSzulo($ADAT, $FIELDS) {


	$diakId = $ADAT['diakId'];
	$tipus = $ADAT['tipus'];

	// Kötelező paraméterek ellenőrzése
	if ($ADAT['csaladinev'] == ''
	    || $ADAT['utonev'] == '' 
	    || !in_array($tipus, array('anya','apa','gondviselo','nevelo'))
	) {
	    $_SESSION['alert'][] = 'message:wrong_data:ujSzulo:csaladinev - '.$_POST['csaladinev'].', utonev - '.$_POST['utonev'].', tipus - '.$tipus;
	    return false;
	}

	$lr = db_connect('naplo_intezmeny', array('fv' => 'ujSzulo'));

	foreach($ADAT as $attr => $value) {
	    if (array_key_exists(($attr), $FIELDS)) {
		if ($value != '') {
		    $V[] = $value;
		    $A[] = $attr;
		}
	    }
	}

	$q = "INSERT INTO szulo (`".implode('`,`', array_fill(0, count($A), '%s'))."`) VALUES ('".implode("', '", array_fill(0, count($V), '%s'))."')";
	$v = mayor_array_join($A, $V);
	$szuloId = db_query($q, array('fv' => 'ujSzulo', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
	if ($szuloId) {
	    $q = "UPDATE diak SET `%sId` = %u WHERE diakId = %u";
	    $v = array($tipus, $szuloId, $diakId);
	    $r = db_query($q, array('fv' => 'ujSzulo', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	    db_close($lr);
	    if ($r) return $szuloId;
	    else return false;
	} else {
	    db_close($lr);
	    return false;
	}

    }

?>
