<?php

    function getDiakJegyek($ADAT) {
	global $_TANEV;
	$q = "SELECT diakId,targyId, dt, SUBSTRING(dt,6,2) AS ho, jegy, jegyTipus 
		FROM jegy LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId) 
		WHERE diakId IN (".implode(',', array_fill(0, count($ADAT['diakIds']), '%u')).") ORDER BY jegy.dt";
	$r = db_query($q, array('fv' => 'getDiakJegyek', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $ADAT['diakIds']));
	$RET = array();
	for ($i = 0; $i < count($r); $i++) {
	    /* Melyik félév is lehetett? */
	    $_felev = (in_date_interval($r[$i]['dt'],$_TANEV['szemeszter'][1]['kezdesDt'],$_TANEV['szemeszter'][1]['zarasDt'])) ? 1:2;

	    if (
		$ADAT['diakAdat'][ $r[$i]['diakId'] ]['osztalyDiak'][0]['kiDt'] == ''
		|| strtotime($r[$i]['dt']) <= strtotime($ADAT['diakAdat'][ $r[$i]['diakId'] ]['osztalyDiak'][0]['kiDt'])
	    )
		$RET[ $r[$i]['diakId'] ][ $r[$i]['targyId'] ][ $r[$i]['ho'] ][] = $r[$i];
		$RET[ $r[$i]['diakId'] ][ 'felevenkent' ][ $r[$i]['targyId'] ][ $_felev ][] = $r[$i];
	}
	return $RET;
    }

?>
