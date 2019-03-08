<?php

    if (_RIGHTS_OK !== true) die();

    global $Szulok, $tipus, $diakAdat, $ADAT;

    if (is_array($diakAdat)) putDiakSzulo($diakAdat, $Szulok, $tipus, $ADAT);
//    if (isset($tipus));
//    else
//	echo 'KERESŐ, vagy ÚJ';


?>
