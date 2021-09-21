<?php

    function getTanarLekotottOraszam($tanarId) {

	$q = "SELECT sum(oraszam)/2  FROM tankorSzemeszter LEFT JOIN tankorTanar USING (tankorId) WHERE tanev=".__TANEV." AND tanarId=%u";

	global $_TANEV;

	$q = "select sum(oraszam/db) from (
		select tankorId, sum(oraszam)/2 as oraszam, (
		    select count(tanarId) from tankorTanar where tankorId=tankorSzemeszter.tankorId and beDt<='".$_TANEV['kezdesDt']."' 
			and (kiDt is NULL OR kiDt>='".$_TANEV['zarasDt']."')
		) as db 
		from tankorSzemeszter left join tankorTanar using (tankorId) 
		where tanev=".__TANEV." and tanarId=%u and beDt<='".$_TANEV['kezdesDt']."' 
		and (kiDt is NULL or kiDt>='".$_TANEV['zarasDt']."') group by tankorId
	    ) as tankorOraszamPerTanar";

	return db_query($q, array('fv'=>'getTanarLekotottOraszam','modul'=>'naplo_intezmeny','result'=>'value','values'=>array($tanarId)));

    }

    function getTankorokBySzuro($Szuro) {
	/*
	    osztaly U tanar U tanarNelkul || targy U mk U tanar U tanarNelkul ||  (osztaly M targy) U (osztaly M mk) U tanar U tanarNelkul
	*/
	global $_TANEV;

	$vanOsztaly = (is_array($Szuro['osztalyIds']) && count($Szuro['osztalyIds'])>0);
	$vanMk = (is_array($Szuro['mkIds']) && count($Szuro['mkIds'])>0);
	$vanTargy = (is_array($Szuro['targyIds']) && count($Szuro['targyIds'])>0);
	$vanTanar = (is_array($Szuro['tanarIds']) && count($Szuro['tanarIds'])>0);
	$vanTanarNelkuliek = (bool)$Szuro['tanarNelkuliTankorok'];

	$v = $q = array();
	if ($vanOsztaly && !$vanMk && !$vanTargy) {
	    $q[] = "SELECT tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    LEFT JOIN tankorOsztaly USING (tankorId)
		    WHERE osztalyId IN (".implode(',', array_fill(0, count($Szuro['osztalyIds']), '%u')).")
		    AND tanev=".__TANEV."
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";
	    $v = array_merge($v, $Szuro['osztalyIds']);
	}
	if ($vanMk) {
	    if ($vanOsztaly) {
		$q[] = "SELECT tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    LEFT JOIN targy USING (targyId)
		    WHERE mkId IN (".implode(',', array_fill(0, count($Szuro['mkIds']), '%u')).")
		    AND tanev=".__TANEV."
		    AND tankorId IN (
			SELECT tankorId FROM tankorOsztaly WHERE osztalyId IN (".implode(',', array_fill(0, count($Szuro['osztalyIds']), '%u')).")
		    )
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";
		$v = array_merge($v, $Szuro['mkIds'], $Szuro['osztalyIds']);
	    } else {
		$q[] = "SELECT tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    LEFT JOIN targy USING (targyId)
		    WHERE mkId IN (".implode(',', array_fill(0, count($Szuro['mkIds']), '%u')).")
		    AND tanev=".__TANEV."
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";
		$v = array_merge($v, $Szuro['mkIds']);
	    }
	}
	if ($vanTargy) {
	    if ($vanOsztaly) {
		$q[] = "SELECT tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    WHERE targyId IN (".implode(',', array_fill(0, count($Szuro['targyIds']), '%u')).")
		    AND tanev=".__TANEV."
		    AND tankorId IN (
			SELECT tankorId FROM tankorOsztaly WHERE osztalyId IN (".implode(',', array_fill(0, count($Szuro['osztalyIds']), '%u')).")
		    )
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";
		$v = array_merge($v, $Szuro['targyIds'], $Szuro['osztalyIds']);
	    } else {
		$q[] = "SELECT tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    WHERE targyId IN (".implode(',', array_fill(0, count($Szuro['targyIds']), '%u')).")
		    AND tanev=".__TANEV."
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";
		$v = array_merge($v, $Szuro['targyIds']);
	    }
	}
	if ($vanTanar) {

	    $q[] = "SELECT tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    LEFT JOIN tankorTanar USING (tankorId)
		    WHERE tanarId IN (".implode(',', array_fill(0, count($Szuro['tanarIds']), '%u')).")
		    AND tanev=".__TANEV."
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";
	    $v = array_merge($v, $Szuro['tanarIds']);

	}
	if ($vanTanarNelkuliek) {
	    $q[] = "SELECT tankor.tankorId AS tankorId, tankorNev, targyId, tankorTipusId, avg(oraszam) AS hetiOraszam
		    FROM tankor LEFT JOIN tankorSzemeszter USING (tankorId)
		    LEFT JOIN tankorTanar ON tankor.tankorId=tankorTanar.tankorId AND beDt<='".$_TANEV['kezdesDt']."' AND (kiDt IS NULL OR kiDt>='".$_TANEV['zarasDt']."')
		    WHERE tanarId IS NULL
		    AND tanev=".__TANEV."
		    GROUP BY tankorId, tankorNev, targyId, tankorTipusId
		    ORDER BY tankorNev, tankorId";

	}

	if (count($q) > 0) {
	    $query = '('.implode(') UNION DISTINCT (', $q).')';
	    $return = db_query($query, array('debug'=>false,'fv'=>'getTankorokBySzuro','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));

	    for ($i=0; $i<count($return); $i++) {
		$return[$i]['tanarIds'] = getTankorTanaraiByInterval($return[$i]['tankorId'], array('tanev' => __TANEV, 'result' => 'idonly'));
		// A tankör lekötött óraszáma
		$tmp = getTankorTervezettOraszamok((array)$return[$i]['tankorId']);
		foreach (array(1, 2) as $szemeszter) {
		    $osz = 0;
		    foreach ($tmp[ $return[$i]['tankorId'] ]['bontasOraszam'][$szemeszter-1] as $oAdat) $osz += floatval($oAdat['hetiOraszam']);
		    $return[$i]['tervezettOraszamok'][$szemeszter] = array('btOraszam'=> $osz, 'tszOraszam' => floatval($tmp[ $return[$i]['tankorId'] ]['oraszam'][$szemeszter-1]));
		};
		$return[$i]['bontasOk'] = (
		    $return[$i]['tervezettOraszamok'][1]['btOraszam']==$return[$i]['tervezettOraszamok'][1]['tszOraszam']
		    && $return[$i]['tervezettOraszamok'][2]['btOraszam']==$return[$i]['tervezettOraszamok'][2]['tszOraszam']
		);
	    }

	    return $return;
	} else { return array(); }
    }

    function getTanarokBySzuro($Szuro) {

	global $_TANEV;

	// Ha nincs kiválasztott tankör, akkor nincs értelme tanárokat lekérdezni
	if (!is_array($Szuro['tankorTargyIds']) || count($Szuro['tankorTargyIds']) == 0) return array();

	// Az osztály nem játszik szerepet a lehetséges tanárok szűrésében
	$vanMk = (is_array($Szuro['mkIds']) && count($Szuro['mkIds'])>0);
	$vanTanar = (is_array($Szuro['tanarIds']) && count($Szuro['tanarIds'])>0);
	// Tárgy mindig lesz - ha más nem a tankörökből
	if (is_array($Szuro['targyIds'])) $targyIds = array_merge($Szuro['targyIds'], $Szuro['tankorTargyIds']);
	else $targyIds = $Szuro['tankorTargyIds'];

	$v = $q = array();

	{
	    // Itt lehet tárgy és munkaközösség, valamint a megadott tanarId-k alapján szűrni
	    if ($vanTanar) {
		$q[] = "SELECT tanarId, concat_ws(' ',viseltNevElotag,viseltCsaladinev,viseltUtonev) as tanarNev,
		    hetiMunkaora,hetiKotelezoOraszam,
		    hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam
		    FROM tanar WHERE statusz IN ('határozatlan idejű','határozott idejű','külső óraadó')
		    AND beDt<='".$_TANEV['kezdesDt']."' AND (kiDt IS NULL OR kiDt<'".$_TANEV['zarasDt']."')
		    AND tanarId IN (".implode(',', array_fill(0, count($Szuro['tanarIds']), '%u')).")";
		$v = array_merge($v, $Szuro['tanarIds']);
	    }
	    if ($vanMk) {
		$q[] = "SELECT tanarId, concat_ws(' ',viseltNevElotag,viseltCsaladinev,viseltUtonev) as tanarNev,
		    hetiMunkaora,hetiKotelezoOraszam,
		    hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam
		    FROM tanar LEFT JOIN mkTanar USING (tanarId) 
		    WHERE statusz IN ('határozatlan idejű','határozott idejű','külső óraadó')
		    AND beDt<='".$_TANEV['kezdesDt']."' AND (kiDt IS NULL OR kiDt<'".$_TANEV['zarasDt']."')
		    AND mkId IN (".implode(',', array_fill(0, count($Szuro['mkIds']), '%u')).")";
		$v = array_merge($v, $Szuro['mkIds']);
	    }
	    // targyIds mindig van
	    $q[] = "SELECT tanarId, concat_ws(' ',viseltNevElotag,viseltCsaladinev,viseltUtonev) as tanarNev,
		    hetiMunkaora,hetiKotelezoOraszam,
		    hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam
		    FROM tanar LEFT JOIN tanarKepesites USING (tanarId)
		    LEFT JOIN kepesitesTargy USING (kepesitesId) 
		    WHERE statusz IN ('határozatlan idejű','határozott idejű','külső óraadó')
		    AND beDt<='".$_TANEV['kezdesDt']."' AND (kiDt IS NULL OR kiDt<'".$_TANEV['zarasDt']."')
		    AND targyId IN (".implode(',', array_fill(0, count($targyIds), '%u')).")";
	    $v = array_merge($v, $targyIds);
	    $q[] = "SELECT tanarId, concat_ws(' ',viseltNevElotag,viseltCsaladinev,viseltUtonev) as tanarNev,
		    hetiMunkaora,hetiKotelezoOraszam,
		    hetiLekotottMinOraszam,hetiLekotottMaxOraszam,hetiKotottMaxOraszam
		    FROM tanar LEFT JOIN mkTanar USING (tanarId)
		    LEFT JOIN targy USING (mkId) 
		    WHERE statusz IN ('határozatlan idejű','határozott idejű','külső óraadó')
		    AND beDt<='".$_TANEV['kezdesDt']."' AND (kiDt IS NULL OR kiDt<'".$_TANEV['zarasDt']."')
		    AND targyId IN (".implode(',', array_fill(0, count($targyIds), '%u')).")";
	    $v = array_merge($v, $targyIds);

	    $query = '('.implode(') UNION DISTINCT (', $q).') ORDER BY tanarNev, tanarId';
	    $return = db_query($query, array('fv'=>'getTanarokBySzuro #1','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));

	}
	// Le kell kérdezni a tárgyait és az eddigi lekötött óraszámát a tanárnak
	for ($i = 0; $i < count($return); $i++) {
	    $return[$i]['targyIds'] = getTargyIdsByTanarId($return[$i]['tanarId']);
	    $return[$i]['lekotottOraszam'] = getTanarLekotottOraszam($return[$i]['tanarId']);
	}

	return $return;

    }

    function getTankorStat() {

	global $_TANEV;

	$q = "select count(distinct tankorId) from tankorSzemeszter left join tankorTanar using (tankorId) 
		where tanev=".__TANEV." and tankorTanar.tanarId is not null
		and beDt<='".$_TANEV['kezdesDt']."' AND (kiDt IS NULL OR kiDt >= '".$_TANEV['zarasDt']."')";
	$ret['kesz'] = db_query($q, array('fv'=>'getTankorStat/1','modul'=>'naplo_intezmeny','result'=>'value'));

	$q = "select count(distinct tankorId) from tankorSzemeszter where tanev=".__TANEV;
	$ret['osszes'] = db_query($q, array('fv'=>'getTankorStat/1','modul'=>'naplo_intezmeny','result'=>'value'));

	return $ret;
    }

?>