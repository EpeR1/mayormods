<?php

    function exportTanarOsztalyOraszam($file, $ADAT) {
	$T = $ADAT['EXPORT'];
        if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $T, 'mayor_tanarOsztalyOraszam');
        elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $T, '');
        elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $T, 'mayor_tanarOsztalyOraszam');
        else return false;

    }


?>