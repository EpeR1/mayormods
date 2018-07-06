<?php

    // SZEREP beállítása
    if (__DIAK===true && __USERDIAKID>0) {
            $userId = __USERDIAKID;
            $userTipus='diak';
    } elseif (__DIAK===true && _SZULODIAKID>0) {
            $userId = __SZULODIAKID;
            $userTipus='szulo';
    } elseif (__TANAR === true) {
            $userId = __USERTANARID;
            $userTipus='tanar';
    } else {
            $userId = 0;
            $userTipus='';
    }
    define('__JEGYZETSZEREPTIPUS',$userTipus);
    define('__JEGYZETSZEREPID',$userId);

    //
    function getJegyzet($SET = array('tolDt'=>'','igDt'=>'','dt'=>'')) {

	// csak a saját jegyzeteim lehet lekérdezni
	if (__DIAK===true && __USERDIAKID>0) {
	    $userId = __USERDIAKID;
	    $userTipus='diak';
	} elseif (__DIAK===true && _SZULODIAKID>0) {
	    $userId = __SZULODIAKID;
	    $userTipus='szulo';
	} elseif (__TANAR === true) {
	    $userId = __USERTANARID;
	    $userTipus='tanar';
	} elseif (__NAPLOADMIN === true) { // ha nem tanár de naplóadmin (speciális eset :) )
	    $userId = 0;
	    $userTipus='admin';
	} else {
	    return false;
	}

	$tolDt = readVariable($SET['tolDt'], 'date');
        $igDt = readVariable($SET['igDt'], 'date');
        initTolIgDt(__TANEV, $tolDt, $igDt);
	// jogosultság szerint 0: privát, 1: speciális, 2: publikus (csak tanár állíthat be?)
	if (__NAPLOADMIN===true && $userId===0 && $userTipus==='admin') { // speciális esetben minden zárt és publikust láthatjuk (kivéve a privátot)
	    $q = "SELECT *,getNev(userId,userTipus) AS nev FROM jegyzet WHERE publikus IN (0,1,2) AND dt >= '%s' AND dt<= '%s' ORDER BY dt";
	    $v = array($tolDt,$igDt);
	} else {
	    $q = "(SELECT *,getNev(userId,userTipus) AS nev FROM jegyzet WHERE (userId=%u AND userTipus='%s') AND dt >= '%s' AND dt<= '%s' ORDER BY dt)";
	    if (count($SET['osztalyIdk'])>0) $q .= " UNION (SELECT jegyzet.*,getNev(userId,userTipus) AS nev FROM jegyzet LEFT JOIN jegyzetOsztaly USING (jegyzetId) WHERE osztalyId IN (". implode(',',$SET['osztalyIdk']) .") AND publikus=1 ORDER BY dt)";
	    if (count($SET['tankorIdk'])>0) $q .= " UNION (SELECT jegyzet.*,getNev(userId,userTipus) AS nev FROM jegyzet LEFT JOIN jegyzetTankor USING (jegyzetId) WHERE tankorId IN (". implode(',',$SET['tankorIdk']) .") AND publikus=1 ORDER BY dt)";
	    $q .= " UNION (SELECT *,getNev(userId,userTipus) AS nev FROM jegyzet WHERE publikus=2 AND dt >= '%s' AND dt<= '%s' ORDER BY dt)";
	    $v = array($userId,$userTipus,$tolDt,$igDt,$tolDt,$igDt);
	}
        $R = db_query($q, array('fv' => 'getJegyzet0', 'modul' => 'naplo', 'values' => $v, 'result'=>'indexed'), $lr);

	return $R;
    }

    //
    function getJegyzetAdat($jegyzetId) {

	global $_OSZTALYA;

	if (__DIAK===true && __USERDIAKID>0) {
	    $userId = __USERDIAKID;
	    $userTipus='diak';
	} elseif (__DIAK===true && _SZULODIAKID>0) {
	    $userId = __SZULODIAKID;
	    $userTipus='szulo';
	} elseif (__TANAR === true) {
	    $userId = __USERTANARID;
	    $userTipus='tanar';
	} elseif (__NAPLOADMIN === true) { // ha nem tanár de naplóadmin (speciális eset :) )
	    $userId = 0;
	    $userTipus='admin';
	} else {
	    return false;
	}


	// jogosultság ellenőr
        if (_POLICY!='public') {
            if (__JEGYZETSZEREPTIPUS == 'diak') {
                $JA['tankorok'] = getTankorByDiakId(__JEGYZETSZEREPID);
                $JA['osztalyok'] = getDiakOsztalya(__JEGYZETSZEREPID,array('tanev'=>$tanev,'tolDt'=>$dt,'igDt'=>$dt));
            } elseif (__JEGYZETSZEREPTIPUS == 'tanar') {
                $JA['tankorok'] = getTankorByTanarId(__JEGYZETSZEREPID);
                //if (is_array($_OSZTALYA) && count($_OSZTALYA)>0) $JA['osztalyok'] = getOsztalyok(null,array('osztalyIds'=>$_OSZTALYA));
                $JA['munkakozossegek'] = getMunkakozossegByTanarId(__JEGYZETSZEREPID, array('idonly'=>false));
            }
            for ($i=0; $i<count($JA['tankorok']); $i++) {$JA['tankorIdk'][] = $JA['tankorok'][$i]['tankorId'];}
            //for ($i=0; $i<count($JA['osztalyok']); $i++) {$JA['osztalyIdk'][] = $JA['osztalyok'][$i]['osztalyId'];}
	    $JA['osztalyIdk'] = $_OSZTALYA;
            for ($i=0; $i<count($JA['munkakozossegek']); $i++) {$JA['mkIdk'][] = $JA['munkakozossegek'][$i]['mkId'];}
        }
	//

	if (__NAPLOADMIN===true && $userId === 0 && $userTipus==='admin') {
	    $q = "SELECT *,getNev(userId,userTipus) AS nev FROM jegyzet WHERE jegyzetId=%u";
	    $v = array($jegyzetId);
	} else {
	    //$q = "SELECT * FROM jegyzet WHERE userId=%u AND userTipus='%s' AND jegyzetId=%u";
	    $q = "(SELECT *,getNev(userId,userTipus) AS nev FROM jegyzet WHERE (userId=%u AND userTipus='%s') AND jegyzetId=%u ORDER BY dt)";
	    if (count($JA['osztalyIdk'])>0) $q .= " UNION (SELECT jegyzet.*,getNev(userId,userTipus) AS nev FROM jegyzet LEFT JOIN jegyzetOsztaly USING (jegyzetId) WHERE osztalyId IN (". implode(',',$JA['osztalyIdk']) .") AND publikus=1  AND jegyzetId=%u ORDER BY dt)";
	    if (count($JA['tankorIdk'])>0) $q .= " UNION (SELECT jegyzet.*,getNev(userId,userTipus) AS nev FROM jegyzet LEFT JOIN jegyzetTankor USING (jegyzetId) WHERE tankorId IN (". implode(',',$JA['tankorIdk']) .") AND publikus=1  AND jegyzetId=%u  ORDER BY dt)";
	    $q .= " UNION (SELECT *,getNev(userId,userTipus) AS nev FROM jegyzet WHERE publikus=2 AND jegyzetId=%u ORDER BY dt)";
	    $v = array($userId,$userTipus,$jegyzetId,$jegyzetId,$jegyzetId,$jegyzetId);
	}
        $R = db_query($q, array('fv' => 'getJegyzet', 'modul' => 'naplo', 'values' => $v, 'result'=>'record'), $lr); // jegyzetId, tehát egy record

	for($i=0;$i<count($R);$i++) {
    	    $q = "SELECT tankorId FROM jegyzetTankor WHERE jegyzetId=%u";
	    $v = array($R['jegyzetId']);
    	    $R['tankorok'] = db_query($q, array('fv' => 'getJegyzet1', 'modul' => 'naplo', 'values' => $v, 'result'=>'idonly'), $lr);
	}
	for($i=0;$i<count($R);$i++) {
    	    $q = "SELECT osztalyId FROM jegyzetOsztaly WHERE jegyzetId=%u";
	    $v = array($R['jegyzetId']);
    	    $R['osztalyok'] = db_query($q, array('fv' => 'getJegyzet2', 'modul' => 'naplo', 'values' => $v, 'result'=>'idonly'), $lr);
	}
	for($i=0;$i<count($R);$i++) {
    	    $q = "SELECT mkId FROM jegyzetMunkakozosseg WHERE jegyzetId=%u";
	    $v = array($R['jegyzetId']);
    	    $R['munkakozossegek'] = db_query($q, array('fv' => 'getJegyzet3', 'modul' => 'naplo', 'values' => $v, 'result'=>'idonly'), $lr);
	}
	return $R;
    }

    function setJegyzetAdat($ADAT) {

	if (__DIAK===true && __USERDIAKID>0) {
	    $userId = __USERDIAKID;
	    $userTipus='diak';
	    $allowedPublikus = array(0,1);
	} elseif (__DIAK===true && _SZULODIAKID>0) {
	    $userId = __SZULODIAKID;
	    $userTipus='szulo';
	    $allowedPublikus = array(0,1);
	} elseif (__TANAR === true) {
	    $userId = __USERTANARID;
	    $userTipus='tanar';
	    $allowedPublikus = array(0,1,2);
	} else {
	    return false;
	}

	if ($ADAT['jegyzetId']<=0) { // insert
	    $q = "INSERT INTO jegyzet (jegyzetId) VALUES ('')";
	    $jegyzetId = db_query($q, array('fv' => 'setJegyzetAdat', 'modul' => 'naplo', 'values' => $v, 'result'=>'insert'));
	    $q = "UPDATE jegyzet SET userId=%u,userTipus='%s' WHERE jegyzetId=%u";
	    $v = array($userId,$userTipus,$jegyzetId);
	    db_query($q, array('fv' => 'setJegyzetAdat', 'modul' => 'naplo', 'values' => $v, 'result'=>'insert'));
	} else {
	    $jegyzetId = $ADAT['jegyzetId'];
	}

	$dt = readVariable($ADAT['dt'],'date');
	$dt = getTanitasiNapVissza(0,$dt);
	$publikus = readVariable($ADAT['publikus'],'id',0,$allowedPublikus);

	$q = "UPDATE jegyzet SET jegyzetLeiras='%s',publikus=%u,dt='%s' WHERE userId=%u AND userTipus='%s' AND jegyzetId=%u";
	$v = array(readVariable($ADAT['jegyzetLeiras'],'string'),$publikus,$dt,$userId,$userTipus,$jegyzetId);
	db_query($q, array('fv' => 'getJegyzet', 'modul' => 'naplo', 'values' => $v, 'result'=>'record'));

	$q = "DELETE FROM jegyzetTankor WHERE jegyzetId=%u";
	$v = array($jegyzetId);
	db_query($q, array('fv' => 'getJegyzet', 'modul' => 'naplo', 'values' => $v, 'result'=>'indexed'), $lr);

	for ($i=0; $i<count($ADAT['tankorId']); $i++) {
	    $q = "INSERT IGNORE INTO jegyzetTankor (jegyzetId,tankorId) VALUES (%u,%u)";
	    $v = array($jegyzetId,intval($ADAT['tankorId'][$i]));
	    db_query($q, array('fv' => 'setJegyzetAdat', 'modul' => 'naplo', 'values' => $v, 'result'=>'insert'));
	}

	$q = "DELETE FROM jegyzetOsztaly WHERE jegyzetId=%u";
	$v = array($jegyzetId);
	db_query($q, array('fv' => 'getJegyzet', 'modul' => 'naplo', 'values' => $v, 'result'=>'indexed'), $lr);

	for ($i=0; $i<count($ADAT['osztalyId']); $i++) {
	    $q = "INSERT IGNORE INTO jegyzetOsztaly (jegyzetId,osztalyId) VALUES (%u,%u)";
	    $v = array($jegyzetId,intval($ADAT['osztalyId'][$i]));
	    db_query($q, array('fv' => 'setJegyzetAdat', 'modul' => 'naplo', 'values' => $v, 'result'=>'insert'));
	}
	$q = "DELETE FROM jegyzetMunkakozosseg WHERE jegyzetId=%u";
	$v = array($jegyzetId);
	db_query($q, array('fv' => 'getJegyzet4', 'modul' => 'naplo', 'values' => $v, 'result'=>'indexed'), $lr);
	for ($i=0; $i<count($ADAT['mkId']); $i++) {
	    $q = "INSERT IGNORE INTO jegyzetMunkakozosseg (jegyzetId,mkId) VALUES (%u,%u)";
	    $v = array($jegyzetId,intval($ADAT['mkId'][$i]));
	    db_query($q, array('fv' => 'setJegyzetAdat', 'modul' => 'naplo', 'values' => $v, 'result'=>'insert'));
	}
	return $jegyzetId;

    }

    function delJegyzet($jegyzetId) {
	if (__DIAK===true && __USERDIAKID>0) {
	    $userId = __USERDIAKID;
	    $userTipus='diak';
	} elseif (__DIAK===true && _SZULODIAKID>0) {
	    $userId = __SZULODIAKID;
	    $userTipus='szulo';
	} elseif (__TANAR === true) {
	    $userId = __USERTANARID;
	    $userTipus='tanar';
	} else {
	    return false;
	}

	$q = "DELETE FROM jegyzet WHERE userId=%u AND userTipus='%s' AND jegyzetId=%u";
	$v = array($userId,$userTipus,$jegyzetId);
	db_query($q, array('fv' => 'jegyzetel', 'modul' => 'naplo', 'values' => $v, 'result'=>''));
	return;
    }

?>
