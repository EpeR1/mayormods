<?php

require_once('include/modules/naplo/share/kepzes.php');

function _evfolyam($tanev = __TANEV) {
    // hasonlóan az evfolyamJel, hez, de csak a rendezéshez használjuk
    $tanev = intval($tanev);
    $evfSorsz = $tanev.'-kezdoTanev+kezdoEvfolyamSorszam';
    return "if (
		(osztaly.vegzoTanev>=$tanev AND osztaly.kezdoTanev<=$tanev),
		CONVERT(REPLACE(SUBSTRING(SUBSTRING_INDEX(evfolyamJelek, ',', ".$evfSorsz."),LENGTH(SUBSTRING_INDEX(evfolyamJelek, ',', ".$evfSorsz."-1))+1),',',''),UNSIGNED),
		NULL
	    )";
}

function _evfolyamJel($tanev = __TANEV) {
    $tanev = intval($tanev);
    $evfSorsz = $tanev.'-kezdoTanev+kezdoEvfolyamSorszam';
    return "if (
		(osztaly.vegzoTanev>=$tanev AND osztaly.kezdoTanev<=$tanev),
		REPLACE(SUBSTRING(SUBSTRING_INDEX(evfolyamJelek, ',', ".$evfSorsz."),LENGTH(SUBSTRING_INDEX(evfolyamJelek, ',', ".$evfSorsz."-1))+1),',',''),
		NULL
	    )";
}

function _osztalyJel($tanev = __TANEV) {
    $tanev = intval($tanev);
    $evfSorsz = $tanev.'-kezdoTanev+kezdoEvfolyamSorszam';
    return "if (
		(osztaly.osztalyJellegId IS NOT NULL AND osztaly.vegzoTanev>=$tanev AND osztaly.kezdoTanev<=$tanev),
		CONCAT(REPLACE(SUBSTRING(SUBSTRING_INDEX(evfolyamJelek, ',', ".$evfSorsz."),LENGTH(SUBSTRING_INDEX(evfolyamJelek, ',', ".$evfSorsz."-1))+1),',',''),'.',osztaly.jel),
		CONCAT(osztaly.kezdoTanev,'/',osztaly.vegzoTanev,'.',osztaly.jel)
	    )";
}

function getEvfolyam($osztalyId, $tanev, $osztalyAdat = null, $olr = null) {
    return null;
}

function getEvfolyamJel($osztalyId, $tanev=__TANEV, $osztalyAdat = null, $_osztalyJellel = false, $olr=null) {

    if (is_array($osztalyAdat)) {
	$ret = $osztalyAdat;
    } else {
	// is_resource mysqli esetén nem jó (object)
	if ($olr) $lr = $olr;
	else $lr = db_connect('naplo_intezmeny');
	$ret = getOsztalyAdat($osztalyId, $tanev, $lr);
	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);
    }
    if ($ret['kezdoTanev']<=$tanev && $tanev<=$ret['vegzoTanev']) {
	$evfSorsz = $tanev-$ret['kezdoTanev']+$ret['kezdoEvfolyamSorszam']; // ez 1 bázisú sorszám!!
	$evfolyamJel = $ret['evfolyamJelek'][$evfSorsz-1];
    } else {
	if ($_osztalyJellel === true) $evfolyamJel = $ret['kezdoTanev'].'/'.$ret['vegzoTanev'];
	else $evfolyamJel = false;
    }

    if ($_osztalyJellel === true) return $evfolyamJel.'.'.$ret['jel'];
    else return $evfolyamJel;
}
/*
 * A kompatibilitás miatt az evfolyamJel-ből evfolyam-ot generálunk
 */
function evfolyamJel2Evfolyam($evfolyamJel) {
    switch ($evfolyamJel) {
	case '1':
	    return 1; break;
	case '2':
	    return 2; break;
	case '3':
	    return 3; break;
	case '4':
	    return 4; break;
	case '5':
	    return 5; break;
	case '6':
	    return 6; break;
	case '7':
	    return 7; break;
	case '8':
	case 'H2':
	case 'H/II/1':
	case 'H/II/2':
	case '1/8':
	    return 8; break;
	case '9':
	case '9N':
	case '9/N':
	case '9Ny':
	case '9/Ny':
	case '9Kny':
	case '9/Kny':
	case '9AJTP':
	case '9/AJTP':
	case '9AJKP':
	case '9/AJKP':
	case 'H1':
	case 'H/I':
	case '2/9':
	case '1/9':
	    return 9; break;
	case '10':
	case '3/10':
	case '2/10':
	    return 10; break;
	case '11':
	case '11/Ny':
	case '3/11':
	case '1/11':
	    return 11; break;
	case '12':
	case '2/12':
	    return 12; break;
	case '13':
	case '1/13':
	case '5/13':
	case 'Szé/12/1':
	    return 13; break;
	case '14':
	case '2/14':
	case 'Szé/12/2':
	    return 14; break;
	case '15':
	    return 15; break;
	default:
	    return null;
    }
}

function getOsztalyJel($osztalyId, $tanev, $osztalyAdat = null, $olr=null) {
    return getEvfolyamJel($osztalyId, $tanev, $osztalyAdat, $_osztalyJellel=true, $olr);
}

