<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    validRegisztracio($ADAT['registrationStatus']['result']['valid'], $ADAT['my']['nodeId']);
    if ($ADAT['registrationStatus']['result']['valid'] != 1) {
	putCheckOldReg($ADAT);
	putRegisztracio($ADAT);
    } else {
	putRegisztracioMod($ADAT);
    }
    putPublicKey($ADAT['my']['publicKey']);

?>
