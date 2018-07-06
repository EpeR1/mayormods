<?php

    function bontasTankorHozzarendeles($bontasIds, $tankorId, $hetiOraszam, $olr=null) {

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) { $lr = db_connect('naplo'); db_start_trans($lr);
	} else { $lr = $olr; }

        // bontasTankor rögzítése
	$ok = true;
        foreach ($bontasIds as $bontasId) {
            $q = "insert into ".__TANEVDBNEV.".bontasTankor (bontasId, tankorId, hetiOraszam) values (%u, %u, %f)";
            $v = array($bontasId, $tankorId, $hetiOraszam);
            $r = db_query($q, array('fv'=>'bontasTankor/bt','modul'=>'naplo','values'=>$v), $lr);
	    $ok = $ok && ($r !== false);
        }

	// is_resource mysqli esetén nem jó (object)
	if (!$olr) {
    	    if (!$ok) db_rollback($lr, 'tankörnév hozzárendelés');
	    db_close($lr);
	}

	return $ok;
    }

    function getBontasAdat($bontasId) {

	$q = "select * from kepzesTargyBontas where bontasId=%u";
	$ret = db_query($q, array('fv'=>'getBontasAdat/1','modul'=>'naplo','result'=>'record','values'=>array($bontasId)));
	
	$q = "select * from bontasTankor where bontasId=%u order by tankorId";
	$ret['tankor-oraszam'] = db_query($q, array('fv'=>'getBontasAdat/2','modul'=>'naplo','result'=>'indexed','values'=>array($bontasId)));

	$ret['hetiOraszam'] = 0;
	if (is_array($ret['tankor-oraszam'])) foreach ($ret['tankor-oraszam'] as $index => $toAdat) $ret['hetiOraszam'] += $toAdat['hetiOraszam'];

	return $ret;
    }

    function osztalyBontasKeszE($osztalyId) {

	// Az osztályhoz rendelt képzések óraterveinek óraszámai
	$q1 = "select kepzesOratervId, hetiOraszam from osztalyNaplo left join ".__INTEZMENYDBNEV.".kepzesOsztaly using (osztalyId) 
		left join ".__INTEZMENYDBNEV.".kepzesOraterv using (kepzesId) 
		where osztalyId=%u and osztalyNaplo.evfolyamJel=kepzesOraterv.evfolyamJel";
	// Az osztályhoz rendelt bontások óraszámai
	$q2 = "select kepzesOratervId, sum(hetiOraszam) as hetiOraszam from kepzesTargyBontas left join bontasTankor using (bontasId) 
		where osztalyId=%u group by bontasId";
	$v = array($osztalyId, $osztalyId);
	// Az óratervet lefedik-e a bontások
	$q12 = "select count(oraterv.kepzesOratervId) from 
		(".$q1.") as oraterv 
		left join (".$q2.") as bontas
		using (kepzesOratervId, hetiOraszam) 
		where bontas.kepzesOratervId is null";
	// A bontások óraszámai teljesek-e
	$q21 = "select count(bontas.kepzesOratervId) from 
		(".$q2.") as bontas 
		left join (".$q1.") as oraterv
		using (kepzesOratervId, hetiOraszam) 
		where oraterv.kepzesOratervId is null";

	return (
		db_query($q12, array('fv'=>'osztalyBontasKeszE/12','modul'=>'naplo','result'=>'value','values'=>$v)) 
		+ db_query($q21, array('fv'=>'osztalyBontasKeszE/21','modul'=>'naplo','result'=>'value','values'=>$v))
		== 0
	);

    }

?>