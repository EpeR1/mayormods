<?php

    global $ADAT;


    $err = 0;
    if (is_array($ADAT['diakok'])) 
	foreach ($ADAT['diakok'] as $_diakId => $dAdat) {
	    _check($ADAT['diakOrarend'][$_diakId],$dAdat,$err);
	}

    if ($err==0) echo 'nincs ütközés';

    
function _check($ADAT,$dAdat,&$err) {
    $TANKORADAT = $ADAT['tankorok'];
    if (is_array($ADAT['orarend']['assoc']))
    foreach($ADAT['orarend']['assoc'] as $het => $HETI) {
	foreach ($HETI as $nap => $NAPI) {
	    foreach($NAPI as $ora => $ORA) {
		if (count($ORA)>1) {
		    $db=0;
		    for ($j=0; $j<count($ORA); $j++) {
			if (is_array($TANKORADAT[$ORA[$j]['tankorId']])) $db++;
		    }
		    if ($db>1) {
			$err++;
			echo $dAdat['diakNev'];
			echo " hét:$het nap:$nap ora:$ora ütközés szám:".count($ORA)."db. <br/>";
			echo '<ul>';
			for ($j=0; $j<count($ORA); $j++) {
			    echo '<li>';
			    echo $TANKORADAT[$ORA[$j]['tankorId']][0]['tankorNev'];
			    echo '('.$TANKORADAT[$ORA[$j]['tankorId']][0]['jelenlet'].')';
			    echo $TANKORADAT[$ORA[$j]['tankorId']][0]['tankorId'];
			    echo '</li>';
			}
			echo '</ul>'."\n";
		    }
		}
	    }
	}
    }
}


?>
