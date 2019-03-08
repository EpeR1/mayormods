<?php
/*
    Module: naplo
*/

    if (file_exists("lang/$lang/module-naplo/share/osztalyzatok.php")) {
        require_once("lang/$lang/module-naplo/share/osztalyzatok.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/module-naplo/share/osztalyzatok.php')) {
        require_once('lang/'._DEFAULT_LANG.'/module-naplo/share/osztalyzatok.php');
    }

    /* Követelmény */
global $KOVETELMENY;

// 2011. évi CXC. törvény 54. § (2) a)
if (!isset($KOVETELMENY['jegy']) || !is_array($KOVETELMENY['jegy'])) {
    $KOVETELMENY['jegy']= array(
	'értékek' => array('1.0','2.0','3.0','4.0','5.0'),
	'sikertelen' => array('1.0'),
	'átlagolható' => true,
	'1.0' => array('rovid' => '1',   'hivatalos' => 'ELEGTELEN'),
	'2.0' => array('rovid' => '2',   'hivatalos' => 'ELEGSEGES', 'megjegyzes' => 'figyelmeztető'),
	'3.0' => array('rovid' => '3',   'hivatalos' => 'KOZEPES'),
	'4.0' => array('rovid' => '4',   'hivatalos' => 'JO',),
	'5.0' => array('rovid' => '5',   'hivatalos' => 'JELES', 'megjegyzes' => 'dicséret'),
    );
}
// 2011. évi CXC. törvény 54. § (2) b)
if (!isset($KOVETELMENY['magatartás']) || !is_array($KOVETELMENY['magatartás'])) {
    $KOVETELMENY['magatartás']=array(
	'értékek' => array('2.0','3.0','4.0','5.0'),
	'sikertelen' => array(),
	'átlagolható' => true,
	'2.0' => array('rovid' => '2', 'hivatalos' => 'ROSSZ'),
	'3.0' => array('rovid' => '3', 'hivatalos' => 'VALTOZO'),
	'4.0' => array('rovid' => '4', 'hivatalos' => 'JO'),
	'5.0' => array('rovid' => '5', 'hivatalos' => 'PELDAS'),
    );
}
// 2011. évi CXC. törvény 54. § (2) c)
if (!isset($KOVETELMENY['szorgalom']) || !is_array($KOVETELMENY['szorgalom'])) {
    $KOVETELMENY['szorgalom']=array(
	'értékek' => array('2.0','3.0','4.0','5.0'),
	'sikertelen' => array(),
	'átlagolható' => true,
	'2.0' => array('rovid' => '2', 'hivatalos' => 'HANYAG'),
	'3.0' => array('rovid' => '3', 'hivatalos' => 'VALTOZO'),
	'4.0' => array('rovid' => '4', 'hivatalos' => 'JO'),
	'5.0' => array('rovid' => '5', 'hivatalos' => 'PELDAS'),
    );
}
// 2011. évi CXC. törvény 54. § (3)
if (!isset($KOVETELMENY['négyszintű (szöveges minősítés)']) || !is_array($KOVETELMENY['négyszintű (szöveges minősítés)'])) {
    $KOVETELMENY['négyszintű (szöveges minősítés)']= array(
        'értékek' => array('1.0','2.0','3.0','4.0'),
	'sikertelen' => array('1.0'),
	'átlagolható' => false,
        '1.0' => array('rovid' => 'FSZ',  'hivatalos' => 'FELZARKOZTATASRA_SZORUL'),
        '2.0' => array('rovid' => 'MFT',    'hivatalos' => 'MEGFELELOEN_TELJESITETT'),
        '3.0' => array('rovid' => 'JT',   'hivatalos' => 'JOL_TELJESITETT'),
        '4.0' => array('rovid' => 'KT',   'hivatalos' => 'KIVALOAN_TELJESITETT'),
    );
}
// 2011. évi CXC. törvény
// 6. § (4) Az érettségi bizonyítvány kiadásának feltétele ötven óra közösségi szolgálat elvégzésének igazolása. A felnőttoktatás keretében szervezett érettségi vizsga esetében közösségi szolgálat végzésének igazolása nélkül is meg lehet kezdeni az érettségi vizsgát. A sajátos nevelési igényű tanulók esetében a szakértői bizottság ez irányú javaslata alapján a közösségi szolgálat mellőzhető.
// 15. közösségi szolgálat: szociális, környezetvédelmi, a tanuló helyi közösségének javát szolgáló, szervezett keretek között folytatott, anyagi érdektől független, egyéni vagy csoportos tevékenység és annak pedagógiai feldolgozása,
if (!isset($KOVETELMENY['teljesített óra']) || !is_array($KOVETELMENY['teljesített óra'])) {

    $KOVETELMENY['teljesített óra']['sikertelen']= array(); // nem kötelező!
    $KOVETELMENY['teljesített óra']['átlagolható']= false;
    $KOVETELMENY['teljesített óra']['összeadható']= true;
    for ($i=1; $i<=100; $i++) {
	$_ertek = ($i%2 == 0) ? floor($i/2).'.0':floor($i/2).'.5';
	$_mutat = ($i%2 == 0) ? floor($i/2):floor($i/2).',5';
	$KOVETELMENY['teljesített óra']['értékek'][] = $_ertek;
	$KOVETELMENY['teljesített óra'][$_ertek] = array('rovid'=>$_mutat.'ó', 'hivatalos'=>$_mutat.' óra');
    }

}
// 2011. évi CXC. törvény 54. § (4)
/*
    A második évfolyam végén és a magasabb évfolyamokon félévkor és év végén a tanuló értékelésére - jóváhagyott
    kerettanterv vagy az oktatásért felelős miniszter engedélyével - az iskola pedagógiai programja a (2) bekezdésben
    meghatározottaktól eltérő jelölés, szöveges értékelés alkalmazását is előírhatja.
*/
if (!isset($KOVETELMENY['féljegy']) || !is_array($KOVETELMENY['féljegy'])) {
    $KOVETELMENY['féljegy']= array(
	'értékek' => array('1.0','1.5','2.0','2.5','3.0','3.5','4.0','4.5','5.0'),
	'sikertelen' => array('1.0'),
	'átlagolható' => true,
	'1.0' => array('rovid' => '1', 'hivatalos' => 'ELEGTELEN'),
	'1.5' => array('rovid' => '1/2', 'hivatalos' => '1/2'),
	'2.0' => array('rovid' => '2',   'hivatalos' => 'ELEGSEGES', 'megjegyzes' => 'figyelmeztető'),
	'2.5' => array('rovid' => '2/3', 'hivatalos' => '2/3'),
	'3.0' => array('rovid' => '3',   'hivatalos' => 'KOZEPES'),
	'3.5' => array('rovid' => '3/4', 'hivatalos' => '3/4'),
	'4.0' => array('rovid' => '4',   'hivatalos' => 'JO',),
	'4.5' => array('rovid' => '4/5', 'hivatalos' => '4/5'),
	'5.0' => array('rovid' => '5',   'hivatalos' => 'JELES', 'megjegyzes' => 'dicséret'),
    );
}
if (!isset($KOVETELMENY['százalékos']) || !is_array($KOVETELMENY['százalékos'])) {
    for ($i=0; $i<=100; $i++) {
	$_ertek = $i.'.0';
	if ($i<20) $KOVETELMENY['százalékos']['sikertelen'][] = $_ertek;
	$KOVETELMENY['százalékos']['értékek'][] = $_ertek;
	$KOVETELMENY['százalékos'][$_ertek] = array('rovid'=>$i.'%', 'hivatalos'=>$i.'%');
    }
}
if (!isset($KOVETELMENY['aláírás']) || !is_array($KOVETELMENY['aláírás'])) {
    $KOVETELMENY['aláírás']=array(
	'értékek' => array('1.0','2.0'),
	'sikertelen' => array('1.0'),
	'átlagolható' => false,
	'1.0' => array('rovid' => '-', 'hivatalos' => 'MEGTAGADVA'),
	'2.0' => array('rovid' => 'AI', 'hivatalos' => 'ALAIRVA'),
    );
}
if (!isset($KOVETELMENY['háromszintű']) || !is_array($KOVETELMENY['háromszintű'])) {
    $KOVETELMENY['háromszintű']=array(
	'értékek' => array('1.0','2.0','3.0'),
	'sikertelen' => array('1.0'),
	'átlagolható' => false,
	'1.0' => array('rovid' => 'NFM', 'hivatalos' => 'NEMFELELTMEG'),
	'2.0' => array('rovid' => 'MF', 'hivatalos' => 'MEGFELELT'),
	'3.0' => array('rovid' => 'KMF', 'hivatalos' => 'KIVALOANMEGFELELT'),
    );
}
if (!isset($KOVETELMENY['nem értékelhető']) || !is_array($KOVETELMENY['nem értékelhető'])) {
    $KOVETELMENY['nem értékelhető']=array(
	'értékek' => array('1.0','2.0','3.0'),
	'sikertelen' => array('2.0','3.0'),
	'átlagolható' => false,
	'1.0' => array('rovid' => 'Fm.', 'hivatalos' => 'FELMENTETT'),
	'2.0' => array('rovid' => 'N.O.', 'hivatalos' => 'NEM_OSZTALYOZHATO'),
	'3.0' => array('rovid' => 'N.J.M.', 'hivatalos' => 'NEM_JELENT_MEG')
    );
}


    if (is_array($KOVETELMENY)) {
	foreach ($KOVETELMENY as $k1 => $T) {
	    foreach ($T as $key => $val) {
		if (defined('_'.$val['hivatalos'])) $KOVETELMENY[$k1][$key]['hivatalos'] = constant('_'.$val['hivatalos']);
		if (defined('_'.$val['rovid'])) $KOVETELMENY[$k1][$key]['rovid'] = constant('_'.$val['rovid']);
	    }
	    if (!isset($KOVETELMENY[$k1]['átlagolható'])) $KOVETELMENY[$k1]['átlagolható'] = true;
	    if (!isset($KOVETELMENY[$k1]['összeadható'])) $KOVETELMENY[$k1]['összeadható'] = false;
	}
    }
    $jegyTipusok = array(
	_TOROLT, _KIS_JEGY, _NORMAL_JEGY, _DOLGOZAT, _TEMAZARO, _VIZSGAJEGY
    );

    $bizonyitvanyJegyzetek = array(
        'dicséret' => _JEGYZET_DICSERET,
        'figyelmeztető' => _JEGYZET_FIGYELMEZTETO,
	'nyelvvizsga' => _JEGYZET_NYELVVIZSGA
    );

    $bizonyitvanyMegjegyzesek = array(
	'figyelmeztető' => _FIGYELMEZTETO,
	'nyelvvizsga' => _NYELVVIZSGA,
	'dicséret' => _DICSERET,
	'2.0' => array('figyelmeztető'),
	'5.0' => array('dicséret','nyelvvizsga')
    );

    // A kitűnő megállapításához csak az átlagot vesszük figyelembe
    if (!defined('_KITUNO_ATLAG')) define('_KITUNO_ATLAG', 5.0);
    // jelesrendű, aki a megadott átlag fölött van és nincs a megadott jegynél gyengébb eredménye egyik tárgyból sem
    if (!defined('_JELES_ATLAG')) define('_JELES_ATLAG', 4.75);
    if (!defined('_JELES_LEGGYENGEBB_JEGY')) define('_JELES_LEGGYENGEBB_JEGY', 3);


    /////////////////////////////////////////////////////////////////////

    // Visszatérési értékek
    // ----------------------------

    //	TOMB[ diakId ] [ targyId ] [ index 0..(n-1) ] VAGY $SET['arraymap'] szerinti

    // !! Ez szigorúan a $szAdat['kezdesDt'] és $szAdat['zarasDt'] között szerzett osztályzatokat adja vissza!

    /////////////////////////////////////////////////////////////////////
/*
// Törlendő!!
    function getDiakZarojegyekByEvfolyam($diakIds, $evfolyam='', $szAdat='', $SET = array('arraymap'=>null)) { // csak evfolyam, evfolyamJelet nem kezel!
	if (count($diakIds)<1) return false;
	elseif (!is_array($diakIds)) $diakIds = array($diakIds);
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('diakId','targyId');
	$values = $diakIds;
	if (is_array($szAdat) && $szAdat['szemeszter']!='') {
	    $qSZ =  " AND evfolyam=%u AND '%s' <= hivatalosDt AND hivatalosDt <= '%s' AND felev=%u"; // eredetileg felev>=%u (???)
	    $values[] = $evfolyam;
	    $values[] = $szAdat['kezdesDt'];
	    $values[] = $szAdat['zarasDt'];
	    $values[] = $szAdat['szemeszter'];
	} elseif ($evfolyam!='') {
	    $qSZ = " AND evfolyam=%u ";
	    $values[] = $evfolyam;
	}
        $q = "SELECT * FROM zaroJegy WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") $qSZ ORDER BY felev, hivatalosDt";
        $r = db_query($q, array('fv' => 'getDiakZarojegyekbyEvfolyam', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $values));
	$ret = reindex($r,$SET['arraymap']);
        return $ret;
    }
*/
    // Az előző függvény évfolyamJel-es változata + a félévkor záruló tárgyak jegyei
    function getDiakZarojegyekByEvfolyamJel($diakIds, $evfolyamJel='', $szAdat='', $SET = array('arraymap'=>null)) {
	if (count($diakIds)<1) return false;
	elseif (!is_array($diakIds)) $diakIds = array($diakIds);
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('diakId','targyId');
	$values = $diakIds;
	$evfolyam = evfolyamJel2Evfolyam($evfolyamJel);
	if (is_array($szAdat) && $szAdat['szemeszter']!='') {
	    $qSZ =  " AND (evfolyamJel='%s' OR (evfolyamJel IS NULL AND evfolyam=%u)) AND '%s' <= hivatalosDt AND hivatalosDt <= '%s' AND felev=%u";
	    $values[] = $evfolyamJel;
	    $values[] = $evfolyam;
	    $values[] = $szAdat['kezdesDt'];
	    $values[] = $szAdat['zarasDt'];
	    $values[] = $szAdat['szemeszter'];
	} elseif ($evfolyamJel!='') {
	    $qSZ = " AND (evfolyamJel='%s' OR (evfolyamJel IS NULL AND evfolyam=%u)) ";
	    $values[] = $evfolyamJel;
	    $values[] = $evfolyam;
	}
        $q = "SELECT * FROM zaroJegy WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") 
		    $qSZ ORDER BY felev, hivatalosDt";
        $r = db_query($q, array('fv' => 'getDiakZarojegyekbyEvfolyamJel', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $values));
	$arraymap = $SET['arraymap'];

	// A félévkor lezáruló tárgyak félévi zárójegyei:
	if ($SET['felevivel'] == true && (!is_array($szAdat) || $szAdat['szemeszter'] == 2)) {
	    $SET['arraymap'] = 'indexed';
	    $r2 = getDiakFeleviZarojegyekByEvfolyamJel($diakIds, $evfolyamJel, $szAdat, $SET);
	    for ($i=0; $i<count($r2); $i++) $r[] = $r2[$i];
	}

	$ret = reindex($r,$arraymap);
        return $ret;
    }

    function getDiakFeleviZarojegyekByEvfolyamJel($diakIds, $evfolyamJel='', $szAdat='', $SET = array('arraymap'=>null)) {
	if (count($diakIds)<1) return false;
	elseif (!is_array($diakIds)) $diakIds = array($diakIds);
	$values = $diakIds;
	// A képzésenként a félévkor lezáruló tárgyak lekérdezése az adott évfolyamon
	$q = "SELECT zaroJegy.* FROM zaroJegy LEFT JOIN kepzesDiak USING (diakId) 
		WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).")
		AND felev=1";
	if ($evfolyamJel != '') { $q .= " AND evfolyamJel='%s'"; $values[] = $evfolyamJel; }
	$q .= " AND CONCAT_WS('-',kepzesId,targyId,evfolyamJel) IN (
		    SELECT CONCAT_WS('-',kepzesId, targyId, evfolyamJel) FROM kepzesOraterv GROUP BY kepzesId, targyId, evfolyamJel HAVING MAX(szemeszter)=1
		) 
		ORDER BY felev, hivatalosDt";
        $r = db_query($q, array('fv' => 'getDiakFeleviZarojegyekbyEvfolyamJel', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $values));
	if ($SET['arraymap'] == 'indexed') $ret = $r;
	else {
	    if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('diakId','targyId');
	    $ret = reindex($r,$SET['arraymap']);
	}
        return $ret;
    }


    function getDiakZarojegyek($diakIds, $tanev='', $szemeszter='', $SET = array('arraymap'=>null)) {
	if (count($diakIds)<1) return false;
	elseif (!is_array($diakIds)) $diakIds = array($diakIds);
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('diakId','targyId');
	$values = $diakIds;
	if ($szemeszter!='') {
	    $qSZ =  " AND tanev=%u AND szemeszter=%u";
	    $values[] = $tanev;
	    $values[] = $szemeszter;
	} elseif ($tanev!='') {
	    $qSZ = " AND tanev=%u ";
	    $values[] = $tanev;
	}
        $q = "SELECT * FROM zaroJegy
		    LEFT JOIN szemeszter ON kezdesDt<=hivatalosDt AND hivatalosDt<=zarasDt
		    WHERE diakId IN (".implode(',', array_fill(0, count($diakIds), '%u')).") ".$qSZ;
        $r = db_query($q, array('fv' => 'getDiakZarojegyek', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $values));
	$ret = reindex($r,$SET['arraymap']);
        return $ret;
    }

    function getDiakokZarojegyeiByTargyId($DIAKIDS, $targyId, $SET = array('tanev'=>__TANEV, 'arraymap'=>null)) {

	global $_TANEV;
	// default
	$BIZ = array();
	if ($SET['tanev']=='') $tanev = __TANEV; else $tanev = $SET['tanev'];
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('diakId','evfolyamJel','szemeszter');

	// walk
	if (count($DIAKIDS) > 0) {
            $q = "SELECT *, IF(evfolyamJel IS NULL,evfolyam,evfolyamJel) AS evfolyamStr FROM ".__INTEZMENYDBNEV.".zaroJegy
		    LEFT JOIN szemeszter ON kezdesDt <= hivatalosDt AND hivatalosDt <= zarasDt
                    WHERE targyId=%u
                    AND tanev=%u
                    AND diakId IN (".implode(",", $DIAKIDS).")";
            array_unshift($DIAKIDS, $targyId, $tanev);
            $ret = db_query($q, array('fv' => 'getDiakokZarojegyeiByTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $DIAKIDS));
	    $BIZ = reindex($ret,$SET['arraymap']);
	}
	return $BIZ;
    }

    function getDiakokVizsgajegyeiByTargyId($DIAKIDS, $targyId, $SET = array('arraymap'=>null)) {
	// default
	$BIZ = array();
	if (!is_array($SET['arraymap'])) $SET['arraymap'] = array('zaroJegyId');

	// walk
	if (count($DIAKIDS) > 0) {
            $q = "SELECT *, IF(evfolyamJel IS NULL,evfolyam,evfolyamJel) AS evfolyamStr FROM vizsga
                    WHERE vizsga.targyId=%u
                    AND vizsga.diakId IN (".implode(",", $DIAKIDS).")";
            array_unshift($DIAKIDS, $targyId);
            $ret = db_query($q, array('fv' => 'getDiakokVizsgajegyeiByTargyId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $DIAKIDS));
	    $BIZ = reindex($ret,$SET['arraymap']);
	}
	return $BIZ;
    }

    function getZaroJegyAdat($zaroJegyId) {
	$q = "SELECT * FROM zaroJegy WHERE zaroJegyId=%u";
	$v = array($zaroJegyId);
	return $r = db_query($q, array('fv' => 'getZaroJegyId', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v));
    }
    function getJegyAdat($jegyId) {
	$q = "SELECT * FROM jegy 
	    LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) 
	    LEFT JOIN ".__INTEZMENYDBNEV.".tankorTipus USING (tankorTipusId) 
	    LEFT JOIN dolgozat USING (dolgozatId) 
	    WHERE jegyId=%u";
	$v = array($jegyId);
	return $r = db_query($q, array('fv' => 'getJegyId', 'modul' => 'naplo', 'result' => 'record', 'values' => $v));
    }

?>
