<?php

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

        require_once('include/share/ssl/ssl.php');
        require_once('include/modules/fenntarto/share/rpc.php');
        require_once('include/modules/fenntarto/share/osztaly.php');
        require_once('include/modules/fenntarto/share/sql.php');


	$RPC = new Interconnect();
	$ADAT['my']['publicKey'] = $RPC->getPublicKey();
	$ADAT['my']['nodeId'] = $RPC->getNodeId();

	$tanev = $ADAT['tanev'] = readVariable($_POST['tanev'], 'numeric unsigned');
	$ADAT['show'] = $req = readVariable($_POST['req'], 'string', null);
	// Kívánt tantárgyfelosztást adó node-ok lekérdezése
	$ADAT['requs'] = getRPCRequests(_USERACCOUNT, 'Tantárgyfelosztás');
	foreach ($ADAT['requs'] as $index => $rAdat) {
	    $nAdat = getPublicDataFromLocalKeychain($rAdat['nodeId']);
	    $ADAT['requs'][$index]['nev'] = $nAdat['nev'];
	}

	if (isset($tanev)) {

	    // Ezek jobb lenne, ha RPC-ből jönnének???
	    // Most két helyen javíandó, ha változik (naplo/include/tantargyfelosztas)
    	    $ADAT['evfolyamJelek'] = getEvfolyamJelek(array('result'=>'idonly'));
	    $ADAT['finanszírozott pedagógus létszám'] = array(
    		'általános iskola'                              => 11.8, // 11.8 tanuló / 1 pedagógus
    		'gimnázium'                                     => 12.5, // 12.5 tanuló / 1 pedagógus
    		'szakiskola, Híd programok'                     => 12,   // ...
    		'szakközépiskola, nem szakkképző évfolyam'      => 12.4,
    		'szakközépiskola, szakkképző évfolyam'          => 13.7
	    ); // -- TODO szakgimnázium???



	    if (is_array($ADAT['requs'])) foreach ($ADAT['requs'] as $rAdat) {
		if (!in_array($rAdat['nodeId'].'/'.$rAdat['OMKod'], $req)) continue;
		// honnan
        	$DATA['nodeId'] = $ADAT['my']['nodeId'];
        	$DATA['userAccount'] = _USERACCOUNT;
        	// hova
    		$RPC->setRemoteHostByNodeId($rAdat['nodeId']);
        	$DATA['OMKod'] = $rAdat['OMKod'];
        	// mit
        	$DATA['func'] = 'getTantargyfelosztasStat';
		$DATA['tanev'] = $tanev;

        	$RPC->setRequestTarget('naplo');
            	$ADAT['rpcResult'] = $RPC->sendRequest($DATA);
		// alert
        	if (is_array($ADAT['rpcResult']['alert'])) foreach ($ADAT['rpcResult']['alert'] as $alert) $_SESSION['alert'][] = $alert;
        	elseif (isset($ADAT['rpcResult']['alert'])) $_SESSION['alert'][] = $ADAT['rpcResult']['alert'];
 
		$ADAT['intezmeny'][] = $ADAT['rpcResult']['IA'];
		//dump($ADAT['rpcResult']);
	    }
	}
    }
?>