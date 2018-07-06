<?php

    if (_RIGHTS_OK !== true) die();
    if (__DIAK!==true && __TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
	exit;
    }

    require_once('include/modules/naplo/share/dolgozat.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');

    $dolgozatId = $_JSON['dolgozatId'] = readVariable($_POST['dolgozatId'], 'id');

    $_JSON['dolgozatAdat'] = getDolgozatAdat($dolgozatId);
    $_JSON['leiras'] = 'Dolgozat';

?>