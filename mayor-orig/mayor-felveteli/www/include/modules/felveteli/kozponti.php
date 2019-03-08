<?php

    function updateLevelToken($oktid) {
	if ($oktid=='') return false;
	$token = bin2hex(openssl_random_pseudo_bytes(20));
	$ip = _clientIp();
	$q = "INSERT INTO levelLog_"._FELVETELI_EVE." (oktid,generalasDt,ip,token) VALUES ('%s',NOW(),'%s','%s')";
	$v = array($oktid,$ip,$token);
	db_Query($q,array('modul'=>'felveteli','values'=>$v,'debug'=>false),$lr);
	return $token;
    }

    function checkLevelToken($token) {
	
    }

    function getIrasbeliEredmeny($nev,$oId,$an=null,$szuldt=null) {
        $lr = @db_connect('felveteli');
	    if ($oId != '') {
		$q = "SELECT * FROM irasbeli_eredmenyek_"._EV." WHERE nev='%s' AND oId='%s'";
		$RET = db_query($q,array('modul'=>'felveteli', 'result'=>'indexed','values'=>array($nev,$oId),'debug'=>false),$lr);
	    } else {
		$q = "SELECT * FROM irasbeli_eredmenyek_"._EV." WHERE nev='%s' AND an='%s' AND szuldt='%s'";
		$RET = db_query($q,array('modul'=>'felveteli', 'result'=>'indexed','values'=>array($nev,$an,$szuldt),'debug'=>false),$lr);
	    }
	db_close($lr);
	return $RET;
    }

    function getFelvetelizoAdatok($nev,$oktid) {
	$lr = db_connect('felveteli');
	$q = "SELECT count(*) AS db FROM adatok_"._EV." WHERE (nev='$nev' or jelige='$nev') AND oktid='$oktid'";
	$c = db_query($q,array('result'=>'value'),$lr);
	if ($c==1) {
	    $q = "SELECT *,(magyar+matek)*0.8+IF(atlag<4,0,(atlag-4)*10+10) AS hozottpont,IF(matek2>0,(magyar+matek2)*0.8+IF(atlag<4,0,(atlag-4)*10+10),0) AS hozottpont2 FROM adatok_"._EV." WHERE (nev='$nev' or jelige='$nev') AND oktid='$oktid'";
	    $Rtmp = db_query($q,array('result'=>'indexed'),$lr);
	    $R = $Rtmp[0];
	    $q = "SELECT * FROM iskolak WHERE omkod like '%".$R['OM']."' LIMIT 1";
	    $Rtmp = db_query($q,array('result'=>'indexed'),$lr);
	    $R['iskolaAdat'] = $Rtmp[0];
	} else {
	    $_SESSION['alert'][] = 'info:none_or_multiple:'.$c;
	}
	if ($lr) db_close($lr);
	return $R;
    }

    function getSzobeli($jid, $olr = '') {
	if (!is_numeric($jid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
// 2012 //	$q = "SELECT * FROM szobeli_"._EV." WHERE jid=$jid ORDER BY napdt,ido,tagozat";
        $q = "SELECT * FROM szobeli_"._EV." AS sz LEFT JOIN jelentkezes"._EV." AS jel ON sz.id=jel.id AND sz.tagozat=jel.tid                                 
                WHERE sz.id=$jid ORDER BY napdt,ido,tagozat";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;
    }

    function getIdeiglenesRangsor($oktid) {

	if (!is_numeric($oktid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	$q = "SELECT jid,tagozat,pont,szobeli,rangsor FROM eredmenyek_tagozatonkent_"._EV." WHERE oktid=$oktid";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
var_dump($R);
	return $R;

    }

    function getIdeiglenesRangsor_old($jid) {

	if (!is_numeric($jid)) return false;
	if ($olr=='') $lr = db_connect('felveteli'); else $lr=$olr;
	$q = "SELECT jid,tagozat,szobeli,rangsor FROM eredmenyek_tagozatonkent_"._EV." WHERE jid=$jid";
	$R = db_query($q,array('result'=>'indexed'),$lr);
	if ($olr=='') db_close($lr);
	return $R;

    }


    //-- RÉGI!!!
/*
    function getFelvetelizoAdatok($nev,$diak = '',$oktid = '') {
	if ($diak!='') $W = "diak='$diak'";
	elseif($oktid!='') $W = "oktid='$oktid'";
	else return false;
        $lr = @db_connect('felveteli');
	    if (_CATEGORY=='admin')
			$query = "SELECT * FROM adatok_"._EV." WHERE (nev='$nev' or jelige='$nev')";
	    else	$query = "SELECT * FROM adatok_"._EV." WHERE (nev='$nev' or jelige='$nev') AND ".$W;

            $result = @mysql_query($query,$lr);
            if (($db=@mysql_num_rows($result))==0) {
                $ret = false;
            } elseif ($db==2) {
                $ret = 'multi';
            } else {
                $ret = @mysql_fetch_assoc($result);
            }

	    $r = @mysql_query("SELECT * FROM iskolak WHERE omkod='".$ret['OM']."' LIMIT 1",$lr);
	    if (@mysql_num_rows($r)==1) {
		$ret['iskolaAdat'] = @mysql_fetch_assoc($r);
	    }
        @mysql_close($lr);
        return $ret;
	
    }


    function getSzobeliEredmeny($jid) {
	$lr = @db_connect('felveteli');
	
//	    $query = "SELECT * FROM szobeli_"._EV." WHERE jid=$jid ORDER BY napdt,ido";
//2.	    $query = "SELECT jid,kod as tagozat, eredmeny,pont FROM jelentkezok_tagozatok WHERE jid=$jid";
	    $query = "SELECT jid,kod as tagozat,rangsor as eredmeny,pont,szobeli FROM eredmenyek_tagozatonkent_"._EV." WHERE jid=$jid";
	    $result = @mysql_query($query,$lr);
	    if (($db=@mysql_num_rows($result))==0) {
		$ret = false;
	    } else {
		while ($sor = @mysql_fetch_assoc($result)) {
		    $ret[] = $sor;
		}
	    }
	@mysql_close($lr);
	return $ret;
    }
*/

?>
