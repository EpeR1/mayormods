<?php

    require_once('include/share/ssl/ssl.php');
    /* Class: Interconnect AES */

    /* remote procedure call remote controller */
    $rpcDetails = $_GET['detail'];
    $initOMKod = readVariable($_GET['init'],'strictstring',0);

    try 
    {
	$RPC = new Interconnect();
	$RPC->setRemoteOMKod($initOMKod);
	$REQUEST = $RPC->processRequest($rpcDetails);
	$func = $REQUEST->func;
    }
    catch (Exception $e)
    {
	$func='';
	$DATA = array('error'=>$e->getMessage());	
    }
    // processing

    // MASTER BOSS -> portal/rpc/rpc, ez itt ELAVULT
    $DATA = array();
    if (isset($func) && $func!='') {
	switch ($func) {
	    case 'getVersion':
	    case 'ping':
		$DATA = $RPC->prepareReply(
		    array('func'=>'getVersion','response_revision'=>_MAYORREV,'pong')
		);
		$RPC->setResponse($DATA);
		break;
	    case 'checkRegistration':
		$otherPublicKey = getSslPublicKeyByOMKod($REQUEST->OMKOD);
		if ($otherPublicKey===false) $valid=0;
		elseif ($REQUEST->publicKey == $otherPublicKey) $valid=1;
		else $valid=2;
		$DATA = $RPC->prepareReply(
		    array('func'=>$func,'response'=>serialize($REQUEST),'valid'=>$valid)
		);
		$RPC->setResponse($DATA);
	    default:		
		break;
	}
    }
?>
