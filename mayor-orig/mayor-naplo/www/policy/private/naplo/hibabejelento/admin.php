<?php

    if (_RIGHTS_OK !== true) die();

    global $Kerelmek,$telephelyId,$TELEPHELY;

    if ($skin=='classic') putHibabejelento($telephelyId);

    if (is_array($Kerelmek) && count($Kerelmek)>0) {
	if ($_GET['view']==2){
	    putKerelmekValasszal($Kerelmek,$telephelyId,$TELEPHELY);
	} else {
	    putKerelmek($Kerelmek,$telephelyId,$TELEPHELY);
	}
    }
?>
