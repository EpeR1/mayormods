<?php


    function hibabejelentes($ADAT) {

	if ($ADAT['txt'] != '') {
	    if ($ADAT['telephelyId']=='') $telephelyId = 'NULL'; else $telephelyId = $ADAT['telephelyId'];
	    $q = "INSERT INTO kerelem (userAccount,szoveg,rogzitesDt,kategoria,telephelyId) VALUES ('"._USERACCOUNT."','%s',NOW(),'%s','%s')";
	    return db_query($q, array('fv' => 'hibabejeletes', 'result'=>'insert','modul' => 'naplo_base', 'values' => array($ADAT['txt'],$ADAT['kategoria'],$telephelyId)));
	} else {
	    return false;
	}

    }

    function kerelemValasz($ADAT) {
	if ($ADAT['txt'] != '' && $ADAT['kerelemId']>0) {
	    //if ($ADAT['telephelyId']=='') $telephelyId = 'NULL'; else $telephelyId = $ADAT['telephelyId'];
	    $q = "INSERT INTO kerelemValasz (kerelemId,userAccount,valasz) VALUES ('%u','"._USERACCOUNT."','%s')";
	    return db_query($q, array('fv' => 'hibabejeletes', 'modul' => 'naplo_base', 'values' => array($ADAT['kerelemId'],$ADAT['txt'])));
	} else {
	    return false;
	}

    }

?>
