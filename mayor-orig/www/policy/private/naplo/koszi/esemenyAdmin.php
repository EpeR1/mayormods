<?php

    global $ADAT;

    echo '<h2>'._ESEMENYMINTAK.'</h2>';
    echo '<div class="kosziline"></div>';
    putKosziEsemenyek($ADAT);

    if (is_numeric($ADAT['kosziEsemenyId'])) {
	putKosziPont($ADAT);

	echo '<h2>'._ESEMENYEK_TANEVBEN.'</h2>';
	echo '<div class="kosziline"></div>';
	putUjKoszi($ADAT);
	putKoszi($ADAT);
    } else {
	putUjKosziEsemeny($ADAT);
    }


?>
