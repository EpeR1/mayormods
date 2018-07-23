<?php

    function intezmenyDbNev($intezmeny) { return 'intezmeny_'.$intezmeny; }
    function tanevDbNev($intezmeny, $tanev) { return 'naplo_'.$intezmeny.'_'.$tanev; }

    function getTanevAdat($tanev = __TANEV, $olr = null) {
    	
        $q = "SELECT * FROM szemeszter WHERE tanev = %u ORDER BY szemeszter";
	$ret['szemeszter'] = db_query($q, array(
	    'fv' => 'getTanevAdat', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'szemeszter', 'values' => array($tanev)
	), $olr);

        if (!is_array($ret['szemeszter'])) return false;

	// A legkorábbi kezdés és legkésőbbi zárás keresése...
        $kezdesDt = '2030-01-01'; $zarasDt = '1980-01-01';
        $kezdes = strtotime($kezdesDt); $zaras = strtotime($zarasDt);
	foreach ($ret['szemeszter'] as $szemeszter => $A) {
        	
        	if ($kezdes > strtotime($A['kezdesDt'])) {
        		$kezdesDt = $A['kezdesDt'];
        		$kezdes = strtotime($kezdesDt);
        	}
        	if ($zaras < strtotime($A['zarasDt'])) {
        		$zarasDt = $A['zarasDt'];
        		$zaras = strtotime($zarasDt);
        	}
        	$ret['statusz'] = $A['statusz'];
        }
        $ret['kezdesDt'] = $kezdesDt; $ret['zarasDt'] = $zarasDt; $ret['tanev'] = $tanev;
	$q = "SELECT MAX(zarasDt) FROM szemeszter WHERE zarasDt<'%s'";
	$ret['elozoZarasDt'] = db_query($q, array('fv'=>'getTanevAdat/elozo','modul'=>'naplo_intezmeny','result'=>'value','values'=>array($ret['kezdesDt'])));
	$q = "SELECT MIN(kezdesDt) FROM szemeszter WHERE kezdesDt>'%s'";
	$ret['kovetkezoKezdesDt'] = db_query($q, array('fv'=>'getTanevAdat/kovetkezo','modul'=>'naplo_intezmeny','result'=>'value','values'=>array($ret['zarasDt'])));
       
        return $ret;
        
    }
    
    function initIntezmeny($DATA) {

	global $MYSQL_DATA;

	$intezmeny = $DATA['intezmeny'];
	$MYSQL_DATA['naplo_intezmeny'] = $MYSQL_DATA['naplo_base'];
    	$MYSQL_DATA['naplo_intezmeny']['db']= intezmenyDbNev($intezmeny);

	define('__INTEZMENY', $intezmeny);
	define('__INTEZMENYDBNEV', intezmenyDbNev(__INTEZMENY));

	if (isset($DATA['telephelyId'])) {
	    define('__TELEPHELYID',$DATA['telephelyId']);
	}

	$num = checkDiakStatusz();
	if ($num != 0) $_SESSION['alert'][] = 'info:success:checkDiakStatusz:helyreállított rekordok száma='.$num;

	$q = "SELECT OMKod FROM intezmeny WHERE rovidNev='%s'";
	define('__OMKOD',db_query($q, array('fv' => 'initIntezmeny', 'modul' => 'naplo_base', 'result' => 'value','values'=>array($intezmeny))));

    }

    function initTanev($intezmeny, $tanev) {

	global $MYSQL_DATA, $_TANEV;

    	$MYSQL_DATA['naplo'] = $MYSQL_DATA['naplo_base'];
    	$MYSQL_DATA['naplo']['db']= tanevDbNev($intezmeny, $tanev);

	define('__TANEV', $tanev);
	define('__TANEVDBNEV', tanevDbNev(__INTEZMENY, __TANEV));
	$_TANEV = getTanevAdat();
	// A kezdes- és zarasDt a szemeszter táblában DATE típusú, így az összehasonlítás korrekt
        $date = date('Y-m-d');
        define('__FOLYO_TANEV',(
            $_TANEV['kezdesDt'] <= $date
            && $date <= $_TANEV['zarasDt']
        ));

	if (file_exists($file = _CONFIGDIR."/module-naplo/config-$intezmeny.php")) require_once($file);
	initDefaults();

	if ($_TANEV['statusz'] == 'aktív') {
	    checkNaploStatus();
	    if (__FOLYO_TANEV === true) 
		if (__MUNKATERV_OK && __ORAREND_OK && __TANKOROK_OK) checkNaplo(date('Y-m-d'));
		else $_SESSION['alert'][]= 'info:checkNaploFailed:Tanév:'.($_TANEV['tanev']).':Részletek '.((__MUNKATERV_OK)?'munkaterv ok':'#chknaplo1 nincs munkaterv!').':'.((__ORAREND_OK)?'órarend ok':'#chknaplo2 nincs órarend!').':'.((__TANKOROK_OK)?'órarend-tankörök ok':'#chknaplo3 órarendi óra tankör összerendezési hiány!');
	}
    }

    function initDefaults() {

	if (!defined('_ZARAS_HATARIDO')) 	 define('_ZARAS_HATARIDO',date('Y-m-01 00:00:00',strtotime('10 days ago')));
        // Helyttesített óra beírása (szaktanár): következő nap 8:00
        if (!defined('_HELYETTESITES_HATARIDO')) define('_HELYETTESITES_HATARIDO',date('Y-m-d',strtotime('8 hours ago'))); // Csak dárum lehet, mert az órák időpontját nem tudjuk
        // Jegyek beírása, módosítása, törlése (szaktanár): zárásig (zárt időintervallum!)
        if (!defined('_OSZTALYOZO_HATARIDO'))    define('_OSZTALYOZO_HATARIDO',_ZARAS_HATARIDO);

        // Saját óra beírása (szaktanár)
        // A mai nap+8 óra előtti tanatási nap utáni napot megelőző hétfő
        // Azaz egy óra a következő hétfői tanítási nap 16:00-ig írható be.
	if (!defined('_HALADASI_HATARIDO'))
    	    define('_HALADASI_HATARIDO',
        	date('Y-m-d H:i:s',
            	    strtotime('last Monday',
                	strtotime('next day',
                    	    strtotime(
                        	getTanitasiNapVissza(1,date('Y-m-d H:i:s',strtotime('+8hours')))
                    	    )
                	)
            	    )
        	)
    	    );

	// A nevek rendezése a helyettesítés kiíráskor: súly szerint (súly) vagy névsorban (ABC)
	if (!defined('__HELYETTESITES_RENDEZES')) define('__HELYETTESITES_RENDEZES','súly');
	// Fogadóórán egy vizit tervezett hossza
	if (!defined('_VIZITHOSSZ')) define('_VIZITHOSSZ',10);
	// Jegyek default súlyozása
	if (!defined('__DEFAULT_SULYOZAS')) define('__DEFAULT_SULYOZAS','1:1:1:1:1');
	// Jegymódosításkor a jegy típus modosítható-e (pl: féljegy --> százalékos)
	if (!defined('__JEGYTIPUS_VALTHATO')) define('__JEGYTIPUS_VALTHATO',false);

	// TANEV
        // Szülői igazolások száma: félévenként legfeljebb 5 nap
        define('__SZULOI_IGAZOLAS_FELEVRE',5);
        define('__SZULOI_IGAZOLAS_EVRE',0);
        // Szülő által igazolható órák maximális száma: félévenként legfeljebb 14 óra
        define('__SZULOI_ORA_IGAZOLAS_FELEVRE',14);
        define('__SZULOI_ORA_IGAZOLAS_EVRE',0);
        // Osztályfőnöki igazolások száma: évi 3 nap
        // Csak ha < 5 igazolatlanja van
        define('__OSZTALYFONOKI_IGAZOLAS_FELEVRE',0);
        define('__OSZTALYFONOKI_IGAZOLAS_EVRE',5);
        define('__OSZTALYFONOKI_ORA_IGAZOLAS_FELEVRE',0);
        define('__OSZTALYFONOKI_ORA_IGAZOLAS_EVRE',21);

        // Összeadjuk-e a késések perceit, hogy átváltsuk
	if (!defined('_KESESI_IDOK_OSSZEADODNAK')) define('_KESESI_IDOK_OSSZEADODNAK', false);
        // Hány késés felel meg egy igazolatlan órának - ha 0 vagy _KESESI_IDOK_OSSZEADODNAK, akkor nem váltjuk át
        if (!defined('_HANY_KESES_IGAZOLATLAN')) define('_HANY_KESES_IGAZOLATLAN', 3);
        // Hány felszerelés hiány felel meg egy igazolatlan órának - ha 0 vagy _KESESI_IDOK_OSSZEADODNAK, akkor nem váltjuk
        if (!defined('_HANY_FSZ_IGAZOLATLAN')) define('_HANY_FSZ_IGAZOLATLAN', 3);
        // Hány egyenruha hiány felel meg egy igazolatlan órának - ha 0, akkor nem váltjuk
        if (!defined('_HANY_EH_IGAZOLATLAN')) define('_HANY_EH_IGAZOLATLAN', 0);

        // Hiányzás, késés, felszerelés hiány, egyenruha hiány beírása (szaktanár): következő nap 16:00
        if (!defined('_HIANYZAS_HATARIDO')) define('_HIANYZAS_HATARIDO',date('Y-m-d 00:00:00',strtotime('16 hours ago')));
        // Hiányzás, késés beírása osztályfőnöknek: 5 tanítási nap
        if (!defined('_OFO_HIANYZAS_BEIRAS')) define('_OFO_HIANYZAS_BEIRAS',5);
        if (!defined('_OFO_HIANYZAS_HATARIDO')) define('_OFO_HIANYZAS_HATARIDO',getTanitasiNapVissza(_OFO_HIANYZAS_BEIRAS,'curdate()'));
        // Igazolás beírásának határideje: 5 tanítási nap
        if (!defined('_IGAZOLAS_BEIRAS')) define('_IGAZOLAS_BEIRAS',6);
        if (!defined('_IGAZOLAS_BEIRAS_HATARIDO')) define('_IGAZOLAS_BEIRAS_HATARIDO',getTanitasiNapVissza(_IGAZOLAS_BEIRAS,'curdate()'));
        // Igazolás leadás határideje - ha nincs közben osztályfőnöki óra: 8 tanítási nap
        if (!defined('_IGAZOLAS_LEADAS')) define('_IGAZOLAS_LEADAS',8);
        if (!defined('_LEGKORABBI_IGAZOLHATO_HIANYZAS')) define('_LEGKORABBI_IGAZOLHATO_HIANYZAS',getTanitasiNapVissza(_IGAZOLAS_BEIRAS+_IGAZOLAS_LEADAS,'curdate()'));

    }

    //---

    function getIdByOid($oId, $td = 'diak') {
    

	if (intval($oId) == 0) {
	    $_SESSION['alert'][] = 'page:insufficient_access:Hiányzó oktatási azonosító!:'.$td;
	    return false;
	}
	$td = readVariable($td, 'enum', 'diak', array('tanar', 'diak'));

	$q = "SELECT ${td}Id FROM $td WHERE oId = '%s'";
	$id = db_query($q, array('fv' => 'getIdByOid', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($oId)));
	
	if (!$id) {
	    $_SESSION['alert'][] = 'message:id_not_found:(oid:'.$oId.')';
	    return false;
	}

	return $id;
    
    }

    function getSzuloIdByUserAccount($szuloCheck=true) {
	$q = "SELECT szuloId FROM ".__INTEZMENYDBNEV.".szulo WHERE userAccount='"._USERACCOUNT."'";
	$szuloId = db_query($q, array('fv' => 'getSzuloIdByUserAccount', 'modul' => 'naplo_intezmeny', 'result' => 'value'));
	if ($szuloCheck===true && __CHECK_SZULO_TORVENYES === true) {
	    $q = "SELECT count(*) FROM `".__INTEZMENYDBNEV."`.`diak` WHERE diakId='".__PARENTDIAKID."' AND 
	    (
	    (anyaId=%u AND FIND_IN_SET('anya',torvenyesKepviselo)=1) OR
	    (apaId=%u AND FIND_IN_SET('apa',torvenyesKepviselo)=2)  OR
	    (gondviseloId=%u)OR
	    (neveloId=%u)    )
	    ";
	    $torvenyesE = db_query($q, array('fv' => 'getSzuloIdByUserAccount', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values'=>array($szuloId,$szuloId,$szuloId,$szuloId)));
	    if ($torvenyesE==0) {
        	$_SESSION['alert'][] = 'page:nem_torvenyes_kepviselo';
	    }
	}
	return $szuloId;
    }


    function isTanar($tanarId) {
	$q = "SELECT COUNT(*) AS db FROM ".__INTEZMENYDBNEV.".tanar 
		WHERE tanarId=%u AND beDt<=CURDATE() AND (kiDt IS NULL OR kiDt>=CURDATE())";
	$v = db_query($q, array('fv' => 'isTanar', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($tanarId)));
	return ($v==1); 
    }

    // Én hajlanék arra is, hogy az egész $Param tömböt kivegyük...
    function getOsztalyIdsByTanarId($tanarId, $Param = array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'csakId'=>true)) {

	global $_TANEV;

	if (is_null($Param['tanev']) || $Param['tanev']=='') $Param['tanev']=__TANEV;
	if ($Param['tanev'] != __TANEV && $Param['tanev']!='') $TA = getTanevAdat($Param['tanev']);
	else $TA = $_TANEV;

	if (isset($Param['tolDt']) && $Param['tolDt']!='') $tolDt = $Param['tolDt']; else unset($tolDt);
	if (isset($Param['igDt'])  && $Param['igDt']!='')  $igDt = $Param['igDt']; else unset($igDt);
	initTolIgDt($Param['tanev'], $tolDt, $igDt);

	$q = "SELECT DISTINCT osztalyId FROM ".__INTEZMENYDBNEV.".osztalyTanar WHERE tanarId=%u AND beDt <= '%s'
        	AND (kiDt IS NULL OR kiDt >= '%s')";
	$v = array($tanarId, $igDt, $tolDt);
	return db_query($q, array('fv' => 'getOsztalyIdsByTanarId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));

    }


    function naploBeallitasok() {

	global $_RPC;

	$lr = db_connect('naplo_base', array('fv' => 'naploBeallitasok'));
	if ($lr == false) return false;

	// session lekérdezése
	$q = "SELECT intezmeny, telephelyId, tanev, parentDiakId FROM session WHERE sessionID='"._SESSIONID."' AND policy='"._POLICY."'";
	$RESULT = db_query($q, array('fv' => 'naploBeallitasok', 'modul' => 'naplo_base', 'result' => 'record'), $lr);

	if ($vanSession = (is_array($RESULT) && (count($RESULT) > 0))) { // Létező session - egyszerű eset

	    if ($RESULT['intezmeny'] != '')
		initIntezmeny($RESULT);
	    if (defined('__INTEZMENY') && $RESULT['tanev'] != '')
		initTanev($RESULT['intezmeny'], $RESULT['tanev']);
	    if (_POLICY == 'parent' && $RESULT['parentDiakId'] != '')
		define('__PARENTDIAKID', $RESULT['parentDiakId']);

	}
	// Az (alapértelmezett) intézmény lekérdezése - ha még nincs meg
	if (!defined('__INTEZMENY')) {

	    if (_RPC) {
		/* Ha az RPC hívás tartalmazza az OMKod-ot */
		$OMKod = readVariable($_RPC['request']['OMKod'],'numeric unsigned');
		$q = "SELECT rovidNev AS intezmeny FROM intezmeny WHERE OMKod=%u";
		$RESULT = db_query($q, array('fv' => 'naploBeallitasok/settings', 'modul' => 'naplo_base', 'values'=> array($OMKod),'result' => 'record'));
		if ($RESULT['intezmeny'] == '' && $page=='naplo') { // ismeretlen OMKod esetén elutasítjuk a további feldolgozást
		    $DATA = array('alert'=>'page:wrong_data:OMKod','OMKod'=>$OMKod,'result'=>'failure');
		    global $RPC;
		    $RPC->setResponse($DATA); $RPC->sendResponse(); die();
		}
	    } else {
		/* egyedi intezmeny és telephely lekérdezése a settings-ből */
		$q = "SELECT intezmeny, telephelyId FROM settings WHERE userAccount='%s' AND policy='%s'";
		$RESULT = db_query($q, array('fv' => 'naploBeallitasok/settings', 'modul' => 'naplo_base', 'values'=> array(_USERACCOUNT,_POLICY),'result' => 'record'));

		/* ellenőrizzük, hogy érvényes-e */
		if ($RESULT['intezmeny'] != '') {
		    $q = "SELECT rovidnev FROM intezmeny WHERE rovidnev='%s'";
		    $RESULT['intezmeny'] = db_query($q, array('fv' => 'naploBeallitasok/settings intézmény', 'modul' => 'naplo_base', 'result' => 'value', 'values'=>array($RESULT['intezmeny'])), $lr);

		    /* telephely ellenőrzése */
		    if ($RESULT['intezmeny'] != '' && $RESULT['telephelyId'] != '') {
			$q = "SELECT telephelyId FROM `%s`.`telephely` WHERE `telephelyId`='%s'";
			$RESULT['telephelyId'] = db_query($q, array(
			    'fv' => 'naploBeallitasok/settings telephely', 'modul' => 'naplo_base', 'result' => 'value', 
			    'values' => array(intezmenyDbNev($RESULT['intezmeny']), $RESULT['telephelyId'])
			), $lr);
		    } else { unset($RESULT['telephelyId']); /* Ha az intézmény hibás, akkor a telephely sem lehet jó... */ }
		}
	    }

	    /* ha nem érvényes vagy nincs elmentve */
	    if ($RESULT['intezmeny']=='') {
		$q = "SELECT rovidnev FROM intezmeny ORDER BY alapertelmezett DESC LIMIT 1";
		$RESULT['intezmeny'] = db_query($q, array('fv' => 'naploBeallitasok/default intézmény', 'modul' => 'naplo_base', 'result' => 'value'), $lr);
	    }

	    /* Ha a settings-ben nem kapott a telephelyId értéket, akkor lássuk, van-e alapértelmezett! */
	    if ($RESULT['telephelyId'] == '') {
		// Csak ha van alapértelmezett telephely, akkor kérdezzük le!
		$q = "SELECT telephelyId FROM `%s`.`telephely` WHERE alapertelmezett=1 LIMIT 1";
		$RESULT['telephelyId'] = db_query($q, array(
		    'fv' => 'naploBeallitasok/default telephely', 'modul' => 'naplo_base', 'result' => 'value',
		    'values' => array(intezmenyDbNev($RESULT['intezmeny']))
		), $lr);
	    }

	    /* */

	    if ($RESULT['intezmeny']) initIntezmeny($RESULT);
	}

	if (defined('__INTEZMENY') && !defined('__TANEV')) {

	    $lr2 = db_connect('naplo_intezmeny', array('fv' => 'naploBeallitasok'));
	    if ($lr2 === false) return false;

	    if (_RPC) {
		// Ha az RPC hívás tartalmazza a tanévet
		$tanev = readVariable($_RPC['request']['tanev'], 'numeric unsigned');
		// ellenőrzés
		$q = "SELECT tanev FROM szemeszter WHERE tanev=%u ORDER BY szemeszter LIMIT 1";
		$v = array($tanev);
		$ret = db_query($q, array('fv' => 'naploBeallitasok/default tanév', 'modul' => 'naplo_intezmeny', 'result' => 'record','values'=>$v));
	    }
	    if (!is_array($ret) || count($ret) == 0) {
		// A mai dátumhoz leközelebb eső kezdesDt, vagy zarasDt határozza meg, hogy melyik az aktív szemeszter
		$q = "SELECT tanev, szemeszter,
				IF(ABS(DATEDIFF(zarasDt,CURDATE()))<ABS(DATEDIFF(kezdesDt,CURDATE())),
					ABS(DATEDIFF(zarasDt,CURDATE())),
					ABS(DATEDIFF(kezdesDt,CURDATE()))) AS sub
				FROM szemeszter WHERE statusz IN ('aktív','lezárt') ORDER BY statusz,sub LIMIT 1";
		$ret = db_query($q, array('fv' => 'naploBeallitasok/default tanév', 'modul' => 'naplo_intezmeny', 'result' => 'record'));
	    }
	    if (is_array($ret) && count($ret) > 0) {
		$RESULT['tanev'] = $ret['tanev']; $RESULT['szemeszter'] = $ret['szemeszter'];
		initTanev(__INTEZMENY, $RESULT['tanev']);
	    }

	}

	// session létrehozása - ha kell
	$intezmeny = ''; $tanev = $telephelyId = 'NULL';
	if (defined('__INTEZMENY')) {
	    $intezmeny = __INTEZMENY;
	    if (defined('__TELEPHELYID')) $telephelyId = __TELEPHELYID;
	    if (!defined('__TANEV')) $_SESSION['alert'][] = 'message:nincs_tanev';
	} else {
	    $_SESSION['alert'][] = 'message:nincs_intezmeny';
	}
	if (defined('__TANEV') && __TANEV!='') $tanev = __TANEV;

	if (defined('_SESSIONID') && _SESSIONID!='' && !$vanSession) {
	    $q = "REPLACE INTO session (sessionID, policy, intezmeny, telephelyId, tanev) VALUES 
		('"._SESSIONID."','"._POLICY."' , '".$intezmeny."',".$telephelyId.", ".$tanev.")";
	    db_query($q, array('fv' => 'naploBeallitasok/session', 'modul' => 'naplo_base'), $lr);
	}

	db_close($lr);
	return defined('__TANEV') && defined('__INTEZMENY');

    } // function

    function nagykoruE($diakId) {
	if (is_numeric($diakId)) {
	    $q = "select IF(diak.szuletesiIdo + interval 18 year < CURDATE(),1,0) FROM `diak` WHERE diakId=%u";
	    return db_query($q, array('fv' => 'nagykoruE', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => array($diakId)));
	} else {
	    return false;
	}
    }



/* ====================================================================================================================================== */



// Kategóriák
    if (_POLICY=='private' && memberOf(_USERACCOUNT, 'naploadmin') || _RUNLEVEL==='cron') {
	$AUTH['my']['categories'][] = 'naploadmin';
	define('__NAPLOADMIN',true);
    } else {
	define('__NAPLOADMIN',false);
    }
    if (_POLICY=='private' && memberOf(_USERACCOUNT, 'vezetoseg')) {
	$AUTH['my']['categories'][] = 'vezetoseg';
	define('__VEZETOSEG',true);
    } else {
	define('__VEZETOSEG',false);
    }
    
    if (_POLICY=='parent')
	define('__DIAK',true);
    else
	define('__DIAK',in_array('diák',$AUTH['my']['categories']));

    define('__TITKARSAG',in_array('titkárság',$AUTH['my']['categories']));

    // a TANAR tagság lejjebb dől el!!!
    $TANARE = in_array('tanár',$AUTH['my']['categories']);



if (__NAPLO_INSTALLED === true) {

    if (__UZENO_INSTALLED === true && _POLICY=='private') {
	if (memberOf(_USERACCOUNT,'uzenoadmin')===true) define('__UZENOADMIN',true);
	else define('__UZENOADMIN',false);
    }	else define('__UZENOADMIN',false);

    if (!naploBeallitasok()) { // Ha nincs intézmény, vagy tanév
	if (__NAPLOADMIN === true) {
	    // naploadmin vegyen fel intézményt, tanévet
	    if (!defined('__INTEZMENY') and "$page:$sub:$f" != 'naplo:admin:intezmenyek') {
		$href = 'index.php?page=naplo&sub=admin&f=intezmenyek';
		header('Location: '.location($href));
	    } elseif (
		defined('__INTEZMENY') and !defined('__TANEV') 
		and "$page:$sub" != 'naplo:admin'
		and "$page:$sub" != 'naplo:intezmeny'
	    ) {
		$href = 'index.php?page=naplo&sub=admin&f=tanevek';
		header('Location: '.location($href));
	    }
	} elseif (!defined('__INTEZMENY') or (!defined('__TANEV') and "$page:$sub" != 'naplo:intezmeny')) {
	    // ures oldal, ez túl szigorú!
	    //$sub = '';
	    //$f = 'error';
	}
    }

    if (defined('__INTEZMENY')) {
	if (__DIAK) {
	    if (_POLICY=='private') {
		define('__USERDIAKID',getIdByOid(_STUDYID,'diak'));
	    } elseif (defined('__PARENTDIAKID')) {
		define('__USERDIAKID',__PARENTDIAKID);
		define('__USERSZULOID', getSzuloIdByUserAccount(("$page/$sub/$f" != 'naplo//diakValaszto')));
	    } elseif ("$page/$sub/$f" != 'naplo//diakValaszto') {
		header('Location: '.location('index.php?page=naplo&f=diakValaszto'));
	    } else {
		define('__USERDIAKID',false);
	    }
	}
	// A diák milyen jogokkal és kötelezettségekkel rendelkezik
	define('__NAGYKORU',(__DIAK===true && nagykoruE(__USERDIAKID)));
	if ($TANARE && ($TANARE=isTanar(getIdByOid(_STUDYID,'tanar')))) { // itt már ellenőrizhetjük, hogy a keretrendszer szerint tanár, a napló szerint is tanár-e még a megfelelő intézmény időszakában
	    define('__USERTANARID',getIdByOid(_STUDYID,'tanar'));
	    if (__USERTANARID !== false) {
		$_OSZTALYA = getOsztalyIdsByTanarId(__USERTANARID, array('tanev'=>__TANEV,'csakId'=>true));
		define('__OSZTALYFONOK',(is_array($_OSZTALYA) && count($_OSZTALYA) > 0));
	    }
	}
    }

} elseif (__NAPLOADMIN===true || memberOf(_USERACCOUNT,'useradmin')===true) {
    $sub = 'admin';
    $f = 'install';
} elseif ($page == 'naplo') {
    global $sub,$f;
    $sub = '';
    $f = 'error';
    $_SESSION['alert'][] = 'page:page_missing';
} else {
    // másik modult nézünk, csak becsatoljuk a base alatt lévő dolgokat.....
    // jó ez vajon??? dump($page,$sub,$f);
}

    define('__TANAR',$TANARE);

?>
