<?php
/*
    Module: base

    validUser($sessionID,$policy,$skin='',$lang='') 

    - ellenőrizzük a $page, $sub, $f formai helyességét
    - validUser - ellenőrzi, hogy be vagyunk-e jelentkezve, beállítja a konstansokst, ha kell elküld az auth oldalra
    - validUser esetén a session kezelő fv-ek csatolása (backend függő)
    - a module, psf include fájlainak és a pre fájlnak a csatolása

    A validUser fv két funkciót lát el egyszerre (ellenőriz, és konstans beállít).
    A konstansokat (pl. _SESSIONID) csak az aktuális policy-re állítjuk, a toPolicyra nem.
    A validUser figyelembe veszi a _SESSION_MAX_IDLE_TIME konstans értékét: ha az activity+_SESSION_MAX_IDLE_TIME már elmúlt - akkor elutasít,
    különben az aktivity értékét frissíti.

    a '_POLICY'-t a base.php-ben definiáljuk.

*/
function sessionHash($algo='ripemd160', $hashThis='') 
{
    if ($hashThis=='') $hashThis = uniqid(rand(), true);
    if (!in_array($algo,hash_algos())) $algo = 'sha1';
    $sessionID = substr( hash($algo, $hashThis), 0, 40 ); // 40: sha1, ripem160 
    return $sessionID;
}

if(!function_exists('hash_equals')) { // lásd még str
      function hash_equals($str1, $str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
            return !$ret;
        }
      }
}


function sessionCookieEncode($sessionID,$now,$extra='')
{
    if ($extra=='') $extra = uniqid(rand(), true);
    $extraHash=sessionHash('ripemd160',$extra);
// pwHash added
//  $value = base64_encode(strtotime($now).'g'.$extraHash);
    $pwHash = sessionHash('ripemd160',uniqid(rand(), true));
    $value = base64_encode(strtotime($now).'g'.$extraHash.'g'.$pwHash);
    return array('name'=>md5($sessionID), 'value'=>$value, 'store'=> $extraHash, 'pwHash'=>$pwHash);
}

function sessionCookieDecode($sessionID) 
{
    return explode('g',base64_decode($_COOKIE[md5($sessionID)]));
}

function pseudoTokenGenerator() {

    if (function_exists('openssl_random_pseudo_bytes'))
	$token = bin2hex(openssl_random_pseudo_bytes(32));
    else
	$token = bin2hex(sessionHash());
    return $token;
}

######################################################################
# Azonosított user ellenőrzése a session tábla alapján
######################################################################