global $_EVFOLYAMJEL_BETUVEL;
$_EVFOLYAMJEL_BETUVEL = array(
    '1' => 'első',
    '2' => 'második',
    '3' => 'harmadik',
    '4' => 'negyedik',
    '5' => 'ötödik',
    '6' => 'hatodik',
    '7' => 'hetedik',
    '8' => 'nyolcadik',
    'H1' => 'Híd I. program',
    'H/I' => 'Híd I. program',
    'H2/1' => 'Híd II. program 1. évfolyam',
    'H/II/1' => 'Híd II. program 1. évfolyam',
    'H2/2' => 'Híd II. program 2. évfolyam',
    'H/II/2' => 'Híd II. program 2. évfolyam',
    '9N' => 'nemzetiségi előkészítő',
    '9/N' => 'nemzetiségi előkészítő',
    '9Ny' => 'nyelvi előkészítő',
    '9/Ny' => 'nyelvi előkészítő',
    '9Kny' => 'két tanítási nyelvű előkészítő',
    '9/Kny' => 'két tanítási nyelvű előkészítő',
    '9AJTP' => 'Arany János Tehetséggondozó Program',
    '9/AJTP' => 'Arany János Tehetséggondozó Program',
    '9AJKP' => 'Arany János Kollégiumi Program',
    '9/AJKP' => 'Arany János Kollégiumi Program',
    '9' => 'kilencedik',
    '10' => 'tizedik',
    '11/Ny' => 'nyelvi előkészítő',
    '11' => 'tizenegyedik',
    '12' => 'tizenkettedik',
    '13' => 'tizenharmadik',
    '14' => 'tizennegyedik',
    '15' => 'tizenötödik',
    '1/8' => '1/8 szakképző évfolyam',
    '2/9' => '2/9 szakképző évfolyam',
    '3/10' => '3/10 szakképző évfolyam',
    '1/9' => '1/9 szakképző évfolyam',
    '2/10' => '2/10 szakképző évfolyam',
    '3/11' => '3/11 szakképző évfolyam',
    '1/11' => '1/11 szakképző évfolyam',
    '2/12' => '1/12 szakképző évfolyam',
    '1/13' => '1/13 szakképző évfolyam',
    '2/14' => '2/14 szakképző évfolyam',
    '5/13' => '5/13 szakképző évfolyam',
    'Szé/12/1' => 'érettségire felkészítő 1. évfolyam',
    'Szé/12/2' => 'érettségire felkészítő 2. évfolyam',
);
/*
    KNT. 6. melléklete: engedélyezett, egyházi, 6-8 évf. gimnázium, 
    NKT. 27. § (5) - tehetséggondozás+felzárkóztatás - 2 óra/hét/osztály
    NKT. 27. § (6) - 1-4 évf. felzárkóztatás: 2 óra/hét/fő
    NKT. 27. § (7) - SNI magántanul 10 óra/hét/fő
 */
global $_EVFOLYAM_ADAT;
$_EVFOLYAM_ADAT = array(
    // tanulóÓraszám => array(testnevelés nélkül, testnevelés)
    // osztályIdőkeret => array(engedélyezett, hittan, 6-8 gimn, nemzetiségi    // tehetséggondozás+felzárkóztatás + 2 óra/hét/osztály
    // létszám => array(min, max, átlag)
    '1' => array('tanulóÓraszám' => array(20,5), 'osztályIdőkeret' => array(52,1,0,2), 'létszám' => array(14,27,23)),
    '2' => array('tanulóÓraszám' => array(20,5), 'osztályIdőkeret' => array(52,1,0,2), 'létszám' => array(14,27,23)),
    '3' => array('tanulóÓraszám' => array(20,5), 'osztályIdőkeret' => array(52,1,0,2), 'létszám' => array(14,27,23)),
    '4' => array('tanulóÓraszám' => array(22,5), 'osztályIdőkeret' => array(55,1,0,2), 'létszám' => array(14,27,23)),
    '5' => array('tanulóÓraszám' => array(23,5), 'osztályIdőkeret' => array(51,1,2,2), 'létszám' => array(14,27,23)),
    '6' => array('tanulóÓraszám' => array(23,5), 'osztályIdőkeret' => array(51,1,2,2), 'létszám' => array(14,27,23)),
    '7' => array('tanulóÓraszám' => array(26,5), 'osztályIdőkeret' => array(56,1,2,2), 'létszám' => array(14,27,23)),
    '8' => array('tanulóÓraszám' => array(26,5), 'osztályIdőkeret' => array(56,1,2,2), 'létszám' => array(14,27,23)),
    'H1' 	=> array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(8,10,9)),
    'H/I' 	=> array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(8,10,9)),
    'H2/1' 	=> array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(8,10,9)),
    'H/II/1' 	=> array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(8,10,9)),
    'H2/2' 	=> array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(8,10,9)),
    'H/II/2' 	=> array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(8,10,9)),
    '9N'     => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)), // Nincs a mellékletben
    '9/N'    => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)),//??
    '9Ny'    => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)),
    '9/Ny'   => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)),
    '9Kny'   => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)), //??
    '9/Kny'  => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)), //??
    '9AJTP'  => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)), //??
    '9/AJTP' => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)), //??
    '9AJKP'  => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)), //??
    '9/AJKP' => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)),//??
    '9'  => array('tanulóÓraszám' => array(30,5), 'osztályIdőkeret' => array(57,2,2,3), 'létszám' => array(26,34,28)),
    '10' => array('tanulóÓraszám' => array(31,5), 'osztályIdőkeret' => array(57,2,2,3), 'létszám' => array(26,34,28)),
    '11/Ny'   => array('tanulóÓraszám' => array(25,5), 'osztályIdőkeret' => array(56,2,0,3), 'létszám' => array(26,34,28)),
    '11' => array('tanulóÓraszám' => array(30,5), 'osztályIdőkeret' => array(58,2,2,3), 'létszám' => array(26,34,28)),
    '12' => array('tanulóÓraszám' => array(30,5), 'osztályIdőkeret' => array(58,2,2,3), 'létszám' => array(26,34,28)),
    '13' => array('tanulóÓraszám' => array(30,5), 'osztályIdőkeret' => array(58,2,2,3), 'létszám' => array(26,34,28)), //??
    '14' => array('tanulóÓraszám' => array(30,5), 'osztályIdőkeret' => array(58,2,2,3), 'létszám' => array(26,34,28)), //??
    '15' => array('tanulóÓraszám' => array(30,5), 'osztályIdőkeret' => array(58,2,2,3), 'létszám' => array(26,34,28)), //??
    '1/8'  => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '2/9'  => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '3/10' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '1/9'  => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '2/10' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '3/11' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '1/11' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '2/12' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '1/13' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '2/14' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    '5/13' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(16,28,24)), //??
    'Szé/12/1' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(26,34,28)),
    'Szé/12/2' => array('tanulóÓraszám' => array(), 'osztályIdőkeret' => array(), 'létszám' => array(26,34,28)),
);

