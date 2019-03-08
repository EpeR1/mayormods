<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    $jegyId = readVariable($_POST['jegyId'], 'id', readVariable($_GET['jegyId'], 'id'));

    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/ora.php');
    require_once('include/modules/naplo/share/jegy.php');
    require_once('include/modules/naplo/share/jegyModifier.php');
    require_once('include/modules/naplo/share/file.php');

    if (isset($jegyId) && ($jegy = getJegyInfo($jegyId))) {

	if (__DIAK && $jegy['diakId'] != __USERDIAKID) {
	    $_SESSION['alert'][] = 'page:illegal_access';
	} else {

	    require_once('include/modules/naplo/share/dolgozat.php');
	    require_once('include/share/date/names.php');
	    require_once('include/modules/naplo/share/osztalyzatok.php');

	    // kép
	    $ADAT['kepMutat'] = __SHOW_FACES_TF;
	    $ADAT['jegyTipusok'] = getEnumField('naplo','jegy','jegyTipus');


	    if (// admin bármikor módosíthat jegyet
		($_TANEV['statusz'] == 'aktív' && __NAPLOADMIN)
		|| (// a tankör tanárai az _OSZTALYOZO határidő szerint módosíthatnak jegyet!
		    __FOLYO_TANEV &&
		    __TANAR &&
		    in_array(__USERTANARID, $jegy['tanár']['idk']) and 
		    (strtotime(_OSZTALYOZO_HATARIDO) <= strtotime($jegy['dt']))
		)
	    ) {

		define('_MODOSITHAT', true);
		global $_JSON;
	    
		// --------- action -------------- //
		if ($action == 'jegyModositas' || $action == 'jegyTorles') {
		    $_tipus = readVariable($_POST['tipus'],'numeric unsigned','NULL');

		    if (isset($_POST['jegyTorles']) || $_tipus == 0 || $action == 'jegyTorles') {
			// jegy törlése
			$action = 'jegyTorles';

			if (jegyTorles($jegyId, $jegy)) { // az eredeti jegyId és már lekérdezett jegy tömb alapján
			    if ($skin != 'ajax') {
				header('Location: '.location('index.php?page=naplo&sub=osztalyozo&f=tankor&tankorId='.$jegy['tankorId']));
				die();
			    } else {
				$_JSON = array('action' => $action, 'result' => true, 'data' => array('jegyId' => $jegyId));
				//$_SESSION['alert'][] = 'info:delete_success:jegyTorles:'.$jegyId;
				unset($jegyId); unset($jegy);
			    }
			}
		    } else { // vagy módosítás:
			list($__jegyTipus,$__jegy) = explode(':', $_POST['jegy']);
			$_jegy = readVariable($__jegy,'regexp',null,array('^[0-9]*\.[0-9]$'));
			$_jegyTipus = readVariable($__jegyTipus, 'enum', null, $ADAT['jegyTipusok']);
			$_megjegyzes = readVariable($_POST['megjegyzes'],'sql');
			$_oraId = readVariable($_POST['oraId'],'id',null);
			$_dolgozatId = readVariable($_POST['dolgozatId'],'id',null);
			// csak (1,2),(3,4,5) váltás, ill. törlés (0) a megengedett
			if (($_tipus-2.5)*($jegy['tipus']-2.5) < 0) $_tipus = $jegy['tipus'];
			if ((!is_null($_jegyTipus) && __JEGYTIPUS_VALTHATO === true) || $_jegyTipus == $jegy['jegyTipus']) {
			    if (jegyModositas($jegyId, $_jegy, $_jegyTipus, $_tipus, $_oraId, $_dolgozatId, $_megjegyzes)) {
				logAction(
				    array(
					'szoveg'=>"Jegy módosítás: $jegyId, ".$jegy['diakId'].", $_jegy, $_tipus, ".$jegy['tankorId'].", ".$jegy['dt'].", $_oraId, $_dolgozatId", 
					'table'=>'jegy'
				    ), 
				    $lr
				);
				$jegy = getJegyInfo($jegyId, __TANEV, $lr);
				$_JSON = array(
				    'action' => $action, 'result' => true, 
				    'data' => array(
					'jegyId'=>$jegyId, 'tipus'=>$jegy['tipus'], 'diakId'=>$jegy['diakId'], 'dolgozatId'=>$jegy['dolgozatId'],
					'jegyStr'=>$KOVETELMENY[ $jegy['jegyTipus'] ][ $jegy['jegy'] ]['rovid']
				    )
				);
			    }
			} else { $_SESSION['alert'][] = 'message:wrong_data:A jegyTipus nem módosítható!:'.$_jegyTipus.' - '.$jegy['jegyTipus']; }

		    }

		}
		// --------- action  vége -------- //

	    } else {
		define('_MODOSITHAT', false);
		// ezeket minek üzenjük? nem is akarta senki módosítani:
/*
		if ($_TANEV['statusz'] != 'aktív') $_SESSION['alert'][] = 'info:nem_modosithato:nem aktív tanév';
		elseif (__FOLYO_TANEV===false) $_SESSION['alert'][] = 'info:nem_modosithato:nem folyó tanév';
		elseif (__TANAR===false)  $_SESSION['alert'][] = 'info:nem_modosithato:nem tanár';
		elseif (!in_array(__USERTANARID, $jegy['tanár']['idk'])) $_SESSION['alert'][] = 'info:nem_modosithato:nem tanítja';
		elseif (strtotime(_OSZTALYOZO_HATARIDO) > strtotime($jegy['dt']))  $_SESSION['alert'][] = 'info:nem_modosithato:lejárt a módosítási határidő';
*/
	    }

            if (isset($jegyId)) { // ajax-os törlés esetén lehet, hogy már nincs jegy...
		$Orak = getOraAdatByTankor($jegy['tankorId']);
        	$Dolgozatok = getTankorDolgozatok($jegy['tankorId']);
	    }

	}

    } else {
	$_SESSION['alert'][] = 'message:wrong_data:jegy:jegyId='.$jegyId;
	unset($jegyId);
    }

?>
