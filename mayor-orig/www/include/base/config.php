<?php

if (PHP_SAPI!=='cli') define('_RUNLEVEL','www'); else define('_RUNLEVEL','cron');

define('_JSLIB','jquery.min');

if (!defined('_LOGLEVEL')) define('_LOGLEVEL',10);

date_default_timezone_set('Europe/Budapest');

if (_RUNLEVEL === 'cron') {
    (include("../config/main-config.php")); // csak webrootból futtatható
} else {
    if (@file_exists('../config/main-config.php') && @is_readable('../config/main-config.php')==true) {
	include("../config/main-config.php");
    } elseif(@file_exists('../config/main-config.php')===false) { 
	die('FATAL ERROR! Missing '.$_SERVER['DOCUMENT_ROOT'].'/../config/main-config.php');
    } elseif (@is_readable('../config/main-config.php')==false) {
	die('FATAL ERROR! Forbidden to read "main-config.php"');
    } else {
	die('FATAL ERROR! Unknown error! '.$_SERVER['DOCUMENT_ROOT'].'/../config/main-config.php');
    }
    if (defined('_LOCKFILE') && @file_exists(_LOCKFILE)) {
	include(_BASEDIR.'/update.php');
	die();
    }
}

if (!defined('_SECURECOOKIE')) define('_SECURECOOKIE', true);

define('_BASE_URL',( ($_SERVER['HTTPS']=='on')?"https://".$_SERVER['SERVER_NAME']:"http://".$_SERVER['SERVER_NAME'] ));

/* classic, blue, ajax + rpc, + cron kiegészítés */
$SKINS = @array_unique(array_merge($SKINS,array('classic','blue','ajax','rpc','gray','cron')));
$SKINSSHOW = @array_unique(array_merge($SKINSSHOW,array('classic','vakbarat','gray')));

if (is_array($POLICIES) && defined('_CONFIGDIR')) {
    foreach ($POLICIES as $key => $_policy) {
        if (file_exists(_CONFIGDIR."/$_policy-conf.php")) {
            @require(_CONFIGDIR."/$_policy-conf.php");
	    if (file_exists(_BASEDIR.'/policy/'.$_policy.'/'.$DEFAULT_PSF[$_policy]['page'].'/'.$DEFAULT_PSF[$_policy]['sub'].'/'.$DEFAULT_PSF[$_policy]['f'].'.php')
		=== false) {
		//nincs meg ez a file
		$_SESSION['alert'][] = 'info:file_not_found:default page:'.$_policy.':'.implode(' ',$DEFAULT_PSF[$_policy]);
	    }
        } else {
            $DEFAULT_PSF[$_policy] = array();
            $_SESSION['alert'][] = 'page:file_not_found:'._CONFIGDIR."/$_policy-conf.php";
        }
    }
} else {
    $_SESSION['alert'][] = 'page:config_error:nincs POLICIES tömb vagy _CONFIGDIR konstans!';
}

if (!defined('__FBCONNECT_ENABLED')) define('__FBCONNECT_ENABLED',false);
if (!defined('__SHOW_FACES_TYPE')) define('__SHOW_FACES_TYPE','circle'); // circle, square, classic

$VALID_MODULES =array('portal','naplo','auth','jatek','password','session','fenntarto');
if(is_array($EXTRA_MODULES)) $VALID_MODULES = array_unique(array_merge($VALID_MODULES,$EXTRA_MODULES));


?>
