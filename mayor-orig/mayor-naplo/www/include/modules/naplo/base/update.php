<?php

/*
    function update002483() {


	// A bejegyzések kezelését konfigurciós állományból áttettük adatbázisba.
	// Ennek a konverziónak a végső lépését PHP-ből célszerű elvégezni - ezt csinálja ez a script

	global $FEGYELMI_FOKOZATOK, $DICSERET_FOKOZATOK, $HIANYZASI_FOKOZATOK;

	// Hogy ne fusson le feleslegesen minden oldalbetöltésekor egy lock file-t készítünk a sikeres lefutás után...
	// Idővel aztán kikommentezzük a függvényhívást, még később akár töröljük is a függvényt...
	$dir = file_exists(_CACHEDIR)?_CACHEDIR:_DOWNLOADDIR;
	$lock = $dir.'/002483.lock';
	if (!file_exists($lock)) {
	    require_once('include/modules/naplo/share/ertekeles.php');

	    $lr = db_connect('naplo_intezmeny');
	    db_start_trans($lr);
	    // A fegyelmi fokozatok neveinek beállítása
	    foreach ($FEGYELMI_FOKOZATOK as $fokozat => $bejegyzesTipusNev) {
		if ($fokozat > 0) {
		    // Ki jogosult beírni
		    $jogosult = array('admin');
		    if (in_array(mb_substr($bejegyzesTipusNev, 0, 9, 'UTF-8'), array('igazgatói', 'nevelőtes'))) $jogosult[] = 'vezetőség';
		    if (mb_substr($bejegyzesTipusNev, 0, 13, 'UTF-8') == 'osztályfőnöki') $jogosult[] = 'osztályfőnök';
		    if (mb_substr($bejegyzesTipusNev, 0, 10, 'UTF-8') == 'szaktanári') $jogosult[] = 'szaktanár';
		    // frissítés
		    $q = "UPDATE `bejegyzesTipus` SET `bejegyzesTipusNev`='%s', `jogosult`='".implode(',', $jogosult)."' WHERE `tipus`='fegyelmi' AND `fokozat`='%s'";
		    $v = array($bejegyzesTipusNev, $fokozat);
		    $r = db_query($q, array('fv' => 'update002483/fegyelmi','modul' => 'naplo_intezmeny','values' => $v, 'rollback' => true), $lr);
		    if (!$r) {
			db_close($lr);
			return false;
		    }
		}
	    }
	    // A dicséret fokozatok neveinek beállítása
	    foreach ($DICSERET_FOKOZATOK as $fokozat => $bejegyzesTipusNev) {
		if ($fokozat > 0) {
		    // Ki jogosult beírni
		    $jogosult = array('admin');
		    if (in_array(mb_substr($bejegyzesTipusNev, 0, 9, 'UTF-8'), array('igazgatói', 'nevelőtes'))) $jogosult[] = 'vezetőség';
		    if (mb_substr($bejegyzesTipusNev, 0, 13, 'UTF-8') == 'osztályfőnöki') $jogosult[] = 'osztályfőnök';
		    if (mb_substr($bejegyzesTipusNev, 0, 10, 'UTF-8') == 'szaktanári') $jogosult[] = 'szaktanár';
		    // frissítés
		    $q = "UPDATE `bejegyzesTipus` SET `bejegyzesTipusNev`='%s', `jogosult`='".implode(',', $jogosult)."' WHERE `tipus`='dicseret' AND `fokozat`='%s'";
		    $v = array($bejegyzesTipusNev, $fokozat);
		    $r = db_query($q, array('fv' => 'update002483/dicseret','modul' => 'naplo_intezmeny','values' => $v, 'rollback' => true), $lr);
		    if (!$r) {
			db_close($lr);
			return false;
		    }
		}
	    }
	    // Az adott számú igazolatlan hiűnyzáshoz rendelt fegyelmi fokozatok eltárolása
	    foreach ($HIANYZASI_FOKOZATOK as $hianyzasDb => $fokozat) {
		if ($fokozat > 0) {
		    $q = "UPDATE `bejegyzesTipus` SET `hianyzasDb`=%u WHERE `tipus`='fegyelmi' AND `fokozat`='%s'";
		    $v = array($hianyzasDb, $fokozat);
		    $r = db_query($q, array('fv' => 'update002483/hianyzas','modul' => 'naplo_intezmeny','values' => $v, 'rollback' => true), $lr);
		    if (!$r) {
			db_close($lr);
			return false;
		    }
		}
	    }
	    // A felesleges fegyelmi és dicséret fokozatok törlése (20-20 fokozat volt felvéve)
	    $q = "DELETE FROM `bejegyzesTipus` WHERE `bejegyzesTipusNev` IS NULL";
	    $r = db_query($q, array('fv' => 'update002483/delete','modul' => 'naplo_intezmeny', 'rollback' => true), $lr);

	    if ($r) db_commit($lr);
	    db_close($lr);

	    $fp = fopen($lock,'w');
	    fwrite($fp, 'PHP update 002483 done.');
	    fclose($fp);
	}
    }
*/


//    update002483();

?>
