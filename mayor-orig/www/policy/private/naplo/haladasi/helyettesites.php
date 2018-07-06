<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();
    
    global $dt, $HELYETTESITES; 
    global $manual, $Tanarok, $Termek;
    global $mozgat, $csere, $orarend;
    global $oraAdat, $ujDt, $TANAR_DT_NAPI_ORAK, $TANAR_UJDT_NAPI_ORAK, $tanarId;
    global $csTanarId, $csDt, $CSTANAR_CSDT_NAPI_ORAK;
    global $Orak;
    global $tools;
    global $_TANEV;

if ($_TANEV['statusz']=='aktív') {

    if ($manual != '') {

	// Kézi adatmódosítás

	putKeziBeallitas($oraAdat, $Termek, $Tanarok);

    } elseif ($mozgat != '') {

	putMozgatas($tanarId, $oraAdat, $TANAR_DT_NAPI_ORAK, $ujDt, $TANAR_UJDT_NAPI_ORAK);

    } elseif ($csere != '') {

	putCsere($tanarId, $oraAdat, $TANAR_DT_NAPI_ORAK, $csTanarId, $csDt, $CSTANAR_CSDT_NAPI_ORAK, $Tanarok); 

    } elseif (isset($_POST['csereAttekintes']) && $_POST['csereAttekintes'] != '') {
	putCsereAttekintes($oraId, $Orak);
    } elseif ($orarend != '') {
    } else {

	// A szokásos helyettesítés oldal...
//	putHianyzoTanarok($HELYETTESITES['tanarok'], $HELYETTESITES['helyettesites']['tanarIds'], $dt);
	putHianyzoTanarForm($HELYETTESITES['tanarok'], $HELYETTESITES['helyettesites']['tanarIds'], $dt);
	putHianyzoOrak($HELYETTESITES, $dt);

    }

}
?>
