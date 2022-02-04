<?php

    global $ADAT;

    if ($skin!='ajax' && $ADAT['orarendvane']==0) {
        putNincsOrarend();
    }
    if (is_array($ADAT)) {
        putOrarend($ADAT);
    }

?>
