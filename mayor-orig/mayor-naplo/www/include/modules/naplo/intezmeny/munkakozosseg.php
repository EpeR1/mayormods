<?php

    // Az általános getter függvények a share-ben vannak (munkakozosseg, targy)

    function ujMunkakozosseg($leiras, $mkVezId='') {


        $lr = db_connect('naplo_intezmeny', array('fv' => 'ujMunkakozosseg'));
	if (!$lr) return false;

	$result = false; // sikerült-e?

	// Van-e már ilyen munkaközösség?
	$q = "SELECT COUNT(mkId) FROM munkakozosseg WHERE leiras='%s'";
        $num = db_query($q, array('fv' => 'ujMunkakozosseg', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($leiras)), $lr);

	if ($num == 0) {
	    $v = array($leiras);
	    if ($mkVezId == '') {
		$MKVEZID = 'NULL';
	    } else {
		$MKVEZID = '%u';
		$v[] = $mkVezId;
	    }
	    $q = "INSERT INTO munkakozosseg (leiras,mkVezId) VALUES ('%s',$MKVEZID)";
    	    $result = db_query($q, array('fv' => 'ujMunkakozosseg', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);

	} else {
	    // már van ilyen munkaközösség...
	    $_SESSION['alert'][] = 'massege:wrong_data:duplikált munkaközösség leírás (név)';
	}
        db_close($lr);

	return $result;

    }

    function modMunkakozosseg($mkId,$leiras,$mkVezId,$MKUJTAGOK,$MKTORLENDOTAGOK) {


        $lr = db_connect('naplo_intezmeny', array('fv' => 'modMunkakozosseg'));

	if (!$lr) return false;
	if ($mkId=='') { $_SESSION['alert'][] = 'message::no mkId'; return false; }

	$result = false; // sikerült-e?

	// Van-e már ilyen munkaközösség?
	$q = "SELECT COUNT(mkId) FROM munkakozosseg WHERE mkId=%u";
        $num = db_query($q, array('fv' => 'modMunkakozosseg', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => array($mkId)), $lr);

	if ($num != 0) {
	    if ($mkVezId == '') {
		$q = "UPDATE munkakozosseg SET leiras='%s',mkVezId=NULL WHERE mkId=%u";
		$v = array($leiras, $mkId);
	    } else {
		$q = "UPDATE munkakozosseg SET leiras='%s',mkVezId=%u WHERE mkId=%u";
		$v = array($leiras, $mkVezId, $mkId);
	    }
    	    $result = db_query($q, array('fv' => 'modMunkakozosseg', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
	} else {
	    // már van ilyen munkaközösség...
	    $_SESSION['alert'][] = 'massege:wrong_data:mkId='.$mkId;
	}
	
	if ($result) {
	    if (is_array($MKUJTAGOK) && count($MKUJTAGOK)>0) {
	    //mkTanar[mkId,tanarId]
		for($i=0; $i<count($MKUJTAGOK); $i++) {
		    $_tanarId = $MKUJTAGOK[$i];
		    $q = "REPLACE INTO mkTanar (mkId,tanarId) VALUES (%u, %u)";
		    db_query($q, array('fv' => 'modMunkakozosseg', 'modul' => 'naplo_intezmeny', 'values' => array($mkId,$_tanarId)), $lr);
		}
	    }
	    if (is_array($MKTORLENDOTAGOK) && count($MKTORLENDOTAGOK)>0) {
		$q = "DELETE FROM mkTanar WHERE mkId=%u AND tanarId IN (".implode(',', array_fill(0, count($MKTORLENDOTAGOK), '%u')).")";
		db_query($q, array('fv' => 'modMunkakozosseg', 'modul' => 'naplo_intezmeny', 'values' => mayor_array_join(array($mkId),$MKTORLENDOTAGOK)), $lr);
	    }
	}
	
	
        db_close($lr);

	return $result;

    }




    function ujTargy($ADAT) {

	$leiras=$ADAT['leiras'];
	$mkId=$ADAT['mkId'];
	$targyJelleg=$ADAT['targyJelleg'];	
	$kirTargyId=$ADAT['kirTargyId'];	
	$kretaTargyNev=$ADAT['kretaTargyNev'];	

	if ($leiras=='') {
	    $_SESSION['alert'][] = 'message:UI:empty field';
	    return false;
	}

	if (is_numeric($kirTargyId)) {
		$q = "INSERT INTO targy (targyNev,mkId,targyJelleg,kirTargyId) VALUES ('%s',%u,'%s',%u)";
		$v = array($leiras,$mkId,$targyJelleg,	$kretaTargyNev, $kirTargyId);
	} else {
		$q = "INSERT INTO targy (targyNev,mkId,targyJelleg) VALUES ('%s',%u,'%s')";
		$v = array($leiras,$mkId,$targyJelleg,	$kretaTargyNev);	
	}
	$result = db_query($q,array('modul'=>'naplo_intezmeny', 'fv'=>'ujTargy','result'=>'insert', 'detailed'=>false, 'debug'=>false, 'values'=>$v));

	return $result;

    }

    function targyModosit($ADAT) {
	$q = "UPDATE targy SET targyJelleg='%s',zaroKovetelmeny='%s',evkoziKovetelmeny='%s',targyRovidNev='%s'";
	$v = array($ADAT['targyJelleg'],$ADAT['zaroKovetelmeny'],$ADAT['evkoziKovetelmeny'],$ADAT['targyRovidNev']);
	if (is_numeric($ADAT['kirTargyId'])) {
	    $q .= ",kirTargyId=%u";
	    array_push($v,$ADAT['kirTargyId']);
	}
	if ($ADAT['kretaTargyNev']!='') {
	    $q .= ",kretaTargyNev='%s'";
	    array_push($v,$ADAT['kretaTargyNev']);
	} else {
	    $q .= ",kretaTargyNev=NULL";
	}
	$q .=" WHERE targyId=%u";
	array_push($v, $ADAT['targyId']);	
	return db_query($q,array( 'modul'=>'naplo_intezmeny', 'fv'=>'targyModosit', 'detailed'=>false, 'values'=>$v));
    }

    function targyTorol($targyId,$mkId) {

	    $q = "DELETE FROM targy WHERE targyId=%u AND mkId=%u";
    	    return db_query($q, array('fv' => 'targyTorol', 'modul' => 'naplo_intezmeny', 'values' => array($targyId, $mkId)), $lr);

    }


    function munkakozossegTorol($mkId) {

	    $q = "DELETE FROM munkakozosseg WHERE mkId=%u";
    	    return db_query($q, array('fv' => 'munkakozossegTorol', 'modul' => 'naplo_intezmeny', 'values' => array($mkId)), $lr);

    }

    function targyBeolvasztas($ADAT) {
    /**
     * Elvárt paraméterek: $ADAt['targyId'], $ADAT['befogadoTargyId'], $ADAT['tankorJeloles'] (lehet üres)
    **/

	$lr = db_connect('naplo_intezmeny');
	db_start_trans($lr);

	// A befogadó tárgy adatai
	$befogadoTargy = getTargyById($ADAT['befogadoTargyId']);
	$targyAdat = getTargyById($ADAT['targyId']);
	// A tárgyhoz tartozó tankörök lekérdezése
	$q = "SELECT tankorId FROM tankor WHERE targyId=%u";
	$tankorIds = db_query($q, array('fv'=>'targyBeolvasztas/tankorok','result'=>'idonly','values'=>array($ADAT['targyId'])), $lr);
	if (is_array($tankorIds) && count($tankorIds)>0) /*foreach ($tankorIds as $tankorId)*/ {
	    /* tárgyhoz tartozó tankörök átnevezése */
	    if (isset($ADAT['tankorJeloles'])) {
		$q = "UPDATE tankorSzemeszter SET tankorNev=CONCAT(LEFT(tankorNev,LOCATE('%s',tankorNev)-1),'%s',' ','%s') 
			WHERE tankorId IN (".implode(',', array_fill(0,count($tankorIds),'%u')).")";
		$v = mayor_array_join(array($targyAdat['targyNev'], $befogadoTargy['targyNev'], $ADAT['tankorJeloles']), $tankorIds);
	    } else {
		$q = "UPDATE tankorSzemeszter SET tankorNev=REPLACE(tankorNev,'%s','%s') 
			WHERE tankorId IN (".implode(',', array_fill(0,count($tankorIds),'%u')).")";
		$v = mayor_array_join(array($targyAdat['targyNev'], $befogadoTargy['targyNev']), $tankorIds);
	    }
	    $r = db_query($q, array('fv'=>'targyBeolvasztas/tankör-átnevezés','values'=>$v), $lr);
	    if (!$r) { db_rollback($lr); db_close($lr); return false; }
	    /* tárgyhoz tartozó tannkörök áthelyezése */
	    $q = "UPDATE tankor SET targyId=%u WHERE targyId=%u";
	    $r = db_query($q, array('fv'=>'targyBeolvasztas/tankör-tárgy','values'=>array($ADAT['befogadoTargyId'], $ADAT['targyId'])), $lr);
	    if (!$r) { db_rollback($lr); db_close($lr); return false; }
	}

	/* tanév adatbázisok lekérdezése */
	$q = "SHOW DATABASES LIKE 'naplo_".__INTEZMENY."%'";
	$dbs = db_query($q, array('fv'=>'targyBeolvasztas/dbs','result'=>'idonly'), $lr);
	if (!$dbs) { db_rollback($lr); db_close($lr); return false; }
	if (is_array($dbs) && count($dbs) > 0) foreach ($dbs as $db) {
	    /* tanév adatbázis tábláinak lekérdezése */
	    $q = "SHOW TABLES FROM $db";
	    $tables = db_query($q, array('fv'=>'targyBeolvasztas/tables','result'=>'idonly'), $lr);
	    if (is_array($tables) && count($tables)>0) foreach ($tables as $table) {
		if ($table == 'targySorszam') {
		    /* törlendő: targySorszam, */
		    $q = "DELETE FROM `$db`.`targySorszam` WHERE targyId=%u";
		    $r = db_query($q, array('fv'=>'targyBeolvasztas/targySorszam','values'=>array($ADAT['targyId'])), $lr);
		    if (!$r) { db_rollback($lr); db_close($lr); return false; }
		} else {
		    /* Tábla tartalmaz-e targyId mezőt... */
		    $q = "SHOW FIELDS FROM `$db`.`$table` LIKE 'targyId'";
		    $ret = db_query($q, array('fv'=>'targyBeolvasztas/table-targyId','result'=>'idonly'), $lr);
		    if (is_array($ret) && count($ret)>0) {
			/* ... ha igen: targyId módosítás */
			$q = "UPDATE `$db`.`$table` SET targyId=%u WHERE targyId=%u";
			$v = array($ADAT['befogadoTargyId'], $ADAT['targyId']);
			$r = db_query($q, array('fv'=>'targyBeolvasztas/table','values'=>$v), $lr);
			if (!$r) { db_rollback($lr); db_close($lr); return false; }
		    }
		}
	    }
	}
	/* intézményi adattáblák lekérdezése */
	$q = "SHOW TABLES";
	$tables = db_query($q, array('fv'=>'targyBeolvasztas/i-tables','result'=>'idonly'), $lr);
	if (is_array($tables) && count($tables)>0) foreach ($tables as $table) {
	    if ($table != 'targy') {
		/* Tábla tartalmaz-e targyId mezőt... */
		$q = "SHOW FIELDS FROM `$table` LIKE 'targyId'";
		$ret = db_query($q, array('fv'=>'targyBeolvasztas/i-table-targyId','result'=>'idonly'), $lr);
		if (is_array($ret) && count($ret)>0) {
		    /* ... ha igen: targyId módosítás */
		    $q = "UPDATE IGNORE `$table` SET targyId=%u WHERE targyId=%u";
		    $v = array($ADAT['befogadoTargyId'], $ADAT['targyId']);
		    $r = db_query($q, array('fv'=>'targyBeolvasztas/i-table','values'=>$v), $lr);
		    if (!$r) { db_rollback($lr); db_close($lr); return false; }
		}
	    } // != tárgy
	}

	/* targy törlése */
	$q = "DELETE FROM targy WHERE targyId=%u";
	$r = db_query($q, array('fv'=>'targyBeolvasztas/delete','values'=>array($ADAT['targyId'])), $lr);
	if (!$r) { db_rollback($lr); db_close($lr); return false; }
    
	db_commit($lr);
	db_close($lr);
	return true;

    }

    function targyMkValtas($ADAT) {
    /**
     * Elvárt paraméterek: $ADAt['targyId'], $ADAT['befogadoMkId']
    **/
	$lr = db_connect('naplo_intezmeny');
	db_start_trans($lr);
	// Az adott tárgy tanköreinek jelenlegi tanárai között van-e az új munkaközösségben nem szereplő
	$q = "SELECT DISTINCT tanarId FROM tankor LEFT JOIN tankorTanar USING (tankorId) 
		WHERE targyId=%u AND beDt<=CURDATE() AND (kiDt IS NULL OR kiDt>=CURDATE())
		AND tanarId NOT IN (SELECT tanarId FROM mkTanar WHERE mkId=%u)";
	$tanarIds = db_query($q, array('fv'=>'targyMkValtas/tanarIds','result'=>'idonly','values'=>array($ADAT['targyId'], $ADAT['befogadoMkId'])), $lr);
	if (is_array($tanarIds) && count($tanarIds)>0) {
	    db_rollback($lr);
	    db_close($lr);
	    $_SESSION['alert'][] = 'message:wrong_data:targyMkValtas:Van az új munkaközösségbe nem tartozó érintett tanár!:'.implode(',',$tanarIds);
	    return false;
	}
	// munkaközösség váltás
	$q = "UPDATE targy SET mkId=%u WHERE targyId=%u";
	$r = db_query($q, array('fv'=>'targyMkValtas/mkId','values'=>array($ADAT['befogadoMkId'], $ADAT['targyId'])), $lr);
	if (!$r) { db_rollback($lr); db_close($lr); return false; }

	db_commit($lr);
	db_close($lr);
	return true;

    }

    function targyAtnevezes($ADAT) {
    /**
     * Elvárt paraméterek: $ADAt['targyId'], $ADAT['ujTargyNev']
    **/
    
	$lr = db_connect('naplo_intezmeny');
	db_start_trans($lr);

	$targyAdat = getTargyById($ADAT['targyId']);
	// tankörnév módosítás
	$q = "UPDATE tankorSzemeszter SET tankorNev=REPLACE(tankorNev,'%s','%s') 
		WHERE tankorId IN (SELECT tankorId FROM tankor WHERE targyId=%u)";
	$v = array($targyAdat['targyNev'], $ADAT['ujTargyNev'], $ADAT['targyId']);
	$r = db_query($q, array('fv'=>'targyAtnevezes/tankor','values'=>$v), $lr);
	if (!$r) { db_rollback($lr); db_close($lr); return false; }
	// tárgy átnevezés
	$q = "UPDATE targy SET targyNev='%s' WHERE targyId=%u";
	$v = array($ADAT['ujTargyNev'], $ADAT['targyId']);
	$r = db_query($q, array('fv'=>'targyAtnevezes/targy','values'=>$v), $lr);
	if (!$r) { db_rollback($lr); db_close($lr); return false; }

	db_commit($lr);
	db_close($lr);
	return true;

    }

?>