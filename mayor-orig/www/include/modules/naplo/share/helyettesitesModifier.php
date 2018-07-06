<?php

    require_once('include/modules/naplo/share/ora.php');       

    function hianyzasEsJegyHozzarendelesTorles($oraId, $olr = '', $tanev='') {


	if ($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;

	if ($tanev!='') {
	    $_tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	    db_selectDb($_tanevDb,$lr);
	}

	if (!is_array($oraId)) $oraId = array($oraId);
//$oraIdList = implode(',', $oraId);
//else $oraIdList = $oraId;
	// Az érintett hiányzások id-inek lekérdezése - naplózás céljából...
	$q = "SELECT hianyzasId, diakId, dt, ora, oraId, tipus, statusz, igazolas FROM hianyzas
		WHERE oraId IN (".implode(',', array_fill(0, count($oraId), '%u')).")";
	$H = db_query($q, array('fv' => 'hianyzasEsJegyHozzarendelesTorles', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $oraId), $lr);
//	$r = m_ysql_query($q, $lr) or die(m_ysql_error());
//	if (!$r) {
//	    $_SESSION['alert'][] = 'message:m_ysql_query_failure:hianyzasEsJegyHozzarendelesTorles:'.$q.':'.m_ysql_error($lr);
//	    if($olr == '') $lr = db_close($lr);
//	    return false;
//	}
	$hIds = array();
	if (is_array($H) && count($H) > 0) {
	    foreach ($H as $key => $hAdat) {
		extract($hAdat, EXTR_PREFIX_ALL, 'tmp');
		$hIds[] = $tmp_hianyzasId;
		logAction(
		    array(
			'szoveg'=>"Helyettesítés/óraelmaradás => törölt hiányzás: hianyzasId=$tmp_hianyzasId, diakId=$tmp_diakId, dt=$tmp_dt, ora=$tmp_ora, oraId=$tmp_oraId, tipus=$tmp_tipus, statusz=$tmp_statusz, igazolas=$tmp_igazolas",
			'table'=>'hianyzas'
		    ), 
		    $lr
		);
	    }
	    // --TODO: hianyzasTorles() - fv-t hívjuk meg!
	    $q = "DELETE FROM hianyzas WHERE hianyzasId IN (".implode(',', array_fill(0, count($hIds), '%u')).")";
	    db_query($q, array('fv' => 'hianyzasEsJegyHozzarendelesTorles', 'modul' => 'naplo', 'values' => $hIds, 'result' => 'affected rows'), $lr);
	}
	// Az elmaradt órákhoz rendelt jegyek hozzárendelésének törlése
	$q = "UPDATE jegy SET oraId=NULL WHERE oraId IN (".implode(',', array_fill(0, count($oraId), '%u')).")";
	$H = db_query($q, array('fv' => 'hianyzasEsJegyHozzarendelesTorles', 'modul' => 'naplo', 'result' => 'affected rows', 'values' => $oraId), $lr);

	if ($olr == '') $lr = db_close($lr);
	return true;
    }


    function masTartja($oraId, $ki, $tipus, $olr = null) {

	if (is_null($ki) || $ki==0) {
	    $_SESSION['alert'][] = '::masTartja():ki értéke nulla vagy NULL!';
//	    $q = "UPDATE ora SET kit=ki,ki=NULL,tipus='%s' WHERE oraId=%u";
//	    $v = array($tipus, $oraId);
//	    return db_query($q, array('fv' => 'masTartja', 'modul' => 'naplo', 'values' => $v), $olr);
	} else {
	    $q = "UPDATE ora SET kit=ki,ki=%u,tipus='%s',modositasDt=now() WHERE oraId=%u";
	    $v = array($ki, $tipus, $oraId);
	    return db_query($q, array('fv' => 'masTartja', 'modul' => 'naplo', 'values' => $v), $olr);
	}
    }

    function oraElmarad($oraId, $olr = null, $tanev = null) {

	$O = getOraAdatById($oraId);

	if ($olr == '') $lr = db_connect('naplo');
	else $lr = $olr;

	if ($tanev!='') {
	    $_tanevDb = tanevDbNev(__INTEZMENY, $tanev);
	    db_selectDb($_tanevDb,$lr);
	}
	if ($O['eredet'] == 'plusz') {
	    // benne van-e egy cserében - mert akkor nem törölhető
	    $q = "SELECT count(*) FROM cserePluszOra WHERE oraId=%u";
	    $O['csere'] = db_query($q, array('fv' => 'oraElmarad', 'modul' => 'naplo', 'result' => 'value', 'values' => array($oraId)),$lr);
	}

	$torol = true;
	if (
	    ($O['eredet'] == 'órarend' && $O['tipus'] == 'normál') // normál órarendi óra
	    || ($O['eredet'] == 'plusz' && $O['csere'] > 0 && $O['tipus'] == 'normál') // cserélt, normállá alakult, plusz óra
	    || ($O['eredet'] == 'plusz' && $O['tipus'] == 'normál máskor') // cserében lévő normál, plusz óra
	) {
	    $q = "UPDATE ora SET kit=ki,ki=NULL,tipus='elmarad',modositasDt=now() WHERE oraId=%u";
	} elseif (
	    ($O['eredet'] == 'órarend' && in_array($O['tipus'], array('helyettesítés','felügyelet','összevonás')))
	    || ($O['eredet'] == 'plusz' && $O['csere'] > 0 && in_array($O['tipus'], array('helyettesítés','felügyelet','összevonás')))
	) {
	    $q = "UPDATE ora SET ki=NULL,tipus='elmarad',modositasDt=NOW() WHERE oraId=%u";
	} elseif (
	    $O['eredet'] == 'plusz' && in_array($O['tipus'], array('helyettesítés','felügyelet','összevonás','normál'))
	    || $O['tipus'] == 'egyéb'
	) {
	    $q = "DELETE FROM ora WHERE oraId=%u";
	} else { $torol = false; }

	if ($torol) {
	    HianyzasEsJegyHozzarendelesTorles($oraId, $lr, $tanev);
	    $ret = db_query($q, array('fv' => 'oraElmarad', 'modul' => 'naplo', 'values' => array($oraId)),$lr);
	} else {
	    $ret = false;
	}

	if ($olr == '') $lr = db_close($lr);
	return $ret;

    }

?>