function validUser($sessionID,$policy,$skin='',$lang='') {

    if (_RUNLEVEL === 'cron') {
    	    define('_USERPASSWORD','MaYoR-cron');
    	    define('_USERACCOUNT','MaYoR-cron');
    	    define('_USERCN','MaYoR-cron User');
    	    define('_STUDYID','');
    	    define('_LANG',$lang);
    	    define('_SESSIONID','cron');
	    return true;
    }

    if ($sessionID == '') {

	if ($policy == _POLICY) {
    	    define('_USERPASSWORD','');
    	    define('_USERACCOUNT','');
    	    define('_USERCN','');
    	    define('_STUDYID','');
//    	    define('_SKIN',$skin);
    	    define('_LANG',$lang);
    	    define('_SESSIONID','');
	}
        return false;

    } else {

       $lr = db_connect('login', array('fv' => 'validUser'));

        if ($lr === false) die('A keretrendeszer adatbázisa nem érhető el! (validUser)');
	// ha nem tudta beállítani a sütit, akkor az $_sc üres lesz így a dt feltétel 1970-01-01, ami nem gond.
// pwHash
//	list($_sessionDt,$_sessionCookie) = sessionCookieDecode($sessionID);
	list($_sessionDt,$_sessionCookie,$_sessionPwHash) = sessionCookieDecode($sessionID);
// pwHash
//        $query = "SELECT userAccount, userCn, studyId, decode(userPassword, '"._MYSQL_ENCODE_STR."'), skin, lang, activity, dt
//                    FROM session WHERE sessionID='%s' AND policy='%s'";
        $query = "SELECT userAccount, userCn, studyId, aes_decrypt(userPassword, '%s'), skin, lang, activity, dt
		    FROM session WHERE sessionID='%s' AND policy='%s'"; // [SECURITY-002] quickfix from marton.drotos@sztaki.hu

	if (defined('_SESSION_MAX_IDLE_TIME') and _SESSION_MAX_IDLE_TIME != 0) $query .= " AND activity + INTERVAL "._SESSION_MAX_IDLE_TIME." HOUR > NOW()";

	$query .= " AND dt='".date('Y-m-d H:i:s', (($_sessionDt=="")?null:$_sessionDt) )."'";
	$query .= " AND sessionCookie='%s'";

	$ret = db_query($query, array('fv' => 'validUser', 'modul' => 'login', 'result' => 'indexed', 'values' => array($_sessionPwHash, $sessionID, $policy, $_sessionCookie)), $lr);

	$num = count($ret);
        if ($num == 1) {

            list($userAccount, $userCn, $studyId, $userPassword, $savedSkin, $lang, $activity, $dt) = array_values($ret[0]);
	    /* PDA */
	    global $SKINS;
	    if (_USER_AGENT!=='ppc' && @in_array($savedSkin,$SKINS) ) $skin=$savedSkin;
	    if ($policy == _POLICY) {
                define('_USERACCOUNT',$userAccount);
	        define('_USERCN',$userCn);
	        define('_STUDYID',$studyId);
        	define('_USERPASSWORD',$userPassword);
//	        define('_SKIN',$skin);
                define('_LANG',$lang);
        	define('_SESSIONID',$sessionID);
	    }
	    // Aktivitás figyelése!
	    $query = "UPDATE session SET activity = NOW() WHERE sessionID = '%s'";
	    db_query($query, array('fv' => 'validUser', 'modul' => 'login', 'values' => array($sessionID)), $lr);
    	    db_close($lr);
            return true;

        } else {

	    if ($policy == _POLICY) {
        	define('_USERPASSWORD','');
        	define('_USERACCOUNT','');
        	define('_USERCN','');
        	define('_STUDYID','');
//        	define('_SKIN',$skin);
        	define('_LANG',$lang);
        	define('_SESSIONID','');
	    }
    	    db_close($lr);
	    if ($num > 1) $_SESSION['alert'][] = 'message:multi_session';
	    if ($_sessionDt==="") define('_NOCOOKIE',true); // beállítjuk, hogy üzenni tudjunk

            return false;

        }

    }

}

//===================================================================================================================================
// ------------------------------------------
// debug üzenetek tárolása - a $_DEBUG változóba kerül midnen
// ------------------------------------------
    if (_RUNLEVEL!=='cron') ob_start();

// ------------------------------------------
// a $page, $sub és $f csak a-z betűvel kezdődhet, utána pedig csak (a-z, /_-). $page és $f nem lehet üres
// ------------------------------------------

    if (
	(preg_match('#^([a-z]|[A-Z])([0-9]|[a-z]|[A-Z]|/|_|-)*$#', $page) == false) OR
	($sub != '' AND	preg_match('#^([a-z]|[A-Z])([0-9]|[a-z]|[A-Z]|/|_|-)*$#', $sub) == false) OR
	(preg_match('#^([0-9]|[a-z]|[A-Z]|_|-)*$#', $f) == false)
    ) {
        $_SESSION['alert'][] = 'page:wrong_page:';
	$RIGHTS_OK = false;
    } else {
	$RIGHTS_OK = true;
    }

