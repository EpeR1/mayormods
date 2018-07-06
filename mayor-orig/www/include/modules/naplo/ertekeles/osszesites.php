<?php
/* // átkerült a share/kerdoiv.php-be!!!

    function getKerdoivStat($kerdoivId) {

	global $_TANEV;

	// Kérdőív címe, határidői
        $q = "SELECT * FROM kerdoiv WHERE kerdoivId=$kerdoivId";
        list($ret) = _m_y_query($q, array('fv' => 'getKerdoivStat', 'db' => 'naplo'));

	// A kérdőív kérdései
        $q = "SELECT * FROM kerdoivKerdes WHERE kerdoivId=$kerdoivId ORDER BY kerdesId";
        $ret['kerdes'] = _m_y_query($q, array('fv' => 'getKerdoivStat/kerdes', 'db' => 'naplo'));

	// A kérdőív válaszai
        $ret['valaszIds'] = array();
	for ($i = 0; $i < count($ret['kerdes']); $i++) {
            $q = "SELECT * FROM kerdoivValasz WHERE kerdesId=".$ret['kerdes'][$i]['kerdesId']." ORDER BY valaszId";
            $ret['kerdes'][$i]['valasz'] = _m_y_query($q, array('fv' => 'getKerdoivStat/valasz', 'db' => 'naplo'));
	    for ($j = 0; $j < count($ret['kerdes'][$i]['valasz']); $j++) $ret['valaszIds'][] = $ret['kerdes'][$i]['valasz'][$j]['valaszId'];
        }	

	// A kérdőív címzettjei
        $q = "SELECT * FROM kerdoivCimzett WHERE kerdoivId=$kerdoivId";
        $ret['cimzett']  = _m_y_multiassoc_query($q, 'cimzettTipus', array('fv' => 'getKerdoivStat/cimzett', 'db' => 'naplo'));
	// A tankör típusú címzettek tanára(i)
	$ret['tanarNev'] = array();
	if (is_array($ret['cimzett']['tankor']) && count($ret['cimzett']['tankor']) > 0) {
	    for ($i = 0; $i < count($ret['cimzett']['tankor']); $i++) {
		$tankorId = $ret['cimzett']['tankor'][$i]['cimzettId'];
		$tanarIds = getTankorTanaraiByInterval(
		    $tankorId, array('tanev' => __TANEV, 'tolDt' => $ret['kerdes']['tolDt'], 'igDt' => $ret['kerdes']['igDt'], 'result' => 'csakId')
		);
		for ($j = 0; $j < count($tanarIds); $j++) {
		    $ret['tanarTankorei'][$tanarIds[$j]][] = $tankorId;
		    if (!isset($ret['tanarNev'][ $tanarIds[$j] ])) $ret['tanarNev'][ $tanarIds[$j] ] = getTanarNevById($tanarIds[$j]);
		}
	    }
	}

	$q = "SELECT * FROM kerdoivValaszSzam WHERE valaszId IN (".implode(',', $ret['valaszIds']).") ORDER BY cimzettTipus,cimzettId,valaszId";
	$tmp = _m_y_query($q, array('fv' => 'getKerdoivStat/szavazat', 'db' => 'naplo'));
	for ($i = 0; $i < count($tmp); $i++)
	    $ret['szavazat'][ $tmp[$i]['cimzettTipus'] ][ $tmp[$i]['cimzettId'] ][ $tmp[$i]['valaszId'] ] = $tmp[$i]['szavazat'];

	$tmp = getTankorok(array("tanev=".__TANEV)); 
	for ($i = 0; $i < count($tmp); $i++) {
	    $ret['tankorAdat'][ $tmp[$i]['tankorId'] ] = $tmp[$i];
	    $ret['tankorAdat'][ $tmp[$i]['tankorId'] ]['letszam'] = getTankorLetszam($tmp[$i]['tankorId'], array('refDt' => $_TANEV['zarasDt']));
	}
        return $ret;

    }
*/

?>
