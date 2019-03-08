<?php

    function magicSzerep() { // TODO
	if (_RUNLEVEL=='cron') {
	    return __SZEREP;
	} else {
	    return __SZEREP;
	}
    }

    function initSzerep() {

	if (defined('__SZEREP')) return false;
	define('__SZEREP',__UZENOSZEREP);

/*
	if (_RUNLEVEL=='cron') {}
	if (__UZENOADMIN===true && __ASWHO==='asAdmin') define('__SZEREP','admin');
	elseif (__TANAR===true) define('__SZEREP', 'tanar');
	elseif (__DIAK===true && defined('__PARENTDIAKID') && intval(__PARENTDIAKID)>0) define('__SZEREP','szulo');
	elseif (__DIAK===true && defined('__USERDIAKID') && intval(__USERDIAKID)>0) define('__SZEREP','diak');
	elseif (__UZENOADMIN===true) define('__SZEREP','admin');
	else define(__SZEREP,'');
*/
    }

    function getUzenoSzerep() {

	if (__TANAR===true) $szerep = 'tanar';
	elseif (__DIAK===true && defined('__PARENTDIAKID') && intval(__PARENTDIAKID)>0) $szerep = 'szulo';
	elseif (__DIAK===true && defined('__USERDIAKID') && intval(__USERDIAKID)>0) $szerep = 'diak';
	elseif (__UZENOADMIN===true) $szerep = 'admin';
	else $szerep='';

	return $szerep;

    }

    function uzenhet($kinek) {
        global $UZENODENY;
	if (is_null($UZENODENY[__SZEREP])) return true;
        return (!in_array($kinek,$UZENODENY[__SZEREP]));
    }

    function getUzenoUzenetek($SET=array('tanev'=>__TANEV,'count'=>false,'filter'=>array(),'ignoreAdmin'=>false,'filterFlag'=>array(),'limits'=>array(),'order'=>'DESC')) {

	$__SZEREP = __SZEREP; // cronból is szeretnénk használni

	if (__SZEREP=='') return array();


	$feladoId = setUzenoFeladoId();
	$TIPUSOK = initUzenoTipusok(array('csakId'=>true,'result'=>'idonly','tanev'=>$SET['tanev'],'forRead'=>true));
	$TIPUSOK[$__SZEREP][] = setUzenoFeladoId();

	if (__UZENOADMIN===true && $SET['ignoreAdmin']===true) return array(); // skip useradmin (pl hirnok)

	if (is_array($SET['filter']) && count($SET['filter'])>0) {
	    for ($i=0; $i<count($SET['filter']); $i++) {
		$X[] = $SET['filter'][$i];
	    }
	}
	if (is_array($SET['filterFlag']) && count($SET['filterFlag'])>0) {
	    for ($i=0; $i<count($SET['filterFlag']); $i++) {
		$Y[] = $SET['filterFlag'][$i];
	    }
	}

	$dbName = 'naplo_'.__INTEZMENY.'_'.$SET['tanev'];

	if (is_array($SET['limits'])) $L = ' LIMIT '.($SET['limits']['pointer']).','.$SET['limits']['limit'];
	if (isset($SET['order'])) $O = ' '.$SET['order'].' '; else $O = ' DESC ';

	if (is_array($X) && count($X)>0) $WX = implode(' AND ',$X).' AND'; else $WX = '';
	if (is_array($Y) && count($Y)>0) $HAVING = 'HAVING '.implode(' AND ',$Y); else $HAVING = '';

	$JOINTABLE = "LEFT JOIN `$dbName`.`uzenoFlagek` ON (uzeno.mId=uzenoFlagek.mId AND Id=$feladoId AND Tipus='".$__SZEREP."')";

	if (__UZENOADMIN!==true) {
	    foreach ($TIPUSOK as $tipus=>$DATA) {
		if (is_array($DATA) && count($DATA)>0) {
		    $W[] = ' (cimzettTipus="'.$tipus.'" AND cimzettId IN ('.  implode(',',$DATA)   .')) ';
		    $W[] = ' (cimzettTipus="'.$tipus.'" AND cimzettTipus=feladoTipus AND cimzettId=0) ';

		}
	    }
	    $q  = "SELECT uzeno.*,uzenoFlagek.flag AS flag FROM $dbName.uzeno $JOINTABLE WHERE ".$WX." ((feladoId=$feladoId and feladoTipus='".$__SZEREP."') OR (".implode(' OR ',$W).")) $HAVING ORDER BY uzeno.mId ".$O.$L;
	    $qc = "SELECT count(*) AS db FROM $dbName.uzeno WHERE ".$WX." ((feladoId=$feladoId and feladoTipus='".$__SZEREP."') OR (".implode(' OR ',$W)."))";
	} else {
	    // NOTE - nem minden id-nek az adatai lesznek lekérdezve később!!!
	    $WX = ($WX=='')?'':'WHERE '.$WX.' 1=1';
//	    $q = "SELECT uzeno.*,uzenoFlagek.flag AS flag FROM `$dbName`.uzeno $JOINTABLE ".$WX." $HAVING ORDER BY uzeno.mId DESC".$L;
	    $q = "SELECT uzeno.* FROM `$dbName`.uzeno ".$WX." ORDER BY uzeno.mId DESC".$L;
	    $qc= "SELECT count(*) AS db FROM $dbName.uzeno ".$WX;  
	}
	if ($SET['count']!==true)
	    $result = db_query($q, array('fv' => 'getUzenoUzenetek/1', 'modul' => 'naplo_intezmeny', 'result' => 'indexed'));
	else 
	    $result = db_query($qc, array('fv' => 'getUzenoUzenetek/2', 'modul' => 'naplo_intezmeny', 'result' => 'value')); 
	return $result ;

    }

    // getUzenoUzenetek-->
    // pre-ből
    function initUzenoTipusok($SET=array('csakId'=>true,'tanev'=>__TANEV,'forRead'=>false)) {
	global $UZENODENY;

	$TIPUSOK = array();
	$feladoId = setUzenoFeladoId();
	switch (__SZEREP) {
	    case 'tanar': /* nem tiltható */
		$TIPUSOK['tankorSzulo'] = $TIPUSOK['tankor'] = getTankorByTanarId($feladoId, $SET['tanev'], array('csakId' => $SET['csakId']));
		$TIPUSOK['munkakozosseg'] = getMunkakozossegByTanarId($feladoId, array('csakId'=>$SET['csakId']));
		if (__OSZTALYFONOK) {
		    $TIPUSOK['osztalySzulo'] = $TIPUSOK['osztaly'] = getOsztalyByTanarId($feladoId, array('tanev'=>$SET['tanev'],'csakId' => $SET['csakId']));
		}
		$TIPUSOK['osztalyTanar'] = getTanarOsztaly($feladoId,$SET);
		break;
	    case 'szulo': /* tiltható */
		//if (uzenhet('tanar')) $TIPUSOK['tanar'] = extendUzenoTipusok(array('csakId'=>false,'tanev'=>$SET['tanev']));
		if (uzenhet('tankorSzulo')  || $SET['forRead']===true)  $TIPUSOK['tankorSzulo']  = getTankorByDiakId( __USERDIAKID , $SET['tanev'], array('csakId' => $SET['csakId']));
		if (uzenhet('osztalySzulo') || $SET['forRead']===true) $TIPUSOK['osztalySzulo'] = getDiakOsztalya(  __USERDIAKID , array('tanev'=>$SET['tanev'], 'result'=>(($SET['csakId'])?'csakid':''), 'csakId' => $SET['csakId']));
		break;
	    case 'diak':  /* tiltható */
		//if (uzenhet('tanar')) $TIPUSOK['tanar'] = extendUzenoTipusok(array('csakId'=>false,'tanev'=>$SET['tanev']));
		if (uzenhet('tankor')  || $SET['forRead']===true)  $TIPUSOK['tankor'] = getTankorByDiakId($feladoId, $SET['tanev'], array('csakId' => $SET['csakId']));
		if (uzenhet('osztaly') || $SET['forRead']===true)  $TIPUSOK['osztaly'] = getDiakOsztalya($feladoId, array('tanev'=>$SET['tanev'], 'result'=>(($SET['csakId'])?'csakid':''), 'csakId' => $SET['csakId']));
		break;
	    case 'admin': /* nem tiltható */
		$TIPUSOK['osztalySzulo'] = $TIPUSOK['osztaly'] = $TIPUSOK['osztalyTanar'] = getOsztalyok($SET['tanev']);
		$TIPUSOK['munkakozosseg'] = getMunkakozossegek(array(), array('csakId'=>$SET['csakId']));
		$TIPUSOK['tankorSzulo']  = $TIPUSOK['tankor'] = getTankorByTanev($SET['tanev']);
		break;
	    default:
		break;
	}
	return $TIPUSOK;
    }


    function extendUzenoTipusok($SET=array('csakId'=>true,'tanev'=>__TANEV,'old'=>false)) { /* Ha szülő/diák, kérdezzük le a gyermek tanköreinek tanárait */

	    $R = array();
	    $TANKOROK = getTankorByDiakId( __USERDIAKID , $SET['tanev'], array('csakId' => true));
	    for ($i=0; $i<count($TANKOROK); $i++) {
		$TT = getTankorTanarai($TANKOROK[$i]);
		for ($j=0; $j<count($TT); $j++) {
		    $T[$TT[$j]['tanarNev']] = $TT[$j]['tanarId'];

		}
	    }
	    if (is_array($T) && count($T)>0) {
		ksort($T);reset($T);
		foreach($T as $nev => $id) $R[] = array('tanarNev'=>$nev, 'tanarId'=>$id);
	    }
	    return $R;
    }

    function setUzenoFeladoId($nooverride=true) {
	if ($nooverride===true && __UZENOADMIN===true && __ASWHO=='asAdmin') $kiId = 0;
	elseif (__SZEREP=='tanar') $kiId = __USERTANARID;
	elseif (__SZEREP=='szulo')  //$kiId = __USERSZULOID; // NOTE ilyen konstans még nincs...
				    $kiId = getSzuloIdByUserAccount();
	elseif (__SZEREP=='diak') $kiId = __USERDIAKID;
	elseif (__SZEREP=='admin') $kiId = 0; // de a csak admin nem üzenhet!
	else return false;
	return $kiId;
    }

    function postUzenet($ADAT) {
	$feladoId = setUzenoFeladoId();
	$feladoTipus = __SZEREP;

	$cimzettId = $ADAT['cimzettId'];
	$cimzettTipus = $ADAT['cimzettTipus'];
	$txt = ($ADAT['txt']);
	$dbName = 'naplo_'.__INTEZMENY.'_'.$ADAT['tanev'];

	$q = "INSERT INTO `%s`.uzeno (dt,txt,feladoId,feladoTipus,cimzettId,cimzettTipus) VALUES (NOW(), '%s', %u, '%s', %u, '%s')";
	$v = array($dbName, $txt, $feladoId, $feladoTipus, $cimzettId, $cimzettTipus);
	return db_query($q,array('fv' => 'uzeno/postUzenet', 'modul' => 'naplo_intezmeny', 'values' => $v));
    }

    // v3.1
    function delUzenet($mId,$tanev=__TANEV) {
	if (!is_numeric($mId) || __UZENOADMIN!==true) return false;
	if (defined('__INTEZMENY')) {
	    $dbName = 'naplo_'.__INTEZMENY.'_'.$tanev;
	    $q = "DELETE FROM `%s`.`uzeno` WHERE `mId`=%d";
	    $params = array('debug'=>false,'values'=>array($dbName,intval($mId)),'modul'=>'naplo','fv'=>'deluzenet','detailed'=>__UZENOADMIN);
	    return db_query($q,$params);
	} else
	    return false;
    }

