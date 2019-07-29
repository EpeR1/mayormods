<?php

    require_once('include/modules/naplo/share/diak.php');

    function getLemorzsolodas($SET = array()) { // tanev, dt

	$R = array();
	$tanev = $SET['tanev'];
	$szemeszter = $SET['szemeszter'];
	$dt = $SET['dt'];
	$elozoDt = $SET['elozoDt'];

	$DIAKADAT = array(); // local cache

	$intezmeny_lr = db_connect('naplo_intezmeny');

	// -- két egymás követő félév alatt átlagosan >=1,1 romlás
	// !! csak a jegy típusokra értelmezhető az átlagolás
	
	    //elozo
	    $q = "SELECT diakId,avg(jegy) AS avg FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('jegy') GROUP BY diakId";
	    $v = array($elozoDt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','keyfield'=>'diakId','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$A[$r[$i]['diakId']] = $r[$i]['avg'];
	    }

	    //kivalasztott
	    $q = "SELECT diakId,avg(jegy) AS avg FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('jegy') GROUP BY diakId";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		if ( isset($A[$r[$i]['diakId']]) && (- $r[$i]['avg'] + $A[$r[$i]['diakId']])>=1.1) {
		    $_diakId = $r[$i]['diakId'];
		    if (!is_array($DIAKADAT[$_diakId])) {
			$DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
			$DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		    }
		    $R['ronto'][] = array(
			'diakId' => $r[$i]['diakId'],
			'avg' => $r[$i]['avg'],
			'elozoAvg' => $A[$r[$i]['diakId']],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])

		    );
		}
	    }
	
	// -- a félév végén (év végén) a tanulmányi átlaga< 3,0
	    //kivalasztott
	    $q = "SELECT diakId,avg(jegy) AS atlag FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('jegy') GROUP BY diakId HAVING atlag<3";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$R['rosszTanulo'][] = array(
			'diakId' => $r[$i]['diakId'],
			'atlag' => $r[$i]['atlag'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])

		);
	    }

	// -- a félév végén (év végén) a tanulmányi átlaga< 2,5
	    //kivalasztott
	    $q = "SELECT diakId,avg(jegy) AS atlag FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('jegy') GROUP BY diakId HAVING atlag<2.5";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$R['nagyonRosszTanulo'][] = array(
			'diakId' => $r[$i]['diakId'],
			'atlag' => $r[$i]['atlag'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])

		);
	    }

	// -- egy-vagy több tárgyból bukik (ez lényegében most is benne van már)
	    $q = "SELECT diakId,count(*) as dbBukas FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('jegy') AND jegy='1.0' GROUP BY diakId";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$R['bukott'][] = array(
			'diakId' => $r[$i]['diakId'],
			'dbBukas' => $r[$i]['dbBukas'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		);
	    }

	// -- magatartása rossz
	    $q = "SELECT diakId FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('magatartás') AND jegy='2.0' GROUP BY diakId";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$R['rosszMagatartasu'][] = array(
			'hivatalosDt' => $dt,
			'diakId' => $r[$i]['diakId'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		);
	    }
	// -- szorgalma hanyag
	    $q = "SELECT diakId FROM zaroJegy WHERE hivatalosDt = '%s' AND jegyTipus IN ('szorgalom') AND jegy='2.0' GROUP BY diakId";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$R['hanyagSzorgalmu'][] = array(
			'diakId' => $r[$i]['diakId'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		);
	    }
	// -- 50 órát elérő igazolatlan hiányzás
	    // --TODO TANÉV!!!
	    $q = "SELECT diakId,count(*) as dbHianyzas FROM hianyzas 
		    LEFT JOIN " .__INTEZMENYDBNEV. ".diak USING (diakId)
		  WHERE dt<= '%s' AND tipus='hiányzás' AND igazolas='' AND diak.statusz='jogviszonyban van' GROUP BY diakId HAVING dbHianyzas>=50";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$_jogviszonya = getDiakJogviszonyByDts(array($_diakId),array($dt));
		if ($_jogviszonya[$_diakId][$dt]['statusz']=='jogviszonyban van') {
		    $R['igazolatlanHianyzo_50'][] = array(
			'diakId' => $r[$i]['diakId'],
			'dbHianyzas' => $r[$i]['dbHianyzas'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakJogviszony' => $_jogviszonya[$_diakId][$dt]['statusz'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		    );
		}
	    }
	
	// -- 100 órát elérő igazolt hiányzás
	    // --TODO TANÉV!!!
	    $q = "SELECT diakId,count(*) as dbHianyzas FROM hianyzas 
		    WHERE dt<= '%s' AND tipus='hiányzás' AND igazolas!=''
		    GROUP BY diakId HAVING dbHianyzas>=100";
	    $v = array($dt);
	    $r = db_query($q, array('modul'=>'naplo','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		// itt szűrhetjük ki a "hibás" diákjogviszonyúakat...
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$_jogviszonya = getDiakJogviszonyByDts(array($_diakId),array($dt));
		if ($_jogviszonya[$_diakId][$dt]['statusz']=='jogviszonyban van') {
		    $R['igazoltanHianyzo_100'][] = array(
			'diakId' => $r[$i]['diakId'],
			'dbHianyzas' => $r[$i]['dbHianyzas'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakJogviszony' => $_jogviszonya[$_diakId][$dt]['statusz'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		    );
		}
	    }
	// -- magántanulóvá vált
	    $q = "SELECT diakId FROM diakJogviszony WHERE dt BETWEEN '%s' AND '%s' AND statusz='magántanuló'";
	    $v = array($elozoDt,$dt);
	    $r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));	
	    for ($i=0; $i<count($r); $i++) {
		$_diakId = $r[$i]['diakId'];
		if (!is_array($DIAKADAT[$_diakId])) {
		    $DIAKADAT[$_diakId] = getDiakAdatById($_diakId);
		    $DIAKADAT[$_diakId]['diakOsztalya'] = getDiakOsztalya($_diakId, array('tanev'=>__TANEV,'tolDt'=>$dt,'igDt'=>$dt), $intezmeny_lr);
		}
		$_jogviszonya = getDiakJogviszonyByDts(array($_diakId),array($dt));
		if ($_jogviszonya[$_diakId][$dt]['statusz']=='magántanuló') { // még mindig
		    $R['magantanuloLett'][] = array(
			'diakId' => $r[$i]['diakId'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		    );
		}
		if ($_jogviszonya[$_diakId][$dt]['statusz']=='egyéni munkarend') { // még mindig
		    $R['magantanuloLett'][] = array(
			'diakId' => $r[$i]['diakId'],
			'diakNev' => $DIAKADAT[$_diakId]['diakNev'],
			'diakOsztalya' => ($DIAKADAT[$_diakId]['diakOsztalya'][0]['osztalyJel'])
		    );
		}
	    }
	
	// -- magántanulóvá minősítése folyamatban - ezt nem tudjuk
	// -- oltalmazott / menekült / menedékes - ezt nem tudjuk
	//  -- veszélyeztetetté vált - ez mi?
	//  -- egyéb nehezen értelmezhető feltételek... - ez mi?

	db_close($intezmeny_lr);

	return $R;

    }


?>