<?php

    if (_RIGHTS_OK !== true) die();
    if (__TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true) return false;

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tankorBlokk.php');
    require_once('include/modules/naplo/share/targy.php');

    $osztalyId = readVariable($_POST['osztalyId'], 'id');

    $_JSON['osztalyId'] = $osztalyId;
    $_JSON['osztalyAdat'] = getOsztalyAdat($osztalyId);
    $_JSON['nev'] =     $_JSON['osztalyAdat']['osztalyJel'];

/*
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
    $_JSON['osztalyok'] = getOsztalyok();
    $_JSON['tankorTanar'] = getTankorTanarai($tankorId);
    $_JSON['tankorDiak'] = //getTankorDiakjai($tankorId);
			   getTankorDiakjaiByInterval($tankorId);
    $_JSON['tankorBlokk'] = getTankorBlokkByTankorId(array($tankorId),__TANEV,array('blokkNevekkel'=>true));
*/
    $_JSON['visibleData'] = true;
?>