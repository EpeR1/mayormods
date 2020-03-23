<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__DIAK && !__NAPLOADMIN && !__VEZETOSEG && !__TITKARSAG) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/oraModifier.php');
    require_once('include/modules/naplo/share/orarend.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/dolgozat.php');
    require_once('include/modules/naplo/share/kepzes.php');
    require_once('include/modules/naplo/share/nap.php');
    require_once('include/modules/naplo/share/terem.php');
    require_once('include/modules/naplo/share/tanmenet.php');
    require_once('include/modules/naplo/share/hetes.php');
    require_once('include/modules/naplo/share/helyettesitesModifier.php');
    require_once('include/modules/naplo/share/file.php');
    require_once('include/share/date/names.php');

    $ADAT['oraId' ] = $oraId = readVariable($_POST['oraId'],'id',readVariable($_GET['oraId'],'id'));
//    $ADAT['hazifeladatId' ] = $hazifeladatId = readVariable($_POST['hazifeladatId'],'id', readVariable($_GET['hazifeladatId'],'id'));
    $ADAT['hazifeladatLeiras' ] = readVariable($_POST['hazifeladatLeiras'],'string');
    $action = readVariable($_POST['action'],'strictstring',null,array('hazifeladatBeiras','hazifeladatKesz'));

    $q = "SELECT hazifeladatId FROM oraHazifeladat WHERE oraId=%u";
    $values = array($ADAT['oraId']);
    $ADAT['hazifeladatId'] = $hazifeladatId = db_query($q, array('modul'=>'naplo','result'=>'value','values'=>$values));
    $ADAT['oraAdat'] = getOraadatById($oraId);

    if (__TANAR===true && $action=='hazifeladatBeiras') {

	if ($hazifeladatId>0) { // update
	    $q = "UPDATE oraHazifeladat set hazifeladatLeiras='%s' WHERE hazifeladatId=%u";
	    $values = array($ADAT['hazifeladatLeiras'],$ADAT['hazifeladatId']);
	    $r = db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values));
	} elseif ($oraId>0) { // insert
	    $q = "INSERT IGNORE INTO oraHazifeladat (hazifeladatLeiras,oraId) VALUES ('%s',%u)";
	    $values = array($ADAT['hazifeladatLeiras'],$ADAT['oraId']);
	    $hazifeladatId = db_query($q, array('modul'=>'naplo','result'=>'insert','values'=>$values));

	}
	if ($oraId>0 && strtotime(date('Y-m-d'))>=strtotime($ADAT['oraAdat']['dt'])) {
	    $leiras = readVariable($_POST['oraLeiras'],'string');
	    updateHaladasiNaploOra($oraId, $leiras);
	}
    } elseif (__DIAK===true) {
	if (defined('__USERDIAKID') && __USERDIAKID>0) {
	    $diakId=__USERDIAKID;
	} elseif (defined('__SZULODIAKID') && __SZULODIAKID>0) {
	    $diakId=__SZULODIAKID;
	}
	if ($diakId>0) {
	    $q = "INSERT IGNORE INTO oraHazifeladatDiak (hazifeladatId,diakId,diakLattamDt) VALUES (%u,%u,NOW())";
	    $values = array($ADAT['hazifeladatId'], $diakId);
	    db_query($q, array('modul'=>'naplo','result'=>'insert','values'=>$values));

	    if ($action=='hazifeladatKesz') {
		if ($diakId>0 && $ADAT['hazifeladatId']>0) {
		$q = "UPDATE oraHazifeladatDiak SET hazifeladatDiakStatus=IF(hazifeladatDiakStatus='','kész','') WHERE hazifeladatId=%u AND diakId=%u";
		$values = array($ADAT['hazifeladatId'], $diakId);
		db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values));
		}
	    }
	    $q = "SELECT * FROM oraHazifeladatDiak WHERE hazifeladatId=%u AND diakId=%u";
	    $values = array($ADAT['hazifeladatId'],$diakId);
	    $ADAT['hazifeladatDiak'] = db_query($q, array('modul'=>'naplo','result'=>'record','values'=>$values));
	}

    }

    $q = "SELECT * FROM oraHazifeladat WHERE oraId=%u";
    $values = array($ADAT['oraId']);
    $ADAT['hazifeladatAdat'] =  db_query($q, array('modul'=>'naplo','result'=>'record','values'=>$values));

    if (__TANAR===true || __NAPLOADMIN===true || __VEZETOSEG===true) {
	$q = "SELECT *,getNev(diakId,'diak') AS diakNev FROM oraHazifeladatDiak WHERE hazifeladatId=%u ORDER BY diakNev";
	$values = array($ADAT['hazifeladatId']);
	$ADAT['hazifeladatDiak'] =  db_query($q, array('debug'=>true,'modul'=>'naplo','result'=>'indexed','values'=>$values));
    }
    $ADAT['oraAdat'] = getOraadatById($oraId);

    $TOOL['vissza'] = array('tipus'=>'vissza',
        'paramName'=>'vissza',
        'icon'=>'',
        'postOverride' => array('igDt'=>$igDt,'tanarId'=>$tanarId,'page'=>'naplo','sub'=>'haladasi','f'=>'haladasi')
    );
    if (isset($oraId)) $TOOL['tanarOraLapozo'] = array('tipus'=>'sor', 'oraId' => $oraId, 'post'=>array('tanarId'));
    getToolParameters();

?>
