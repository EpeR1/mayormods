<?php
/*
    module: mayor-base

    A alap metódusok RPC kezelője

*/
    require_once('include/share/ssl/ssl.php');

    try 
    {
	/* 
	    rights.php:
		$RPC = new Interconnect();
		$RPC->setRemoteHostByNodeId($senderNodeId);
		$REQUEST = $RPC->processRequest();
	*/
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
	    // itt a currens verziót kellene visszaadni
	    case 'getVersion':
	    case 'ping':
		$DATA = array('func'=>'getVersion','revision'=>_MAYORREV,'pong');
		$RPC->setResponse($DATA);
		break;
	    default:
		$DATA['result'] = 'ismeretlen függvény: '.$func;
		break;
	}
	
    }
?>
