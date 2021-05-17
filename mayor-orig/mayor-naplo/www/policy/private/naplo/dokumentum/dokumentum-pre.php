<?php

/*
   dump(addDokumentum(array('dokumentumLeiras'=>'Éves Munkaterv','dokumentumUrl'=>
'http://szerver4.kanizsay.sulinet.hu/KDESZIG/munkaterv_20_21.pdf',
'dokumentumSorrend'=>2)));
*/

    if (_RIGHTS_OK!==true) die();

    if (__NAPLOADMIN===true) {
	$_SESSION['MAYOR_RIGHTS_OK'] = true;

    if ($action=='addDokumentum') {
	$_leiras = readVariable($_POST['dokumentumLeiras'],'string');
	$_rovidLeiras = readVariable($_POST['dokumentumRovidLeiras'],'string');
	$_url = readVariable($_POST['dokumentumUrl'],'url');
	$_megjegyzes = readVariable($_POST['dokumentumMegjegyzes'],'string');
	$_sorrend  = readVariable($_POST['dokumentumSorrend'],'id');
	$_tipus  = readVariable($_POST['dokumentumTipus'],'enum','tanev',array('general','tanev'));
	$_policy  = readVariable($_POST['dokumentumPolicy'],'enum','private',array('public','parent','private'));
	$dokumentumId = addDokumentum(
	    array(
		'dokumentumLeiras' => $_leiras,
		'dokumentumRovidLeiras' => $_rovidLeiras,
		'dokumentumUrl' => $_url,
		'dokumentumMegjegyzes' => $_megjegyzes,
		'dokumentumSorrend'=> $_sorrend,
		'dokumentumTipus' => $_tipus,
		'dokumentumPolicy' => $_policy
	    )
	);
    } elseif ($action=='delDokumentum') {
	$_ids = readVariable($_POST['dokumentumId'],'id');
	delDokumentum($_ids);
    }
    }
    $ADAT = getDokumentumok();
    $ADATASSOC = getDokumentumokAssoc();

?>