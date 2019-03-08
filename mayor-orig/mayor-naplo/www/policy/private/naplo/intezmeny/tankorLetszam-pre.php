<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG && !__TANAR) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/targy.php');
        require_once('include/modules/naplo/share/munkakozosseg.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/osztaly.php');

        require_once('include/modules/naplo/share/szemeszter.php');
        require_once('include/modules/naplo/share/tankorBlokk.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/jegy.php');
        require_once('include/modules/naplo/share/jegyModifier.php'); //????
        require_once('include/share/date/names.php');

	$mkId = readVariable($_POST['mkId'],'id');
	$targyId = readVariable($_POST['targyId'],'id');
	$tankorId = readVariable($_POST['tankorId'],'id', readVariable($_GET['tankorId'],'id') );
	$tanev = readVariable($_POST['tanev'],'numeric');

	$ADAT['osztalyonkent'] = readVariable($_POST['osztalyonkent'],'bool');
	$ADAT['tankorLetszamLimit'] = readVariable($_POST['tankorLetszamLimit'],'numeric');
	$ADAT['targyId'] = readVariable($_POST['targyId'],'id');
	$ADAT['mkId'] = readVariable($_POST['mkId'],'id');

        if (!isset($tanev)) $tanev=__TANEV;

        if ($tanev!=__TANEV) $TA = getTanevAdat($tanev); else $TA = $_TANEV;

        $ADAT['tanev'] = $tanev;

        /* Dátumok */
        if (isset($_POST['refDt']) && $_POST['refDt'] != '') $refDt = $_POST['refDt'];
        elseif (time()<strtotime($TA['kezdesDt'])) $refDt = $TA['kezdesDt'];
        else  $refDt = date('Y-m-d');

        $ADAT['refDt'] = $refDt;

        if (isset($_POST['igDt']) && $_POST['igDt'] != '') $ADAT['igDt'] = $igDt = $_POST['igDt'];
        $_POST['tolDt']=$_POST['refDt'];

       // tankörök lekérdzése
        if (isset($mkId)) {
            $ADAT['tankorok'] = getTankorByMkId($mkId, $tanev);
        } elseif (isset($targyId)) {
            $ADAT['tankorok'] = getTankorByTargyId($targyId, $tanev, array('idonly'=>false));
        } elseif (isset($osztalyId)) {
            $ADAT['tankorok'] = getTankorByOsztalyId($osztalyId, $tanev, array('tanarral' => true));
        } elseif (isset($tanarId)) {
            $ADAT['tankorok'] = getTankorByTanarId($tanarId, $tanev,
                array('csakId' => false, 'tolDt' => '', 'igDt' => '', 'tanarral' => true)
            );
        } else {
	    // ez kicsit sokáig tart sajnos
	    $ADAT['tankorok'] = getTankorByTanev($tanev);
	}
	$lr = db_connect('naplo_intezmeny');

	for ($i=0; $i<count($ADAT['tankorok']); $i++) {
	    $_tankorId = $ADAT['tankorok'][$i]['tankorId'];
            $ADAT['tankorok'][$i]['letszam'] = getTankorLetszam($_tankorId, array('refDt'=>$ADAT['refDt']), $lr );
            if ($ADAT['osztalyonkent']==1 && $ADAT['tankorok'][$i]['letszam']!=0) $ADAT['tankorok'][$i]['letszamOsztaly'] = getTankorLetszamOsztalyonkent($_tankorId, array('tanev'=>$tanev,'refDt'=>$ADAT['refDt']), $lr );
            //$ADAT['tankorok'][$i]['osztalyai'] = getTankorOsztalyaiByTanev($_tankorId, $tanev, array('result' => 'id', 'tagokAlapjan' => true));
            $ADAT['tankorok'][$i]['tanarai'] = getTankorTanarai($_tankorId, $lr);
        }

	db_close($lr);

    	//$ADAT['osztaly'] = getOsztalyok($tanev,array('result'=>'assoc'));
        // -------------------------------------------------------------------------
        $TOOL['tanevSelect'] = array('tipus'=>'cella','paramName' => 'tanev',
            'tervezett'=>false,
            'post'=>array('mkId','targyId','tankorId','dt'));
        $TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post'=>array('tanev','dt'));
        $TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'post'=>array('mkId','tanev','dt'));
//        $TOOL['tankorSelect'] = array('tipus'=>'cella','paramName'=>'tankorId', 'post'=>array('tanev','mkId','targyId','dt'));
//        $TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=tankor'),
//            'titleConst' => array('_TANKOR'), 'post' => array('tankorId','mkId','targyId','dt','tanev'));

        $TOOL['tanevLapozo'] = array('tipus'=>'sor','paramName'=>'tanev', 'post'=>array('mkId','targyId','tankorId','dt'),
                        'tanev'=>$tanev,'tervezett'=>false);

        // megj: ha nincs munkaterv, akkor a selectben nem lesz kiválasztva semmi...
        $TOOL['datumSelect'] = array(
            'tipus'=>'sor',
            'paramName' => 'refDt',
            'hanyNaponta' => 1,
            'tolDt' => date('Y-m-d', strtotime($TA['kezdesDt'])),
            'igDt' => $TA['zarasDt'],
//            'napTipusok' => array('tanítási nap', 'speciális tanítási nap'),
            'post'=>array('mkId','targyId','tankorId','tanev')
        );
	getToolParameters();
    }
?>
