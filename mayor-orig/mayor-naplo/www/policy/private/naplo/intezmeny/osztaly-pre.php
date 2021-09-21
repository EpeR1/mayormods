<?php

if (_RIGHTS_OK !== true) die();


    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/intezmenyek.php');

    $ADAT['tablo']['tanevek'] = getTanevek(true);
    $ADAT['tablo']['telephely'] = getTelephelyek();
    $ADAT['tablo']['telephelyIds'] = array();
    foreach ($ADAT['tablo']['telephely'] as $i => $tAdat) $ADAT['tablo']['telephelyIds'][] = $tAdat['telephelyId'];	

    $ADAT['tablo']['osztalyId'] = $osztalyId = $_POST['osztalyId'] = readVariable($_POST['osztalyId'], 'id', readVariable($_GET['osztalyId'],'id',null));
    $ADAT['tablo']['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV, $ADAT['tablo']['tanevek']);

    //$telephelyId = readVariable($_POST['telephelyId'], 'id');

    if ($osztalyId!='') {
	$ADAT['tablo']['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev);
        $ADAT['tablo']['diakok'] = getDiakok(array('tanev' => $tanev,'osztalyId'=>$osztalyId));
	$ADAT['tablo']['diakIds'] = array_keys(reindex($ADAT['tablo']['diakok'],array('diakId')));
	$ADAT['tablo']['diakKepzes'] = getKepzesByDiakId($ADAT['tablo']['diakIds'], array('result' => 'assoc'));
    }

    $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post'=>array('tanev','telephelyId','dt'), 'telephelyId' => $telephelyId);


// ------------------------------

if (__NAPLOADMIN!==true) {

} else { // naploadmin

require_once('include/share/date/names.php');
require_once('include/modules/naplo/share/file.php');
require_once('include/modules/naplo/share/osztalyModifier.php');
require_once('include/modules/naplo/share/intezmenyek.php');
require_once('include/modules/naplo/share/diak.php');
require_once('include/modules/naplo/share/tanar.php');
require_once('include/modules/naplo/share/kepzes.php');
require_once('include/modules/naplo/share/szemeszter.php');
require_once('include/modules/naplo/share/tankor.php');
require_once('include/modules/naplo/share/tankorModifier.php');
require_once('include/modules/naplo/share/tankorDiakModifier.php');
require_once('include/modules/naplo/share/hianyzasModifier.php');
require_once('include/modules/naplo/share/jegyModifier.php');
require_once('include/modules/naplo/share/jegy.php');
require_once('include/modules/naplo/share/kereso.php');
require_once('include/share/net/upload.php');

define('FILE_UPLOAD_DIR',_DOWNLOADDIR.'/private/naplo/upload/');

if (defined('__INTEZMENY') && __INTEZMENY != '') {
	$ADAT['tanevek'] = getTanevek(true);
	$ADAT['tanarok'] = getTanarok();
	$ADAT['kepzesek'] = getKepzesek();
	$ADAT['telephely'] = getTelephelyek();
	$ADAT['telephelyIds'] = array();
	foreach ($ADAT['telephely'] as $i => $tAdat) $ADAT['telephelyIds'][] = $tAdat['telephelyId'];	
	$ADAT['osztalyJellegek'] = getOsztalyJellegek(array('result'=>'assoc')); // Ez mondjuk nem intézmény függő...
}

$ADAT['tanev'] = $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV, $ADAT['tanevek']);
$ADAT['telephelyId'] = $telephelyId = readVariable($_POST['telephelyId'], 'id', (isset($_POST['telephelyId'])?null:readVariable(__TELEPHELYID,'id')), $ADAT['telephelyIds']);
$ADAT['osztalyId'] = $osztalyId = $_POST['osztalyId'] = readVariable($_POST['osztalyId'], 'id', readVariable($_GET['osztalyId'],'id',null));
if ($tanev != __TANEV) $TA = getTanevAdat($tanev);
else $TA = $_TANEV;

/* -------- */
// Adatok frissítése adatállományból

if (__NAPLOADMIN===true && 
	(
	    (isset($_POST['fileName']) && $_POST['fileName'] != '') 
	    or
	    (is_array($_FILES) && $_FILES['upfile']['name']!='')
	)
    ) {

    define('_SKIP_ON_DUP',readVariable($_POST['skipOnDup'],'bool'));
    if (is_array($_FILES) && $_FILES['upfile']['name']!='') { // távoli feltöltés
	try {
	    $_F = array('subdir'=>FILE_UPLOAD_DIR, 'filename'=>uniqid()); // move ide
	    $sikeresFeltoltes = mayorFileUpload($_F, false);
	    $fileName = FILE_UPLOAD_DIR.$_F['filename'];
        } catch (Exception $e) {
            dump($e);
        }
    } else { // helyi beolvasás
	//    $fileName = fileNameNormal($_POST['fileName']);
	$fileName = ($_POST['fileName']); // TODO
    }
    $mezo_elvalaszto = '	'; // "\t"
    $ADATOK = array();

	if (file_exists($fileName)) {

		if (!is_array($_POST['MEZO_LISTA'])) {

			$ADATOK = readUpdateFile($fileName);
			if (count($ADATOK) > 0) $attrList = getTableFields('diak', 'naplo_intezmeny',array('beDt','kiDt'));
			else $_SESSION['alert'][] = 'message:wrong_data';

		} else {

			$MEZO_LISTA = $_POST['MEZO_LISTA'];
			$KULCS_MEZOK = $_POST['KULCS_MEZOK'];
			updateOsztaly($osztalyId, $fileName, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto, readVariable($_POST['rovatfej'], 'bool'));
			$ADAT['updatedFromFile']=true;
		} // MEZO_LISTA tömb
	} else {
		$_SESSION['alert'][] = 'message:file_not_found:'.$fileName;
	} // A file létezik-e

} // van file
/* -------- */

if (isset($osztalyId)) {
	$_evfolyamSzamElteres = getOsztalyEvfolyamSzamElteres($osztalyId);
	if ($_evfolyamSzamElteres>0) $_SESSION['alert'][] = 'alert:hibás évfolyam beállítás! Az osztály jellege kevesebb évfolyamot ír elő! (Ellenőrizd, hogy végzés tanéve jól van-e beállítva!)';
	elseif ($_evfolyamSzamElteres<0) $_SESSION['alert'][] = 'info:kevesebb évfolyam alatt végez az osztály, mint az osztály jellege előírná. Ez megszűnő, vagy később belépő osztályoknál megengedett.';

	if ( in_date_interval(date('Y-m-d'),$TA['elozoZarasDt'],$TA['kovetkezoKezdesDt']) ) {
	    $dt = $ADAT['dt'] = readVariable($_POST['dt'], 'date', date('Y-m-d'));
	} else {
	    $dt = $ADAT['dt'] = $TA['elozoZarasDt'];
	}
	$ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev);

	if (!($ADAT['osztalyAdat']["kezdoTanev"]<=$tanev && $ADAT['osztalyAdat']["vegzoTanev"]>=$tanev)) $_SESSION['alert'][]='error:hibás tanév beállítás';

	$diakIds = $ADAT['osztalyAdat']['kepzesIds'] = array();
	if (is_array($ADAT['osztalyAdat']['kepzes'])) 
	    for ($i = 0; $i < count($ADAT['osztalyAdat']['kepzes']); $i++) $ADAT['osztalyAdat']['kepzesIds'][] = $ADAT['osztalyAdat']['kepzes'][$i]['kepzesId'];
	$OsztalyNevsor = getDiakokByOsztaly($osztalyId, array('tanev' => $tanev,'felveteltNyertEkkel'=>true));

	foreach ($OsztalyNevsor as $key => $value) if (is_numeric($key)) {
	    $diakIds[] = $key;
	    $ADAT['osztalyNevsor'][$key] = $value;
	}

	$ADAT['diakKepzes'] = getKepzesByDiakId($diakIds, array('result' => 'assoc'));
	$ADAT['diakok'] = getDiakok(array('tanev' => $tanev));
}


if ($action == 'oidEllenor' && __NAPLOADMIN===true) {

    // --TODO
    $_re = str_replace(' ',"\n",str_replace('\r','\n',$_POST['oidtxt']));
    $oidxp = explode("\n",str_replace(' ',"\n",str_replace('\r','\n',$_POST['oidtxt'])));
    $ADAT['oidtxt'] = $_re;
    for($i=0; $i<count($oidxp); $i++) {
	$_oid = trim($oidxp[$i]);
        $ADAT['oidCheck'][$_oid] = getDiakokByPattern($_oid);
    }

} elseif ($action == 'osztalyAdatModositas' && __NAPLOADMIN) {

	$leiras = readVariable($_POST['leiras'], 'string');
	$ofoTanarId = readVariable($_POST['ofoTanarId'], 'id');
	$ofoBeDt = readVariable($_POST['ofoBeDt'], 'date'); 
	$ofoKiDt = readVariable($_POST['ofoKiDt'], 'date', '');
	$tanarId = readVariable($_POST['tanarId'], 'id'); 
	$beDt = readVariable($_POST['beDt'], 'date');
	$kiDt = readVariable($_POST['kiDt'], 'date');
	$ADAT['telephelyId'] = readVariable($_POST['telephelyId'], 'id', null, $ADAT['telephelyIds']);
	$ADAT['osztalyJellegId'] = readVariable($_POST['osztalyJellegId'],'id',null);
	$ADAT['kezdoEvfolyamSorszam'] = readVariable($_POST['kezdoEvfolyamSorszam'],'numeric unsigned', $ADAT['osztalyAdat']['kezdoEvfolyamSorszam'], 
	    range(1, count($ADAT['osztalyAdat']['evfolyamJelek'])-$ADAT['osztalyAdat']['vegzoTanev']+$ADAT['osztalyAdat']['kezdoTanev'])
	);

	$lr = db_connect('naplo_intezmeny');
		// leírás/telephely megadása, módosítása
		if (
		    (isset($leiras) && $leiras != $ADAT['osztalyAdat']['leiras'])
		    || (isset($ADAT['telephelyId']) && $ADAT['telephelyId'] != $ADAT['osztalyAdat']['telephelyId'])
		    || (isset($ADAT['kezdoEvfolyamSorszam']) && $ADAT['kezdoEvfolyamSorszam'] != $ADAT['osztalyAdat']['kezdoEvfolyamSorszam'])

		    || (isset($ADAT['osztalyJellegId']) && $ADAT['osztalyJellegId'] != $ADAT['osztalyAdat']['osztalyJellegId'])
		) {
		    osztalyLeirasTelephelyModositas($osztalyId, $leiras, $ADAT['telephelyId'], $ADAT['osztalyJellegId'], $ADAT['kezdoEvfolyamSorszam'], $ADAT['osztalyAdat'], $lr);
		    $_SESSION['alert'][] = 'info:done';
		}
		// Osztályfőnöki kinevezés lezárása
		for ($i = 0; $i < count($ofoTanarId); $i++) {
			if ($ofoKiDt[$i] != '') osztalyfonokKileptetes($osztalyId, $ofoTanarId[$i], $ofoBeDt[$i], $ofoKiDt[$i], $lr);
		}
		// Új osztályfőnök felvétele
		if ($tanarId != '' and $beDt != '') osztalyfonokKinevezes($osztalyId, $tanarId, $beDt, $lr);
		// A módosított adatok lekérdezése
		$ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev, $lr);
		$ADAT['osztalyAdat']['kepzesIds'] = array();
		if (is_array($ADAT['osztalyAdat']['kepzes'])) 
		    for ($i = 0; $i < count($ADAT['osztalyAdat']['kepzes']); $i++) $ADAT['osztalyAdat']['kepzesIds'][] = $ADAT['osztalyAdat']['kepzes'][$i]['kepzesId'];
	db_close($lr);

} elseif ($action == 'nyekOsztalyLeptetes' && __NAPLOADMIN) {
// EZ MÁR ELAVULT! TODO
/*
	$ADAT['ujOsztaly']['tanevek'] = $ADAT['tanevek'];
//	$ADAT['ujOsztaly']['kezdoTanev'] = $ADAT['osztalyAdat']['vegzoTanev']+1;
//	$ADAT['ujOsztaly']['vegzoTanev'] = readVariable($_POST['vegzoTanev'], 'numeric unsigned', null, $ADAT['tanevek']);
//	$ADAT['ujOsztaly']['kezdoEvfolyam'] = $ADAT['osztalyAdat']['kezdoEvfolyam'];
	$ADAT['ujOsztaly']['jel'] = readVariable($_POST['osztalyJel'], 'string');
	$ADAT['ujOsztaly']['leiras'] = $ADAT['osztalyAdat']['leiras'];
	$ADAT['ujOsztaly']['telephelyId'] = $ADAT['osztalyAdat']['telephelyId'];
	$ADAT['ujOsztaly']['osztalyJellegId'] = $ADAT['osztalyJellegek'][ $ADAT['osztalyAdat']['osztalyJellegId'] ]['kovOsztalyJellegId']; // NyEK osztály
	// Új osztály létrehozása
//	if (isset($ADAT['ujOsztaly']['kezdoTanev']) && isset($ADAT['ujOsztaly']['vegzoTanev']) && isset($ADAT['ujOsztaly']['kezdoEvfolyam']) && isset($ADAT['ujOsztaly']['jel'])) {
//		$ujOsztalyId = ujOsztaly($ADAT['ujOsztaly']);
//	}
	// Képzés hozzárendelés
	setOsztalyKepzesei($ujOsztalyId, $ADAT['osztalyAdat']['kepzesIds']);
	// A jelen év végétől kinevezzük az osztályfőnököt
	if (__TANEV == $ADAT['osztalyAdat']['vegzoTanev']) $beDt = date('Y-m-d', strtotime('next month', strtotime($_TANEV['zarasDt'])));
	else $beDt = $ADAT['ujOsztaly']['kezdoTanev'].'-08-01';
	osztalyfonokKinevezes($ujOsztalyId, $ADAT['osztalyAdat']['osztalyfonok']['tanarId'], $beDt);	
	// osztálytagok felvétele
	$diakIds = array_merge($OsztalyNevsor['jogviszonyban van'], $OsztalyNevsor['magántanuló']);
	foreach ($diakIds as $diakId) {
	    ujTag($ujOsztalyId, $diakId, $beDt, null);
	    osztalyDiakTorol(array('osztalyId' => $osztalyId, 'diakId' => $diakId, 'tolDt' => $beDt, 'igDt' => null, 'zaradekkal' => false));
	}
	// TODO: régi osztályhoz rendelt tankörök hozzárendelése az új osztályhoz és átnevezés
*/
} elseif ($action == 'osztalyJelVegzesModositas' && __NAPLOADMIN) {
	// TODO: nem szabadna akárhogy változtatni a tanéveket! Nem lehet vtanev<ktanev és nem lehet az osztály évfolyamok száma > az osztalyJelleg evfolyamok számánál
	$ujKezdoTanev = readVariable($_POST['ujKezdoTanev'], 'numeric unsigned');
	$ujVegzoTanev = readVariable(
		$_POST['ujVegzoTanev'], 'numeric unsigned',null,array(),
		'$return>='.$ujKezdoTanev.' && $return-'.$ujKezdoTanev.'+'.$ADAT['osztalyAdat']['kezdoEvfolyamSorszam'].'<='.count($ADAT['osztalyAdat']['evfolyamJelek'])
	);
	$ujOsztalyJel = readVariable($_POST['ujOsztalyJel'], 'string');
	if (is_null($ujKezdoTanev) || is_null($ujVegzoTanev) || $ujOsztalyJel == '') {
	    $_SESSION['alert'][] = 'message:wrong_data:kezdoTanev='.$_POST['ujKezdoTanev'].', vegzoTanev='.$_POST['ujVegzoTanev'].', jel='.$_POST['ujOsztalyJel'];
	} else {
	    $lr = db_connect('naplo_intezmeny');
	    db_start_trans($lr);
	    $r = array();
		if ($ujVegzoTanev!='') {
		    $q = "UPDATE osztaly SET vegzoTanev=%u WHERE osztalyId=%u";
		    $v = array($ujVegzoTanev,$osztalyId);
		    $r[] = db_query($q, array('fv' => '', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
		}
		if ($ujKezdoTanev!='') {
		    $q = "UPDATE osztalyDiak SET beDt = (SELECT kezdesDt FROM szemeszter WHERE tanev=%u AND szemeszter=1) WHERE osztalyId=%u AND beDt<(SELECT kezdesDt FROM szemeszter WHERE tanev=%u AND szemeszter=1)";
		    $v = array($ujKezdoTanev,$osztalyId,$ujKezdoTanev);
		    $r[] = db_query($q, array('fv' => '', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
		    
		    $q = "UPDATE osztaly SET kezdoTanev=%u WHERE osztalyId=%u";
		    $v = array($ujKezdoTanev,$osztalyId);
		    $r[] = db_query($q, array('fv' => '', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
		}

		if ($ujOsztalyJel=='') {
		    $q = "SELECT jel FROM osztaly WHERE osztalyId = %u";
		    $v = array($osztalyId);
		    $osztalyJel = db_query($q, array('fv' => '', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v), $lr);
		    $ujOsztalyJel = $osztalyJel;
		} else {
		    $osztalyJel=$ujOsztalyJel;
		    $q = "UPDATE osztaly SET jel='%s' WHERE osztalyId=%u";
		    $v = array($ujOsztalyJel,$osztalyId);
		    $r[] = db_query($q, array('fv' => '', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v), $lr);
		}
		$OSZTALYADAT = getOsztalyAdat($osztalyId,__TANEV,$lr);

		// az osztalyNaplo.osztalyJelet is módosítani kell, minden érintett tanévben!
		$r[] = updateosztalyNev($osztalyId, $lr);

	    if (!in_array(false,$r)) db_commit($lr);
	    else db_rollback($lr);		
	    db_close($lr);
	} // ha van kezdoTanev, zaroTanev, jel
	
} elseif ($action == 'ujOsztaly' && __NAPLOADMIN) {

	$ADAT['kezdoTanev'] = readVariable($_POST['kezdoTanev'], 'numeric unsigned', null, $ADAT['tanevek']);
	$ADAT['vegzoTanev'] = readVariable($_POST['vegzoTanev'], 'numeric unsigned', null, $ADAT['tanevek']);
	$ADAT['kezdoEvfolyamSorszam'] = readVariable($_POST['kezdoEvfolyamSorszam'], 'numeric unsigned',1);
	$ADAT['jel'] = readVariable($_POST['jel'], 'string');
	$ADAT['leiras'] = readVariable($_POST['leiras'], 'string');
	$ADAT['telephelyId'] = readVariable($_POST['telephelyId'], 'id', null, $ADAT['telephelyIds']);
	$ADAT['osztalyJellegId'] = readVariable($_POST['osztalyJellegId'],'id',null);
	if (isset($ADAT['osztalyJellegId']) && isset($ADAT['kezdoTanev']) && isset($ADAT['vegzoTanev']) && isset($ADAT['kezdoEvfolyamSorszam']) && isset($ADAT['jel'])) {
		$osztalyId = $ADAT['osztalyId'] = ujOsztaly($ADAT);
		if ($osztalyId) {
		    $ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev);
		    $diakIds = $ADAT['osztalyAdat']['kepzesIds'] = array();
		    if (is_array($ADAT['osztalyAdat']['kepzes'])) 
			for ($i = 0; $i < count($ADAT['osztalyAdat']['kepzes']); $i++) $ADAT['osztalyAdat']['kepzesIds'][] = $ADAT['osztalyAdat']['kepzes'][$i]['kepzesId'];
		    $ADAT['diakok'] = getDiakok(array('tanev' => $tanev));
		}
	} else {
		$_SESSION['alert'][] = 'message:empty_field:'."${ADAT['kezdoTanev']}, ${ADAT['vegzoTanev']}, ${ADAT['kezdoEvfolyamSorszam']}, ${ADAT['jel']}";
	}
} elseif ($action == 'osztalyNevsor' && (__NAPLOADMIN || __VEZETOSEG || _TITKARSAG)) {
	$kepzesMod = array();
	if (is_array($_POST['diakKepzes'])) foreach ($_POST['diakKepzes'] as $index => $kepzes) {
	    list($_diakId,$kepzesId) = explode('/', $kepzes);
	    // kirakjuk az osztályhoz nem tartozó képzéseit is - hogy könnyebb legyen kezelni...
	    if (in_array($_diakId, $diakIds)) {
		    $kepzesMod[$kepzesId][] = $_diakId;
	    }
	}
	diakKepzesModositas($diakIds, $kepzesMod, $dt);
	$ADAT['diakKepzes'] = getKepzesByDiakId($diakIds, array('result' => 'assoc'));
} elseif ($action == 'ujTag' && (__NAPLOADMIN || __VEZETOSEG || __TITKARSAG)) {
	$diakId = readVariable($_POST['diakId'], 'id');
	$beDt = readVariable($_POST['beDt'], 'date');
	$kiDt = readVariable($_POST['kiDt'], 'date');
	if (isset($diakId)  && isset($beDt)) {
	    if (ujTag($osztalyId, $diakId, $beDt, $kiDt)) {
		$OsztalyNevsor = getDiakokByOsztaly($osztalyId, array('tanev' => $tanev,'felveteltNyertEkkel'=>true));
		foreach ($OsztalyNevsor as $key => $value) if (is_numeric($key)) {
		    $ADAT['osztalyNevsor'][$key] = $value;
		}
		$diakIds = array_keys($ADAT['osztalyNevsor']);
	    }
	}
} elseif ($action == 'tagTorles' && __NAPLOADMIN) {
    $elozoTanev = getTanevAdat(intval(__TANEV)-1); $elozoTanevZarasTime = strtotime($elozoTanev['zarasDt']);
    $PARAM['diakId'] = readVariable($_POST['diakId'], 'id');
//    $PARAM['tolDt'] = readVariable($_POST['tolDt'], 'date', null, array(), 'strtotime($return) > '.$elozoTanevZarasTime);
    $PARAM['tolDt'] = readVariable($_POST['tolDt'], 'date');
    $PARAM['igDt'] = readVariable($_POST['igDt'], 'date', null, array(), 'strtotime('."'${PARAM['tolDt']}'".') < strtotime($return)');
    $PARAM['osztalyId'] = $ADAT['osztalyId'];
    $PARAM['zaradekkal'] = false;
    if (isset($PARAM['osztalyId']) && isset($PARAM['diakId']) && isset($PARAM['tolDt'])) {
	if (osztalyDiakTorol($PARAM)) {
	    $OsztalyNevsor = getDiakokByOsztaly($osztalyId, array('tanev' => $tanev,'felveteltNyertEkkel'=>true));
	    foreach ($OsztalyNevsor as $key => $value) if (is_numeric($key)) {
		$ADAT['osztalyNevsor'][$key] = $value;
	    }
	    $diakIds = array_keys($ADAT['osztalyNevsor']);
	}	
    } else { $_SESSION['alert'][] = 'message:empty_field'; }
} elseif ($action == 'osztalyTorles' && __NAPLOADMIN) {
	if (osztalyTorles($osztalyId)) {
		unset($osztalyId);
		$ADAT['osztalyAdat'] = array();
	}
}

/* ------------------------------------------------- */
/* REFRESH */

if (isset($osztalyId)) {
	$ADAT['osztalyAdat'] = getOsztalyAdat($osztalyId, $tanev);
	$diakIds = $ADAT['osztalyAdat']['kepzesIds'] = array();
	if (is_array($ADAT['osztalyAdat']['kepzes'])) 
	    for ($i = 0; $i < count($ADAT['osztalyAdat']['kepzes']); $i++) $ADAT['osztalyAdat']['kepzesIds'][] = $ADAT['osztalyAdat']['kepzes'][$i]['kepzesId'];
	$OsztalyNevsor = getDiakokByOsztaly($osztalyId, array('tanev' => $tanev,'felveteltNyertEkkel'=>true));
        $ADAT['osztalyNevsor'] = array();
	foreach ($OsztalyNevsor as $key => $value) if (is_numeric($key)) {
	    $diakIds[] = $key;
	    $ADAT['osztalyNevsor'][$key] = $value;
	    $ADAT['osztalyNevsor'][$key]['diakNaploSorszam'] = getDiakNaploSorszam($key,$tanev,$osztalyId);
	}
	$ADAT['diakKepzes'] = getKepzesByDiakId($diakIds, array('result' => 'assoc'));
	$ADAT['diakok'] = getDiakok(array('tanev' => $tanev));

	for ($_ev = $ADAT['osztalyAdat']['kezdoTanev']; $_ev<=$ADAT['osztalyAdat']['vegzoTanev']; $_ev++) {
	    $ADAT['osztalyJelek'][$_ev] = getOsztalyJel($osztalyId,$_ev,$ADAT['osztalyAdat']);
	}
}

// írjuk ezt felül, nekünk minden eddigi ofő bejegyzésre szükségünk van
if (isset($osztalyId)) $ADAT['osztalyAdat']['osztalyfonokok'] = getOsztalyfonokok($osztalyId);

$TOOL['tanevSelect'] = array('tipus'=>'cella','paramName' => 'tanev', 'tervezett' => true, 'action' => 'tanevValtas', 'post'=>array('telephelyId'));
$TOOL['telephelySelect'] = array('tipus'=>'cella','paramName' => 'telephelyId', 'post' => array('tanev','dt'));
$TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'post'=>array('tanev','telephelyId','dt'), 'telephelyId' => $telephelyId);
$TOOL['datumSelect'] = array('tipus'=>'sor','paramName'=>'dt','tolDt'=>$TA['elozoZarasDt'],'igDt'=>$TA['kovetkezoKezdesDt'],'override'=>true,'post'=>array('tanev','telephelyId','osztalyId'));
$TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=kepzes'),
           'titleConst' => array('_KEPZES'), 'post' => array(''),                                                                                                          
    	    'paramName'=>'kepzesId'); // paramName ?
} // naploadmin


getToolParameters();



?>