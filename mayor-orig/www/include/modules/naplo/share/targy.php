<?php

    function getTargyakByMkId($mkId, $SET = array('result' => 'indexed')) {

	$q = "SELECT * FROM targy WHERE mkId=%u";
	$v = array($mkId);
	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed', 'idonly', 'assoc'));
	return db_query($q, array('fv' => 'getTargyakByMkId', 'modul' => 'naplo_intezmeny', 'result' => $result, 'values' => $v));    
    
    }

    // Miért van három szinte azonos függvény?
    function getTargyById($targyId) {

	$q = "SELECT * FROM targy WHERE targyId=%u";
	$v = array($targyId);
	return db_query($q, array('fv' => 'getTargyById', 'modul' => 'naplo_intezmeny', 'result'=> 'record', 'values'=>$v));

    }

    function getTargyAdatByIds($targyIds=null) { //2009 // esetleg ByIds nélkül kifejezőbb lenne a név...
	if (is_array($targyIds) && count($targyIds) > 0) {
	    $q = "SELECT * FROM targy WHERE targyId IN (".implode(',', array_fill(0, count($targyIds), '%u')).") ORDER BY targyNev";
	    $v = $targyIds;
	} else {
	    $q = "SELECT * FROM targy ORDER BY targyNev";
	    $v = array();
	}
	$R = db_query($q, array('fv' => 'getTargyAdatByIds', 'modul' => 'naplo_intezmeny', 'result' => 'assoc', 'keyfield' => 'targyId','values'=>$v));
	return $R;
    }

    function getTargyak($SET = array('mkId' => null, 'targySorrendNev' => null, 'tanev' => __TANEV, 'osztalyId' => null, 'arraymap' => null) ) {

	if (isset($SET['tanev']) && $SET['tanev'] != __TANEV) $tanevDb = tanevDbNev(__INTEZMENY, $SET['tanev']);
        else $tanevDb = __TANEVDBNEV;
	$targySorrendNev = readVariable($SET['targySorrendNev'], 'enum', null, getTargySorrendNevek());

	if (isset($SET['mkId'])) {
	    $W = 'WHERE mkId = %u '; $v = array($SET['mkId']);
	} else {
	    $W = ''; $v = array();
	}
	if ($targySorrendNev != '' && $SET['osztalyId'] != '') {
	    $q = "SELECT targy.* FROM targy LEFT JOIN $tanevDb.targySorszam 
		    ON targy.targyId = targySorszam.targyId AND osztalyId=%u AND sorrendNev='%s'
		    $W ORDER BY IF(sorszam IS NULL,100,sorszam), targyNev";
	    array_unshift($v, $SET['osztalyId'], $targySorrendNev);
	} else {
    	    $q = "SELECT * FROM targy $W ORDER BY targyNev";
	}
	$r = db_query($q, array('modul' => 'naplo_intezmeny', 'result' => 'indexed', 'fv' => 'getTargyak', 'values' => $v));

	if (is_array($SET['arraymap'])) {
	    $RE = reindex($r,$SET['arraymap']);
	} else {
	    $RE = $r;
	}

	return $RE;
    }

    function getTargySorrendNevek($tanev = __TANEV) {
	require_once('include/modules/naplo/share/file.php');
	return getEnumField('naplo_base', tanevDbNev(__INTEZMENY, $tanev).'.targySorszam', 'sorrendNev');
    }

    function getTanevTargySorByOsztalyId($osztalyId, $tanev = __TANEV, $sorrendNev = 'bizonyítvány') {

        global $_TANEV;

        if ($tanev != __TANEV) $Tanev = getTanevAdat($tanev);
        else $Tanev = $_TANEV;

	// Az összes diákra szükség van aktív tanévben is - év végén a végzősök már nincsenek jogviszonyban...
	$Statusz = array('jogviszonyban van','magántanuló','jogviszonya felfüggesztve','jogviszonya lezárva');
	$diakIds = array();
        $Diak = getDiakok(array('osztalyId' => $osztalyId, 'tanev' => $tanev, 'statusz' => $Statusz));
        for ($i = 0; $i < count($Diak); $i++) $diakIds[] = $Diak[$i]['diakId'];


	if (count($diakIds) > 0) {

	    // a második paraméter $Tanev, de kompatibilis a $szemeszteradattal)
	    $targyak = getTargyakByDiakIds($diakIds,$Tanev,$osztalyId,$sorrendNev, array('csakOratervi'=>false,'filter'=>''));
	    return $targyak;
	}
	else
	{
	    return false;
	}

    }
