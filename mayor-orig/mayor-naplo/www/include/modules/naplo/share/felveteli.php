<?php

    function updateLevelToken($oId) {
        if ($oId=='') return false;
        $token = bin2hex(openssl_random_pseudo_bytes(20));
        $ip = _clientIp();
        $q = "INSERT INTO felveteli_levelLog (oId,generalasDt,ip,token) VALUES ('%s',NOW(),'%s','%s')";
        $v = array($oId,$ip,$token);
        db_Query($q,array('modul'=>'naplo','values'=>$v,'debug'=>false),$lr);
        return $token;
    }

    function checkLevelToken($token) {

    }

    function getIrasbeliEredmeny($nev,$oId) {
        $lr = @db_connect('felveteli');
	    $q = "SELECT * FROM irasbeli_eredmenyek_"._EV." WHERE nev='%s' AND oId='%s'";
	    $RET = db_query($q,array('modul'=>'felveteli', 'result'=>'indexed','values'=>array($nev,$oId),'debug'=>false),$lr);
	db_close($lr);
	return $RET;
    }

    function getFelvetelizoAdatok($nev,$oId) {
	$lr = db_connect('naplo');
	if (__FELVETELIADMIN===TRUE && $oId=='') {
	    $q = "SELECT count(*) AS db FROM felveteli WHERE (nev LIKE '$nev%' or jelige='$nev')";
	} else {
	    $q = "SELECT count(*) AS db FROM felveteli WHERE (nev='$nev' or jelige='$nev') AND oId='$oId'";
	}
	$c = db_query($q,array('result'=>'value','modul'=>'naplo'),$lr);
	if ($c==1) {
	    if (__FELVETELIADMIN===TRUE && $oId=='')
		$q = "SELECT * FROM felveteli WHERE (nev LIKE '$nev%' or jelige='$nev')";
	    else
		$q = "SELECT * FROM felveteli WHERE (nev='$nev' or jelige='$nev') AND oId='$oId'";
	    $R = db_query($q,array('result'=>'record','modul'=>'naplo'),$lr);
//	    if ($R['OM'] != '') {
//		$q = "SELECT * FROM iskolak WHERE omkod like '%".$R['OM']."' LIMIT 1";
//		$Rtmp = db_query($q,array('result'=>'indexed'),$lr);
//		$R['iskolaAdat'] = $Rtmp[0];
//	    } else { 
		$R['iskolaAdat'] = array(); 
//	    }
	} else {
	    // $_SESSION['alert'][] = 'info:Nincs találat!:'.$c;
	    if (__FELVETELIADMIN===true && $nev!='') { //chatty error
		$q = "SELECT nev,oId FROM felveteli WHERE (nev LIKE '$nev%' or jelige='$nev')";
		$ER = db_query($q,array('result'=>'indexed','modul'=>'naplo'),$lr);	
		if (count($ER)>0) $_SESSION['alert'][] = 'info:'.json_encode($ER);
	    }
	}
	if ($lr) db_close($lr);
	return $R;
    }
/*
    function getSzobeli($jid, $olr = '') {
	if (!is_numeric($jid)) return false;
	if ($olr=='') $lr = db_connect('naplo'); else $lr=$olr;
	//$q = "SELECT * FROM szobeli_"._EV." WHERE jid=$jid ORDER BY napdt,ido,tagozat";
	$q = "SELECT * FROM szobeli_"._EV." WHERE id=$jid ORDER BY napdt,ido,tagozat";
	// 2012
//	$q = "SELECT * FROM szobeli_"._EV." AS sz LEFT JOIN jelentkezok_tagozatok"._EV." AS jel ON sz.id=jel.id AND sz.tagozat=jel.tid 
//		WHERE sz.id=$jid ORDER BY napdt,ido,tagozat";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }
*/

    function getSzobeliByoId($oId, $olr = '') {
	if (!is_numeric($oId)) return false;
	if ($olr=='') $lr = db_connect('naplo'); else $lr=$olr;
	$q = "SELECT * FROM felveteli_szobeli
		LEFT JOIN felveteli_tagozat USING (tagozat)
	    WHERE `oId`='$oId' ORDER BY napdt,ido,felveteli_szobeli.tagozat";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getJelentkezes($oId, $olr = '') {
	if (!is_numeric($oId)) return false;
	if ($olr=='') $lr = db_connect('naplo'); else $lr=$olr;
	$q = "SELECT * FROM felveteli_jelentkezes LEFT JOIN felveteli_tagozat USING (tagozat) WHERE oId='%s'";
	$v = array($oId);
	$R = db_query($q,array('result'=>'indexed', 'values'=>$v),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getIdeiglenesRangsor($oId) {

	if (!is_numeric($oId)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	$q = "SELECT tagozat,rangsor,pont,szobeli,joslat FROM eredmenyek_tagozatonkent_"._EV." WHERE oId='$oId'";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;

    }

    function getFelveteliTagozat() {
	if ($olr=='') $lr = db_connect('naplo'); else $lr=$olr;
	$q = "SELECT * FROM felveteli_tagozat";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

?>
