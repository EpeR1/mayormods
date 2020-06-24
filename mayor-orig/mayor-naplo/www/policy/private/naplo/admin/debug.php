<?php
global $ADAT;

    if (is_array($ADAT)) {
	foreach ($ADAT['debug_result'] as $key => $value) {
	    echo '<h1>'.$key.'</h1>';
	    dump($value);
	}
    }

?>