<?php

    /* --- képzések lekérdezése --- */

    function getKepesitesek($SET = null) {

	$result = readVariable($SET['result'], 'enum', 'indexed', array('indexed','assoc'));
	$q = "SELECT * FROM kepesites ORDER BY kepesitesNev";
	return db_query($q, array('fv'=>'getKepesitesek','modul'=>'naplo_intezmeny','result'=>$result,'keyfield'=>'kepesitesId'));

    }

    function getTanarKepesites($tanarId) {

	$q = "SELECT * FROM tanarKepesites LEFT JOIN kepesites USING (kepesitesId) WHERE tanarId=%u";
	return db_query($q, array('fv'=>'getTanarKepesites','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array($tanarId)));

    }

    function getTanarKepesitesIds($tanarIds = null) {

	$q = "SELECT tanarId, kepesitesId FROM tanarKepesites";
	if (is_array($tanarIds)) {
	    $q .= " WHERE tanarId IN (".implode(',', array_fill(0, count($tanarIds), '%u')).")";
	    $v = $tanarIds;
	}
	$r = db_query($q, array('fv'=>'getTanarKepesitesIds','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));

	if (is_array($r)) {
	    for ($i=0; $i<count($r); $i++) $result[ $r[$i]['tanarId'] ][] = $r[$i]['kepesitesId'];
	    return $result;
	}
	return $r;

    }

    /* --- kepesítés adatai --- */

    function getKepesitesTargy($kepesitesId) {

	$q = "SELECT * FROM kepesitesTargy LEFT JOIN targy USING (targyId) WHERE kepesitesId=%u ORDER BY targyNev, targyId";
	return db_query($q, array('fv'=>'getKepesitesTargy','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array($kepesitesId)));

    }

    function getKepesitesTanar($kepesitesId) {

	$q = "SELECT *, CONCAT_WS(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev) AS tanarNev FROM tanarKepesites LEFT JOIN tanar USING (tanarId) 
		WHERE kepesitesId=%u ORDER BY tanarNev, tanarId";
	return db_query($q, array('fv'=>'getKepesitesTanar','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>array($kepesitesId)));

    }

    /* --- set --- */

    function tanarKepesitesHozzarendeles($tanarId, $kepesitesId) {

	$q = "INSERT INTO tanarKepesites (tanarId, kepesitesId) VALUES (%u,%u)";
	$v = array($tanarId, $kepesitesId);
	return db_query($q, array('fv'=>'tanarKepesitesHozzarendeles','modul'=>'naplo_intezmeny','values'=>$v));

    }

    function kepesitesTargyHozzarendeles($kepesitesId, $targyId) {

	$q = "INSERT INTO kepesitesTargy (kepesitesId, targyId) VALUES (%u,%u)";
	$v = array($kepesitesId, $targyId);
	return db_query($q, array('fv'=>'kepesitesTargyHozzarendeles','modul'=>'naplo_intezmeny','values'=>$v));

    }

    function kepesitesTargyTorles($kepesitesId, $targyId) {

	$q = "DELETE FROM kepesitesTargy WHERE kepesitesId=%u AND targyId=%u";
	$v = array($kepesitesId, $targyId);
	return db_query($q, array('fv'=>'kepesitesTargyTorles','modul'=>'naplo_intezmeny','values'=>$v));

    }

    function tanarKepesitesTorles($tanarId, $kepesitesId) {

	$q = "DELETE FROM tanarKepesites WHERE tanarId=%u AND kepesitesId=%u";
	$v = array($tanarId, $kepesitesId);
	return db_query($q, array('fv'=>'tanarKepesitesTorles','modul'=>'naplo_intezmeny','values'=>$v));

    }

    function ujKepesites($vegzettseg, $fokozat, $specializacio, $kepesitesNev) {
	$q = "INSERT INTO kepesites (vegzettseg, fokozat, specializacio, kepesitesNev) VALUES ('%s','%s','%s','%s')";
	$v = array($vegzettseg, $fokozat, $specializacio, $kepesitesNev);
	return db_query($q, array('fv'=>'ujKepesites','modul'=>'naplo_intezmeny','result'=>'insert','values'=>$v));

    }

    function kepesitesModositas($kepesitesId, $vegzettseg, $fokozat, $specializacio, $kepesitesNev) {

	$q = "UPDATE kepesites SET vegzettseg='%s',fokozat='%s',specializacio='%s',kepesitesNev='%s' WHERE kepesitesId=%u";
	$v = array($vegzettseg, $fokozat, $specializacio, $kepesitesNev, $kepesitesId);
	return db_query($q, array('debug'=>false,'fv'=>'kepesitesModositas','modul'=>'naplo_intezmeny','values'=>$v));

    }

?>