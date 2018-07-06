<?php

    global $Q_ERR;

    if (is_array($Q_ERR) && count($Q_ERR)>0) {
	echo '<table border="1">';
	for($i=0; $i<count($Q_ERR); $i++) {
	    echo '<tr><th>'.$i.'</th></tr><tr><td>';
	    echo htmlDiff($Q_ERR[$i]['inDb'],$Q_ERR[$i]['inFile']); //filenamenormal checked
	    echo '</td><td>';
	    echo '</td></tr>'."\n";
	}
	echo '</table>';
    }
?>
