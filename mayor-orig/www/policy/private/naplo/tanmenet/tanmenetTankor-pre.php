<?php

    if (_RIGHTS_OK !== true) die();
    if (!__TANAR && !__VEZETOSEG && !__NAPLOADMIN) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/szemeszter.php');
//    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
//    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/tanmenet.php');

    $ADAT['tanev'] = __TANEV;
    $ADAT['tanarId'] = $tanarId = readVariable($_POST['tanarId'], 'id', readVariable($_GET['tanarId'], 'id', __TANAR?__USERTANARID:null));

    if (isset($tanarId)) {
        $ADAT['tankorIds'] = getTankorByTanarId($tanarId, __TANEV, array('csakId' => true));
	$ADAT['tankorok'] = getTankorAdatByIds($ADAT['tankorIds'], array('tanev' => __TANEV, 'dt' => $_TANEV['kezdesDt']));
	$ADAT['tankorTanmenet'] = getTanmenetByTankorIds($ADAT['tankorIds'], array('tanev' => $ADAT['tanev']));
	$tanmenetAdat = $ADAT['tanmenetek'] = array();
	if (is_array($ADAT['tankorok']))
	foreach ($ADAT['tankorok'] as $tankorId => $tAdat) {
	    if (!is_array($ADAT['tanmenetek'][ $tAdat['targyId'] ])) {
		$ADAT['tanmenetek'][ $tAdat['targyId'] ] = getTanmenetByTargyId($tAdat['targyId'], array('result'=>'assoc'));
		if (is_array($ADAT['tanmenetek'][ $tAdat['targyId'] ]))
		foreach ($ADAT['tanmenetek'][ $tAdat['targyId'] ] as $tanmenetId => $tanAdat) {
		    // Mikor módosítható az adott tanmenet?
		    $ADAT['tanmenetek'][ $tAdat['targyId'] ][ $tanmenetId ]['modosithato'] =  (
			__NAPLOADMIN 								// admin bármikor
                        || (
                            (__VEZETOSEG || __USERTANARID == $tanAdat['tanarId']) 		// vezetőségi tag és a létrehozó szaktanár...
                            && ($tanAdat['statusz'] == 'új' || $tanAdat['statusz'] == 'kész')	// ... ha még nincs jóváhagyva
                        )

		    ); 
		    $ADAT['tanmenetAdat'][$tanmenetId] = $tanAdat;
		}
	    }
	}

//echo '<pre>'; var_dump($ADAT['tanmenetek']); echo '</pre>';

	$ADAT['idoszak'] = getIdoszakByTanev(array('tanev' => $ADAT['tanev'], 'tipus' => array('tanmenet leadás'), 'tolDt' => date('Y-m-d'), 'igDt' => date('Y-m-d')));
	define('__TANMENETLEADASIDOSZAK',(count($ADAT['idoszak']) > 0));
	if (!__TANMENETLEADASIDOSZAK) $_SESSION['alert'][] = 'info:nincs_tanmenetleadas_idoszak:tanev='.$ADAT['tanev'];
        define('__MODOSITHAT',
            __NAPLOADMIN // admin bármikor
	    || __VEZETOSEG || __USERTANARID == $ADAT['tanarId'] // vezetőség és szaktanár is bármikor
//            || (
//                (__VEZETOSEG || __USERTANARID == $ADAT['tanarId']) // vezetőségi tag és a létrehozó szaktanár...
//                && __TANMENETLEADASIDOSZAK // megfelelő időszakban vagyunk
//            )
        );
        

	if (__MODOSITHAT===true && $action == 'hozzarendeles') {
	    if (isset($_POST['masolas'])) $action = 'masolas';
	    elseif (isset($_POST['uj'])) $action = 'uj';
	    elseif (isset($_POST['modosit'])) $action = 'modosit';
	    elseif (isset($_POST['info'])) $action = 'info';
	} else {
	    $action = '';
	}
	$ADAT['tankorId'] = readVariable($_POST['tankorId'], 'id');

	if (__MODOSITHAT === true) { 
	  if ($action == 'hozzarendeles') {
	    $ADAT['tanmenetId'] = readVariable($_POST['tanmenetId'], 'id');
	    tankorTanmenetHozzarendeles($ADAT);
	    $ADAT['tankorTanmenet'][$ADAT['tankorId']] = $ADAT['tanmenetId'];
	  } elseif (__TANAR && $action == 'uj') {
	    header('Location: '.location('index.php?page=naplo&sub=tanmenet&f=ujTanmenet&tankorId='.$ADAT['tankorId']));
	  } elseif (__TANAR && $action == 'masolas') {
	    $eredetiTanmenetId = readVariable($_POST['tanmenetId'], 'id');
	    if (isset($eredetiTanmenetId)) {
		$ADAT['tanmenetId'] = tanmenetDuplikalas($eredetiTanmenetId, __USERTANARID);
		tankorTanmenetHozzarendeles($ADAT);
		header('Location: '.location('index.php?page=naplo&sub=tanmenet&f=tanmenetModositas&tanmenetId='.$ADAT['tanmenetId']));
	    }
	  } elseif ($action == 'modosit') {
	    $ADAT['tanmenetId'] = readVariable($_POST['tanmenetId'], 'id');
	    if (isset($ADAT['tanmenetId'])) header('Location: '.location('index.php?page=naplo&sub=tanmenet&f=tanmenetModositas&tanmenetId='.$ADAT['tanmenetId']));
	  } elseif ($action == 'info') {
	    $ADAT['tanmenetId'] = readVariable($_POST['tanmenetId'], 'id');
	    if (isset($ADAT['tanmenetId'])) header('Location: '.location('index.php?page=naplo&sub=tanmenet&f=tanmenetInfo&tanmenetId='.$ADAT['tanmenetId']));
	  }
	}
    }


    $TOOL['tanarSelect'] = array('tipus'=>'cella', 'paramName'=>'tanarId', 'post'=>array());
//    $TOOL['osztalySelect']= array('tipus'=>'cella', 'paramName'=>'osztalyId', 'post'=>array());
    getToolParameters();
?>
