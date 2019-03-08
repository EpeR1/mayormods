<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/file.php');

	$tanev = __TANEV;
	$ADAT['kepzesId'] = $kepzesId = readVariable($_POST['kepzesId'], 'id', null);

	$ADAT['kepzesOraterv.kovetelmeny'] = getEnumField('naplo_intezmeny','kepzesOraterv','kovetelmeny');
	$KOT = $ADAT['kepzesOraterv.tipus'] = getEnumField('naplo_intezmeny','kepzesOraterv','tipus');

	function _escape($val,$A=null) {
	    global $KOT;
	    if (is_null($A) || !is_array($A)) $A = $KOT;
	    for ($i=0; $i<count($A); $i++) {
		if ($A[$i]==$val) { return 'E'.$i; }
	    }
	}
	function _unescape($val,$A=null) {
	    global $KOT;
	    if (!is_array($A)) $A = $KOT;
	    for ($i=0; $i<count($A); $i++) {
		if ($i==intval(substr($val,1))) { return $A[$i]; }
	    }
	}

	if ($action == 'oratervMasolas') {
	    $masolandoKepzesId = readVariable($_POST['masolandoKepzesId'],'id');
	    if (isset($masolandoKepzesId) && isset($kepzesId)) kepzesOratervMasolas($masolandoKepzesId, $kepzesId);
	} else if ($action == 'do') {
	    // prepare for walk
	    $submit_done = false;
	    reset($_POST);
	    foreach($_POST as $key => $value) {
		if (substr($key,0,6)=='torol_') {
		    list($_rest, $_tipus, $_targyId) = explode('_',$key);
		    $_tipus = _unescape(readVariable($_tipus,'strictstring'));
		    $_targyId = readVariable($_targyId,'id');
		    dropKepzesOratervRekord($kepzesId,$_tipus,$_targyId);
		    $submit_done = true;
		}		
	    }
	    if (!$submit_done) {
		list($tipus,$targyId) = explode('_',readVariable($_POST['UJ_targyTipusId'],'string'));
		    $UJtipus = (substr($tipus,0,1)=='E') ? _unescape(readVariable($tipus,'strictstring')) : '';
		    $UJtargyId = readVariable($targyId,'id');
		    unset($tipus); unset($targyId);
		reset($_POST);
		foreach($_POST as $key => $value) {
		    if (substr($key,0,3) == 'MO_') {//MAX ÓRASZÁM
			list($skey, $evfolyamJel) = explode('_',$key);
			if (is_numeric($value)) $DDATA['oraszamok'][$evfolyamJel]['max'] = $value;
		    } elseif (substr($key,0,3) == 'KO_') {//KÖT ÓRASZÁM
			list($skey, $evfolyamJel) = explode('_',$key);
			if (is_numeric($value)) $DDATA['oraszamok'][$evfolyamJel]['kotelezo'] = $value;
		    } elseif (substr($key,0,2) == 'T_') {// a tárgyhoz tartozó óraszám
			list($tipus, $targyId, $val, $evfolyamJel, $szemeszter) = explode('_',substr($key,2));
			$tipus = _unescape($tipus);
			$targyId = readVariable($targyId,'id');
			//if (intval($targyId)==0) $targyId = _unescape($targyId,$ADAT['kepzesOraterv.tipus']);
			if ($val=='O') { // heti óraszám
			    $DDATA['adatok'][$tipus][$targyId][$evfolyamJel][$szemeszter]['oraszam'] = $value;
			} elseif ($val=='K') { // követelmény
			    $DDATA['adatok'][$tipus][$targyId][$evfolyamJel][$szemeszter]['kovetelmeny'] = $value;
			}
		    /* Ha az új tárgyhoz tartozó adatok jönnek, pakoljuk a megfelelő tömbbe */
		    } elseif (substr($key,0,4) == 'UJ_O' && $UJtipus!='') {// az új tárgyhoz tartozó óraszám
			list($skey1, $skey2, $evfolyamJel, $szemeszter) = explode('_',$key);
			if (is_numeric($value)) $DDATA['adatok'][$UJtipus][$UJtargyId][$evfolyamJel][$szemeszter]['oraszam'] = $value;
		    } elseif (substr($key,0,4) == 'UJ_K' && $UJtipus!='') {// az új tárgyhoz tartozó óraszám
			list($skey1, $skey2, $evfolyamJel, $szemeszter) = explode('_',$key);
			$DDATA['adatok'][$UJtipus][$UJtargyId][$evfolyamJel][$szemeszter]['kovetelmeny'] = $value;
		    }
		}
		modifyKepzesOraterv($DDATA,$kepzesId);
	    }

	}

	if (isset($kepzesId)) {
	    $ADAT['kepzesAdat'] = getKepzesAdatById($kepzesId);
	    $ADAT['oraszam'] = getKepzesOraszam($kepzesId);
	    $ADAT['oraterv'] = getKepzesOraterv($kepzesId);
	    $ADAT['targyak'] = getTargyak();
	    if ($ADAT['oraterv']==array()) {
		// A kiválasztottal azonos osztályJelleghez tartozó képzések listája - ebbe benne van maga a kiválasztott képzés is!
		$ADAT['hasonloKepzesek'] = getKepzesByOsztalyJelleg($ADAT['kepzesAdat']['osztalyJellegId']);
	    }
	}
	$ADAT['osztalyok'] = getOsztalyok($tanev, array('result' => 'assoc','minden'=>true));

//	$ADAT['kepzesOraszam'] = getKepzesOraszam($kepzesId);
//	$ADAT['kepzesOraterv'] = getKepzesOraterv($kepzesId);

        if (isset($kepzesId) && !is_numeric($ADAT['kepzesAdat']['osztalyJelleg']['osztalyJellegId']))
            $_SESSION['alert'][] = 'error:nincs megadva osztály jelleg!';



	$TOOL['kepzesSelect'] = array('tipus'=>'cella', 'post' => array());
      $TOOL['oldalFlipper'] = array('tipus' => 'cella', 'url' => array('index.php?page=naplo&sub=intezmeny&f=osztaly','index.php?page=naplo&sub=intezmeny&f=kepzes'),   
                                              'titleConst' => array('_OSZTALYHOZ','_KEPZESHEZ'), 'post' => array('kepzesId'),                                                   
                                                                                      'paramName'=>'kepzesId'); // paramName ?                
	getToolParameters();

    }
?>
