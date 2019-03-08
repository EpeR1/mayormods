<?php

    function updateNaploSettings($intezmeny) {
	$q = "DELETE FROM settings WHERE userAccount='%s' AND policy='%s'";
	db_query($q, array('debug'=>false,'fv'=>'updateNaploSettings','modul'=>'naplo_base','values'=>array(_USERACCOUNT,_POLICY)));
	$q = "INSERT INTO settings (userAccount,policy,intezmeny) VALUES ('%s','%s','%s')";
	return db_query($q, array('debug'=>false,'fv'=>'updateNaploSettings','modul'=>'naplo_base','values'=>array(_USERACCOUNT,_POLICY,$intezmeny)));
    }
?>
