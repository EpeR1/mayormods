<?php

    function getIrasbeliEredmeny($nev,$oId) {
        $lr = @db_connect('felveteli');
	    $q = "SELECT * FROM irasbeli_eredmenyek_"._EV." WHERE nev='%s' AND oId='%s'";
	    $RET = db_query($q,array('modul'=>'felveteli', 'result'=>'indexed','values'=>array($nev,$oId),'debug'=>false),$lr);
	db_close($lr);
	return $RET;
    }

    function getFelvetelizoAdatok($nev,$oktid) {
	$lr = db_connect('felveteli');
	$q = "SELECT count(*) AS db FROM adatok_"._EV." WHERE (nev='$nev' or jelige='$nev') AND oktid='$oktid'";
	$c = db_query($q,array('result'=>'value','modul'=>'felveteli'),$lr);
	if ($c==1) {
	    $q = "SELECT *,
		    (magyar+matek)*0.8+IF(atlag<3.75,-5,(atlag-4)*20+0) AS hozottpont,
		    IF(matek2>0,(magyar+matek2)*0.8+IF(atlag<3.75,-5,(atlag-4)*20+0),0) AS hozottpont_mat,
		    IF(magyar2>0,(magyar2+matek)*0.8+IF(atlag<3.75,-5,(atlag-4)*20+0),0) AS hozottpont_magy 
		  FROM adatok_"._EV." WHERE (nev='$nev' or jelige='$nev') AND oktid='$oktid'";
	    $Rtmp = db_query($q,array('result'=>'indexed','modul'=>'felveteli'),$lr);
	    $R = $Rtmp[0];
	    if ($R['OM'] != '') {
		$q = "SELECT * FROM iskolak WHERE omkod like '%".$R['OM']."' LIMIT 1";
		$Rtmp = db_query($q,array('result'=>'indexed'),$lr);
		$R['iskolaAdat'] = $Rtmp[0];
	    } else { 
		$R['iskolaAdat'] = array(); 
	    }
	} else {
	    $_SESSION['alert'][] = 'info:none_or_multiple:'.$c;
	}
	if ($lr) db_close($lr);
	return $R;
    }

    function getSzobeli($jid, $olr = '') {
	if (!is_numeric($jid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	//$q = "SELECT * FROM szobeli_"._EV." WHERE jid=$jid ORDER BY napdt,ido,tagozat";
	$q = "SELECT * FROM szobeli_"._EV." WHERE id=$jid ORDER BY napdt,ido,tagozat";
	// 2012
//	$q = "SELECT * FROM szobeli_"._EV." AS sz LEFT JOIN jelentkezok_tagozatok"._EV." AS jel ON sz.id=jel.id AND sz.tagozat=jel.tid 
//		WHERE sz.id=$jid ORDER BY napdt,ido,tagozat";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getSzobeliByOktid($oktid, $olr = '') {
	if (!is_numeric($oktid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	$q = "SELECT * FROM szobeli_"._EV." WHERE `oktid`='$oktid' ORDER BY napdt,ido,tagozat";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getJelentkezes($jid, $olr = '') {
	if (!is_numeric($jid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	$q = "SELECT * FROM jelentkezok_tagozatok_"._EV." WHERE jid=$jid ORDER BY rangsor";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getIdeiglenesRangsor($oktid) {

	if (!is_numeric($oktid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	$q = "SELECT tagozat,rangsor,pont,szobeli,joslat FROM eredmenyek_tagozatonkent_"._EV." WHERE oktid='$oktid'";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;

    }



?>
