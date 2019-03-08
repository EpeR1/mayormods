<?php

    if (_RIGHTS_OK !== true) die();
//    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/diak.php');
    require_once('include/modules/naplo/share/kepzes.php');
    require_once('include/modules/naplo/share/szulo.php');
    require_once('include/modules/naplo/share/hianyzas.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/tanar.php');

    $szuloId = readVariable($_POST['szuloId'], 'id',null);
    if (!is_numeric($szuloId)) { $_JSON = array(); exit;}

    /* PRIVÁT ADATOK */
    if (__NAPLOADMIN === true || __VEZETOSEG === true || __TITKARSAG ===true || __TANAR ===true) {
	$_JSON = getSzuloAdat($szuloId);
	$_tmp = getSzulokDiakjai(array($szuloId));
	$_JSON['szuloGyermekei'] = $_tmp[$szuloId];
    }
    /* PUBLIKUS ADATOK */
    $_JSON['diakId'] = $diakId;
    $_JSON['tanev'] = __TANEV;

?>