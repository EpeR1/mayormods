<?php

    function getNevnap($ho,$nap) {
	$v = array($ho,$nap);
	return db_query("SELECT `nevnap` FROM `nevnap` WHERE `honap`=%u AND `nap`=%u", array('modul'=>'portal','values'=>$v,'result'=>'value'));
    }

?>
