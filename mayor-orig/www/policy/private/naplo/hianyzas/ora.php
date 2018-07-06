<?php

  if (_RIGHTS_OK !== true) die();

    global $ORAADAT,$ADAT;

    if (is_array($ORAADAT) && is_array($ADAT))    
	putHianyzok($ORAADAT,$ADAT);

?>
