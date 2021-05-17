<?php

    function checkConstants() {


	$_check = array(
	    '__OSZTALYFONOKI_IGAZOLAS_EVRE',
//	    '__TANITASI_HETEK_SZAMA',
//	    '__VEGZOS_TANITASI_HETEK_SZAMA',
	    '__SZULOI_IGAZOLAS_FELEVRE',
            '__SZULOI_IGAZOLAS_EVRE',
            '__SZULOI_ORA_IGAZOLAS_FELEVRE',
            '__SZULOI_ORA_IGAZOLAS_EVRE',
            '__OSZTALYFONOKI_IGAZOLAS_FELEVRE',
            '__OSZTALYFONOKI_IGAZOLAS_EVRE',
            '__OSZTALYFONOKI_ORA_IGAZOLAS_FELEVRE',
            '__OSZTALYFONOKI_ORA_IGAZOLAS_EVRE',
            '_KESESI_IDOK_OSSZEADODNAK',
            '_HANY_KESES_IGAZOLATLAN',
            '_HANY_FSZ_IGAZOLATLAN',
            '_HIANYZAS_HATARIDO',
            '_OFO_HIANYZAS_BEIRAS',
            '_OFO_HIANYZAS_HATARIDO',
            '_IGAZOLAS_BEIRAS',
	    '_IGAZOLAS_BEIRAS_HATARIDO',
            '_IGAZOLAS_LEADAS',
            '_LEGKORABBI_IGAZOLHATO_HIANYZAS',
	    '_VIZITHOSSZ',
	    '__DEFAULT_SULYOZAS',
	    
	);

	for($i=0; $i<count($_check); $i++) {
	    if (!defined($_check[$i])) $_SESSION['alert'][] = 'alert:missing_constant:'.$_check[$i];
	}

	if (defined('_TANKOR_MODOSITAS_HATARIDO'))
	    $_SESSION['alert'][] = 'alert:obsolete_constant:_TANKOR_MODOSITAS_HATARIDO';
    }

    global $page,$sub,$f;

    if (__NAPLOADMIN===true && !($page=='naplo' && $sub=='admin' && $f=='tanevek') && defined('__INTEZMENY')) checkConstants();

    if (!defined('__UZENO_DEFAULTLIMIT')) define('__UZENO_DEFAULTLIMIT',20);
    if (!defined('__UZENO_AJAXLIMIT')) define('__UZENO_AJAXLIMIT',5);
    if (!defined('__SHOW_FACES')) define('__SHOW_FACES','always');


    if ($skin!='vakbarat') { // nem szép megoldás, de nem tudok jobbat, ez a beállítás skin függő
	if (__SHOW_FACES=='always') {
	    define('__SHOW_FACES_TF',true);
	} elseif (__SHOW_FACES=='menu-driven') {
	    define('__SHOW_FACES_TF',true);
	} elseif (__SHOW_FACES=='optional') {
	    define('__SHOW_FACES_TF',true);
	} else {
	    define('__SHOW_FACES_TF',false);
	}
    } else {
	define('__SHOW_FACES_TF',false);    
    }

    // CHECK ME!
    if (!defined('__DETAILED')) {
	if (__NAPLOADMIN===true) {
	    define('__DETAILED',true);
	} else {
	    define('__DETAILED',false);
	}
    }

    if (!defined('__HIANYZASTOROLHETO')) define('__HIANYZASTOROLHETO',false); else define('__HIANYZASTOROLHETO',true);

    if (!defined('__MAXORA_MINIMUMA')) define('__MAXORA_MINIMUMA',8);
    if (!defined('__HETIMAXNAP_MINIMUMA')) define('__HETIMAXNAP_MINIMUMA',5);

    if (!defined('__HIANYZASBA_NEM_SZAMITO_TIPUSOK')) {
	define('__HIANYZASBA_NEM_SZAMITO_TIPUSOK','délutáni,egyéni foglalkozás,tanórán kívüli');   
    }
    if (!defined('__TANITASINAP_HETENTE')) define('__TANITASINAP_HETENTE',5);

    if (!defined('__ORACIMKE_ENABLED')) define('__ORACIMKE_ENABLED',true) ;

    /* üzenő szerepkor beallitasa */
	if (__UZENOADMIN===true) {
    	    $__asWho = readVariable($_POST['asWho'],'strictstring','asIs',array('asAdmin','asIs'));
    	    define('__ASWHO',$__asWho);
	} else {
	    define('__ASWHO','asIs');
	}

        if (defined('__UZENOADMIN') && __UZENOADMIN===true && defined('__ASWHO') && __ASWHO==='asAdmin') define('__UZENOSZEREP','admin');
        elseif (__TANAR===true) define('__UZENOSZEREP', 'tanar');
        elseif (__DIAK===true && defined('__PARENTDIAKID') && intval(__PARENTDIAKID)>0) define('__UZENOSZEREP','szulo');
        elseif (__DIAK===true && defined('__USERDIAKID') && intval(__USERDIAKID)>0) define('__UZENOSZEREP','diak');
        elseif (__UZENOADMIN===true) define('__UZENOSZEREP','admin');
        else define('__UZENOSZEREP','');

    if (!is_array($KERELEM_TAG) || count($KERELEM_TAG) ==0) $KERELEM_TAG = array('névsor','jogviszony','órarend, haladási','hiányzás, igazolás','jegy, zárójegy');

    if (!defined('__HABEL_GYURI_DEBUG')) define('__HABEL_GYURI_DEBUG',false);
    if (!defined('MAYOR_SOCIAL')) define('MAYOR_SOCIAL',false);

    if (_USERACCOUNT=='mayoradmin' || (_POLICY=='private' && memberOf(_USERACCOUNT, 'felveteliadmin'))) {
        $AUTH['my']['categories'][] = 'felveteliadmin';
        define('__FELVETELIADMIN',true);
    } else {
        define('__FELVETELIADMIN',false);
    }

?>
