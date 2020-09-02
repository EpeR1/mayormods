<?php

    global $ADAT;

    putTTFimport($ADAT);

    if (count($ADAT['bug']['targy'])>0) {
	echo '<h1>Hibás tárgyak</h1>';
	echo '<pre>';
	var_dump($ADAT['bug']['targy']);
	echo '</pre>';
    }

    if (count($ADAT['bug']['diak'])>0) {
	echo '<h1>Hibás diákok</h1>';
	echo '<pre>';
	var_dump($ADAT['bug']['diak']);
	echo '</pre>';
    }

    echo 'Használt kulcs-érték párok (osztályJel-osztályId):';
    echo '<pre>';
	var_dump($ADAT['kulcsertektar']['osztalyjel2id']);
    echo '</pre>';



?>