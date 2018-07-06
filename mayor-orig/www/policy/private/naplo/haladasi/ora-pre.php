<?php
    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:illegal_access';
    } else {

        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/helyettesitesModifier.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/modules/naplo/share/orarend.php');
        require_once('include/share/date/names.php');

	$oraId = readVariable($_POST['oraId'],'id');
	$oraElmarad = readVariable($_POST['oraElmarad'],'string');
	$oraMegtartva = readVariable($_POST['oraMegtartva'],'string');
	if (isset($oraElmarad)) {
	    $_JSON['toDo'] =  'oraElmarad'; // processJSON js-ben használjuk
	    $_JSON['oraId'] = $oraId;
	} elseif (isset($oraMegtartva)) {
	    $_JSON['toDo'] = 'oraMegtartva'; // processJSON js-ben használjuk
	    $_JSON['oraId'] = $oraId;
	}

	$ADAT['oraAdat'] = getOraAdatById($oraId);
//	$_JSON['html'] = putOraAdat($ADAT);
	$ADAT['oraAdat']['debug'] = serialize($_POST);

    }
?>
