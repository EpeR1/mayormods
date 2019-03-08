<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/tanmenet.php');
//    require_once('include/share/date/names.php');

    $tanev = $ADAT['tanev'] = readVariable($_POST['tanev'], 'numeric unsigned', defined('__TANEV')?__TANEV:null);
    $ADAT['idoszak'] = getIdoszakByTanev(array('tanev' => $ADAT['tanev'], 'tipus' => array('tanmenet leadás'), 'tolDt' => date('Y-m-d'), 'igDt' => date('Y-m-d')));
    define('__TANMENETLEADASIDOSZAK',(count($ADAT['idoszak']) > 0));
    if (!__TANMENETLEADASIDOSZAK) $_SESSION['alert'][] = 'info:nincs_tanmenetleadas_idoszak:tanev='.$ADAT['tanev'];
    else $_SESSION['alert'][] = 'info:tanmenetleadas_idoszak_vege:'.substr($ADAT['idoszak'][0]['igDt'],0,10);

    $ADAT['tanarok'] = getTanarok(array('tanev' => $tanev));
    
    $ADAT['tankorIds'] = array();
    for ($i = 0; $i < count($ADAT['tanarok']); $i++) {
	$tanarId = $ADAT['tanarok'][$i]['tanarId'];
        $ADAT['tanarok'][$i]['tankorIds'] = getTankorByTanarId($tanarId, __TANEV, array('csakId' => true));
	$ADAT['tankorIds'] = array_unique(array_merge($ADAT['tankorIds'], $ADAT['tanarok'][$i]['tankorIds']));
    }
    $ADAT['tankorok'] = getTankorAdatByIds($ADAT['tankorIds'], array('tanev' => __TANEV, 'dt' => $_TANEV['kezdesDt']));
    $ADAT['tankorTanmenet'] = getTanmenetByTankorIds($ADAT['tankorIds'], array('tanev' => $ADAT['tanev']));
    $ADAT['tanmenetek'] = getTanmenetek();

//echo '<pre>'; var_dump($ADAT['tanmenetek']); echo '<pre>';
    // Melyik tanárnak hány "leadott jóváhagyott" / "leadott, nem jóváhagyott" / "nem leadott" tanmenet-tankör hozzárendelése van?
    for ($i = 0; $i < count($ADAT['tanarok']); $i++) {
	$tanarId = $ADAT['tanarok'][$i]['tanarId'];
	foreach ($ADAT['tanarok'][$i]['tankorIds'] as $tankorId) {
	    if (isset($ADAT['tankorTanmenet'][$tankorId])) {
		$tanmenetId = $ADAT['tankorTanmenet'][$tankorId];
		if (
		    $ADAT['tanmenetek'][$tanmenetId]['statusz'] == 'jóváhagyott'
		    || $ADAT['tanmenetek'][$tanmenetId]['statusz'] == 'publikus'
		) $ADAT['tanarok'][$i]['db']['jóváhagyott']++;
		elseif ($ADAT['tanmenetek'][$tanmenetId]['statusz'] == 'kész') $ADAT['tanarok'][$i]['db']['kész']++;
		else $ADAT['tanarok'][$i]['db']['új']++;
//		if ($ADAT['tanmenetek'][$tanmenetId]['jovahagyva']) $ADAT['tanarok'][$i]['db']['jovahagyva']++;
//		else $ADAT['tanarok'][$i]['db']['nincs jovahagyva']++;
	    } else {
		$ADAT['tanarok'][$i]['db']['hiányzik']++;
	    }
	}
    }
?>
