<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {

        $_SESSION['alert'][] = 'page:insufficient_access';

    } else {

	require_once('include/share/ssl/ssl.php');
	require_once('include/modules/naplo/share/intezmenyek.php');
	require_once('include/modules/naplo/share/rpc.php');
	require_once('include/modules/naplo/share/file.php');

	$ADAT['privileges'] = getSetField('naplo_base', 'rpcJogosultsag', 'priv');

	$ADAT['nodes'] = reindex(getPublicDataFromLocalKeychain(), array('nodeId'));
	$ADAT['intezmenyek'] = reindex(getIntezmenyek(), array('OMKod'));
	$ADAT['privs'] = getRPCPrivileges();

	if ($action == 'addNode') {

	    $DATA['nodeId'] = readVariable($_POST['nodeId'], 'id');
	    $DATA['userAccount'] = readVariable($_POST['userAccount'], 'string');
	    $DATA['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned');

	    addNodeToRPCPrivs($DATA);
	    $ADAT['privs'] = getRPCPrivileges();

	} elseif ($action == 'setPriv') {

	    $DATA['nodeId'] = readVariable($_POST['nodeId'], 'id');
	    $DATA['userAccount'] = readVariable($_POST['userAccount'], 'string');
	    $DATA['OMKod'] = readVariable($_POST['OMKod'], 'numeric unsigned');
	    $DATA['priv'] = readVariable($_POST['priv'], 'enum', 'OMKod', $ADAT['privileges']);

	    setPrivileges($DATA);
	    $ADAT['privs'] = getRPCPrivileges();
	    
	}

//dump($ADAT['privileges']);
    }

?>