<?php

    if (_RIGHTS_OK !== true) die();

    if (!__USERADMIN) {
        $_SESSION['alert'][] = "page:insufficient_access";
    } else {

    	require('include/share/ssl/ssl.php');

	$ADAT['NODETIPUSOK'] = array('intézmény','backup','fejlesztői');
	$ADAT['OSSZEG'] = array(0,1000,2000,3000,5000,10000);;
	// regisztrációs űrlap elemei (portal-mayor-ból átemelve...
	$ADAT['MEZOK'] = array(
	    'nodeTipus' => array('kotelezo'=>true,'options'=>$ADAT['NODETIPUSOK']),
    	    'nev' => array('kotelezo'=>true),
    	    'rovidNev' => array(),
    	    'OMKod' => array(),
    	    'cimHelyseg' => array('kotelezo'=>true),
    	    'cimIrsz' => array('kotelezo'=>true),
    	    'cimKozteruletNev' => array('kotelezo'=>true),
    	    'cimKozteruletJelleg' => array('kotelezo'=>true),
    	    'cimHazszam' => array('kotelezo'=>true),
    	    'telefon' => array(),
    	    'fax' => array(),
    	    'email' => array(),
    	    'honlap' => array(),
    	    'url' => array(),
    	    'kapcsolatNev' => array('kotelezo'=>true),
    	    'kapcsolatEmail' => array('kotelezo'=>true),
    	    'kapcsolatTelefon' => array(),
		// külön, más helyen jelennek meg
    	    'dij'=> array('kotelezo'=>true,'display'=>false,'type'=>'none'), 
    	    'egyebTamogatas' => array('display'=>false,'type'=>'none'),
    	    'utemezes'=>array('display'=>false,'type'=>'none'),
		 // rejtett mezők - nem módosíthatók
    	    'regId'=>array('display'=>false,'type'=>'none'),
    	    'dt'=>array('display'=>false),
    	    'publicKey' => array('kotelezo'=>true,'display'=>false,'readonly'=>true,'type'=>'textarea'),
	);
	$ADAT['my']['url'] = $url = substr($_SERVER["HTTP_REFERER"], 0, strpos($_SERVER["HTTP_REFERER"], 'index.php?'));
	$ADAT['my']['dt'] = date('Y-m-d');

	$RPC = new Interconnect();
	$ADAT['my']['publicKey'] = $RPC->getPublicKey();
	$ADAT['my']['nodeId'] = $RPC->getNodeId();

	// regisztráció
	if ($action == 'sendRegRequest') {
	    unset($_POST['action']);
	    $_POST['publicRequest'] = 'registration';
	    $res = json_decode(sendPublicRequest($_POST), true);
	    foreach ($res['alert'] as $index => $aAdat) $_SESSION['alert'][] = implode(':',$aAdat);
	    if ($res['success'] === true)  {
		setNodeId($res['nodeId'], $ADAT['my']['publicKey']);
		$ADAT['my']['nodeId'] = $res['nodeId'];
		$RPC = new Interconnect(); // újracsatlakozás...
	    }
	    //dump($res);
	} elseif ($action == 'checkOldRegByPublicKey') {
	    $DATA['publicKey'] = $ADAT['my']['publicKey'];
	    $DATA['publicRequest'] = 'getNodeIdByPublicKey';
	    // ha kellene ellenőrzés, akkar a választ a publicKey-el kódolva kellene küldeni...
	    $res = json_decode(sendPublicRequest($DATA), true);
	    foreach ($res['alert'] as $index => $aAdat) $_SESSION['alert'][] = implode(':',$aAdat);
	    if ($res['success'] === true)  {
		setNodeId($res['nodeId'], $ADAT['my']['publicKey']);
		$ADAT['my']['nodeId'] = $res['nodeId'];
		$RPC = new Interconnect(); // újracsatlakozás...
	    }
	    dump($res);
	}

	if ($ADAT['my']['nodeId'] != 0)
	try {
	    $RPC->setRemoteHostByNodeId(''); // controller
	    $RPC->setRequestTarget('controller');
    	    $ADAT['registrationStatus']['result'] = $RPC->sendRequest(array('func'=>'checkRegistration'));
	    //dump($ADAT['registrationStatus']['result'], $RPC->getStatus());
	    // Teszt: egy alap metódus lekérdezése...
	    //$RPC->setRequestTarget('base');
    	    //  $ADAT['controllerVersion'] = $RPC->sendRequest(array('func'=>'ping'));
	    //  dump($ADAT['controllerVersion']);
	    //  $RPC->setRequestTarget('controller');
	    if ($ADAT['registrationStatus']['result']['valid'] == 1) {
		$ADAT['regAdat'] = $RPC->getRegistrationDataByNodeId($ADAT['my']['nodeId']);
		//dump($ADAT['regAdat'], $RPC->getStatus());
	    }
	} catch (Exception $e) {
	    dump("checkRegistration",$e);
	}

	if ($action == 'modRegAdat') {
	    $MOD = array();
	    foreach ($ADAT['regAdat']['nodeData'] as $key => $value) {
		$newValue = readVariable($_POST[$key], 'string');
		if (
		    !in_array($key, array('nodeId','regId','publicKey'))
		    && str_replace(array("\n","\r","\n\r"),"",$value) != str_replace(array("\n","\r","\n\r"),"",$newValue)
		    && !is_null($newValue)
		) $MOD[$key] = $newValue;
	    }
	    if (count($MOD) > 0) try {
		// Interconnect-en keresztül!!
		$ADAT['modRegData']['result'] = $RPC->sendRequest(
		    array('func'=>'modRegData', 'data'=>$MOD, 'regId'=>$ADAT['regAdat']['nodeData']['regId'])
		);
		// A megjelenítéshez módosítjuk a kirakandó adatokat helyben is.
		if ($ADAT['modRegData']['result']['result'] === true) {
		    $_SESSION['alert'][] = 'info:success:A regisztrációs adatokat módosítottuk a regisztrációs szerveren.';
		    foreach ($MOD as $key => $val) $ADAT['regAdat']['nodeData'][$key] = $val;
		} else {
		    $_SESSION['alert'][] = 'message:wrong_data:Az adatmódosítás nem sikerült a regisztrációs szerveren.';
		}
	    } catch (Exception $e) {
		dump("modRegData",$e);
	    }

	}

    }

?>
