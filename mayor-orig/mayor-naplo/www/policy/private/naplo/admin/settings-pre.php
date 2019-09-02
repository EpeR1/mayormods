<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = "page:insufficient_access";
    } else {

	if ($action=='set') {

	}

    }


?>
