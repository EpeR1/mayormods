<?php

    /*
	A loadFile() függvény a paraméterül kapott $ADAT['fileName'] nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
	és berakja a $OrarendiOra globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

	---------------------------------------------

	Az alábbi példa olyan tabulátorokkal tagolt állományt dolgoz fel, melyben egy sorban a következő adatok szerepelnek:
	    nap,ora,tanarId,osztalyJel,targyJel,teremId

    */

    function loadFile($ADAT) {

	global $OrarendiOra;
	$OrarendiOra = array();

	$fp = fopen($ADAT['fileName'], 'r');
	if (!$fp) return false;

	while ($sor = fgets($fp, 1024)) {

	    $OrarendiOra[] = explode('	',$ADAT['orarendiHet'].'	'.chop($sor).'	'.$ADAT['tolDt'].'	'.$ADAT['igDt']);

	}

	fclose($fp);
	return true;

    }

?>
