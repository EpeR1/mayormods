<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {


	$tanev = readVariable($_POST['tanev'],'numeric',__TANEV);
	$osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/kepzes.php');
	require_once('include/modules/session/search/searchGroup.php');
	require_once('include/modules/session/search/searchAccount.php');
	require_once('include/modules/session/createAccount.php');

	if (isset($osztalyId)) {
	    $osztalyAdat = getOsztalyAdat($osztalyId, $tanev, $lr);
	    $osztalyTagok = getOsztalyNevsorEsOid($osztalyId);
	    $oIds = $Tagok = array();
	    for ($i = 0; $i < count($osztalyTagok); $i++) {
		$oIds[] = $oId = $osztalyTagok[$i]['oId'];
		$Tagok[$oId] = $osztalyTagok[$i];
		$osztalyTagok[$i]['userAccounts'] = array();
		if ($oId != '') {
		    $ret = searchAccount('studyId', $oId, array('userAccount'), 'private');
            	    if ($ret['count'] == 1) {
			$osztalyTagok[$i]['userAccount'] = $ret[0]['userAccount'][0];
            	    } elseif ($ret['count'] > 1) {
			$osztalyTagok[$i]['userAccounts'] = array();
			for ($j = 0; $j < $ret['count']; $j++) $osztalyTagok[$i]['userAccounts'][] = $ret[$j]['userAccount'][0];
			$_SESSION['alert'][] = "message:wrong_data:több account egy oId-hoz:oId=$oId:userAccount=".implode(',', $osztalyTagok[$i]['userAccounts']);
		    }
		    if (!isset($osztalyTagok[$i]['userAccount']) && count($osztalyTagok[$i]['userAccounts']) == 0) {
			// Ha az oId alapján nem találtunk, akkor tovább keresünk név alapján
			$ret = searchAccount('userCn', $osztalyTagok[$i]['diakNev'], array('userAccount','studyId'), 'private');
           		if ($ret['count'] > 0) {
			    $osztalyTagok[$i]['userAccounts'] = $osztalyTagok[$i]['studyIds'] = array();
			    for ($j = 0; $j < $ret['count']; $j++) {
				$osztalyTagok[$i]['userAccounts'][] = $ret[$j]['userAccount'][0];
				$osztalyTagok[$i]['studyIds'][] = $ret[$j]['studyId'][0];
			    }
			}
			// Javasolt azonosító generálása
			$csNev = $osztalyTagok[$i]['viseltCsaladinev'];
			$pos = mb_strpos($csNev, ' ', 0, 'UTF-8');
			if ($pos > 0 && mb_substr($csNev, $pos-2, 2, 'UTF-8') == 'né') {
			    $csNev = mb_substr($csNev,$pos+1,strlen($csNev)-$pos-1,'UTF-8');
			    $pos = mb_strpos($csNev, ' ', 0, 'UTF-8');
			}
			if ($pos > 0) $csNev = mb_substr($csNev, 0, $pos, 'UTF-8');
			$uNev = $osztalyTagok[$i]['viseltUtonev'];
			$pos = mb_strpos($uNev, ' ', 0, 'UTF-8');
			if ($pos > 0) $uNev = mb_substr($uNev, 0, $pos, 'UTF-8');
			if ($AUTH[_POLICY]['unixStyleAccounts']) {
			    $csNev = ekezettelen(kisbetus($csNev));
			    $uNev = ekezettelen(kisbetus($uNev));
			    $osztalyTagok[$i]['generatedAccount'] = substr($csNev.$uNev[0], 0, 8);
			} else {
    			    $pos = mb_strpos($csNev, ' ', 0, 'UTF-8');
			    $osztalyTagok[$i]['generatedAccount'] = $csNev.'.'.$uNev;
			}
		    }
		}
	    }
	}

	if ($action == 'createAzonosito') {
	    $userPassword = readVariable($_POST['userPassword'], 'sql', null);
	    $CONTAINER = $AUTH['private'][ $AUTH['private']['backend'].'Containers'];
	    $container = readVariable($_POST['container'], 'enum', null, $CONTAINER);
	    if (is_array($CONTAINER) && count($CONTAINER) > 0 && !isset($container)) {
		$_SESSION['alert'][] = 'message:empty_fields:container';
	    } else {
		define('__JELSZOGENERALAS', $userPassword == '');

		if (is_array($_POST['userAccount'])) for ($i = 0; $i < count($_POST['userAccount']); $i++) {
		    $oId = readVariable($_POST['oId'][$i], 'number', null);
		    $userAccount = readVariable($_POST['userAccount'][$i], 'sql', null);
		    if (isset($oId) && isset($userAccount)) {
			if (__JELSZOGENERALAS === true) {
			    $userPassword = $Tagok[$oId]['userPassword'] = sprintf("%u", crc32($Tagok[$oId]['viseltCsaladinev']));
			}
			$userCn = $Tagok[$oId]['diakNev'];
			createAccount($userCn, $userAccount, $userPassword, 'private', 
			    $SET = array('category' => 'diák', 'container' => $container, 'groups' => '', 'policyAttrs' => array('studyId' => $oId))
			);
			// a settings táblában felvesszük, hogy melyik intézményhez van rendelve (ez persze nem korlátoz, csak egy alapértelmezés
                        $q = "DELETE FROM settings WHERE userAccount='%s' AND policy='%s'";
                        db_query($q, array('debug'=>false,'fv'=>'updateNaploSettings','modul'=>'naplo_base','values'=>array($userAccount, 'private')));
                        $q = "INSERT INTO settings (userAccount,policy,intezmeny) VALUES ('%s','%s','%s')";
                        db_query($q, array('debug'=>false,'fv'=>'updateNaploSettings','modul'=>'naplo_base','values'=>array($userAccount, 'private', __INTEZMENY)));
		    }
		}
	    }

	    // Újraolvasás
	    $osztalyTagok = getOsztalyNevsorEsOid($osztalyId);
	    for ($i = 0; $i < count($osztalyTagok); $i++) {
		$oId = $osztalyTagok[$i]['oId'];
		$osztalyTagok[$i]['userAccounts'] = array();
		if ($oId != '') {
		    $ret = searchAccount('studyId', $oId, array('userAccount'), 'private');
            	    if ($ret['count'] == 1) {
			$osztalyTagok[$i]['userAccount'] = $ret[0]['userAccount'][0];
            	    } elseif ($ret['count'] > 1) {
			$osztalyTagok[$i]['userAccounts'] = array();
			for ($j = 0; $j < $ret['count']; $j++) $osztalyTagok[$i]['userAccounts'][] = $ret[$j]['userAccount'][0];
		    }
		    if (!isset($osztalyTagok[$i]) && !is_array($osztalyTagok[$i]['userAccounts'])) {
			// Ha az oId alapján nem találtunk, akkor tovább keresünk név alapján
			$ret = searchAccount('userCn', $osztalyTagok[$i]['diakNev'], array('userAccount','studyId'), 'private');
            		if ($ret['count'] > 0) {
			    $osztalyTagok[$i]['userAccounts'] = $osztalyTagok[$i]['studyIds'] = array();
			    for ($j = 0; $j < $ret['count']; $j++) {
				$osztalyTagok[$i]['userAccounts'][] = $ret[$j]['userAccount'][0];
				$osztalyTagok[$i]['studyIds'][] = $ret[$j]['studyId'][0];
			    }
			}
		    }
		}
	    } // for - újraolvasás
	}

	$TOOL['tanevSelect'] = array('tipus' => 'cella','tanev' => $tanev, 'paramName' => 'tanev', 'post' => array('osztalyId'));
	$TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('igDt', 'tanev'));
	getToolParameters();


    }

?>
