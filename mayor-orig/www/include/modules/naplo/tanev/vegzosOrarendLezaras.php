<?php

    /*
	A megadott osztályokhoz - és csak azokhoz - rendelt tankörök listája
    */
    //function getTankorByOsztalyIds($osztalyIds, $tanev = __TANEV) { //MOVED to SHARE libs

    function vegzosOrarendLezaras($ADAT) {

	// A lezárási dátum utáni bejegyzések törlése
	$q = "DELETE FROM orarendiOra WHERE tolDt >= '%s' AND (tanarId,osztalyJel,targyJel) IN (
		    SELECT tanarId,osztalyJel,targyJel FROM orarendiOraTankor WHERE tankorId IN (".implode(',', array_fill(0, count($ADAT['vegzosTankor']), '%u')).")
		)";
	$v = mayor_array_join(array($ADAT['dt']), $ADAT['vegzosTankor']);
	db_query($q, array('fv' => 'vegzosOrarendLezarads', 'modul' => 'naplo', 'values' => $v));

	// A lezárás dátuma után végződő bejegyzáések igDt-inek beállítása
	$q = "UPDATE orarendiOra LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) SET igDt=('%s' - INTERVAL 1 DAY)
		WHERE tankorId IN (".implode(',', array_fill(0, count($ADAT['vegzosTankor']),'%u')).") AND igDt > '%s'";
	$v = mayor_array_join(array($ADAT['dt']), $ADAT['vegzosTankor'], array($ADAT['dt']));
	db_query($q, array('fv' => 'vegzosOrarendLezarads', 'modul' => 'naplo', 'values' => $v));

    }

    function vegzosHaladasiNaploLezaras($ADAT) {

	$q = "DELETE FROM ora WHERE dt >= '%s' AND tankorId IN (".implode(',', array_fill(0, count($ADAT['vegzosTankor']), '%u')).")";
	$v = mayor_array_join(array($ADAT['dt']), $ADAT['vegzosTankor']);
	db_query($q, array('fv' => 'vegzosHaladasiNaploLezarads', 'modul' => 'naplo', 'values' => $v));

    }

?>
