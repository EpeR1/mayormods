<?php

    function getVersenyek() {

	$q = "SELECT * FROM verseny";
	return db_query($q, array('modul'=>'naplo_intezmeny'));

    }

    function ujVerseny($ADAT) {


	$q = "INSERT INTO verseny (targyId,versenyNev) VALUES (%u,'%s')";
	$v = array($ADAT['targyId'],$ADAT['versenyNev']);

	

    }

?>
