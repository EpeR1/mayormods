<?php

if (_RIGHTS_OK !== true) die();
if (!__NAPLOADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
} else {


	$tanev = readVariable($_POST['tanev'],'numeric',__TANEV);
	$osztalyId = readVariable($_POST['osztalyId'], 'numeric unsigned', null);

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/session/search/searchGroup.php');
	require_once('include/modules/session/search/searchAccount.php');
	require_once('include/modules/session/createAccount.php');

    if (isset($osztalyId)) {

	    $osztalyTagok = getOsztalyNevsorEsSzulo($osztalyId);
	    $Tagok = array();
	    for ($i = 0; $i < count($osztalyTagok); $i++) {
		foreach (array('anya','apa') as $szulo) {

		    $szuloId = $osztalyTagok[$i][$szulo.'Id'];
		    if ($szuloId == '') continue;
		    $szAdat = $osztalyTagok[$i][$szulo] = getSzuloAdat($szuloId);
		    $acc = $osztalyTagok[$i][$szulo]['userAccount'];
		    $Tagok[$szuloId] = $osztalyTagok[$i]; // Az adatrögzítés szuloId alapján megy
		    $Tagok[$szuloId]['szuloNev'] = $szAdat['szuloNev'];
		    $Tagok[$szuloId]['userPassword'] = $osztalyTagok[$i][$szulo]['userPassword'] = sprintf("%u", crc32($szAdat['csaladinev']));

		    if ($acc == '') $ret = array('count' => 0); 
		    else $ret = searchAccount('userAccount', $acc, array('userAccount','userCn'), 'parent');

		    if ($ret['count'] > 0) {
			for ($j = 0; $j < $ret['count']; $j++) { // csak a pontos egyezés jó - több pontos egyezés meg nem lehet
			    if ($ret[$j]['userAccount'][0] == $acc) {
				$osztalyTagok[$i][$szulo.'Account'] = $ret[$j]['userAccount'][0];
				$osztalyTagok[$i][$szulo.'UserCn'] = $ret[$j]['userCn'][0];
				break;
			    }
			}
		    }
		    if (!isset($osztalyTagok[$i][$szulo.'Account'])) {
			// Ha az userAccount alapján nem találtunk, akkor tovább keresünk név alapján
			$ret = searchAccount('userCn', $szAdat['szuloNev'], array('userAccount','userCn'), 'parent');
           		if ($ret['count'] > 0) {
			    $osztalyTagok[$i][$szulo.'Accounts'] = $osztalyTagok[$i][$szulo.'Cns'] = array();
			    for ($j = 0; $j < $ret['count']; $j++) {
				$osztalyTagok[$i][$szulo.'Accounts'][] = $ret[$j]['userAccount'][0];
				$osztalyTagok[$i][$szulo.'Cns'][] = $ret[$j]['userCn'][0];
			    }
			}
			// Javasolt azonosító generálása
			$csNev = $szAdat['csaladinev'];
			$pos = mb_strpos($csNev, ' ', 0, 'UTF-8');
			if ($pos > 0 && mb_substr($csNev, $pos-2, 2, 'UTF-8') == 'né') {
			    $csNev = mb_substr($csNev,$pos+1,strlen($csNev)-$pos-1,'UTF-8');
			    $pos = mb_strpos($csNev, ' ', 0, 'UTF-8');
			}
			if ($pos > 0) $csNev = mb_substr($csNev, 0, $pos, 'UTF-8');
			$uNev = $szAdat['utonev'];
			$pos = mb_strpos($uNev, ' ', 0, 'UTF-8');
			if ($pos > 0) $uNev = mb_substr($uNev, 0, $pos, 'UTF-8');
			if ($AUTH['parent']['unixStyleAccounts']) {
			    $csNev = ekezettelen(kisbetus($csNev));
			    $uNev = ekezettelen(kisbetus($uNev));
			    $osztalyTagok[$i][$szulo.'GeneratedAccount'] = substr($csNev.$uNev[0], 0, 8);
			} else {
			    $pos = mb_strpos($csNev, ' ', 0, 'UTF-8');
			    $osztalyTagok[$i][$szulo.'GeneratedAccount'] = $csNev.'.'.$uNev;
			}
		    }
		} // anya | apa
	    } // osztály tagok

	if ($action == 'createAzonosito') {
	    $userPassword = readVariable($_POST['userPassword'], 'sql', null);
	    $CONTAINER = $AUTH['parent'][ $AUTH['parent']['backend'].'Containers'];
	    $container = readVariable($_POST['container'], 'enum', null, $CONTAINER);
	    if (is_array($CONTAINER) && count($CONTAINER) > 0 && !isset($container)) {
		$_SESSION['alert'][] = 'message:empty_fields:container';
	    } else {
		define('__JELSZOGENERALAS', $userPassword == '');

		if (is_array($_POST['userAccount'])) {
		    for ($i = 0; $i < count($_POST['userAccount']); $i++) {
			$szuloId = readVariable($_POST['szuloId'][$i], 'id', null);
			$userAccount = readVariable($_POST['userAccount'][$i], 'sql', null);
			if (isset($szuloId) && $userAccount != '') {
			    if (__JELSZOGENERALAS === true) {
				$userPassword = $Tagok[$szuloId]['userPassword'];// = sprintf("%u", crc32($szAdat['csaladinev']));
			    }
			    $userCn = $Tagok[$szuloId]['szuloNev'];
			    $ret = createAccount($userCn, $userAccount, $userPassword, 'parent', 
				array('category' => null, 'container' => $container, 'groups' => '', 'policyAttrs' => array())
			    );
			    if ($ret === true) {
				// a szulo.userAccount módosítása
				$q = "UPDATE szulo SET userAccount='%s' WHERE szuloId=%u";
				$v = array($userAccount, $szuloId);
				db_query($q, array('fv'=>'szuloiAzonositok-pre','modul'=>'naplo_intezmeny','values'=>$v));
				// a settings táblában felvesszük, hogy melyik intézményhez van rendelve (ez persze nem korlátoz, csak egy alapértelmezés
    				$q = "DELETE FROM settings WHERE userAccount='%s' AND policy='%s'";
    				db_query($q, array('debug'=>false,'fv'=>'updateNaploSettings','modul'=>'naplo_base','values'=>array($userAccount, 'parent')));
    				$q = "INSERT INTO settings (userAccount,policy,intezmeny) VALUES ('%s','%s','%s')";
    				db_query($q, array('debug'=>false,'fv'=>'updateNaploSettings','modul'=>'naplo_base','values'=>array($userAccount, 'parent', __INTEZMENY)));

			    }
			}
		    }


		    // Újraolvasás
		    for ($i = 0; $i < count($osztalyTagok); $i++) {
			foreach (array('anya','apa') as $szulo) {

			    $szuloId = $osztalyTagok[$i][$szulo.'Id'];
			    if ($szuloId == '') continue;
			    $szAdat = $osztalyTagok[$i][$szulo] = getSzuloAdat($szuloId);
			    $acc = $osztalyTagok[$i][$szulo]['userAccount'];
			    $Tagok[$szuloId] = $osztalyTagok[$i]; // Az adatrögzítés szuloId alapján megy
			    $Tagok[$szuloId]['szuloNev'] = $szAdat['szuloNev'];
			    $Tagok[$szuloId]['userPassword'] = $osztalyTagok[$i][$szulo]['userPassword'] = sprintf("%u", crc32($szAdat['csaladinev']));

			    if ($acc == '') $ret = array('count' => 0); 
			    else $ret = searchAccount('userAccount', $acc, array('userAccount','userCn'), 'parent');

			    if ($ret['count'] > 0) {
				for ($j = 0; $j < $ret['count']; $j++) { // csak a pontos egyezés jó - több pontos egyezés meg nem lehet
				    if ($ret[$j]['userAccount'][0] == $acc) {
					$osztalyTagok[$i][$szulo.'Account'] = $ret[$j]['userAccount'][0];
					$osztalyTagok[$i][$szulo.'UserCn'] = $ret[$j]['userCn'][0];
					break;
				    }
				}
			    }
			    if (!isset($osztalyTagok[$i][$szulo.'Account'])) {
				// Ha az userAccount alapján nem találtunk, akkor tovább keresünk név alapján
				$ret = searchAccount('userCn', $szAdat['szuloNev'], array('userAccount','userCn'), 'parent');
           			if ($ret['count'] > 0) {
				    $osztalyTagok[$i][$szulo.'Accounts'] = $osztalyTagok[$i][$szulo.'Cns'] = array();
				    for ($j = 0; $j < $ret['count']; $j++) {
					$osztalyTagok[$i][$szulo.'Accounts'][] = $ret[$j]['userAccount'][0];
					$osztalyTagok[$i][$szulo.'Cns'][] = $ret[$j]['userCn'][0];
				    }
				}
			    }
			} // anya | apa
		    } // osztály tagok
		    // Újraolvasás vége




		} // van elküldött userAccount
	    }

	} // action

    } // van osztály kiválasztva


    $TOOL['tanevSelect'] = array('tipus' => 'cella','tanev' => $tanev, 'paramName' => 'tanev', 'post' => array('osztalyId'));
    $TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array('igDt', 'tanev'));
    getToolParameters();


}

?>
