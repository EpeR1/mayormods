<?php

if (_RIGHTS_OK !== true) die();

global $table, $dbtable, $fileName, $ADATOK, $attrList, $MEZO_LISTA;

if (isset($table)) {
	if ($fileName == '') {
		putFileSelectForm($dbtable);
	} elseif ($MEZO_LISTA == '') {
	    if (count($ADATOK) > 0)
		putFieldSelectForm($fileName, $ADATOK, $attrList, $dbtable);
	    else
		echo 'NINCS ADAT!';
	} else {
	    echo 'Adatfeldolgozás kész.';
	}
} else { echo 'nincs table'; }
?>
