<?php

    if (_RUNLEVEL!=='cron') die('not valid RUNLEVEL! Exiting.');
    if (__EMAIL_ENABLED!==true) die('EMAIL_ENABLED is false! Exiting.');

    require_once('include/modules/naplo/share/hirnok.php');
    require_once('include/share/net/phpmailer.php');

    global $_TANEV;

    $refDt = date('Y-m-d H:i:s');
    $q = "SELECT naploId,naploTipus,utolsoEmailDt,userAccount,policy,email FROM hirnokFeliratkozas WHERE utolsoEmailDt IS NULL or utolsoEmailDt<'%s'";
    $v = array($refDt);
    $r = db_query($q,array('fv'=>'cron','modul'=>'naplo_intezmeny','result'=>'indexed','values'=>$v));

    for ($i=0; $i<count($r); $i++) {
	$d = $r[$i];
	if (defined('_DEVEL') && _DEVEL===true) $d['email'] = 'konczy+test@gmail.com'; // over
	$TOLDTBYUSER[$d['naploTipus']][$d['naploId']] = ($d['utolsoEmailDt']=='') ? $_TANEV['kezdesDt'] : $d['utolsoEmailDt']; // setDt
	if (!in_array($d['naploId'],$USER[$d['naploTipus']])) $USER[$d['naploTipus']][] = $d['naploId'];
	$ADAT['feliratkozas'][$d['naploTipus']][$d['naploId']][] = array(
	    'userAccount'=>$d['userAccount'], 
	    'policy'=>$d['policy'], 
	    'email'=>$d['email'], 
	    'setDt'=> $refDt
	);
    }

    // Megszorítás: egy naplóbeli entitás utolsoEmailDt-je együtt kell, hogy mozogjon ebben a feldolgozási rendszerben.
    // Javítható: hirnokWrapper() függvény sokszoros használatával
    $ADAT['hirnokFolyam'] = hirnokWrapper(array('tolDt'=>$tolDt,
	'diakId'=>$USER['diak'],
	'tanarId'=>$USER['tanar'],
	'tolDtByUser'=>$TOLDTBYUSER
    ));


?>