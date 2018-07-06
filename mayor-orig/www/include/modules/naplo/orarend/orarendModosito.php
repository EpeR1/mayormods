<?php                                                                                            

    function getHibasOrak() {
	
           $q = "SELECT * FROM orarendiOra LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) LEFT JOIN nap 
			ON (((DAYOFWEEK(dt)+5) MOD 7)+1 = orarendiOra.nap)
                        AND orarendiOra.het=nap.orarendiHet
                        AND orarendiOra.tolDt<=dt AND orarendiOra.igDt>=dt
			HAVING nap.dt IS NULL";

            return db_query($q, array('fv' => 'getHibasOrak', 'modul' => 'naplo', 'result' => 'indexed'), $lr);
    }

    function checkHaladasi($SET = array('tolDt'=>null,'igDt'=>null)) {
	$q = "SELECT dt,count(*) as db FROM ora WHERE dt BETWEEN '%s' AND '%s' GROUP BY dt";
	$v = array($SET['tolDt'],$SET['igDt']);
	return db_query($q, array('fv' => 'checkHaladasi', 'modul' => 'naplo', 'result' => 'assoc','keyfield'=>'dt','values'=>$v));
    }

   function orarendiOraTankorFelvesz($ADAT) { // ez egy másolata az orarendiOraTankorAssoc() függvénynek...

        $lr = db_connect('naplo');
/*
        foreach( array_unique($_POST['ORARENDKULCSOK']) as $_k => $_v) {
            list($_tanarId,$_osztalyJel,$_targyJel) = explode('%',$_v);
            $q = "DELETE FROM orarendiOraTankor WHERE tanarId=%u AND osztalyJel='%s' AND targyJel='%s'";
            $v = array($_tanarId, $_osztalyJel, $_targyJel);
            db_query($q, array('fv' => 'orarendiOraTankorAssoc', 'modul' => 'naplo', 'values' => $v), $lr);
        }
*/

//        for ($i = 0; $i < count($ADAT); $i++) {
	    $_tanarId = $ADAT['tanarId'];
	    $_osztalyJel = $ADAT['osztalyJel'];
	    $_targyJel = $ADAT['targyJel'];
	    $_tankorId = $ADAT['tankorId'];
                if (!is_null($_tanarId)) {
                    // bugfix 152->153. Muszáj kitörölni, mert előtte már lehet hogy egy másik tankörhöz rögzítettük ugyanazt a kulcsot... ????
                    // ez így persze egy sor felesleges query.
                    $q = "DELETE FROM orarendiOraTankor WHERE tanarId=%u AND osztalyJel='%s' AND targyJel='%s'";
                    $v = array($_tanarId, $_osztalyJel, $_targyJel);
                    $db = db_query($q, array('fv' => 'orarendTankor', 'modul' => 'naplo', 'result' => 'affected rows', 'values' => $v), $lr);
                    $q = "REPLACE INTO orarendiOraTankor (tanarId,osztalyJel,targyJel,tankorId) VALUES (%u, '%s', '%s', %u)";
                    $v = array($_tanarId, $_osztalyJel, $_targyJel, $_tankorId);
                    db_query($q, array('fv' => 'orarendTankor', 'modul' => 'naplo', 'values' => $v), $lr);
                }
//        }
        db_close($lr);

    }

    function orarendiOraTorol($ADAT) {

            //$q = "DELETE FROM orarendiOra WHERE tanarId=%u AND osztalyJel='%s' AND targyJel='%s' AND tolDt='%s'";
            //$v = array($ADAT['tanarId'], $ADAT['osztalyJel'], $ADAT['targyJel'], $ADAT['tolDt']);
            $q = "DELETE FROM orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND tolDt='%s'";
            $v = array($ADAT['het'],$ADAT['nap'], $ADAT['ora'], $ADAT['tanarId'], $ADAT['tolDt']);
            db_query($q, array('fv' => 'orarendiOraTorol', 'modul' => 'naplo', 'values' => $v), $lr);

    }

    function getFelvehetoBlokk($ADAT) {

	$RET = array();
	if (count($ADAT['tankorIds']) > 0) {
	    $q = "SELECT DISTINCT blokkId, blokkNev FROM tankorBlokk LEFT JOIN blokk USING (blokkId) WHERE tankorId IN (".implode(',',$ADAT['tankorIds']).")";
	    $RET = db_query($q, array('fv'=>'getFelvehetoBlokkok', 'modul' => 'naplo', 'result' => 'indexed'));
	}
	return $RET;

    }

    function teremModosit($ADAT) {
	if ($ADAT['tolDt']=='' || $ADAT['igDt']=='') return false;
	$ok = true;
	$lr = db_connect('naplo');
	db_start_trans($lr);

	if (is_null($ADAT['teremId'])) {
	    $v = array($ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['tanarId'], $ADAT['tolDt'], $ADAT['igDt']);
	    $q = "UPDATE orarendiOra SET teremId = NULL WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND tolDt<='%s' AND '%s'<=igDt";
	    $r = db_query($q, array('fv'=>'teremModosit', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
	} else {

	// Ellenőrizzük hogy foglalt-e...
	    $v = array($ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['teremId'], $ADAT['tolDt'], $ADAT['igDt']);
	    $q = "SELECT * FROM orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND teremId=%u AND tolDt<='%s' AND '%s'<=igDt";
	    $r = db_query($q, array('fv'=>'teremModosit', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v, 'rollback' => true), $lr);
	    if (count($r)>0) {
        	// ez a terem foglalt ... mi legyen?
		$_SESSION['alert'][] = 'info:nem_szabadTerem:'.$ADAT['teremId'];
		db_rollback($lr); db_close($lr);
		return false;
	    }
	// Van ilyen bejegyzés???
	    $v = array($ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['tanarId'], $ADAT['igDt'], $ADAT['tolDt']);
//	    $q = "SELECT * FROM orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND tolDt<='%s' AND '%s'<=igDt";
	    $q = "SELECT * FROM orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u";
	    $ret = db_query($q, array('fv'=>'teremModosit', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v, 'rollback' => true), $lr);
	    if ($ret===false) { $_SESSION['alert'][] = 'info:nincs_bejegyzes:'; db_rollback($lr); db_close($lr); return false; } 
	// - -- --- ---- -----
	    for ($i=0; $i<count($ret); $i++) {
		if ($ADAT['teremId'] != '') {
		    $q = "UPDATE orarendiOra SET teremId='%s' WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND tolDt='%s' AND igDt='%s'";
		    $v = array($ADAT['teremId'],$ADAT['het'],$ADAT['nap'],$ADAT['ora'],$ADAT['tanarId'],$ret[$i]['tolDt'],$ret[$i]['igDt']);
		    $r = db_query($q, array('fv'=>'teremModosit','modul'=>'naplo','values'=>$v, 'rollback'=>true), $lr);
		    if ($r===false) $ok=false;
		}
	    }
	}

	if ($ok) db_commit($lr); else db_rollback($lr);
	db_close($lr);


    }

    function pluszBlokkFelvesz($ADAT) {

	$lr = db_connect('naplo');
	db_start_trans($lr);

	$TANKORIDK = getTankorokByBlokkId(array($ADAT['blokkId']));
	for ($i=0; $i<count($TANKORIDK); $i++) {
	    $A = $ADAT; // az alapértelmezett adatokat vegyük át, majd írjuk felül:
	    $A['tankorId'] = $TANKORIDK[$i];
	    $A['tanarIdk'] = getTankorTanaraiByInterval($A['tankorId'],array('tolDt' => $ADAT['tolDt'], 'igDt' => $ADAT['igDt']));
	    $A['tanarId'] = $A['tanarIdk'][0]['tanarId']; // az elsőt vegyük alapul ha több van :(
	    $ok = pluszOraFelvesz($A, $lr);
	    if ($ok === false) break;
	}

	if ($ok) db_commit($lr); else db_rollback($lr);
	db_close($lr);

    }



    function pluszOraFelvesz($ADAT, $olr='') {

	if ($ADAT['tolDt']=='' || $ADAT['igDt']=='') return false;

	// kell egyáltalán ellenőrizni az ütközéseket?
	// Ha a felveendő órán nem kötelező a jelenlét, nem érdekel minket az egész!
	$TA = getTankorAdat($ADAT['tankorId']);

	$ok = true;
	if ($olr=='') {
	    $lr = db_connect('naplo');
	    db_start_trans($lr);
	} else {
	    $lr = $olr;
	}

	// Csak akkor ellenőrizzük, ha tényleg kell - persze értelmetlen állapot is előidézhető így...
	if ($TA[$ADAT['tankorId']][0]['jelenlet'] != 'nem kötelező') {
	    // ütközések ellenőrzése
	    // az adott tanárnak lett-e közben azon a helyen órája?

	    // -- tanarLukasOrajae();

	    // a diákok ütköznek-e? (jelenlét és felmentés vizsgálata
	    $DIAKOK = getTankorDiakjaiByInterval($ADAT['tankorId'], __TANEV, $ADAT['tolDt'], $ADAT['igDt'], $lr);
	    for ($i=0; $i<count($DIAKOK['idk']); $i++) { 
		$_diakId = $DIAKOK['idk'][$i];
		    // Gyűjtsük ki azokat a dátumokat, amikben vizsgálódunk
		    // ... minden munkatervben azonos az órarendi hát (tanítási nap esetén) ...
		    $q = "select distinct dt from nap where dt>='%s' and dt<='%s' and dayofweek(dt)=%u and orarendiHet!=0";
		    $v = array($ADAT['tolDt'],$ADAT['igDt'],$ADAT['nap']+1);
		    $NAPOK = db_query($q, array('fv'=>'pluszOraFelvesz-check','modul'=>'naplo', 'result'=>'indexed', 'values'=> $v), $lr);
		    for ($n=0; $n<count($NAPOK); $n++) {
                	$dt = $NAPOK[$n]['dt'];
			// ezen a napok van -e olyan tankörnek órája, amire nincs felmentése - szuperlekérdezés! :) - vigyázz az intervallumokkal és a NOT NULL-okkal!
			// ha van, az bizony ütközést jelent, azon az órán ott kellene lennie a diáknak!
			$q = "SELECT distinct orarendiOraTankor.tankorId FROM orarendiOra 
				LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
				LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
				LEFT JOIN ".__INTEZMENYDBNEV.".tankorDiak ON (orarendiOraTankor.tankorId = tankorDiak.tankorId AND tankorDiak.diakId=%u AND tankorDiak.beDt<='%s' AND (tankorDiak.kiDt>='%s' or tankorDiak.kiDt IS NULL))
				LEFT JOIN ".__INTEZMENYDBNEV.".tankorDiakFelmentes ON (tankorDiakFelmentes.felmentesTipus='óralátogatás alól' AND orarendiOraTankor.tankorId = tankorDiak.tankorId AND tankorDiakFelmentes.diakId=%u AND tankorDiakFelmentes.beDt<='%s' AND (tankorDiakFelmentes.kiDt>='%s' or tankorDiakFelmentes.kiDt IS NULL))
				LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId)
				WHERE tankorTipus.jelenlet='kötelező' AND tolDt<='%s' AND igDt>='%s' AND orarendiOra.het=%u AND orarendiOra.nap=%u AND orarendiOra.ora=%u 
				AND tankorDiak.tankorId IS NOT NULL AND tankorDiakFelmentes.tankorId IS NULL";
			$v = array($_diakId,$dt,$dt, $_diakId, $dt, $dt,$dt,$dt,$ADAT['het'],$ADAT['nap'],$ADAT['ora']);
			$UTKOZIK = db_query($q, array('fv'=>'pluszOraFelvesz-pre','modul'=>'naplo','result'=>'indexed', 'values'=> $v), $lr);
			// és jelezzük a hibát!
			// megj: ennél azért szofisztikáltabb is lehetne a hibajelentő függvény...
			if (count($UTKOZIK)>0) {
                    	    // sajnos ütközés van, nem vehetjük fel:
			    $ok = false;
			    if (is_null($_DA[$_diakId])) {
				$_DA[$_diakId] = getDiakAdatById($_diakId,null,$lr); // csak akkor, ha le kell kérdezni
				$db_utkozes++;
			    }
			    $_U = array();
                            for ($u=0; $u<count($UTKOZIK); $u++) {
				if ($UTKOZIK[$u]['tankorId'] == $ADAT['tankorId']) $_SESSION['alert'][] = 'info:utkozes::::::saját magával ütközik ezen a napon!:'.$dt;
				$_U[] = $UTKOZIK[$u]['tankorId'];
			    }             
			    $_RESZLET[] = $_DA[$_diakId]['diakNev'].' '.$_DA[$_diakId]['diakId'].':tankorId '.implode(', ',$_U).':dt '.$dt;
			}
		    }

	    }

	    // ütközés ellenőr vége
	}  // ha regisztralando, kulonben nem kell ellenőrizni az ütközéseket

	if ($ok===true) {
	    $q = "SELECT targyJel,osztalyJel FROM orarendiOraTankor WHERE tanarId=%u AND tankorId=%u";
	    $record = db_query($q, array('fv'=>'pluszOraFelvesz','modul'=>'naplo','result'=>'record', 'values'=> array($ADAT['tanarId'],$ADAT['tankorId'])), $lr);
	    if ($record===false || $record['targyJel']=='' || $record['osztalyJel']=='') {
		$record=array(); // reset
		$record['targyJel'] = 'm-'.$ADAT['tankorId'];
		$record['osztalyJel'] = 'm-'.$ADAT['tanarId'];
		$q = "REPLACE INTO orarendiOraTankor (tanarId,osztalyJel,targyJel,tankorId) VALUES (%u,'%s','%s',%u)";
		$r = db_query($q, array('fv'=>'pluszOraFelvesz/b','modul'=>'naplo','values'=> array($ADAT['tanarId'],$record['osztalyJel'],$record['targyJel'],$ADAT['tankorId'])), $lr);
		if ($r===false) $ok = false;
	    } // különben van megfelelő bejegyzésünk
	    if ($ADAT['teremId']=='') $ADAT['teremId'] = "null";
	    else $ADAT['teremId'] = intval($ADAT['teremId']);
	    if ($ok===true) {
		// Nem hibás, de jobb lenne a %s-t csak sztring-ekre használni, NULL-t valahogy másképp kezelni.
		$q = "INSERT INTO orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt) VALUES (%u,%u,%u,%u,'%s','%s',%s,'%s','%s')";
		$v = array($ADAT['het'],$ADAT['nap'],$ADAT['ora'],$ADAT['tanarId'],$record['osztalyJel'],$record['targyJel'],$ADAT['teremId'],$ADAT['tolDt'],$ADAT['igDt']);
		$r = db_query($q, array('fv'=>'pluszOraFelvesz/c','modul'=>'naplo','values'=>$v), $lr);
		if ($r===false) $ok = false;
	    }
	    if ($ok===true && $ADAT['haladasiModositando']!=0) {
		// ciklus $ADAT['tolDt'],$ADAT['igDt'] között, órarend
		$q = "SELECT DISTINCT dt,count(ora.oraId) AS db FROM nap LEFT JOIN ora USING (dt) WHERE nap.tipus='tanítási nap' AND orarendiHet=%u AND dt>='%s' AND dt<='%s' AND (WEEKDAY(dt)+1)=%u GROUP BY dt HAVING db>0 ORDER BY dt";
		$v = array($ADAT['het'],$ADAT['tolDt'],$ADAT['igDt'],$ADAT['nap']);
		$NAP = db_query($q, array('fv'=>'pluszOraFelvesz/ora','modul'=>'naplo','values'=>$v,'result'=>'indexed'), $lr);
		for ($i=0; $i<count($NAP); $i++) {
		    $_dt = $NAP[$i]['dt']; 
		    ujOraFelvesz(
            	      array('dt'=>$_dt,
                            'ora'=>$ADAT['ora'],
                            'ki'=> $ADAT['tanarId'],
                            'tipus'=>'normál',
			    'tankorId'=>$ADAT['tankorId'],
                            'eredet'=>'órarend',
                            'leiras'=>'',
                            'feladatTipusId'=>null,
                            'munkaido'=>'lekötött'),
        	    $lr); // az ütközést nem kezeljük külön. Ha van, akkor is tovább mehetünk
		}
	    }
	} else {
    	    if (is_array($_RESZLET) && count($_RESZLET)>0)
		$_SESSION['alert'][] = 'info:utkozes:'.$db_utkozes.':'.$ADAT['het'].':'.$ADAT['nap'].':'.$ADAT['ora'].':'.implode(' *** ',$_RESZLET);
	}
	if ($olr=='') {
	    if ($ok) db_commit($lr); else db_rollback($lr);
	    db_close($lr);
	} else {
	    return $ok;
	}
    }

    function orarendiOraLezar($ADAT) {

	$lr = db_connect('naplo', array('fv' => 'orarendiOraLezar'));
	db_start_trans($lr);

	    // Az érintett órarendi bejegyzés lekérdezése
	    $v = array($ADAT['het'], $ADAT['nap'], $ADAT['ora'], $ADAT['tanarId'], $ADAT['dt'], $ADAT['dt'], $ADAT['kulcsTolDt']);
	    $q = "SELECT * FROM orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND tolDt<='%s' AND '%s'<=igDt AND tolDt='%s'";
	    $ret = db_query($q, array('fv'=>'orarendiOraLezar', 'modul' => 'naplo', 'result' => 'record', 'values' => $v, 'rollback' => true), $lr);
	    if ($ret===false) { db_close($lr); return false; } 
	    if (count($ret)>0 && $ret['teremId'] == '') $ret['teremId'] = 'NULL';	    

	    // Az érintett bejegyzés törlése
	    $q = "DELETE FROM orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND tolDt<='%s' AND '%s'<=igDt AND tolDt='%s'";
	    $r = db_query($q, array('fv'=>'orarendiOraLezar', 'modul' => 'naplo', 'values' => $v, 'rollback' => true), $lr);
	    if ($r===false) { db_close($lr); return false; }

	    // A bejegyzés tolDt előtti részének visszarakása - ha van egyáltalán
	    if (strtotime($ret['tolDt']) < strtotime($ADAT['tolDt'])) {
		$igDt = date('Y-m-d', strtotime('-1 day', strtotime($ADAT['tolDt'])));
		$q = "INSERT INTO orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt) VALUES
		(".$ret['het'].",".$ret['nap'].",".$ret['ora'].",".$ret['tanarId'].",
		'".$ret['osztalyJel']."','".$ret['targyJel']."',".$ret['teremId'].",'".$ret['tolDt']."','".$igDt."')";
		$r = db_query($q, array('fv'=>'orarendiOraLezar', 'modul' => 'naplo', 'rollback' => true), $lr);
		if (!$r) { db_close($lr); return false; }
	    }

	    // A bejegyzés igDt utáni részének visszarakása - itt esetleg lehet ütközés!
	    if (strtotime($ret['igDt']) > strtotime($ADAT['igDt'])) {
		$tolDt = date('Y-m-d', strtotime('+1 day', strtotime($ADAT['igDt'])));
		$q = "INSERT INTO orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt) VALUES
		(".$ret['het'].",".$ret['nap'].",".$ret['ora'].",".$ret['tanarId'].",
		'".$ret['osztalyJel']."','".$ret['targyJel']."',".$ret['teremId'].",'".$tolDt."','".$ret['igDt']."')";
		$r = db_query($q, array('fv'=>'orarendiOraLezar', 'modul' => 'naplo', 'rollback' => true), $lr);
		if (!$r) { db_close($lr); return false; }
	    }

	db_commit($lr);
	db_close($lr);
	return true;

    }

?>
