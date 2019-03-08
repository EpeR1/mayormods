<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (is_array($ADAT['tankorBlokk']['blokkNevek'])) putBlokkOraszamForm($ADAT);
    putTobbszorosOraForm($ADAT);
    putExportForm($ADAT);

?>
