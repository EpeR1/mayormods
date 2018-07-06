<?php

    /*
        A loadFile() függvény a paraméterül kapott $fileName nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
        és berakja a $OrarendiOra globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

        ------------------------------------------

        Az alábbi példa az aSc-ből Ctrl-Shift-E kombinációval exportálható szöveges állomány adatit dolgozza fel.
	Ebben egy sor egy óra adatait tartalmazza - akár az orarendiOra adatbázis egy rekordja.
	Feltételezzük, hogy a tanárnevek _pontosan_ megegyeznek a naplóbeli nevekkel, továbbá a termek rövid neve
	az azonosító számuk.
	A szkript kezeli a blokkokat, ezeket szétbontja különrekordokra

	0. Day Name		- a nap neve		- eldobjuk
	1. Day Number in Cycle	- a nap száma		- "nap"
	2. Period in Day	- az óra száma		- "ora"
	3. Period in Cycle	- ???			- eldobjuk
	4. Form			- osztály		- "osztalyJel"
	5. Form 'short'		- osztály rövid neve	- eldobjuk
	6. Subject		- tárgy neve		- "targyJel" - 1. rész
	7. Subject 'short'	- tárgy rövid neve	- eldobjuk
	8. Classroom		- terem neve		- eldobjuk
	9. Classroom 'short'	- terem rövid neve	- "teremId"
	0. Teacher Name		- tanár neve		- átalakítva "tanarId"-re
	1. Teacher Short	- tanár rövid neve	- eldobjuk
	2. Group		- Csoport neve		- "targyJel" - 2. rész
	3. Cycle		- ???
    */

    require_once('include/modules/naplo/share/tanar.php');

    function loadFile($ADAT) {

        global $OrarendiOra;

        $OrarendiOra = array();

        $Tanarok = getTanarok(array('tanev' => __TANEV, 'result' => 'assoc'));
dump($Tanarok);
        foreach ($Tanarok as $tanarId => $tanarAdat) $Tanar[ $tanarAdat['tanarNev'] ] = $tanarId;


        $fp = fopen($ADAT['fileName'], 'r');
        if (!$fp) return false;

	// Az első sor üres - eldobjuk 
	$sor = fgets($fp, 1024);
	// A második sor mezőneveket tartalmaz - eldobjuk
	$sor = fgets($fp, 1024);
	$ok = true; $OrarendiOra = array();
	// Az adatsorok feldolgozása
	$kulcs2index = $kulcsDb = array();
	while ($sor = fgets($fp, 1024)) {

	    $rec = explode('	', chop($sor));

	    $het = $ADAT['orarendiHet'];
	    $nap = $rec[1];
	    $ora = $rec[2];
	    $tanarNev = explode(',', mb_convert_encoding($rec[10],'UTF-8','ISO-8859-2')); // konvetál és szétvág!
	    $tanarIds = array();
	    foreach ($tanarNev as $tNev) {
		if ($tNev == 'Ernst') $tNev = 'Ernst, Mader';
		if ($tNev == ' Mader') continue;
		if (!isset($Tanar[$tNev])) {
		    $_SESSION['alert'][] = 'message:wrong_data:Hiányzó azonosító (tanár név='.$tNev.')';
		    $ok = false;
		}
		$tanarIds[] = $Tanar[$tNev];
	    }
	    $oJelek = explode(',', $rec[4]);
	    if (count($oJelek) > 1) $osztalyJel = $oJelek[0].'...'; 	// Nem fér ki több, csak jelezzük, hogy volt még...
	    else $osztalyJel = $oJelek[0];				// nem érdemes szétvágni, mert nem feleltethető meg...
	    $targyJel = mb_convert_encoding($rec[6],'UTF-8','ISO-8859-2'); //.$rec[12];
	    $teremIds = explode(',',$rec[9]); // szétvág
	    $tolDt = $ADAT['tolDt'];
	    $igDt = $ADAT['igDt'];

	    // Többhetes órarend esetén egy tanárnak lehet több bejegyzése is ugyanarra az időpontra. Ekkor valahogy ki kell választani a megfelelőt.
	    // Egy adott kulcs első előfordulásakor a rekordot elmentjük és indexét is rögzítjük. Többedig előfordulás esetén csak azt rögzítjük,
	    // hogy hanyadik előfordulás volt egész addig, amíg a megadott órarendiHét számadik előfordulásig nem jutunk. Ekkor felülírjuk a korábbi rekordot.
	    // blokkok szétbontása több rekordra
	    // TODO: Ha egy blokk egyik tagja ütközik csak, akkor valójában az összesnek adott heti bejegyzéssé kellene változnia.
    	    for ($i = 0; $i < count($tanarIds); $i++) {
		
		$tanarId = $tanarIds[$i];
		$teremId = ($teremIds[$i]==0)?'NULL':$teremIds[$i]; // a 0 érték nem lehet teremId
		$kulcsDb["$het-$nap-$ora-$tanarId"]++;
		if (!isset($kulcs2index["$het-$nap-$ora-$tanarId"])) {
            	    $OrarendiOra[] = array(
                	$het,$nap,$ora,$tanarId,$osztalyJel,$targyJel,$teremId,$tolDt,$igDt
            	    );
		    $kulcs2index["$het-$nap-$ora-$tanarId"] = count($OrarendiOra)-1;
		} else {
		    if ($het == $kulcsDb["$het-$nap-$ora-$tanarId"]) {
			$OrarendiOra[ $kulcs2index["$het-$nap-$ora-$tanarId"] ] = array(
                	    $het,$nap,$ora,$tanarId,$osztalyJel,$targyJel,$teremId,$tolDt,$igDt
            		);
		    }

		}
	    }
	}

	fclose($fp);
	return $ok;

    }


?>
