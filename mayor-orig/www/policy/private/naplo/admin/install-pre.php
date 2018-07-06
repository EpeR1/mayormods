<?php

    if (_RIGHTS_OK !== true) die();
//    if (__NAPLO_INSTALLED !== false) die('A __NAPLO_INSTALLED konstans már true? ('._CONFIGDIR.'/module-naplo/config.php)');

	function checkInstall($force = false) {

	    global $group_ok, $admin_ok, $db_ok, $MYSQL_DATA;

	    // naploadmin?
	    $group_ok = true;
	    $admin_ok = memberOf(_USERACCOUNT,'naploadmin');
	    
	    if (!$admin_ok) {
		if (strpos($_SESSION['alert'][count($_SESSION['alert'])-1], 'no_group:naploadmin')) {
		    $group_ok = false;
		    array_pop($_SESSION['alert']);
		}
		// Nincs naploadmin csoport
		if (!$group_ok && $force===true) {
		    require_once('include/modules/session/createGroup.php');
		    if (createGroup('naploadmin','Napló adminisztrátorok',_USERACCOUNT,'egyéb')) {
			$group_ok = true;
			$admin_ok = true;
		    }
		}
	    }

	    // mayor_naplo adatbázis van?
	    $db_ok = db_connect('naplo_base');
	    if ($db_ok) db_close($db_ok);
	    else $_SESSION['alert'][] = '::naplo_base';

    }

    if ($action === 'createDatabase') {

        require_once('include/modules/naplo/share/mysql.php');
        $dbNev = $MYSQL_DATA['naplo_base']['db'];
        //createDatabase($dbNev, __ALAP_DB_FILE, $_POST['rootUser'], $_POST['rootPassword']);

    }

    checkInstall();


?>
