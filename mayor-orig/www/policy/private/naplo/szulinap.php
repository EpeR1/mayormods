<?php

   if (_RIGHTS_OK !== true) die();

    global $ADAT;

if ($skin=='ajax') {

    $szulinapos= false;

    if (__NAPLOADMIN || __TANAR) {
	echo '<ul>';
	for ($i=0; $i<count($ADAT['ma']['diak']); $i++) {
	echo '<li>';
	echo $ADAT['ma']['diak'][$i]['viseltNevElotag'].' ';
	echo $ADAT['ma']['diak'][$i]['viseltCsaladinev'].' ';
	echo $ADAT['ma']['diak'][$i]['viseltUtonev'];
	echo '</li>';
	}
	echo '</ul>';
    }

    if (is_array($ADAT['ma']['diakOsztaly'])) foreach($ADAT['ma']['diakOsztaly'] as $_diakId => $_osztalyId) {
	$O[$_osztalyId[0]]++;
	if ( defined('__USERDIAKID') && __USERDIAKID == $_diakId ) $szulinapos = true;

    }

    if ($szulinapos) echo _HAPPYBIRTHDAY;

    if (is_array($O)) {
	echo '<ul>';
	foreach($O as $osztalyId => $db) {
	    echo '<li>';
	    echo $ADAT['osztaly'][$osztalyId]['osztalyJel'].': ' ;
	    echo $db.' ';
	    echo '</li>';
	}
	echo '</ul>';
    }

}

?>
