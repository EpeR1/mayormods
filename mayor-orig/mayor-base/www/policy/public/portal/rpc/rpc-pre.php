<?php
/*
    MOVED!!
     - mayor-base/public/rpc/rpc
     - mayor-portal-mayor/rpc/controller/rpc
    ===========================================

    require_once('include/share/ssl/ssl.php');

    // MOVE
    function getRegisztraltIskolaAdat($nodeId) {
	if ($nodeId=='') {
	    $q = "SELECT * FROM regisztracio";
	    $r = db_query($q, array('modul'=>'portal','result'=>'indexed'));
	} else {
	    $q = "SELECT * FROM regisztracio WHERE nodeId='%s'";
	    $v = array($nodeId);
	    $r = db_query($q, array('modul'=>'portal','result'=>'record','values'=>$v));
	}
	return $r;
    }

    function getPublicDataByNodeIdFromReg($nodeId) {
	$q = "SELECT * FROM regisztracio WHERE nodeId='%s'";
	$v = array($nodeId);
	$r = db_query($q, array('debug'=>false,'fv'=>'getPublicDataByNodeIdFromReg','modul'=>'portal','result'=>'record','values'=>$v));
	return $r;
    }
    function modRegData($nodeId, $regId, $DATA) {
	$q = "UPDATE regisztracio SET ".implode(',',array_fill(0, count($DATA), "%s='%s'"))." WHERE regId=%u AND nodeId=%u";
	foreach ($DATA as $key=>$val) {
	    $v[] = $key; $v[] = $val;
	}
	$v[] = $regId; $v[] = $nodeId;
	$r = db_query($q, array('debug'=>true,'fv'=>'modRegData','modul'=>'portal','values'=>$v));
	if ($r) return true;
	else return $q;
    }

    /* Class: Interconnect AES * /

    /* remote procedure call remote controller * /
    try 
    {
	/* rights.php:
		$RPC = new Interconnect();
		$RPC->setRemoteHostByNodeId($senderNodeId);
		$REQUEST = $RPC->processRequest();
	* /
	$REQUEST = $RPC->getIncomingRequest();
	$func = $REQUEST['func'];

    }
    catch (Exception $e)
    {
	$func='';
	$DATA = array('error'=>$e->getMessage());	
    }
    // processing
    $DATA = array();
    if (isset($func) && $func!='') {
	switch ($func) {
	    case 'getVersion':
	    case 'ping':
		$DATA = array('func'=>'getVersion','response_revision'=>_MAYORREV,'pong');
		$RPC->setResponse($DATA);
		break;
	    case 'checkRegistration':
		$DATA = getPublicDataByNodeIdFromReg($senderNodeId);
		if (is_array($DATA)) $valid = 1; else $valid = 0;
		$DATA = array('func'=>$func,'valid'=>$valid,'status'=>$RPC->getStatus(),'get'=>$_GET,'post'=>$_POST);
		$RPC->setResponse($DATA);
		break;
	    case 'getPublicDataByNodeId':
		$DATA = getPublicDataByNodeIdFromReg($REQUEST['nodeId']);
		$RPC->setResponse($DATA);
		break;
	    case 'modRegData':
		$DATA['result'] = modRegData($senderNodeId,$REQUEST['regId'],$REQUEST['data']);
		$DATA['func'] = 'modRegData';
		$RPC->setResponse($DATA);
		break;
	    case 'getIskola':
		$iskolaAdat = getRegisztraltIskolaAdat($REQUEST['otherNodeId']);
		$DATA = array('func'=>$func,'iskolaAdat'=>$iskolaAdat);
		$RPC->setResponse($DATA);
		break;
	    case 'getRegistrationData':
		$DATA = getPublicDataByNodeIdFromReg($REQUEST['nodeId']);
		break;
	    case 'refreshRegistration':
		break;
	    case 'getPublicData':
		$iskolaAdat = getRegisztraltIskolaAdat();
		$DATA = array('func'=>$func,'szomszedok'=>$iskolaAdat);
		$RPC->setResponse($DATA);
		break;
	    default:		
		break;
	}
	
    }
*/
?>