// használja a kepzes.php!!
define('_SQL_EVFOLYAMJEL_SORREND', "FIELD(evfolyamJel,'1','2','3','4','5','6','7','8',".
"'9N','9/N','9Ny','9/Ny','9Kny','9/Kny','9AJTP','9/AJTP','9AJKP','9/AJKP','9','10','11/Ny','11','12','13','14','15',"
."'H1','H/I','H2','H/II/1','H/II/2',"
."'1/8','2/9','3/10','1/9','2/10','3/11',"
."'1/11','2/12','1/13','2/14','3/15','4/16','5/13','Szé/12/1','Szé/12/2')");

function getEvfolyamJelek($SET = array('result'=>'indexed')) {
    $J = array(
'1','2','3','4','5','6','7','8',
'9N','9/N','9Ny','9/Ny','9Kny','9/Kny','9AJTP','9/AJTP','9AJKP','9/AJKP','9','10','11/Ny','11','12','13','14','15',
'H1','H/I','H2','H/II/1','H/II/2',
'1/8','2/9','3/10','1/9','2/10','3/11',
'1/11','2/12','1/13','2/14','3/15','4/16','5/13','Szé/12/1','Szé/12/2'
);
    if ($SET['result'] == 'idonly') return $J;
    $ret = array();
    foreach ($J as $evfolyamJel) $ret[] = array('evfolyamJel'=>$evfolyamJel);
    return $ret;
}

function getKovetkezoEvfolyamJel($evfolyamJel) {

    $q = "select distinct substring_index(substring_index(evfolyamJelek,'%s,',-1),',',1) 
	    from osztalyJelleg where evfolyamJelek like '%s' ',%%' or evfolyamJelek like '%%,' '%s' ',%%'"; // stringek egymásutánja MySQL szerint egymásután fűzést jelöl...
    $ret = db_query($q, array('fv'=>'getKovetkezoEvfolyamJel','modul'=>'naplo_base','result'=>'value','values'=>array($evfolyamJel, $evfolyamJel, $evfolyamJel)));
    if ($ret == '') {
	$_SESSION['alert'][] = 'info:getKovetkezoEvfolyamJel hiba:'.$evfolyamJel;
	return false;
    } else {
	return $ret;
    }
/*
    if (in_array($evfolyamJel, array('1','2','3','4','5','6','7','8','9','10','11','12','13','14'))) return $evfolyamJel+1;
    else if (in_array($evfolyamJel, array('11/Ny'))) return 11;
    else if (in_array($evfolyamJel, array('9N','9Ny','9Kny','9AJTP','9AJKP','H2'))) return 9;
    else if (in_array($evfolyamJel, array('9/N','9/Ny','9/Kny','9/AJTP','9/AJKP'))) return 9;
    else if (in_array($evfolyamJel, array('7N','7Ny'))) return 7;
    else if (in_array($evfolyamJel, array('7/N','7/Ny'))) return 7;
    else if (in_array($evfolyamJel, array('4N','4Ny'))) return 4;
    else if (in_array($evfolyamJel, array('4/N','4/Ny'))) return 4;
    else if ($evfolyamJel == 'H1' || $evfolyamJel == 'H/I') return 10;
    else if ($evfolyamJel == 'H/II/1') return 'H/II/2';
    else if ($evfolyamJel == 'Szé/12/1') return 'Szé/12/2';
    else if (in_array($evfolyamJel, array('1/8','2/9','1/9','2/10','1/11','2/12','1/13'))) {
	list($e, $m) = explode('/',$evfolyamJel);
	$e++; $m++; return $e.'/'.$m;
    }
    else {
	$_SESSION['alert'][] = 'info:getKovetkezoEvfolyamJel hiba:'.$evfolyamJel;
	return false;
    }
*/
}

