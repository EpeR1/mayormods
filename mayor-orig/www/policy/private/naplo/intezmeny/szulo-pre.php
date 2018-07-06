<?php

    if (_RIGHTS_OK !== true) die();

    if (isset($_POST['szuloId']) && $_POST['szuloId'] != '') $szuloId = $_POST['szuloId'];
    elseif (isset($_GET['szuloId']) && $_GET['szuloId'] != '') $szuloId = $_GET['szuloId'];

    if (isset($_POST['attr']) && $_POST['attr'] != '') $attr = $_POST['attr'];
    elseif (isset($_GET['attr']) && $_GET['attr'] != '') $attr = $_GET['attr'];

    require_once('include/modules/naplo/share/szulo.php');

    $Szulok = getSzulok();

?>
