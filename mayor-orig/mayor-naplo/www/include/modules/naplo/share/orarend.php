<?php

    // Csatoljuk be az általános share könyvtárat - ezt innen tesszük, közvetlenül nem hívható függvény
    require_once('include/modules/naplo/share/nap.php');
/*
    function _genNapok($tolDt,$igDt) { }
*/

// ELEM FELETTI FÜGGVÉNYEK
    
    function getOrarendByTargyId($targyId, $SET=array('tolDt'=>'','igDt'=>'')) {
	// tankörök lekérdezése
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	$NAPOK = _genNapok($tolDt,$igDt); 
	// dátumfüggő FS#100
	for ($i=0; $i<count($NAPOK); $i++) {
	    $_dt = $NAPOK[$i];
	    $TANKORIDK[$_dt] = getTankorByTargyId($targyId, __TANEV, array('csakId'=>true,'tolDt'=>$_dt, 'igDt'=>$_dt));
	}
	return getOrarend($TANKORIDK, array('tolDt'=>$tolDt, 'igDt'=>$igDt));
    }
    
    // ++
    function getOrarendByTanarId($tanarId, $SET=array('tolDt'=>'','igDt'=>'', 'telephely'=>null, 'orarendiOraTankor'=>false)) {
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	$NAPOK = _genNapok($tolDt,$igDt); 
	// dátumfüggő FS#100
	for ($i=0; $i<count($NAPOK); $i++) {
	    $_dt = $NAPOK[$i];
	    $TANKORIDK[$_dt] = getTankorByTanarId($tanarId,__TANEV, array('csakId'=>true,'tolDt'=>$_dt, 'igDt'=>$_dt));
	}
	/* Figyelem! Az első talált munkatervet vesszük itt figyelembe!!! */
	$munkatervIds = getMunkatervByTanarId($tanarId, array('result' => 'idonly', 'tanev'=>__TANEV, 'tolDt'=>$tolDt, 'igDt'=>$igDt));
	$RESULT = getOrarend($TANKORIDK, array('tolDt'=>$tolDt, 'igDt'=>$igDt, 'telephely'=>$SET['telephely'], 'munkatervId'=>$munkatervIds));
	/* --------------------------------------------*/
	if ($SET['orarendiOraTankor']===true 
	    && is_null($SET['telephely'])
	) {
	    /* és kérdezzük le nem tankörId-kkel is... */
	    // Ez a rész csak az orarendiOra Tankörös részhez kell... Ene ... tényleg kell? :) //
	    // Kelljen. Ha a getOrarend nem tud dűlőre jutni, még mindig látszik valami... [k]
	    /* A tanítási hét kitalálása */
	    // erre valójában nincs szükség */
/*
	    $het = getOrarendiHetByDt($tolDt, array('alert'=>false)); 		// Ez NULL-t ad vissza, ha nincs bejegyzés!!!
	    if (is_null($het)) {// nincs a nap táblában ilyen bejegyzés, de megkereshetjük a következő tanítási napot. 
				// (ez persze ahhoz vezet, hogy ha 7 napnál nagyobb a különbség, akár hetek is ugorhatnak
		//$kovTanNap = getTanitasiNap('elore',1,"$tolDt 08:00:00");
		$het = getOrarendiHetByDt($kovTanNap);
	    }
*/	    /* -- */

	    /* !! Így egy nap többször is szerepelhet !! Ugye ez nem baj?? */
	    $munkatervId = $munkatervIds; //hack myself
	    if (!is_array($munkatervId) || count($munkatervId) == 0) {
		return false;
	    }
	    $q = "SELECT dt,orarendiHet,DAYOFWEEK(dt)-1 AS nap,csengetesiRendTipus FROM nap WHERE dt>='%s' AND dt<='%s' 
		    AND munkatervId IN (".implode(',', array_fill(0, count($munkatervId), '%u')).")";
	    $v = mayor_array_join(array($tolDt, $igDt), $munkatervId);
	    $RES = db_query($q, array('fv' => 'getOrarendByTanarId', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	    for($i=0; $i<count($RES); $i++) {
		$W[] = ' (nap='.$RES[$i]['nap'].' AND het='.$RES[$i]['orarendiHet'].') ';
	    }

	    $q = "SELECT * FROM orarendiOra LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) 
		  WHERE igDt>='%s' AND tolDt<='%s' AND tanarId=%u ";
	    if (is_array($W)) $q .= "AND (".implode(' OR ',$W).")";
	    $v = array($tolDt, $igDt, $tanarId);
	    $RES = db_query($q, array('fv' => 'getOrarendByTanarId', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	    for($i=0; $i<count($RES); $i++) {
		    $RESULT['orarendiOra'][$RES[$i]['het']][$RES[$i]['nap']][$RES[$i]['ora']][] = 
			array('tankorId'=>$RES[$i]['tankorId'], 
				'tolDt'=>$RES[$i]['tolDt'],
				'tanarId'=>$RES[$i]['tanarId'],
				'targyJel'=>$RES[$i]['targyJel'],
				'osztalyJel'=>$RES[$i]['osztalyJel'],
				'teremId'=>$RES[$i]['teremId']);
	    }

	}
	/* --------------------------------------- */	
	return $RESULT;
    }
	
    // ++
    function getOrarendByDiakId($diakId, $SET = array('tolDt'=>'','igDt'=>'','osztalyId'=>'')) {
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt($tanev, $tolDt, $igDt);
	$NAPOK = _genNapok($tolDt,$igDt); 
	// dátumfüggő FS#100
	for ($i=0; $i<count($NAPOK); $i++) {
	    $_dt = $NAPOK[$i];
	    $TANKORIDK[$_dt] = getTankorByDiakId($diakId,__TANEV, array('csakId'=>true,'tolDt'=>$_dt,'igDt'=>$_dt));
	}
	if ($SET['osztalyId']!='') {
	    $munkatervId = getMunkatervByOsztalyId($SET['osztalyId']);	
	} else {
	    $munkatervId = getMunkatervByDiakId($diakId, array('tolDt'=>$tolDt,'igDt'=>$igDt));
	}
	$RESULT = getOrarend($TANKORIDK, array('tolDt'=>$tolDt,'igDt'=>$igDt,'NAPOK'=>$NAPOK, 'munkatervId'=>$munkatervId));
	return $RESULT;
    }

    // ++
    function getOrarendByOsztalyId($osztalyId, $SET=array('tolDt'=>'','igDt'=>'')) {
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	$NAPOK = _genNapok($tolDt,$igDt); 
	// dátumfüggő FS#100
	for ($i=0; $i<count($NAPOK); $i++) {
	    $_dt = $NAPOK[$i];
	    $TANKORIDK[$_dt] = getTankorByOsztalyId($osztalyId,__TANEV,array('csakId'=>true,'tolDt'=>$_dt,'igDt'=>$_dt));
	}
	//
	$munkatervId = getMunkatervByOsztalyId($osztalyId);
	$RESULT = getOrarend($TANKORIDK, array('tolDt'=>$tolDt,'igDt'=>$igDt,'NAPOK'=>$NAPOK, 'munkatervId'=>$munkatervId));
	return $RESULT;
    }

    // ++
    function getOrarendByTankorId($tankorId, $SET=array('tolDt'=>'','igDt'=>'')) {
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	$NAPOK = _genNapok($tolDt,$igDt); 
	// dátumfüggő FS#100
	for ($i=0; $i<count($NAPOK); $i++) {
	    $_dt = $NAPOK[$i];
	    $TANKORIDK[$_dt][0] = $tankorId;
	}
	$RESULT = getOrarend($TANKORIDK, array('tolDt'=>$tolDt,'igDt'=>$igDt));
	return $RESULT;
    }

    // ++
    function getOrarendByMkId($mkId, $SET=array('tolDt'=>'','igDt'=>'','telephely'=>null)) {
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt);
	$NAPOK = _genNapok($tolDt,$igDt); 
	// dátumfüggő FS#100
	for ($i=0; $i<count($NAPOK); $i++) {
	    $_dt = $NAPOK[$i];
	    $TANKORIDK[$_dt] = getTankorByMkId($mkId,__TANEV,array('csakId'=>true,'tolDt'=>$_dt,'igDt'=>$_dt));
	}
	$munkatervIds = getMunkatervek(array('result'=>'idonly'));
	$RESULT = getOrarend($TANKORIDK, array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephely'=>$SET['telephely'],'munkatervId'=>$munkatervIds));
	return $RESULT;
    }

    function getOrarend($TANKOROK, $SET=array('tolDt'=>'','igDt'=>'', 'telephely'=>null, 'NAPOK' => null, 'munkatervId'=>null)) {

	//a tankörök tömb szerkezete megváltozik rev1300
	if (!is_array($TANKOROK) || count($TANKOROK)==0) return false;
        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt); // valljuk meg, ez kicsit tág intervallum lehet...
	if (isset($SET['NAPOK']) && is_array($SET['NAPOK'])) {
	    $NAPOK = $SET['NAPOK'];
	} else {
	    $NAPOK = _genNapok($tolDt,$igDt);
	}

	$tanevDb = tanevDbNev(__INTEZMENY, __TANEV);

	if ($SET['telephely']!='' && is_string($SET['telephely'])) {
	    $W_TELEPHELY1 = " LEFT JOIN terem USING (teremId)";
	    $W_TELEPHELY2 = " WHERE telephely='".$SET['telephely']."'  ";
	}
	$RESULT['assocFormat']['$nap$']['$ora$']['orak']['$index$']=true;
	$RESULT['tankorokFormat']['$index$'] = true;
	$_TMPTANKORIDK = array();
	$RESULT['telephelyIdk'] = array();
	for ($nI=0; $nI<count($NAPOK); $nI++) { // nI as napIndex
	    $_stamp = strtotime($NAPOK[$nI]);
	    $_dow = (date('w', $_stamp));
	    //$nap = $nI+1; // ez a rossz!
	// Vasárnap
	    $nap = ($_dow==0)?7:$_dow; 
	    $RESULT['napok'][$nap] = getOrarendiHetByDt($NAPOK[$nI],array('result'=>'assoc','munkatervId'=>$SET['munkatervId']));
	    $het = $RESULT['napok'][$nap]['het'];
	    if (in_array($RESULT['napok'][$nap]['tipus'],array('tanítási nap','speciális tanítási nap')) && $het!=0) { // ha van egyáltalán beállított tanítás...
		$_dt = $NAPOK[$nI];
		$TIME = "igDt>='%s' AND tolDt<='%s' AND nap=%u"; // ez miírt van külön? [bb]
		$_TK  = $TANKOROK[$_dt];
		if (is_array($_TK) && count($_TK)>0) {
		    $q = "SELECT orarendiOra.*,orarendiOraTankor.*,tankorTipus.jelleg,terem.telephelyId FROM $tanevDb.orarendiOra 
			    ".$W_TELEPHELY1."
			    LEFT JOIN $tanevDb.orarendiOraTankor USING (tanarId,osztalyJel,targyJel) 
			    ".$W_TELEPHELY2."
			    LEFT JOIN tankor USING (tankorId) LEFT JOIN tankorTipus USING (tankorTipusId)
			    LEFT JOIN terem USING (teremId)
			    HAVING $TIME AND het=%u AND tankorId IN (".implode(',', array_fill(0, count($_TK), '%u')).")
			    AND tankorId IN (
				SELECT tankorId FROM $tanevDb.nap 
				LEFT JOIN $tanevDb.munkatervOsztaly USING (munkatervId)
				LEFT JOIN tankorOsztaly USING (osztalyId)
				WHERE tipus IN ('tanítási nap') AND dt='".$_dt."'
			    )			    

		    "; // nagy lekérdezés
		    array_unshift($_TK, $_dt, $_dt, $nap, $het);
		    $RES = db_query($q, array('fv' => 'getOrarend', 'modul' => 'naplo_intezmeny', 'values' => $_TK, 'result' => 'indexed'), $olr);
		    /* asszoc tömböt szeretnénk, és kigyűjtük az érintett tanköröket */
		    for($i = 0; $i < count($RES); $i++) {
			if (!in_array($RES[$i]['telephelyId'],$RESULT['telephelyIdk']) && $RES[$i]['telephelyId']>0) $RESULT['telephelyIdk'][] = $RES[$i]['telephelyId'];
			$RESULT['assoc'][$RES[$i]['nap']][$RES[$i]['ora']]['orak'][] = array('igDt'=>$RES[$i]['igDt'],'tolDt'=>$RES[$i]['tolDt'], 'het'=>$RES[$i]['het'],'tankorId'=>$RES[$i]['tankorId'], 'tanarId'=>$RES[$i]['tanarId'],'targyJel'=>$RES[$i]['targyJel'],'osztalyJel'=>$RES[$i]['osztalyJel'],'teremId'=>$RES[$i]['teremId'],'jelleg'=>$RES[$i]['jelleg'],'oo'=>true);
			$_TMPTANKORIDK[$RES[$i]['tankorId']] = true; 
		    }
		    $RESULT['db'] += count($RES);
		}
	    }
	}
	if (is_array($_TMPTANKORIDK)) foreach ($_TMPTANKORIDK as $_tankorId => $_tmp) {
	    $RESULT['tankorok'][] = $_tankorId;
	}
	// adjuk tovább az esetlegesen lekérdezett tankörlistát... (ez csak Id-k gyűjteménye)
	$RESULT['mindenTankorByDt'] = $TANKOROK;
	return $RESULT;
	
    }

    function getOrarendByTeremId($teremId, $het = '', $SET=array('tolDt'=>'','igDt'=>'', 'telephely'=>null)) {

	$diff = 5;

        $tolDt = $SET['tolDt']; $igDt = $SET['igDt'];
	initTolIgDt(__TANEV, $tolDt, $igDt); // valljuk meg, ez kicsit tág intervallum lehet...
	if (isset($SET['NAPOK']) && is_array($SET['NAPOK'])) {
	    $NAPOK = $SET['NAPOK'];
	} else {
	    $NAPOK = _genNapok($tolDt,$igDt);
	}

	if ($telephely!='') { 
	    $W_TELEP = " AND telephely='%s'";
	    $v = array($telephely);
	} else {
	    $W_TELEP = '';
	    $v = array();
	}

//	for ($nap=1; $nap<=count($NAPOK); $nap++) {
	for ($nI=0; $nI<count($NAPOK); $nI++) { // nI as napIndex
	    $_stamp = strtotime($NAPOK[$nI]);
	    $_dow = (date('w', $_stamp));
	// Vasárnap
	    $nap = ($_dow==0)?7:$_dow;
	    $RESULT['napok'][$nap] = getOrarendiHetByDt($NAPOK[$nI],array('result'=>'assoc'));
	    $het = $RESULT['napok'][$nap]['het'];
	    if ($het!=0) { // ha van egyáltalán beállított tanítás...
		$_dt = $NAPOK[$nI];
		$TIME = "igDt>='%s' AND tolDt<='%s' AND nap=%u"; // miért van ez külön? [bb]
		$q = "SELECT * FROM orarendiOra LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId) LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) HAVING $TIME 
			AND het=%u AND teremId=%u".$W_TELEP;
		array_unshift($v, $_dt, $_dt, $nap, $het, $teremId);
		$RES = db_query($q, array('fv' => 'getOrarend', 'modul' => 'naplo', 'values' => $v, 'result' => 'indexed'), $olr);
		/* és egészítsük ki bonyolultabb asszoc tömbbé */
		for($i = 0; $i < count($RES); $i++) {
		    if (!in_array($RES[$i]['telephelyId'],$RESULT['telephelyIdk']) && $RES[$i]['telephelyId']>0) $RESULT['telephelyIdk'][] = $RES[$i]['telephelyId'];
		    $RESULT['assoc'][$RES[$i]['nap']][$RES[$i]['ora']]['orak'][] = array('het'=>$RES[$i]['het'],'tankorId'=>$RES[$i]['tankorId'], 'tanarId'=>$RES[$i]['tanarId'],'targyJel'=>$RES[$i]['targyJel'],'osztalyJel'=>$RES[$i]['osztalyJel'],'teremId'=>$RES[$i]['teremId']);
		    if (!is_null($RES[$i]['tankorId'])) $RESULT['tankorok'][] = $RES[$i]['tankorId'];
		}
	    }
	}
	return $RESULT;

    }



    // Az adott dátum napján érvényes órarend lekérdezése
    function getOrarendByDt($dt, $orarendiHet = array(1), $tanev = __TANEV) {
	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	$q = "SELECT * FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel) 
		WHERE het IN (".implode(',', array_fill(0, count($orarendiHet), '%u')).") AND tolDt<='%s' AND '%s' <= igDt
		ORDER BY het,nap,ora,tanarId";
	array_unshift($orarendiHet, $tanevDb, $tanevDb);
	array_push($orarendiHet, $dt, $dt);
	return db_query($q, array('fv' => 'getOrarendByDt', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $orarendiHet));
    }

    function getOrarendiHetek($SET = array('tolDt' => '', 'igDt' => '', 'tanev' => __TANEV, 'csakOrarendbol' => false, 'felsoHatar' => 20)) {

        $tolDt = readVariable($SET['tolDt'], 'datetime', null); 
	$igDt  = readVariable($SET['igDt'],  'datetime', null); 
	$csakOrarendbol = readVariable($SET['csakOrarendbol'], 'bool', false);
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	$felsoHatar = readVariable($SET['felsoHatar'], 'numeric unsigned', 20);

	initTolIgDt($tanev, $tolDt, $igDt);
	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);

	$q = "SELECT DISTINCT het FROM `%s`.orarendiOra WHERE igDt>='%s' AND tolDt<='%s' AND het<%u ORDER BY het";
        $ret = db_query($q, array('fv' => 'getOrarendiHetek', 'modul' => 'naplo', 'result' => 'idonly', 'values' => array($tanevDb, $tolDt, $igDt, $felsoHatar)));
        if (!$csakOrarendbol) {
            // nincs még egyetlen órarendi bejegyzés sem - vagyük a munkatervből (kell a tolDt-igDt megszorítás?)
            $q = "SELECT DISTINCT orarendiHet FROM `%s`.nap WHERE orarendiHet != 0 AND orarendiHet<%u AND '%s'<=dt AND dt<='%s' ORDER BY orarendiHet";
	    $ret = @array_values(@array_unique(@array_merge($ret,db_query($q, array('fv' => 'getOrarendiHetek', 'modul' => 'naplo', 'result' => 'idonly', 'values' => array($tanevDb,$felsoHatar,$tolDt,$igDt))))));
        }

        return $ret;
    }

    function getLastOrarend($SET = array('tanev' => __TANEV)) {
	$v = array(tanevDbNev(__INTEZMENY, $SET['tanev']));
	return db_query("SELECT max(het) FROM `%s`.orarendiOra", array('fv' => 'getLastOrarend', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'));
    }

    function getMinOra($SET = array('tanev' => __TANEV)) {
	$v = array(tanevDbNev(__INTEZMENY, $SET['tanev']));
	return db_query("SELECT MIN(ora) FROM `%s`.orarendiOra", array('fv' => 'getMinOra', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'));
    }
    function getMaxOra($SET = array('tanev' => __TANEV)) {
	$v = array(tanevDbNev(__INTEZMENY, $SET['tanev']));
	$ret = db_query("SELECT MAX(ora) FROM `%s`.orarendiOra", array('fv' => 'getMaxOra', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'));
	if (defined('__MAXORA_MINIMUMA') && __MAXORA_MINIMUMA>$ret) $ret = __MAXORA_MINIMUMA;
	return $ret;
    }
    function getMaxNap($SET = array('tanev' => __TANEV, 'tolDt'=>null, 'igDt'=>null, 'haladasi'=>false)) {
	if ($SET['tanev']=='') $SET['tanev'] = __TANEV;
	$tanevDbNev = tanevDbNev(__INTEZMENY, $SET['tanev']);
	$v = array($tanevDbNev);
	$maxNap = db_query("SELECT max(nap) FROM `%s`.orarendiOra", array('fv' => 'getMaxNap', 'modul' => 'naplo_intezmeny', 'values' => $v, 'result' => 'value'));
        $halMaxNap=0;
	if ($SET['haladasi']===true) {
	    $v = array($tanevDbNev,$SET['tolDt'],$SET['igDt']);
	    $q = "select max(dayofweek(dt)-1) AS halMaxNap from `%s`.ora where dt>='%s' and dt<='%s'" ;
	    $halMaxNap = 
		db_query($q,
		array('fv' => 'getMaxNap2', 'modul' => 'naplo', 'result' => 'value','values'=>$v));
	}
	return  (($halMaxNap>$maxNap)?$halMaxNap:$maxNap);
    }

    function getOrarendiHetByDt($dt, $SET = array('result' => '', 'alert'=>false, 'munkatervId'=>null)) {

	/* ezt javíthatnánk!!! */
	if ( (!is_array($SET['munkatervId']) && $SET['munkatervId'] != '') || (is_array($SET['munkatervId']) && count($SET['munkatervId'])>0)  ) {
	    if (is_array($SET['munkatervId']))
		$W = ' AND orarendiHet!=0 AND munkatervId IN ('.implode(',',$SET['munkatervId']).')';
	    else	
		$W = ' AND orarendiHet!=0 AND munkatervId = '.intval($SET['munkatervId']);
	} else $W = '';

	if ($dt == '') { 
	    $return = getLastOrarend();
	} else {
	    if ($SET['result']=='assoc')
		$return = db_query(
		    "SELECT orarendiHet AS het,dt,tipus,megjegyzes,csengetesiRendTipus FROM nap WHERE dt='%s' ". $W,
		    array('fv' => 'getOrarendiHetByDt', 'modul' => 'naplo', 'values' => array($dt), 'result' => 'record')
		);
	    else
		$return = db_query(
		    "SELECT orarendiHet FROM nap WHERE dt='%s' ".$W,
		    array('fv' => 'getOrarendiHetByDt', 'modul' => 'naplo', 'values' => array($dt), 'result' => 'value')
		);
	}
	if ($SET['alert']===true && is_null($return)) {
	    $_SESSION['alert'][] = 'info:nincs_nap_bejegyzes:'.$dt;
	}
	return $return;
    }

    function getOrarendiOraTankor($tanev = __TANEV) {

	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	$q = "SELECT * FROM `%s`.orarendiOraTankor";
	return db_query($q, array('fv' => 'getOrarendiOraTankor', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($tanevDb)));

    }

    function getOrarendiOraAdat($SET = array('tanev' => __TANEV, 'dt' => null, 'tanarId' => null, 'het' => null, 'nap' => null, 'ora' => null)) {

	$tanev = readVariable($SET['tanev'], 'numeric unsigned', (defined('__TANEV')?__TANEV:null));
	$dt = readVariable($SET['dt'], 'datetime', null);
	initTolIgDt($tanev, $dt, $dt);

	// Ha van dátum, de nincs hét, nap óra, akkor azt a dátum alapján kellene beállítani)

	if (isset($SET['tanarId']) && isset($SET['het']) && isset($SET['nap']) && isset($SET['ora']) && isset($dt) && isset($SET['tanev'])) {

	    $tanevDb = tanevDbNev(__INTEZMENY, $SET['tanev']);
	    $q = "SELECT * FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
		    WHERE tolDt <= '%s' AND '%s' <= igDt AND tanarId=%u AND het=%u
		    AND nap=%u AND ora=%u";
	    $v = array($tanevDb, $tanevDb, $dt, $dt, $SET['tanarId'], $SET['het'], $SET['nap'], $SET['ora']);
	    return db_query($q, array('fv' => 'getOrarendiOraAdat', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v));

	} else {
	    $_SESSION['alert'][] = 'message:empty_field:tanarId,het,nap,ora,tanev,dt';
	    return false;
	}

    }

    function getTankorHetiOraszam($tankorId, $SET = array('tanev' => __TANEV, 'dt' => null, 'het' => 1)) {

	global $_TANEV;

	$tanev =  readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
	if ($tanev == __TANEV) $TA = $_TANEV;
	else $TA = getTanevAdat($tanev);
	$dt = readVariable($SET['dt'], 'datetime', $TA['zarasDt']);
	$het = readVariable($SET['het'], 'numeric unsigned', 1);
	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	
	$q = "SELECT COUNT(*) FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId,osztalyJel,targyJel)
		WHERE tolDt<='%s' AND '%s'<=igDt AND tankorId=%u AND het=%u";
	$v = array($tanevDb, $tanevDb, $dt, $dt, $tankorId, $het);
	return db_query($q, array('fv' => 'getTankorHetiOraszam', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v));

    }
    function getCsengetesiRend() { // 'csengetesiRendTipus','telephelyId','nap','ora'
	$SET['arraymap'] = array('csengetesiRendTipus','telephelyId','nap','ora');
	$q = "SELECT * FROM csengetesiRend";
	$result = db_query($q, array('fv'=>'getCsengetesiRend','modul'=>'naplo_intezmeny','result'=>'indexed'));
	return reindex($result,$SET['arraymap']);
    }

    function orarendvane() {
	$q = "SELECT IF(count(*)>0,1,0) FROM orarendiOra";
	return db_query($q, array('fv'=>'orarendvane','modul'=>'naplo','result'=>'value'));
    }

    function getUtolsoorak() {
	$q = "SELECT *,getOraTolTime(ora.oraId) AS tolTime, getOraIgTime(ora.oraId) AS igTime
FROM (SELECT dt,max(ora) AS utolsooraateremben,teremId,terem.leiras AS teremNev FROM ora LEFT JOIN ".__INTEZMENYDBNEV.".terem
USING (teremId) WHERE teremId IS NOT NULL GROUP BY dt,teremId) AS x LEFT JOIN ora ON (ora.dt = x.dt AND x.utolsooraateremben = ora.ora AND x.teremId = ora.teremId)";
	$r = db_query($q, array('fv'=>'getUtolsoorak','modul'=>'naplo','result'=>'indexed'));
	$RES = array();
	for ($i=0; $i<count($r); $i++) {
	    $RES[$r[$i]['dt']][$r[$i]['teremId']] = $r[$i];
	}
	return $RES;
    }

?>
