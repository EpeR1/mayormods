<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    if (isset($ADAT['tanmenetId'])) putTanmenetInfo($ADAT);
    else  {
	if ($skin != 'ajax') formBegin(array('class'=>"tanmenet"));
        echo '<input type="hidden" name="tanmenetId" id="informTanmenetId" value="" />'."\n";
	echo '<p class="kiemelt">Nincs tanmenet megadva!</p>';
	if ($skin != 'ajax') formEnd();
    }

?>
