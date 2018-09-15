<?php

    /*
        A loadFile() függvény a paraméterül kapott $fileName nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
        és berakja a $OrarendiOra globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

        ------------------------------------------

        Az alábbi példa az eKréta rendszer imoprt formátumából (xlsx) készített tabulátorokkal tagolt, UTF-8 kódolású
	állomány adatait dolgozza fel.

	Ebben egy sor egy óra adatait tartalmazza - akár az orarendiOra adatbázis egy rekordja.
	Feltételezzük, hogy a tanárnevek _pontosan_ megegyeznek a naplóbeli nevekkel, továbbá a termek rövid neve
	az azonosító számuk.
	A szkript kezeli a blokkokat, ezeket szétbontja különrekordokra

	0. Óra érvényességének kezdete	- yyyy.mm.dd formátumú dátum		- eldobjuk
	1. Őra érvényességének vége	- yyyy.mm.dd formátumú dátum		- eldobjuk
	2. Hetirend			- Minden héten/A hét/B hét		- 1. hét esetén az első kettő, 2. esetén az 1. és a harmadik érvényes - többit eldobjuk
	3. Nap				- a nap magyar neve			- konvertáljuk hétfő --> 1, ..., vasárnap --> 7 alakra
	4. Óra (az adott napon belül)	- pozitív egész szám			- --> ora
	5. Osztály			- az osztály jele vagy üres		- --> osztalyJel (első része)
	6. Csoport			- a csoport jele a Krétában		- --> osztalyJel (második része)
	7. Tantárgy			- a tárgy hivatalos neve		- --> targyJel
	8. Tanár			- a tanár neve				- konvertáljuk tanarId-vé
	9. Helyiség			- terem száma/neve			- konvertáljuk teremId-vé
	0. -- üres --			- üres oszlop				- eldobjuk
	1. Szabad			- üres oszlop				- eldobjuk
    */

    require_once('include/modules/naplo/share/tanar.php');

    function terem2teremId($terem) {
	if ($terem == 'könyvtár') return 12;
	else if ($terem == 'fonotéka') return 13;
	else if ($terem == 'studió') return 14;
	else if ($terem == 'tornaterem 1.') return 26;
	else if ($terem == 'tornaterem 2.') return 27;
	else if ($terem == 'konditerem') return 29;
	else if ($terem == 'szuterén') return 401;
	else if ($terem == 'klub') return 402;
	else if ($terem == 'díszterem') return 601;
	else return intval($terem);
    }

    function loadFile($ADAT) {

        global $OrarendiOra;

        $OrarendiOra = array();

	$Napok = array('hétfő'=>1, 'kedd'=>2, 'szerda'=>3, 'csütörtök'=>4, 'péntek'=>5, 'szombat'=>6, 'vasárnap'=>7);
        $Tanarok = getTanarok(array('tanev' => __TANEV, 'result' => 'assoc'));
        foreach ($Tanarok as $tanarId => $tanarAdat) $Tanar[ $tanarAdat['tanarNev'] ] = $tanarId;
	$Tanar['Pintér László (1961. 03. 14.)'] = $Tanar['Pintér László'];
	$Tanar['Pintér László (1975. 02. 25.)'] = $Tanar['Pintér László Sp'];
	$Tanar['Balkayné Kalló Ágnes Zsófia'] = $Tanar['Balkayné Kalló Ágnes'];
	$Tanar['Füredi-Szabó Tünde Erzsébet'] = $Tanar['Füredi-Szabó Tünde'];
	$Tanar['Jäger Csaba Miklós'] = $Tanar['Jäger Csaba'];
	$Tanar['dr Bándi Irisz Réka'] = $Tanar['Dr. Bándi Írisz Réka'];
	$Tanar['Nagyné Hodula Andrea Éva'] = $Tanar['Nagyné Hodula Andrea'];
	$Tanar['Gondi Ferenc Lászlóné'] = $Tanar['Gondi Ferencné'];
	$Tanar['dr Jánossyné dr Solt Anna Mária'] = $Tanar['Dr Jánossyné Dr. Solt Anna'];
	$Tanar['Mészárosné Romhányi Margit Mária'] = $Tanar['Mészárosné Romhányi Margit'];
	$Tanar['Jenes Béla Tibor'] = $Tanar['Jenes Béla'];
	$Tanar['Csapody Barbara Mária'] = $Tanar['Csapody Barbara'];
	$Tanar['dr Szabóné Karácsonyi Virág'] = $Tanar['dr. Szabóné Karácsonyi Virág'];
	$Tanar['dr Kas Géza Imre'] = $Tanar['Dr. Kas Géza Imre'];


        $fp = fopen($ADAT['fileName'], 'r');
        if (!$fp) return false;

	// Az első  mezőneveket tartalmaz - eldobjuk
	$sor = fgets($fp, 1024);
	$ok = true; $OrarendiOra = array();
	// Az adatsorok feldolgozása
	$kulcs2index = $kulcsDb = array();
	while ($sor = fgets($fp, 1024)) {

	    $rec = explode('	', chop($sor));

	    $nap = $Napok[$rec[3]];
	    $ora = $rec[4];
	    if (!isset($Tanar[$rec[8]])) {
		    $_SESSION['alert'][] = 'message:wrong_data:Hiányzó azonosító (tanár név='.$rec[8].')';
		    $ok = false;
	    } else {
		$tanarId = $Tanar[$rec[8]];
	    }
	    $osztalyJel = $rec[5].$rec[6];
	    $targyJel = $rec[7];
	    $teremId = terem2teremId($rec[9]); 
	    $tolDt = $ADAT['tolDt'];
	    $igDt = $ADAT['igDt'];

	    if ($rec[2] == 'A hét') {
		$het = 1;
            	$OrarendiOra[] = array( $het,$nap,$ora,$tanarId,$osztalyJel,$targyJel,$teremId,$tolDt,$igDt );
	    } elseif ($rec[2] == 'B hét') {
		$het = 2;
            	$OrarendiOra[] = array( $het,$nap,$ora,$tanarId,$osztalyJel,$targyJel,$teremId,$tolDt,$igDt );
	    } elseif ($rec[2] == 'Minden héten') {
		$het = 1;
            	$OrarendiOra[] = array( $het,$nap,$ora,$tanarId,$osztalyJel,$targyJel,$teremId,$tolDt,$igDt );
		$het = 2;
            	$OrarendiOra[] = array( $het,$nap,$ora,$tanarId,$osztalyJel,$targyJel,$teremId,$tolDt,$igDt );
	    }


	}

	fclose($fp);
	return $ok;

    }


?>