/*
 * getEvfolyamAdatByDiakId
 *
 * return [ array('evfolyam', 'evfolyamJel') | false | array('evfolyam'=>array(), 'evfolyamJel'=>array())
 */
function getEvfolyamAdatByDiakId($diakId, $dt, $tanev, $csakHaEgyertelmu = true) {

	$OSZTALYOK = getDiakOsztalya($diakId,array('tanev'=>$tanev,'tolDt'=>$dt,'igDt'=>$dt));
	$ret = array('evfolyam'=>array(), 'evfolyamJel'=>array());
	foreach ($OSZTALYOK as $oAdat) {
	    $osztalyId = $oAdat['osztalyId'];
            $OA = getOsztalyAdat($osztalyId, $tanev);
	    $evfolyam = getEvfolyam($osztalyId, $tanev, $OA);
	    $evfolyamJel = getEvfolyamJel($osztalyId, $tanev, $OA);
	    if (!in_array($evfolyam, $ret['evfolyam'])) $ret['evfolyam'][] = $evfolyam;
	    if ($evfolyamJel != '' && !in_array($evfolyamJel, $ret['evfolyamJel'])) $ret['evfolyamJel'][] = $evfolyamJel;
	}
	if ($csakHaEgyertelmu===true) {
    	    if (count($ret['evfolyamJel'])===1 && count($ret['evfolyam']===1)) {
        	return array('evfolyam'=>$ret['evfolyam'][0], 'evfolyamJel'=>$ret['evfolyamJel'][0]);
    	    } else {
        	$_SESSION['alert'][] = '::nem tudom kitalálni az évfolyamot (db osztály: '.count($OSZTALYOK).", diakId: $diakId, tanev: $tanev)";
        	return false;
    	    }
	} else {
	    return $ret;
	}

}

//!!ITT!!
function getOsztalyIdByEvfolyamJel($evfolyamJel, $tanev = __TANEV) {

    if (is_array($evfolyamJel) && count($evfolyamJel) > 0) {
	$q = "SELECT osztalyId FROM osztaly LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) 
		    WHERE "._evfolyamJel($tanev)." IN (".implode(',', array_fill(0, count($evfolyamJel), "'%s'")).") 
		    ORDER BY osztalyId";
	return db_query($q, array('fv' => 'getOsztalyIdByEvfolyamJe', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $evfolyamJel));
    } else {
	return array();
    }
}

function getOsztalyIdByTankorIds($tankorIds, $SET = array('result' => 'indexed')) {
    $q = "SELECT tankorId,osztalyId FROM tankorOsztaly WHERE tankorId IN (".implode(',', array_fill(0, count($tankorIds), '%u')).")";
    return db_query($q, array('fv' => 'tankorokOsztalyi', 'modul' => 'naplo_intezmeny', 'result' => $SET['result'], 'values' => $tankorIds));
}

// Elavult, már nem használt függvény
// function checkOsztalyInTanev($tanev, $osztalyId='') {
// 
// 	$tanevDb = tanevDbNev(__INTEZMENY, $tanev);
// 	$lr = db_connect('naplo_intezmeny', array('fv' => 'checkOsztalyInTanev'));
// 	if (!$lr) return false;
// 	//$tlr = db_connect('naplo');
// 	
// 	// Ellenőrizzük, hogy van-e az adott tanévben jele...
// 	$OSZTALYOK = getOsztalyok($tanev);
// 	for ($i = 0; $i < count($OSZTALYOK); $i++) {
// 	    if ($OSZTALYOK[$i]['osztalyJel'] == '' && ($tanev-$OSZTALYOK[$i]['kezdoTanev']) >= 0) {
// //		$v = array($tanevDb, $OSZTALYOK[$i]['osztalyId'], genOsztalyJel($tanev,$OSZTALYOK[$i]));
// 		$v = array($tanevDb, $OSZTALYOK[$i]['osztalyId'], $OSZTALYOK[$i]['osztalyJel'], $OSZTALYOK[$i]['evfolyam'], $OSZTALYOK[$i]['evfolyamJel']);
// 		$q = "REPLACE INTO `%s`.osztalyNaplo (osztalyId,osztalyJel,evfolyam,evfolyamJel) VALUES (%u,'%s',%u,'%s')";
// 		db_query($q, array('fv' => 'checkOsztalyInTanev', 'modul' => 'naplo_intezmeny', 'values' => $v), $lr);
// 	    }
// 	}
// 	db_close($lr);
// 
// }

