<?php
global $ADAT;

    echo '<style type="text/css">
/*	div.mayorbody { color:white; background-color: #888; padding:10px;}*/
    </style>';

    if (is_array($ADAT)) {
	foreach ($ADAT['debug_result'] as $key => $value) {
	    echo '<h1>'.$key.'</h1>';
	    dump($value);
	}
    }

?>