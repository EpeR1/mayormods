<?php

    if (_RIGHTS_OK !== true) die();

    if (!__USERADMIN) {
        $_SESSION['alert'][] = "page:insufficient_access";
    } else {

    	require('include/share/ssl/ssl.php');

	$ADAT['my']['url'] = $url = substr($_SERVER["HTTP_REFERER"], 0, strpos($_SERVER["HTTP_REFERER"], 'index.php?'));
	$ADAT['my']['dt'] = date('Y-m-d');

	$RPC = new Interconnect();
	$ADAT['my']['publicKey'] = $RPC->getPublicKey();
	$ADAT['my']['nodeId'] = $RPC->getNodeId();


	if ($action == 'rpcPing') {
	    $ADAT['nodeId'] = readVariable($_POST['nodeId'],'id');
	    $RPC->setRemoteHostByNodeId($ADAT['nodeId']);
            $RPC->setRequestTarget('base');
	    $ADAT['pingResult'] = $RPC->sendRequest(array('func'=>'ping'));
            $ADAT['nodeVersion'] = $ADAT['pingResult']['revision'];
            //  $RPC->setRequestTarget('controller');
	} elseif ($action == 'getPublicDataByNodeId') {
	    $ADAT['nodeId'] = readVariable($_POST['nodeId'],'id');
            $RPC->setRequestTarget('base');
	    $RPC->setRemoteHostByNodeId($ADAT['nodeId']);
	} elseif ($action == 'removeNode') {
	    $ADAT['nodeId'] = readVariable($_POST['nodeId'],'id');
	    removeNodeFromLocalKeychain($ADAT['nodeId']);	    
	}

	$ADAT['nodes'] =  getPublicDataFromLocalKeychain();

    }

?>
