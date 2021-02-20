<?php

if (_RIGHTS_OK !== true) die();

    require_once('include/modules/portal/share/hirek.php');
    $hirId = readVariable($_POST['hirId'],'id',null);
    if ($hirId=='') $hirId = readVariable($_GET['hirId'],'id',null);
    $action = readVariable($_POST['action'],'strictstring',array(null,'save',''));
//    if (__PORTAL_CODE=='vmg' && $hirId>0 && isOwner($hirId)===false) {
//	$_SESSION['alert'][] = 'page:not_owner';
//    }

    if ($action=='save' && (__HIREKADMIN || $hirId=='' || isOwner($hirId))) {
       global $LANGUAGES;
	if (__HIREKADMIN === true) {
    	    $DATA['cim'] = readVariable($_POST['cim'],'string');
    	    $DATA['txt'] = readVariable($_POST['txt'],'string');
	} else {
    	    $DATA['cim'] = readVariable($_POST['cim'],'string');
    	    $DATA['txt'] = readVariable($_POST['txt'],'string');
	}
        $DATA['hirId'] = $hirId;
    	$DATA['pic'] = readVariable($_POST['pic'],'string');
        $DATA['kdt'] = readVariable($_POST['kdt'],'datetime',date('Y-m-d H:i:s'));
        $DATA['vdt'] = readVariable($_POST['vdt'],'datetime');
        $DATA['flag']= readVariable($_POST['flag'],'numeric',null);
        $DATA['class']= readVariable($_POST['class'],'numeric',null);
        $DATA['cid']= readVariable($_POST['cid'],'numeric',null);
        $DATA['kategoriaId']= readVariable($_POST['kategoriaId'],'id',null);
        $DATA['lang'] = readVariable($_POST['lang'],'strictstring',$LANGUAGES);
	$DATA['owner'] = _USERACCOUNT;
	if (is_array($_POST['csoport'])) {
	    $DATA['csoport'] = implode(',',readVariable($_POST['csoport'],'sql'));
	}
	$r = saveHir($DATA);
	if ($hirId=='') $hirId=$r;
    }
    if ($hirId!='' && is_numeric($hirId) && (__HIREKADMIN===true || isOwner($hirId)===true))
	$HIREK = getHirek(array('id'=>$hirId));
    elseif ($hirId!='')
	$_SESSION['alert'][] = 'page:not_owner';

    $ADAT['kategoriak'] = getKategoriak();

?>