/*
    Egy osztályhoz rendelt tankörök tárgyai - lehet, hogy valójában nincs is olyan diák, aki tanulja ezt a tárgyat. 
    Magatartás/szorgalom nincs benne... Miért is ne lenne benne? Én nem látom, hogy ki lenne szűrve...
    nyomtatas/haladasinaplo-ban használt függvény!
*/
    function getTargyakByOsztalyId($osztalyId, $tanev = __TANEV, $SET = array('targySorrendNev' => 'napló', 'result' => 'indexed')) {
	if ($tanev != __TANEV) $tanevDbNev = tanevDbNev(__INTEZMENY, $tanev);
	else $tanevDbNev = __TANEVDBNEV;
	$q = "SELECT DISTINCT targy.targyId AS targyId, targyNev, targyRovidNev FROM targy
		LEFT JOIN tankor USING (targyId) 
		LEFT JOIN tankorOsztaly USING (tankorId) 
		LEFT JOIN ".$tanevDbNev.".targySorszam 
		    ON tankorOsztaly.osztalyId=targySorszam.osztalyId AND targy.targyId=targySorszam.targyId AND sorrendNev = '%s'
		LEFT JOIN tankorSzemeszter USING (tankorId)
		WHERE tanev=%u AND tankorOsztaly.osztalyId=%u ORDER BY IFNULL(sorszam,1000),targyNev";
	return db_query($q, array('fv' => 'getTargyByOsztalyId', 'modul' => 'naplo_intezmeny', 'result' => $SET['result'], 'values' => array($SET['targySorrendNev'],$tanev,$osztalyId)));
    }

    function getTargyakByDiakId($diakId, 
		$SET = array('tanev' => __TANEV, 'dt' => null, 'tolDt' => null, 'igDt' => null, 'result' => 'indexed','csakOratervi'=>false, 'osztalyId' => null, 'filter' => null, 'targySorrendNev' => null)) {
    /*
	A függvény először lekérdezi a diák osztályait az adott időintervallumban. Ha van megadva osztalyId paraméter, akkor csak
	azt veszi a továbbiakban figyelembe! Majd a diák és az egyes osztályok képzéseit is lekérdezzük (az intervallum végi dátummal!).
	
	Ha van megadva osztalyId, akkor csak az oda sorolható tárgyakat kérdezzük le, illetve a más osztályhoz nem sorolható, de évfolyamban megfelelőket
	(ld. szűrés)

	Ezek után a tárgyakat három forrásból veszi:
	1. A diák adott időintervallumbeli tankör tagságaiból vett tárgyakat osztályokhoz rendeli, vagy az egyéb kategóriába teszi
	2. Az adott időintervallumba eső zárójegyek tárgyait a korábbi tankörök alapján rendeli osztályokhoz, a maradék, de évfolyamban
	   a diák valamely osztályának megfelelő tárgyat az egyéb kategóriába sorolja. Azok a zárójegy tárgyak tehát sose kerülnek be,
	   amik évfolyamban nem felelnek meg...
	3. A diák esetleges képzései egyben osztályhoz is rendelik az onnan származó tárgyakat.

	A keletkező adatszerkezet:
	    array(
		osztaly => array($osztalyId1 => array( .... tárgyId-k ....), $osztalyId2 => array( ... ), ...),
		egyeb => array( ... tárgyId-k ...),
		targyAdat => array(
		    $targyId1 => array(targyNev => ..., evkoziKovetelmeny => ..., zaroKovetelmeny => ..., jegyTipus => ..., evfolyam => ..., evfolyamJel =>) // nem mindegyik van mindig!!
		)
	    )

	Ha nincs megadva 'result' paraméter akkor a teljes tömböt adja vissza a fv.

	Ha van megadva 'result', akkor figyelembe vesszi a 'filter' paramétert is - eddig egy értéket vesz figyelembe: kovetelmeny
	Ezután a függvény $result-nak megfelelően átalakítva adja vissza a szelektált tárgyak listáját (idonly, assoc, indexed)

    */
	// ERŐS TANÉVFÜGGŐSÉG!
	$tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);

	// A tárgysorrend tanév függő
	if ($tanev != __TANEV) $tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	else $tanevDb = __TANEVDBNEV;

	$dt = readVariable($SET['dt'], 'datetime', null);
	$tolDt = readVAriable($SET['tolDt'], 'date', $dt);
	$igDt = readVAriable($SET['igDt'], 'date', $dt);
	initTolIgDt($tanev, $tolDt, $igDt);
	if (is_null($dt)) $dt = $igDt;
	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed','assoc','keyvaluepair','idonly'));
