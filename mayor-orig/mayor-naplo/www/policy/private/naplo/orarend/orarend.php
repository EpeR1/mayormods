<?php

    global $ADAT;

    if ($ADAT['orarendvane']==0)
	putNincsOrarend();
    if (is_array($ADAT))
	putOrarend($ADAT);

?>
