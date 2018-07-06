<?php


function ujOsztaly($ADAT) {
/*
    TODO: átnézendő az új évfolyam rendszer esetén:
    kTanev, vTanev - az osztály tényleges indulási és befejező tanéve (ami != a végzés tanéve) -- ok
    osztalyJel generálás (évfolyam lekérdezés) -- ok
*/

	global $mayorCache;
	$mayorCache->delType('osztaly');

	$leiras = $ADAT['leiras']; $kTanev = $ADAT['kezdoTanev']; $vTanev = $ADAT['vegzoTanev'];
	$kEvfolyamSorszam = $ADAT['kezdoEvfolyamSorszam']; $jel = $ADAT['jel']; 
	$telephelyId = $ADAT['telephelyId'];
	// Felhasználva, hogy le vannak kérdzve a definiált tanévek
	// Ellenőrizzük, hogy a megadott tanév helyes-e...
	if (!in_array($kTanev,$ADAT['tanevek']) || !in_array($vTanev,$ADAT['tanevek'])) {
		$_SESSION['alert'][] = 'message:wrong_data:ujOsztaly:'."$kTanev/$vTanev";
		return false;
	}

	// Csatlakozás az adatbázishoz
	$lr = db_connect('naplo_intezmeny', array('fv' => 'ujOsztaly'));
	if (!$lr) return false;

	// Osztály felvétele
	if (isset($telephelyId)) {
	    $q = "INSERT INTO osztaly (leiras, kezdoTanev, vegzoTanev, kezdoEvfolyamSorszam, jel, telephelyId, osztalyJellegId)
		VALUES ('%s', %u, %u, %u, '%s', %u, %u)";
	    $v = array($leiras, $kTanev, $vTanev, $kEvfolyamSorszam, $jel, $telephelyId, $ADAT['osztalyJellegId']);
	} else {
	    $q = "INSERT INTO osztaly (leiras, kezdoTanev, vegzoTanev, kezdoEvfolyamSorszam, jel, osztalyJellegId)
		    VALUES ('%s', %u, %u, %u, '%s', %u)";
	    $v = array($leiras, $kTanev, $vTanev, $kEvfolyamSorszam, $jel, $ADAT['osztalyJellegId']);
	}
	$osztalyId = db_query($q, array('fv' => 'ujOsztaly', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
	if (!$osztalyId) { db_close($lr); return false; }

	// Az aktív tanévek osztalyNaplo táblájába vegyük fel az osztályt! És ha inicializálva van a munkaterv, akkor a default-hoz rendeljük is hozzá!
	// Aktív tanévek lekérdezése
	$ok = updateOsztalyNev($osztalyId, $lr);

	db_close($lr);
	return $osztalyId;
}

function updateOsztalyNev($osztalyId, $lr = null) {
	/*
	    Az aktív tanévek osztalyNaplo táblájába vegyük fel/módosítsuk az osztályt! És ha inicializálva van a munkaterv, és nincs még hozzárendelve,
	    akkor a default-hoz rendeljük is hozzá!
	    Hopp! És a tankör-nevek?
	*/

	global $mayorCache;
	$mayorCache->delType('osztaly');

	// Aktív tanévek lekérdezése
	$q ="SELECT DISTINCT tanev FROM szemeszter WHERE statusz = 'aktív'";
	$ret = db_query($q, array('fv' => 'updateOsztalyNev', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array()), $lr);
	if (!is_array($ret)) { return false; }
	$ok = true;
	foreach ($ret as $key => $te) {
	    $tanevDb = tanevDbNev(__INTEZMENY,$te);
	    $osztalyAdat = getOsztalyAdat($osztalyId, $te, $lr);
	    if ($te < $osztalyAdat['kezdoTanev'] || $te > $osztalyAdat['vegzoTanev']) { // ebben a tanévben nem érintett az osztály
		// törlés az osztalyNaplo táblából
		$q = "DELETE FROM `%s`.osztalyNaplo WHERE osztalyId=%u";
		$v = array($tanevDb, $osztalyId);
		$ok = $ok && db_query($q, array('fv' => 'updateOsztalyNev/osztalyNaplo - delete', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
		// törlés a munkatervOsztaly táblából
		$q = "DELETE FROM `%s`.munkatervOsztaly WHERE osztalyId=%u";
		$v = array($tanevDb, $osztalyId);
		$ok = $ok && db_query($q, array('fv' => 'updateOsztalyNev/munkatervOsztaly - delete', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	    } else { // ebben a tanévben érintett az osztály
		$osztalyJel = getOsztalyJel($osztalyId, $te, $osztalyAdat, $lr);
		$evfolyamJel = getEvfolyamJel($osztalyId, $te, $osztalyAdat, $osztalyJellel=false, $lr);
		$evfolyam = getEvfolyam($osztalyId, $te, $osztalyAdat, $lr);
		// Osztálynapló felvétele
		$q = "REPLACE INTO `%s`.osztalyNaplo (osztalyId, osztalyJel, evfolyam, evfolyamJel) VALUES (%u, '%s', %u, '%s')";
		$v = array($tanevDb, $osztalyId, $osztalyJel, $evfolyam, $evfolyamJel);
		$ok = $ok && db_query($q, array('fv' => 'updateOsztalyNev/osztalyNaplo - replace', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
		if (!$ok) continue;
		// Van-e inicializált munkaterv?
		$q = "SELECT COUNT(*) FROM `%s`.munkaterv WHERE munkatervId=1";
		$v = array($tanevDb);
		$db = db_query($q, array('fv' => 'updateOsztalyNev/munkaterv', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'), $lr);
		if ($db == 1) { // Ha van, akkor van-e már osztaly-munkaterv hozzárendelés
		    $q = "SELECT COUNT(*) FROM `%s`.munkatervOsztaly WHERE osztalyId=%u";
		    $v = array($tanevDb, $osztalyId);
		    $db = db_query($q, array('fv' => 'updateOsztalyNev/munkatervOsztaly - select', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result'=>'value'), $lr);
		    if ($db == 0) { // Ha nincs, akkor rendeljük az 1-es munkatervhez az osztályt
			$q = "INSERT INTO `%s`.munkatervOsztaly (munkatervId, osztalyId) VALUES (1, %u)";
			$v = array($tanevDb, $osztalyId);
			$ok = $ok && db_query($q, array('fv' => 'updateOsztaly/munkatervOsztaly - insert', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
		    }
		}
	    }
	} // foreach...
	// tankör-nevek módosítása
	$q = "SELECT tankorId FROM tankorOsztaly WHERE osztalyId = %u";
        $v = array($osztalyId);
        $tankorIds = db_query($q, array('fv' => 'updateOsztalyNev', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v), $lr);
        if (is_array($tankorIds) && count($tankorIds)>0) {
            for ($i=0; $i<count($tankorIds); $i++) {
                $_tankorId = $tankorIds[$i];
                $ujTankorNevek[] = (setTankorNev($_tankorId, $tankorNevExtra=null, $lr)); // ha ez nem sikerül, a session üzeni majd a megfelelő hibát, nem a tranzakció része
            }
        }
	return $ok;
}

function updateOsztaly($osztalyId, $file, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto = '	', $rovatfej = false) {


	if (!file_exists($file)) {
		$_SESSION['alert'][] = 'message:file_not_found:'.$file;
		return false;
	}

	if (!is_array($MEZO_LISTA)) {
		$_SESSION['alert'][] = 'message:wrong_parameter:MEZO_LISTA';
		return false;
	}

	if (!is_array($KULCS_MEZOK)) {
		$_SESSION['alert'][] = 'message:wrong_parameter:KULCS_MEZOK';
		return false;
	}

	// A beDt és kiDt kiszűrése a MEZO_LISTABOL
	$keyBeDt = array_search('beDt',$MEZO_LISTA);
	if (!$keyBeDt && $keyBeDt !== 0) $keyBeDt = false;
	else $MEZO_LISTA[$keyBeDt] = '';
	$keyKiDt = array_search('kiDt',$MEZO_LISTA);
	if (!$keyKiDt && $keyKiDt !== 0) $keyKiDt = false;
	else $MEZO_LISTA[$keyKiDt] = '';

	// és a KULCS_MEZOK közül
	$KULCS_MEZOK = array_diff($KULCS_MEZOK,array('beDt','kiDt'));

	// A frissítendő attribútumok listája
	$attrList = array_values(array_filter($MEZO_LISTA));

	$fp = fopen($file,'r');
	if (!$fp) {
		$_SESSION['alert'][] = 'message:file_open_error:'.$file;
		return false;
	}

	$lr = db_connect('naplo_intezmeny', array('fv' => 'updateOsztaly'));
	if (!$lr) { 
	    $_SESSION['alert'][] = 'message:db_connect_failure:updateOsztaly';
	    fclose($fp); 
	    return false; 
	}
	db_start_trans($lr);

	// Az első sor kihagyása
	if ($rovatfej) $sor = fgets($fp,1024);
	$TAG = $TAGV = array();
	while ($sor = fgets($fp, 1024)) {

		$adatSor = explode($mezo_elvalaszto, chop($sor));
		$beDt = $kiDt = '';
		if ($keyBeDt !== false) $beDt = $adatSor[$keyBeDt];
		if ($keyKiDt !== false) $kiDt = $adatSor[$keyKiDt];
		// keresési feltétel összerakása
		$where = $wherev = array();
		for ($i = 0; $i < count($KULCS_MEZOK); $i++) {
			if ($adatSor[$KULCS_MEZOK[$i]] != '') {
			    if ($adatSor[$KULCS_MEZOK[$i]] == '\N') {
				$where[] = "`%s`=NULL";
				array_push($wherev, $MEZO_LISTA[$KULCS_MEZOK[$i]]);
			    } else {
				$where[] = "`%s`='%s'";
				array_push($wherev, $MEZO_LISTA[$KULCS_MEZOK[$i]], $adatSor[$KULCS_MEZOK[$i]]);
			    }
			}
		}
		if (count($where) > 0) {
			$q = "SELECT diakId FROM diak WHERE ".implode(' AND ', $where);
			$diakIds = db_query($q, array('fv' => 'updateOsztaly', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $wherev), $lr);
			$num = count($diakIds);
		} else { $num = 0; }
		if ($num == 1 && _SKIP_ON_DUP === true) { $_SESSION['alert'][] = 'info:_SKIP_ON_DUP:'.serialize($sor); continue; }
		if ($num == 1) { // update
			// tag felvételhez adatsor
			$diakId = $diakIds[0];
			if (!isset($kiDt) || $kiDt=='') {
			    $TAG[] = "(%u, %u, '%s', NULL)";
			    array_push($TAGV, $diakId, $osztalyId, $beDt);
			} else {
			    $TAG[] = "(%u, %u, '%s', '%s')";
			    array_push($TAGV, $diakId, $osztalyId, $beDt, $kiDt);
			}
			// diak adatok frissítése
			$UPDATE = $UPDATEV = array();
			for ($i = 0; $i < count($MEZO_LISTA); $i++) {
				if (
				    $MEZO_LISTA[$i] != ''
                        	    and $adatSor[$i] != ''
                        	    and !in_array($i, $KULCS_MEZOK)
				) {
					$UPDATE[] = "`%s`='%s'";
					array_push($UPDATEV, $MEZO_LISTA[$i], $adatSor[$i]);
				}
			}
			if (count($UPDATE) > 0) {
				$q = "UPDATE diak SET ".implode(',', $UPDATE)." WHERE ".implode(' AND ', $where);
				$v = mayor_array_join($UPDATEV, $wherev);
				$r = db_query($q, array('fv' => 'updateOsztaly/update', 'modul' => 'naplo_intezmeny', 'values' => $v, 'rollback' => true), $lr);
                    		if (!$r) {
                        	    db_close($lr);
				    fclose($fp); 
                        	    return false;
                    		}
			}
		} elseif ($num == 0) { // insert
		    $insertValues = $insertPatterns = array();
                    for ($i = 0; $i < count($MEZO_LISTA); $i++) {
                        if ($MEZO_LISTA[$i] != '') {
                            if ($adatSor[$i] == '\N') {
                                $insertValues[] = 'NULL';
                                $insertPatterns[] = '%s';
                            } else {
                                $insertValues[] = $adatSor[$i];
                                $insertPatterns[] = "'%s'";
                            }
                        }
                    }
                    $q = 'INSERT INTO `diak` ('.implode(',', array_fill(0, count($attrList), '%s')).') 
                                    VALUES ('.implode(',', $insertPatterns).')';
                    $v = mayor_array_join($attrList, $insertValues);
		    /*



			$value = array();
			for ($i = 0; $i < count($MEZO_LISTA); $i++) {
				if ($MEZO_LISTA[$i] != '') $value[] = $adatSor[$i];
			}
			// beszúrás egyesével, hogy meglegyen a diakId (insert_id)
			$q = "INSERT INTO diak (`".implode("`,`", array_fill(0, count($attrList), '%s'))."`) 
				VALUES ('".implode("','", array_fill(0, count($value), '%s'))."')";
			$v = array_merge($attrList, $value);

		    */
		    $diakId = db_query($q, array('fv' => 'updateOsztaly/insert', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v, 'rollback' => true), $lr);
                    if (!$diakId) {
                        db_close($lr);
			fclose($fp); 
                        return false;
                    }

			if ($diakId) {
				// tagok adatai
				if (!isset($kiDt) || $kiDt=='') {
				    $TAG[] = "(%u, %u,'%s', NULL)";
				    array_push($TAGV, $diakId, $osztalyId, $beDt);
				} else {
				    $TAG[] = "(%u, %u, '%s', '%s')";
				    array_push($TAGV, $diakId, $osztalyId, $beDt, $kiDt);
				}
			}
		} else {
			$_SESSION['alert'][] = 'message:wrong_data:'.$where;
		}
	} // while
	if (count($TAG) > 0) { // tagok felvétele az osztályba
		$q = "REPLACE INTO osztalyDiak (diakId,osztalyId,beDt,kiDt) VALUES ".implode(",\n",$TAG);
		$r = db_query($q, array('fv' => 'updateOsztaly/osztályba', 'modul' => 'naplo_intezmeny', 'values' => $TAGV, 'rollback'=>true), $lr);
		if (!$r) {
                    db_close($lr);
		    fclose($fp); 
                    return false;
		}
	}
	db_commit($lr);
	db_close($lr);

	fclose($fp);
	return true;
}

/* áthelyezve: share/osztalyModifier.php
// osztalyId, tanarId, beDt --> kiDt
function osztalyfonokKileptetes($osztalyId, $tanarId, $beDt, $kiDt, $olr = '') {
...
}
*/

function osztalyfonokKinevezes($osztalyId, $tanarId, $beDt, $lr = null) {
	
	global $mayorCache;
	$mayorCache->delType('osztaly');
			
	// Ellenőrizzük, hogy az adott időszakban nincs-e már kinevezve ofőnek
	$q = "SELECT COUNT(*) AS db FROM osztalyTanar WHERE osztalyId=%u AND tanarId=%u
					AND (beDt<'%s' AND '%s'<kiDt)";
	$v = array($osztalyId, $tanarId, $beDt, $beDt);
	$db = db_query($q, array('fv' => 'osztalyfonokKinevezes', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v), $lr);
	
	if ($db > 0) {
		$_SESSION['alert'][] = 'message:wrong_data:már ki van nevezve:'."$beDt - $kiDt:$num";
		return false;
	}

	$q = "INSERT INTO osztalyTanar (osztalyId, tanarId, beDt, kiDt) VALUES (%u, %u, '%s', NULL)";
	$v = array($osztalyId, $tanarId, $beDt);
	return db_query($q, array('fv' => 'osztalyfonokKinevezes', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);

}

/*
    TODO: az osztalyJellegId módosítása nem megengedett, vagy végig kell gondolni, 
    hogy mi mindent érint (osztalyJel, evfolyam, evfolyamJel - minden érintett tanév osztalyNaplo táblájában+tankör nevek+vegzoTanev...)
*/
function osztalyLeirasTelephelyModositas($osztalyId, $leiras, $telephelyId, $osztalyJellegId, $kezdoEvfolyamSorszam, $osztalyAdat, $lr = null) {

	global $mayorCache;
	$mayorCache->delType('osztaly');

	if (isset($telephelyId) && $telephelyId != '') {
	    $q = "UPDATE osztaly SET leiras='%s',telephelyId=%u, kezdoEvfolyamSorszam=%u WHERE osztalyId=%u";
	    $v = array($leiras, $telephelyId, $kezdoEvfolyamSorszam, $osztalyId);
	} else {
	    $q = "UPDATE osztaly SET leiras='%s',telephelyId=NULL, kezdoEvfolyamSorszam=%u WHERE osztalyId=%u";
	    $v = array($leiras, $kezdoEvfolyamSorszam, $osztalyId);
	}
	$ret = db_query($q, array('fv' => 'osztalyLeirasTelephelyModositas', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	return osztalyJellegModositas($osztalyId, $osztalyJellegId, $osztalyAdat, $lr);

}

function osztalyJellegModositas($osztalyId, $osztalyJellegId, $osztalyAdat, $lr = null) {

    global $mayorCache;
    $mayorCache->delType('osztaly');

    // Az osztalyJelleg lekérdezése
    $ojAdat = getOsztalyJellegAdat($osztalyJellegId);
    $ojEvfolyamJelek = explode(',', $ojAdat['evfolyamJelek']);
    // csak akkor módosítunk, ha az oszály évfolyamainak száma <= az osztály-jelleg évfolyamainak száma
    if (count($ojEvfolyamJelek) < ($osztalyAdat['vegzoTanev']-$osztalyAdat['kezdoTanev']+$osztalyAdat['kezdoEvfolyamSorszam'])) {
	$_SESSION[] = 'message:wrong_data:Az osztály évfolyamainak száma nem engedi meg az adott osztály-jellegre váltást';
	return false;
    }
    // osztalyJelleg módosítása
    $q = "UPDATE osztaly SET osztalyJellegId=%u WHERE osztalyId=%u";
    $v = array($osztalyJellegId, $osztalyId);
    $ret = db_query($q, array('fv' => 'osztalyJellegModositas', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
    // tanév adatbázisok frissítése (osztalyNaplo, munkatervOsztaly)
    return updateOsztalyNev($osztalyId, $lr);
}

function osztalyTorles($osztalyId) {

	global $mayorCache;
	$mayorCache->delType('osztaly');

	$q = "DELETE FROM osztaly WHERE osztalyId=%u";
	return db_query($q, array('fv' => 'osztalyTorles', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId)));

}

function ujTag($osztalyId, $diakId, $beDt, $kiDt) {
/*
 * Az osztályba sorolás MOSTANTÓL többszakaszos, mint pl. a tankörbesorolás, azaz a diakId:osztalyId:beDt a
 * kulcs a kapcsolótáblában.
 */

	// Csatlakozás az adatbázishoz
	$lr = db_connect('naplo_intezmeny', array('fv' => 'ujTag'));
	if (!$lr) return false;
	db_start_trans($lr);

	// Van-e már beDt-t tartalmazó osztálytagsága
	$q = "SELECT beDt FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND beDt<'%s' AND (kiDt IS NULL OR kiDt >= '%s')";
	$ret = db_query($q, array('fv' => 'ujTag', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($osztalyId, $diakId, $beDt, $beDt)));
	if (!is_null($ret)) $beDt = $ret;

	if ($kiDt != '') {
	    // Ha kiDt nem üres, akkor: van-e kiDt-t tartalmazó osztálytagsága
	    $q = "SELECT kiDt FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND beDt<'%s' AND (kiDt IS NULL OR kiDt >= '%s')";
	    $ret = db_query($q, array('fv' => 'ujTag', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => array($osztalyId, $diakId, $kiDt, $kiDt)));
	    if (!is_null($ret)) $kiDt = $ret['kiDt'];
	}

	if ($kiDt == '') {
	    // Ha $kiDt üres, akkor töröljük az eddigi bejegyzéseket a lefedett tartományból és felvesszük az újat
	    $q = "DELETE FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND beDt>='%s'";
	    db_query($q, array('fv' => 'ujTag/töröl', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId, $diakId, $beDt)));
	    $q = "INSERT INTO osztalyDiak (osztalyId, diakId, beDt, kiDt) VALUES (%u, %u, '%s', NULL)";
	    db_query($q, array('fv' => 'ujTag/felvesz', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId, $diakId, $beDt)));
	} else {
	    // Ha $kiDt nem üres, akkor töröljük az eddigi bejegyzéseket a lefedett tartományból és felvesszük az újat
	    $q = "DELETE FROM osztalyDiak WHERE osztalyId=%u AND diakId=%u AND '%s'<=beDt AND kiDt<='%s'";
	    db_query($q, array('fv' => 'ujTag/töröl', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId, $diakId, $beDt, $kiDt)));
	    $q = "INSERT INTO osztalyDiak (osztalyId, diakId, beDt, kiDt) VALUES (%u, %u, '%s', '%s')";
	    db_query($q, array('fv' => 'ujTag/felvesz', 'modul' => 'naplo_intezmeny', 'values' => array($osztalyId, $diakId, $beDt, $kiDt)));
	}

	db_commit($lr);
	db_close($lr);	
	return $r;
}

function diakKepzesModositas($diakIds, $kepzesMod, $dt) {

    $modKepzesIds = array_keys($kepzesMod);

    if (!is_array($diakIds) || !is_array($modKepzesIds) || count($diakIds) == 0) return false;
    $lr = db_connect('naplo_intezmeny');
    db_start_trans($lr);

    // A megadott diákok adott dátum szerinti képzései
    $q = "SELECT kepzesId, diakId FROM kepzesDiak WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") AND tolDt<='%s' AND (igDt IS NULL OR '%s'<=igDt)";
    $v = mayor_array_join($diakIds, array($dt,$dt));
    $kepzesOld = db_query($q, array('fv' => 'diakKepzesModositas/dKepzes','modul'=>'naplo_intezmeny','values'=>$v,'result'=>'keyvalues'), $lr);
    if (!is_array($kepzesOld)) { db_rollback($lr); db_close($lr); return false; }

    $oldKepzesIds = array_keys($kepzesOld);
    $kepzesIds = array_unique(array_merge($modKepzesIds, $oldKepzesIds));
    foreach ($kepzesIds as $kepzesId) {

	if (!is_array($kepzesMod[$kepzesId])) $kepzesMod[$kepzesId] = array();
	if (!is_array($kepzesOld[$kepzesId])) $kepzesOld[$kepzesId] = array();

	$add = array_unique(array_diff($kepzesMod[$kepzesId], $kepzesOld[$kepzesId]));
	$del = array_unique(array_diff($kepzesOld[$kepzesId], $kepzesMod[$kepzesId]));
	$diff = array_unique(array_merge($add, $del));
	// Aki $dt után került be a képzésbe és most kiveendő vagy felveendő, azt töröljük 
	if (count($diff) > 0) {
	    $q = "DELETE FROM kepzesDiak WHERE kepzesId=%u AND tolDt>'%s' 
		AND diakId IN (".implode(',',array_fill(0,count($diff),'%u')).")";
	    $v = mayor_array_join(array($kepzesId, $dt), $diff);
	    $r = db_query($q, array('fv'=>'diakKepzesModositas/delete','modul'=>'naplo_intezmeny','values'=>$v), $lr);
	    if (!$r) { db_rollback($lr); db_close($lr); return false; }
	}
	if (count($del) > 0) {
	    // a tolDt=$dt esetén csak a lezárandókat kell törölni
	    $q = "DELETE FROM kepzesDiak WHERE kepzesId=%u AND tolDt='%s' 
		AND diakId IN (".implode(',',array_fill(0,count($del),'%u')).")";
	    $v = mayor_array_join(array($kepzesId, $dt), $del);
	    $r = db_query($q, array('fv'=>'diakKepzesModositas/delete','modul'=>'naplo_intezmeny','values'=>$v), $lr);
	    // Aki korábban benne volt a képzésben, de most nincs, azt le kell zárni
	    $q = "UPDATE kepzesDiak SET igDt='%s' - INTERVAL 1 DAY WHERE kepzesId=%u AND tolDt<'%s' AND (igDt IS NULL OR '%s'<=igDt) 
		AND diakId IN (".implode(',',array_fill(0,count($del),'%u')).")";
	    $v = mayor_array_join(array($dt, $kepzesId, $dt, $dt), $del);
	    $r = db_query($q, array('fv'=>'diakKepzesModositas/update','modul'=>'naplo_intezmeny','values'=>$v), $lr);
	    if (!$r) { db_rollback($lr); db_close($lr); return false; }
	}
	if (count($add) > 0) {
	    // Aki eddig nem volt, azt fel kell venni
	    $v = $INS = array();
	    foreach ($add as $diakId) {
		$INS[] = "(%u, %u, '%s', NULL)";
		array_push($v, $kepzesId, $diakId, $dt);
	    }
	    if (count($INS)>0) {
		$q = "INSERT INTO kepzesDiak (kepzesId, diakId, tolDt, igDt) VALUES ".implode(',',$INS);
		db_query($q, array('fv'=>'diakKepzesModositas/inster','modul'=>'naplo_intezmeny','values'=>$v), $lr);
		if (!$r) { db_rollback($lr); db_close($lr); return false; }
	    }
	}
    }    
    db_commit($lr);
    db_close($lr);

}

?>