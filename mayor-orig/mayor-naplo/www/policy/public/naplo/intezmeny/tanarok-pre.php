<?php

    if (_RIGHTS_OK !== true) die();
    
    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/modules/naplo/share/szemeszter.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/kepesites.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');

    if (defined('__INTEZMENY') and __INTEZMENY != '') {
	$Tanevek = getTanevek();
	$Telephelyek = getTelephelyek();
	$telephelyIds = array();
	if (is_array($Telephelyek)) foreach ($Telephelyek as $index => $tAdat) $telephelyIds[] = $tAdat['telephelyId'];
	$tanev = readVariable($_POST['tanev'], 'id', defined('__TANEV') ? __TANEV : null, $Tanevek);
	$telephelyId = readVariable($_POST['telephelyId'], 'id', defined('__TELEPHELYID') ? __TELEPHELYID : null, $telephelyIds);
/*
	if ($action == 'tanevValasztas') {
    	    if (isset($tanev) && $tanev !== __TANEV) {
	    	if (updateSessionTanev($tanev)) {
	    		header('Location: '.location('index.php?page=naplo&sub=intezmeny&f=valtas'));
	    	}
	    }
    	} 	elseif ($action == 'telephelyValasztas') {
	    if (isset($telephelyId) && $telephelyId != __TELEPHELYID) {
	    	if (updateSessionTelephelyId($telephelyId)) {
	    		header('Location: '.location('index.php?page=naplo&sub=intezmeny&f=valtas'));
	    	}
	    }
	}
*/
    }
    $ADAT['tanarok'] = getTanarok(array('extraAttrs'=>'titulus,statusz,besorolas,email,YEAR(beDt) AS belepesEve,IFNULL("",YEAR(kiDt)) AS kilepesEve, elerhetoseg'));
    $ADAT['targyak'] = getTargyak(array('arraymap'=>array('targyId')));
    $lr = db_connect('naplo_intezmeny');

    for($i=0; $i<count($ADAT['tanarok']); $i++) {
        $_tanarId = $ADAT['tanarok'][$i]['tanarId'];
        $ADAT['tanarOsztaly'][$_tanarId] = getOsztalyIdsByTanarId($_tanarId, array('tanev'=>__TANEV,'csakId'=>true),$lr);
        $ADAT['tanarMunkakozosseg'][$_tanarId] = getVezetettMunkakozossegByTanarId($_tanarId,array('result'=>'assoc'),$lr);
        $ADAT['tanarok'][$i]['kepesites'] = getTanarKepesites($_tanarId);
//      $ADAT['tanarok'][$i]['kepesitesNev'] = $ADAT['kepesitesek'];
    }
    $ADAT['osztalyok'] = getOsztalyok(__TANEV,array('result'=>'assoc'),$lr);
    db_close($lr);

//    $TOOL['intezmenySelect'] = array('tipus'=>'cella', 'action' => 'intezmenyValasztas', 'post' => array());
//    $TOOL['telephelySelect'] = array('tipus'=>'cella', 'action' => 'telephelyValasztas', 'post' => array());
//    $TOOL['tanevSelect'] = array('tipus'=>'cella', 'action' => 'tanevValasztas', 'post' => array());
    
    getToolParameters();

?>
