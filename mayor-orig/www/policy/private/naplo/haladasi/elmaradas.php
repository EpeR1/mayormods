<?php

    if (_RIGHTS_OK !== true) die();

    global $Elmaradas, $Tanarok;

    if (
	(is_array($Elmaradas['lezart']) && count($Elmaradas['lezart']) > 0)
	|| (is_array($Elmaradas['beirando']) && count($Elmaradas['beirando']) > 0)
    ) putElmaradas($Elmaradas, $Tanarok);

?>
