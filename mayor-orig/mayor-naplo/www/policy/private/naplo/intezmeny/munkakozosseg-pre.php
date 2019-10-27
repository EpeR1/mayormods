<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');
    require_once('include/modules/naplo/share/targy.php');
    require_once('include/modules/naplo/share/file.php');

    $ADAT['targyJellegENUM'] = getEnumField('naplo_intezmeny','targy','targyJelleg');
    $ADAT['targy.evkoziKovetelmeny'] = getEnumField('naplo_intezmeny','targy','evkoziKovetelmeny');
    $ADAT['targy.zaroKovetelmeny'] = getEnumField('naplo_intezmeny','targy','zaroKovetelmeny');

    $ADAT['mkId'] = $mkId = readVariable($_POST['mkId'],'id');
    $ADAT['targyId'] = $targyId = readVariable($_POST['targyId'],'id');
    $ADAT['targyJelleg'] = $targyJelleg = readVariable($_POST['targyJelleg'],'sql');

    $ADAT['kirTargyak'] = getKirTargyak();

    if (__NAPLOADMIN) {
	switch ($action) {
	    case 'ujMunkakozosseg':
		if ($_POST['leiras'] != '')
		    if ( ($mkId=ujMunkakozosseg($_POST['leiras'],$_POST['mkVezId']))===false )
			unset($mkId); unset($ADAT['mkId']);
		break;
	    case 'modMunkakozosseg':
		if ($_POST['mkId']!='' && $_POST['leiras'] != '')
		    modMunkakozosseg($_POST['mkId'],$_POST['leiras'],$_POST['mkVezId'],$_POST['mkUjTagok'],$_POST['mkTagok']);
		break;
	    case 'ujTargy':
		if ($_POST['targyleiras']!='') {
		    $_ADAT['mkId'] = readVariable($_POST['mkId'],'id');
		    $_ADAT['leiras']=readVariable($_POST['targyleiras'],'string');
		    $_ADAT['targyJelleg']=readVariable($_POST['targyJelleg'],'string');
		    $_ADAT['evkoziKovetelmeny']=readVariable($_POST['evkoziKovetelmeny'],'string');
		    $_ADAT['zaroKovetelmeny']=readVariable($_POST['zaroKovetelmeny'],'string');
		    $_ADAT['kirTargyId'] = readVariable($_POST['kirTargyId'],'id',null, $ADAT['kirTargyak']);
		    $_ADAT['kretaTargyNev']=readVariable($_POST['kretaTargyNev'],'string');
		    $_ti=ujTargy($_ADAT);
		    if ($_ti!==false && is_numeric($_ti)) $targyId=$_ti;
		    unset($_ti);
		    unset($_ADAT);
		}
		break;
	    case 'targyValtoztat':
		$_ADAT['targyId'] = $targyId;
		$_ADAT['targyJelleg'] = $targyJelleg;
		$_ADAT['evkoziKovetelmeny'] =  readVariable($_POST['evkoziKovetelmeny'],'sql');
		$_ADAT['zaroKovetelmeny'] = readVariable($_POST['zaroKovetelmeny'],'sql');
		$_ADAT['targyRovidNev'] =  readVariable($_POST['targyRovidNev'],'sql');
		$_ADAT['kirTargyId'] = readVariable($_POST['kirTargyId'],'id',null);
		$_ADAT['kretaTargyNev']=readVariable($_POST['kretaTargyNev'],'string');
		targyModosit($_ADAT);
		break;
	    case 'targyTorol':
		if (targyTorol($_POST['targyId'],$_POST['mkId']))
		    unset($targyId);
		break;
	    case 'munkakozossegTorol':
		if (munkakozossegTorol($_POST['mkId'])) 
		    unset($mkId); unset($ADAT['mkId']);
		break;
	    case 'targyAtnevezes':
		$ADAT['ujTargyNev'] = readVariable($_POST['ujTargyNev'],'string');
		if ($ADAT['ujTargyNev'] != '') targyAtnevezes($ADAT);
		break;
	    case 'targyMkValtas':
		$ADAT['befogadoMkId'] = readVariable($_POST['befogadoMkId'], 'id');
		if (targyMkValtas($ADAT)) {
		    unset($mkId); unset($ADAT['mkId']);
		}
		break;
	    case 'targyBeolvasztas':
		$ADAT['befogadoTargyId'] = readVariable($_POST['befogadoTargyId'], 'id');
		$ADAT['tankorJeloles'] = readVariable($_POST['tankorJeloles'], 'enum', null, array_values($TANKOR_TIPUS));
		if (isset($ADAT['befogadoTargyId'])) {
		    if (targyBeolvasztas($ADAT)) {
			$_SESSION['alert'][] = 'info:success';
			unset($targyId); unset($ADAT['targyId']);
		    }
		}
		break;
	}
    }

    $TANAROK = getTanarok();

    // ha csak tággyId adott, kérdezzük le a mkId-t is...
    
    $ADAT['munkakozossegek'] = getMunkakozossegek();
    $ADAT['targyTargy'] = getTargyTargy();
    if ($targyId!='') {
	$ADAT['targyAdat'] = getTargyById($targyId);
	if ($mkId=='') $ADAT['mkId'] = $mkId = $ADAT['targyAdat']['mkId'];
    }

    if (isset($mkId) && $mkId!='') {
	if (defined('__TANEV')) $__TANEV = __TANEV; else $__TANEV = '';
	$TANAROK_INMK = getTanarok(array('mkId' => $mkId, 'tanev' => $__TANEV));
	$ADAT['mkAdat'] = getMunkakozossegById($mkId);
	$ADAT['targyak'] = getTargyakByMkId($mkId);
    } else {
	$TANAROK_INMK = array();
    }
    

    $TOOL['munkakozossegSelect'] = array('tipus'=>'cella','munkakozossegek'=>$ADAT['munkakozossegek'],'paramName' => 'mkId', 'post'=>array());
    $TOOL['targySelect'] = array('tipus'=>'cella', 'mkId' => $mkId, 'post'=>array('mkId'));
    getToolParameters();

?>
