<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/felveteli.php');

    if (__FELVETELIADMIN===true) { 
	die(); // ilyen nem lehet!
    }

    $IDOSZAK = getIdoszakByTanev(array('tanev' => __TANEV, 'szemeszter' => 2, 'tipus' => array('felvételi szóbeli lekérdezés','felvételi ideiglenes rangsor lekérdezés','felvételi végeredmény lekérdezés'), 'tolDt'=>date('Y-m-d H:i:s'),'return' => '', 'arraymap'=>null));
    $now = mktime();
    for ($i=0; $i<count($IDOSZAK); $i++) {
	$tolDt= $IDOSZAK[$i]['tolDt'];
	$igDt= $IDOSZAK[$i]['igDt'];
	if (strtotime($tolDt)<=$now && $now<strtotime($igDt)) {
	    $ok = true;
	    break;
	}
    }
    if ($ok===true) {
	define('_SZOBELI_LEKERDEZHETO', true);
    } else {
	define('_SZOBELI_LEKERDEZHETO', false);
	if ($IDOSZAK[0]['tolDt']!='') $ADAT['szobeliPublikalasDt'] = $IDOSZAK[0]['tolDt'];
    }

    $action = readVariable($_POST['action'],'strictstring',null,array('lekerdezes','szobeliLekerdezes'));

    if (in_array($action,array('szobeliLekerdezes')) && _SZOBELI_LEKERDEZHETO === true) {
	$nev = readVariable($_POST['nev'],'sql');
	$oId =  readVariable($_POST['oId'],'strictstring');
        if ($nev !='' || $oId !='') {
	    $ADAT = getFelvetelizoAdatok($nev,$oId);
	    if (is_array($ADAT)) {
		$ADAT['szobeli'] = getSzobeliByoId(intval($ADAT['oId']));
		$ADAT['jelentkezes'] = getJelentkezes(intval($ADAT['oId']));
		if ($ADAT['oId']!='' && $ADAT['vegeredmeny']!='') {
    		    $ADAT['token'] = updateLevelToken($ADAT['oId']);// token generálás
		}
	    }
	}
    }

?>