// ------------------------------------------
// Security Check: $policy szerinti ellenőrzés
// ------------------------------------------

    // A validUser (session.php) beállítja az alapvető session konstansokat is
    if ( !validUser($sessionID,$policy,$skin,$lang) ) {
	    if ($AUTH[$policy]['authentication'] == 'required') {
		if (defined('_NOCOOKIE')) $_SESSION['alert'][] = 'message:cookie';//$extendAlert='alert[]=message:cookie&';
		$_SESSION['alert'][]='message:auth_failure:'._CONTROL_FLAG_REQUIRED;
		header('Location: index.php?policy=public&page=auth&f=login&toPolicy='.$policy."&toPSF=$page:$sub:$f&sessionID=$sessionID");
		die();
	    } else {
	    // Hibás, vagy nem létező sessionID esetének kezelése - ha nem kötelező a sessionID --> nem csinálunk semmit (lehet egy másik policy-ben valid
	    }

    } 
    if (file_exists('include/share/session/base.php')) {
	// A session kezeléshez szükséges backend függő függvények pl. memberOf
        require('include/share/session/base.php');

    }
    // Remote Protocol Call (MaYoR)
    if ($skin=='rpc') {
	define('_RPC',true);
        require_once('include/share/ssl/ssl.php');
        try
        {
	    $_RPC['senderNodeId'] = $senderNodeId = readVariable($_POST['senderNodeId'],'strictstring',0);
            $RPC = new Interconnect();
	    $RPC->setRequestTarget('controller'); // A remoteHost lekérdezéshez kellhet
	    $RPC->setRemoteHostByNodeId($_RPC['senderNodeId']);
            $RPC->processRequest(); // vélhetően van request
	    $_RPC['request'] = $RPC->getIncomingRequest();
        }
        catch (Exception $e)
        {
            //$func='';
            //$DATA = array('error'=>$e->getMessage());
        }
    } else { define('_RPC',false); }
    // Interconnect end

    define('_RIGHTS_OK',$RIGHTS_OK);

    /* XSRF2 */
    define('__SALTNAME','MS_'.sha1($page.'_'.$sub.'_'.$f));
    define('__SALTVALUE',sessionHash());
    // mtoken
    if (empty($_SESSION['mayorToken'])) {
	$_SESSION['mayorToken'] = pseudoTokenGenerator();
    }
    if (count($_POST)>0) {
	if (!empty($_POST['mayorToken'])) {
	    if (hash_equals($_SESSION['mayorToken'], $_POST['mayorToken'])) {
		// OK, token regenerálás + visszakuldjuk az ETAG-ben
		$_SESSION['mayorToken'] = pseudoTokenGenerator();
	    } else {
		if ($_POST['action']!='') { /* Ha nincs action formváltozó, nincs szükség hibaüzenetre, a form nem módosít, de most... */
		    $_SESSION['alert'][] = 'message:not_valid_form:pnu2'; 
		    $_JSON['result'] = false;
		}
		unset($_POST['action']);
		unset($action);
	    }
	} else { // klasszikus ellenőrzés, fallback // TODO BEGIN DEPRECATED BLOCK
	    // $_JSON['result'] = false; // ITT gátolhatjuk a működést
	    if ($_COOKIE[__SALTNAME]=='') { // a session átállásig - ez semmitől nem véd, adott nevű sütit generálni bárki tud
		$_SESSION['alert'][] = 'message:not_valid_form:no cookie'.$_SESSION[__SALTNAME]; 
		$_JSON['result'] = false;
		unset($_POST['action']);
		unset($action);
	    } elseif (!is_null($_COOKIE[__SALTNAME]) && ($_COOKIE[__SALTNAME] == $_POST[__SALTNAME])) {
		// rendben 
	    } else {
		if ($_POST['action']!='') { // Ha nincs action formváltozó, nincs szükség hibaüzenetre, a form nem módosít
		    $_SESSION['alert'][] = 'message:not_valid_form'; 
		    $_JSON['result'] = false;
		}
		unset($_POST['action']);
		unset($action);
	    }
	} // END DEPRECATED BLOCK
    }
    // eredeti post kezelés + ETAG prevent cache
    if (($_SERVER['HTTPS']!=='on') || (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!='' && substr($_SERVER['HTTP_REFERER'],4,1)!=='s')) $_ssl = false; else $_ssl=true;
    if (@setcookie(__SALTNAME,__SALTVALUE,time()+60*60*_SESSION_MAX_IDLE_TIME,'/','',$_ssl, true) == false) {
        $_SESSION['alert'][] = 'message:no_cookie:unabletoset';
    }
    /* /XSRF2 previous revision: r4138 */

    // betöltjük az össes config-ot... (lásd még widgets)
    try {
	$_dirlist = scandir(_CONFIGDIR);
	for ($i=0; $i<count($_dirlist); $i++) {
	    $_dir = $_dirlist[$i];
	    if (is_dir(_CONFIGDIR.'/'.$_dir) && substr($_dir,0,7)=='module-') {
		$_configFile = _CONFIGDIR . "/".$_dir.'/config.php';
		list($_rest,$_module) = explode('-',$_dir);
		if (in_array($_module,$VALID_MODULES)) {
		    $_loadModule[] = $_module;
		    if (file_exists($_configFile)) {
			require_once($_configFile);
		    }
		}
	    }
	}
    } catch (Exception $e) {
	$_SESSION['alert'][] = 'info:config hiba:'.$e->getMessage();
    }
    // ha betöltenénk az ÖSSZES modul include-jait, akkor be kéne mindent tölteni kb. ugyhogy ezt hagyjuk
    if ($dh_all = @opendir("include/widgets/")) {
	$includes = array();
	while (($_file = readdir($dh_all)) !== false) {
    	    if (substr($_file,-4) == '.php') $includes[] = $_file;
	}
	closedir($dh_all);
	sort($includes);
	for ($i = 0; $i < count($includes); $i++) {
    	    require_once("include/widgets/".$includes[$i]);
	}
    }
    unset($file);
    /* ---- */
    if (!in_array($page,$_loadModule)) $_loadModule[] = $page;
    for ($i=0; $i<count($_loadModule); $i++) {
	$_module = $_loadModule[$i];
	if (in_array($_module,$VALID_MODULES)) {
		if ($dh = @opendir("include/modules/$_module/base")) {
		    $includes = array();
		    while (($file = readdir($dh)) !== false) {
    			if (substr($file,-4) == '.php') $includes[] = $file;
		    }
		    closedir($dh);
		    sort($includes);
		    for ($j = 0; $j < count($includes); $j++) {
    			require_once("include/modules/$_module/base/".$includes[$j]);
		    }
		}
	}
    }
    unset($file);
    /* ---- */


    $skinFile = _CONFIGDIR."/skin-$skin/$page-config.php";
    if ($sub == '') {
	    $includeFile = "include/modules/$page/$f.php";
	    $preFile = "policy/$policy/$page/$f-pre.php";
	    $pageFile = "policy/$policy/$page/$f.php";
	    $staticFile = "static/$lang/$page/$f.html";
    } else {
	    $includeFile = "include/modules/$page/$sub/$f.php";
	    $preFile = "policy/$policy/$page/$sub/$f-pre.php";
	    $pageFile = "policy/$policy/$page/$sub/$f.php";
	    $staticFile = "static/$lang/$page/$sub/$f.html";
    }
    if (!file_exists($preFile) && !file_exists($pageFile) && !file_exists($staticFile)) 
	$_SESSION['alert'][] = 'page:page_missing:'.$page.'-'.$sub.'-'.$f;
    /* DEFAULTS zcheck() */
    if (defined('_ENABLE_IFRAME_EMBEDING'))
	define('_ENABLE_IFRAME_EMBEDDING',_ENABLE_IFRAME_EMBEDING);
    elseif (!defined('_ENABLE_IFRAME_EMBEDDING'))
	define('_ENABLE_IFRAME_EMBEDDING',false);

    if (!defined('__MAX_MENU')) define('__MAX_MENU',7);

    if (!defined('__SUPPORT_EMAIL_ADDRESS')) {
	if (!defined('__EMAIL_ENABLED')) define('__EMAIL_ENABLED',false);
    } elseif (!defined('__SUPPORT_EMAIL_NAME')) {
	define('__SUPPORT_EMAIL_NAME',_SITE.' support');
	if (!defined('__EMAIL_ENABLED')) define('__EMAIL_ENABLED',true);
    } else {
	if (!defined('__EMAIL_ENABLED')) define('__EMAIL_ENABLED',true);
    } 
    // ---------
    //Breadcrumb
    if (count($_SESSION['breadcrumb'])>10) array_shift($_SESSION['breadcrumb']);
    $_SESSION['breadcrumb'][] = array('page'=>"$page",'sub'=>"$sub",'f'=>"$f");
    // ---------
    if (file_exists($includeFile)) require($includeFile);
    if (file_exists($skinFile)) require($skinFile);
    if (file_exists($preFile)) include($preFile);

// ---------------------------------------
// debug üzenetek tárolásának vége
// ---------------------------------------
    $_DEBUG = ob_get_contents();
    ob_end_clean();


?>