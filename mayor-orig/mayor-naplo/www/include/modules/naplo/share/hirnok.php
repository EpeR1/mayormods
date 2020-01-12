<?php

    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/dolgozat.php');
    require_once('include/modules/naplo/share/osztalyzatok.php');

    require_once('include/modules/naplo/uzeno/uzeno.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/szulo.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');

global $SZEMESZTER;

    function hirnokWrapper($SET) {
	global $_TANEV;
	$RESULT = array();
	if (isset($SET['diakId']) && !is_array($SET['diakId'])) $SET['diakId'] = array(0=>$SET['diakId']);
	if (isset($SET['tanarId']) && !is_array($SET['tanarId'])) $SET['tanarId'] = array(0=>$SET['tanarId']);
	// if (strtotime($SET['tolDt'])>strtotime(date('Y-m-d'))) $SET['tolDt'] = date('Y-m-d H:i:s');
	if (isset($SET['diakId']) && is_array($SET['diakId'])) {
	    for ($i=0;$i<count($SET['diakId']); $i++) {
		$_diakId= $SET['diakId'][$i];
		if ($SET['tolDtByUser']['diak'][$_diakId]!='') {
		    $_tolDt = $SET['tolDtByUser']['diak'][$_diakId];
		} elseif ($SET['tolDt']!='') {
		    $_tolDt = $SET['tolDt'];
		} else {
		    $_tolDt = $_TANEV['kezdesDt'].' 08:00:00';
		}
		$SUBSET = array('tolDt'=>$_tolDt,'diakId'=>$_diakId);
		$RESULT[] = array(
		    'hirnokFolyamAdatok' => array(
			'id'=>$_diakId,
			'tipus'=>'diak',
			'cn'=>getDiakNevById($_diakId),
			'adat'=>getDiakAdatById($_diakId)
		    ),
		    'hirnokFolyamUzenetek' => getHirnokFolyam($SUBSET)
		);
	    }
	}
	if (isset($SET['tanarId']) && is_array($SET['tanarId'])) {
	    for ($i=0;$i<count($SET['tanarId']); $i++) {
		$_tanarId= $SET['tanarId'][$i];
		if ($SET['tolDtByUser']['tanar'][$_tanarId]!='') {
		    $_tolDt = $SET['tolDtByUser']['tanar'][$_tanarId];
		} elseif ($SET['tolDt']!='') {
		    $_tolDt = $SET['tolDt'];
		} else {
		    $_tolDt = $_TANEV['kezdesDt'].' 08:00:00';
		}
		$SUBSET = array('tolDt'=>$_tolDt,'tanarId'=>$_tanarId);
		$RESULT[] = array(
		    'hirnokFolyamAdatok' => array(
			'id'=>$_tanarId,
			'tipus'=>'tanar',
			'cn'=>getTanarNevById($_tanarId),
			'adat'=>getTanarAdatById($_tanarId)
		    ),
		    'hirnokFolyamUzenetek' => getHirnokFolyam($SUBSET)
		);
	    }
	}
	return $RESULT;
    }


    function getHirnokFolyam($SET = array()) {

	global $_TANEV;
	$R = array();

	$TARGYADAT = array();
	$DIAKADAT = array();
	$TANARADAT = array();
	$TANKORADAT = array();
	$ORAADAT = array();

	if (__NAPLOADMIN===true) {
	    if ($SET['diakId']>0) $diakId=$SET['diakId'];
	    elseif ($SET['tanarId']>0) $tanarId=$SET['tanarId'];
	    elseif (__TANAR ===true) $tanarId = __USERTANARID;
	} else {
	    if (__DIAK===true) { // diák nézet
		$diakId = __USERDIAKID;
	    } elseif (__TANAR ===true) { // tanár nézet
		$tanarId = __USERTANARID;
	    }
	}

        // tankörök lekérdezése
        if (isset($diakId)) $TANKOROK = getTankorByDiakId($diakId, __TANEV);
        // elseif (isset($osztalyId)) $Tankorok = getTankorByOsztalyId($osztalyId, __TANEV);
        elseif (isset($tanarId)) $TANKOROK = getTankorByTanarId($tanarId, __TANEV);

        $tankorIds = array();
        for ($i = 0; $i < count($TANKOROK); $i++) $tankorIds[] = $TANKOROK[$i]['tankorId'];
	// DOLGOZATOK (leginkább a jövőben???)
        if (is_array($tankorIds)) {
            $_dolgozatok = getTankorDolgozatok($tankorIds, TRUE, $SET['tolDt'], $_TANEV['zarasDt']); // ennek a tömbnek a szerkezete elég fura...
	    for ($i=0; $i<count($_dolgozatok['dolgozatIds']); $i++) {
		$r = $_dolgozatok[$_dolgozatok['dolgozatIds'][$i]];
		if (strtotime($r['modositasDt'])>strtotime($SET['tolDt'])) {
		    $R[ strtotime($r['modositasDt']) ][] = array('hirnokTipus' => 'dolgozat',
			'dolgozatAdat' => $r
		    );
		} else {
		    // dump( 'nem aktuális a változtatás, már láttuk' );
		}
	    }
        }

	if (__DIAK===true || (__NAPLOADMIN===true && $diakId>0)) {
	    if (_OSZTALYZATOK_ELREJTESE !== true || time() > strtotime($_TANEV['szemeszter'][2]['zarasDt'])) { // --TODO
		// új zárójegyek, osztályzatok (diák esetén)
		$q = "SELECT * from zaroJegy WHERE diakId=%u AND modositasDt>='%s'";
		$v = array($diakId,$SET['tolDt']);
		$r = db_query($q, array('fv'=>'getHirnokFolyam/zaroJegy','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));
		for ($i=0; $i<count($r); $i++) {
		// infók: diakId, targyId
		if (!is_array($DIAKADAT[$r[$i]['diakId']])) $DIAKADAT[$r[$i]['diakId']] = getDiakAdatById($r[$i]['diakId']);
		if (!is_array($TARGYADAT[$r[$i]['targyId']])) $TARGYADAT[$r[$i]['targyId']] = getTargyById($r[$i]['targyId']);
		$R[strtotime($r[$i]['modositasDt'])][] = array('hirnokTipus'=>'zaroJegy',
		    'zaroJegyAdat'=>$r[$i],
		    'diakAdat' => $DIAKADAT[$r[$i]['diakId']],
		    'targyAdat' => $TARGYADAT[$r[$i]['targyId']]
		);
		}
	    } // -- elrejtésmarhaság
	    // új jegyek (diák esetén)
	    $q = "SELECT * from jegy WHERE diakId=%u AND modositasDt>='%s'";
	    $v = array($diakId,$SET['tolDt']);
	    $r = db_query($q, array('fv'=>'getHirnokFolyam/jegy','modul'=>'naplo','result'=>'indexed','values'=>$v));
	    if (is_array($r))
	    for ($i=0; $i<count($r); $i++) {
	    // infók: diakId,tankorId,oraId,dolgozatId
	    if (!is_array($DIAKADAT[$r[$i]['diakId']])) $DIAKADAT[$r[$i]['diakId']] = getDiakAdatById($r[$i]['diakId']);
	    if (!is_array($TANKORADAT[$r[$i]['tankorId']])) $TANKORADAT[$r[$i]['tankorId']] = getTankorAdat($r[$i]['tankorId']);
	    $_targyId = $TANKORADAT[$r[$i]['tankorId']][$r[$i]['tankorId']][0]['targyId'];
	    if (!is_array($TARGYADAT[$r[$i]['targyId']])) $TARGYADAT[ $_targyId ] = getTargyById($_targyId);
	    if (!is_array($ORAADAT[$r[$i]['oraId']])) $ORAADAT[$r[$i]['oraId']] = getOraAdatById($r[$i]['oraId']);
	    if (!is_array($DOLGOZATADAT[$r[$i]['dolgozatId']])) $DOLGOZATADAT[$r[$i]['dolgozatId']] = getDolgozatAdat($r[$i]['dolgozatId']);
	    $R[strtotime($r[$i]['modositasDt'])][] = array('hirnokTipus'=>'jegy',
		'jegyAdat'=>$r[$i],
		'diakAdat' => $DIAKADAT[$r[$i]['diakId']],
		'tankorAdat' => $TANKORADAT[$r[$i]['tankorId']][$r[$i]['tankorId']], // 0-1 ELSŐ-MÁSODIK FÉLÉV ADATAI
		'targyAdat' => $TARGYADAT[$_targyId],
		'oraAdat' => $ORAADAT[$r[$i]['oraId']],
		'dolgozatAdat' => $DOLGOZATADAT[$r[$i]['dolgozatId']],
	    );
	    }

	    // bejegyzés
	    $q = "SELECT * from bejegyzes LEFT JOIN ".__INTEZMENYDBNEV.".bejegyzesTipus USING (bejegyzesTipusId) 
		WHERE diakId=%u AND beirasDt>='%s'";
	    $v = array($diakId,$SET['tolDt']);
	    $r = db_query($q, array('fv'=>'getHirnokFolyam/bejegyzes','modul'=>'naplo','result'=>'indexed','values'=>$v));
	    for ($i=0; $i<count($r); $i++) {
	    // infók: diakId, targyId
	    if (!is_array($DIAKADAT[$r[$i]['diakId']])) $DIAKADAT[$r[$i]['diakId']] = getDiakAdatById($r[$i]['diakId']);
	    if (!is_array($TANARADAT[$r[$i]['tanarId']])) $TANARADAT[$r[$i]['tanarId']] = getTanarAdatById($r[$i]['tanarId']);
	    $R[strtotime($r[$i]['beirasDt'])][] = array('hirnokTipus'=>'bejegyzes',
		'bejegyzesAdat'=>$r[$i],
		'diakAdat' => $DIAKADAT[$r[$i]['diakId']],
		'tanarAdat' => $TANARADAT[$r[$i]['tanarId']][0],
	    );
	    }

	    // hiányzás
	    $q = "SELECT * from hianyzas WHERE diakId=%u AND modositasDt>='%s'";
	    $v = array($diakId,$SET['tolDt']);
	    $r = db_query($q, array('fv'=>'getHirnokFolyam/hianyzas','modul'=>'naplo','result'=>'indexed','values'=>$v));
	    for ($i=0; $i<count($r); $i++) {
	    // infók: diakId, targyId
	    if (!is_array($DIAKADAT[$r[$i]['diakId']])) $DIAKADAT[$r[$i]['diakId']] = getDiakAdatById($r[$i]['diakId']);
	    if (!is_array($ORAADAT[$r[$i]['oraId']])) $ORAADAT[$r[$i]['oraId']] = getOraAdatById($r[$i]['oraId']);
	    $R[strtotime($r[$i]['modositasDt'])][] = array('hirnokTipus'=>'hianyzas',
		'hianyzasAdat'=>$r[$i],
		'diakAdat' => $DIAKADAT[$r[$i]['diakId']],
		'oraAdat' => $ORAADAT[$r[$i]['oraId']],
	    );
	    }

	} // ha diák
	if (__TANAR === true || (__NAPLOADMIN===true && $tanarId>0)) {
    	    if (defined('__USERTANARID') && is_numeric(__USERTANARID)) {
        	$q = "SELECT COUNT(*) FROM ora WHERE ki=".__USERTANARID." AND dt <= CURDATE() AND (leiras IS NULL OR leiras='')";
        	$r = db_query($q, array('fv' => 'getBeirasiAdatok', 'modul' => 'naplo', 'result' => 'value'));
		$R[mktime()][] = array(
		    'hirnokTipus'=>'haladasiBeiratlan',
		    'db'=>$r
		);
		$q = "select * from idoszak where NOW() BETWEEN tolDt AND igDt ORDER BY tolDt";
        	$r = db_query($q, array('fv' => 'getIdoszakAktiv', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));
		for ($i=0; $i<count($r); $i++) {
		    $R[mktime()][] = array(
		    'hirnokTipus'=>'idoszak',
		    'idoszakAdat'=>$r[$i]
		    );
		}
	    }
	    if (count($tankorIds)>0) {
		// haladási óra - helyettesítőknek!
		$q = "SELECT *,getNev(ki,'tanar') AS kiCn,getNev(kit,'tanar') AS kitCn, getNev(tankorId,'tankor') AS tankorCn from ora WHERE tankorId NOT IN  (".implode(',',$tankorIds).") AND ki=%u AND modositasDt>='%s'";
		$v = array($tanarId,$SET['tolDt']);
		$r = db_query($q, array('fv'=>'getHirnokFolyam/haladasi','modul'=>'naplo','result'=>'indexed','values'=>$v));
		for ($i=0; $i<count($r); $i++) {
		    $R[strtotime($r[$i]['modositasDt'])][] = array('hirnokTipus'=>'haladasiOra',
			'oraAdat'=>$r[$i],
		    );
		}
	    }
	}
	// timestamp szerint asszociatív
	// $R[strtotime($SET['tolDt'])][] = array('cim' => 'ELSŐ', 'txt'=>$SET['tolDt']);

	// haladási óra
	if (count($tankorIds)>0) {
	    $q = "SELECT *,getNev(ki,'tanar') AS kiCn,getNev(kit,'tanar') AS kitCn, getNev(tankorId,'tankor') AS tankorCn from ora WHERE tankorId IN  (".implode(',',$tankorIds).") AND modositasDt>='%s'";
	    $v = array($SET['tolDt']);
	    $r = db_query($q, array('fv'=>'getHirnokFolyam/haladasi2','modul'=>'naplo','result'=>'indexed','values'=>$v));
	    for ($i=0; $i<count($r); $i++) {
	    $R[strtotime($r[$i]['modositasDt'])][] = array('hirnokTipus'=>'haladasiOra',
		'oraAdat'=>$r[$i],
	    );
	    }
	}

	// utolsó óra
	if (__TANAR === true || (__NAPLOADMIN===true && $tanarId>0)) {
    	    if (defined('__USERTANARID') && is_numeric(__USERTANARID)) { // cron esetén nincs ilyen
		$q = "SELECT *,getOraTolTime(ora.oraId) AS tolTime, getOraIgTime(ora.oraId) AS igTime FROM (SELECT dt,max(ora) AS utolsooraateremben,teremId,terem.leiras AS teremNev FROM ora LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId) WHERE dt=curdate() AND teremId IS NOT NULL GROUP BY teremId) AS x LEFT JOIN ora ON (ora.dt = x.dt AND x.utolsooraateremben = ora.ora AND x.teremId = ora.teremId) WHERE ora.ki=%u";
		$v = array(__USERTANARID);
		$r = db_query($q, array('fv'=>'getHirnokFolyam/haladasi2','modul'=>'naplo','result'=>'indexed','values'=>$v));
		for ($i=0; $i<count($r); $i++) {
		    $R[mktime()][] = array(
		    'hirnokTipus'=>'utolsoora',
		    'adat'=>$r[$i]
		    );
		}
	    }
	}

	// Üzenő
//	 /* 20170418
	initSzerep();
	$_SET['tanev'] = __TANEV;
//	$_SET['limits'] = array('limit'=>10, 'mutato'=>1, 'pointer'=>1);
// --TODO NOTE-- EZ KELL!!!!!!
	$_SET['filter'][] = 'dt>="'.$SET['tolDt'].'"';
	$_SET['ignoreAdmin'] = true;
	$r = getUzenoUzenetek($_SET);

	for ($i=0; $i<count($r); $i++) {
	    // üzenő címzett/feladó kitalálós
	    $feladoNev=$cimzettNev = '';
	    if ($r[$i]['feladoTipus'] == 'diak') {
		if (!is_array($DIAKADAT[$r[$i]['feladoIdId']])) $DIAKADAT[$r[$i]['feladoId']] = getDiakAdatById($r[$i]['feladoId']);
		$feladoNev = $DIAKADAT[$r[$i]['feladoId']]['diakNev'];
	    }
	    if ($r[$i]['cimzettTipus'] == 'diak') {
		if (!is_array($DIAKADAT[$r[$i]['cimzettId']])) $DIAKADAT[$r[$i]['cimzettId']] = getDiakAdatById($r[$i]['cimzettId']);
		$cimzettNev = $DIAKADAT[$r[$i]['cimzettId']]['diakNev'];
	    }
	    if ($r[$i]['feladoTipus'] == 'tanar') {
		if (!is_array($TANARADAT[$r[$i]['feladoId']])) $TANARADAT[$r[$i]['feladoId']] = array_pop(getTanarAdatById($r[$i]['feladoId']));
		$feladoNev = $TANARADAT[$r[$i]['feladoId']]['tanarNev'];
	    }
	    if ($r[$i]['cimzettTipus'] == 'tanar') {
		if (!is_array($TANARADAT[$r[$i]['cimzettId']])) $TANARADAT[$r[$i]['cimzettId']] = array_pop(getTanarAdatById($r[$i]['cimzettId']));
		$cimzettNev = $TANARADAT[$r[$i]['cimzettId']]['tanarNev'];
	    }
	    if ($r[$i]['feladoTipus'] == 'szulo') {
		if (!is_array($SZULOADAT[$r[$i]['feladoId']])) $SZULOADAT[$r[$i]['feladoId']] = (getSzuloNevById($r[$i]['feladoId']));
		$feladoNev = $SZULOADAT[$r[$i]['feladoId']];
	    }
	    if ($r[$i]['cimzettTipus'] == 'szulo') {
		if (!is_array($SZULOADAT[$r[$i]['cimzettId']])) $SZULOADAT[$r[$i]['cimzettId']] = (getSzuloNevById($r[$i]['cimzettId']));
		$cimzettNev = $SZULOADAT[$r[$i]['cimzettId']];
	    }
	    if ($r[$i]['cimzettTipus'] == 'munkakozosseg') {
		if (!is_array($MUNKAKOZOSSEGADAT[$r[$i]['munkakozossegId']])) $MUNKAKOZOSSEGADAT[$r[$i]['cimzettId']] = (getMunkakozossegNevById($r[$i]['cimzettId']));
		$cimzettNev = $MUNKAKOZOSSEGADAT[$r[$i]['cimzettId']];
	    }
	    if (in_array($r[$i]['cimzettTipus'],array('tankor','tankorSzulo'))) {
		if (!is_array($TANKORADAT[$r[$i]['cimzettId']])) $TANKORADAT[$r[$i]['cimzettId']] = getTankorNevById($r[$i]['cimzettId']);
		$cimzettNev = $TANKORADAT[$r[$i]['cimzettId']];
	    }
	    if (in_array($r[$i]['cimzettTipus'],array('osztaly','osztalySzulo','osztalyTanar'))) {
		if (!is_array($OSZTALYADAT[$r[$i]['cimzettId']])) $OSZTALYADAT[$r[$i]['cimzettId']] = getOsztalyNevById($r[$i]['cimzettId']);
		$cimzettNev = $OSZTALYADAT[$r[$i]['cimzettId']];
	    }

	    // cimzett felado vége
	    $R[strtotime($r[$i]['dt'])][] = array('hirnokTipus'=>'uzeno',
		'uzenoAdat' => $r[$i],
		'feladoNev' => $feladoNev,
		'cimzettNev' => $cimzettNev,
	    ); 
	}
//	 20170418 */

	reset($R);
	krsort($R);

	return $R;
    }


        function getHirnokFeliratkozasok($mind=false) {

	    if ($mind===true) { // hirek feliratkozáshoz!
		if (__NAPLOADMIN === true) {
        	    $q = "SELECT * FROM hirnokFeliratkozas ORDER BY email";
        	    $values = array();
		} else {
        	    $q = "SELECT * FROM hirnokFeliratkozas WHERE userAccount='%s' AND policy='%s'";
        	    $values = array(_USERACCOUNT,_POLICY);
		}
	    } else {
        	$q = "SELECT naploTipus,naploId,email FROM hirnokFeliratkozas WHERE userAccount='%s' AND policy='%s'";
        	$values = array(_USERACCOUNT,_POLICY);
	    }
            $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$values));

	    if ($mind===true) { // hirnokFeliratkozashoz
		return $r;
	    } else { // egyébként
        	for ($i=0; $i<count($r); $i++) {
            	    $result[$r[$i]['naploTipus']][] = $r[$i]['naploId'];
        	}
	    }
	    return $result;
        }

	function getHirnokEmail() {return getFutarEmail;}
	function getFutarEmail() {

	    if ( _POLICY=='parent' && defined('__PARENTDIAKID') ) {
		$naploId = __USERDIAKID;
		$naploTipus='diak';
		$q = "SELECT email FROM `szulo` WHERE szuloId=%u";
    		$values = array(__USERSZULOID);
        	$email = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'record','values'=>$values));
	    } elseif (__DIAK===true) {
		$naploId = __USERDIAKID;
		$naploTipus='diak';
		$q = "SELECT email FROM `diak` WHERE diakId=%u";
    		$values = array($naploId);
        	$email = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'record','values'=>$values));
    	    } elseif (__TANAR ===true) { // tanár nézet
                $naploId = __USERTANARID;
		$naploTipus='tanar';
		$q = "SELECT email FROM `tanar` WHERE tanarId=%u";
    		$values = array($naploId);
        	$email = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'record','values'=>$values));
    	    } else return false;

    	    $q = "SELECT email FROM `hirnokFeliratkozas` WHERE userAccount='%s' AND policy='%s' AND naploTipus='%s' AND naploId=%u";
    	    $values = array(_USERACCOUNT,_POLICY,$naploTipus,$naploId);
            $futarEmail = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'record','values'=>$values));
	    return array('futar'=>$futarEmail,'hirnok'=>$futarEmail,'naplo'=>$email);
	}

	function addHirnokFeliratkozas($ADAT) {

	    if (intval($ADAT['naploId'])==0) return false;
	    $q = "SELECT max(utolsoEmailDt) FROM hirnokFeliratkozas WHERE naploId=%u AND naploTipus='%s'";
	    $v = array(intval($ADAT['naploId']), $ADAT['naploTipus']);
	    $utolsoEmailDt = db_query($q, array('fv'=>'addHirnokFeliratkozas/get', 'modul'=>'naplo_intezmeny', 'values'=>$v, 'result'=>'value'));

	    if ($utolsoEmailDt=='0000-00-00 00:00:00') {
		$q = "INSERT INTO hirnokFeliratkozas (naploId, naploTipus, userAccount, policy, email, feliratkozasDt, utolsoEmailDt, megtekintesDt) 
		    VALUES (%u,'%s','%s','%s','%s',NOW(),null,null)";
		$v = array(intval($ADAT['naploId']), $ADAT['naploTipus'], _USERACCOUNT, _POLICY, $ADAT['email']);
	    } else {
		$q = "INSERT INTO hirnokFeliratkozas (naploId, naploTipus, userAccount, policy, email, feliratkozasDt, utolsoEmailDt, megtekintesDt) 
		    VALUES (%u,'%s','%s','%s','%s',NOW(),'%s',null)";
		$v = array(intval($ADAT['naploId']), $ADAT['naploTipus'], _USERACCOUNT, _POLICY, $ADAT['email'], $utolsoEmailDt);
	    }
	    return db_query($q, array('fv'=>'addHirnokFeliratkozas/set', 'modul'=>'naplo_intezmeny', 'values'=>$v, 'result'=>'insert'));

	}


	function delHirnokFeliratkozas($ADAT) {

	    // if (!is_array($ADAT['hirnokFeliratkozas'])) $X = array($ADAT['hirnokFeliratkozas']);
	    $q = "DELETE FROM hirnokFeliratkozas WHERE hirnokFeliratkozasId = %u";
	    $v = array(intval($ADAT['hirnokFeliratkozasId']));
	    return db_query($q, array('fv'=>'delHirnokFeliratkozas', 'modul'=>'naplo_intezmeny', 'values'=>$v, 'result'=>'delete'));
	
	}

?>