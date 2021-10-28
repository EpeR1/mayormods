<?php

    if (_RIGHTS_OK !== true) die();
    global $ADAT;

    if (__INTEZMENY=='kanizsay') {
	$ADAT['kulsosview']=false;
	putTanarLista_large($ADAT);
	$ADAT['kulsosview']=true;
	putTanarLista_large($ADAT);
    } else putTanarLista($ADAT);

?>