/*
    A flagUzenet() függvény az adott mId-t egy user szempontjából 
    (az üzenő terminológia szerint feladoId+feladoTipus ~szerep)
    flageli meg.

    Jelentései: LSB - olvasott (1=true, 0=X)
*/

    // v3.1
    function flagUzenet($ADAT) {

	$mId = $ADAT['mId'];
	$feladoId = setUzenoFeladoId();
	$feladoTipus = __SZEREP;
	$flag = $ADAT['flag'];
	$tanev = ($ADAT['tanev']=='') ? __TANEV : $ADAT['tanev'];
	if (defined('__INTEZMENY')) {
	    $dbName = 'naplo_'.__INTEZMENY.'_'.$tanev;
	    $q = "REPLACE INTO `%s`.`uzenoFlagek`(`mId`,`Id`,`Tipus`,`flag`) VALUES (%d,%d,'%s',%d)";
	    $params = array(
		'values'=>array($dbName,intval($mId),$feladoId,$feladoTipus,$flag),
		'modul'=>'naplo','fv'=>'flaguzenet',
		'detailed'=>__UZENOADMIN);
	    return db_query($q,$params);
	} else
	    return false;

    }

    function getUzenetFlagek($mIds, $tanev=__TANEV) {
	if (defined('__INTEZMENY') && is_array($mIds)) {
	    $dbName = 'naplo_'.__INTEZMENY.'_'.$tanev;
	    $q = "SELECT * FROM `uzenoFlagek` WHERE mId IN (".implode(',',$mIds).")";
	    $params = array('result'=>'assoc','keyfield'=>'mId','debug'=>false,'modul'=>'naplo','fv'=>'getUzenoFlagek','detailed'=>__UZENOADMIN);
	    return db_query($q,$params);
	} else
	    return false;	
    }

?>
