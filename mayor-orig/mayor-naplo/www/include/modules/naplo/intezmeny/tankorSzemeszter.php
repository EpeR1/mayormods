<?php
/*
    function getTankorSzemeszterek($tankorIds) 
	| A függvény visszaadja az összes `tankorSzemeszter` bejegyzést: szűrő tankorIds tömb 
	--> [multiassoc][tankorId]
    function getSzemeszterek_spec($tolTanev = '', $igTanev = '')
	| `szemeszter` tábla bejegyzései 
	--> [indexed]
    function tankorSzemeszterModositas($Modositas, $tankorSzemeszter, $tankorNevek, $Szemeszterek, $tanevZarasDt) {
	| A módosító
	* function _createName($ADAT,$SZ,$extra)
*/

    function getTankorSzemeszterek($tankorIds) {

	if (count($tankorIds) > 0) {
	    $q = "SELECT * FROM tankorSzemeszter WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
	    return db_query($q, array(
		'fv' => 'getTankorSzemeszterek', 'modul' => 'naplo_intezmeny', 'result' => 'multiassoc', 'keyfield' => 'tankorId', 'values' => $tankorIds
	    ));
	} else {
	    return array();
	}

    }

    function getSzemeszterek_spec($tolTanev = '', $igTanev = '') {

	$v = array();
	if ($tolTanev != '') {
	    $where = "WHERE tanev >= %u";
	    $v[] = $tolTanev;
	}
	if ($igTanev != '') { 
	    if ($tolTanev != '') $where .= " AND tanev <= %u";
	    else $where = "WHERE tanev <= %u";
	    $v[] = $igTanev;
	}
	
	$q = "SELECT * FROM szemeszter $where ORDER BY tanev,szemeszter";
	return db_query($q, array('fv' => 'getSzemeszterek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

    }

    function tankorSzemeszterModositas($Modositas, $tankorSzemeszter, $tankorNevek, $Szemeszterek, $tanevZarasDt) {

	// $tankorTanarAdatok inicializásása - $Szemeszterek alapján
	$tankorIds = $tankorTanarAdatok = array();
	for ($i = 0; $i < count($Szemeszterek); $i++) {
	    $tankorTanarAdatok[$Szemeszterek[$i]['tanev']][$Szemeszterek[$i]['szemeszter']] = array(
		'tankorIds' => array(),
		'tanev' => $Szemeszterek[$i]['tanev'],
		'statusz' => $Szemeszterek[$i]['statusz'],
		'kezdesDt' => $Szemeszterek[$i]['kezdesDt'],
		'zarasDt' => $Szemeszterek[$i]['zarasDt']
	    );
	}

	$lr = db_connect('naplo_intezmeny');

	$Values = array();
	$Values = $v = array();
	for ($i = 0; $i < count($Modositas); $i++) {

	    $M = $Modositas[$i];
	    if ($tankorTanarAdatok[$M['tanev']][$M['szemeszter']]['statusz'] != 'lezárt') {

		if (is_array($tankorSzemeszter[$M['tankorId']][$M['tanev']][$M['szemeszter']])) {
		    // update - vagy semmi
		    $regiOraszam = $tankorSzemeszter[$M['tankorId']][$M['tanev']][$M['szemeszter']]['oraszam'];
		    if (floatval($M['oraszam']) >= 0 && $regiOraszam != $M['oraszam']) { // állíthatjuk nullára
			// update - most már tényleg - itt csak az óraszám változik:
			$q = "UPDATE tankorSzemeszter SET oraszam=%f WHERE tankorId=%u AND tanev=%u AND szemeszter=%u";
			db_query($q, array(
			    'fv' => 'tankorSzemeszterModositas', 'modul' => 'naplo_intezmeny', 
			    'values' => array($M['oraszam'], $M['tankorId'], $M['tanev'], $M['szemeszter'])
			    ,'debug'=>false
			));
		    } elseif (
			floatval($M['oraszam']) == 0 
			&& $tankorTanarAdatok[$M['tanev']][$M['szemeszter']]['statusz'] == 'tervezett'
			&& $regiOraszam != $M['oraszam']
		    ) { // Ekkor törölhetjük... nemde?
			$q = "DELETE FROM tankorSzemeszter 
				WHERE tankorId=%u AND tanev=%u 
				AND szemeszter=%u";
			db_query($q, array(
			    'fv' => 'tankorSzemeszterModositas', 'modul' => 'naplo_intezmeny', 
			    'values' => array($M['tankorId'], $M['tanev'], $M['szemeszter'])
			    ,'debug'=>false
			));
		    }
		} else {
		    // insert
		    $tankorNev = _createName($M, array($M['tanev'].'/'.$M['szemeszter']), $tankorNevek[$M['tankorId']]); // todo setTankorNev() függvénnyel inkább, tankor.tankorNevExtra
		    if ($tankorNev != '') {
			array_push($v, $M['tankorId'], $M['tanev'], $M['szemeszter'], $M['oraszam'], $tankorNev);
			$Values[] = "(%u, %u, %u, %f, '%s')";
			// A tanár felvételéhez kell a tol-ig dt (Szemeszterek)
			// tankorIds
			$tankorTanarAdatok[$M['tanev']][$M['szemeszter']]['tankorIds'][] = $M['tankorId'];
			if (!in_array($M['tankorId'], $tankorIds)) $tankorIds[] = $M['tankorId'];
			// tankorTanarIds - a tanevZarasDt pillanatában lévő tanárait kérdezzük le
		    
		    }
		} // insert vagy update
	    } // nem lezárt szemeszter
	} // for
	if (count($Values) > 0) {
	    $q = "INSERT INTO tankorSzemeszter (tankorId, tanev, szemeszter, oraszam, tankorNev)
		    VALUES ".implode(',', $Values);
	    db_query($q, array('fv' => 'tankorSzemeszterModositas', 'modul' => 'naplo_intezmeny', 'values' => $v,'debug'=>false));
	    // Az $tanev évi tanárt is rendeljük hozzá!
	    // kik voltak tanárok a zárás napján
	    $q = "SELECT DISTINCT tankorId, tanarId FROM tankorTanar
		    WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")
		    AND '%s' >= beDt AND '%s' <= kiDt";
	    $v = mayor_array_join($tankorIds, array($tanevZarasDt, $tanevZarasDt));
	    $ret = db_query($q, array('fv' => 'tankorSzemeszterModositas', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v, 'debug'=>false), $lr);
	    for ($i = 0; $i < count($ret); $i++) $tankorTanarIds[$ret[$i]['tankorId']][] = $ret[$i]['tanarId'];
	    foreach ($tankorTanarAdatok as $szTanev => $tanevAdat)
		foreach ($tanevAdat as $szSzemeszter => $szemeszterAdat)
		    if (count($szemeszterAdat['tankorIds']) > 0) {
		    tankorTanarFelvesz(
			$szemeszterAdat['tankorIds'], $tankorTanarIds, $szemeszterAdat,
			$szemeszterAdat['kezdesDt'], $szemeszterAdat['zarasDt']
		    );
	    }
	}

	db_close($lr);
	return true;
	
    }

    function _createName($ADAT,$SZ,$extra) {

    global $TANKOR_TIPUS;

	$tankorId = $ADAT['tankorId'];
	if ($tankorId=='') return false;
	$tanev = $ADAT['tanev'];
	$szemeszter = $ADAT['szemeszter'];
	
	$targyId = getTankorTargyId($tankorId);
	$TANKOROSZTALYOK = getTankorOsztalyai($tankorId, array('result' => 'id'));

	if ($targyId=='') return false;
        $TARGYADAT = getTargyById($targyId);
        $kdt='3000-01-01';
        $vdt='1970-01-01';
        if (is_array($SZ))
        for ($j=0; $j<count($SZ); $j++) {
            $nev = '';
	    list($_tanev,$_szemeszter)= explode('/',$SZ[$j]);
	    //checkOsztalyInTanev($_tanev);
            $OSZTALYOK = getOsztalyok($_tanev);
            if ($OSZTALYOK!== false && is_array($OSZTALYOK) && is_array($TANKOROSZTALYOK)) {
                $nev = '';
                $TMP = array();
                for($i=0; $i<count($OSZTALYOK); $i++) {
                    // Ha évenként változik az osztály jele, akkor jobb, ha nem generáljuk, hanem a lekérdezett adatokat használjuk!
                    // $_oj = genOsztalyJel($_tanev, $OSZTALYOK[$i]);
                    $_oj = $OSZTALYOK[$i]['osztalyJel'];
                    if ($_oj!==false) {
                        list($e,$o) = explode('.',$_oj);
                        if (in_array($OSZTALYOK[$i]['osztalyId'], $TANKOROSZTALYOK)) $TMP[$e][]= $o;
                    }
                }
                if (count(array_keys($TMP)) == 1) { // évfolyamon belüli osztályok:
                    $nev = implode('||',array_keys($TMP));
                    $nev .= '.'.implode('',$TMP[$nev]);
                } elseif (count((array_keys($TMP)))>1) { // multi évfolyam:
                    $K = (array_keys($TMP));
                    sort($K);
                    $nev = $K[0].'-'.$K[count($K)-1].'.';
                } else { // ekkorra már elballagott minden osztaly...
                    $nev = false;

                }

            } else {
                $nev = false; // adott szemeszterbe nem jár osztály
        }

            if ($nev!== false) {
                $nev .= ' '.$TARGYADAT['targyNev'];
		foreach($TANKOR_TIPUS as $_tt=>$_tipus) {
		    if (strstr($extra,$_tipus)) {
			$_extra = $_tipus;
		    }
		}
                $nev .= ' '.$_extra;
            }
        }
	return $nev;

    }

?>
