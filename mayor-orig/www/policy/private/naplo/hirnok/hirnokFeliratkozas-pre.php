<?php

    require_once('include/modules/naplo/share/hirnok.php');
    global $_TANEV;

    if (__EMAIL_ENABLED!==true) $_SESSION['alert'][] = 'page:hiba:az e-mail küldés nincs engedélyezve, keresd az üzemeltetőt!';

    $tolDt = readVariable($_POST['tolDt'],'date',readVariable($_GET['tolDt'], 'date', getTanitasinapvissza(7)));
    if (strtotime($tolDt)>strtotime(date('Y-m-d'))) $tolDt = date('Y-m-d',strtotime('-10 day'));

    $osztalyId = readVariable($_POST['osztalyId'], 'id');
    if (__NAPLOADMIN === true) { // csak adminnak engedjük kiválasztani - lásd még include
	if ($action=='hirnokFeliratkozas') {
	    $S['naploId'] = readVariable($_POST['naploId'],'numeric');
	    $S['naploTipus'] = readVariable($_POST['naploTipus'],'string',null,array('tanar','diak'));
	    $S['email'] = readVariable($_POST['email'],'email');
	    $S['hirnokFeliratkozasId'] = readVariable($_POST['hirnokFeliratkozasId'],'numeric');
	    if ($S['hirnokFeliratkozasId']>0) delHirnokFeliratkozas($S);
	    elseif ($S['email']!='') addHirnokFeliratkozas($S);
	}
	$ADAT['hirnokFeliratkozas'] = $feliratkozott = getHirnokFeliratkozasok(true);
//	if ($diakId==0 && count($feliratkozott['diak'])>0) $diakId = $feliratkozott['diak'];
//	if ($tanarId==0 && count($feliratkozott['tanar'])>0) $tanarId = $feliratkozott['tanar'];
//	if ($tanarId==0 && defined('__USERTANARID')) $tanarId = __USERTANARID;
    } else {
        if (__DIAK===true) { // diák nézet + szülő?
                $diakId = $naploId = __USERDIAKID;
		$naploTipus = 'diak'; // szulo???
        } elseif (__TANAR ===true) { // tanár nézet
                $tanarId = $naploId = __USERTANARID;
		$naploTipus = 'tanar';
        }
	if ($action=='hirnokFeliratkozas') {
	    $S['email'] = readVariable($_POST['email'],'email');
	    $S['naploId'] = $naploId;
	    $S['naploTipus'] = $naploTipus;

	    $S['hirnokFeliratkozasId'] = readVariable($_POST['hirnokFeliratkozasId'],'numeric');
	    if ($S['hirnokFeliratkozasId']>0) delHirnokFeliratkozas($S);
	    elseif ($S['email']!='') addHirnokFeliratkozas($S);
	}
	$ADAT['email'] = ''; // lekérdezhetnénk az objektum e-mail címét később
	$ADAT['hirnokFeliratkozas'] = $feliratkozott = getHirnokFeliratkozasok(true);

    }

?>