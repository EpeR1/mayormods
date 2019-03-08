<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;
    
    if (count($ADAT['vizsgaIdoszak']) > 0) putVizsgaJelentkezes($ADAT);
    elseif (__NAPLOADMIN === true) echo '<a href="'.href('index.php?page=naplo&sub=admin&f=szemeszterek&szemeszterId='.$ADAT['szemeszterId']).'">'._VIZSGAIDOSZAK_FELVETELE.'</a>';
?>
