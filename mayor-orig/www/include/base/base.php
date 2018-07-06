<?php
/*
    Module: base
*/

function isMobile() {
    if(preg_match('/(up.browser|up.link|windows ce|iemobile|mmp|symbian|smartphone|midp|wap|phone| vodafone|o2|pocket|mobile|pda|psp)/i',strtolower($_SERVER['HTTP_USER_AGENT'])))
	return true;
    //if(((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'text/vnd.wap.wml')>0) or (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0)) or ((((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']) or isset($_SERVER['X-OperaMini-Features']) or isset($_SERVER['UA-pixels']))))))
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array('acs-','alav','alca','amoi','audi','aste','avan','benq' ,'bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java' ,'jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','opwv','palm','pana','pant','pdxg' ,'phil','play','pluc','port','prox','qtek','qwap', 'sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw' ,'xda','xda-');
    if(in_array($mobile_ua,$mobile_agents)) {
	return true;
    }
}

// ------------------------------------------
// PHP session
// ------------------------------------------

    session_start();

// ------------------------------------------
// Böngésző azonosítás
// ------------------------------------------

    if (defined('_ALLOWPDAIDENTIFICATION') && isMobile()) define('_USER_AGENT','ppc'); // inkább ketté kéne bontani [wap,ppc] tartalomra
    else
    if (strpos($_SERVER['HTTP_USER_AGENT'],'Opera') !== false) define('_USER_AGENT','opera');
    elseif (strpos($_SERVER['HTTP_USER_AGENT'],'Gecko') !== false) define('_USER_AGENT','gecko');
    elseif (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') !== false) define('_USER_AGENT','msie');
    else define('_USER_AGENT','');

    if (strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')!=false) define('_USER_AGENT_PLUS','iPhone');
    else define('_USER_AGENT_PLUS','');

    if (($_SESSION['isMobile']=isMobile())===true) $_SESSION['pageLayout']=1; // patch

// MaYoR revision konstans definiálása

    if (@file_exists(_LOGDIR.'/revision')) {
	$_rf = @fopen(_LOGDIR.'/revision', "r");
	if ($_rf) $rev = @fgets($_rf, 64);
	@fclose($_rf);
    } else {
	//rev missing...
    }
    define('_MAYORREV',chop($rev));
    unset($rev);

// ------------------------------------------
// Default értékek
// ------------------------------------------
    /*
	$policy - hozzáférési mód                  - kötelező
	$page   - megjelenítendő oldal             - kötelező
	$sub    - aloldal                          - opcionális
	$f      - file                             - kötelező
	$lang   - az oldal nyelve                  - kötelező
	skin    - az oldal témája, "bőre"          - kötelező
	$action - elvégzendő feladat megjelölésére - opcionális
	$_SESSION['alert']  - figyelmeztető üzenet (tömb)      - opcionális
    */

    if (($__key = array_search('blue', $SKINS)) !== false) unset($SKINS[$__key]);
    if (($__key = array_search('pda', $SKINS)) !== false) unset($SKINS[$__key]);
    if ($AUTH['public']['skin'] == 'blue') $AUTH['public']['skin'] = 'classic';
    if ($AUTH['private']['skin'] == 'blue') $AUTH['private']['skin'] = 'classic';
    if ($AUTH['parent']['skin'] == 'blue') $AUTH['parent']['skin'] = 'classic';
    // itt beolvassuk, később még egy szigorítás is van

    if (_RUNLEVEL=='cron') {
	$policy = 'private';
	@$page = readVariable($_SERVER['argv'][1],'strictstring');
	@$sub = readVariable($_SERVER['argv'][2],'strictstring');
	@$f = readVariable($_SERVER['argv'][3],'strictstring');
	@$sessionID = "cron";
    } else {
	@$policy = readVariable($_REQUEST['policy'],'strictstring',null,$POLICIES);
	@$page = readVariable($_REQUEST['page'],'strictstring');
	@$sub = readVariable($_REQUEST['sub'],'strictstring');
	@$f = readVariable($_REQUEST['f'],'strictstring');
	@$sessionID = readVariable($_GET['sessionID'],'hexa');
    }
    if ($f == '') {
	if ($sub != '') $f = $sub;
	elseif ($page != '') $f = $page;
    }
    @$lang = readVariable($_GET['lang'],'strictstring',null,$LANGUAGES);
    if (_RUNLEVEL === 'cron') $skin = 'cron';
    else @$skin = readVariable($_POST['skin'],'strictstring',readVariable($_GET['skin'],'strictstring',null,$SKINS),$SKINS);
    @$action = readVariable($_REQUEST['action'],'strictstring',null);
    // ++ ha még mindig üres a skin, és pocketpc-ről/pda jövünk (de megengedjük a felülírást)
    // if (_USER_AGENT==='ppc' && $skin=='') $skin='pda';
    // ++
    //--
    if (is_array($POLICIES) && !in_array($policy, $POLICIES)) $policy = _DEFAULT_POLICY;

    define('_POLICY', $policy);

    // Miert ne csatolnánk be az összes policy beállítsait? --> config.php

    if ($f=='' && is_array($DEFAULT_PSF[$policy])) extract($DEFAULT_PSF[$policy], EXTR_OVERWRITE);
    if (!in_array($lang, $LANGUAGES)) $lang=_DEFAULT_LANG;
    // ha a skin még mindig üres, akkor az ellenőrzésnél a default-ot állítjuk be...
    if (!@in_array($skin, $SKINS)) $skin = (isset($AUTH[$policy]['skin'])) ? $AUTH[$policy]['skin'] : _DEFAULT_SKIN;

    if (file_exists("lang/$lang/base/base.php")) {
        require("lang/$lang/base/base.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/base/base.php')) {
        require('lang/'._DEFAULT_LANG.'/base/base.php');
    }

    // A skin-hez tartozó beállátosok.. ha vannak... - ide való? De még a *-pre elé!
    if (file_exists(_CONFIGDIR."/skin-$skin/config.php")) {
        require(_CONFIGDIR."/skin-$skin/config.php");
    } elseif (file_exists(_CONFIGDIR.'/skin-'._DEFAULT_SKIN.'/config.php')) {
        require(_CONFIGDIR.'/skin-'._DEFAULT_SKIN.'/config.php');
    }

// -----------------------------------------------------------------
//  page()
// -----------------------------------------------------------------

function page($page, $sub, $f, $lang, $skin, $policy = _DEFAULT_POLICY) {

    global $_JSON;
    if (html_alert($_SESSION['alert'])) { // A figyelmeztető üzenet letilthatja az oldal további megjelenítését.

        if ($sub != '') {
            $load = "$sub/$f";
        } else {
            $load = $f;
        }

        if (file_exists("policy/$policy/$page/$load.php")) {
    	    if (file_exists("lang/$lang/module-$page/base.php")) {
		require_once("lang/$lang/module-$page/base.php");
	    } elseif (file_exists("lang/"._DEFAULT_LANG."/module-$page/base.php")) {
		require_once("lang/"._DEFAULT_LANG."/module-$page/base.php");
    	    }
    	    if (file_exists("lang/$lang/module-$page/$load.php")) {
		require_once("lang/$lang/module-$page/$load.php");
	    } elseif (file_exists("lang/"._DEFAULT_LANG."/module-$page/$load.php")) {
		require_once("lang/"._DEFAULT_LANG."/module-$page/$load.php");
    	    }
    	    if (file_exists("skin/$skin/module-$page/html/base.phtml")) {
		require_once("skin/$skin/module-$page/html/base.phtml");
	    } elseif (file_exists("skin/"._DEFAULT_SKIN."/module-$page/html/base.phtml")) {
		require_once("skin/"._DEFAULT_SKIN."/module-$page/html/base.phtml");
    	    }
    	    if (file_exists("skin/$skin/module-$page/html/$load.phtml")) {
		require_once("skin/$skin/module-$page/html/$load.phtml");
	    } elseif (file_exists("skin/"._DEFAULT_SKIN."/module-$page/html/$load.phtml")) {
		require_once("skin/"._DEFAULT_SKIN."/module-$page/html/$load.phtml");
    	    }

	    include("policy/$policy/$page/$load.php");
	    //szamlal($policy,$page);
        } elseif (file_exists("static/$lang/$page/$load.html")) {
	    include("static/$lang/$page/$load.html");
	    //szamlal($policy,$page);
        } else {
            //??? ha már kiírtuk a hibaüzeneteket, újabbat nem írhatunk ki sajnos :( html_alert(array('page:page_missing:'."[$page]:[$sub]:[$f]")); --> rights.php
        }
    }
}

function href($href,$get = array('sessionID','lang','skin','policy')) {

    global $sessionID,$lang,$skin,$policy,$page,$sub,$f,$action;
    global $SKINS;
    if ($href!='') {
        if (strpos($href,'?') === false) {
            $href .= '?';
        } else {
            $href.='&';
        }
	for ($i=0;$i<count($get);$i++) {
	    $par = $get[$i];
	    if ($par == 'skin' && $skin == 'ajax') $value = readVariable($_GET['toSkin'],'enum',null,$SKINS);
	    else $value = $$par;
	    if (is_array($value)) { // pl. $_SESSION['alert']
		for ($j=0;$j<count($value);$j++) {
		    $href .= $par.'[]='.$value[$j].'&';
		}
	    } else {
		$href .= "$par=".$value.'&';
	    }
	}
	$href = substr($href,0,-1);
	if ($skin == 'pda') $href .= '&rand='.rand(); // PDA hack - mer' a szemétje nem olvassa újra, hiába a fejlécen a sok okos varázslat... :(
        $href = str_replace('&','&#38;',str_replace('&#38;','&',$href));
    }
    return $href;

}

function location($href,$get = array('sessionID','lang','skin','policy')) {

    global $sessionID,$lang,$skin,$policy,$page,$sub,$f,$action;

    if ($href!='') {
        if (strpos($href,'?')===false) {
            $href.='?';
        } else {
            $href.='&';
        }
	for ($i=0;$i<count($get);$i++) {
	    $par = $get[$i];
	    if (is_array($$par)) { // pl. $_SESSION['alert']
		for ($j=0;$j<count($$par);$j++) {
		    $href .= $par.'[]='.${$par}[$j].'&';
		}
	    } else {
		$href .= "$par=".$$par.'&';
	    }
	}
	$href = substr($href,0,-1);
        $href = str_replace('&#38;','&',$href);
    }

    return $href;

}

?>
