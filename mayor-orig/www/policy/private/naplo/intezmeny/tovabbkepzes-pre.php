<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN!==true && __TANAR!==true && __VEZETOSEG!==true) $_SESSION['alert'][]='page:insufficient_access';

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/tanarModifier.php');

    global $SSSHH;
    $SSSHH .= "<script type=\"text/javascript\" src=\"https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['timeline','corechart']}]}\"></script>\n";

    $tanev = $ADAT['tanev'] = readVariable($_POST['tanev'],'numeric',__TANEV);

    if (__TANAR) $tanarId = __USERTANARID;
    if (__VEZETOSEG || __NAPLOADMIN) define('__MODOSITHAT',true);
    else define('__MODOSITHAT',false);

/* ------------------------------------------------- */
  if (__MODOSITHAT) {
    if ($action=='modTanarTovabbkepzesForduloDt') {
	$_tanarId = readVariable($_POST['tanarId'],'id');
	$_forduloDt = readVariable($_POST['forduloDt'],'date');
	$_JSON['result'] = true;
	$_JSON['tanarId'] = $_tanarId;
	$_JSON['forduloDt'] = $_dt;
	$_JSON['result'] = updateTanarAdat($_tanarId,array('tovabbkepzesForduloDt'=>$_forduloDt));
    } elseif ($action=='ujTovabbkepzes') {
	$ADAT['tovabbkepzoIntezmenyId'] = readVariable($_POST['tovabbkepzoIntezmenyId'],'id',null);
	if (is_null($ADAT['tovabbkepzoIntezmenyId'])) {
	    $UJINTEZMENY['intezmenyRovidNev'] = readVariable($_POST['intezmenyRovidNev'],'string',null);
	    $UJINTEZMENY['intezmenyNev'] = readVariable($_POST['intezmenyNev'],'string',null);
	    if ($UJINTEZMENY['intezmenyRovidNev']!='' && $UJINTEZMENY['intezmenyNev']!='') $ADAT['tovabbkepzoIntezmenyId'] = ujTovabbkepzoIntezmeny($UJINTEZMENY);
	}
	if ($ADAT['tovabbkepzoIntezmenyId']>0) {
	    $ADAT['tovabbkepzesNev'] = readVariable($_POST['tovabbkepzesNev'],'string',null);
	    $ADAT['oraszam'] = intval(readVariable($_POST['oraszam'],'numeric unsigned',null));
	    $ADAT['kategoria'] = readVariable($_POST['kategoria'],'string',null);
	    $ADAT['akkreditalt'] = ($ADAT['kategoria']=='egyéb') ? 0:1;
	    ujTovabbkepzes($ADAT);
	}
    } elseif ($action=='modTovabbkepzes') {
	$MOD = readVariable($_POST['tovabbkepzesId'],'id',null);
	for ($i=0; $i<count($MOD); $i++) {
	    $ADAT['tovabbkepzesId'] = $MOD[$i];
	    $ADAT['tovabbkepzesNev'] = readVariable($_POST['tovabbkepzesNev_'.$MOD[$i]],'string',null);
	    $ADAT['oraszam'] = intval(readVariable($_POST['oraszam_'.$MOD[$i]],'numeric unsigned',null));
	    $ADAT['kategoria'] = readVariable($_POST['kategoria_'.$MOD[$i]],'string',null);
	    $ADAT['akkreditalt'] = ($ADAT['kategoria']=='egyéb') ? 0:1;
	    modTovabbkepzes($ADAT);
	}
    } elseif ($action=='ujTovabbkepzesTanar') { // az egész táblázat egyszerre módosul - lehetne máshogy is
	$ADAT['tovabbkepzesId'] = readVariable($_POST['tovabbkepzesId'],'id',null);
	$ADAT['tanarId'] = readVariable($_POST['tanarId'],'id',null);
	$ADAT['tolDt'] = readVariable($_POST['tolDt'],'date',null);
	$ADAT['igDt'] = readVariable($_POST['igDt'],'date',null);
	$ADAT['tanusitvanyDt'] = readVariable($_POST['tanusitvanyDt'],'date',null);
	$ADAT['tanusitvanySzam'] = readVariable($_POST['tanusitvanySzam'],'string',null);
	if ($ADAT['tovabbkepzesId']>0 && $ADAT['tanarId']>0)
	    ujTovabbkepzesTanar($ADAT);
	$MOD = readVariable($_POST['tovabbkepzesTanar'],'string',null);
	if (is_array($MOD) && count($MOD)>0) {
	    for ($i=0;$i<count($MOD); $i++) {
		list($_tovabbkepzesId,$_tanarId) = explode('_',$MOD[$i]);
		$M = array();
		$M['tovabbkepzesId'] = $_tovabbkepzesId;
		$M['tanarId'] = $_tanarId;
		$M['tolDt'] = readVariable($_POST['tolDt_'.$_tovabbkepzesId.'_'.$_tanarId],'date',null);
		$M['igDt'] = readVariable($_POST['igDt_'.$_tovabbkepzesId.'_'.$_tanarId],'date',null);
		$M['tanusitvanyDt'] = readVariable($_POST['tanusitvanyDt_'.$_tovabbkepzesId.'_'.$_tanarId],'date',null);
		$M['tanusitvanySzam'] = readVariable($_POST['tanusitvanySzam_'.$_tovabbkepzesId.'_'.$_tanarId],'string',null);
		if ($_POST['tovabbkepzesTorles_'.$_tovabbkepzesId.'_'.$_tanarId]=='-') {
		    delTovabbkepzesTanar($M);
		} else {
		    modTovabbkepzesTanar($M);
		}
	    }
	}
    } elseif ($action == 'ujTovabbkepzesTE') {
	$U = readVariable($_POST['TE'],'string',null);
	for ($i=0; $i<count($U); $i++) {
	    list($_D['tovabbkepzesId'],$_D['tanarId'],$_D['tanev']) = explode('_',$U[$i]);
	    $_D['reszosszeg'] = readVariable($_POST['reszosszeg_'.$U[$i]],'numeric unsigned',0);
	    $_D['tamogatas'] = readVariable($_POST['tamogatas_'.$U[$i]],'numeric unsigned',0);
	    $_D['tovabbkepzesStatusz'] = readVariable($_POST['TE_statusz_'.$U[$i]],'enum','',array('','terv','jóváhagyott','elutasított','megszűnt','megszakadt','teljesített'));
//	    $_D['tovabbkepzesStatusz'] = readVariable($_POST['TE_statusz_'.$U[$i]],'string','');
	    $_D['tavollet'] = readVariable($_POST['tavollet_'.$U[$i]],'string','');
	    $_D['helyettesitesRendje'] = readVariable($_POST['helyettesitesRendje_'.$U[$i]],'string','');
	    $_D['prioritas'] = readVariable($_POST['prioritas_'.$U[$i]],'string','');
	    if ($_D['tovabbkepzesStatusz']=='') delTovabbkepzesTE($_D);
	    else modTovabbkepzesTE($_D);
	}
	$keretOsszeg = readVariable($_POST['keretOsszeg'],'numeric unsigned',0);
	modKeretOsszeg($ADAT['tanev'],$keretOsszeg);
    }

  }
/* ------------------------------------------------- */

    $ADAT['tovabbkepzoIntezmenyek'] = getTovabbkepzoIntezmenyek();
    $ADAT['tovabbkepzesek'] = getTovabbkepzesek();
    $ADAT['tovabbkepzesTanar'] = getTanarTovabbkepzesek();
    $ADAT['lehetsegesTovabbkepzesek'] = getTanarTovabbkepzesByEv($ADAT['tanev']);
    $ADAT['tovabbkepzesTerv'] = getTovabbkepzesTerv($ADAT['tanev']);
    $ADAT['tanarok'] = getTanarok(
	array('extraAttrs'=>'tovabbkepzesForduloDt','összes'=>true)
    );
    $ADAT['tanarTovabbkepzesCiklus'] = getTanarTovabbkepzesCiklus();
    $ADAT['keretOsszeg'] = getKeretosszeg($ADAT['tanev']);
    $ADAT['tovabbkepzesFolyamat']=getTanarTovabbkepzesFolyamat();
    $TOOL['tanevSelect'] = array('tipus'=>'cella', 'action' => 'tanevValasztas', 'post' => array(), 'tervezett' => true);
    getToolParameters();
?>