//!!!!
	$targySorrendNev = readVariable($SET['targySorrendNev'], 'sql', null, array('napló','anyakönyv','ellenőrző','bizonyítvány','egyedi'));
	$return = array('egyeb' => array(), 'osztaly' => array(), 'targyAdat' => array());

	if ($SET['csakOratervi']===true) {
	    $LEFTJOIN = " LEFT JOIN tankorTipus USING (tankorTipusId) ";
	    $W = " AND tankorTipus.oratervi='óratervi' ";
	}

	// A diák osztályai, képzései - a tárgyak csoportosításához!
	$diakOsztalyIds = getDiakOsztalya($diakId, array('tanev' => $tanev, 'tolDt' => $tolDt, 'igDt' => $igDt, 'result' => 'idonly'));
	if (isset($SET['osztalyId'])) {
	    if (!in_array($SET['osztalyId'], $diakOsztalyIds)) {
		// Nem biztos, hogy hiba. Pl. zárási stat-ban, ha első félévben osztályt vált, akkor második félévben ebbe az ágba kerül a lekérdezés...
		if (__NAPLOADMIN) $_SESSION['alert'][] = 'info:wrong_data:getTargyakByDiakId:A diák ('.$diakId.') nem tagja az osztalynak ('.$SET['osztalyId'].')';
		return false;
	    } else {
		$diakOsztalyIds = array($SET['osztalyId']);
	    }
	}
	$diakEvfolyamJel = array();
	if (count($diakOsztalyIds)==0) {
	    // --TODO, ilyenkor mi van?
	    // Pl. ha év közben elmegy a tanuló az iskolából...
	    // Mindenesetre ne jelenjen meg mindig mindenkinek ez az üzenet, mert idegesítő... elég az adminnak
	    if (__NAPLOADMIN) $_SESSION['alert'][] = 'info:not_implemented:nincs_osztaly,getTargyakByDiakId:diakId='.$diakId;
	} else {
	    $osztalyKepzes = getKepzesByOsztalyId($diakOsztalyIds, array('result' => 'indexed', 'arraymap' => array('osztalyId','kepzesId')));
	    foreach ($diakOsztalyIds as $osztalyId) {
		$return['osztaly'][$osztalyId] = array();
		$osztalyAdat[$osztalyId] = array(
		    'targy' => getTargyakByOsztalyId($osztalyId, $tanev, array('result' => 'idonly')),
		    'evfolyam'=>getEvfolyam($osztalyId,$tanev),
		    'evfolyamJel'=>getEvfolyamJel($osztalyId,$tanev)
		);
		if (is_array($osztalyKepzes[$osztalyId])) foreach ($osztalyKepzes[$osztalyId] as $kepzesId => $kAdat) {
		    if (isset($kepzesOsztaly[$kepzesId])) { 
			// Ha azonos évfolyamon vannak a képzéshez rendelt osztályok, akkor nem okoz gondot a több osztály a képzésből jövő tárgyak lekérdezésénél - ezért nem üzenünk hibát
			if ($osztalyAdat[$osztalyId]['evfolyamJel'] != $osztalyAdat[ $kepzesOsztaly[$kepzesId] ]['evfolyamJel']) { 
			    $_SESSION['alert'][] = 'message:wrong_data:getTargyakByDiakId:egy képzés több különböző évfolyamú osztályhoz tartozik';
			}
			// TODO , evfolyamJel re való áttérés a képzéseknél - ellenőrzés, tesztelés...
			if ($osztalyAdat[$osztalyId]['evfolyamJel'] != $osztalyAdat[ $kepzesOsztaly[$kepzesId] ]['evfolyamJel']) { 
			    $_SESSION['alert'][] = 'message:wrong_data:getTargyakByDiakId:egy képzés több különböző évfolyamú osztályhoz tartozik';
			}
		    } else {
			$kepzesOsztaly[$kepzesId] = $osztalyId;
		    }
		}
		if (!in_array($osztalyAdat[$osztalyId]['evfolyamJel'], $diakEvfolyamJel)) $diakEvfolyamJel[] = $osztalyAdat[$osztalyId]['evfolyamJel'];
	    }
	}

	if (count($diakEvfolyamJel)==0) {
	    // --TODO, ilyenkor mi van?	
	    // Pl. ha év közben elmegy a tanuló az iskolából...
	    // Legalább ne mindenkinek, csak az adminnak jelenjen meg a hibaüzenet...
	    if (__NAPLOADMIN) $_SESSION['alert'][] = 'info:not_implemented:nincs_evfolyam,getTargyakByDiakId';
	}
	
	// Tárgyak lekérdezése a diák aktuális tankör-tagságai alapján
	$q = "SELECT DISTINCT targyId,targyNev, zaroKovetelmeny, tankor.kovetelmeny AS evkoziKovetelmeny
		FROM targy
		LEFT JOIN tankor USING (targyId) 
		LEFT JOIN tankorSzemeszter USING (tankorId)
		$LEFTJOIN
		LEFT JOIN tankorDiak USING (tankorId)
		WHERE diakId=%u AND tanev=%u AND beDt <= '%s' AND ('%s' <= kiDt OR kiDt IS NULL)
		$W
		ORDER BY targyNev, tankor.kovetelmeny DESC"; // Ha több tankör van egy tárgyhoz különböző követelményekkel, akkor ne a "nincs" legyen az utolsó!
	$v = array($diakId, $tanev, $igDt, $tolDt);

	$return['targyAdat'] = db_query($q, array('fv' => 'getTargyByDiakId', 'modul' => 'naplo_intezmeny', 'keyfield' => 'targyId', 'result' => 'assoc', 'values' => $v));

	foreach ($return['targyAdat'] as $targyId => $tAdat) {
	    $osztalybaSorolva = false;
	    foreach ($diakOsztalyIds as $osztalyId) {
		if (in_array($targyId, $osztalyAdat[$osztalyId]['targy'])) {
		    $return['osztaly'][$osztalyId][] = $targyId;
		    $osztalybaSorolva = true;
		}
	    }
	    if (!$osztalybaSorolva) $return['egyeb'][] = $targyId;
	}

	// Tárgyak lekérdezése a beírt zárójegyek, osztályzatok alapján
	if (count($diakEvfolyamJel)>0) {
	    // Ez lenne az új javaslat: az adott tolDt-igDt közötti VAGY adott évfolyamokra illeszkedő zárójegyek legyenek lekérdezve - ez így bővebb... (evfolyam-->evfolyamJel tesztelendő)
	    $q = "SELECT targyId, targyNev, evfolyam, evfolyamJel, jegyTipus FROM zaroJegy LEFT JOIN targy USING (targyId)                                                     
		WHERE diakId=%u AND (('%s' <= hivatalosDt AND hivatalosDt <= '%s') OR evfolyamJel IN (".implode(',', array_fill(0, count($diakEvfolyamJel), '%u'))."))";
	    // Ez az eredeti tolDt-igDt közötti ÉS adott évfolyamokra illeszkedő jegyek... (evfolyam-->evfolyamJel tesztelendő)
	    $q = "SELECT targyId, targyNev, evfolyam, evfolyamJel, jegyTipus FROM zaroJegy LEFT JOIN targy USING (targyId)
		WHERE diakId=%u AND '%s' <= hivatalosDt AND hivatalosDt <= '%s' AND evfolyamJel IN (".implode(',', array_fill(0, count($diakEvfolyamJel), '%u')).")";
	    $v = $diakEvfolyamJel;
	    array_unshift($v, $diakId, $tolDt, $igDt);
	    $retZJ = db_query($q, array('fv' => 'getTargyByDiakId/zárójegyek', 'modul' => 'naplo_intezmeny', 'keyfield' => 'targyId', 'result' => 'assoc', 'values' => $v));
	    foreach ($retZJ as $targyId => $tAdat) {
		$osztalybaSorolva = false;
		if (isset($return['targyAdat'][$targyId])) $return['targyAdat'][$targyId]['jegyTipus'] = $tAdat['jegyTipus'];
		else $return['targyAdat'][$targyId] = $tAdat;
		foreach ($diakOsztalyIds as $osztalyId) {
		    if (in_array($targyId, $osztalyAdat[$osztalyId]['targy'])) {
			if (!in_array($targyId, $return['osztaly'][$osztalyId])) $return['osztaly'][$osztalyId][] = $targyId;
			$osztalybaSorolva = true;
		    }
		}
		if (!$osztalybaSorolva && !in_array($targyId, $return['egyeb'])) $return['egyeb'][] = $targyId;
	    }
	}

	// Tárgyak lekérdezése képzés alapján
	// - van-e a diáknak képzése?
	$kepzesIds = getKepzesByDiakId($diakId, array('dt'=>$dt, 'result' => 'idonly'));
	if (is_array($kepzesIds) && count($kepzesIds) > 0) {
	    foreach ($kepzesIds as $kepzesId) {
		$osztalyId = $kepzesOsztaly[$kepzesId];
		$evfolyamJel = $osztalyAdat[$osztalyId]['evfolyamJel'];
		$retK = getKepzesOraterv($kepzesId, array('arraymap' => array('evfolyamJel', 'targyId')));
		if (is_array($retK[$evfolyamJel])) foreach ($retK[$evfolyamJel] as $targyId => $tAdat) {
		    if ($targyId != '') {
			if (!in_array($targyId, $return['osztaly'][$osztalyId])) $return['osztaly'][$osztalyId][] = $targyId; //Ne üzenjünk hibát? Ezek szerint egy képzésben előírt tárgy nem szerepelt a tankörök között...
			if (is_array($return['targyAdat'][$targyId])) $return['targyAdat'][$targyId]['zaroKovetelmeny'] = $tAdat[ (count($tAdat)-1) ]['kovetelmeny']; // A képzés követelménye felülírja a tárgyét...
			else $return['targyAdat'][$targyId] = array_merge(getTargyById($targyId), array('evfolyamJel' => $evfolyamJel, 'zaroKovetelmeny' => $tAdat[ (count($tAdat)-1) ]['kovetelmeny'])); // Ha még nem szerepelt a tárgy, akkor vegyük fel az adatait...
		    }
		}
	    }
	}
	// Kellene még szűrni - követelmény, vagy jelenlét szerint, és le kellene kezelni a $result paramétert... (rev. 2251))
	if ($SET['result'] == '') return $return; // Ha nincs visszatérési forma megadva, akkor adjuk vissza az egész tömböt!
	
	if ($targySorrendNev != '' && is_array($return['targyAdat']) && count($return['targyAdat'])>0) {
	    // Rendezés... a $diakOsztalyIds[0] alapján!
	    $q = "SELECT targy.targyId FROM targy LEFT JOIN ".$tanevDb.".targySorszam
		    ON targy.targyId = targySorszam.targyId AND osztalyId=%u AND sorrendNev='%s'
		    WHERE targy.targyId IN (".implode(',', array_fill(0, count(array_keys($return['targyAdat'])), '%u')).") ORDER BY sorszam, targyNev";
	    $v = array_keys($return['targyAdat']);
	    array_unshift($v, $diakOsztalyIds[0], $targySorrendNev);
	    $sorrend = db_query($q, array('fv' => 'getTargyakByDiakId/targySorrend', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));	
	} else {
	    $sorrend = array_keys($return['targyAdat']);
	}

	// Szűrés
	$ret = array();
	if (isset($SET['osztalyId'])) $osztalyId = $SET['osztalyId']; // Ha van szűrés az osztályra...
	foreach ($sorrend as $targyId) {
	    $tAdat = $return['targyAdat'][$targyId];

	    if ( // szűrés az osztályra
		!isset($SET['osztalyId']) // nincs osztályra szűrés...
		|| (is_array($return['osztaly'][$osztalyId]) && in_array($targyId, $return['osztaly'][$osztalyId])) // vagy a szűrendő osztályhoz kapcsolódó tárgy
		|| (in_array($targyId, $return['egyeb']) && isset($tAdat['evfolyamJel']) && in_array($tAdat['evfolyamJel'], $diakEvfolyamJel)) // vagy nem kapcsolható más osztályhoz, évfolyama viszont megfelel a szűrendő évfolyamnak
	    ) {
		if (
		    $SET['filter'] != 'kovetelmeny' || // ha nincs követelmény szerinti szűrés...
		    ( // Szűrés a követelményre
			(isset($tAdat['evkoziKovetelmeny']) && $tAdat['evkoziKovetelmeny'] != 'nincs') // vagy van/lehet évközi értékelése ...
			|| (isset($tAdat['zaroKovetelmeny']) && $tAdat['zaroKovetelmeny'] != 'nincs' && $tAdat['zaroKovetelmeny'] != '')  // vagy év végi osztályzata...
			|| (isset($tAdat['jegyTipus']))						       // vagy van már beírva osztályzata
		    )
		) { // Megfelelő adatforma kialakítása
		    if ($SET['result'] == 'idonly') $ret[] = $targyId;
		    elseif ($SET['result'] == 'indexed') $ret[] = $tAdat;
		    elseif ($SET['result'] == 'assoc') $ret[$targyId] = $tAdat;
		}
	    }
	}
	
	return $ret;

    }

    function getTargyakByDiakIds($diakIds, $szemeszterAdat, $osztalyId, $sorrendNev, $SET = array('result' => 'indexed', 'keyfield' => null,'csakOratervi'=>true,'filter'=>'kovetelmeny')) {
    /*
	A függvény lekérdezi az összes diák tárgyait a share/targy/getTargyakByDiakId függvénnyel,
	majd ezen id-k alapján kérdezi le a rendezett tárgy listát - névvel és egyéb adatokkal indexelt tömbként.
    */

	if ( !isset($SET['csakOratervi']) ) $SET['csakOratervi'] = true;
	if ( !isset($SET['filter']) ) $SET['filter'] = 'kovetelmeny';
	if ( !isset($SET['result']) ) $SET['result'] = 'indexed';

	if ($szemeszterAdat['tanev'] == __TANEV) $tanevDb = __TANEVDBNEV;
	else $tanevDb = tanevDbNev(__INTEZMENY, $szemeszterAdat['tanev']);

	$targyIds = $tmp = array();
	foreach ($diakIds as $key => $diakId) {
	    $ret = getTargyakByDiakId($diakId, array('osztalyId' => $osztalyId, 'result' => 'idonly', 'csakOratervi' => $SET['csakOratervi'], 'filter' => $SET['filter'],
		    'tanev' => $szemeszterAdat['tanev'], 'tolDt' => $szemeszterAdat['kezdesDt'], 'igDt' => $szemeszterAdat['zarasDt']));
	    if (is_array($ret)) {
		$targyIds = array_unique(array_merge($tmp, $ret));
		$tmp = $targyIds;
	    }
	}
	if (is_array($targyIds) && count($targyIds)>0) {
	    $q = "SELECT kirTargyId,targy.targyId AS targyId, IF(targyRovidNev='' OR targyRovidNev IS NULL,targyNev,targyRovidNev) as targyRovidNev, targyNev, sorszam, targy.zaroKovetelmeny 
		FROM targy LEFT JOIN ".$tanevDb.".targySorszam
		ON targy.targyId = targySorszam.targyId AND osztalyId=%u AND sorrendNev='%s'
		WHERE targy.targyId IN (".implode(',', array_fill(0, count($targyIds), '%u')).")
		ORDER BY sorszam,targyNev";
		$v = mayor_array_join(array($osztalyId, $sorrendNev,), $targyIds);
		return db_query($q, array('fv' => 'getTargyakByDiakIds', 'modul' => 'naplo_intezmeny', 'result' => $SET['result'], 'keyfield' => $SET['keyfield'], 'values' => $v));	
	} else {
	    return false;
	}
    }

    function getTargyIdsByTanarId($tanarId) {

	$q1 = "SELECT targyId FROM kepesitesTargy LEFT JOIN tanarKepesites USING (kepesitesId) WHERE tanarId=%u";
	$q2 = "SELECT targyId FROM targy LEFT JOIN mkTanar USING (mkId) WHERE tanarId=%u";
	$q = "($q1) UNION DISTINCT ($q2)";

	return db_query($q, array('fv'=>'getTargyIdsByTanarId','modul'=>'naplo_intezmeny','result'=>'idonly','values'=>array($tanarId, $tanarId)));

    }

    function getMagatartas($SET = array('csakId' => true, 'result' => 'idonly')) { // Nekem szimpatikusabb a result paraméter... [bb] :)
	if ($SET['csakId']===true || $SET['result'] == 'idonly') { $w='targyId'; $r='idonly'; } 
	elseif ($SET['result'] == 'value') { $w ='targyId'; $r='value'; }
	else {$w='*';$r='indexed'; }
	$q = "SELECT $w FROM targy WHERE targyJelleg='magatartás'";
	$ret=db_query($q, array('fv' => 'getMagatartas', 'modul' => 'naplo_intezmeny', 'result' => $r));
	if (count($ret)==0) $_SESSION['alert'][]='message:nincs_targy:magatartás';
	return $ret;
    }
    function getSzorgalom($SET = array('csakId' => true, 'result' => 'idonly')) {
	if ($SET['csakId']===true || $SET['result'] == 'idonly') { $w='targyId'; $r='idonly'; } 
	elseif ($SET['result'] == 'value') { $w ='targyId'; $r='value'; }
	else {$w='*';$r='indexed'; }
	$q = "SELECT $w FROM targy WHERE targyJelleg='szorgalom'";
	$ret=db_query($q, array('fv' => 'getSzorgalom', 'modul' => 'naplo_intezmeny', 'result' => $r));
	if (count($ret)==0) $_SESSION['alert'][]='message:nincs_targy:szorgalom';
	return $ret;
    }
    function getOsztalyfonoki($SET = array('csakId' => true, 'result' => 'idonly')) {
	if ($SET['csakId']===true || 'result' == 'idonly') { $w='targyId'; $r='idonly'; } 
	elseif ($SET['result'] == 'value') { $w ='targyId'; $r='value'; }
	else {$w='*';$r='indexed'; }
	// még nincs "osztályfőnöki" tárgy jelleg - de kéne, nem?
	$q = "SELECT $w FROM targy WHERE targyJelleg='osztályfőnöki' or targyNev = 'osztályfőnöki'";
	$ret=db_query($q, array('fv' => 'getSzorgalom', 'modul' => 'naplo_intezmeny', 'result' => $r));
	if (count($ret)==0) $_SESSION['alert'][]='message:nincs_targy:szorgalom';
	return $ret;
    }


    function getKirTargyak() {                                                                                                                                                                         
	$q = "SELECT * FROM kirTargy";
	return db_query($q, array('fv'=>'getKirTargyak','modul'=>'naplo_base','result'=>'indexed'));
    }

    function getTargyTargy($SET=array()) {
	
	$q = "SELECT * FROM targyTargy";
	$r = db_query($q, array('fv'=>'getTargyTargy','modul'=>'naplo_intezmeny','result'=>'indexed'));
	//$R = reindex($r,array('foTargyId'));
	for ($i=0; $i<count($r); $i++) {
	    $R['FOal'][$r[$i]['foTargyId']][] = $r[$i]['alTargyId'];
	    $R['alFO'][$r[$i]['alTargyId']][] = $r[$i]['foTargyId'];
	}
	return $R;
    }

    function getTargyNevByTargyId($targyId) {
	$q = "SELECT targyNev FROM targy WHERE targyId=%u";
	$v = array(intval($targyId));
	return $r = db_query($q, array('fv'=>'getTargyNev','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));
    }

?>
