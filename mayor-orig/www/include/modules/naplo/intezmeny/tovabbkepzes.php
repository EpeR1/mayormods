<?php

    function getTanarTovabbkepzesCiklus() {
	// $q = "select tanarId,tovabbkepzesStatusz,sum(reszosszeg) as sumReszosszeg,sum(oraszam) as sumOraszam,tanar.tovabbkepzesForduloDt,tanev from tovabbkepzesTanulmanyiEgyseg 
	//       left join tovabbkepzes USING (tovabbkepzesId) LEFT JOIN tanar USING (tanarId) 
	//       WHERE tanev BETWEEN YEAR(tovabbkepzesForduloDt)-7 AND YEAR(tovabbkepzesForduloDt) GROUP BY tanarId,tovabbkepzesStatusz";
	$q = "select tanarId,tovabbkepzesStatusz,sum(sumReszosszeg) AS sumReszosszeg,sum(IF(akkreditalt=1,sumOraszam,IF(sumOraszam<=30,sumoraszam,30))) AS sumOraszam,
	      tovabbkepzesForduloDt,tanev 
	    FROM (select akkreditalt,tanarId,tovabbkepzesStatusz,sum(reszosszeg) as sumReszosszeg,sum(oraszam) as sumOraszam,tanar.tovabbkepzesForduloDt,tanev from tovabbkepzesTanulmanyiEgyseg               left join tovabbkepzes USING (tovabbkepzesId) LEFT JOIN tanar USING (tanarId)               
	    WHERE tanev BETWEEN YEAR(tovabbkepzesForduloDt)-8 AND YEAR(tovabbkepzesForduloDt)-1 GROUP BY tanarId,tovabbkepzesStatusz,akkreditalt) AS a GROUP BY tanarId,tovabbkepzesStatusz";
	$r = db_query($q, array('debug'=>false,'fv'=>'getTovabbkepzesek','modul'=>'naplo_intezmeny','result'=>'indexed'));
	return reindex($r,array('tanarId','tovabbkepzesStatusz'));	
    }

    function getTanarTovabbkepzesFolyamat($dt="NOW()") {
	$q = "select tanarId,tolDt,igDt,tanusitvanyDt,tanusitvanySzam from tovabbkepzesTanar WHERE NOW() BETWEEN tolDt AND igDt";
	$r = db_query($q, array('debug'=>false,'fv'=>'getTovabbkepzesek','modul'=>'naplo_intezmeny','result'=>'assoc','keyfield'=>'tanarId'));
	return $r;
    }

    function getTovabbkepzesTerv($tanev) {
	$q = "SELECT * FROM tovabbkepzesTanulmanyiEgyseg WHERE tanev=%u";
	$values = array($tanev);
	$r = db_query($q, array('debug'=>false,'fv'=>'getTovabbkepzesek','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$values));
	return $r;
    }

    function getTanarTovabbkepzesByEv($tanev,$tanarId=null) {
	if ($tanarId>0) {
	    $values = array($tanev,$tanev,$tanarId);
	    $W = ' tanarId=%u AND ';
	} else {
	    $values = array($tanev,$tanev);
	    $W = '';
	}
	$q = "SELECT * FROM tovabbkepzesTanar
		WHERE $W tolDt<=DATE(CONCAT(%u+1,'-08-31')) AND (igDt>=DATE(CONCAT(%u,'-09-01')) OR igDt IS NULL) AND (tanusitvanySzam='' or tanusitvanySzam IS NULL)";
	$r = db_query($q, array('debug'=>false,'fv'=>'getTanarTovabbkepzesByEv','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$values));
	return $r;
    }

    // TODO ?
    function getTanarTeljesitettTanulmanyiEgyseg() {
	$q = "SELECT * FROM tovabbkepzesTanulmanyiEgyseg LEFT JOIN tovabbkepzesTanar USING (tovabbkepzesId,tanarId) WHERE tovabbkepzesStatusz='teljesített'";
	$values = array($tanarId);
	$r = db_query($q, array('debug'=>false,'fv'=>'getTanarTovabbkepzesByEv','modul'=>'naplo_intezmeny','result'=>'multiassoc','keyfield'=>'tanarId','values'=>$values));
	return $r;
    }

    function getTanarTovabbkepzesek($SET = array()) {
	$W = '';
	$values=array();
	if (is_array($SET) && count($SET)>0) {
	    foreach ($SET as $k => $v) {
		$M[] = "$k='%s'";
		$values[] = $v;
	    }
	    $W = 'WHERE '.implode(' AND ',$M);
	}
	$q = "SELECT * FROM tovabbkepzesTanar ".$W;
	$r = db_query($q, array('debug'=>false,'fv'=>'getTovabbkepzesek','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$values));
	return $r;
    }
    function getTovabbkepzesek() {
	$q = "SELECT * FROM tovabbkepzes LEFT JOIN tovabbkepzoIntezmeny USING (tovabbkepzointezmenyId) ORDER BY tovabbkepzesNev";
	$r = db_query($q, array('fv'=>'getTovabbkepzesek','modul'=>'naplo_intezmeny','result'=>'indexed'));
	return $r;
    }
    function getTovabbkepzoIntezmenyek() {
	$q = "SELECT * FROM tovabbkepzoIntezmeny ORDER BY intezmenyRovidnev,intezmenyNev";
	$r = db_query($q, array('fv'=>'getTovabbkepzoIntezmenyek','modul'=>'naplo_intezmeny','result'=>'indexed'));
	return $r;
    }
    function getKeretosszeg($tanev) {
	$q = "SELECT keretOsszeg FROM tovabbkepzesKeret WHERE tanev=%u";
	$v = array($tanev);
	$r = db_query($q, array('fv'=>'getKeretosszeg','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v));
	return $r;
    }

    function ujTovabbkepzoIntezmeny($ADAT) {
	$q = "INSERT INTO tovabbkepzoIntezmeny (intezmenyRovidnev,intezmenyNev) VALUES ('%s','%s')";
	$v = array($ADAT['intezmenyRovidNev'],$ADAT['intezmenyNev']);
	$r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzoIntezmeny','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v));
	return $r;
    }


    function modKeretosszeg($tanev,$keretOsszeg) {
	$q = "REPLACE INTO tovabbkepzesKeret (tanev,keretOsszeg) VALUES (%u,%u)";
	$v = array($tanev,$keretOsszeg);
	db_query($q, array('debug'=>false,'fv'=>'modKeretOsszeg','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v));
    }

    function ujTovabbkepzes($ADAT) {
	if ($ADAT['tovabbkepzesNev']=='') return false;
	$q = "INSERT INTO tovabbkepzes (tovabbkepzesNev,tovabbkepzoIntezmenyId, oraszam, kategoria, akkreditalt) VALUES ('%s',%u,%u,'%s',%u)";
	$v = array($ADAT['tovabbkepzesNev'],$ADAT['tovabbkepzoIntezmenyId'],$ADAT['oraszam'],$ADAT['kategoria'],$ADAT['akkreditalt']);
	$r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzes','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v));
	return $r;
    }
    function modTovabbkepzes($ADAT) {
	if ($ADAT['tovabbkepzesId']=='') return false;
	$q = "UPDATE tovabbkepzes SET tovabbkepzesNev='%s',oraszam=%u,kategoria='%s' WHERE tovabbkepzesId=%u";
	$v = array($ADAT['tovabbkepzesNev'],$ADAT['oraszam'],$ADAT['kategoria'],$ADAT['tovabbkepzesId']);
	$r = db_query($q, array('debug'=>false,'fv'=>'modTovabbkepzes','modul'=>'naplo_intezmeny','result'=>'update','values'=>$v));
	return $r;
    }
    function ujTovabbkepzesTanar($ADAT) {
	if ($ADAT['tovabbkepzesId']=='') return false;
	$q = "INSERT INTO tovabbkepzesTanar (tovabbkepzesId,tanarId, tolDt, igDt) VALUES (%u,%u,'%s','%s')";
	$v = array($ADAT['tovabbkepzesId'],$ADAT['tanarId'],$ADAT['tolDt'],$ADAT['igDt']);
	$r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v));
	return $r;
    }
    function modTovabbkepzesTanar($ADAT) {
	//if ($ADAT['tovabbkepzesId']=='') return false;
	$q = "UPDATE tovabbkepzesTanar SET tolDt='%s', igDt='%s', tanusitvanyDt='%s', tanusitvanySzam='%s' WHERE tovabbkepzesId=%u AND tanarId=%u";
	$v = array($ADAT['tolDt'],$ADAT['igDt'],$ADAT['tanusitvanyDt'],$ADAT['tanusitvanySzam'],$ADAT['tovabbkepzesId'],$ADAT['tanarId']);
	$r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'update','values'=>$v));
	return $r;
    }
    function delTovabbkepzesTanar($ADAT) {
	//if ($ADAT['tovabbkepzesId']=='') return false;
	$q = "DELETE FROM tovabbkepzesTanar WHERE tovabbkepzesId=%u AND tanarId=%u";
	$v = array($ADAT['tovabbkepzesId'],$ADAT['tanarId']);
	$r = db_query($q, array('debug'=>false,'fv'=>'delTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'delete','values'=>$v));
	return $r;
    }
    function ujTovabbkepzesTE($ADAT,$lr) {
	$q = "INSERT INTO tovabbkepzesTanulmanyiEgyseg (tovabbkepzesId, tanarId, tanev, reszosszeg, tamogatas, tovabbkepzesStatusz, tavollet,helyettesitesRendje,prioritas) VALUES (%u,%u,%u,%u,%u,'%s','%s','%s','%s')";
	$v = array($ADAT['tovabbkepzesId'],$ADAT['tanarId'],$ADAT['tanev'],intval($ADAT['reszosszeg']),intval($ADAT['tamogatas']),$ADAT['tovabbkepzesStatusz'],$ADAT['tavollet'],$ADAT['helyettesitesRendje'],$ADAT['prioritas']);
	$r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v),$lr);
    }
    function modTovabbkepzesTE($ADAT) {
	$lr = db_connect('naplo_intezmeny');
	$q = "SELECT count(*) AS db FROM tovabbkepzesTanulmanyiEgyseg WHERE tovabbkepzesId=%u AND tanarId=%u AND tanev=%u";
	$v = array($ADAT['tovabbkepzesId'],$ADAT['tanarId'],$ADAT['tanev']);
	$db = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v),$lr);
	if ($db==0) {
	    ujTovabbkepzesTE($ADAT,$lr);
	    $q = "UPDATE tovabbkepzesTanulmanyiEgyseg SET reszosszeg=%u,tamogatas=%u,tovabbkepzesStatusz='%s',tavollet='%s',helyettesitesRendje='%s',prioritas='%s' WHERE tovabbkepzesId=%u AND tanarId=%u AND tanev=%u";
	    $v = array(intval($ADAT['reszosszeg']),intval($ADAT['tamogatas']),$ADAT['tovabbkepzesStatusz'],$ADAT['tavollet'],$ADAT['helyettesitesRendje'],$ADAT['prioritas'],$ADAT['tovabbkepzesId'],$ADAT['tanarId'],$ADAT['tanev']);    
	    $r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v),$lr);

	} else {
	    $q = "UPDATE tovabbkepzesTanulmanyiEgyseg SET reszosszeg=%u,tamogatas=%u,tovabbkepzesStatusz='%s',tavollet='%s',helyettesitesRendje='%s',prioritas='%s' WHERE tovabbkepzesId=%u AND tanarId=%u AND tanev=%u";
	    $v = array(intval($ADAT['reszosszeg']),intval($ADAT['tamogatas']),$ADAT['tovabbkepzesStatusz'],$ADAT['tavollet'],$ADAT['helyettesitesRendje'],$ADAT['prioritas'],$ADAT['tovabbkepzesId'],$ADAT['tanarId'],$ADAT['tanev']);    
	    $r = db_query($q, array('debug'=>false,'fv'=>'ujTovabbkepzesTanar','modul'=>'naplo_intezmeny','result'=>'value','values'=>$v),$lr);
	}
	db_commit($lr);
	db_close($lr);
    }
    function delTovabbkepzesTE($ADAT) {
	// echo 'EZT TÖRÖLNÉM';
	// dump($ADAT);	
    }

?>