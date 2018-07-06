<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';
    else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/tankorModifier.php');

	$tankorId = readVariable($_POST['tankorId'], 'id',null);
	$tankorSzemeszter = $_POST['tankorSzemeszter'];
	$tankorNevExtra = readVariable($_POST['tankorNevExtra'],'string');
	$osztalyIds = readVariable($_POST['osztalyIds'], 'id');

	tankorOsztalyHozzarendeles($tankorId, $osztalyIds); // A tankör nevét nem módosítja!!
	tankorSzemeszterHozzarendeles($tankorId, $tankorSzemeszter); // A tankör nevét nem módosítja
	setTankorNev($tankorId, $tankorNevExtra);

	$tmp = getTankorAdat($tankorId);
	$targyAdat = getTargyById($tmp[$tankorId][0]['targyId']);

	$_JSON = $tmp[$tankorId][0];
	$_JSON['tankorNevTargyNelkul'] = str_replace($targyAdat['targyNev'].' ','',$_JSON['tankorNev']);
	$_JSON['tankorNevReszei'] = array(
    	    'evfOszt' => substr($_JSON['tankorNev'],0, strpos($_JSON['tankorNev'],' ')),
    	    'targyNev' => $targyAdat['targyNev'],
    	    'tankorJel' =>$_JSON['tankorJel']
	);
	$_JSON['tankorNevReszei']['tankorNevExtra'] = trim(str_replace($_JSON['tankorNevReszei']['evfOszt'].' '.$_JSON['tankorNevReszei']['targyNev'],'',
            str_replace($_JSON['targyJel'],'',$_JSON['tankorNev'])));
	$_JSON['tankorSzemeszter'] = getTankorSzemeszterei($tankorId);
	$_JSON['osztalyIds'] = getTankorOsztalyai($tankorId);
	$_JSON['alert'] = $_SESSION['alert'];
	$_JSON['osztalyok'] = getOsztalyok();
	$_JSON['tankorTipusok'] = getTankorTipusok();
	// $_JSON['post'] = $_POST;
    }

?>