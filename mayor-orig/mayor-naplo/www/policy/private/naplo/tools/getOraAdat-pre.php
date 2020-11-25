<?php

    if (_RIGHTS_OK !== true) die();
//    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/oraModifier.php');
    require_once('include/modules/naplo/share/orarend.php');
    require_once('include/modules/naplo/share/dolgozat.php');
    require_once('include/modules/naplo/share/jegyzet.php');

    $oraId = readVariable($_POST['oraId'], 'id');

    if (!is_numeric($oraId)) {
	$_JSON = array();
	exit;
    }

    /* PRIVÁT ADATOK */
    if (__NAPLOADMIN === true || __VEZETOSEG === true || __TITKARSAG === true || __TANAR === true || __DIAK === true ) {
	$oraBeirhato = oraBeirhato($oraId);
	$dolgozatBeirhato = $oraBeirhato;
	if ($oraBeirhato===true && $action=='oraBeiras') {
	    $leiras = readVariable($_POST['leiras'], 'string');
	    $cimkeId = readVariable($_POST['cimkeId'], 'id');
	    updateHaladasiNaploOra($oraId,$leiras,$cimkeId);
	}
	$_JSON['oraAdat'] = $ORAADAT = getOraAdatById($oraId);
	if ($ORAADAT['hazifeladatId']>0) {
	    oraHazifeladatDiakLatta($ORAADAT['hazifeladatId']); // log: diakLattamDt
	}
	if ($dolgozatBeirhato === true && $action=='dolgozatBeiras') {
	    $dolgozatId = ujDolgozat('',$ORAADAT['tankorId']);
	    dolgozatModositas($dolgozatId,'',$ORAADAT['dt']);
	}
	
	$q = "SELECT oraId FROM ora WHERE tankorId=%u AND dt<'%s' ORDER BY oraId DESC";
	$v = array($ORAADAT['tankorId'],$ORAADAT['dt']);
	$idk = db_query($q, array('fv'=>'pre','modul'=>'naplo','result'=>'idonly','values'=>$v));
	
	for ($i=0; $i<count($idk); $i++) {
	    $_JSON['elozoOrak'][$i] = getOraAdatById($idk[$i]);
	}

	$_JSON['dolgozat'] = getTankorDolgozatok(array($ORAADAT['tankorId']), TRUE, $ORAADAT['dt'], $ORAADAT['dt']);
//	$_JSON['jegyzet'] = getJegyzet(array('tolDt'=>$tolDt,'igDt'=>$igDt,'tankorIdk'=>$JA['tankorIdk'],
       // módosítható az óra?
        $_JSON['oraBeirhato'] = $oraBeirhato;
        $_JSON['dolgozatBeirhato'] = $dolgozatBeirhato;

        if ($oraBeirhato===true) { // HTML FORM
            $oraForm = '<form method="post" action="'.href('index.php?page=naplo&sub=tools&f=getOraAdat').'">
    		<input class="salt" type="hidden" name="'.__SALTNAME.'" value="'.__SALTVALUE.'" />
	        <input class="mayorToken" type="hidden" name="mayorToken" value="'.$_SESSION['mayorToken'].'" />
	        <input type="hidden" name="action" value="oraBeiras" />
	        <input type="hidden" name="oraId" value="'.$oraId.'" />
	        <input type="hidden" name="cimkeId" value="'.$_JSON['oraAdat']['cimkeId'].'" />
	        <textarea name="leiras" style="margin-top:8px; width:99%; height:100px;">'. supertext($_JSON['oraAdat']['leiras']).'</textarea>';
	    $oraForm .= '<button type="button" class="setOraAdat mentes" value="mentés" data-oraid="'.$oraId.'"><span class="icon-ok"></span> MENTÉS </button>';
	    $oraForm .= '</form>';
            $_JSON['oraForm'] = $oraForm;
        }
        if ($dolgozatBeirhato===true) { // HTML FORM
            $dolgozatForm = '<form method="post" action="'.href('index.php?page=naplo&sub=tools&f=getOraAdat').'">
    		<input class="salt" type="hidden" name="'.__SALTNAME.'" value="'.__SALTVALUE.'" />
	        <input class="mayorToken" type="hidden" name="mayorToken" value="'.$_SESSION['mayorToken'].'" />
	        <input type="hidden" name="action" value="dolgozatBeiras" />
	        <input type="hidden" name="oraId" value="'.$oraId.'" />';
	    $dolgozatForm .= '<button type="button" class="setOraAdat mentes" value="mentés" data-dolgozatid="'.$dolgozatId.'"><span class="icon-ok"></span> Új dolgozat bejelentés '.$ORAADAT['dt'].'</button>';
	    $dolgozatForm .= '</form>';
            $_JSON['dolgozatForm'] = $dolgozatForm;
        }
    }

    /* PUBLIKUS ADATOK */
    $_JSON['oraId'] = $oraId;
    $_JSON['tanev'] = __TANEV;

    // TEMPLATE
//    $ORAK = getTanarNapiOrak($tanarId);
    $s = '';
    if (is_array($ORAK) && count($ORAK)>0) {
	for ($ora=__MIN_ORA; $ora<=__MAX_ORA; $ora++) {
	    $OA = $ORAK[$ora];
	    $s .= '<div class="ora">'.$ora.'. '.$OA[$i]['tankorNev'];
	    for ($i=0; $i<count($OA); $i++) {
		$s .= $OA[$i]['tankorNev'];
	    }
	    $s .='</div>';
	}
    }
    $_JSON['html'] = serialize($_JSON['oraAdat']);
    $_JSON['debug'] = false;
?>