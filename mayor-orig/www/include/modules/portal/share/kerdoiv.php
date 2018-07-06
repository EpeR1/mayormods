<?php

    function getKerdoiv($policy='public') {

	$v = array($policy);
	$q = "select * from kerdesek where sorszam=(select max(sorszam) from kerdesek WHERE policy='%s')";
	$R['kerdes'] = db_query($q, array('fv' => 'getKerdoiv', 'modul' => 'portal', 'values'=>$v,'result' => 'record'));

	$q = "select * from valaszok where kszam=(select max(sorszam) from kerdesek WHERE policy='%s')";
	$R['valaszok'] = db_query($q, array('fv' => 'getKerdoiv', 'modul' => 'portal', 'result' => 'indexed', 'values'=>$v, 'keyfiled' => 'kszam'));
	return $R;

    }

    function szavazotte($kerdoivId) {
	if (defined('_USERACCOUNT') && defined('_POLICY') && _USERACCOUNT!='' && _POLICY != '') {
	    $q = "SELECT count(*) AS db FROM kerdoivSzavazott WHERE kerdoivId=%u AND policy='%s' AND userAccount='%s'";
	    $v = array($kerdoivId, _POLICY, _USERACCOUNT);
	    return (db_query($q, array('fv' => 'szavaz', 'modul' => 'portal', 'result'=>'value','values' => $v))>0);
	} else {
	    if (isset($_SESSION['kerdoivSzavazott']))
		return true;
	    else
		return false;
	} 
    }

    function szavaz($id, $db = 1, $kerdoivId) {
	if (defined('_USERACCOUNT') && defined('_POLICY')) {
	    $q = "INSERT INTO kerdoivSzavazott (kerdoivId,policy,userAccount) VALUES (%u,'%s','%s')";
	    $v = array($kerdoivId, _POLICY, _USERACCOUNT);
	    db_query($q, array('fv' => 'szavaz', 'modul' => 'portal', 'values' => $v));
	}
	$q = "UPDATE valaszok SET pontszam=pontszam + (%u) WHERE sorszam=%u";
	$v = array($db, $id);
	return db_query($q, array('fv' => 'szavaz', 'modul' => 'portal', 'values' => $v));
    }

    function getRegiKerdesek() {
	$q = "SELECT * FROM kerdesek ORDER BY sorszam DESC LIMIT 10";
	return db_query($q, array('modul'=>'portal','result'=>'indexed'));
    }


?>