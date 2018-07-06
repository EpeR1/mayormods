<?php

if (__UZENO_INSTALLED === true) {

    global $ADAT;
    if ( count($ADAT['uzenetek'])>0  ) 
	putUzeno($ADAT);
    if ($skin!='ajax' && $ADAT['feladoId']!==0) 
	putUzenoUzenet($ADAT);

}
?>
