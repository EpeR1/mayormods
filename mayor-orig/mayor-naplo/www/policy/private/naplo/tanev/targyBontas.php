<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;


    if ($ADAT['targyBontasStatus']===false) {
	putTargyBontasInit();
    }else{
	putFilter($ADAT);
	if (
	    is_array($ADAT['osztalyIds']) && count($ADAT['osztalyIds'])>0
	    && is_array($ADAT['kepzesIds']) && count($ADAT['kepzesIds'])>0
	) {
	    putTargyBontas($ADAT);
	}
    }
?>