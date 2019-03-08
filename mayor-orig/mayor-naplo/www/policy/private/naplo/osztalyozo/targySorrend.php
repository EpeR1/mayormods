<?php

    if (_RIGHTS_OK !== true) die();

    global $osztalyId, $sorrendNev, $Targyak;

    if (isset($sorrendNev) && $sorrendNev != '') putTargySorrendForm($osztalyId, $sorrendNev, $Targyak);
?>
