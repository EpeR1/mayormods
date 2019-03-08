<?php

    define('_WEBSERVER_ADDR',$_SERVER['SERVER_ADDR']);

    function createDatabase($dbName, $queryFile, $rootUser = 'root', $rootPassword = '', $convert = array()) {

	global $MYSQL_DATA;

	$ok = true;

	// Kötelező paraméterek ellenőrzése
        if ($dbName == '' or $rootUser == '' /* or $rootPassword == '' // SuliX root jelszó nélküli alapértelmezésben // */) {
	    $_SESSION['alert'][] = 'message:empty_field:createDatabase';
	    return false;
	}

	/* Ki kell találni, hány SQL szerverünk van! */
	$HOSTS = _setHosts();

	for ($h=0; $h<count($HOSTS); $h++) {
	    $host = $HOSTS[$h];
    	    // Csatlakozás root-ként
	    $lr = db_connect(null, array('priv' => 'root', 'host'=> $host, 'force' => true, 'username' => $rootUser, 'password' => $rootPassword, 'db' => 'mayor_naplo', 'fv' => 'createDatabase'));
	    if (!$lr) return false;

	    // Kliens karakterkódolása
	    db_query("SET CHARACTER SET utf8", array('fv'=>'createDatabase#1'), $lr);

	    // Az adatbázis létrehozása
	    $q = "CREATE DATABASE `%s` CHARACTER SET utf8 COLLATE utf8_hungarian_ci";
    	    $r = db_query($q, array('fv'=>'createDatabase#2', 'values' => array($dbName)), $lr);
	    if ($r !== true) { // pl. ha már létezik...
		db_close($lr);
		return false;
	    }

	    /* Minden hoston létrehozzuk a usereket! Write/Read - et egyaránt! Még akkor is, ha csak a masterről olvasunk, slave-re írunk */
    	    $password =  $MYSQL_DATA['naplo_base']['pwWrite'];
    	    $user = $MYSQL_DATA['naplo_base']['userWrite'];
    	    $userHost = ($host=='localhost' || $host=='127.0.0.1') ? 'localhost' : _WEBSERVER_ADDR;
	    $q = "GRANT ALL ON `%s`.* TO '%s'@'%s' IDENTIFIED BY '%s'";
    	    $r = db_query($q, array('fv' => 'createDatabase#3', 'values' => array($dbName, $user, $userHost, $password)), $lr);
	    if ($r !== true) $ok = false;

    	    $password =  $MYSQL_DATA['naplo_base']['pwRead'];
    	    $user = $MYSQL_DATA['naplo_base']['userRead'];
    	    $userHost = ($host=='localhost' || $host=='127.0.0.1') ? 'localhost' : _WEBSERVER_ADDR;
	    $q = "GRANT SELECT,EXECUTE ON `%s`.* TO '%s'@'%s' IDENTIFIED BY '%s'";
    	    $r = db_query($q, array('fv' => 'createDatabase#4', 'values' => array($dbName, $user, $userHost, $password)), $lr);
	    if ($r !== true) $ok = false;

	    if (db_query("USE `%s`", array('fv' => 'createDatabase#use', 'values' => array($dbName)), $lr) && $ok) {

    		$fp = fopen($queryFile, 'r');
    		$query = fread($fp, filesize($queryFile));
    		fclose($fp);

		// A tárolt eljárásoknak, függvényeknek "DELIMITER //" és "DELIMITER ; //" között kell lenniük - egy blokkban a file végén!
		list($query, $delimiter) = explode('DELIMITER //', $query);
	    
		// Tábladefiníciók - normál query-k
    		$QUERIES = explode(';', str_replace("\n", '', $query));
    		for ($i = 0; $i < count($QUERIES); $i++) {
        	    $q = $QUERIES[$i];
        	    if (trim($q) != '' and substr($q, 0, 2) != '--' and substr($q, 0, 3) != '/*!') {
	    		if (is_array($convert))
	    		foreach ( $convert as $mit=>$mire ) $q = str_replace($mit,$mire,$q);
			$r = db_query($q, array('fv'=>'createDatabase#6-'.$i), $lr);
			if ($r !== true) { $ok = false; break; }
		    } elseif ($q != '') {
			$_SESSION['alert'][] = ':query_error:'.$q;
		    }
    		}

    		if ($ok !== false) {
    		    list($delimiter, $end) = explode('DELIMITER ; //',$delimiter);
		    $procQueries = explode('//', $delimiter);
		    for ($i = 0; $i < count($procQueries); $i++) {
			$q = trim($procQueries[$i]); // ebben vannak most ;-ők és sortörések...
			if ($q[strlen($q)-1] == ';') $q = substr($q, 0, -1); // A végén nem lehet ; !!
			if ($q != '') {
	    		    if (is_array($convert))
	    		    foreach ( $convert as $mit=>$mire ) $q = str_replace($mit,$mire,$q);
			    $r = db_query($q, array('fv'=>'createDatabase#7-'.$i), $lr);
			    if ($r !== true) { $ok = false; break; }
			}
		    }
		} // if ok    
	    } else {
		$ok = false;
	    }

	    if ($ok === false) {
		$_SESSION['alert'][] = 'message:sql_db_dropped:'.$dbName;
		db_query("DROP DATABASE `%s`", array('fv' => 'createDatabase#7', 'values' => array($dbName)), $lr);
	    }
	} // HOSTS ciklusa

        db_close($lr);
	return $ok;

    }

    function revokeWriteAccessFromDb($dbName, $rootUser = 'root', $rootPassword = '') {

	global $MYSQL_DATA;

	// Kötelező paraméterek ellenőrzése
        if ($dbName == '' or $rootUser == '' or $rootPassword == '') {
	    $_SESSION['alert'][] = 'message:empty_field:revokeWriteAccessFromDb';
	    return false;
	}

	/* Minden MySQL hostról elvesszük a jogot */
	/* Ki kell találni, hány SQL szerverünk van! */
	$HOSTS = _setHosts();

	for ($h=0; $h<count($HOSTS); $h++) {
	    $host = $HOSTS[$h];

    	    // Csatlakozás root-ként
	    $lr = db_connect(null, array('priv' => 'root', 'force' => true, 'host'=>$host, 'username' => $rootUser, 'password' => $rootPassword, 'db' => 'mayor_naplo', 'fv' => 'revokeWriteAccessFromDb'));
	    if (!$lr) return false;

	    // jogok elvétele a write usertől
	    $user = $MYSQL_DATA['naplo_base']['userWrite'];
    	    $userHost = ($host=='localhost' || $host=='127.0.0.1') ? 'localhost' : _WEBSERVER_ADDR;
	    $q = "REVOKE ALTER,CREATE,DROP,INSERT,UPDATE,DELETE ON `%s`.* FROM '%s'@'%s'";
    	    $r = db_query($q, array('fv' => 'revokeWriteAccessFromDb', 'values' => array($dbName, $user, $userHost)), $lr);
    	    db_close($lr);
    	}
	return $r;

    }

    function grantWriteAccessToDb($dbName, $rootUser = 'root', $rootPassword = '') {

	global $MYSQL_DATA;

	// Kötelező paraméterek ellenőrzése
        if ($dbName == '' or $rootUser == '' or $rootPassword == '') {
	    $_SESSION['alert'][] = 'message:empty_field:grantWriteAccessToDb';
	    return false;
	}

	/* Ki kell találni, hány SQL szerverünk van! */
	$HOSTS = _setHosts();

	for ($h=0; $h<count($HOSTS); $h++) {
	    $host = $HOSTS[$h];

            // Csatlakozás root-ként
	    $lr = db_connect(null, array('priv' => 'root', 'force' => true, 'host'=>$host, 'username' => $rootUser, 'password' => $rootPassword, 'db' => 'mayor_naplo', 'fv' => 'grantWriteAccessToDb'));
	    if (!$lr) return false;

	    // Írási jog...
    	    $user = $MYSQL_DATA['naplo_base']['userWrite'];
    	    $userHost = ($host=='localhost' || $host=='127.0.0.1') ? 'localhost' : _WEBSERVER_ADDR;
	    $q = "GRANT ALL ON `%s`.* TO '%s'@'%s'";
    	    $r = db_query($q, array('fv' => 'grantWriteAccessToDb', 'values' => array($dbName, $user, $userHost)), $lr);

    	    db_close($lr);
    	}
	return $r;

    }

    function _setHosts() {
	global $MYSQL_DATA;
	$HOSTS = array();
	foreach ( array('hostWrite','hostRead','host') as $_host ) {
	    if ($MYSQL_DATA['naplo_base'][$_host]!='' && !in_array($MYSQL_DATA['naplo_base'][$_host],$HOSTS)) {
		$HOSTS[] = $MYSQL_DATA['naplo_base'][$_host];
	    }
	}
	if (count($HOSTS)==0) 
	    if ($MYSQL_DATA['host']=='') 
		$HOSTS[] = 'localhost';
	    else 
		$HOSTS[] = $MYSQL_DATA['host'];
	return $HOSTS;    
    }

?>
