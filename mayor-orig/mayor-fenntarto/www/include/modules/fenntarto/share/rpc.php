<?php

    function getRPCRequests($userAccount, $requ = null) {

	if (isset($requ)) {
    	    $q = "SELECT * FROM rpcKerelem WHERE userAccount='%s' AND requ LIKE '%%%s%%'";
	    $v = array($userAccount, $requ);
	} else {
    	    $q = "SELECT * FROM rpcKerelem WHERE userAccount='%s'";
	    $v = array($userAccount);
        }
	return db_query($q, array('debug'=>false,'fv'=>'getRPCRequests','modul'=>'fenntarto','result'=>'indexed','values'=>$v));

    }
    function delNodeFromRPCRequs($DATA) {

        $q = "DELETE FROM rpcKerelem WHERE nodeId=%u AND userAccount='%s' AND OMKod=%u";
        $v = array($DATA['nodeId'], $DATA['userAccount'], $DATA['OMKod']);
        return db_query($q, array('debug'=>false,'fv'=>'delNodeFromRPCRequs','modul'=>'fenntarto','values'=>$v));

    }
    function addNodeToRPCRequs($DATA) {

        $q = "INSERT INTO rpcKerelem (nodeId, userAccount, OMKod) VALUES (%u, '%s', %u)";
        $v = array($DATA['nodeId'], $DATA['userAccount'], $DATA['OMKod']);
        return db_query($q, array('debug'=>false,'fv'=>'addNodeToRPCRequs','modul'=>'fenntarto','values'=>$v));

    }
    function setRequests($DATA) {

        $q = "UPDATE rpcKerelem SET requ='%s' WHERE nodeId=%u AND userAccount='%s' AND OMKod=%u";
        $v = array(implode(',',$DATA['requ']),$DATA['nodeId'],$DATA['userAccount'],$DATA['OMKod']);
        return db_query($q, array('debug'=>false,'fv'=>'setRequests','modul'=>'fenntarto','values'=>$v));

    }


?>
