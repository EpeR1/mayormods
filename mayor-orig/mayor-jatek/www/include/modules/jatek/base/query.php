<?php

/* AZ ÁLTALÁNOS RÉSZ */

    function _my_query($q,$DATA='',$olr='') {
	$DATA['modul'] = $DATA['db'];
	$DATA['result'] = 'indexed';
	return db_query($q, $DATA, $olr);
    }

    function _my_assoc_query($q,$keyfield,$DATA='',$olr='') {
	$DATA['modul'] = $DATA['db'];
	$DATA['result'] = 'assoc';
	$DATA['keyfield'] = $keyfield;
	return db_query($q, $DATA, $olr);
    }

    function _my_multiassoc_query($q,$keyfield,$DATA='',$olr='') {
	$DATA['modul'] = $DATA['db'];
	$DATA['result'] = 'multiassoc';
	$DATA['keyfield'] = $keyfield;
	return db_query($q, $DATA, $olr);
    }

    function _my_id_query($q,$DATA,$olr='') {
	$DATA['modul'] = $DATA['db'];
	$DATA['result'] = 'idonly';
	return db_query($q, $DATA, $olr);
    }
    function _my_value_query($q,$DATA,$olr='') {
	$DATA['modul'] = $DATA['db'];
	$DATA['result'] = 'value';
	return db_query($q, $DATA, $olr);
    }

?>
