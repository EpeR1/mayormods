<?php

    global $ADAT;
#    echo 'Használt kulcs érték párok:';
#    var_dump($ADAT['kulcsertektar']['osztalyjel2id']);

    putTTFimport($ADAT);

    if (count($ADAT['bug']['targy'])>0) 
	var_dump($ADAT['bug']['targy']);

    if (count($ADAT['bug']['diak'])>0) 
	var_dump($ADAT['bug']['diak']);





?>