<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['kepesitesId'])) putKepesitesAdat($ADAT);
    else putUjKepesites($ADAT);

?>