function getOsztalyok($tanev = '', $SET = array('result' => 'indexed', 'minden'=>false, 'telephelyId' => null, 'osztalyIds'=>null), $olr=null) {
	global $_TANEV,$mayorCache;

	if (!is_array($SET)) $SET = array('result' => 'indexed', 'minden'=>false, 'telephelyId' => null);

	if (!isset($SET['result'])) $SET['result'] = 'indexed';
	if (!isset($SET['minden'])) $SET['minden'] = false;
	if (!isset($SET['telephelyId'])) $SET['telephelyId'] = null;


	$key = __FUNCTION__.':'.md5($tanev.serialize($SET));
        if ($mayorCache->exists($key)) return $mayorCache->get($key);

	$W = array();
	if (is_array($SET['osztalyIds']) && count($SET['osztalyIds'])>0) {
	    $W[] = ' osztalyId IN ('.implode(',',$SET['osztalyIds']).') ';
	}

	if ($tanev == '' && defined('__TANEV')) $tanev = __TANEV;
	$telephelyId = readVariable($SET['telephelyId'], 'id');

	// is_resource mysqli esetén nem jó (object)
	if ($olr) $lr = $olr;
	else $lr = db_connect('naplo_intezmeny');

	// A tanév státuszának lekérdezése
	// REQUEST -- ezt a szemeszter fv könyvtár csinálja inkább
	if ($tanev == __TANEV) {
		$statusz = $_TANEV['statusz'];
	} else {
		$q ="SELECT statusz FROM szemeszter WHERE tanev='$tanev' LIMIT 1";
		$statusz = db_query($q, array('fv' => 'getOsztalyok', 'modul' => 'naplo_intezmeny', 'result' => 'value'), $lr);
	}
	// Az osztályok adatainak lekérdezése
	if ($SET['minden']!==true) {
	    if (isset($telephelyId)) {
		$W[] = "kezdoTanev <= %u AND vegzoTanev >= %u AND (telephelyId = %u OR telephelyId IS NULL)";
		$v = array($tanev, $tanev, $telephelyId);
	    } else {
		$W[] = "kezdoTanev <= %u AND vegzoTanev >= %u";
		$v = array($tanev, $tanev);
	    }
	} else { $v = array(); }
	if (count($W)>0) $WHERE = "WHERE ".implode(' AND ',$W);
	if ($statusz == 'tervezett') {
		// Nincs még tanév adatbázis --> csak az osztly tábla használható;
		$q = "SELECT `osztalyId`, `leiras`, `kezdoTanev`, `vegzoTanev`, `jel`, `kezdoEvfolyamSorszam`,"._osztalyJel($tanev)." AS `osztalyJel`, 
				"._evfolyam($tanev)." AS evfolyam, "._evfolyamJel($tanev)." AS evfolyamJel, `telephelyId`,`osztalyJellegId`
				FROM `osztaly` LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) $WHERE";
	} elseif ($SET['minden']===true) {
		$q = "SELECT `osztalyId`, `leiras`, `kezdoTanev`, `vegzoTanev`, `jel`, `kezdoEvfolyamSorszam`,
				IF (ISNULL(osztalyJel),"._osztalyJel($tanev).",osztalyJel) AS `osztalyJel`, 
				"._evfolyam($tanev)." AS evfolyam, "._evfolyamJel($tanev)." AS evfolyamJel, `telephelyId`,`osztalyJellegId`
				FROM `osztaly` LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) 
				LEFT JOIN `".tanevDbNev(__INTEZMENY, $tanev)."`.`osztalyNaplo` USING (`osztalyId`)
				$WHERE";
	} else {
		// Van tanév adatbázis --> lekérdezhető az osztály jelölése is.
		$q = "SELECT `osztaly`.`osztalyId` AS `osztalyId`, `leiras`, `kezdoTanev`, `vegzoTanev`, `jel`, `kezdoEvfolyamSorszam`, `osztalyJel`, 
				`evfolyam`, `evfolyamJel`, `telephelyId`,`osztalyJellegId`
				FROM `osztaly` LEFT JOIN `".tanevDbNev(__INTEZMENY, $tanev)."`.`osztalyNaplo` USING (`osztalyId`) $WHERE";
	}
	$q .= ' ORDER BY '._SQL_EVFOLYAMJEL_SORREND.', osztalyJel, kezdoTanev';
//	$q .= "ORDER BY evfolyamJel, kezdoTanev, jel";
//	$q .= "ORDER BY LPAD(SUBSTRING_INDEX(`osztalyJel`,'.',1),4,'0'),LPAD(SUBSTRING_INDEX(`osztalyJel`,'.',-1),4,'0'),`kezdoTanev`,`jel`";

	$return = array();
	if ($SET['result']==='assoc') { // ha assoc, a keyfield automatikusan az osztalyId legyen!!!
	    $r1 = db_query($q, array('fv' => 'getOsztalyok', 'modul'=>'naplo_intezmeny', 'result'=>'indexed', 'values' => $v), $lr );
	    for ($i=0; $i<count($r1); $i++) {
		//$return[$i] = $r1[$i];
		//$return[$i]['osztalyfonok']=getOsztalyfonok($r1[$i]['osztalyId']);
		$return[$r1[$i]['osztalyId']] = $r1[$i]; 
		$return[$r1[$i]['osztalyId']]['osztalyfonok']=getOsztalyfonok($r1[$i]['osztalyId']); // TODO: lr
		$return[$r1[$i]['osztalyId']]['osztalyfonokNev']=$return[$r1[$i]['osztalyId']]['osztalyfonok']['tanarNev'];
	    }
	} elseif ($SET['result']==='indexed') {
	    $r1 = db_query($q, array('fv' => 'getOsztalyok', 'modul'=>'naplo_intezmeny', 'result'=>'indexed', 'values' => $v), $lr );
	    for ($i=0; $i<count($r1); $i++) {
		$return[$i] = $r1[$i];
		if ($SET['mindenOsztalyfonok']===true) {
		    $return[$i]['osztalyfonok']=getOsztalyfonokok($r1[$i]['osztalyId'],$tanev); // TODO: lr
		    $return[$i]['osztalyfonokNev']=_genOfNev($return[$i]['osztalyfonok']);
		} else {
		    $return[$i]['osztalyfonok']=getOsztalyfonok($r1[$i]['osztalyId']); //TODO: lr
		    $return[$i]['osztalyfonokNev']=$return[$i]['osztalyfonok']['tanarNev'];
		}
	    }
	} else {
	    $_SESSION['alert'][] = '::shared lib failure, unknown result type(getOsztalyok)';
	}
	// is_resource mysqli esetén nem jó (object)
	if (!$olr) db_close($lr);

	$mayorCache->set($key,$return,'osztaly');
	return $return;
	
}

