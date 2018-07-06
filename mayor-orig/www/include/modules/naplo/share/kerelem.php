<?php
   function getKerelemOsszesito() {
        $q = "SELECT lezarasDt,jovahagyasDt FROM kerelem";   
        $R = db_query($q, array('modul'=>'naplo_base','result'=>'indexed'));
	for($i=0; $i<count($R); $i++) {
        	if ($R[$i]['lezarasDt']!='' && $R[$i]['jovahagyasDt']!='')
		{
		    $SECS[] = strtotime($R[$i]['lezarasDt']) - strtotime($R[$i]['jovahagyasDt']);
		}
	}
	$RET['n'] = $i+1;
	$RET['nofdone'] = count($SECS);
	if (count($SECS)>0)
	    $RET['avgTime'] = number_format(array_sum($SECS) / (24*3600*count($SECS)),2);
	else
	    $RET['avgTime'] = '-';
	return $RET;
    }     

    function getKerelem($kerelemId) {
	$q = "SELECT * FROM kerelem WHERE kerelemId=%u";
	$RESULT = db_query($q, array('fv' => 'getKerelmek', 'modul' => 'naplo_base', 'result' => 'indexed', 'values'=>array($kerelemId)));
	return $RESULT;
    }
    function getKerelemValaszok($kerelemId) {
	$q = "SELECT * FROM kerelemValasz WHERE kerelemId=%u ORDER BY valaszDt";
	$RESULT = db_query($q, array('fv' => 'getKerelmek', 'modul' => 'naplo_base', 'result' => 'indexed', 'values'=>array($kerelemId)));
	return $RESULT;
    }

    function getValasz($valaszId) {
	$q = "SELECT * FROM kerelemValasz WHERE valaszId=%u";
	$RESULT = db_query($q, array('fv' => 'getKerelemValasz', 'modul' => 'naplo_base', 'result' => 'indexed', 'values'=>array($valaszId)));
	return $RESULT;
    }

    function getKerelmek($telephelyId='', $kerelemId='', $lezarasMaxDt='') {
	if (isset($lezarasMaxDt) && $lezarasMaxDt!='') {
	    $W_lezaras = '(lezarasDt IS NULL OR lezarasDt>"'.$lezarasMaxDt.' 23:59:59")';
	} else {
	    $W_lezaras = 'lezarasDt IS NULL';
	}
	if (isset($telephelyId) && $telephelyId!='') {
	    $W = ' AND (telephelyId=%u OR telephelyId IS NULL)';
	    $v[] = $telephelyId;
	} elseif (isset($kerelemId) && $kerelemId!='') {
	    $W = ' AND kerelemId=%u';
	    $v[] = $kerelemId;
	} else { 
	    $W='';
	}
	$q = "SELECT kerelem.*, IF (valaszDt IS NOT NULL,max(valaszDt),rogzitesDt) AS mx FROM kerelem LEFT JOIN kerelemValasz USING (kerelemId) WHERE $W_lezaras $W GROUP BY kerelemId ORDER BY mx DESC";

	$RESULT['kerelmek'] = db_query($q, array('debug'=>false,'fv' => 'getKerelmek', 'modul' => 'naplo_base', 'result' => 'indexed', 'values'=>$v));
	$_kerelemIdk = array();
	for ($i=0; $i<count($RESULT['kerelmek']); $i++) {
	    if ($RESULT['kerelmek'][$i]['kerelemId']>0) $_kerelemIdk[] = $RESULT['kerelmek'][$i]['kerelemId'];
	}
	if (is_array($_kerelemIdk) && count($_kerelemIdk)>0) {
	    $q = 'SELECT * FROM kerelemValasz WHERE kerelemId IN ('.implode(',',$_kerelemIdk).') ORDER BY valaszDt';
	    $RESULT['valaszok'] = db_query($q, array('fv' => 'getKerelmek', 'modul' => 'naplo_base', 'keyfield'=>'kerelemId','result' => 'multiassoc'));
	}
	return $RESULT;
    }

    function getSajatKerelmek($telephelyId='') {
	$W = (isset($telephelyId) && $telephelyId!='') ? ' AND (telephelyId=%u OR telephelyId IS NULL)' : '';
	$q = "SELECT * FROM kerelem WHERE userAccount='"._USERACCOUNT."' AND (lezarasDt IS NULL OR (lezarasDt > (curdate() - interval 1 day))) $W ORDER BY rogzitesDt DESC";
	$RESULT['kerelmek'] = db_query($q, array('fv' => 'getSajatKerelmek', 'modul' => 'naplo_base', 'result' => 'indexed', 'values'=>array($telephelyId)));
	for ($i=0; $i<count($RESULT['kerelmek']); $i++) {
	    $_kerelemIdk[] = $RESULT['kerelmek'][$i]['kerelemId'];
	}
	if (count($_kerelemIdk)>0) {
	    $q = 'SELECT * FROM kerelemValasz WHERE kerelemId IN ('.implode(',',$_kerelemIdk).') ORDER BY valaszDt';
	    $RESULT['valaszok'] = db_query($q, array('fv' => 'getKerelmek', 'modul' => 'naplo_base', 'keyfield'=>'kerelemId','result' => 'multiassoc'));
	}

	return $RESULT;
    }

    function hibaAdminRogzites($Adat) {
        $kerelemId = $Adat['kerelemId'];
        $valasz = $Adat['valasz'];
        $kategoria = $Adat['kategoria'];
	$userAccount = $Adat['jovahagyasAccount'];
	$telephelyId = ($Adat['kerelemTelephelyId']!='') ? $Adat['kerelemTelephelyId'] : 'NULL';
	$modosithat = false; // egyelőre nem használjuk :)
	// jogosultság ellenőrzés
	if (__VEZETOSEG===true || __NAPLOADMIN===true) {
	    $modosithat = true;    
	} else {
	    $q = "SELECT kerelemId FROM kerelem WHERE userAccount ='%s' AND kerelemId=%u";
	    $v = array(_USERACCOUNT,$kerelemId);
	    $checkKerelemId = db_query($q, array('fv' => 'hangya', 'result'=>'value', 'modul' => 'naplo_base', 'values' => $v));
	    if ($checkKerelemId!=$kerelemId) return false;
	    else $modosithat = true;
	}
	if ($valasz!='') {
	    $q = "INSERT INTO kerelemValasz (valasz,kerelemId,userAccount) VALUES ('%s',%u,'"._USERACCOUNT."')";
	    $v = array($valasz,$kerelemId);
	    db_query($q, array('fv' => 'hangya', 'modul' => 'naplo_base', 'values' => $v));
	}
	$q = "UPDATE kerelem SET kategoria='%s',telephelyId='%s' WHERE kerelemId=%u";
	$v = array($kategoria,$telephelyId,$kerelemId);
        db_query($q, array('fv' => 'hibaAdminRogzites', 'modul' => 'naplo_base', 'values' => $v));

	if (__VEZETOSEG===true || __NAPLOADMIN===true) {
          if (isset($Adat['jovahagy'])) {
            $q = "UPDATE kerelem SET jovahagyasAccount='%s',jovahagyasDt=NOW() WHERE kerelemId=%u";
	    $v = array($userAccount, $kerelemId);
	    $extraTxt = '[státusz módosítás: Jóváhagyva]';
          } elseif ($Adat['nemHagyJova']) {
	    //$q = "SELECT jovahagyasDt FROM kerelem WHERE kerelemId=%u";
	    //$jdt = db_query($q, array('fv' => 'hibaAdminRogzites', 'modul' => 'naplo_base', 'result' => 'value', 'values' => array($kerelemId)));
            $q = "UPDATE kerelem SET jovahagyasDt=NULL WHERE kerelemId=%u";
	    $v = array($kerelemId);
	    $extraTxt = '[státusz módosítás: Nincs jóváhagyva]';
          } elseif (isset($Adat['lezar'])) {
            $q = "UPDATE kerelem SET lezarasDt=NOW() WHERE kerelemId=%u";
	    $v = array($kerelemId);
	    $extraTxt = '[státusz módosítás: Lezárva]';
          } else {
            //$q = "UPDATE kerelem SET valasz='%s' WHERE kerelemId=%u";
	    //$v = array($valasz, $kerelemId);
          }
          $R = db_query($q, array('fv' => 'hibaAdminRogzites', 'modul' => 'naplo_base', 'values' => $v));
	  if ($extraTxt!='') {
	    $q = "INSERT INTO kerelemValasz (valasz,kerelemId,userAccount) VALUES ('%s',%u,'"._USERACCOUNT."')";
	    $v = array($extraTxt,$kerelemId);
	    db_query($q, array('fv' => 'hangya', 'modul' => 'naplo_base', 'values' => $v));
	  }
	}
	return $R;
    }

?>
