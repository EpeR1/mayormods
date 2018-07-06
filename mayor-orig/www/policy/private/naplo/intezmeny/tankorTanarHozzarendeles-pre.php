<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN && !__VEZETOSEG) { $_SESSION['alert'] = 'page:insufficient_access'; }
    else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/munkakozosseg.php');
	require_once('include/modules/naplo/share/tanar.php');
	require_once('include/modules/naplo/share/targy.php');
	require_once('include/modules/naplo/share/tankorModifier.php');

	$ADAT['szuro'] = array(
	    'osztalyok' =>  getOsztalyok(),
	    'munkakozossegek' => getMunkakozossegek(),
	    'tanarok' => getTanarok(),
	    'targyak' => getTargyak(),

	    'osztalyIds'=>readVariable($_POST['osztalyIds'],'id',array()), 
	    'mkIds'=>readVariable($_POST['mkIds'],'id',array()),
	    'tanarNelkuliTankorok' => readVariable($_POST['tanarNelkuliTankorok'],'bool'),
	    'tanarIds'=>readVariable($_POST['tanarIds'],'id',array()),
	    'targyIds'=>readVariable($_POST['targyIds'],'id',array()),
	);
	foreach ($ADAT['szuro']['targyak'] as $idx => $tAdat) $ADAT['targyAdat'][ $tAdat['targyId'] ] = $tAdat;

	// A szűrőben beállítottnak megefelő tankörök lekérése
	$ADAT['tankorok'] = getTankorokBySzuro($ADAT['szuro']);
	$ADAT['szuro']['tankorTargyIds'] = array();
	foreach ($ADAT['tankorok'] as $ids => $tAdat) 
	    if (!in_array($tAdat['targyId'], $ADAT['szuro']['tankorTargyIds'])) 
		$ADAT['szuro']['tankorTargyIds'][] = $tAdat['targyId'];
	$ADAT['tanarok'] = getTanarokBySzuro($ADAT['szuro']);
	// stat
	$ADAT['keszTankorDb'] = 0;
	foreach ($ADAT['tankorok'] as $tAdat) if (is_array($tAdat['tanarIds']) && count($tAdat['tanarIds'])>0) $ADAT['keszTankorDb']++;
	$ADAT['tankorStat'] = getTankorStat();

	if ($action == 'tankorTanarFelvesz') {

            $tankorId = readVariable($_POST['tankorId'],'id');
            $tanarId = readVariable($_POST['tanarId'],'id');
	    $_JSON = array(
		'post' => $_POST,
		'result' => tankorTanarModosit($tankorId, $tanarId, array('tanev'=>__TANEV))
	    );

	} elseif ($action == 'tankorTanarTorol') {

            $_tankorId = readVariable($_POST['tankorId'],'id');
            $_tanarId = readVariable($_POST['tanarId'],'id');
            tankorTanarTorol($_tankorId,$_tanarId,array('tanev'=>$tanev));

	    $_JSON = array(
		'post' => $_POST,
	    );
	}

//dump($ADAT['tanarok']);


    }
 
?>