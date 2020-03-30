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
    require_once('include/share/net/upload.php');
    require_once('include/share/date/names.php');

// config
    define('FILE_UPLOAD_DIR',_DOWNLOADDIR.'/private/naplo/haladasi/hazifeladat/');
    $config['lowfreespacelimit'] = 335617790; // 330MB
/*
    dump(disk_free_space($config['dir']));
    dump($file_uploads = ini_get('file_uploads')); // must be On
    dump($post_max_size = ini_get('post_max_size')); // recommended: 64M
    dump($upload_max_filesize = ini_get('upload_max_filesize')); // recommended: 60M
    dump(human_filesize(file_upload_max_size()));
*/
	    
    if (disk_free_space(FILE_UPLOAD_DIR)<$config['lowfreespacelimit']) {
	$disableUpload = true;
	if (__NAPLOADMIN === true) $_SESSION['alert'][] = 'info:nincs elég szabad hely, ezért nem tudsz filet feltölteni';
    }
    if (!ini_get('file_uploads')) {
	$disableUpload = true;
	if (__NAPLOADMIN === true) $_SESSION['alert'][] = 'info:feltöltés kikapcsolva (file_uploads)';
    }
    if ($disableUpload===true) define('FILE_UPLOAD_ENABLED',false);
    else define('FILE_UPLOAD_ENABLED',true);
// --

    $ADAT['oraId' ] = $oraId = readVariable($_POST['oraId'],'id',readVariable($_GET['oraId'],'id'));
