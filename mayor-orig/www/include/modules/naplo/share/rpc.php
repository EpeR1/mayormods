<?php

    function getRPCPrivileges() {

	$q = "SELECT * FROM rpcJogosultsag";
	return db_query($q, array('fv'=>'getRPCPrivileges','modul'=>'naplo_base','result'=>'indexed'));

    }
    function getRPCPrivilegeByNUO($nodeId, $userAccount, $OMKod) {

	$q = "SELECT priv FROM rpcJogosultsag WHERE nodeId=%u AND userAccount='%s' AND OMKod=%u";
	$v = array($nodeId, $userAccount, $OMKod);
	return db_query($q, array('fv'=>'getRPCPrivileges','modul'=>'naplo_base','result'=>'value','values'=>$v));

    }
    function addNodeToRPCPrivs($DATA) {

	$q = "INSERT INTO rpcJogosultsag (nodeId, userAccount, OMKod) VALUES (%u, '%s', %u)";
	$v = array($DATA['nodeId'], $DATA['userAccount'], $DATA['OMKod']);
	return db_query($q, array('debug'=>false,'fv'=>'addNodeToRPCPrivs','modul'=>'naplo_base','values'=>$v));

    }
    function setPrivileges($DATA) {
	$q = "UPDATE rpcJogosultsag SET priv='%s' WHERE nodeId=%u AND userAccount='%s' AND OMKod=%u";
	$v = array(implode(',',$DATA['priv']),$DATA['nodeId'],$DATA['userAccount'],$DATA['OMKod']);
	return db_query($q, array('debug'=>false,'fv'=>'setPrivileges','modul'=>'naplo_base','values'=>$v));
    }

?>