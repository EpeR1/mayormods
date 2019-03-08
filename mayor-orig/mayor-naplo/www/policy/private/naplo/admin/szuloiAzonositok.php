<?php

    if (_RIGHTS_OK !== true) die();

    global $osztalyId, $osztalyTagok, $Tagok, $ldapTagok, $tanev;

    if (is_array($osztalyTagok)) putCreateAzonositoForm($osztalyId, $osztalyTagok, $tanev, $Tagok);

?>
