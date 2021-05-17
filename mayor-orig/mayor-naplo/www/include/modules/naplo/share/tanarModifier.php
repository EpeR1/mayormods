<?php

    function updateTanarAdat($tanarId,$SET) {
	if (is_array($SET)) {
	    foreach($SET as $k => $v) {
		$kv[] = "$k='%s'";
	    }
	    $q = "UPDATE tanar SET ".implode(',',$kv)." WHERE tanarId=%u";
	}
	$v = array_merge(array_values($SET),array($tanarId));
	return db_query($q, array('fv' => 'updateTanarAdat', 'modul' => 'naplo_intezmeny', 'result' => 'update', 'values' => $v), $olr);
    }

?>
