<?php


    if (_RIGHTS_OK !== true) die();


    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/verseny.php');
//    $ADAT['versenyek'] =  getVersenyek();
//    $ADAT['targyak'] = getTargyak();

/*    
    require_once('include/modules/naplo/share/intezmenyek.php');
    require_once('include/modules/naplo/share/szemeszter.php');

    $intezmeny = readVariable($_POST['intezmeny'], 'strictstring', defined('__INTEZMENY') ? __INTEZMENY : null );
    if ($action == 'intezmenyValasztas') {
    	if (isset($intezmeny) && $intezmeny !== __INTEZMENY) {
	    	if (updateSessionIntezmeny($intezmeny)) {
			if (updateNaploSettings($intezmeny))
	    		    header('Location: '.location('index.php?page=naplo&sub=intezmeny&f=valtas'));
	    	}
    	}
    } 

    if (defined('__INTEZMENY') and __INTEZMENY != '') {
	$Tanevek = getTanevek();
	$Telephelyek = getTelephelyek();
	$telephelyIds = array();
	if (is_array($Telephelyek)) foreach ($Telephelyek as $index => $tAdat) $telephelyIds[] = $tAdat['telephelyId'];
	$tanev = readVariable($_POST['tanev'], 'id', defined('__TANEV') ? __TANEV : null, $Tanevek);
	$telephelyId = readVariable($_POST['telephelyId'], 'id', defined('__TELEPHELYID') ? __TELEPHELYID : null, $telephelyIds);
	if ($action == 'tanevValasztas') {
    	    if (isset($tanev) && $tanev !== __TANEV) {
	    	if (updateSessionTanev($tanev)) {
	    		header('Location: '.location('index.php?page=naplo&sub=intezmeny&f=valtas'));
	    	}
	    }
    	} elseif ($action == 'telephelyValasztas') {
	    if (isset($telephelyId) && $telephelyId != __TELEPHELYID) {
	    	if (updateSessionTelephelyId($telephelyId)) {
	    		header('Location: '.location('index.php?page=naplo&sub=intezmeny&f=valtas'));
	    	}
	    }
	}
    }

    $TOOL['intezmenySelect'] = array('tipus'=>'cella', 'action' => 'intezmenyValasztas', 'post' => array());
    $TOOL['telephelySelect'] = array('tipus'=>'cella', 'action' => 'telephelyValasztas', 'post' => array());
    $TOOL['tanevSelect'] = array('tipus'=>'cella', 'action' => 'tanevValasztas', 'post' => array());
    
    getToolParameters();
*/
?>
