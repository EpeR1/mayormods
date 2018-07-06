<?php
/*
    module: mayor-base

    A alap metódusok RPC kezelője

*/
    require_once('include/share/ssl/ssl.php');
    require_once('include/modules/naplo/share/rpc.php');

    /* 
	rights.php:
	    $_RPC['senderNodeId']
	    $RPC = new Interconnect();
	    $RPC->setRemoteHostByNodeId($senderNodeId);
	    $RPC->processRequest();
	    $_RPC['request'] = $RPC->getIncomingRequest();
    */
    $func = $_RPC['request']['func'];
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
	    case 'getPrivilegeInfo':
		$nodeId = $_RPC['senderNodeId'];
		$userAccount = readVariable($_RPC['request']['userAccount'], 'string');
		$OMKod = readVariable($_RPC['request']['OMKod'],'numeric unsigned');
		$priv = explode(',',getRPCPrivilegeByNUO($nodeId, $userAccount, $OMKod));
		if (is_array($priv) && in_array('Jogosultság',$priv)) {
		    $DATA = array('func'=>'getPrivilegeInfo','result'=>'success','priv'=>$priv);
		} else {
		    $DATA = array('func'=>'getPrivilegeInfo','result'=>'failure','alert'=>'message:insufficient_access');
		}
		$RPC->setResponse($DATA);
		break;
	    case 'getTantargyfelosztasStat':
		$nodeId = $_RPC['senderNodeId'];
		$userAccount = readVariable($_RPC['request']['userAccount'], 'string');
		$OMKod = readVariable($_RPC['request']['OMKod'],'numeric unsigned'); // a naplo/base/rights már felhasználta az __INTEZMENY beállításnál
		$priv = explode(',',getRPCPrivilegeByNUO($nodeId, $userAccount, $OMKod));
		if (is_array($priv) && in_array('Tantárgyfelosztás',$priv)) {
		    // Az __INTEZMENY és __TANEV beállítását a naplo/rights.php már elvégezte...

		    require_once('include/modules/naplo/share/intezmenyek.php');
		    require_once('include/modules/naplo/share/osztaly.php');
		    require_once('include/modules/naplo/share/targy.php');
		    require_once('include/modules/naplo/share/tankor.php');
		    require_once('include/modules/naplo/stat/tantargyFelosztas.php');
		    global $ADAT; $ADAT = array();
		    $IA = getTantargyfelosztasStat();

		    $DATA = array('func'=>'getTantargyfelosztasStat','result'=>'success','tanev'=>__TANEV,'intezmeny'=>__INTEZMENY, 'IA'=>$IA);
		} else {
		    $DATA = array('func'=>'getTantargyfelosztasStat','result'=>'failure','alert'=>'message:insufficient_access');
		}
		$RPC->setResponse($DATA);
		break;
	    default:
		$DATA['result'] = 'ismeretlen függvény: '.$func;
		break;
	}
	
    }
?>
