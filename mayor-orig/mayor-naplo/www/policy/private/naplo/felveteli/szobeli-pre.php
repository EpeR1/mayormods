<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/felveteli.php');

    $IDOSZAK = getIdoszakByTanev(array('tanev' => __TANEV, 'szemeszter' => 2, 'tipus' => array('felvételi szóbeli lekérdezés'), 'tolDt'=>date('Y-m-d H:i:s'),'return' => '', 'arraymap'=>null));
    $now = mktime();
    for ($i=0; $i<count($IDOSZAK); $i++) {
	$tolDt= $IDOSZAK[$i]['tolDt'];
	$igDt= $IDOSZAK[$i]['igDt'];
	if (strtotime($tolDt)<=$now && $now<strtotime($igDt)) {
	    $ok = true;
	    break;
	}
    }
    if (__FELVETELIADMIN===true) {
	$ok = true;
    }

    if ($ok===true) {
	define('_SZOBELI_LEKERDEZHETO', true);
    } else {
	define('_SZOBELI_LEKERDEZHETO', false);
	if ($IDOSZAK[0]['tolDt']!='') $ADAT['szobeliPublikalasDt'] = $IDOSZAK[0]['tolDt'];
    }
    $action = readVariable($_POST['action'],'strictstring',null,array('lekerdezes','szobeliLekerdezes','modositas','ujSzobeli','szobeliModositas','ujFelveteli','diakTorol'));

    if ($action=='modositas' && __FELVETELIADMIN===true) {

	$lr = db_connect('naplo');
	$kulcsMezok = array('_nev','_oId');
	$_nev = readVariable($_POST['_nev'],'sql');
	$_oId =  readVariable($_POST['_oId'],'strictstring');
	// Jelentkezések
	$jelentkezesTagozat = readVariable($_POST['jelentkezesTagozat'],'id');
	if (is_array($jelentkezesTagozat)) {
	    $q = "DELETE FROM felveteli_jelentkezes WHERE oId='%s' AND tagozat NOT IN (".implode(',',$jelentkezesTagozat).")";
	    $v = array($_oId);
	    db_query($q,array('values'=>$v),$lr);
	    for ($i=0; $i<count($jelentkezesTagozat); $i++) {
		$q = "INSERT IGNORE INTO felveteli_jelentkezes (oId,tagozat) VALUES ('%s',%u)";
		$v = array($_oId,$jelentkezesTagozat[$i]);
		db_query($q,array('values'=>$v),$lr);
	    }

	}    
	// Alapadatok
	$modosithatoMezok = array('nev','oId','szuldt','an','lakcim_irsz','lakcim_telepules','lakcim_utcahazszam',
				  'tartozkodasi_irsz','tartozkodasi_telepules','tartozkodasi_utcahazszam','omkod','atlag','magyar','matek','pont');
	foreach( $modosithatoMezok as $_key) {
	    $q = "UPDATE felveteli SET `%s`='%s' WHERE oId='%s'";
	    $v = array($_key,readVariable($_POST[$_key],'sql'),$_oId);
	    db_query($q,array('values'=>$v),$lr);
	}

	$diakTorol = readVariable($_POST['diakTorol'],'id');
	if ($diakTorol==1) {
	    $q = "DELETE FROM felveteli WHERE oId='%s'";
	    $v = array($_oId);
	    db_query($q,array('debug'=>true,'values'=>$v),$lr);
	    unset($oId);
	    unset($nev);
	}
	db_close($lr);

    } elseif ($action=='ujFelveteli' && __FELVETELIADMIN===true) {

	$_nev = readVariable($_POST['_nev'],'sql');
	$_oId =  readVariable($_POST['_oId'],'strictstring');
	if ($_nev!='' && $_oId!='') {
	    $lr = db_connect('naplo');
	    // SELECT -> nev, oid
	    // else
	    $q = "INSERT INTO felveteli (oId,nev) VALUES('%s','%s')";
	    $v = array($_oId,$_nev);
	    $r = db_query($q,array('debug'=>true,'values'=>$v,'result'=>'insert'),$lr);
	    if ($r!==false) {
		$nev = $_nev;
		$oId = $_oId;
	    }
	    db_close($lr);
	}

    } elseif ($action=='ujSzobeli' && __FELVETELIADMIN===true) {

	$oId =  readVariable($_POST['oId'],'strictstring');
	$lr = db_connect('naplo');
	$q = "INSERT INTO felveteli_szobeli (oId) VALUES('%s')";
	$v = array($oId);
	$felveteliSzobeliId = db_query($q,array('values'=>$v,'result'=>'insert'),$lr);
	$modosithatoMezok = array('szoveg','bizottsag','nap','napdt','ido','tagozat','szobeliTipus');
	foreach( $modosithatoMezok as $_key) {
	    $_val = readVariable($_POST[$_key],'sql');
	    if ($_val!='') {
		$q = "UPDATE felveteli_szobeli SET `%s`='%s' WHERE felveteliSzobeliId=%u";
		$v = array($_key,$_val,$felveteliSzobeliId);
		db_query($q,array('values'=>$v),$lr);
	    }
	}
	db_close($lr);	

    } elseif ($action=='szobeliModositas' && __FELVETELIADMIN===true) {

	$oId =  readVariable($_POST['oId'],'strictstring');
	$felveteliSzobeliId =  readVariable($_POST['felveteliSzobeliId'],'id');
	$lr = db_connect('naplo');
	if ($felveteliSzobeliId>0) {
	    $q = "DELETE FROM felveteli_szobeli WHERE felveteliSzobeliId=%u AND oId = '%s'";
	    $v = array($felveteliSzobeliId,$oId);
	    db_query($q,array('values'=>$v),$lr);
	}
	foreach($_POST as $_pk => $_pv) {
	    if (substr($_pk,0,11) == 'szobelipont') {
		list($_tmp, $_felveteliSzobeliId ) = explode('_',$_pk);
		$felveteliSzobeliId = readVariable($_felveteliSzobeliId,'id');
		$szobelipont = readVariable($_pv,'id');
		$q = "UPDATE felveteli_szobeli SET szobelipont=%u WHERE felveteliSzobeliId=%u";
		$v = array($szobelipont,$felveteliSzobeliId);
		db_query($q,array('values'=>$v),$lr);
	    }
	}
	db_close($lr);	
    } else {
	$nev = readVariable($_POST['nev'],'sql');
	$oId =  readVariable($_POST['oId'],'strictstring');
    }

    if (in_array($action,array('szobeliLekerdezes','modositas','ujSzobeli','szobeliModositas','ujFelveteli')) && _SZOBELI_LEKERDEZHETO === true) {

	if ($nev=='') $nev = readVariable($_POST['nev'],'sql');
	if ($oId=='') $oId =  readVariable($_POST['oId'],'strictstring');

        if (__FELVETELIADMIN===true || $oId !='') {
	    $ADAT = getFelvetelizoAdatok($nev,$oId);
	    if (is_array($ADAT)) {
		$ADAT['szobeli'] = getSzobeliByoId(intval($ADAT['oId']));
  		// $EREDMENY = getIdeiglenesRangsor(intval($ADAT['oId']));
		//$EREDMENY = getSzobeliEredmeny($ADAT['id']);
		$ADAT['jelentkezes'] = getJelentkezes(intval($ADAT['oId']));
	    }
	    //$EREDMENY = getIrasbeliEredmeny($nev,$oId);
	    $ADAT['tagozat'] = getFelveteliTagozat();
	}
    }

// TODO:
    if ($ADAT['oId']!='') { //++ vegeredmeny
        $ADAT['token'] = updateLevelToken($ADAT['oId']);// token generálás
    }

?>
