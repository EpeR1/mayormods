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
    $_JSON['dolgozatAdat'] = $Dolgozat = getDolgozatAdat($dolgozatId);

      define(__MODOSITHAT,
            isset($dolgozatId)
            && (
                (__NAPLOADMIN === true && $_TANEV['statusz'] == 'aktív')
                || (
                    __FOLYO_TANEV === true && __TANAR === true
                    && is_array($Dolgozat['tankorok'])
                    && in_array(__USERTANARID, $Dolgozat['tankorok'][0]['tanarok'])
                )
            )
        );

    /*if (__MODOSITHAT === true) {
	$dolgozatBeirhato = oraBeirhato($oraId);
	if ($dolgozatBeirhato === true && $action=='dolgozatTorles') {
            dolgozatTorles($dolgozatId);
        }
    }*/

    $_JSON['leiras'] = 'Dolgozat';

?>