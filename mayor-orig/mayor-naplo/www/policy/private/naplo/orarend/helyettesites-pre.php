<?php

    if (_RIGHTS_OK !== true) die();

	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/targy.php');
        require_once('include/modules/naplo/share/munkakozosseg.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/osztaly.php');

        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/share/date/names.php');

        require_once('include/modules/naplo/share/helyettesites.php');
        require_once('include/modules/naplo/share/szemeszter.php');
        require_once('include/modules/naplo/share/terem.php');


    //$mkId = $_POST['mkId'];
    if (isset($_POST['targyId'])  && intval($_POST['targyId']) != 0) $targyId = intval($_POST['targyId']);
    if (isset($_POST['tankorId']) && intval($_POST['tankorId']) != 0) $tankorId = intval($_POST['tankorId']);
    if (isset($_POST['tankorId']) && intval($_POST['tankorId']) != 0) $tankorId = intval($_POST['tankorId']);
    $telephelyId = readVariable($_POST['telephelyId'], 'id', null);

                //$tanev = $_POST['tanev'];
                //$action = $_POST['action'];
    if (isset($_POST['osztalyId']) && intval($_POST['osztalyId']) != 0) $osztalyId = intval($_POST['osztalyId']);
    if (isset($_POST['tanarId'])  && intval($_POST['tanarId']) != 0 ) $tanarId = intval($_POST['tanarId']);
    if (isset($_POST['diakId'])   && intval($_POST['diakId']) != 0  ) $diakId = intval($_POST['diakId']);
    if (isset($_POST['het'])      && intval($_POST['het']) != 0     ) $het = intval($_POST['het']);
    if (isset($_POST['mkId'])     && intval($_POST['mkId']) != 0    ) $mkId = intval($_POST['mkId']);
    if (isset($_POST['tolDt'])    && strtotime($_POST['tolDt']) > 0) $tolDt = $_POST['tolDt'];

    /* Az órarendihét kiválasztása */
        if (!isset($tolDt)) $tolDt = date('Y-m-d');
        if (strtotime($tolDt) > strtotime($_TANEV['zarasDt'])) $tolDt = $_TANEV['zarasDt'];
        elseif (strtotime($tolDt) < strtotime($_TANEV['kezdesDt'])) $tolDt = $_TANEV['kezdesDt'];
        if ($tolDt != '') $het = getOrarendiHetByDt($tolDt);
        if ($het == '') $het = getLastOrarend();
        //$igDt = date('Y-m-d', mktime(0,0,0,date('m',strtotime($tolDt)), date('d',strtotime($tolDt))+6, date('Y',strtotime($tolDt))));
	$igDt = $tolDt;

    if ($osztalyId!='') {
	$TANKORIDK = getTankorByOsztalyId($osztalyId,__TANEV,array('csakId'=>true));
	$ADAT['orak'] = getHelyettesitendoOrak(array('osztalyId'=>$osztalyId,'tolDt'=>$tolDt,'igDt'=>$igDt,'tankorIdk'=>$TANKORIDK));
    } elseif ($tanarId!='') {
	$TANKORIDK = getTankorByTanarId($tanarId,__TANEV,array('csakId'=>true));
	$ADAT['orak'] = getHelyettesitendoOrak(array('tolDt'=>$tolDt,'igDt'=>$igDt,'tankorIdk'=>$TANKORIDK));
    } else {
	$ADAT['orak'] = getHelyettesitendoOrak(array('tolDt'=>$tolDt,'igDt'=>$igDt,'telephelyId'=>$telephelyId));
    }
    $ADAT['tankorok'] = getTankoradatByIds($ADAT['orak']['tankorok'],array('dt'=>$tolDt)); // $ADAT['orak'] at vizsgáld meg!
    $ADAT['tanarok'] = getTanarok(array('result'=>'assoc'));
    $ADAT['termek'] = getTermek(array('result'=>'assoc'));

        /* TOOL ME :) */
        $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('tanarId','osztalyId','tankorId'),
            'paramName' => 'tolDt', 'hanyNaponta' => 1,
            'override'=>true, // használathoz még át kell írni pár függvényt!!!
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
        );
        $TOOL['telephelySelect'] = array('tipus'=>'cella', 'paramName'=>'telephelyId', 'post'=>array('tolDt','osztalyId','tanarId'));                                                 
                                                                                                                                           
        //$TOOL['orarendiHetSelect'] = array('tipus'=>'cella' , 'paramName' => 'het', 'post'=>array('targyId','tankorId','osztalyId','tanarId'), 'disabled'=>true);
        //$TOOL['munkakozossegSelect'] = array('tipus'=>'cella','paramName' => 'mkId', 'post'=>array('tanev'));
        //$TOOL['targySelect'] = array('tipus'=>'cella', 'paramName' => 'targyId', 'post'=>array('het'));
//        if ($osztalyId!='') {
//            $TOOL['diakSelect'] = array('tipus'=>'sor','paramName'=>'diakId', 'post'=>array('tolDt','osztalyId'));
//        } else
//            $TOOL['munkakozossegSelect'] = array('tipus'=>'sor','paramName'=>'mkId', 'post'=>array('tolDt'));
        $TOOL['osztalySelect']= array('tipus'=>'cella','paramName'=>'osztalyId', 'post'=>array('tolDt'));
        $TOOL['tanarSelect'] = array('tipus'=>'cella','paramName'=>'tanarId', 'post'=>array('tolDt'));
//        if ($osztalyId!='' || $tanarId!='' || $diakId!='' || $mkId!='') $TOOL['tankorSelect'] = array('tipus'=>'sor','paramName'=>'tankorId', 'post'=>array('tolDt','osztaly

        getToolParameters();

?>
