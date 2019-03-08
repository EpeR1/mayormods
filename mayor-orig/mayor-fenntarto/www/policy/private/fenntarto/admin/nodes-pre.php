<?php

        if (_RIGHTS_OK !== true) die();


        require_once('include/share/ssl/ssl.php');
        require_once('include/modules/fenntarto/share/rpc.php');
        require_once('include/modules/fenntarto/share/sql.php');

        $ADAT['requests'] = getSetField('fenntarto', 'rpcKerelem', 'requ');

	$ADAT['nodes'] = reindex(getPublicDataFromLocalKeychain(), array('nodeId'));
        $ADAT['requ'] = getRPCRequests(_USERACCOUNT);

	$RPC = new Interconnect();
        $ADAT['my']['publicKey'] = $RPC->getPublicKey();
        $ADAT['my']['nodeId'] = $RPC->getNodeId();

        if ($action == 'addNode') {

            $DATA['userAccount'] = _USERACCOUNT;
            $DATA['nodeId'] = readVariable($_POST['nodeId'], 'id');
            $DATA['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned');

            addNodeToRPCRequs($DATA);
            $ADAT['requ'] = getRPCRequests(_USERACCOUNT);

        } elseif ($action == 'setRequ') {

            $DATA['userAccount'] = _USERACCOUNT;
            $DATA['nodeId'] = readVariable($_POST['nodeId'], 'id');
            $DATA['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned');
            $DATA['requ'] = readVariable($_POST['requ'], 'enum', 'OMKod', $ADAT['requests']);

            setRequests($DATA);
            $ADAT['requ'] = getRPCRequests(_USERACCOUNT);

	} elseif ($action == 'delRequ') {

            $DATA['userAccount'] = _USERACCOUNT;
            $DATA['nodeId'] = readVariable($_POST['nodeId'], 'id');
            $DATA['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned');

	    delNodeFromRPCRequs($DATA);
            $ADAT['requ'] = getRPCRequests(_USERACCOUNT);

        } elseif ($action == 'checkJogosultsag') {

	    // honnan
            $DATA['nodeId'] = $ADAT['my']['nodeId'];
            $DATA['userAccount'] = _USERACCOUNT;
	    // hova
	    $remoteNodeId = readVariable($_POST['nodeId'], 'id');
	    $RPC->setRemoteHostByNodeId($remoteNodeId);
            $DATA['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned');
	    // mit
	    $DATA['func'] = 'getPrivilegeInfo';

            $RPC->setRequestTarget('naplo');
            $ADAT['rpcResult'] = $RPC->sendRequest($DATA);
	    if (is_array($ADAT['rpcResult']['alert'])) foreach ($ADAT['rpcResult']['alert'] as $alert) $_SESSION['alert'][] = $alert;
	    elseif (isset($ADAT['rpcResult']['alert'])) $_SESSION['alert'][] = $ADAT['rpcResult']['alert'];
	    if (is_array($ADAT['rpcResult']['priv'])) $ADAT['nodes'][ $RPC->getRemoteNodeId() ][0]['priv'] = $ADAT['rpcResult']['priv'];
	    //dump($ADAT['rpcResult']);

	}


?>