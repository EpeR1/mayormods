<?php

    function tanarAdatModositas($ADAT) {

        $FIELDS = getTableFields('tanar');
	$v = array();
        foreach($ADAT as $attr => $value) {
            if (array_key_exists($attr, $FIELDS) && !in_array($attr, array('action','tanarId'))) {
                if ($value == '') {
		    $T[] = "$attr=NULL";
                } else {
		    $v[] = $value;
            	    $T[] = "$attr='%s'";
		}
            }
        }
        $q = "UPDATE tanar SET ".implode(',',$T)." WHERE tanarId=%u";
	$v[] = $ADAT['tanarId'];
        return db_query($q, array('fv' => 'tanarAdatModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));

    }

    function ujTanar($ADAT) {


        $FIELDS = getTableFields('tanar');
	$A = $V = $v = array();
	// Ellenőrizzük, hogy az oktatási azonosító szerepel-e már....
	if ($ADAT['oId'] != '') {
	    $q = "SELECT COUNT(*) FROM tanar WHERE oId=%u";
	    $db = db_query($q, array('fv' => 'ujTanar/ütközés ellenőrzés','modul' => 'naplo_intezmeny', 'values' => array($ADAT['oId']), 'result' => 'value'));
	    if ($db > 0) {
		$_SESSION['alert'][] = 'message:wrong_data:ujTanar/ütközés ellenőrzés:ütköző oktatási azonosító ('.$ADAT['oId'].')';
		return false;
	    }
	}
        foreach($ADAT as $attr => $value) {
            if (array_key_exists($attr,$FIELDS) && !in_array($attr, array('action','tanarId'))) {
                if ($value == '' && !in_array($attr, array('viseltNevElotag'))) {
		    $V[] = "NULL";
                } else {
		    $V[] = "'%s'";
		    $v[] = $value;
		}
                $A[] = "$attr";
            }
        }
        $q = "INSERT INTO tanar (".implode(',', $A).") VALUES (".implode(',',$V).')';

        return db_query($q, array('fv' => 'ujTanar', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);

    }

?>