//    $ADAT['hazifeladatId' ] = $hazifeladatId = readVariable($_POST['hazifeladatId'],'id', readVariable($_GET['hazifeladatId'],'id'));
    $ADAT['hazifeladatLeiras' ] = readVariable($_POST['hazifeladatLeiras'],'string');
    $action = readVariable($_POST['action'],'strictstring',null,array('hazifeladatBeiras','hazifeladatKesz','hazifeladatFeltoltes','lattam'));

    $q = "SELECT hazifeladatId FROM oraHazifeladat WHERE oraId=%u";
    $values = array($ADAT['oraId']);
    $ADAT['hazifeladatId'] = $hazifeladatId = db_query($q, array('modul'=>'naplo','result'=>'value','values'=>$values));
    $ADAT['oraAdat'] = getOraadatById($oraId);
    $ADAT['nevsor'] = getTankorDiakjaiByInterval($ADAT['oraAdat']['tankorId'], __TANEV, $ADAT['oraAdat']['dt'], $ADAT['oraAdat']['dt']);

    if (__TANAR===true && $action=='hazifeladatBeiras') {
	$hazifeladatFeltoltesEngedely = readVariable($_POST['hazifeladatFeltoltesEngedely'],'id',0);
	if ($hazifeladatId>0) { // update
	    $q = "UPDATE oraHazifeladat set hazifeladatLeiras='%s',hazifeladatFeltoltesEngedely=%u WHERE hazifeladatId=%u";
	    $values = array($ADAT['hazifeladatLeiras'],$hazifeladatFeltoltesEngedely,$ADAT['hazifeladatId']);
	    $r = db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values));
	} elseif ($oraId>0) { // insert
	    $q = "INSERT IGNORE INTO oraHazifeladat (hazifeladatLeiras,oraId,hazifeladatFeltoltesEngedely) VALUES ('%s',%u,%u)";
	    $values = array($ADAT['hazifeladatLeiras'],$ADAT['oraId'],$hazifeladatFeltoltesEngedely);
	    $hazifeladatId = db_query($q, array('modul'=>'naplo','result'=>'insert','values'=>$values));

	}
	if ($oraId>0 && strtotime(date('Y-m-d'))>=strtotime($ADAT['oraAdat']['dt'])) {
	    $leiras = readVariable($_POST['oraLeiras'],'string');
	    updateHaladasiNaploOra($oraId, $leiras);
	}
    } elseif (__TANAR===true && $action=='lattam') {
	$lr = db_connect('naplo');
	db_start_trans($lr);
	$lattamDiakIds = readVariable($_POST['lattam'],'id');
	$megsemlattamDiakIds = readVariable($_POST['megsemlattam'],'id');
	for ($i=0; $i<count($lattamDiakIds); $i++) {
	    $_diakId = $lattamDiakIds[$i];
	    $values = array($ADAT['hazifeladatId'], $_diakId);
	    $q = "SELECT count(*) AS db FROM oraHazifeladatDiak WHERE hazifeladatId=%u AND diakId=%u";
	    $db = db_query($q, array('modul'=>'naplo','result'=>'value','values'=>$values),$lr);
	    if ($db==1) {
		$q = "UPDATE oraHazifeladatDiak SET tanarLattamDt=NOW() WHERE hazifeladatId=%u AND diakId=%u";
		$r = db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values),$lr);
	    } else {
		$q = "INSERT IGNORE INTO oraHazifeladatDiak (hazifeladatId,diakId,tanarLattamDt) VALUES (%u,%u,NOW())";
		db_query($q, array('modul'=>'naplo','result'=>'insert','values'=>$values),$lr);
	    }
	}
	for ($i=0; $i<count($megsemlattamDiakIds); $i++) {
	    $_diakId = $megsemlattamDiakIds[$i];
	    $q = "UPDATE oraHazifeladatDiak SET tanarLattamDt=null WHERE hazifeladatId=%u AND diakId=%u";
	    $values = array($ADAT['hazifeladatId'], $_diakId);
	    db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values),$lr);
	}
	db_commit($lr);
	db_close($lr);
    } elseif (__DIAK===true) {
	if (defined('__USERDIAKID') && __USERDIAKID>0) {
	    $diakId=__USERDIAKID;
	} elseif (defined('__SZULODIAKID') && __SZULODIAKID>0) {
	    $diakId=__SZULODIAKID;
	}

	if ($diakId>0) {
	    // $q = "INSERT IGNORE INTO oraHazifeladatDiak (hazifeladatId,diakId,diakLattamDt) VALUES (%u,%u,NOW())";
	    // $values = array($ADAT['hazifeladatId'], $diakId);
	    // db_query($q, array('modul'=>'naplo','result'=>'insert','values'=>$values));

	    oraHazifeladatDiakLatta($ADAT['hazifeladatId']);

	    if ($action=='hazifeladatKesz') {
		if ($diakId>0 && $ADAT['hazifeladatId']>0) {
		$q = "UPDATE oraHazifeladatDiak SET hazifeladatDiakStatus=IF(hazifeladatDiakStatus='','kész','') WHERE hazifeladatId=%u AND diakId=%u";
		$values = array($ADAT['hazifeladatId'], $diakId);
		db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values));
		}
	    }

	    if (FILE_UPLOAD_ENABLED===true && $action=='hazifeladatFeltoltes') {
		if (is_array($_FILES) && $_FILES['upfile']['name']!='') {
		    $FILEADAT['subdir'] = FILE_UPLOAD_DIR;
		    $ext = filename2ext($_FILES['upfile']['name']);
		    $ext2 = filemime2ext($_FILES['upfile']['type']);
		    if ($ext != $ext2) {
			// $_SESSION['alert'][] = 'info:file_mime_type_extension_mismatch:'.$ext.':'.$ext2;
			if ($ext2!='') $ext = $ext2;
		    }
		    $FILEADAT['filename'] = $hazifeladatId.'_'.$diakId.'_'.uniqid().'.'.$ext;
		    try {
			$sikeresFeltoltes = mayorFileUpload($FILEADAT, false);
		    } catch (Exception $e) {
			dump($e);
		    }

		    if ($sikeresFeltoltes===true) {
			// --todo unlink existing?--

			$q = "SELECT hazifeladatDiakFilename FROM oraHazifeladatDiak WHERE hazifeladatId=%u AND diakId=%u";
			$values = array($ADAT['hazifeladatId'],$diakId);
			$oldFilename = db_query($q, array('modul'=>'naplo','result'=>'value','values'=>$values));
			
			if (file_exists(FILE_UPLOAD_DIR.$oldFilename)) unlink(FILE_UPLOAD_DIR.$oldFilename);

			$origFilename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $_FILES['upfile']['name']);
			$origFilename = mb_ereg_replace("([\.]{2,})", '', $_FILES['upfile']['name']);
			if ($origFilename=='') $origFilename = $FILEADAT['filename'];
			$q = "UPDATE oraHazifeladatDiak SET hazifeladatDiakFilename='%s',hazifeladatDiakOrigFilename='%s' WHERE hazifeladatId=%u AND diakId=%u";
			$values = array($FILEADAT['filename'],$origFilename,$ADAT['hazifeladatId'], $diakId);
			db_query($q, array('modul'=>'naplo','result'=>'update','values'=>$values));
		    }
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
	$ADAT['hazifeladatDiak'] =  db_query($q, array('debug'=>false,'modul'=>'naplo','result'=>'indexed','values'=>$values));
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