/*
    erettsegizo - csak azok az osztályok jelennek meg, melyek a megadott tanévben végeznek és osztályJellegük szerint érettségizők
    vizsgazo - csak azok az osztályok jelennek meg, melyek a megadott tanévben végeznek és osztályJellegük szerint érettségizők vagy szakmai vizsgát tevők
*/
function getVegzosOsztalyok($SET = array('tanev' => __TANEV, 'result' => 'assoc', 'erettsegizo' => false, 'vizsgazo' => false)) {

    global $_TANEV;

    $tanev = readVariable($SET['tanev'], 'numeric unsigned', __TANEV);
    if ($tanev == __TANEV) $TA = $_TANEV;
    else $TA = getTanevAdat($tanev);

    if ($SET['erettsegizo'] === true) {
	$WHERE_ERETTSEGIZO = " AND vegzesKovetelmenye='érettségi vizsga' ";
	$JOIN_ERETTSEGIZO = " LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) ";
    } else if ($SET['vizsgazo'] === true) {
	$WHERE_ERETTSEGIZO = " AND vegzesKovetelmenye IN ('érettségi vizsga','szakmai vizsga') ";
	$JOIN_ERETTSEGIZO = " LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) ";
    } else {
	$WHERE_ERETTSEGIZO = "";
    }

    if ($SET['result'] == 'id' || $SET['result'] == 'idonly') {
	// Csak az osztalyId kell
	$q = "SELECT osztalyId FROM osztaly".$JOIN_ERETTSEGIZO." WHERE osztaly.vegzoTanev = %u".$WHERE_ERETTSEGIZO."";
	$return = db_query($q, array('fv' => 'getVegzosOsztalyok', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($tanev)));
    } else {
	if ($TA['statusz'] == 'tervezett') {
	    // Nincs még tanév adatbázis --> csak az osztly tábla használható;
	    $q = "SELECT osztalyId,leiras,kezdoTanev,vegzoTanev,jel,kezdoEvfolyamSorszam,"._osztalyJel($tanev)." AS osztalyJel
		FROM osztaly".$JOIN_ERETTSEGIZO."
		WHERE vegzoTanev = %u".$WHERE_ERETTSEGIZO."
		ORDER BY LPAD(SUBSTRING_INDEX(osztalyJel,'.',1),4,'0'),LPAD(SUBSTRING_INDEX(osztalyJel,'.',-1),4,'0'),kezdoTanev,jel";
	    $v = array($tanev);
	} else {
	    // Van tanév adatbázis --> lekérdezhető az osztály jelölése is.
	    $q = "SELECT osztaly.osztalyId AS osztalyId,leiras,kezdoTanev,vegzoTanev,jel,kezdoEvfolyamSorszam,osztalyJel
		FROM osztaly LEFT JOIN `%s`.osztalyNaplo USING (osztalyId)".$JOIN_ERETTSEGIZO."
		WHERE vegzoTanev = %u".$WHERE_ERETTSEGIZO."
		ORDER BY LPAD(SUBSTRING_INDEX(osztalyJel,'.',1),4,'0'),LPAD(SUBSTRING_INDEX(osztalyJel,'.',-1),4,'0'),kezdoTanev,jel";
	    $v = array(tanevDbNev(__INTEZMENY, $tanev), $tanev);
	}
	$return = db_query($q, array('fv' => 'getVegzosOsztalyok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
	if (is_array($return)) for ($i = 0; $i < count($return); $i++) {
	    $return[$i]['osztalyfonok'] = getOsztalyfonok($return[$i]['osztalyId'], $tanev);
	}
    }

    return $return;
}

function getOsztalyfonok($osztalyId, $tanev=__TANEV, $olr = null) {

	global $_TANEV;
	
	// Az adott tanév elejének és végének lekérdezése
	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $olr);
	else $tanevAdat = $_TANEV;
	
	initTolIgDt($tanev, $kDt, $zDt);
        
	$q = "SELECT osztalyTanar.tanarId,
		TRIM(CONCAT(viseltNevElotag,' ',viseltCsaladiNev,' ',viseltUtonev)) As tanarNev, osztalyTanar.beDt, osztalyTanar.kiDt
			FROM osztalyTanar LEFT JOIN tanar USING (tanarId)
			WHERE osztalyId = %u
			AND osztalyTanar.beDt <= '%s'
			AND (osztalyTanar.kiDt IS NULL OR osztalyTanar.kiDt > '%s')
	    		ORDER BY osztalyTanar.beDt DESC LIMIT 1";
	$v = array($osztalyId, $zDt, $kDt);
	return db_query($q, array('fv' => 'getOsztalyfonok', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v), $olr);

}

function getOsztalyfonokok($osztalyId, $tanev = null, $olr = null) {

    global $_TANEV;
	
    $ret = false;
    if (is_null($tanev)) {

	$q = "SELECT osztalyTanar.tanarId AS tanarId,
		    TRIM(CONCAT(viseltNevElotag,' ',viseltCsaladiNev,' ',viseltUtonev)) AS tanarNev,
		    osztalyTanar.beDt AS beDt,
		    osztalyTanar.kiDt AS kiDt
		FROM osztalyTanar LEFT JOIN tanar USING (tanarId)
		WHERE osztalyId = %u
		ORDER BY osztalyTanar.beDt";
	$ret = db_query($q, array('fv' => 'getOsztalyfonokok','modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($osztalyId)), $olr);

    } else {

	// Az adott tanév elejének és végének lekérdezése
	if ($tanev != __TANEV) $tanevAdat = getTanevAdat($tanev, $olr);
	else $tanevAdat = $_TANEV;
	
	$kDt = $tanevAdat['kezdesDt']; $zDt = $tanevAdat['zarasDt']; 

	$q = "SELECT osztalyTanar.tanarId AS tanarId,
		    TRIM(CONCAT(viseltNevElotag,' ',viseltCsaladiNev,' ',viseltUtonev)) AS tanarNev,
		    osztalyTanar.beDt AS beDt,
		    osztalyTanar.kiDt AS kiDt
		FROM osztalyTanar LEFT JOIN tanar USING (tanarId)
		WHERE osztalyId = %u
		AND osztalyTanar.beDt < '%s'
		AND (osztalyTanar.kiDt IS NULL OR osztalyTanar.kiDt > '%s')
		ORDER BY osztalyTanar.beDt";

	$ret = db_query($q, array('fv' => 'getOsztalyfonokok', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => array($osztalyId, $zDt, $kDt)));
	if (is_array($ret)) for ($i = 0; $i < count($ret); $i++) {
	    if ($ret[$i]['kiDt'] == '' || strtotime($ret[$i]['kiDt']) > time()) {
		$ret[$i]['aktiv'] = true;
	    }
	}

   }

    return $ret;

}

function getOsztalyAdat($osztalyId, $tanev = __TANEV, $olr = '') {
	
    if ($osztalyId=='') return false;

    global $_TANEV;

    // Csatlakozás az adatbázishoz
    if ($olr == '') $lr = db_connect('naplo_intezmeny', array('fv' => 'getOsztalyAdat'));
    else $lr = $olr;
    if (!$lr) return false;

	// Osztály adatainak lekérdezése
	if ($tanev == __TANEV) {
	    $tanevDb = __TANEVDBNEV; $TA = $_TANEV;
	} else {
	    $tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	    $TA = getTanevAdat($tanev, $lr);
	}
	if ($TA['statusz'] == 'aktív')
	    $q = "SELECT osztaly.osztalyId AS osztalyId,leiras,kezdoTanev,vegzoTanev,jel,kezdoEvfolyamSorszam,osztalyJel,evfolyamJel,telephelyId,osztalyJellegId,kirOsztalyJellegId,elokeszitoEvfolyam,evfolyamJelek
		FROM osztaly LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) 
		LEFT JOIN %1\$s.osztalyNaplo USING (osztalyId) WHERE osztalyId=%2\$u";
	else
	    $q = "SELECT osztaly.osztalyId AS osztalyId,leiras,kezdoTanev,vegzoTanev,jel,kezdoEvfolyamSorszam,"._osztalyJel($tanev)." AS osztalyJel,"._evfolyamJel($tanev)." AS evfolyamJel,telephelyId,osztalyJellegId,kirOsztalyJellegId,elokeszitoEvfolyam,evfolyamJelek
		FROM osztaly LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) WHERE osztalyId=%2\$u";
	$v = array($tanevDb, $osztalyId);
	$osztalyAdat = db_query($q, array('fv' => 'getOsztalyAdat', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v), $lr);
	$osztalyAdat['evfolyamJelek'] = explode(',',$osztalyAdat['evfolyamJelek']);
	$osztalyAdat['osztalyfonok'] = getOsztalyfonok($osztalyId, $tanev, $lr);
	$osztalyAdat['osztalyfonokok'] = getOsztalyfonokok($osztalyId, $tanev, $lr);
	$osztalyAdat['osztalyfonokNev']	= _genOfNev($osztalyAdat['osztalyfonokok']);
	$osztalyAdat['kepzes'] = getKepzesByOsztalyId($osztalyId); // ? lr
    if ($olr == '') db_close($lr);
	
    return $osztalyAdat;

}

function _genOfNev($_OF) {
    $_of= array();
    for ($j=0; $j<count($_OF); $j++) {
	$_of[] = $_OF[$j]['tanarNev'];
    }
    return implode(', ',array_unique($_of));
}

function getOsztalyNevById($osztalyId,$SET=array('tanev'=>__TANEV)) {
    $OA = getOsztalyAdat($osztalyId, $SET['tanev']);
    return $OA['osztalyJel'].' '.$OA['leiras'];
}

function getOsztalyTankorei($osztalyId, $tanev=__TANEV) {

    $lr = db_connect('naplo_intezmeny');

	$q = "SELECT tankorId FROM tankorOsztaly WHERE osztalyId=%u";
	$T = db_query($q, array('fv' => 'getOsztalyTankorei', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => array($osztalyId)), $lr);

	$q = "SELECT DISTINCT tankorId, tankorNev
		FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		WHERE tanev=%u AND tankorId IN (".implode(',', array_fill(0, count($T), '%u')).") ORDER BY tankorNev";
	array_unshift($T, $tanev);
	$return = db_query($q, array('fv' => 'getOsztalyTankorei', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $T), $lr);

    db_close($lr);

    return $return;

}

function getOsztalyJellegAdat($osztalyJellegId) {
    $q = "SELECT * FROM osztalyJelleg WHERE osztalyJellegId=%u";
    return db_query($q, array('fv'=>'getKirOsztalyJellegek','values'=>array($osztalyJellegId),'modul'=>'naplo_base','result'=>'record'));
}

function getKirOsztalyJellegek() {
    $q = "SELECT * FROM kirOsztalyJelleg";
    return db_query($q, array('fv'=>'getKirOsztalyJellegek','modul'=>'naplo_base','result'=>'indexed'));
}

function getOsztalyJellegek($SET = array('result' => 'indexed')) {
    $q = "SELECT * FROM osztalyJelleg";
    return db_query($q, array('fv'=>'getOsztalyJellegek','modul'=>'naplo_base','result'=>$SET['result'], 'keyfield' => 'osztalyJellegId'));
}

function getVegzosOsztalyJellegIds() {
    $q = "SELECT osztalyJellegId FROM osztalyJelleg WHERE vegzesKovetelmenye IN ('érettségi vizsga','szakmai vizsga')";
    return db_query($q, array('fv'=>'getVegzosOsztalyJellegIds','modul'=>'naplo_base','result'=>'idonly'));
}

function getOsztalyEvfolyamSzamElteres($osztalyId) {
    $q = "SELECT CONVERT(vegzoTanev-kezdoTanev,SIGNED) - CONVERT((LENGTH(`evfolyamJelek`)-LENGTH(REPLACE(`evfolyamJelek`, ',', ''))),SIGNED) AS evfolyamSzamElteres FROM osztaly LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) WHERE osztalyId=%u";
    $v = array($osztalyId);
    $r = db_query($q, array('fv'=>'checkOsztalyEvfolyamSzam','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));
    return $r;
}

function getOsztalyHetiIdokeret($osztalyId, $osztalyAdat = null, $SET = array('egyhaziE' => null)) {

    global $_EVFOLYAM_ADAT;

    require_once('include/modules/naplo/share/intezmenyek.php');

    if (!is_array($osztalyAdat)) $osztalyAdat = getOsztalyAdat($osztalyId);
    if (is_null($SET['egyhaziE'])) {
	$intezmenyAdat = getIntezmenyByRovidnev(__INTEZMENY);
	$SET['egyhaziE'] = ($intezmenyAdat['fenntarto']=='egyházi');
    }

    $iAdat = $_EVFOLYAM_ADAT[$osztalyAdat['evfolyamJel']]['osztályIdőkeret'];

    $idokeret['engedélyezett'] = $idokeret['összes'] = $iAdat[0]; // engedélyezett
    $idokeret['összes'] += $idokeret['tehetséggondozás-felzárkóztatás'] = 2; // tehettség gondozás, felzárkóztatás
    if ($SET['egyhaziE']) $idokeret['összes'] += $idokeret['egyházi'] = $iAdat[1];
    if (in_array($osztalyAdat['osztalyJellegId'], array(51,52,53,61,62,63))) $idokeret['összes'] += $idokeret['gimnázium'] = $iAdat[2];
    if ($nemzetisegi) $idokeret['összes'] += $idokeret['nemzetiségi'] = $iAdat[3];

    return $idokeret;

}

function getOsztalyByTanarId($tanarId, $Param = array('tanev' => __TANEV, 'tolDt' => '', 'igDt' => '', 'csakId'=>true)) {

    global $_TANEV;
    if (is_null($Param['tanev']) || $Param['tanev']=='') $Param['tanev']=__TANEV;
    if ($Param['tanev'] != __TANEV && $Param['tanev']!='') $TA = getTanevAdat($Param['tanev']);
    else $TA = $_TANEV;

    if (isset($Param['tolDt']) && $Param['tolDt']!='') $tolDt = $Param['tolDt']; else unset($tolDt);
    if (isset($Param['igDt'])  && $Param['igDt']!='')  $igDt = $Param['igDt']; else unset($igDt);
    initTolIgDt($Param['tanev'], $tolDt, $igDt);

    if ($Param['csakId']===true) {
	$q = "SELECT DISTINCT osztalyId FROM ".__INTEZMENYDBNEV.".osztalyTanar WHERE tanarId=%u AND beDt <= '%s'
    	    AND (kiDt IS NULL OR kiDt >= '%s')";
	$v = array($tanarId, $igDt, $tolDt);
	return db_query($q, array('fv' => 'getOsztalyByTanarId', 'modul' => 'naplo_intezmeny', 'result' => 'idonly', 'values' => $v));
    } else {
	    $q = "SELECT DISTINCT osztalyId, "._osztalyJel($TA['tanev'])." AS osztalyJel
		FROM ".__INTEZMENYDBNEV.".osztalyTanar LEFT JOIN osztaly USING (osztalyId)
		LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId)
		WHERE tanarId=%u AND beDt <= '%s'
		AND (kiDt IS NULL OR kiDt >= '%s')";
	    $v = array($tanarId, $igDt, $tolDt);
	    return db_query($q, array('fv' => 'getOsztalyByTanarId', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));
    }
}


?>