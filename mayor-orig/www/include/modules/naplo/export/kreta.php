<?php

    function exportKretaTanarAdat($file, $ADAT) {
	$T = $ADAT['EXPORT'];
        if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $T, 'kreta_mayor_tanarAdat');
        elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $T, '');
        elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $T, 'kreta_mayor_tanarAdat');
        else return false;

    }

    function exportTankorTanar($file, $ADAT) {

	$T = $ADAT['EXPORT'];

        if ($ADAT['formatum'] == 'xml') return generateXLS("$file.${ADAT['formatum']}", $T, 'kreta_ETTF_simple');
        elseif ($ADAT['formatum'] == 'csv') return generateCSV("$file.${ADAT['formatum']}", $T, '');
        elseif ($ADAT['formatum'] == 'ods') return generateODS("$file.${ADAT['formatum']}", $T, 'kreta_ETTF_simple');
        else return false;

    }

?>